<?php
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
                <label>Usuario</label>
                <input type="text" name="usuario" required autofocus>
            </div>
            <div class="controlformulario">
                <label>Contraseña</label>
                <input type="password" name="contrasena" required>
            </div>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>