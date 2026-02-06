sudo mysql -u root -p

--Creamos la base de datos AVLA y las tablas principales.

CREATE DATABASE AVLA
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;;

USE AVLA;

CREATE TABLE vehiculos(
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca_id INT NOT_NULL,
    modelo_id INT NOT_NULL,
    año DATE NOT NULL,
    vin VARCHAR(255) UNIQUE NOT NULL,
    color VARCHAR(255) NOT NULL,
    precio DECIMAL(12,2) NOT NULL CHECK (precio >= 0),
    estado VARCHAR(10) CHECK (estado IN ('nuevo', 'usado'))
    kilometraje INT DEFAULT 0,
    fecha_ingreso DATE NOT NULL DEFAULT CURRENT_DATE 
);

CREATE TABLE marca(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    pais_origen VARCHAR(255) NOT NULL
);

CREATE TABLE modelo(
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca_id INT NOT NULL,
    nombre VARCHAR(100),
    tipo VARCHAR(50) CHECK (tipo IN ('sedan', 'SUV', 'pickup', 'berlina', 'furgonetas', 'coup', 'descapotables', 'rancheras'))
);

CREATE TABLE cliente(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    DNI_NIE VARCHAR(9) UNIQUE NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono INT NOT NULL,
    fecha_registro
);

ALTER TABLE vehiculos
ADD CONSTRAINT fk_vehiculos_marca
FOREIGN KEY (marca_id) REFERENCES marca(id)

ALTER TABLE vehiculos
ADD CONSTRAINT fk_vehiculos_modelo
FOREIGN KEY (modelo_id) REFERENCES modelo(id)


