<?php
// Protección: Evita acceso DIRECTO a este archivo
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    die('Acceso denegado. Este archivo es solo para inclusión interna.');
}

// Configuración de la base de datos (variables de entorno para producción)
define('DB_HOST',    getenv('DB_HOST')    ?: 'sql7.freesqldatabase.com');
define('DB_NAME',    getenv('DB_NAME')    ?: 'sql7821268');
define('DB_USER',    getenv('DB_USER')    ?: 'sql7821268');
define('DB_PASS',    getenv('DB_PASS')    ?: 'MyRTakBYy4');
define('DB_CHARSET', 'utf8mb4');

// Conexión MySQLi
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conexion->connect_error) {
    error_log("Error de conexión MySQLi: " . $conexion->connect_error);
    die("Error de conexión a la base de datos. Contacte al administrador.");
}

// Establecer charset
$conexion->set_charset(DB_CHARSET);
?>