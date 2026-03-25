<?php
// Back/index.php - API REST

require_once __DIR__ . '/config/cors.php';
handleCORS();

header('Content-Type: application/json');

// Obtener acción
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Routing de endpoints
switch ($action) {
    case 'test':
        echo json_encode([
            'status' => 'ok',
            'message' => 'API funcionando',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        break;

    case 'get_vehicles':
        // Aquí iría tu lógica de DB
        echo json_encode(['status' => 'ok', 'data' => []]);
        break;

    case 'login':
        // Endpoint para login vía API (opcional)
        $data = json_decode(file_get_contents('php://input'), true);
        $usuario = $data['usuario'] ?? '';
        $contrasena = $data['contrasena'] ?? '';
        
        // Validar credenciales (usa password_verify en producción)
        // ... lógica de autenticación ...
        
        echo json_encode(['status' => 'ok', 'token' => 'abc123']);
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint no encontrado',
            'available' => ['test', 'get_vehicles', 'login']
        ]);
        break;
}
exit;

session_start();
include "db.php";

$error = '';

// Si ya está "logueado", ir al escritorio
if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] === true) {
    header("Location: escritorio.php");
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    $stmt = $conexion->prepare("SELECT id, usuario, nombre_completo, activo FROM administrador WHERE usuario = ? AND contrasena = ? LIMIT 1");
    $stmt->bind_param("ss", $usuario, $contrasena);  // ✅ Compara texto plano
    $stmt->execute();
    $resultado = $stmt->get_result();
    $admin = $resultado->fetch_assoc();
    $stmt->close();

    if ($admin && $admin['activo'] == 1) {
        // ✅ Login exitoso - crear sesión
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_usuario'] = $admin['usuario'];
        $_SESSION['admin_nombre'] = $admin['nombre_completo'];
        $_SESSION['es_admin'] = true;  // ← Esta es la clave para proteger el panel

        header("Location: escritorio.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - AVLA Autosgestor</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="login-box">

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <h1>AVLA Admin</h1>

            <div class="controlformulario">
                <input type="text" name="usuario" placeholder="Usuario" required autofocus>
            </div>
            <div class="controlformulario">
                <input type="password" name="contrasena" placeholder="Constraseña" required>
            </div>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>