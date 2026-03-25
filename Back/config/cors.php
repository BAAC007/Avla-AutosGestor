<?php
// Back/config/cors.php
function handleCORS(): void {
    // Orígenes permitidos (ajusta en producción)
    $allowedOrigins = [
        'https://avla-autosgestor.onrender.com',  // Tu dominio en Render
        'http://localhost:8080',                   // Desarrollo local
        'http://localhost:3000',
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Validar origen
    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Vary: Origin");
    } else {
        // Fallback seguro: no enviar header si no está permitido
        // header("Access-Control-Allow-Origin: *"); // ⚠️ Solo para testing
    }

    // Headers CORS estándar
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
    header("Access-Control-Max-Age: 86400"); // Cache preflight 24h
    header("Access-Control-Allow-Credentials: true");

    // Manejar preflight OPTIONS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit(0);
    }
}