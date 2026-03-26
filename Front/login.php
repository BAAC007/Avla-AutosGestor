<?php
session_start();
require_once dirname(__DIR__) . '/Back/db.php';

$mensaje = '';
$error   = '';

if (!isset($conexion) || !$conexion) {
    die("Error: No hay conexión a la base de datos");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario    = trim($_POST['usuario']   ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        $error = "Por favor, complete todos los campos";
    } else {
        $stmt = $conexion->prepare("
            SELECT id, nombre, contrasena, email
            FROM cliente
            WHERE usuario = ?
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $cliente   = $resultado->fetch_assoc();
            $stmt->close();

            if ($cliente && password_verify($contrasena, $cliente['contrasena'])) {
                $_SESSION['cliente_id']     = $cliente['id'];
                $_SESSION['cliente_nombre'] = $cliente['nombre'];
                $_SESSION['cliente_email']  = $cliente['email'];
                $_SESSION['logueado']       = true;
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } else {
            $error = "Error en el sistema. Intente nuevamente.";
            error_log("Error login: " . $conexion->error);
        }
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
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="POST">
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