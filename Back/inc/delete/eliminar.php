<?php
// inc/delete/eliminar.php (se ejecuta incluido desde admin/escritorio.php)
require_once dirname(__DIR__) . '/auth.php';
requerir_admin();

include_once dirname(__DIR__, 2) . "/db.php";

// Define la ruta base ABSOLUTA desde el DocumentRoot
$base_url = '/admin/';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $base_url . "escritorio.php?error=metodo_no_permitido");
    exit;
}

require_once dirname(__DIR__) . '/csrf.php';
if (!csrf_verificar()) {
    header("Location: " . $base_url . "escritorio.php?error=csrf_invalido");
    exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . $base_url . "escritorio.php?error=id_invalido");
    exit;
}

// Verificar pruebas de manejo
$stmt = $conexion->prepare("SELECT id FROM prueba_manejo WHERE vehiculo_id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$tiene_pruebas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($tiene_pruebas) {
    header("Location: " . $base_url . "escritorio.php?error=tiene_pruebas_manejo");
    $conexion->close();
    exit;
}

// Verificar otras tablas relacionadas (todas existen tras migracion_v2.sql)
$tablas_relacionadas = [
    'venta'         => 'vehiculo_id',
    'compra'        => 'vehiculo_id',
    'reserva'       => 'vehiculo_id',
    'mantenimiento' => 'vehiculo_id',
];

foreach ($tablas_relacionadas as $tabla => $columna) {
    // Los nombres de tabla/columna son constantes del array de arriba, no input del usuario
    $stmt = $conexion->prepare("SELECT id FROM `$tabla` WHERE `$columna` = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $tiene_relacion = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($tiene_relacion) {
        header("Location: " . $base_url . "escritorio.php?error=tiene_registros_relacionados");
        $conexion->close();
        exit;
    }
}

// Eliminar vehículo
$sql = "DELETE FROM vehiculo WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: " . $base_url . "escritorio.php?msg=vehiculo_eliminado");
} else {
    header("Location: " . $base_url . "escritorio.php?error=eliminacion_fallida");
}

$stmt->close();
$conexion->close();
exit;
?>