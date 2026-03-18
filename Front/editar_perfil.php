<?php
session_start();
require_once dirname(__DIR__) . '/Back/db.php';

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || !isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($conexion) || !$conexion) {
    die("Error: No hay conexion a la base de datos");
}

$cliente_id = $_SESSION['cliente_id'];
$mensaje = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $password_actual  = $_POST['password_actual'] ?? '';
    $password_nuevo   = $_POST['password_nuevo'] ?? '';
    $password_repetir = $_POST['password_repetir'] ?? '';

    // Validaciones basicas
    if (empty($nombre) || empty($email) || empty($telefono)) {
        $error = "Nombre, email y telefono son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del email no es valido.";
    } else {
        // Verificar que el email no este en uso por otro cliente
        $stmt = $conexion->prepare("SELECT id FROM cliente WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $cliente_id);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existe) {
            $error = "Ese email ya esta en uso por otra cuenta.";
        } else {
            // Si quiere cambiar la contrasena
            if (!empty($password_nuevo)) {
                if (empty($password_actual)) {
                    $error = "Debes introducir tu contrasena actual para cambiarla.";
                } elseif ($password_nuevo !== $password_repetir) {
                    $error = "La nueva contrasena no coincide con la confirmacion.";
                } elseif (strlen($password_nuevo) < 6) {
                    $error = "La nueva contrasena debe tener al menos 6 caracteres.";
                } else {
                    // Verificar contrasena actual
                    $stmt = $conexion->prepare("SELECT contrasena FROM cliente WHERE id = ?");
                    $stmt->bind_param("i", $cliente_id);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!password_verify($password_actual, $row['contrasena'])) {
                        $error = "La contrasena actual no es correcta.";
                    } else {
                        // Actualizar con nueva contrasena
                        $stmt = $conexion->prepare("UPDATE cliente SET nombre=?, email=?, telefono=?, contrasena=? WHERE id=?");
                        $nuevo_hash = password_hash($password_nuevo, PASSWORD_DEFAULT);
                        $stmt->bind_param("ssssi", $nombre, $email, $telefono, $nuevo_hash, $cliente_id);
                        if ($stmt->execute()) {
                            $_SESSION['cliente_nombre'] = $nombre;
                            $mensaje = "Datos actualizados correctamente.";
                        } else {
                            $error = "Error al guardar los cambios.";
                        }
                        $stmt->close();
                    }
                }
            } else {
                // Actualizar solo datos personales sin cambiar contrasena
                $stmt = $conexion->prepare("UPDATE cliente SET nombre=?, email=?, telefono=? WHERE id=?");
                $stmt->bind_param("sssi", $nombre, $email, $telefono, $cliente_id);
                if ($stmt->execute()) {
                    $_SESSION['cliente_nombre'] = $nombre;
                    $mensaje = "Datos actualizados correctamente.";
                } else {
                    $error = "Error al guardar los cambios.";
                }
                $stmt->close();
            }
        }
    }
}

// Cargar datos actuales del cliente
$stmt = $conexion->prepare("SELECT nombre, usuario, email, telefono, DNI_NIE FROM cliente WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Concesionario AVLA</title>
    <link rel="stylesheet" href="css/editar_perfil.css">
    <style>
        .perfil-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }

        .perfil-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0e1c5a, #2d2f3b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .campo-readonly {
            background: #f0f0f0;
            color: #888;
            cursor: not-allowed;
        }

        .seccion-password {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 10px;
        }

        .seccion-password h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .hint {
            font-size: 12px;
            color: #aaa;
            margin-top: 4px;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">

        <div class="header">
            <h1>Editar Perfil</h1>
            <a id="index" href="dashboard.php">Volver al inicio</a>
            <a id="logout" href="logout.php">Cerrar sesión</a>
        </div>

        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="section">
            <div class="perfil-header">
                <div class="perfil-avatar">
                    <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                </div>
                <div>
                    <strong style="font-size:18px;"><?php echo htmlspecialchars($cliente['nombre']); ?></strong><br>
                    <span style="color:#888;font-size:14px;">@<?php echo htmlspecialchars($cliente['usuario']); ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="form-row">
                    <div>
                        <label>Nombre completo *</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                    </div>
                    <div>
                        <label>Usuario</label>
                        <input type="text" value="<?php echo htmlspecialchars($cliente['usuario']); ?>" class="campo-readonly" readonly>
                        <p class="hint">El usuario no se puede cambiar.</p>
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                    </div>
                    <div>
                        <label>Telefono *</label>
                        <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
                    </div>
                </div>
                <div>
                    <label>DNI/NIE</label>
                    <input type="text" value="<?php echo htmlspecialchars($cliente['DNI_NIE']); ?>" class="campo-readonly" readonly>
                    <p class="hint">El DNI/NIE no se puede cambiar.</p>
                </div>

                <div class="seccion-password">
                    <h3>Cambiar contrasena (opcional)</h3>
                    <p class="hint" style="margin-bottom:14px;">Deja estos campos en blanco si no quieres cambiar tu contrasena.</p>
                    <div class="form-row">
                        <div>
                            <label>Contrasena actual</label>
                            <input type="password" name="password_actual" placeholder="Tu contrasena actual">
                        </div>
                        <div>
                            <label>Nueva contrasena</label>
                            <input type="password" name="password_nuevo" placeholder="Minimo 6 caracteres">
                        </div>
                    </div>
                    <div>
                        <label>Confirmar nueva contrasena</label>
                        <input type="password" name="password_repetir" placeholder="Repite la nueva contrasena">
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px;">
                    <button type="submit">Guardar cambios</button>
                    <a href="dashboard.php" style="padding:12px 25px;background:#7f8c8d;color:white;border-radius:4px;text-decoration:none;font-size:1rem;">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</body>

</html>