<?php
// ⚠️ IMPORTANTE: Usa esta ruta para incluir tu db.php existente
require_once '../Back/inc/db.php';

session_start();
$mensaje = '';
$error = '';

// Procesar login si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni_nie = $_POST['dni_nie'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Validaciones básicas
    if (empty($dni_nie) || empty($contrasena)) {
        $error = "Por favor, complete todos los campos";
    } else {
        try {
            // ✅ Usa la conexión ya configurada en db.php
            $stmt = $pdo->prepare("
                SELECT id, nombre, contrasena, email 
                FROM cliente 
                WHERE DNI_NIE = :dni_nie 
                LIMIT 1
            ");

            $stmt->execute(['dni_nie' => $dni_nie]);
            $cliente = $stmt->fetch();

            if ($cliente && password_verify($contrasena, $cliente['contrasena'])) {
                // Login exitoso
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nombre'] = $cliente['nombre'];
                $_SESSION['cliente_email'] = $cliente['email'];
                $_SESSION['logueado'] = true;

                // Redirigir al dashboard del cliente
                header('Location: ../Back/dashboard.php'); // ¡Cambia esto si tienes otro dashboard!
                exit();
            } else {
                $error = "DNI/NIE o contraseña incorrectos";
            }
        } catch (PDOException $e) {
            $error = "Error en el sistema. Intente nuevamente.";
            error_log("Error login: " . $e->getMessage());
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        #input {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        .button {
            text-align: center;
            margin-top: 10px;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: transform 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .mensaje {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .exito {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            display: block;
            font-weight: bold;
        }
    </style>
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