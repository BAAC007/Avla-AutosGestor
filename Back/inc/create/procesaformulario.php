<?php
// Solo mostrar errores en desarrollo, nunca en producción
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include dirname(__DIR__, 2) . "/db.php";

$base_url = '/admin/';

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $base_url . "escritorio.php?error=metodo_no_permitido");
    exit;
}

// ══════════════════════════════════════════════════════════
// 1. RECOGER Y VALIDAR DATOS
// ══════════════════════════════════════════════════════════
$marca_id      = intval($_POST['marca_id'] ?? 0);
$modelo_id     = intval($_POST['modelo_id'] ?? 0);
$año           = intval($_POST['año'] ?? 0);
$vin           = strtoupper(trim($_POST['vin'] ?? ''));
$color         = trim($_POST['color'] ?? '');
$precio        = floatval($_POST['precio'] ?? 0);
$estado        = in_array($_POST['estado'] ?? '', ['nuevo', 'usado']) ? $_POST['estado'] : 'nuevo';
$kilometraje   = intval($_POST['kilometraje'] ?? 0);

// ✅ fecha_ingreso validada con formato correcto
$fecha_ingreso_raw = $_POST['fecha_ingreso'] ?? '';
$fecha_ingreso     = date('Y-m-d', strtotime($fecha_ingreso_raw)) ?: date('Y-m-d');

// Validaciones básicas
if (empty($marca_id) || empty($modelo_id) || empty($vin) || empty($color) || empty($precio)) {
    header("Location: " . $base_url . "escritorio.php?error=faltan_campos");
    exit;
}

if (strlen($vin) !== 17) {
    header("Location: " . $base_url . "escritorio.php?error=vin_invalido");
    exit;
}

// ══════════════════════════════════════════════════════════
// 2. TRANSACCIÓN CON PREPARED STATEMENTS
// ══════════════════════════════════════════════════════════
$conexion->begin_transaction();

try {

    // ─── 2.1 INSERT del vehículo con prepared statement ──
    $stmt = $conexion->prepare("
        INSERT INTO vehiculo (marca_id, modelo_id, año, vin, color, precio, estado, kilometraje, fecha_ingreso)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiissdsis", $marca_id, $modelo_id, $año, $vin, $color, $precio, $estado, $kilometraje, $fecha_ingreso);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al crear vehículo: " . $stmt->error);
    }

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
            $precio_inst  = floatval($_POST['instalacion'][$accesorio_id] ?? 0);

            $stmt_acc->bind_param("iid", $vehiculo_id, $accesorio_id, $precio_inst);

            if (!$stmt_acc->execute()) {
                throw new Exception("Error al añadir accesorio ID $accesorio_id: " . $stmt_acc->error);
            }
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
            $fecha_prueba  = date('Y-m-d', strtotime($_POST['prueba_fecha'][$i] ?? '')) ?: date('Y-m-d');
            $hora_prueba   = trim($_POST['prueba_hora'][$i] ?? '');
            $observaciones = trim($_POST['prueba_observaciones'][$i] ?? '');

            if ($cliente_id > 0 && !empty($hora_prueba)) {
                $stmt_prueba->bind_param("iisss", $vehiculo_id, $cliente_id, $fecha_prueba, $hora_prueba, $observaciones);

                if (!$stmt_prueba->execute()) {
                    throw new Exception("Error al añadir prueba de manejo #$i: " . $stmt_prueba->error);
                }
            }
        }
        $stmt_prueba->close();
    }

    // ─── 2.4 Todo OK ─────────────────────────────────────
    $conexion->commit();
    header("Location: " . $base_url . "escritorio.php?msg=vehiculo_creado");
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error al crear vehículo: " . $e->getMessage());
    header("Location: " . $base_url . "escritorio.php?error=creacion_fallida");
    exit;
}

$conexion->close();
exit;
?>