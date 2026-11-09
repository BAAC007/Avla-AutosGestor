<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../inc/csrf.php';
require_once __DIR__ . '/../inc/throttle.php';

$error = '';

if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] === true) {
    header("Location: escritorio.php");
    exit;
}

if (($_GET['error'] ?? '') === 'acceso_denegado') {
    $error = 'Debes iniciar sesión como administrador.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verificar()) {
        $error = "Token de seguridad inválido. Recarga la página e inténtalo de nuevo.";
    } else {
        $usuario    = trim($_POST['usuario']    ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if (login_bloqueado($conexion, 'admin', $usuario)) {
            $error = LOGIN_MSG_BLOQUEO;
        } else {
            // Buscar solo por usuario
            $stmt = $conexion->prepare("SELECT id, usuario, nombre_completo, contrasena, activo, rol FROM administrador WHERE usuario = ? LIMIT 1");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $admin = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Se verifica SIEMPRE un hash (aunque el usuario no exista) para que el
            // tiempo de respuesta no delate qué usuarios existen.
            $hash_ok = password_verify($contrasena, $admin['contrasena'] ?? '$2y$10$aSu6ff8WFHxVfn6H9EyJ7.eLtAT0gj8MCA15dT6D08HrtBiEW0XCi');

            if ($admin && $admin['activo'] == 1 && $hash_ok) {
                session_regenerate_id(true);   // evita la fijación de sesión
                $_SESSION['admin_id']      = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                $_SESSION['admin_nombre']  = $admin['nombre_completo'];
                $_SESSION['admin_rol']     = $admin['rol'];
                $_SESSION['es_admin']      = true;

                // Actualizar hash si el algoritmo por defecto ha cambiado + último acceso
                $nuevo_hash = password_needs_rehash($admin['contrasena'], PASSWORD_DEFAULT)
                    ? password_hash($contrasena, PASSWORD_DEFAULT) : $admin['contrasena'];
                $upd = $conexion->prepare("UPDATE administrador SET contrasena = ?, ultimo_acceso = NOW() WHERE id = ?");
                $upd->bind_param("si", $nuevo_hash, $admin['id']);
                $upd->execute();
                $upd->close();

                login_limpiar($conexion, 'admin', $usuario);
                header("Location: escritorio.php");
                exit;
            }

            login_registrar_fallo($conexion, 'admin', $usuario);
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