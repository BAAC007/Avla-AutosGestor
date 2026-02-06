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

--para conectar las tablas con una clave foranea.
ALTER TABLE emails
ADD CONSTRAINT fk_emails_personas
FOREIGN KEY (persona) REFERENCES personas(identificador)