sudo mysql -u root -p

--Creamos la base de datos AVLA y las tablas principales.

CREATE DATABASE AVLA
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;;

USE AVLA;

CREATE TABLE vehiculo(
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

ALTER TABLE vehiculo
ADD CONSTRAINT fk_vehiculo_marca
FOREIGN KEY (marca_id) REFERENCES marca(id)

ALTER TABLE vehiculo
ADD CONSTRAINT fk_vehiculo_modelo
FOREIGN KEY (modelo_id) REFERENCES modelo(id)

ALTER TABLE modelo
ADD CONSTRAINT fk_modelo_marca
FOREIGN KEY (marca_id) REFERENCES marca(id)



--Continuamos con las tablas de la parte de las transacciones.

/*

Algunas de las lineas de la siguientes tablas no tienen tipo por cuestiones que no se que tipo deberia darles me falta agregar las claves foraneas para conectar las tablas.

*/

CREATE TABLE venta(
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehiculo_id INT NOT NULL,
    cliente_id INT NOT NULL,
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    precio_final DECIMAL,
    forma_pago VARCHAR(50) CHECK (forma_pago IN ('Efectivo','Transferencia')),
    estado VARCHAR(10) CHECK (estado IN ('nuevo', 'usado'))
);

CREATE TABLE compra(
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehiculo_id INT NOT NULL,
    proveedor_id INT NOT NULL,
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    precio_compra DECIMAL,
    documento VARCHAR UNIQUE NOT NULL,
);

CREATE TABLE financiacion(
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT NOT NULL,
    entidad_bancaria VARCHAR(255) NOT NULL,
    monto DECIMAL,
    cuotas,
    tasa_intereses,
    fecha_aprobacion
);

CREATE TABLE pago(
    id INT PRIMARY KEY AUTO_INCREMENT,
    venta_id INT,
    monto DECIMAL,
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    metodo_pago VARCHAR(50) CHECK (metodo_pago('Efectivo', 'Transferencia')),
    referencia,
);


ALTER TABLE venta
ADD CONSTRAINT fk_venta_vehiculo
FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id)

ALTER TABLE venta
ADD CONSTRAINT fk_venta_cliente
FOREIGN KEY (cliente_id) REFERENCES cliente(id)

ALTER TABLE compra
ADD CONSTRAINT fk_compra_vehiculo
FOREIGN KEY (vehiculo_id) REFERENCES vehiculo(id)

ALTER TABLE compra
ADD CONSTRAINT fk_compra_proveedor
FOREIGN KEY (proveedor_id) REFERENCES proveedor(id)

ALTER TABLE financiacion
ADD CONSTRAINT fk_financiacion_venta
FOREIGN KEY (venta_id) REFERENCES venta(id)

ALTER TABLE pago
ADD CONSTRAINT fk_pago_venta
FOREIGN KEY (venta_id) REFERENCES venta(id)


--Continuamos con las tablas de gestion operativa

CREATE TABLE proveedores(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    contacto VARCHAR(255) NOT NULL,
    telefono INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
);

CREATE TABLE accesorio(
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL NOT NULL,
    stock INT NOT NULL,
);

CREATE TABLE vehiculo_accesorio(
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehiculo_id INT NOT NULL,
    accesorio_id INT NOT NULL,
    precio_instalacion DECIMAL
);

CREATE TABLE prueba_manejo(
    id INT PRIMARY KEY AUTO_INCREMENT,
    vehiculo_id INT NOT NULL,
    cliente_id INT NOT NULL,
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    observaciones TEXT
);