<?php
include dirname(__DIR__, 2) . "/db.php";

$base_url = '/mi_area/Proyectos/Avila-AutosGestor/Back/';

// Validar ID
if (!isset($_POST['id_vehiculo']) || !is_numeric($_POST['id_vehiculo'])) {
    header("Location: " . $base_url . "escritorio.php?error=id_invalido");
    exit;
}

$id = intval($_POST['id_vehiculo']);

// Recoger datos (misma lógica que en crear)
$marca_id    = intval($_POST['marca_id']);
$modelo_id   = intval($_POST['modelo_id']);
$año         = intval($_POST['año']);
$vin         = strtoupper(trim($_POST['vin']));
$color       = trim($_POST['color']);
$precio      = floatval($_POST['precio']);
$estado      = in_array($_POST['estado'], ['nuevo', 'usado']) ? $_POST['estado'] : 'nuevo';
$kilometraje = intval($_POST['kilometraje']);
$fecha       = $_POST['fecha_ingreso'] ?? date('Y-m-d');

// Escapar strings
$vin_e = $conexion->real_escape_string($vin);
$color_e = $conexion->real_escape_string($color);
$estado_e = $conexion->real_escape_string($estado);

// UPDATE query
$sql = "UPDATE vehiculo SET
    marca_id = $marca_id,
    modelo_id = $modelo_id,
    año = $año,
    vin = '$vin_e',
    color = '$color_e',
    precio = $precio,
    estado = '$estado_e',
    kilometraje = $kilometraje,
    fecha_ingreso = '$fecha'
    WHERE id = $id";

if ($conexion->query($sql) === TRUE) {
    header("Location: " . $base_url . "escritorio.php?msg=vehiculo_actualizado");
} else {
    error_log("Error actualizando: " . $conexion->error);
    header("Location: " . $base_url . "escritorio.php?error=actualizacion_fallida");
}
$conexion->close();
exit;
