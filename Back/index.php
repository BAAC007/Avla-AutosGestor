<?php
// Back/index.php - API REST para Avla-AutosGestor

// ============================================================================
// 1. HEADERS Y CORS (siempre antes de cualquier output)
// ============================================================================
header('Content-Type: application/json; charset=utf-8');

// CORS: Permitir orígenes (ajusta en producción)
$allowed_origins = [
    'https://avla-autosgestor.onrender.com',
    'http://localhost:8080',
    'http://localhost:3000',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
} else {
    // Fallback seguro para desarrollo
    header("Access-Control-Allow-Origin: *");
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Access-Control-Max-Age: 86400');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ============================================================================
// 2. CONEXIÓN A BASE DE DATOS
// ============================================================================
function getDBConnection() {
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $name = getenv('DB_NAME') ?: 'avla_autosgestor';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // No exponer detalles de error en producción
        error_log("DB Error: " . $e->getMessage());
        return null;
    }
}

// ============================================================================
// 3. FUNCIONES DE RESPUESTA
// ============================================================================
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sendError($message, $status = 400) {
    sendResponse(['error' => true, 'message' => $message], $status);
}

// ============================================================================
// 4. ENDPOINTS DE LA API
// ============================================================================

// Obtener acción (GET o POST)
$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {
    
    // 🔹 Endpoint de prueba
    case 'test':
        sendResponse([
            'status' => 'ok',
            'message' => 'API de Avla-AutosGestor funcionando ✅',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
        break;

    // 🔹 Obtener vehículos (público)
    case 'get_vehicles':
        $pdo = getDBConnection();
        if (!$pdo) {
            sendError('Error de conexión a la base de datos', 500);
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id, marca, modelo, año, precio, imagen, disponible FROM vehiculos WHERE activo = 1 ORDER BY created_at DESC");
            $stmt->execute();
            $vehicles = $stmt->fetchAll();
            
            sendResponse([
                'status' => 'ok',
                'count' => count($vehicles),
                'data' => $vehicles
            ]);
        } catch (Exception $e) {
            error_log("Error get_vehicles: " . $e->getMessage());
            sendError('Error al obtener vehículos', 500);
        }
        break;

    // 🔹 Obtener vehículo por ID
    case 'get_vehicle':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            sendError('ID de vehículo no válido', 400);
        }
        
        $pdo = getDBConnection();
        if (!$pdo) {
            sendError('Error de conexión a la base de datos', 500);
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id, marca, modelo, año, precio, descripcion, imagen, disponible FROM vehiculos WHERE id = ? AND activo = 1");
            $stmt->execute([$id]);
            $vehicle = $stmt->fetch();
            
            if (!$vehicle) {
                sendError('Vehículo no encontrado', 404);
            }
            
            sendResponse([
                'status' => 'ok',
                'data' => $vehicle
            ]);
        } catch (Exception $e) {
            error_log("Error get_vehicle: " . $e->getMessage());
            sendError('Error al obtener el vehículo', 500);
        }
        break;

    // 🔹 Login de administrador (para panel)
    case 'admin_login':
        // Leer datos JSON si es POST con Content-Type: application/json
        $input = json_decode(file_get_contents('php://input'), true);
        $usuario = trim($_POST['usuario'] ?? $input['usuario'] ?? '');
        $contrasena = $_POST['contrasena'] ?? $input['contrasena'] ?? '';
        
        if (empty($usuario) || empty($contrasena)) {
            sendError('Usuario y contraseña son requeridos', 400);
        }
        
        $pdo = getDBConnection();
        if (!$pdo) {
            sendError('Error de conexión a la base de datos', 500);
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id, usuario, nombre_completo, contrasena, activo FROM administrador WHERE usuario = ? LIMIT 1");
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch();
            
            // ⚠️ IMPORTANTE: Usa password_verify() en producción
            // if ($admin && password_verify($contrasena, $admin['contrasena']) && $admin['activo'] == 1)
            
            // Temporal para desarrollo (comparación texto plano - NO USAR EN PROD)
            if ($admin && $admin['contrasena'] === $contrasena && $admin['activo'] == 1) {
                // Generar token simple (usa JWT en producción)
                $token = base64_encode(json_encode([
                    'id' => $admin['id'],
                    'usuario' => $admin['usuario'],
                    'exp' => time() + 3600 // 1 hora
                ]));
                
                sendResponse([
                    'status' => 'ok',
                    'message' => 'Login exitoso',
                    'data' => [
                        'token' => $token,
                        'admin' => [
                            'id' => $admin['id'],
                            'nombre' => $admin['nombre_completo'],
                            'usuario' => $admin['usuario']
                        ]
                    ]
                ]);
            } else {
                sendError('Credenciales incorrectas', 401);
            }
        } catch (Exception $e) {
            error_log("Error admin_login: " . $e->getMessage());
            sendError('Error en autenticación', 500);
        }
        break;

    // 🔹 Verificar token de admin (para proteger endpoints)
    case 'verify_token':
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['token'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
        
        if (empty($token)) {
            sendError('Token no proporcionado', 401);
        }
        
        try {
            $decoded = json_decode(base64_decode($token), true);
            if (!$decoded || !isset($decoded['exp']) || $decoded['exp'] < time()) {
                sendError('Token inválido o expirado', 401);
            }
            
            sendResponse([
                'status' => 'ok',
                'message' => 'Token válido',
                'admin_id' => $decoded['id']
            ]);
        } catch (Exception $e) {
            sendError('Error al verificar token', 401);
        }
        break;

    // 🔹 Endpoint por defecto (cuando no se especifica action)
    default:
        if ($action === null) {
            // Sin action: mostrar documentación básica
            sendResponse([
                'api' => 'Avla-AutosGestor API',
                'version' => '1.0.0',
                'endpoints' => [
                    'GET /api/?action=test' => 'Probar conexión',
                    'GET /api/?action=get_vehicles' => 'Listar vehículos',
                    'GET /api/?action=get_vehicle&id=1' => 'Obtener vehículo por ID',
                    'POST /api/?action=admin_login' => 'Login administrador',
                    'GET /api/?action=verify_token&token=xxx' => 'Verificar token'
                ],
                'note' => 'Para más información, consulta la documentación'
            ], 200);
        } else {
            // Action no reconocido
            sendError("Endpoint '$action' no encontrado", 404);
        }
        break;
}

// ============================================================================
// 5. FALLBACK FINAL (nunca debería llegar aquí)
// ============================================================================
sendError('Método no permitido', 405);