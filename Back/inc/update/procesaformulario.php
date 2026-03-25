<?php
include dirname(__DIR__, 2) . "/db.php";

$base_url = '/Back/';

// Validar ID
if (!isset($_POST['id_vehiculo']) || !is_numeric($_POST['id_vehiculo'])) {
    header("Location: " . $base_url . "escritorio.php?error=id_invalido");
    exit;
}

$id = intval($_POST['id_vehiculo']);

// ══════════════════════════════════════════════════════════
// 1. RECOGER Y VALIDAR DATOS DEL VEHÍCULO
// ══════════════════════════════════════════════════════════
$marca_id    = intval($_POST['marca_id']);
$modelo_id   = intval($_POST['modelo_id']);
$año         = intval($_POST['año']);
$vin         = strtoupper(trim($_POST['vin']));
$color       = trim($_POST['color']);
$precio      = floatval($_POST['precio']);
$estado      = in_array($_POST['estado'], ['nuevo', 'usado']) ? $_POST['estado'] : 'nuevo';
$kilometraje = intval($_POST['kilometraje']);
$fecha       = $_POST['fecha_ingreso'] ?? date('Y-m-d');

$vin_e    = $conexion->real_escape_string($vin);
$color_e  = $conexion->real_escape_string($color);
$estado_e = $conexion->real_escape_string($estado);

// ══════════════════════════════════════════════════════════
// 2. TRANSACCIÓN: actualizar vehículo + accesorios + pruebas
// ══════════════════════════════════════════════════════════
$conexion->begin_transaction();

try {

    // ─── 2.1 UPDATE del vehículo principal ───────────────
    $sql_update = "UPDATE vehiculo SET
        marca_id    = $marca_id,
        modelo_id   = $modelo_id,
        año         = $año,
        vin         = '$vin_e',
        color       = '$color_e',
        precio      = $precio,
        estado      = '$estado_e',
        kilometraje = $kilometraje,
        fecha_ingreso = '$fecha'
        WHERE id = $id";

    if (!$conexion->query($sql_update)) {
        throw new Exception("Error al actualizar vehículo: " . $conexion->error);
    }

    // ─── 2.2 SINCRONIZAR ACCESORIOS ──────────────────────
    // Estrategia: borrar todos los accesorios actuales del vehículo
    // y volver a insertar solo los que vienen marcados en el formulario.
    // Es más sencillo que comparar diferencias y evita duplicados.

    $conexion->query("DELETE FROM vehiculo_accesorio WHERE vehiculo_id = $id");

    if (!empty($_POST['accesorios']) && is_array($_POST['accesorios'])) {
        foreach ($_POST['accesorios'] as $accesorio_id) {
            $accesorio_id = intval($accesorio_id);
            $precio_inst  = floatval($_POST['instalacion'][$accesorio_id] ?? 0);

            $sql_acc = "INSERT INTO vehiculo_accesorio (vehiculo_id, accesorio_id, precio_instalacion)
                        VALUES ($id, $accesorio_id, $precio_inst)";

            if (!$conexion->query($sql_acc)) {
                throw new Exception("Error al guardar accesorio ID $accesorio_id: " . $conexion->error);
            }
        }
    }

    // ─── 2.3 SINCRONIZAR PRUEBAS DE MANEJO ───────────────
    // Los IDs a eliminar vienen en prueba_eliminar[]
    if (!empty($_POST['prueba_eliminar']) && is_array($_POST['prueba_eliminar'])) {
        foreach ($_POST['prueba_eliminar'] as $prueba_id_eliminar) {
            $prueba_id_eliminar = intval($prueba_id_eliminar);
            $conexion->query("DELETE FROM prueba_manejo WHERE id = $prueba_id_eliminar AND vehiculo_id = $id");
        }
    }

    // Recorrer todas las pruebas del formulario
    if (!empty($_POST['prueba_id']) && is_array($_POST['prueba_id'])) {
        $ids_eliminar = array_map('intval', $_POST['prueba_eliminar'] ?? []);
        $total = count($_POST['prueba_id']);

        for ($i = 0; $i < $total; $i++) {
            $prueba_id      = intval($_POST['prueba_id'][$i]);
            $cliente_id     = intval($_POST['prueba_cliente_id'][$i] ?? 0);
            $fecha_prueba   = $conexion->real_escape_string($_POST['prueba_fecha'][$i] ?? date('Y-m-d'));
            $hora_prueba    = $conexion->real_escape_string($_POST['prueba_hora'][$i] ?? '');
            $observaciones  = $conexion->real_escape_string($_POST['prueba_observaciones'][$i] ?? '');

            // Saltar si: no hay cliente, no hay hora, o está marcada para eliminar
            if ($cliente_id === 0 || empty($hora_prueba)) continue;
            if (in_array($prueba_id, $ids_eliminar)) continue;

            if ($prueba_id > 0) {
                // ── ACTUALIZAR prueba existente ──
                $sql_prueba = "UPDATE prueba_manejo SET
                    cliente_id     = $cliente_id,
                    fecha          = '$fecha_prueba',
                    hora           = '$hora_prueba',
                    observaciones  = '$observaciones'
                    WHERE id = $prueba_id AND vehiculo_id = $id";
            } else {
                // ── INSERTAR nueva prueba ──
                $sql_prueba = "INSERT INTO prueba_manejo (vehiculo_id, cliente_id, fecha, hora, observaciones)
                               VALUES ($id, $cliente_id, '$fecha_prueba', '$hora_prueba', '$observaciones')";
            }

            if (!$conexion->query($sql_prueba)) {
                throw new Exception("Error en prueba de manejo #$i: " . $conexion->error);
            }
        }
    }

    // ─── 2.4 Confirmar todo ───────────────────────────────
    $conexion->commit();
    header("Location: " . $base_url . "escritorio.php?msg=vehiculo_actualizado");
    exit;

} catch (Exception $e) {

    $conexion->rollback();
    error_log("Error al actualizar vehículo $id: " . $e->getMessage());
    header("Location: " . $base_url . "escritorio.php?error=actualizacion_fallida");
}

$conexion->close();
exit;
?>