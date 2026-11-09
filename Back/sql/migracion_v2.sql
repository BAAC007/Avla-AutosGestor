-- ============================================================================
--  AVLA Autosgestor · Migración de base de datos v1 -> v2
--  Probada sobre MySQL 8.0.46 partiendo del dump original (avla.sql).
--
--  CÓMO USAR
--    1. Haz una copia de seguridad ANTES:  mysqldump -h HOST -P PUERTO -u USER -p BD > backup.sql
--    2. Ejecútalo UNA sola vez:           mysql -h HOST -P PUERTO -u USER -p BD < migracion_v2.sql
--       (si ya se aplicó, la tabla schema_version existirá y el script falla en el
--        primer CREATE sin tocar nada más)
--
--  QUÉ HACE
--    · Limpia las filas de prueba de prueba_manejo (test / test_debug)
--    · Renombra vehiculo.año -> vehiculo.anio (sin tilde)
--    · Amplía vehiculo (ficha técnica, disponibilidad, matrícula, garantía...)
--    · Añade galería de imágenes, empleados, roles de admin
--    · Amplía venta (vendedor, descuento, IVA, nº factura) y endurece pago
--    · Añade contacto (leads), reserva, favorito, tasacion, mantenimiento,
--      venta_accesorio e intento_login
--    · Añade estados a prueba_manejo y financiacion, y evita doble reserva de
--      la misma prueba / del mismo coche
--    · Crea las vistas v_vehiculo_catalogo y v_venta_resumen
--
--  Los datos técnicos de los vehículos quedan vacíos: rellénalos desde el panel
--  (o ejecuta el opcional datos_demo_v2.sql si tus ids 1-5 son los del dump original).
-- ============================================================================

SET NAMES utf8mb4;

-- Marca de versión (si esta tabla ya existe, el script se detiene aquí)
CREATE TABLE schema_version (
  version  varchar(20) NOT NULL,
  aplicada timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 0. Limpieza de datos de prueba
-- ----------------------------------------------------------------------------
DELETE FROM prueba_manejo WHERE observaciones IN ('test', 'test_debug');

-- ----------------------------------------------------------------------------
-- 1. EMPLEADO (necesaria antes de las FK de venta / prueba / administrador)
-- ----------------------------------------------------------------------------
CREATE TABLE empleado (
  id          int          NOT NULL AUTO_INCREMENT,
  nombre      varchar(255) NOT NULL,
  email       varchar(255) NOT NULL,
  telefono    varchar(20)  DEFAULT NULL,
  cargo       varchar(20)  NOT NULL DEFAULT 'vendedor',
  activo      tinyint(1)   NOT NULL DEFAULT 1,
  fecha_alta  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_empleado_email (email),
  CONSTRAINT chk_empleado_cargo CHECK (cargo IN ('vendedor','gerente','mecanico','administrativo'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE administrador
  ADD COLUMN rol           varchar(20) NOT NULL DEFAULT 'gestor',
  ADD COLUMN empleado_id   int         DEFAULT NULL,
  ADD COLUMN ultimo_acceso timestamp   NULL DEFAULT NULL,
  ADD CONSTRAINT chk_admin_rol CHECK (rol IN ('superadmin','gestor','vendedor')),
  ADD CONSTRAINT fk_admin_empleado FOREIGN KEY (empleado_id) REFERENCES empleado (id) ON DELETE SET NULL;

UPDATE administrador SET rol = 'superadmin' WHERE usuario IN ('admin', 'bryan');

-- ----------------------------------------------------------------------------
-- 2. VEHICULO
-- ----------------------------------------------------------------------------
-- Quitar el CHECK que referencia a `año` antes de renombrar la columna.
-- Se busca por su contenido (rango 1900-2100): su nombre puede variar y el nombre
-- de columna dentro del CHECK puede estar mal codificado (aÃ±o) en dumps antiguos.
SELECT tc.CONSTRAINT_NAME INTO @chk_anio
  FROM information_schema.TABLE_CONSTRAINTS tc
  JOIN information_schema.CHECK_CONSTRAINTS cc
    ON cc.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA AND cc.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
 WHERE tc.TABLE_SCHEMA = DATABASE() AND tc.TABLE_NAME = 'vehiculo'
   AND tc.CONSTRAINT_TYPE = 'CHECK' AND cc.CHECK_CLAUSE LIKE '%1900%' AND cc.CHECK_CLAUSE LIKE '%2100%'
 LIMIT 1;
SET @sql_chk = IF(@chk_anio IS NULL, 'DO 0', CONCAT('ALTER TABLE vehiculo DROP CONSTRAINT `', @chk_anio, '`'));
PREPARE stmt_chk FROM @sql_chk;
EXECUTE stmt_chk;
DEALLOCATE PREPARE stmt_chk;
ALTER TABLE vehiculo RENAME COLUMN `año` TO anio;

ALTER TABLE vehiculo
  MODIFY COLUMN estado varchar(10) NOT NULL DEFAULT 'nuevo',
  ADD COLUMN matricula              varchar(15)       DEFAULT NULL,
  ADD COLUMN combustible            varchar(20)       DEFAULT NULL,
  ADD COLUMN transmision            varchar(15)       DEFAULT NULL,
  ADD COLUMN potencia_cv            smallint unsigned DEFAULT NULL,
  ADD COLUMN puertas                tinyint unsigned  DEFAULT NULL,
  ADD COLUMN plazas                 tinyint unsigned  DEFAULT NULL,
  ADD COLUMN etiqueta_dgt           varchar(5)        DEFAULT NULL,
  ADD COLUMN propietarios_anteriores tinyint unsigned NOT NULL DEFAULT 0,
  ADD COLUMN garantia_meses         smallint unsigned NOT NULL DEFAULT 0,
  ADD COLUMN descripcion            text              DEFAULT NULL,
  ADD COLUMN disponibilidad         varchar(15)       NOT NULL DEFAULT 'disponible'
        COMMENT 'Stock: disponible / reservado / vendido (independiente de estado nuevo/usado)',
  ADD COLUMN destacado              tinyint(1)        NOT NULL DEFAULT 0,
  ADD UNIQUE KEY uq_vehiculo_matricula (matricula),
  ADD KEY idx_disponibilidad_precio (disponibilidad, precio),
  ADD CONSTRAINT chk_vehiculo_anio         CHECK (anio BETWEEN 1900 AND 2100),
  ADD CONSTRAINT chk_vehiculo_combustible  CHECK (combustible IS NULL OR combustible IN
        ('gasolina','diésel','híbrido','híbrido enchufable','eléctrico','GLP','GNC')),
  ADD CONSTRAINT chk_vehiculo_transmision  CHECK (transmision IS NULL OR transmision IN ('manual','automático')),
  ADD CONSTRAINT chk_vehiculo_etiqueta     CHECK (etiqueta_dgt IS NULL OR etiqueta_dgt IN ('0','ECO','C','B','SIN')),
  ADD CONSTRAINT chk_vehiculo_disponibil   CHECK (disponibilidad IN ('disponible','reservado','vendido'));

-- Disponibilidad derivada de las ventas existentes
UPDATE vehiculo v SET disponibilidad = 'vendido'
 WHERE EXISTS (SELECT 1 FROM venta s WHERE s.vehiculo_id = v.id AND s.estado = 'completada');
UPDATE vehiculo v SET disponibilidad = 'reservado'
 WHERE disponibilidad = 'disponible'
   AND EXISTS (SELECT 1 FROM venta s WHERE s.vehiculo_id = v.id AND s.estado = 'pendiente');

-- (Los datos técnicos de demostración están en datos_demo_v2.sql, opcional.)

-- ----------------------------------------------------------------------------
-- 3. GALERÍA DE IMÁGENES (vehiculo.imagen se mantiene como portada)
-- ----------------------------------------------------------------------------
CREATE TABLE vehiculo_imagen (
  id           int              NOT NULL AUTO_INCREMENT,
  vehiculo_id  int              NOT NULL,
  ruta         varchar(500)     NOT NULL,
  orden        smallint unsigned NOT NULL DEFAULT 0,
  es_principal tinyint(1)       NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_vehiculo_orden (vehiculo_id, orden),
  CONSTRAINT fk_vimg_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculo (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO vehiculo_imagen (vehiculo_id, ruta, orden, es_principal)
SELECT id, imagen, 0, 1 FROM vehiculo WHERE imagen IS NOT NULL AND imagen <> '';

-- ----------------------------------------------------------------------------
-- 4. CLIENTE
-- ----------------------------------------------------------------------------
ALTER TABLE cliente
  MODIFY COLUMN usuario varchar(50) NOT NULL,
  ADD COLUMN direccion        varchar(255) DEFAULT NULL,
  ADD COLUMN ciudad           varchar(100) DEFAULT NULL,
  ADD COLUMN codigo_postal    varchar(10)  DEFAULT NULL,
  ADD COLUMN activo           tinyint(1)   NOT NULL DEFAULT 1,
  ADD COLUMN acepta_marketing tinyint(1)   NOT NULL DEFAULT 0,
  ADD COLUMN ultimo_acceso    timestamp    NULL DEFAULT NULL;

-- ----------------------------------------------------------------------------
-- 5. VENTA / PAGO / FINANCIACIÓN
-- ----------------------------------------------------------------------------
ALTER TABLE venta
  MODIFY COLUMN precio_final decimal(12,2) NOT NULL COMMENT 'Total final (descuento e IVA incluidos)',
  MODIFY COLUMN forma_pago   varchar(50)   NOT NULL DEFAULT 'Transferencia',
  MODIFY COLUMN estado       varchar(20)   NOT NULL DEFAULT 'pendiente',
  ADD COLUMN vendedor_id    int           DEFAULT NULL,
  ADD COLUMN descuento      decimal(12,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN iva_porcentaje decimal(4,2)  NOT NULL DEFAULT 21.00,
  ADD COLUMN numero_factura varchar(30)   DEFAULT NULL,
  ADD COLUMN observaciones  text          DEFAULT NULL,
  ADD COLUMN fecha_creacion timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD UNIQUE KEY uq_venta_factura (numero_factura),
  ADD KEY idx_vendedor (vendedor_id),
  ADD CONSTRAINT chk_venta_descuento CHECK (descuento >= 0),
  ADD CONSTRAINT chk_venta_iva       CHECK (iva_porcentaje BETWEEN 0 AND 100),
  ADD CONSTRAINT fk_venta_vendedor   FOREIGN KEY (vendedor_id) REFERENCES empleado (id) ON DELETE SET NULL;

UPDATE venta SET numero_factura = CONCAT('F-', YEAR(fecha), '-', LPAD(id, 4, '0')) WHERE estado = 'completada';

-- Un pago siempre pertenece a una venta (antes admitía NULL y SET NULL)
ALTER TABLE pago DROP FOREIGN KEY pago_ibfk_1;
ALTER TABLE pago
  MODIFY COLUMN venta_id int NOT NULL,
  ADD CONSTRAINT fk_pago_venta FOREIGN KEY (venta_id) REFERENCES venta (id) ON DELETE RESTRICT;

ALTER TABLE financiacion
  ADD COLUMN estado varchar(15) NOT NULL DEFAULT 'solicitada',
  ADD CONSTRAINT chk_financiacion_estado CHECK (estado IN ('solicitada','aprobada','rechazada','finalizada'));
UPDATE financiacion SET estado = 'aprobada' WHERE fecha_aprobacion IS NOT NULL;

-- Accesorios vendidos en cada venta (antes solo estaban ligados al vehículo)
CREATE TABLE venta_accesorio (
  venta_id        int           NOT NULL,
  accesorio_id    int           NOT NULL,
  cantidad        int           NOT NULL DEFAULT 1,
  precio_unitario decimal(10,2) NOT NULL,
  PRIMARY KEY (venta_id, accesorio_id),
  KEY idx_accesorio (accesorio_id),
  CONSTRAINT fk_va_venta     FOREIGN KEY (venta_id)     REFERENCES venta (id)     ON DELETE CASCADE,
  CONSTRAINT fk_va_accesorio FOREIGN KEY (accesorio_id) REFERENCES accesorio (id) ON DELETE RESTRICT,
  CONSTRAINT chk_va_cantidad CHECK (cantidad > 0),
  CONSTRAINT chk_va_precio   CHECK (precio_unitario >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 6. PRUEBAS DE MANEJO
-- ----------------------------------------------------------------------------
ALTER TABLE prueba_manejo
  ADD COLUMN estado      varchar(15) NOT NULL DEFAULT 'pendiente',
  ADD COLUMN empleado_id int         DEFAULT NULL,
  ADD CONSTRAINT chk_prueba_estado CHECK (estado IN ('pendiente','confirmada','realizada','cancelada')),
  ADD CONSTRAINT fk_prueba_empleado FOREIGN KEY (empleado_id) REFERENCES empleado (id) ON DELETE SET NULL;

UPDATE prueba_manejo SET estado = IF(fecha < CURDATE(), 'realizada', 'pendiente');

-- Impide dos pruebas activas del mismo coche en el mismo día y hora
ALTER TABLE prueba_manejo
  ADD COLUMN slot_activo varchar(40)
      GENERATED ALWAYS AS (IF(estado = 'cancelada', NULL, CONCAT(vehiculo_id, '|', fecha, '|', hora))) VIRTUAL,
  ADD UNIQUE KEY uq_prueba_slot (slot_activo);

-- ----------------------------------------------------------------------------
-- 7. CAPTACIÓN: contacto, reserva, favoritos, tasación
-- ----------------------------------------------------------------------------
CREATE TABLE contacto (
  id             int          NOT NULL AUTO_INCREMENT,
  cliente_id     int          DEFAULT NULL,
  vehiculo_id    int          DEFAULT NULL,
  empleado_id    int          DEFAULT NULL,
  nombre         varchar(255) NOT NULL,
  email          varchar(255) NOT NULL,
  telefono       varchar(20)  DEFAULT NULL,
  tipo           varchar(20)  NOT NULL DEFAULT 'informacion',
  mensaje        text         NOT NULL,
  estado         varchar(15)  NOT NULL DEFAULT 'nuevo',
  fecha_creacion timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_estado_fecha (estado, fecha_creacion),
  KEY idx_cliente (cliente_id),
  KEY idx_vehiculo (vehiculo_id),
  CONSTRAINT fk_contacto_cliente  FOREIGN KEY (cliente_id)  REFERENCES cliente (id)  ON DELETE SET NULL,
  CONSTRAINT fk_contacto_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculo (id) ON DELETE SET NULL,
  CONSTRAINT fk_contacto_empleado FOREIGN KEY (empleado_id) REFERENCES empleado (id) ON DELETE SET NULL,
  CONSTRAINT chk_contacto_tipo   CHECK (tipo   IN ('informacion','tasacion','financiacion','otro')),
  CONSTRAINT chk_contacto_estado CHECK (estado IN ('nuevo','en_proceso','cerrado'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reserva (
  id               int           NOT NULL AUTO_INCREMENT,
  vehiculo_id      int           NOT NULL,
  cliente_id       int           NOT NULL,
  fecha_reserva    datetime      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_expiracion datetime      NOT NULL,
  importe_senal    decimal(10,2) NOT NULL DEFAULT 0.00,
  estado           varchar(15)   NOT NULL DEFAULT 'activa',
  observaciones    text          DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_vehiculo (vehiculo_id),
  KEY idx_cliente (cliente_id),
  CONSTRAINT fk_reserva_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculo (id) ON DELETE RESTRICT,
  CONSTRAINT fk_reserva_cliente  FOREIGN KEY (cliente_id)  REFERENCES cliente (id)  ON DELETE RESTRICT,
  CONSTRAINT chk_reserva_estado  CHECK (estado IN ('activa','convertida','expirada','cancelada')),
  CONSTRAINT chk_reserva_senal   CHECK (importe_senal >= 0),
  -- Solo puede haber UNA reserva activa por vehículo
  activa_vehiculo_id int GENERATED ALWAYS AS (IF(estado = 'activa', vehiculo_id, NULL)) VIRTUAL,
  UNIQUE KEY uq_reserva_activa (activa_vehiculo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE favorito (
  cliente_id  int       NOT NULL,
  vehiculo_id int       NOT NULL,
  fecha       timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (cliente_id, vehiculo_id),
  KEY idx_vehiculo (vehiculo_id),
  CONSTRAINT fk_fav_cliente  FOREIGN KEY (cliente_id)  REFERENCES cliente (id)  ON DELETE CASCADE,
  CONSTRAINT fk_fav_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculo (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tasación: coche que el cliente entrega a cambio
CREATE TABLE tasacion (
  id                   int           NOT NULL AUTO_INCREMENT,
  cliente_id           int           NOT NULL,
  venta_id             int           DEFAULT NULL,
  marca                varchar(100)  NOT NULL,
  modelo               varchar(100)  NOT NULL,
  anio                 int           NOT NULL,
  kilometraje          int           NOT NULL DEFAULT 0,
  matricula            varchar(15)   DEFAULT NULL,
  estado_conservacion  varchar(15)   NOT NULL DEFAULT 'bueno',
  valor_estimado       decimal(12,2) DEFAULT NULL,
  estado               varchar(15)   NOT NULL DEFAULT 'solicitada',
  observaciones        text          DEFAULT NULL,
  fecha                timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_cliente (cliente_id),
  KEY idx_venta (venta_id),
  CONSTRAINT fk_tasacion_cliente FOREIGN KEY (cliente_id) REFERENCES cliente (id) ON DELETE RESTRICT,
  CONSTRAINT fk_tasacion_venta   FOREIGN KEY (venta_id)   REFERENCES venta (id)   ON DELETE SET NULL,
  CONSTRAINT chk_tasacion_anio   CHECK (anio BETWEEN 1900 AND 2100),
  CONSTRAINT chk_tasacion_km     CHECK (kilometraje >= 0),
  CONSTRAINT chk_tasacion_cons   CHECK (estado_conservacion IN ('excelente','bueno','regular','malo')),
  CONSTRAINT chk_tasacion_estado CHECK (estado IN ('solicitada','valorada','aceptada','rechazada')),
  CONSTRAINT chk_tasacion_valor  CHECK (valor_estimado IS NULL OR valor_estimado >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 8. POSTVENTA: mantenimiento / taller
--    (eliminar.php ya consultaba esta tabla, pero no existía)
-- ----------------------------------------------------------------------------
CREATE TABLE mantenimiento (
  id          int           NOT NULL AUTO_INCREMENT,
  vehiculo_id int           NOT NULL,
  empleado_id int           DEFAULT NULL,
  tipo        varchar(20)   NOT NULL DEFAULT 'revision',
  fecha       date          NOT NULL,
  descripcion text          DEFAULT NULL,
  coste       decimal(10,2) NOT NULL DEFAULT 0.00,
  estado      varchar(15)   NOT NULL DEFAULT 'programado',
  PRIMARY KEY (id),
  KEY idx_vehiculo (vehiculo_id),
  KEY idx_fecha (fecha),
  CONSTRAINT fk_mant_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculo (id) ON DELETE RESTRICT,
  CONSTRAINT fk_mant_empleado FOREIGN KEY (empleado_id) REFERENCES empleado (id) ON DELETE SET NULL,
  CONSTRAINT chk_mant_tipo   CHECK (tipo   IN ('revision','reparacion','itv','preparacion')),
  CONSTRAINT chk_mant_estado CHECK (estado IN ('programado','en_curso','completado')),
  CONSTRAINT chk_mant_coste  CHECK (coste >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 9. SEGURIDAD: registro de intentos de login (limitación de fuerza bruta)
-- ----------------------------------------------------------------------------
CREATE TABLE intento_login (
  id      int         NOT NULL AUTO_INCREMENT,
  ambito  varchar(10) NOT NULL,
  usuario varchar(50) NOT NULL,
  ip      varchar(45) NOT NULL,
  fecha   timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_usuario_fecha (ambito, usuario, fecha),
  KEY idx_ip_fecha (ambito, ip, fecha),
  CONSTRAINT chk_intento_ambito CHECK (ambito IN ('admin','cliente'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- 10. VISTAS
-- ----------------------------------------------------------------------------
CREATE OR REPLACE SQL SECURITY INVOKER VIEW v_vehiculo_catalogo AS
SELECT v.id, v.marca_id, v.modelo_id, m.nombre AS marca, mo.nombre AS modelo, mo.tipo,
       v.anio, v.color, v.precio, v.estado, v.disponibilidad, v.kilometraje, v.combustible,
       v.transmision, v.potencia_cv, v.puertas, v.plazas, v.etiqueta_dgt, v.matricula,
       v.propietarios_anteriores, v.garantia_meses, v.descripcion, v.destacado,
       v.fecha_ingreso, v.imagen
  FROM vehiculo v
  JOIN marca  m  ON m.id  = v.marca_id
  JOIN modelo mo ON mo.id = v.modelo_id
 WHERE v.disponibilidad <> 'vendido';

CREATE OR REPLACE SQL SECURITY INVOKER VIEW v_venta_resumen AS
SELECT s.id AS venta_id, s.cliente_id, s.vehiculo_id, s.vendedor_id, s.fecha, s.estado,
       s.precio_final,
       COALESCE(SUM(p.monto), 0)                  AS total_pagado,
       s.precio_final - COALESCE(SUM(p.monto), 0) AS pendiente_pago
  FROM venta s
  LEFT JOIN pago p ON p.venta_id = s.id
 GROUP BY s.id;

INSERT INTO schema_version (version) VALUES ('2.0.0');
