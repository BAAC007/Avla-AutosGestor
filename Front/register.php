<?php
session_start();
require_once '../Back/inc/db.php';
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 🔑 Capturar todos los campos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $dni_nie = trim($_POST['dni_nie'] ?? '');
    $email_real = trim($_POST['email_real'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $clave_confirm = $_POST['clave_confirm'] ?? '';
    
    // Validaciones
    if (empty($nombre) || empty($apellidos) || empty($usuario) || empty($dni_nie) || empty($email_real) || empty($clave)) {
        $error = "Todos los campos marcados con * son obligatorios";
    } elseif ($clave !== $clave_confirm) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($clave) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } elseif (strlen($usuario) < 4) {
        $error = "El nombre de usuario debe tener al menos 4 caracteres";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
        $error = "El usuario solo puede contener letras, números y guión bajo";
    } elseif (!filter_var($email_real, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor ingresa un email válido";
    } else {
        try {
            // Iniciar transacción
            $pdo->beginTransaction();
            
            // Paso 1: Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT id FROM cliente WHERE usuario = :usuario");
            $stmt->execute(['usuario' => $usuario]);
            if ($stmt->fetch()) {
                $error = "Este nombre de usuario ya está en uso. Elige otro.";
                $pdo->rollBack();
            } else {
                // Paso 2: Verificar DNI/NIE duplicado
                $stmt = $pdo->prepare("SELECT id FROM cliente WHERE DNI_NIE = :dni_nie");
                $stmt->execute(['dni_nie' => $dni_nie]);
                if ($stmt->fetch()) {
                    $error = "Este DNI/NIE ya está registrado";
                    $pdo->rollBack();
                } else {
                    // Paso 3: Verificar email duplicado
                    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE email = :email");
                    $stmt->execute(['email' => $email_real]);
                    if ($stmt->fetch()) {
                        $error = "Este email ya está registrado";
                        $pdo->rollBack();
                    } else {
                        // Paso 4: Hashear contraseña
                        $contrasena_hash = password_hash($clave, PASSWORD_DEFAULT);
                        
                        // Paso 5: Combinar nombre + apellidos
                        $nombre_completo = trim($nombre . ' ' . $apellidos);
                        
                        // Paso 6: Insertar cliente con TODOS los campos
                        $stmt = $pdo->prepare("
                            INSERT INTO cliente (nombre, usuario, contrasena, DNI_NIE, email, telefono) 
                            VALUES (:nombre, :usuario, :contrasena, :dni_nie, :email, :telefono)
                        ");
                        
                        $stmt->execute([
                            'nombre' => $nombre_completo,
                            'usuario' => $usuario,
                            'contrasena' => $contrasena_hash,
                            'dni_nie' => $dni_nie,
                            'email' => $email_real,
                            'telefono' => $telefono
                        ]);
                        
                        $pdo->commit();
                        $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión";
                        
                        // Limpiar campos después del registro exitoso
                        $nombre = $apellidos = $usuario = $dni_nie = $email_real = $telefono = '';
                    }
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
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
        .required {
            color: #c62828;
            font-weight: bold;
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
        input:required {
            border-color: #2196F3;
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
        .form-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
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
                <input type="text" name="nombre" placeholder="Nombre(s) *" required 
                       value="<?php echo htmlspecialchars($nombre ?? ''); ?>">
            </div>
            <div id="input">
                <input type="text" name="apellidos" placeholder="Apellidos *" required 
                       value="<?php echo htmlspecialchars($apellidos ?? ''); ?>">
            </div>
            <div id="input">
                <input type="text" name="usuario" placeholder="Nombre de usuario *" required 
                       value="<?php echo htmlspecialchars($usuario ?? ''); ?>" minlength="4" maxlength="50">
                <small class="form-note">⚠️ Solo letras, números y guión bajo. Mínimo 4 caracteres.</small>
            </div>
            <div id="input">
                <input type="text" name="dni_nie" placeholder="DNI/NIE *" required 
                       value="<?php echo htmlspecialchars($dni_nie ?? ''); ?>" maxlength="9">
            </div>
            <div id="input">
                <input type="email" name="email_real" placeholder="Email *" required 
                       value="<?php echo htmlspecialchars($email_real ?? ''); ?>">
                <small class="form-note">📧 Recibirás ofertas y facturas en este email</small>
            </div>
            <div id="input">
                <input type="tel" name="telefono" placeholder="Teléfono *" required 
                       value="<?php echo htmlspecialchars($telefono ?? ''); ?>" pattern="[0-9]{9,15}">
            </div>
            <div id="input">
                <input type="password" name="clave" placeholder="Contraseña *" required minlength="8">
                <small class="form-note">🔒 Mínimo 8 caracteres</small>
            </div>
            <div id="input">
                <input type="password" name="clave_confirm" placeholder="Confirmar contraseña *" required minlength="8">
            </div>
            <div class="button">
                <button type="submit">Crear Cuenta</button>
            </div>
        </form>
        
        <a href="login.php" class="login-link">¿Ya tienes cuenta? Inicia sesión</a>
    </div>
    
    <script>
        // Validación mejorada de contraseñas y usuario
        document.querySelector('form').addEventListener('submit', function(e) {
            const clave = document.querySelector('[name="clave"]').value;
            const confirm = document.querySelector('[name="clave_confirm"]').value;
            const usuario = document.querySelector('[name="usuario"]').value;
            const email = document.querySelector('[name="email_real"]').value;
            
            // Validar contraseñas
            if (clave !== confirm) {
                e.preventDefault();
                alert('❌ Las contraseñas no coinciden');
                return false;
            }
            
            if (clave.length < 8) {
                e.preventDefault();
                alert('❌ La contraseña debe tener al menos 8 caracteres');
                return false;
            }
            
            // Validar usuario
            if (usuario.length < 4) {
                e.preventDefault();
                alert('❌ El nombre de usuario debe tener al menos 4 caracteres');
                return false;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(usuario)) {
                e.preventDefault();
                alert('❌ El usuario solo puede contener letras, números y guión bajo (_)');
                return false;
            }
            
            // Validar email
            if (!email.includes('@')) {
                e.preventDefault();
                alert('❌ Por favor ingresa un email válido');
                return false;
            }
        });
        
        // Mostrar sugerencias en tiempo real para el usuario
        document.querySelector('[name="usuario"]').addEventListener('input', function(e) {
            const usuario = e.target.value;
            const regex = /^[a-zA-Z0-9_]*$/;
            
            if (!regex.test(usuario)) {
                e.target.value = usuario.replace(/[^a-zA-Z0-9_]/g, '');
            }
        });
    </script>
</body>
</html>