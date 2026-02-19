<?php
// Protección: Evita acceso DIRECTO a este archivo
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    die('⚠️ Acceso denegado. Este archivo es solo para inclusión interna.');
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'AVLA');
define('DB_USER', 'AVLA');
define('DB_PASS', 'AVLA*123$');
define('DB_CHARSET', 'utf8mb4');

// Conexión MySQLi (crea la variable $conexion que usan tus otros archivos)
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conexion->connect_error) {
    error_log("Error de conexión MySQLi: " . $conexion->connect_error);
    die("⚠️ Error de conexión a la base de datos. Contacte al administrador.");
}

// Establecer charset
$conexion->set_charset(DB_CHARSET);
?>