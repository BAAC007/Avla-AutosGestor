<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../inc/csrf.php';

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

        $stmt = $conexion->prepare(
            "SELECT id, usuario, nombre_completo, contrasena, activo 
     FROM administrador WHERE usuario = ? LIMIT 1"
        );
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && $admin['activo'] == 1 && password_verify($contrasena, $admin['contrasena'])) {
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