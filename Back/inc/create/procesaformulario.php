<?php
include __DIR__ . "/../db.php";

// Recoger datos del POST (adaptados a vehiculo)
$marca_id       = intval($_POST['marca_id']);        // INT, foreign key
$modelo_id      = intval($_POST['modelo_id']);       // INT, foreign key
$año            = intval($_POST['año']);             // INT 1900-2100
$vin            = strtoupper(trim($_POST['vin']));   // VARCHAR(17), único
$color          = trim($_POST['color']);             // VARCHAR(50)
$precio         = floatval($_POST['precio']);        // DECIMAL(12,2)
$estado         = $_POST['estado'];                  // ENUM: 'nuevo' o 'usado'
$kilometraje    = intval($_POST['kilometraje']);     // INT >= 0
$fecha_ingreso  = $_POST['fecha_ingreso'] ?? date('Y-m-d'); // DATE

// Validación básica (opcional pero recomendada)
if (empty($vin) || strlen($vin) !== 17) {
    die("Error: El VIN debe tener exactamente 17 caracteres.");
}

// Escapar strings para evitar errores básicos de sintaxis SQL
$vin_escaped    = $conexion->real_escape_string($vin);
$color_escaped  = $conexion->real_escape_string($color);
$estado_escaped = $conexion->real_escape_string($estado);

$sql = "
  INSERT INTO vehiculo (
    marca_id,
    modelo_id,
    año,
    vin,
    color,
    precio,
    estado,
    kilometraje,
    fecha_ingreso
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
  )
";

if ($conexion->query($sql) === TRUE) {
    // Redirigir con mensaje de éxito
    header("Location: /ruta/a/tu/escritorio.php?msg=vehiculo_creado");
} else {
    // Mostrar error si falla (útil en desarrollo)
    echo "Error al registrar: " . $conexion->error;
}

$conexion->close();
exit;
