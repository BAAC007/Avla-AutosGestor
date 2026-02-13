<?php
// Protección: Evita acceso DIRECTO a este archivo (desde navegador)
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

// Conexión PDO (única instancia)
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    error_log("Error DB: " . $e->getMessage());
    die("⚠️ Error de conexión a la base de datos. Contacte al administrador.");
}
?>