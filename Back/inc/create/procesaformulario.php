<?php
// inc/create/procesaformulario.php
// Se ejecuta incluido desde admin/escritorio.php (accion=crear). No es accesible por web.
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

// Validar método y CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    volver_con_error('metodo_no_permitido');
}
if (!csrf_verificar()) {
    volver_con_error('csrf_invalido');
}

// ══════════════════════════════════════════════════════════
// 1. RECOGER Y VALIDAR DATOS
// ══════════════════════════════════════════════════════════
$marca_id    = intval($_POST['marca_id'] ?? 0);
$modelo_id   = intval($_POST['modelo_id'] ?? 0);
$anio        = intval($_POST['anio'] ?? 0);
$vin         = strtoupper(trim($_POST['vin'] ?? ''));
$color       = trim($_POST['color'] ?? '');
$precio      = floatval($_POST['precio'] ?? 0);
$estado      = in_array($_POST['estado'] ?? '', ['nuevo', 'usado'], true) ? $_POST['estado'] : 'nuevo';
$kilometraje = max(0, intval($_POST['kilometraje'] ?? 0));
$fecha_ingreso = fecha_valida($_POST['fecha_ingreso'] ?? '', date('Y-m-d'));

if ($marca_id <= 0 || $modelo_id <= 0 || $vin === '' || $color === '' || $precio <= 0) {
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
// 2. TRANSACCIÓN CON PREPARED STATEMENTS
// ══════════════════════════════════════════════════════════
$conexion->begin_transaction();

try {

    // ─── 2.1 INSERT del vehículo ─────────────────────────
    $stmt = $conexion->prepare("
        INSERT INTO vehiculo
            (marca_id, modelo_id, anio, vin, color, precio, estado, kilometraje, fecha_ingreso,
             matricula, combustible, transmision, potencia_cv, puertas, plazas, etiqueta_dgt,
             propietarios_anteriores, garantia_meses, descripcion, disponibilidad)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iiissdsis" . "sssiiisiiss",
        $marca_id, $modelo_id, $anio, $vin, $color, $precio, $estado, $kilometraje, $fecha_ingreso,
        $ficha['matricula'], $ficha['combustible'], $ficha['transmision'],
        $ficha['potencia_cv'], $ficha['puertas'], $ficha['plazas'], $ficha['etiqueta_dgt'],
        $ficha['propietarios_anteriores'], $ficha['garantia_meses'],
        $ficha['descripcion'], $ficha['disponibilidad']
    );
    $stmt->execute();
    $vehiculo_id = $conexion->insert_id;
    $stmt->close();

    // ─── 2.2 INSERT de accesorios ────────────────────────
    if (!empty($_POST['accesorios']) && is_array($_POST['accesorios'])) {
        $stmt_acc = $conexion->prepare("
            INSERT INTO vehiculo_accesorio (vehiculo_id, accesorio_id, precio_instalacion)
            VALUES (?, ?, ?)
        ");
        foreach ($_POST['accesorios'] as $accesorio_id) {
            $accesorio_id = intval($accesorio_id);
            $precio_inst  = max(0, floatval($_POST['instalacion'][$accesorio_id] ?? 0));
            $stmt_acc->bind_param("iid", $vehiculo_id, $accesorio_id, $precio_inst);
            $stmt_acc->execute();
        }
        $stmt_acc->close();
    }

    // ─── 2.3 INSERT de pruebas de manejo ─────────────────
    if (!empty($_POST['prueba_cliente_id']) && is_array($_POST['prueba_cliente_id'])) {
        $stmt_prueba = $conexion->prepare("
            INSERT INTO prueba_manejo (vehiculo_id, cliente_id, fecha, hora, observaciones)
            VALUES (?, ?, ?, ?, ?)
        ");
        $total_pruebas = count($_POST['prueba_cliente_id']);

        for ($i = 0; $i < $total_pruebas; $i++) {
            $cliente_id    = intval($_POST['prueba_cliente_id'][$i] ?? 0);
            $fecha_prueba  = fecha_valida($_POST['prueba_fecha'][$i] ?? '');
            $hora_prueba   = hora_valida($_POST['prueba_hora'][$i] ?? '');
            $observaciones = trim($_POST['prueba_observaciones'][$i] ?? '');

            if ($cliente_id <= 0 || $hora_prueba === null) {
                continue; // fila vacía: la sección es opcional
            }
            if ($fecha_prueba === null) {
                throw new InvalidArgumentException("Fecha de prueba #$i no válida");
            }
            $stmt_prueba->bind_param("iisss", $vehiculo_id, $cliente_id, $fecha_prueba, $hora_prueba, $observaciones);
            $stmt_prueba->execute();
        }
        $stmt_prueba->close();
    }

    // ─── 2.4 Todo OK ─────────────────────────────────────
    $conexion->commit();
    header("Location: " . $base_url . "escritorio.php?msg=vehiculo_creado");
    exit;

} catch (InvalidArgumentException $e) {
    $conexion->rollback();
    volver_con_error('datos_invalidos');

} catch (Throwable $e) {
    $conexion->rollback();
    error_log("Error al crear vehículo: " . $e->getMessage());

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
    volver_con_error('creacion_fallida');
}
