<?php
session_start();
require_once dirname(__DIR__) . '/Back/db.php';
require_once dirname(__DIR__) . '/Back/inc/csrf.php';
require_once dirname(__DIR__) . '/Back/inc/throttle.php';

$mensaje = '';
$error   = '';

if (!isset($conexion) || !$conexion) {
    die("Error: No hay conexión a la base de datos");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario    = trim($_POST['usuario']   ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (!csrf_verificar()) {
        $error = "Token de seguridad inválido. Recarga la página e inténtalo de nuevo.";
    } elseif (empty($usuario) || empty($contrasena)) {
        $error = "Por favor, complete todos los campos";
    } elseif (login_bloqueado($conexion, 'cliente', $usuario)) {
        $error = LOGIN_MSG_BLOQUEO;
    } else {
        $stmt = $conexion->prepare("
            SELECT id, nombre, contrasena, email, activo
            FROM cliente
            WHERE usuario = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $cliente = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Se verifica siempre un hash para no delatar qué usuarios existen por el tiempo de respuesta
        $hash_ok = password_verify($contrasena, $cliente['contrasena'] ?? '$2y$10$aSu6ff8WFHxVfn6H9EyJ7.eLtAT0gj8MCA15dT6D08HrtBiEW0XCi');

        if ($cliente && $cliente['activo'] == 1 && $hash_ok) {
            session_regenerate_id(true);   // evita la fijación de sesión
            $_SESSION['cliente_id']     = $cliente['id'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];
            $_SESSION['cliente_email']  = $cliente['email'];
            $_SESSION['logueado']       = true;

            $upd = $conexion->prepare("UPDATE cliente SET ultimo_acceso = NOW() WHERE id = ?");
            $upd->bind_param("i", $cliente['id']);
            $upd->execute();
            $upd->close();

            login_limpiar($conexion, 'cliente', $usuario);
            header('Location: dashboard.php');
            exit();
        }

        login_registrar_fallo($conexion, 'cliente', $usuario);
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesión - AVLA</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="imagenes/favicon/favicon.ico" type="image/x-icon">
    <link rel="icon" href="imagenes/Avlalogo.png" type="image/png">
</head>

<body>
    <div class="login-container">
        <form action="login.php" method="POST">
            <?php echo csrf_campo_html(); ?>
            <?php if ($error): ?>
                <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($mensaje): ?>
                <div class="mensaje exito"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            <h1>Inicio de sesión</h1>
            <div class="input-group">
                <input type="text" name="usuario" placeholder="Nombre de usuario" required>
            </div>
            <div class="input-group">
                <input type="password" name="contrasena" placeholder="Contraseña" required>
            </div>
            <div class="button">
                <button type="submit">Iniciar sesión</button>
            </div>
            <a href="register.php" class="register-link">¿No tienes cuenta? Registrarse</a>
        </form>
    </div>
</body>

</html>