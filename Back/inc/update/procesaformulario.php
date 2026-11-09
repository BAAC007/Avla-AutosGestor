<?php
// inc/update/procesaformulario.php
// Se ejecuta incluido desde admin/escritorio.php (accion=actualizar). No es accesible por web.
require_once dirname(__DIR__) . '/auth.php';
requerir_admin();

include_once dirname(__DIR__, 2) . "/db.php";
require_once dirname(__DIR__) . '/csrf.php';
require_once dirname(__DIR__) . '/util.php';

$base_url = '/admin/';

function volver_con_error(string $codigo): void
{
    global $base_url;
    header("Location: " . $base_url . "escritorio.php?error=" . $codigo);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    volver_con_error('metodo_no_permitido');
}
if (!isset($_POST['id_vehiculo']) || !is_numeric($_POST['id_vehiculo'])) {
    volver_con_error('id_invalido');
}
if (!csrf_verificar()) {
    volver_con_error('csrf_invalido');
}

$id = intval($_POST['id_vehiculo']);

// ══════════════════════════════════════════════════════════
// 1. RECOGER Y VALIDAR DATOS DEL VEHÍCULO
// ══════════════════════════════════════════════════════════
$marca_id    = intval($_POST['marca_id'] ?? 0);
$modelo_id   = intval($_POST['modelo_id'] ?? 0);
$anio        = intval($_POST['anio'] ?? 0);
$vin         = strtoupper(trim($_POST['vin'] ?? ''));
$color       = trim($_POST['color'] ?? '');
$precio      = floatval($_POST['precio'] ?? 0);
$estado      = in_array($_POST['estado'] ?? '', ['nuevo', 'usado'], true) ? $_POST['estado'] : 'nuevo';
$kilometraje = max(0, intval($_POST['kilometraje'] ?? 0));
$fecha       = fecha_valida($_POST['fecha_ingreso'] ?? '', date('Y-m-d'));

if ($id <= 0 || $marca_id <= 0 || $modelo_id <= 0 || $color === '' || $precio <= 0) {
    volver_con_error('faltan_campos');
}
if (strlen($vin) !== 17) {
    volver_con_error('vin_invalido');
}
if ($anio < 1900 || $anio > 2100 || mb_strlen($color) > 50) {
    volver_con_error('datos_invalidos');
}

try {
    $ficha = vehiculo_ficha_desde_post($_POST);
} catch (InvalidArgumentException $e) {
    volver_con_error('datos_invalidos');
}

// ══════════════════════════════════════════════════════════
// 2. TRANSACCIÓN: actualizar vehículo + accesorios + pruebas
// ══════════════════════════════════════════════════════════
$conexion->begin_transaction();

try {

    // ─── 2.1 UPDATE del vehículo principal ───────────────
    $stmt = $conexion->prepare("
        UPDATE vehiculo SET
            marca_id = ?, modelo_id = ?, anio = ?, vin = ?, color = ?, precio = ?,
            estado = ?, kilometraje = ?, fecha_ingreso = ?,
            matricula = ?, combustible = ?, transmision = ?, potencia_cv = ?, puertas = ?,
            plazas = ?, etiqueta_dgt = ?, propietarios_anteriores = ?, garantia_meses = ?,
            descripcion = ?, disponibilidad = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        "iiissdsis" . "sssiiisiiss" . "i",
        $marca_id, $modelo_id, $anio, $vin, $color, $precio, $estado, $kilometraje, $fecha,
        $ficha['matricula'], $ficha['combustible'], $ficha['transmision'],
        $ficha['potencia_cv'], $ficha['puertas'], $ficha['plazas'], $ficha['etiqueta_dgt'],
        $ficha['propietarios_anteriores'], $ficha['garantia_meses'],
        $ficha['descripcion'], $ficha['disponibilidad'],
        $id
    );
    $stmt->execute();
    $stmt->close();

    // ─── 2.2 SINCRONIZAR ACCESORIOS ──────────────────────
    // Se borran los actuales y se insertan los marcados en el formulario.
    $stmt = $conexion->prepare("DELETE FROM vehiculo_accesorio WHERE vehiculo_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    if (!empty($_POST['accesorios']) && is_array($_POST['accesorios'])) {
        $stmt_acc = $conexion->prepare("
            INSERT INTO vehiculo_accesorio (vehiculo_id, accesorio_id, precio_instalacion)
            VALUES (?, ?, ?)
        ");
        foreach ($_POST['accesorios'] as $accesorio_id) {
            $accesorio_id = intval($accesorio_id);
            $precio_inst  = max(0, floatval($_POST['instalacion'][$accesorio_id] ?? 0));
            $stmt_acc->bind_param("iid", $id, $accesorio_id, $precio_inst);
            $stmt_acc->execute();
        }
        $stmt_acc->close();
    }

    // ─── 2.3 SINCRONIZAR PRUEBAS DE MANEJO ───────────────
    $ids_eliminar = array_map('intval', $_POST['prueba_eliminar'] ?? []);

    if ($ids_eliminar) {
        $stmt_del = $conexion->prepare("DELETE FROM prueba_manejo WHERE id = ? AND vehiculo_id = ?");
        foreach ($ids_eliminar as $prueba_id_eliminar) {
            $stmt_del->bind_param("ii", $prueba_id_eliminar, $id);
            $stmt_del->execute();
        }
        $stmt_del->close();
    }

    if (!empty($_POST['prueba_id']) && is_array($_POST['prueba_id'])) {
        $stmt_upd = $conexion->prepare("
            UPDATE prueba_manejo SET cliente_id = ?, fecha = ?, hora = ?, observaciones = ?
            WHERE id = ? AND vehiculo_id = ?
        ");
        $stmt_ins = $conexion->prepare("
            INSERT INTO prueba_manejo (vehiculo_id, cliente_id, fecha, hora, observaciones)
            VALUES (?, ?, ?, ?, ?)
        ");

        $total = count($_POST['prueba_id']);
        for ($i = 0; $i < $total; $i++) {
            $prueba_id     = intval($_POST['prueba_id'][$i]);
            $cliente_id    = intval($_POST['prueba_cliente_id'][$i] ?? 0);
            $fecha_prueba  = fecha_valida($_POST['prueba_fecha'][$i] ?? '');
            $hora_prueba   = hora_valida($_POST['prueba_hora'][$i] ?? '');
            $observaciones = trim($_POST['prueba_observaciones'][$i] ?? '');

            // Saltar si: no hay cliente, no hay hora, o está marcada para eliminar
            if ($cliente_id <= 0 || $hora_prueba === null) continue;
            if (in_array($prueba_id, $ids_eliminar, true)) continue;
            if ($fecha_prueba === null) {
                throw new InvalidArgumentException("Fecha de prueba #$i no válida");
            }

            if ($prueba_id > 0) {
                $stmt_upd->bind_param("isssii", $cliente_id, $fecha_prueba, $hora_prueba, $observaciones, $prueba_id, $id);
                $stmt_upd->execute();
            } else {
                $stmt_ins->bind_param("iisss", $id, $cliente_id, $fecha_prueba, $hora_prueba, $observaciones);
                $stmt_ins->execute();
            }
        }
        $stmt_upd->close();
        $stmt_ins->close();
    }

    // ─── 2.4 Confirmar todo ───────────────────────────────
    $conexion->commit();
    header("Location: " . $base_url . "escritorio.php?msg=vehiculo_actualizado");
    exit;

} catch (InvalidArgumentException $e) {
    $conexion->rollback();
    volver_con_error('datos_invalidos');

} catch (Throwable $e) {
    $conexion->rollback();
    error_log("Error al actualizar vehículo $id: " . $e->getMessage());

    if ((int)$e->getCode() === 1062) { // clave duplicada
        if (strpos($e->getMessage(), 'uq_prueba_slot') !== false) {
            volver_con_error('prueba_duplicada');
        }
        if (strpos($e->getMessage(), 'vin') !== false) {
            volver_con_error('vin_duplicado');
        }
        if (strpos($e->getMessage(), 'matricula') !== false) {
            volver_con_error('matricula_duplicada');
        }
    }
    volver_con_error('actualizacion_fallida');
}
