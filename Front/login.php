<?

session_start();

// Incluir la conexión
require_once 'config/database.php';

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
            $pdo = getDBConnection();
            
            // Preparar consulta (protección contra SQL Injection)
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
                
                // Redirigir al dashboard
                header('Location: dashboard.php');
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
</head>

<body>
    <form action="login.php" method="POST">
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
    </form>
</body>

</html>