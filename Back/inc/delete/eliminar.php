<?php
// inc/delete/eliminar.php
include dirname(__DIR__, 2) . "/db.php";

// ✅ Define la ruta base ABSOLUTA desde el DocumentRoot
$base_url = '/mi_area/Proyectos/Avila-AutosGestor/Back/';

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . $base_url . "escritorio.php?error=id_invalido");
    exit;
}

$id = intval($_GET['id']);

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

// Verificar otras tablas relacionadas
$tablas_relacionadas = [
    'venta' => 'vehiculo_id',
    'mantenimiento' => 'vehiculo_id',
];

foreach ($tablas_relacionadas as $tabla => $columna) {
    $check_table = $conexion->query("SHOW TABLES LIKE '$tabla'");
    if ($check_table->num_rows > 0) {
        $stmt = $conexion->prepare("SELECT id FROM $tabla WHERE $columna = ? LIMIT 1");
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