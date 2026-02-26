-- Creamos la base de datos AVLA
CREATE DATABASE IF NOT EXISTS AVLA
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;

USE AVLA;

-- Creamos un usuario para las conexiones futuras
CREATE USER IF NOT EXISTS 'AVLA'@'localhost' IDENTIFIED BY 'AVLA*123$';

GRANT ALL PRIVILEGES ON AVLA.* TO 'AVLA'@'localhost';
FLUSH PRIVILEGES;

-- Tablas principales
CREATE TABLE marca(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    pais_origen VARCHAR(100) NOT NULL,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE modelo(
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) CHECK (tipo IN ('sedan', 'SUV', 'pickup', 'berlina', 'furgoneta', 'coupé', 'descapotable', 'ranchera')),
    INDEX idx_marca (marca_id),
    INDEX idx_nombre (nombre),
    FOREIGN KEY (marca_id) REFERENCES marca(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehiculo(
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca_id INT NOT NULL,
    modelo_id INT NOT NULL,
    año INT NOT NULL CHECK (año >= 1900 AND año <= 2100),
    vin VARCHAR(17) UNIQUE NOT NULL,
    color VARCHAR(50) NOT NULL,
    precio DECIMAL(12,2) NOT NULL CHECK (precio >= 0),
    estado VARCHAR(10) CHECK (estado IN ('nuevo', 'usado')),
    kilometraje INT DEFAULT 0 CHECK (kilometraje >= 0),
    fecha_ingreso DATE NOT NULL DEFAULT CURRENT_DATE,
    INDEX idx_marca (marca_id),
    INDEX idx_modelo (modelo_id),
    INDEX idx_vin (vin),
    FOREIGN KEY (marca_id) REFERENCES marca(id) ON DELETE RESTRICT,
    FOREIGN KEY (modelo_id) REFERENCES modelo(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cliente(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,  -- Para hashes de contraseñas
    DNI_NIE VARCHAR(9) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefono VARCHAR(20) NOT NULL,  -- Cambiado a VARCHAR
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dni (DNI_NIE),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tablas de transacciones
CREATE TABLE proveedor(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    contacto VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE venta(
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehiculo_id INT NOT NULL,
    cliente_id INT NOT NULL,
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    precio_final DECIMAL(12,2) NOT NULL CHECK (precio_final >= 0),
    forma_pago VARCHAR(50) CHECK (forma_pago IN ('Efectivo','Transferencia','Financiación')),
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'completada', 'cancelada')),
    INDEX idx_vehiculo (vehiculo_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha (fecha),
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id) ON DELETE RESTRICT,
    FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE compra(
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehiculo_id INT NOT NULL,
    proveedor_id INT NOT NULL,
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    precio_compra DECIMAL(12,2) NOT NULL CHECK (precio_compra >= 0),
    documento VARCHAR(100) UNIQUE NOT NULL,
    INDEX idx_vehiculo (vehiculo_id),
    INDEX idx_proveedor (proveedor_id),
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id) ON DELETE RESTRICT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedor(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE financiacion(
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT NOT NULL,
    entidad_bancaria VARCHAR(255) NOT NULL,
    monto DECIMAL(12,2) NOT NULL CHECK (monto >= 0),
    cuotas INT NOT NULL CHECK (cuotas > 0),
    tasa_interes DECIMAL(5,2) NOT NULL CHECK (tasa_interes >= 0),
    fecha_aprobacion DATE,
    INDEX idx_venta (venta_id),
    FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pago(
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT,
    monto DECIMAL(12,2) NOT NULL CHECK (monto >= 0),
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    metodo_pago VARCHAR(50) CHECK (metodo_pago IN ('Efectivo', 'Transferencia', 'Tarjeta')),
    referencia VARCHAR(100),
    INDEX idx_venta (venta_id),
    INDEX idx_fecha (fecha),
    FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tablas de gestión operativa
CREATE TABLE accesorio(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
    stock INT NOT NULL CHECK (stock >= 0),
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehiculo_accesorio(
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehiculo_id INT NOT NULL,
    accesorio_id INT NOT NULL,
    precio_instalacion DECIMAL(10,2) CHECK (precio_instalacion >= 0),
    UNIQUE KEY unique_vehiculo_accesorio (vehiculo_id, accesorio_id),
    INDEX idx_vehiculo (vehiculo_id),
    INDEX idx_accesorio (accesorio_id),
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id) ON DELETE CASCADE,
    FOREIGN KEY (accesorio_id) REFERENCES accesorio(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE prueba_manejo(
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehiculo_id INT NOT NULL,
    cliente_id INT NOT NULL,
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    hora TIME NOT NULL,  -- Agregado campo hora
    observaciones TEXT,
    INDEX idx_vehiculo (vehiculo_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha (fecha),
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id) ON DELETE RESTRICT,
    FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--Algunos inserts:
-- 2.1 Marcas
INSERT INTO marca (id, nombre, pais_origen) VALUES 
(1, 'Toyota', 'Japón'),
(2, 'Ford', 'Estados Unidos'),
(3, 'BMW', 'Alemania'),
(4, 'Renault', 'Francia');

-- 2.2 Clientes (CON campo 'usuario')
INSERT INTO cliente (id, nombre, contrasena, DNI_NIE, email, telefono, usuario) VALUES 
(1, 'Juan Pérez', 'password123', '12345678A', 'juan.perez@email.com', '600111222', 'jperez'),
(2, 'María García', 'password123', '87654321B', 'maria.garcia@email.com', '600333444', 'mgarcia'),
(3, 'Carlos López', 'password123', '11223344C', 'carlos.lopez@email.com', '600555666', 'clopez');

-- 2.3 Proveedores
INSERT INTO proveedor (id, nombre, contacto, telefono, email, direccion) VALUES 
(1, 'Importaciones Globales SL', 'Ana Torres', '910000001', 'contacto@importglobales.com', 'Polígono Industrial Norte, Calle A'),
(2, 'Motor Directo SA', 'Luis Ruiz', '930000002', 'ventas@motordirecto.com', 'Carretera Nacional II, Km 20');

-- 2.4 Accesorios
INSERT INTO accesorio (id, nombre, precio, stock) VALUES 
(1, 'Alarma con GPS', 150.00, 50),
(2, 'Kit de Limpieza Premium', 45.50, 100),
(3, 'Cubrematos Personalizados', 80.00, 30),
(4, 'Cámara de Marcha Atrás', 120.00, 25);

-- 2.5 Modelos
INSERT INTO modelo (id, marca_id, nombre, tipo) VALUES 
(1, 1, 'Corolla', 'sedan'),
(2, 1, 'RAV4', 'SUV'),
(3, 2, 'F-150', 'pickup'),
(4, 3, 'Serie 3', 'berlina'),
(5, 4, 'Clio', 'ranchera');

-- 2.6 Vehículos
INSERT INTO vehiculo (id, marca_id, modelo_id, año, vin, color, precio, estado, kilometraje, fecha_ingreso) VALUES 
(1, 1, 1, 2023, '1HGBH41JXMN109186', 'Blanco', 25000.00, 'nuevo', 0, '2023-10-01'),
(2, 1, 2, 2022, '2HGBH41JXMN109187', 'Negro', 35000.00, 'usado', 15000, '2023-10-05'),
(3, 2, 3, 2023, '3HGBH41JXMN109188', 'Rojo', 45000.00, 'nuevo', 0, '2023-11-01'),
(4, 3, 4, 2021, '4HGBH41JXMN109189', 'Azul', 40000.00, 'usado', 30000, '2023-11-10'),
(5, 4, 5, 2024, '5HGBH41JXMN109190', 'Gris', 18000.00, 'nuevo', 0, '2024-01-15');

-- 2.7 Compras
INSERT INTO compra (id, vehiculo_id, proveedor_id, fecha, precio_compra, documento) VALUES 
(1, 1, 1, '2023-09-25', 20000.00, 'FAC-2023-001'),
(2, 2, 2, '2023-10-01', 28000.00, 'FAC-2023-002'),
(3, 3, 1, '2023-10-20', 38000.00, 'FAC-2023-003');

-- 2.8 Ventas
INSERT INTO venta (id, vehiculo_id, cliente_id, fecha, precio_final, forma_pago, estado) VALUES 
(1, 1, 1, '2023-10-10', 25000.00, 'Financiación', 'completada'),
(2, 2, 2, '2023-10-15', 35000.00, 'Transferencia', 'completada'),
(3, 3, 3, '2023-11-05', 45000.00, 'Efectivo', 'pendiente');

-- 2.9 Financiación
INSERT INTO financiacion (id, venta_id, entidad_bancaria, monto, cuotas, tasa_interes, fecha_aprobacion) VALUES 
(1, 1, 'Banco Santander', 20000.00, 48, 4.50, '2023-10-09');

-- 2.10 Pagos
INSERT INTO pago (id, venta_id, monto, fecha, metodo_pago, referencia) VALUES 
(1, 1, 5000.00, '2023-10-10', 'Transferencia', 'REF-001-Entrada'),
(2, 2, 35000.00, '2023-10-15', 'Transferencia', 'REF-002-Total'),
(3, 3, 1000.00, '2023-11-05', 'Tarjeta', 'REF-003-Reserva');

-- 2.11 Vehiculo_Accesorio
INSERT INTO vehiculo_accesorio (id, vehiculo_id, accesorio_id, precio_instalacion) VALUES 
(1, 1, 1, 50.00),
(2, 1, 3, 20.00),
(3, 4, 4, 60.00);

-- 2.12 Prueba de Manejo
INSERT INTO prueba_manejo (id, vehiculo_id, cliente_id, fecha, hora, observaciones) VALUES 
(1, 5, 1, '2024-01-10', '10:30:00', 'Cliente interesado, revisar financiación'),
(2, 4, 2, '2024-01-12', '16:00:00', 'Prueba satisfactoria, espera aprobación de crédito'),
(3, 2, 3, '2024-01-14', '11:15:00', 'Cliente prefiere color blanco, no disponible');


CREATE TABLE administrador (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(100) NOT NULL, 
    email VARCHAR(100),
    nombre_completo VARCHAR(100),
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuarios de prueba
INSERT INTO administrador (usuario, contrasena, email, nombre_completo) VALUES 
('admin', 'admin123', 'admin@avla.com', 'Administrador Principal'),
('baac', 'baac123', 'baac@avla.com', 'Bryan - Demo');