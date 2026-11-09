
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `accesorio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accesorio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_nombre` (`nombre`),
  CONSTRAINT `accesorio_chk_1` CHECK ((`precio` >= 0)),
  CONSTRAINT `accesorio_chk_2` CHECK ((`stock` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `accesorio` WRITE;
/*!40000 ALTER TABLE `accesorio` DISABLE KEYS */;
INSERT INTO `accesorio` VALUES (1,'Alarma con GPS',150.00,50),(2,'Kit de Limpieza Premium',45.50,100),(3,'Cubrematos Personalizados',80.00,30),(4,'Cámara de Marcha Atrás',120.00,25);
/*!40000 ALTER TABLE `accesorio` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `administrador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nombre_completo` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `rol` varchar(20) NOT NULL DEFAULT 'gestor',
  `empleado_id` int DEFAULT NULL,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `fk_admin_empleado` (`empleado_id`),
  CONSTRAINT `fk_admin_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_admin_rol` CHECK ((`rol` in (_utf8mb4'superadmin',_utf8mb4'gestor',_utf8mb4'vendedor')))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `administrador` WRITE;
/*!40000 ALTER TABLE `administrador` DISABLE KEYS */;
INSERT INTO `administrador` VALUES (1,'admin','$2y$10$snLfpWX0xXoCI0yEDYlvdO4feMlO28rWhb6jPFqNXtGgK5LWp6FMS','admin@avla.com','Administrador Principal',1,'superadmin',NULL,NULL),(2,'baac','$2y$10$Qi081aDCfFXUgnk/vu2gBebCRLHtX1ZCh6LQzpsx6yyFeQmEDZ712','baac@avla.com','Bryan - Demo',1,'gestor',NULL,NULL);
/*!40000 ALTER TABLE `administrador` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `DNI_NIE` varchar(9) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `acepta_marketing` tinyint(1) NOT NULL DEFAULT '0',
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `DNI_NIE` (`DNI_NIE`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `idx_dni` (`DNI_NIE`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (1,'Juan Pérez','$2y$10$BXo.epwG0VldsPjHOZSNQeVXECVXFcmzPIJtVSmWWbTtkrvwTialW','jperez','12345678A','juan.perez@email.com','600111222','2026-03-10 09:57:40',NULL,NULL,NULL,1,0,NULL),(2,'María García','$2y$10$P9YWN7k92e0fCFlGiBhx1.n8scWzAb15oUXImMp22SE0iX2guPjd.','mgarcia','87654321B','maria.garcia@email.com','600333444','2026-03-10 09:57:40',NULL,NULL,NULL,1,0,NULL),(3,'Carlos López','$2y$10$9jJnvJeD5As.OITofpe9luQkgucH/rqyK6VWU4JocSrJDr5K83kPm','clopez','11223344C','carlos.lopez@email.com','600555666','2026-03-10 09:57:40',NULL,NULL,NULL,1,0,NULL),(4,'bryan avila castro','$2y$10$7z1Q4P85gQabwA98heCcDeY1HvzCGs20txY.94PSupubtQLQD5z3m','baac','Z4569834D','infor@baac.com','623257946','2026-03-12 09:46:00',NULL,NULL,NULL,1,0,NULL);
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int NOT NULL,
  `proveedor_id` int NOT NULL,
  `fecha` date NOT NULL,
  `precio_compra` decimal(12,2) NOT NULL,
  `documento` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documento` (`documento`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `idx_proveedor` (`proveedor_id`),
  CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `compra_chk_1` CHECK ((`precio_compra` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `compra` WRITE;
/*!40000 ALTER TABLE `compra` DISABLE KEYS */;
INSERT INTO `compra` VALUES (1,1,1,'2023-09-25',20000.00,'FAC-2023-001'),(2,2,2,'2023-10-01',28000.00,'FAC-2023-002'),(3,3,1,'2023-10-20',38000.00,'FAC-2023-003');
/*!40000 ALTER TABLE `compra` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `contacto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `vehiculo_id` int DEFAULT NULL,
  `empleado_id` int DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'informacion',
  `mensaje` text NOT NULL,
  `estado` varchar(15) NOT NULL DEFAULT 'nuevo',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_estado_fecha` (`estado`,`fecha_creacion`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `fk_contacto_empleado` (`empleado_id`),
  CONSTRAINT `fk_contacto_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contacto_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contacto_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_contacto_estado` CHECK ((`estado` in (_utf8mb4'nuevo',_utf8mb4'en_proceso',_utf8mb4'cerrado'))),
  CONSTRAINT `chk_contacto_tipo` CHECK ((`tipo` in (_utf8mb4'informacion',_utf8mb4'tasacion',_utf8mb4'financiacion',_utf8mb4'otro')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `contacto` WRITE;
/*!40000 ALTER TABLE `contacto` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacto` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleado` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `cargo` varchar(20) NOT NULL DEFAULT 'vendedor',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_alta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_empleado_email` (`email`),
  CONSTRAINT `chk_empleado_cargo` CHECK ((`cargo` in (_utf8mb4'vendedor',_utf8mb4'gerente',_utf8mb4'mecanico',_utf8mb4'administrativo')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `empleado` WRITE;
/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
/*!40000 ALTER TABLE `empleado` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `favorito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorito` (
  `cliente_id` int NOT NULL,
  `vehiculo_id` int NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cliente_id`,`vehiculo_id`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  CONSTRAINT `fk_fav_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fav_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `favorito` WRITE;
/*!40000 ALTER TABLE `favorito` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorito` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `financiacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financiacion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `entidad_bancaria` varchar(255) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `cuotas` int NOT NULL,
  `tasa_interes` decimal(5,2) NOT NULL,
  `fecha_aprobacion` date DEFAULT NULL,
  `estado` varchar(15) NOT NULL DEFAULT 'solicitada',
  PRIMARY KEY (`id`),
  KEY `idx_venta` (`venta_id`),
  CONSTRAINT `financiacion_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `venta` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_financiacion_estado` CHECK ((`estado` in (_utf8mb4'solicitada',_utf8mb4'aprobada',_utf8mb4'rechazada',_utf8mb4'finalizada'))),
  CONSTRAINT `financiacion_chk_1` CHECK ((`monto` >= 0)),
  CONSTRAINT `financiacion_chk_2` CHECK ((`cuotas` > 0)),
  CONSTRAINT `financiacion_chk_3` CHECK ((`tasa_interes` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `financiacion` WRITE;
/*!40000 ALTER TABLE `financiacion` DISABLE KEYS */;
INSERT INTO `financiacion` VALUES (1,1,'Banco Santander',20000.00,48,4.50,'2023-10-09','aprobada');
/*!40000 ALTER TABLE `financiacion` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `intento_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `intento_login` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ambito` varchar(10) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_fecha` (`ambito`,`usuario`,`fecha`),
  KEY `idx_ip_fecha` (`ambito`,`ip`,`fecha`),
  CONSTRAINT `chk_intento_ambito` CHECK ((`ambito` in (_utf8mb4'admin',_utf8mb4'cliente')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `intento_login` WRITE;
/*!40000 ALTER TABLE `intento_login` DISABLE KEYS */;
/*!40000 ALTER TABLE `intento_login` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `mantenimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mantenimiento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int NOT NULL,
  `empleado_id` int DEFAULT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'revision',
  `fecha` date NOT NULL,
  `descripcion` text,
  `coste` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(15) NOT NULL DEFAULT 'programado',
  PRIMARY KEY (`id`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `fk_mant_empleado` (`empleado_id`),
  CONSTRAINT `fk_mant_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mant_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_mant_coste` CHECK ((`coste` >= 0)),
  CONSTRAINT `chk_mant_estado` CHECK ((`estado` in (_utf8mb4'programado',_utf8mb4'en_curso',_utf8mb4'completado'))),
  CONSTRAINT `chk_mant_tipo` CHECK ((`tipo` in (_utf8mb4'revision',_utf8mb4'reparacion',_utf8mb4'itv',_utf8mb4'preparacion')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `mantenimiento` WRITE;
/*!40000 ALTER TABLE `mantenimiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `mantenimiento` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `marca`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marca` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `pais_origen` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `marca` WRITE;
/*!40000 ALTER TABLE `marca` DISABLE KEYS */;
INSERT INTO `marca` VALUES (1,'Toyota','Japón'),(2,'Ford','Estados Unidos'),(3,'BMW','Alemania'),(4,'Renault','Francia');
/*!40000 ALTER TABLE `marca` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `modelo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modelo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marca_id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_marca` (`marca_id`),
  KEY `idx_nombre` (`nombre`),
  CONSTRAINT `modelo_ibfk_1` FOREIGN KEY (`marca_id`) REFERENCES `marca` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modelo_chk_1` CHECK ((`tipo` in (_utf8mb4'sedan',_utf8mb4'SUV',_utf8mb4'pickup',_utf8mb4'berlina',_utf8mb4'furgoneta',_utf8mb4'coupé',_utf8mb4'descapotable',_utf8mb4'ranchera')))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `modelo` WRITE;
/*!40000 ALTER TABLE `modelo` DISABLE KEYS */;
INSERT INTO `modelo` VALUES (1,1,'Corolla','sedan'),(2,1,'RAV4','SUV'),(3,2,'F-150','pickup'),(4,3,'Serie 3','berlina'),(5,4,'Clio','ranchera');
/*!40000 ALTER TABLE `modelo` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venta_id` int NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_venta` (`venta_id`),
  KEY `idx_fecha` (`fecha`),
  CONSTRAINT `fk_pago_venta` FOREIGN KEY (`venta_id`) REFERENCES `venta` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pago_chk_1` CHECK ((`monto` >= 0)),
  CONSTRAINT `pago_chk_2` CHECK ((`metodo_pago` in (_utf8mb4'Efectivo',_utf8mb4'Transferencia',_utf8mb4'Tarjeta')))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pago` WRITE;
/*!40000 ALTER TABLE `pago` DISABLE KEYS */;
INSERT INTO `pago` VALUES (1,1,5000.00,'2023-10-10','Transferencia','REF-001-Entrada'),(2,2,35000.00,'2023-10-15','Transferencia','REF-002-Total'),(3,3,1000.00,'2023-11-05','Tarjeta','REF-003-Reserva');
/*!40000 ALTER TABLE `pago` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `contacto` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `proveedor` WRITE;
/*!40000 ALTER TABLE `proveedor` DISABLE KEYS */;
INSERT INTO `proveedor` VALUES (1,'Importaciones Globales SL','Ana Torres','910000001','contacto@importglobales.com','Polígono Industrial Norte, Calle A'),(2,'Motor Directo SA','Luis Ruiz','930000002','ventas@motordirecto.com','Carretera Nacional II, Km 20');
/*!40000 ALTER TABLE `proveedor` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `prueba_manejo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prueba_manejo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `observaciones` text,
  `estado` varchar(15) NOT NULL DEFAULT 'pendiente',
  `empleado_id` int DEFAULT NULL,
  `slot_activo` varchar(40) GENERATED ALWAYS AS (if((`estado` = _utf8mb4'cancelada'),NULL,concat(`vehiculo_id`,_utf8mb4'|',`fecha`,_utf8mb4'|',`hora`))) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_prueba_slot` (`slot_activo`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `fk_prueba_empleado` (`empleado_id`),
  CONSTRAINT `fk_prueba_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prueba_manejo_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `prueba_manejo_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_prueba_estado` CHECK ((`estado` in (_utf8mb4'pendiente',_utf8mb4'confirmada',_utf8mb4'realizada',_utf8mb4'cancelada')))
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `prueba_manejo` WRITE;
/*!40000 ALTER TABLE `prueba_manejo` DISABLE KEYS */;
INSERT INTO `prueba_manejo` (`id`, `vehiculo_id`, `cliente_id`, `fecha`, `hora`, `observaciones`, `estado`, `empleado_id`) VALUES (1,5,1,'2024-01-10','10:30:00','Cliente interesado, revisar financiación','realizada',NULL),(2,4,2,'2024-01-12','16:00:00','Prueba satisfactoria, espera aprobación de crédito','realizada',NULL),(3,2,3,'2024-01-14','11:15:00','Cliente prefiere color blanco, no disponible','realizada',NULL),(6,5,4,'2026-03-18','15:00:00','','realizada',NULL);
/*!40000 ALTER TABLE `prueba_manejo` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `reserva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `fecha_reserva` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_expiracion` datetime NOT NULL,
  `importe_senal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(15) NOT NULL DEFAULT 'activa',
  `observaciones` text,
  `activa_vehiculo_id` int GENERATED ALWAYS AS (if((`estado` = _utf8mb4'activa'),`vehiculo_id`,NULL)) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reserva_activa` (`activa_vehiculo_id`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `idx_cliente` (`cliente_id`),
  CONSTRAINT `fk_reserva_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_reserva_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_reserva_estado` CHECK ((`estado` in (_utf8mb4'activa',_utf8mb4'convertida',_utf8mb4'expirada',_utf8mb4'cancelada'))),
  CONSTRAINT `chk_reserva_senal` CHECK ((`importe_senal` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `reserva` WRITE;
/*!40000 ALTER TABLE `reserva` DISABLE KEYS */;
/*!40000 ALTER TABLE `reserva` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `schema_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schema_version` (
  `version` varchar(20) NOT NULL,
  `aplicada` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `schema_version` WRITE;
/*!40000 ALTER TABLE `schema_version` DISABLE KEYS */;
INSERT INTO `schema_version` VALUES ('2.0.0','2026-11-09 08:53:45');
/*!40000 ALTER TABLE `schema_version` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tasacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasacion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `venta_id` int DEFAULT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `anio` int NOT NULL,
  `kilometraje` int NOT NULL DEFAULT '0',
  `matricula` varchar(15) DEFAULT NULL,
  `estado_conservacion` varchar(15) NOT NULL DEFAULT 'bueno',
  `valor_estimado` decimal(12,2) DEFAULT NULL,
  `estado` varchar(15) NOT NULL DEFAULT 'solicitada',
  `observaciones` text,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_venta` (`venta_id`),
  CONSTRAINT `fk_tasacion_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tasacion_venta` FOREIGN KEY (`venta_id`) REFERENCES `venta` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_tasacion_anio` CHECK ((`anio` between 1900 and 2100)),
  CONSTRAINT `chk_tasacion_cons` CHECK ((`estado_conservacion` in (_utf8mb4'excelente',_utf8mb4'bueno',_utf8mb4'regular',_utf8mb4'malo'))),
  CONSTRAINT `chk_tasacion_estado` CHECK ((`estado` in (_utf8mb4'solicitada',_utf8mb4'valorada',_utf8mb4'aceptada',_utf8mb4'rechazada'))),
  CONSTRAINT `chk_tasacion_km` CHECK ((`kilometraje` >= 0)),
  CONSTRAINT `chk_tasacion_valor` CHECK (((`valor_estimado` is null) or (`valor_estimado` >= 0)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tasacion` WRITE;
/*!40000 ALTER TABLE `tasacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasacion` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `v_vehiculo_catalogo`;
/*!50001 DROP VIEW IF EXISTS `v_vehiculo_catalogo`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_vehiculo_catalogo` AS SELECT 
 1 AS `id`,
 1 AS `marca_id`,
 1 AS `modelo_id`,
 1 AS `marca`,
 1 AS `modelo`,
 1 AS `tipo`,
 1 AS `anio`,
 1 AS `color`,
 1 AS `precio`,
 1 AS `estado`,
 1 AS `disponibilidad`,
 1 AS `kilometraje`,
 1 AS `combustible`,
 1 AS `transmision`,
 1 AS `potencia_cv`,
 1 AS `puertas`,
 1 AS `plazas`,
 1 AS `etiqueta_dgt`,
 1 AS `matricula`,
 1 AS `propietarios_anteriores`,
 1 AS `garantia_meses`,
 1 AS `descripcion`,
 1 AS `destacado`,
 1 AS `fecha_ingreso`,
 1 AS `imagen`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_venta_resumen`;
/*!50001 DROP VIEW IF EXISTS `v_venta_resumen`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_venta_resumen` AS SELECT 
 1 AS `venta_id`,
 1 AS `cliente_id`,
 1 AS `vehiculo_id`,
 1 AS `vendedor_id`,
 1 AS `fecha`,
 1 AS `estado`,
 1 AS `precio_final`,
 1 AS `total_pagado`,
 1 AS `pendiente_pago`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vehiculo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehiculo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marca_id` int NOT NULL,
  `modelo_id` int NOT NULL,
  `anio` int NOT NULL,
  `vin` varchar(17) NOT NULL,
  `color` varchar(50) NOT NULL,
  `precio` decimal(12,2) NOT NULL,
  `estado` varchar(10) NOT NULL DEFAULT 'nuevo',
  `kilometraje` int DEFAULT '0',
  `fecha_ingreso` date NOT NULL,
  `imagen` varchar(500) DEFAULT NULL,
  `matricula` varchar(15) DEFAULT NULL,
  `combustible` varchar(20) DEFAULT NULL,
  `transmision` varchar(15) DEFAULT NULL,
  `potencia_cv` smallint unsigned DEFAULT NULL,
  `puertas` tinyint unsigned DEFAULT NULL,
  `plazas` tinyint unsigned DEFAULT NULL,
  `etiqueta_dgt` varchar(5) DEFAULT NULL,
  `propietarios_anteriores` tinyint unsigned NOT NULL DEFAULT '0',
  `garantia_meses` smallint unsigned NOT NULL DEFAULT '0',
  `descripcion` text,
  `disponibilidad` varchar(15) NOT NULL DEFAULT 'disponible' COMMENT 'Stock: disponible / reservado / vendido (independiente de estado nuevo/usado)',
  `destacado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `vin` (`vin`),
  UNIQUE KEY `uq_vehiculo_matricula` (`matricula`),
  KEY `idx_marca` (`marca_id`),
  KEY `idx_modelo` (`modelo_id`),
  KEY `idx_vin` (`vin`),
  KEY `idx_disponibilidad_precio` (`disponibilidad`,`precio`),
  CONSTRAINT `vehiculo_ibfk_1` FOREIGN KEY (`marca_id`) REFERENCES `marca` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `vehiculo_ibfk_2` FOREIGN KEY (`modelo_id`) REFERENCES `modelo` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_vehiculo_anio` CHECK ((`anio` between 1900 and 2100)),
  CONSTRAINT `chk_vehiculo_combustible` CHECK (((`combustible` is null) or (`combustible` in (_utf8mb4'gasolina',_utf8mb4'diésel',_utf8mb4'híbrido',_utf8mb4'híbrido enchufable',_utf8mb4'eléctrico',_utf8mb4'GLP',_utf8mb4'GNC')))),
  CONSTRAINT `chk_vehiculo_disponibil` CHECK ((`disponibilidad` in (_utf8mb4'disponible',_utf8mb4'reservado',_utf8mb4'vendido'))),
  CONSTRAINT `chk_vehiculo_etiqueta` CHECK (((`etiqueta_dgt` is null) or (`etiqueta_dgt` in (_utf8mb4'0',_utf8mb4'ECO',_utf8mb4'C',_utf8mb4'B',_utf8mb4'SIN')))),
  CONSTRAINT `chk_vehiculo_transmision` CHECK (((`transmision` is null) or (`transmision` in (_utf8mb4'manual',_utf8mb4'automático')))),
  CONSTRAINT `vehiculo_chk_2` CHECK ((`precio` >= 0)),
  CONSTRAINT `vehiculo_chk_3` CHECK ((`estado` in (_utf8mb4'nuevo',_utf8mb4'usado'))),
  CONSTRAINT `vehiculo_chk_4` CHECK ((`kilometraje` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehiculo` WRITE;
/*!40000 ALTER TABLE `vehiculo` DISABLE KEYS */;
INSERT INTO `vehiculo` VALUES (1,1,1,2023,'1HGBH41JXMN109186','Blanco',25000.00,'nuevo',0,'2023-10-01','imagenes/vehiculos/toyota_corolla.jpg',NULL,'híbrido','automático',140,5,5,'ECO',0,36,'Berlina compacta híbrida, 0 km, lista para entrega.','vendido',0),(2,1,2,2022,'2HGBH41JXMN109187','Negro',35000.00,'usado',15000,'2023-10-05','imagenes/vehiculos/toyota_rav4.jpg','1234KLM','híbrido','automático',218,5,5,'ECO',1,12,'SUV híbrido de un solo propietario con revisiones al día.','vendido',0),(3,2,3,2023,'3HGBH41JXMN109188','Rojo',45000.00,'nuevo',0,'2023-11-01','imagenes/vehiculos/ford_f150.jpg',NULL,'gasolina','automático',400,4,5,'C',0,24,'Pickup de gran tamaño, 0 km.','reservado',0),(4,3,4,2021,'4HGBH41JXMN109189','Azul',40000.00,'usado',30000,'2023-11-10','imagenes/vehiculos/bmw_serie3.jpg','5678NPR','diésel','automático',190,4,5,'C',1,12,'Berlina premium con historial de mantenimiento completo.','disponible',0),(5,4,5,2024,'5HGBH41JXMN109190','Gris',18000.00,'nuevo',0,'2024-01-15','imagenes/vehiculos/renault_clio.jpg',NULL,'gasolina','manual',91,5,5,'C',0,24,'Utilitario urbano, 0 km, bajo consumo.','disponible',0);
/*!40000 ALTER TABLE `vehiculo` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehiculo_accesorio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehiculo_accesorio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int NOT NULL,
  `accesorio_id` int NOT NULL,
  `precio_instalacion` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vehiculo_accesorio` (`vehiculo_id`,`accesorio_id`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `idx_accesorio` (`accesorio_id`),
  CONSTRAINT `vehiculo_accesorio_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehiculo_accesorio_ibfk_2` FOREIGN KEY (`accesorio_id`) REFERENCES `accesorio` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `vehiculo_accesorio_chk_1` CHECK ((`precio_instalacion` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehiculo_accesorio` WRITE;
/*!40000 ALTER TABLE `vehiculo_accesorio` DISABLE KEYS */;
INSERT INTO `vehiculo_accesorio` VALUES (1,1,1,50.00),(2,1,3,20.00),(3,4,4,60.00);
/*!40000 ALTER TABLE `vehiculo_accesorio` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vehiculo_imagen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehiculo_imagen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT '0',
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_vehiculo_orden` (`vehiculo_id`,`orden`),
  CONSTRAINT `fk_vimg_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vehiculo_imagen` WRITE;
/*!40000 ALTER TABLE `vehiculo_imagen` DISABLE KEYS */;
INSERT INTO `vehiculo_imagen` VALUES (1,1,'imagenes/vehiculos/toyota_corolla.jpg',0,1),(2,2,'imagenes/vehiculos/toyota_rav4.jpg',0,1),(3,3,'imagenes/vehiculos/ford_f150.jpg',0,1),(4,4,'imagenes/vehiculos/bmw_serie3.jpg',0,1),(5,5,'imagenes/vehiculos/renault_clio.jpg',0,1);
/*!40000 ALTER TABLE `vehiculo_imagen` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int NOT NULL,
  `cliente_id` int NOT NULL,
  `fecha` date NOT NULL,
  `precio_final` decimal(12,2) NOT NULL COMMENT 'Total final (descuento e IVA incluidos)',
  `forma_pago` varchar(50) NOT NULL DEFAULT 'Transferencia',
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `vendedor_id` int DEFAULT NULL,
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `iva_porcentaje` decimal(4,2) NOT NULL DEFAULT '21.00',
  `numero_factura` varchar(30) DEFAULT NULL,
  `observaciones` text,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venta_factura` (`numero_factura`),
  KEY `idx_vehiculo` (`vehiculo_id`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_vendedor` (`vendedor_id`),
  CONSTRAINT `fk_venta_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `empleado` (`id`) ON DELETE SET NULL,
  CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculo` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_venta_descuento` CHECK ((`descuento` >= 0)),
  CONSTRAINT `chk_venta_iva` CHECK ((`iva_porcentaje` between 0 and 100)),
  CONSTRAINT `venta_chk_1` CHECK ((`precio_final` >= 0)),
  CONSTRAINT `venta_chk_2` CHECK ((`forma_pago` in (_utf8mb4'Efectivo',_utf8mb4'Transferencia',_utf8mb4'Financiación'))),
  CONSTRAINT `venta_chk_3` CHECK ((`estado` in (_utf8mb4'pendiente',_utf8mb4'completada',_utf8mb4'cancelada')))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `venta` WRITE;
/*!40000 ALTER TABLE `venta` DISABLE KEYS */;
INSERT INTO `venta` VALUES (1,1,1,'2023-10-10',25000.00,'Financiación','completada',NULL,0.00,21.00,'F-2023-0001',NULL,'2026-11-09 08:53:45'),(2,2,2,'2023-10-15',35000.00,'Transferencia','completada',NULL,0.00,21.00,'F-2023-0002',NULL,'2026-11-09 08:53:45'),(3,3,3,'2023-11-05',45000.00,'Efectivo','pendiente',NULL,0.00,21.00,NULL,NULL,'2026-11-09 08:53:45');
/*!40000 ALTER TABLE `venta` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `venta_accesorio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `venta_accesorio` (
  `venta_id` int NOT NULL,
  `accesorio_id` int NOT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`venta_id`,`accesorio_id`),
  KEY `idx_accesorio` (`accesorio_id`),
  CONSTRAINT `fk_va_accesorio` FOREIGN KEY (`accesorio_id`) REFERENCES `accesorio` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_va_venta` FOREIGN KEY (`venta_id`) REFERENCES `venta` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_va_cantidad` CHECK ((`cantidad` > 0)),
  CONSTRAINT `chk_va_precio` CHECK ((`precio_unitario` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `venta_accesorio` WRITE;
/*!40000 ALTER TABLE `venta_accesorio` DISABLE KEYS */;
/*!40000 ALTER TABLE `venta_accesorio` ENABLE KEYS */;
UNLOCK TABLES;
/*!50001 DROP VIEW IF EXISTS `v_vehiculo_catalogo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_vehiculo_catalogo` AS select `v`.`id` AS `id`,`v`.`marca_id` AS `marca_id`,`v`.`modelo_id` AS `modelo_id`,`m`.`nombre` AS `marca`,`mo`.`nombre` AS `modelo`,`mo`.`tipo` AS `tipo`,`v`.`anio` AS `anio`,`v`.`color` AS `color`,`v`.`precio` AS `precio`,`v`.`estado` AS `estado`,`v`.`disponibilidad` AS `disponibilidad`,`v`.`kilometraje` AS `kilometraje`,`v`.`combustible` AS `combustible`,`v`.`transmision` AS `transmision`,`v`.`potencia_cv` AS `potencia_cv`,`v`.`puertas` AS `puertas`,`v`.`plazas` AS `plazas`,`v`.`etiqueta_dgt` AS `etiqueta_dgt`,`v`.`matricula` AS `matricula`,`v`.`propietarios_anteriores` AS `propietarios_anteriores`,`v`.`garantia_meses` AS `garantia_meses`,`v`.`descripcion` AS `descripcion`,`v`.`destacado` AS `destacado`,`v`.`fecha_ingreso` AS `fecha_ingreso`,`v`.`imagen` AS `imagen` from ((`vehiculo` `v` join `marca` `m` on((`m`.`id` = `v`.`marca_id`))) join `modelo` `mo` on((`mo`.`id` = `v`.`modelo_id`))) where (`v`.`disponibilidad` <> 'vendido') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_venta_resumen`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 SQL SECURITY INVOKER */
/*!50001 VIEW `v_venta_resumen` AS select `s`.`id` AS `venta_id`,`s`.`cliente_id` AS `cliente_id`,`s`.`vehiculo_id` AS `vehiculo_id`,`s`.`vendedor_id` AS `vendedor_id`,`s`.`fecha` AS `fecha`,`s`.`estado` AS `estado`,`s`.`precio_final` AS `precio_final`,coalesce(sum(`p`.`monto`),0) AS `total_pagado`,(`s`.`precio_final` - coalesce(sum(`p`.`monto`),0)) AS `pendiente_pago` from (`venta` `s` left join `pago` `p` on((`p`.`venta_id` = `s`.`id`))) group by `s`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

