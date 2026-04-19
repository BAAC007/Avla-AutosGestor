<?php
session_start();
require_once __DIR__ . '/../db.php';

$error = '';

if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] === true) {
    header("Location: escritorio.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verificar()) {
        $error = "Token de seguridad inválido. Recarga la página e inténtalo de nuevo.";
    } else {
        $usuario    = trim($_POST['usuario']    ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        $stmt = $conexion->prepare("SELECT id, usuario, nombre_completo, activo FROM administrador WHERE usuario = ? AND contrasena = ? LIMIT 1");
        $stmt->bind_param("ss", $usuario, $contrasena);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $admin     = $resultado->fetch_assoc();
        $stmt->close();

        if ($admin && $admin['activo'] == 1) {
            $_SESSION['admin_id']      = $admin['id'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            $_SESSION['admin_nombre']  = $admin['nombre_completo'];
            $_SESSION['es_admin']      = true;
            header("Location: escritorio.php");
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - AVLA Autosgestor</title>
    <link rel="stylesheet" href="/back-css/index.css">
</head>
<body>
    <div class="login-box">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="index.php">
            <?php echo csrf_campo_html(); ?>
            <h1>AVLA Admin</h1>
            <div class="controlformulario">
                <input type="text" name="usuario" placeholder="Usuario" required autofocus>
            </div>
            <div class="controlformulario">
                <input type="password" name="contrasena" placeholder="Contraseña" required>
            </div>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>