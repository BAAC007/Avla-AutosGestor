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