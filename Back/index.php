<?php
header('Content-Type: application/json; charset=utf-8');

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
    header("Access-Control-Allow-Origin: *");
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function getDBConnection()
{
    $host = getenv('DB_HOST') or die('❌ Falta variable DB_HOST');
    $user = getenv('DB_USER') or die('❌ Falta variable DB_USER');
    $pass = getenv('DB_PASS') or die('❌ Falta variable DB_PASS');
    $name = getenv('DB_NAME') or die('❌ Falta variable DB_NAME');

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        return null;
    }
}

function sendResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sendError($message, $status = 400)
{
    sendResponse(['error' => true, 'message' => $message], $status);
}

function crearToken($datos)
{
    $secret  = getenv('JWT_SECRET') or die('❌ Falta variable JWT_SECRET');
    $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode($datos));
    $firma   = hash_hmac('sha256', "$header.$payload", $secret);
    return "$header.$payload.$firma";
}

function verificarToken($token)
{
    $secret = getenv('JWT_SECRET') or die('❌ Falta variable JWT_SECRET');
    $partes = explode('.', $token);
    if (count($partes) !== 3) return null;

    [$header, $payload, $firma] = $partes;

    $firma_esperada = hash_hmac('sha256', "$header.$payload", $secret);
    if (!hash_equals($firma_esperada, $firma)) return null;

    $datos = json_decode(base64_decode($payload), true);
    if (!$datos || $datos['exp'] < time()) return null;

    return $datos;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {

    case 'test':
        sendResponse([
            'status'    => 'ok',
            'message'   => 'API de Avla-AutosGestor funcionando',
            'timestamp' => date('Y-m-d H:i:s'),
            'version'   => '1.0.0'
        ]);
        break;

    case 'get_vehicles':
        $pdo = getDBConnection();
        if (!$pdo) sendError('Error de conexión a la base de datos', 500);

        try {
            $stmt = $pdo->prepare("
                SELECT v.id, v.precio, v.año, v.color, v.kilometraje, v.estado, v.imagen,
                       m.nombre as marca, mo.nombre as modelo
                FROM vehiculo v
                INNER JOIN marca m ON v.marca_id = m.id
                INNER JOIN modelo mo ON v.modelo_id = mo.id
                WHERE v.estado IN ('nuevo', 'usado')
                ORDER BY v.fecha_ingreso DESC
            ");
            $stmt->execute();
            $vehicles = $stmt->fetchAll();
            sendResponse(['status' => 'ok', 'count' => count($vehicles), 'data' => $vehicles]);
        } catch (Exception $e) {
            error_log("Error get_vehicles: " . $e->getMessage());
            sendError('Error al obtener vehículos', 500);
        }
        break;

    case 'get_vehicle':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) sendError('ID de vehículo no válido', 400);

        $pdo = getDBConnection();
        if (!$pdo) sendError('Error de conexión a la base de datos', 500);

        try {
            $stmt = $pdo->prepare("
                SELECT v.id, v.precio, v.año, v.color, v.kilometraje, v.estado, v.imagen, v.vin,
                       m.nombre as marca, mo.nombre as modelo, mo.tipo as tipo
                FROM vehiculo v
                INNER JOIN marca m ON v.marca_id = m.id
                INNER JOIN modelo mo ON v.modelo_id = mo.id
                WHERE v.id = ?
            ");
            $stmt->execute([$id]);
            $vehicle = $stmt->fetch();
            if (!$vehicle) sendError('Vehículo no encontrado', 404);
            sendResponse(['status' => 'ok', 'data' => $vehicle]);
        } catch (Exception $e) {
            error_log("Error get_vehicle: " . $e->getMessage());
            sendError('Error al obtener el vehículo', 500);
        }
        break;

    case 'admin_login':
        $input      = json_decode(file_get_contents('php://input'), true);
        $usuario    = trim($_POST['usuario']    ?? $input['usuario']    ?? '');
        $contrasena = $_POST['contrasena'] ?? $input['contrasena'] ?? '';

        if (empty($usuario) || empty($contrasena)) sendError('Usuario y contraseña son requeridos', 400);

        $pdo = getDBConnection();
        if (!$pdo) sendError('Error de conexión a la base de datos', 500);

        try {
            $stmt = $pdo->prepare("SELECT id, usuario, nombre_completo, contrasena, activo FROM administrador WHERE usuario = ? LIMIT 1");
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($contrasena, $admin['contrasena']) && $admin['activo'] == 1) {
                $token = crearToken([
                    'id'      => $admin['id'],
                    'usuario' => $admin['usuario'],
                    'exp'     => time() + 3600
                ]);
                sendResponse([
                    'status'  => 'ok',
                    'message' => 'Login exitoso',
                    'data'    => [
                        'token' => $token,
                        'admin' => [
                            'id'      => $admin['id'],
                            'nombre'  => $admin['nombre_completo'],
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

    default:
        if ($action === null) {
            sendResponse([
                'api'       => 'Avla-AutosGestor API',
                'version'   => '1.0.0',
                'endpoints' => [
                    'GET /?action=test'             => 'Probar conexión',
                    'GET /?action=get_vehicles'     => 'Listar vehículos',
                    'GET /?action=get_vehicle&id=1' => 'Obtener vehículo por ID',
                    'POST /?action=admin_login'     => 'Login administrador',
                ]
            ]);
        } else {
            sendError("Endpoint '$action' no encontrado", 404);
        }
        break;
}

sendError('Método no permitido', 405);
