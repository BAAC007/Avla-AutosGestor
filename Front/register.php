<?php

session_start();
require_once '../Back/inc/db.php';
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 🔑 CAMPOS CORRECTOS según TU FORMULARIO HTML:
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $dni_nie = trim($_POST['dni_nie'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $usuario = trim($_POST['usuario'] ?? ''); // ¡OJO: tu tabla NO tiene este campo!
    $clave = $_POST['clave'] ?? '';
    
    // ⚠️ PROBLEMA CRÍTICO: Tu tabla CLIENTE requiere EMAIL pero NO existe en tu formulario
    // Solución temporal: usamos el usuario como email (NO RECOMENDADO para producción)
    // ¡MEJOR: agrega un campo email al formulario!
    $email = !empty($usuario) ? $usuario . '@concesionario.avla' : 'cliente@avla.es';
    
    // Validaciones
    if (empty($nombre) || empty($dni_nie) || empty($clave)) {
        $error = "Los campos Nombre, DNI/NIE y Contraseña son obligatorios";
    } elseif (strlen($clave) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } else {
        try {
            // ✅ Usa $pdo directamente (ya está definido en db.php)
            // Paso 1: Verificar DNI/NIE duplicado
            $stmt = $pdo->prepare("SELECT id FROM cliente WHERE DNI_NIE = :dni_nie");
            $stmt->execute(['dni_nie' => $dni_nie]);
            if ($stmt->fetch()) {
                $error = "Este DNI/NIE ya está registrado";
                exit;
            }
            
            // Paso 2: Verificar email duplicado (usando el email temporal)
            $stmt = $pdo->prepare("SELECT id FROM cliente WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error = "Este email ya está registrado";
                exit;
            }
            
            // Paso 3: Hashear contraseña
            $contrasena_hash = password_hash($clave, PASSWORD_DEFAULT);
            
            // Paso 4: Combinar nombre + apellidos para el campo 'nombre' de la tabla
            $nombre_completo = trim($nombre . ' ' . $apellidos);
            
            // Paso 5: Insertar cliente
            $stmt = $pdo->prepare("
                INSERT INTO cliente (nombre, contrasena, DNI_NIE, email, telefono) 
                VALUES (:nombre, :contrasena, :dni_nie, :email, :telefono)
            ");
            
            $stmt->execute([
                'nombre' => $nombre_completo,
                'contrasena' => $contrasena_hash,
                'dni_nie' => $dni_nie,
                'email' => $email, // ¡TEMPORAL! Debes agregar campo email real
                'telefono' => $telefono
            ]);
            
            $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión";
            
        } catch (PDOException $e) {
            $error = "Error al registrar. Intente nuevamente.";
            error_log("Registro error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Concesionario AVLA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
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
        .login-link {
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
    <div class="register-container">
        <h1>📝 Registro de Cliente</h1>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div id="input">
                <input type="text" name="nombre" placeholder="Nombre(s)" required 
                       value="<?php echo htmlspecialchars($nombre ?? ''); ?>">
            </div>
            <div id="input">
                <input type="text" name="apellidos" placeholder="Apellidos" required 
                       value="<?php echo htmlspecialchars($apellidos ?? ''); ?>">
            </div>
            <div id="input">
                <input type="text" name="dni_nie" placeholder="DNI/NIE" required 
                       value="<?php echo htmlspecialchars($dni_nie ?? ''); ?>">
            </div>
            <div id="input">
                <input type="tel" name="telefono" placeholder="Teléfono" required 
                       value="<?php echo htmlspecialchars($telefono ?? ''); ?>">
            </div>
            <div id="input">
                <input type="email" name="email_real" placeholder="Email (¡IMPORTANTE!)" required>
                <small style="color:#666; display:block; margin-top:5px;">
                    ⚠️ Este campo es obligatorio para recibir ofertas y facturas
                </small>
            </div>
            <div id="input">
                <input type="text" name="usuario" placeholder="Nombre de usuario (opcional)">
            </div>
            <div id="input">
                <input type="password" name="clave" placeholder="Contraseña (mín. 8 caracteres)" required>
            </div>
            <div id="input">
                <input type="password" name="clave_confirm" placeholder="Confirmar contraseña" required>
            </div>
            <div class="button">
                <button type="submit">Crear Cuenta</button>
            </div>
        </form>
        
        <a href="login.php" class="login-link">¿Ya tienes cuenta? Inicia sesión</a>
    </div>
    
    <script>
        // Validación simple de contraseñas en el frontend
        document.querySelector('form').addEventListener('submit', function(e) {
            const clave = document.querySelector('[name="clave"]').value;
            const confirm = document.querySelector('[name="clave_confirm"]').value;
            const email = document.querySelector('[name="email_real"]').value;
            
            if (clave !== confirm) {
                e.preventDefault();
                alert('❌ Las contraseñas no coinciden');
                return;
            }
            
            if (clave.length < 8) {
                e.preventDefault();
                alert('❌ La contraseña debe tener al menos 8 caracteres');
                return;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('❌ Por favor ingresa un email válido');
                return;
            }
        });
    </script>
</body>
</html>