<?php
// ⚠️ IMPORTANTE: Usa esta ruta para incluir tu db.php existente
require_once dirname(__DIR__) . '/Back/db.php';

session_start();
$mensaje = '';
$error = '';

// Validar que $conexion exista
if (!isset($conexion) || !$conexion) {
    die("❌ Error: No hay conexión a la base de datos");
}

// Procesar login si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni_nie = $_POST['dni_nie'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Validaciones básicas
    if (empty($dni_nie) || empty($contrasena)) {
        $error = "Por favor, complete todos los campos";
    } else {
        // ✅ Preparar sentencia con MySQLi (usando ? en vez de :dni_nie)
        $stmt = $conexion->prepare("
            SELECT id, nombre, contrasena, email 
            FROM cliente 
            WHERE DNI_NIE = ? 
            LIMIT 1
        ");

        if ($stmt) {
            // Bind del parámetro: "s" = string
            $stmt->bind_param("s", $dni_nie);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $cliente = $resultado->fetch_assoc();
            $stmt->close();

            // Verificar credenciales
            if ($cliente && password_verify($contrasena, $cliente['contrasena'])) {
                // Login exitoso
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nombre'] = $cliente['nombre'];
                $_SESSION['cliente_email'] = $cliente['email'];
                $_SESSION['logueado'] = true;

                // Redirigir al dashboard del cliente
                header('Location: ../Back/dashboard.php');
                exit();
            } else {
                $error = "DNI/NIE o contraseña incorrectos";
            }
        } else {
            $error = "Error en el sistema. Intente nuevamente.";
            error_log("Error login (MySQLi prepare): " . $conexion->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesion</title>
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

            <h1>Inicio de sesion</h1>
            <div id="input">
                <input type="text" name="usuario" placeholder="Nombre de usuario">
            </div>
            <div id="input">
                <input type="password" name="clave" placeholder="Clave">
            </div>
            <div class="button">
                <button type="submit">Iniciar sesion</button>
            </div>
            <a href="register.php" class="register-link">¿No tienes cuenta? Registrarse</a>
        </form>
    </div>
</body>

</html>