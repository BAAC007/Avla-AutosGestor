sudo mysql -u root -p

CREATE DATABASE autosgestor;

USE autosgestor;

CREATE TABLE vehiculos(
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca_id INT,
    modelo_id INT,
    
);