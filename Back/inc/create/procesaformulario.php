<?php
// Mostrar errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir conexión
include dirname(__DIR__, 2) . "/db.php";

// Definir ruta base absoluta
$base_url = '/mi_area/Proyectos/Avila-AutosGestor/Back/';

// Validar que se recibieron datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $base_url . "escritorio.php?error=metodo_no_permitido");
    exit;
}

// Recoger y sanitizar datos del formulario
$marca_id       = intval($_POST['marca_id'] ?? 0);
$modelo_id      = intval($_POST['modelo_id'] ?? 0);
$año            = intval($_POST['año'] ?? 0);
$vin            = strtoupper(trim($_POST['vin'] ?? ''));
$color          = trim($_POST['color'] ?? '');
$precio         = floatval($_POST['precio'] ?? 0);
$estado         = in_array($_POST['estado'] ?? '', ['nuevo', 'usado']) ? $_POST['estado'] : 'nuevo';
$kilometraje    = intval($_POST['kilometraje'] ?? 0);
$fecha_ingreso  = $_POST['fecha_ingreso'] ?? date('Y-m-d');

// Validaciones básicas
if (empty($marca_id) || empty($modelo_id) || empty($vin) || empty($color) || empty($precio)) {
    error_log("Error: Faltan campos obligatorios");
    header("Location: " . $base_url . "escritorio.php?error=faltan_campos");
    exit;
}

if (strlen($vin) !== 17) {
    error_log("Error: VIN debe tener 17 caracteres");
    header("Location: " . $base_url . "escritorio.php?error=vin_invalido");
    exit;
}

// Escapar strings para seguridad
$vin_escaped    = $conexion->real_escape_string($vin);
$color_escaped  = $conexion->real_escape_string($color);
$estado_escaped = $conexion->real_escape_string($estado);

// Construir query
$sql = "INSERT INTO vehiculo (
    marca_id, modelo_id, año, vin, color, precio, estado, kilometraje, fecha_ingreso
) VALUES (
    $marca_id,
    $modelo_id,
    $año,
    '$vin_escaped',
    '$color_escaped',
    $precio,
    '$estado_escaped',
    $kilometraje,
    '$fecha_ingreso'
)";

// Ejecutar consulta
if ($conexion->query($sql) === TRUE) {
    // ✅ Éxito: redirigir con mensaje
    header("Location: " . $base_url . "escritorio.php?msg=vehiculo_creado");
    exit;
} else {
    // ❌ Error: registrar y mostrar
    $error_msg = "Error al registrar: " . $conexion->error;
    error_log($error_msg);
    
    // En desarrollo, mostrar el error
    echo "<div style='background:#fee2e2;color:#991b1b;padding:20px;margin:20px;border-radius:8px;font-family:monospace;'>";
    echo "<h3>❌ Error al crear el vehículo</h3>";
    echo "<p><strong>Error MySQL:</strong> " . htmlspecialchars($conexion->error) . "</p>";
    echo "<p><strong>SQL ejecutado:</strong><br><code>" . htmlspecialchars($sql) . "</code></p>";
    echo "<br><a href='" . $base_url . "escritorio.php'>← Volver al panel</a>";
    echo "</div>";
    
    // Descomenta esto en producción:
    // header("Location: " . $base_url . "escritorio.php?error=creacion_fallida");
}

$conexion->close();
exit;
?>