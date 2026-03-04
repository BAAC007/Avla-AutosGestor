<?php
// Mostrar errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir conexión
include dirname(__DIR__, 2) . "/db.php";

// Definir ruta base absoluta
$base_url = '/mi_area/Proyectos/Avila-AutosGestor/Back/';

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $base_url . "escritorio.php?error=metodo_no_permitido");
    exit;
}

// ══════════════════════════════════════════════════════════
// 1. RECOGER Y VALIDAR DATOS DEL VEHÍCULO
// ══════════════════════════════════════════════════════════
$marca_id      = intval($_POST['marca_id'] ?? 0);
$modelo_id     = intval($_POST['modelo_id'] ?? 0);
$año           = intval($_POST['año'] ?? 0);
$vin           = strtoupper(trim($_POST['vin'] ?? ''));
$color         = trim($_POST['color'] ?? '');
$precio        = floatval($_POST['precio'] ?? 0);
$estado        = in_array($_POST['estado'] ?? '', ['nuevo', 'usado']) ? $_POST['estado'] : 'nuevo';
$kilometraje   = intval($_POST['kilometraje'] ?? 0);
$fecha_ingreso = $_POST['fecha_ingreso'] ?? date('Y-m-d');

// Validaciones básicas
if (empty($marca_id) || empty($modelo_id) || empty($vin) || empty($color) || empty($precio)) {
    header("Location: " . $base_url . "escritorio.php?error=faltan_campos");
    exit;
}

if (strlen($vin) !== 17) {
    header("Location: " . $base_url . "escritorio.php?error=vin_invalido");
    exit;
}

// Escapar strings
$vin_e    = $conexion->real_escape_string($vin);
$color_e  = $conexion->real_escape_string($color);
$estado_e = $conexion->real_escape_string($estado);

// ══════════════════════════════════════════════════════════
// 2. INICIAR TRANSACCIÓN (garantiza consistencia total)
//    Si cualquier INSERT falla, se deshace TODO.
// ══════════════════════════════════════════════════════════
$conexion->begin_transaction();

try {

    // ─── 2.1 INSERT del vehículo principal ───────────────
    $sql_vehiculo = "INSERT INTO vehiculo (
        marca_id, modelo_id, año, vin, color, precio, estado, kilometraje, fecha_ingreso
    ) VALUES (
        $marca_id, $modelo_id, $año,
        '$vin_e', '$color_e',
        $precio, '$estado_e',
        $kilometraje, '$fecha_ingreso'
    )";

    if (!$conexion->query($sql_vehiculo)) {
        throw new Exception("Error al crear vehículo: " . $conexion->error);
    }

    // Obtener el ID del vehículo recién creado
    $vehiculo_id = $conexion->insert_id;

    // ─── 2.2 INSERT de accesorios (si se seleccionaron) ──
    if (!empty($_POST['accesorios']) && is_array($_POST['accesorios'])) {
        foreach ($_POST['accesorios'] as $accesorio_id) {
            $accesorio_id = intval($accesorio_id);

            // Precio de instalación (puede ser 0)
            $precio_inst = floatval($_POST['instalacion'][$accesorio_id] ?? 0);

            $sql_acc = "INSERT INTO vehiculo_accesorio (vehiculo_id, accesorio_id, precio_instalacion)
                        VALUES ($vehiculo_id, $accesorio_id, $precio_inst)";

            if (!$conexion->query($sql_acc)) {
                throw new Exception("Error al añadir accesorio ID $accesorio_id: " . $conexion->error);
            }
        }
    }

    // ─── 2.3 INSERT de pruebas de manejo (si se añadieron) ─
    if (!empty($_POST['prueba_cliente_id']) && is_array($_POST['prueba_cliente_id'])) {
        $total_pruebas = count($_POST['prueba_cliente_id']);

        for ($i = 0; $i < $total_pruebas; $i++) {
            $cliente_id     = intval($_POST['prueba_cliente_id'][$i] ?? 0);
            $fecha_prueba   = $conexion->real_escape_string($_POST['prueba_fecha'][$i] ?? date('Y-m-d'));
            $hora_prueba    = $conexion->real_escape_string($_POST['prueba_hora'][$i] ?? '');
            $observaciones  = $conexion->real_escape_string($_POST['prueba_observaciones'][$i] ?? '');

            // Solo insertar si se seleccionó un cliente y hay hora
            if ($cliente_id > 0 && !empty($hora_prueba)) {
                $sql_prueba = "INSERT INTO prueba_manejo (vehiculo_id, cliente_id, fecha, hora, observaciones)
                               VALUES ($vehiculo_id, $cliente_id, '$fecha_prueba', '$hora_prueba', '$observaciones')";

                if (!$conexion->query($sql_prueba)) {
                    throw new Exception("Error al añadir prueba de manejo #$i: " . $conexion->error);
                }
            }
        }
    }

    // ─── 2.4 Todo OK: confirmar la transacción ───────────
    $conexion->commit();
    header("Location: " . $base_url . "escritorio.php?msg=vehiculo_creado");
    exit;

} catch (Exception $e) {

    // ─── Error: revertir todo lo que se hizo ─────────────
    $conexion->rollback();
    error_log("Error al crear vehículo con datos relacionados: " . $e->getMessage());

    // En desarrollo mostramos el error; en producción redirigir con código de error
    echo "<div style='background:#fee2e2;color:#991b1b;padding:20px;margin:20px;border-radius:8px;font-family:monospace;'>";
    echo "<h3>❌ Error al crear el vehículo</h3>";
    echo "<p><strong>Detalle:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<br><a href='" . $base_url . "escritorio.php'>← Volver al panel</a>";
    echo "</div>";

    // Descomenta esto en producción:
    // header("Location: " . $base_url . "escritorio.php?error=creacion_fallida");
}

$conexion->close();
exit;
?>