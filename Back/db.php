<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    die('Acceso denegado. Este archivo es solo para inclusión interna.');
}

define('DB_HOST',    getenv('DB_HOST')    ?: '80z6e6.h.filess.io');
define('DB_NAME',    getenv('DB_NAME')    ?: 'AVLA_maptiredup');
define('DB_USER',    getenv('DB_USER')    ?: 'AVLA_maptiredup');
define('DB_PASS',    getenv('DB_PASS')    ?: 'edff45ef858e16c0c3958fcde46b78ba1d00e1e2');
define('DB_CHARSET', 'utf8mb4');

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexion->connect_error) {
    error_log("Error de conexión MySQLi: " . $conexion->connect_error);
    die("Error de conexión a la base de datos. Contacte al administrador.");
}

$conexion->set_charset(DB_CHARSET);
?>