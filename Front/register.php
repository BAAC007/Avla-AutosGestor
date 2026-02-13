<?

session_start();
require_once 'config/database.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'] ?? '';
    $dni_nie = $_POST['dni_nie'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $contrasena_confirm = $_POST['contrasena_confirm'] ?? '';

    // Validaciones
    if (empty($nombre) || empty($dni_nie) || empty($email) || empty($contrasena)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($contrasena !== $contrasena_confirm) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($contrasena) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres";
    } else {
        try {
            $pdo = getDBConnection();

            // Verificar si el DNI/NIE ya existe
            $stmt = $pdo->prepare("SELECT id FROM cliente WHERE DNI_NIE = :dni_nie");
            $stmt->execute(['dni_nie' => $dni_nie]);

            if ($stmt->fetch()) {
                $error = "Este DNI/NIE ya está registrado";
            } else {
                // Verificar si el email ya existe
                $stmt = $pdo->prepare("SELECT id FROM cliente WHERE email = :email");
                $stmt->execute(['email' => $email]);

                if ($stmt->fetch()) {
                    $error = "Este email ya está registrado";
                } else {
                    // Hash de la contraseña
                    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

                    // Insertar nuevo cliente
                    $stmt = $pdo->prepare("
                        INSERT INTO cliente (nombre, contrasena, DNI_NIE, email, telefono) 
                        VALUES (:nombre, :contrasena, :dni_nie, :email, :telefono)
                    ");

                    $stmt->execute([
                        'nombre' => $nombre,
                        'contrasena' => $contrasena_hash,
                        'dni_nie' => $dni_nie,
                        'email' => $email,
                        'telefono' => $telefono
                    ]);

                    $mensaje = "¡Registro exitoso! Ahora puedes iniciar sesión";
                }
            }
        } catch (PDOException $e) {
            $error = "Error al registrar. Intente nuevamente.";
            error_log("Error registro: " . $e->getMessage());
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
</head>

<body>
    <form action="register.php" method="POST">
        <h1>Registro</h1>
        <div id="input">
            <input type="text" name="nombre" placeholder="Nombre">
        </div>
        <div id="input">
            <input type="text" name="apellidos" placeholder="Apellidos">
        </div>
        <div id="input">
            <input type="text" name="dni_nie" placeholder="DNI/NIE">
        </div>
        <div id="input">
            <input type="text" name="telefono" placeholder="Telefono">
        </div>
        <div id="input">
            <input type="text" name="usuario" placeholder="Nombre de usuario">
        </div>
        <div id="input">
            <input type="password" name="clave" placeholder="Clave">
        </div>
        <div class="button">
            <button type="submit">Registrarse</button>
        </div>
    </form>
</body>

</html>