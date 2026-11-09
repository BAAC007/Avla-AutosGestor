<?php
session_start();
require_once dirname(__DIR__) . '/Back/db.php';

// Verificar que esté logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || !isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit();
}

$cliente_id = $_SESSION['cliente_id'];
$mensaje = '';
$error = '';

// Validar conexión
if (!isset($conexion) || !$conexion) {
    die("Error: No hay conexión a la base de datos");
}

require_once dirname(__DIR__) . '/Back/inc/csrf.php';
require_once dirname(__DIR__) . '/Back/inc/util.php';

// Procesar formulario de nueva prueba de manejo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_prueba'])) {

    $vehiculo_id   = intval($_POST['vehiculo_id'] ?? 0);
    $fecha         = fecha_valida($_POST['fecha_prueba'] ?? '');
    $hora          = hora_valida($_POST['hora_prueba'] ?? '');
    $observaciones = mb_substr(trim($_POST['observaciones'] ?? ''), 0, 500);

    if (!csrf_verificar()) {
        $error = "Token de seguridad inválido. Recarga la página e inténtalo de nuevo.";
    } elseif ($vehiculo_id <= 0) {
        $error = "Selecciona un vehículo válido";
    } elseif ($fecha === null) {
        $error = "La fecha no es válida";
    } elseif ($fecha < date('Y-m-d')) {
        $error = "La fecha no puede estar en el pasado";
    } elseif ($hora === null) {
        $error = "La hora es obligatoria";
    } else {
        // El vehículo debe existir y estar disponible (no reservado ni vendido)
        $chk = $conexion->prepare("SELECT disponibilidad FROM vehiculo WHERE id = ?");
        $chk->bind_param("i", $vehiculo_id);
        $chk->execute();
        $veh = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!$veh || $veh['disponibilidad'] !== 'disponible') {
            $error = "Ese vehículo no está disponible para pruebas de manejo";
        } else {
            try {
                $stmt = $conexion->prepare(
                    "INSERT INTO prueba_manejo (cliente_id, vehiculo_id, fecha, hora, observaciones) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("iisss", $cliente_id, $vehiculo_id, $fecha, $hora, $observaciones);
                $stmt->execute();
                $stmt->close();
                $mensaje = "Prueba de manejo agendada correctamente";
            } catch (mysqli_sql_exception $e) {
                if ((int)$e->getCode() === 1062) {
                    $error = "Ese horario ya está ocupado para este vehículo. Elige otra hora u otro día.";
                } else {
                    error_log("agendar_prueba: " . $e->getMessage());
                    $error = "No se pudo agendar la prueba. Inténtalo de nuevo.";
                }
            }
        }
    }
}

// Obtener datos del cliente
$stmt = $conexion->prepare("SELECT nombre, usuario, email, telefono, DNI_NIE FROM cliente WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener ventas del cliente (sus compras) en una sola consulta
$stmt = $conexion->prepare("
    SELECT v.id, ma.nombre AS marca_nombre, mo.nombre AS modelo_nombre, ve.anio, ve.color,
           v.precio_final AS precio, v.fecha, v.forma_pago, v.estado
    FROM venta v
    INNER JOIN vehiculo ve ON v.vehiculo_id = ve.id
    INNER JOIN marca ma    ON ve.marca_id   = ma.id
    INNER JOIN modelo mo   ON ve.modelo_id  = mo.id
    WHERE v.cliente_id = ?
    ORDER BY v.fecha DESC
");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener pruebas de manejo del cliente
$stmt = $conexion->prepare("
    SELECT pm.id, pm.fecha, pm.hora, pm.observaciones, pm.estado, v.anio,
           m.nombre AS modelo_nombre, ma.nombre AS marca_nombre
    FROM prueba_manejo pm
    INNER JOIN vehiculo v ON pm.vehiculo_id = v.id
    INNER JOIN modelo m   ON v.modelo_id = m.id
    INNER JOIN marca ma   ON v.marca_id  = ma.id
    WHERE pm.cliente_id = ?
    ORDER BY pm.fecha DESC, pm.hora DESC
");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$pruebas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Vehículos disponibles para prueba de manejo
$stmt = $conexion->prepare("
    SELECT v.id, m.nombre AS marca, mo.nombre AS modelo, v.anio, v.color, v.precio
    FROM vehiculo v
    INNER JOIN marca m   ON v.marca_id  = m.id
    INNER JOIN modelo mo ON v.modelo_id = mo.id
    WHERE v.disponibilidad = 'disponible'
    ORDER BY m.nombre, mo.nombre
");
$stmt->execute();
$vehiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionario AVLA</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="icon" href="imagenes/favicon/favicon.ico" type="image/x-icon">
    <link rel="icon" href="imagenes/Avlalogo.png" type="image/png">

</head>

<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>Hola, <?php echo htmlspecialchars($cliente['nombre']); ?></h1>
            <a id="index" href="index.php">Volver al inicio</a>
            <a id="logout" href="logout.php">Cerrar sesión</a>
        </div>

        <?php if ($error): ?>
            <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <!-- Sección: Información de cuenta -->
        <div class="section">
            <h2>Mi Información</h2>
            <div class="info-grid">
                <div class="info-item"><strong>Usuario</strong><span><?php echo htmlspecialchars($cliente['usuario']); ?></span></div>
                <div class="info-item"><strong>Nombre completo</strong><span><?php echo htmlspecialchars($cliente['nombre']); ?></span></div>
                <div class="info-item"><strong>Email</strong><span><?php echo htmlspecialchars($cliente['email']); ?></span></div>
                <div class="info-item"><strong>Teléfono</strong><span><?php echo htmlspecialchars($cliente['telefono']); ?></span></div>
                <div class="info-item"><strong>DNI/NIE</strong><span><?php echo htmlspecialchars($cliente['DNI_NIE']); ?></span></div>
            </div>
            <p style="margin-top:15px;"><a href="editar_perfil.php" style="color:#3498db;">Editar mi información</a></p>
        </div>

        <!-- Sección: Mis compras -->
        <div class="section">
            <h2>Mis Compras</h2>
            <?php if (empty($ventas)): ?>
                <p class="empty">Aún no has realizado compras.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Fecha</th>
                                <th>Precio</th>
                                <th>Forma de pago</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($venta['marca_nombre'] . ' ' . $venta['modelo_nombre'] . ' (' . $venta['anio'] . ')'); ?>
                                        <br><small style="color:#7f8c8d;"><?php echo htmlspecialchars($venta['color']); ?></small>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></td>
                                    <td><?php echo number_format($venta['precio'], 2, ',', '.'); ?> €</td>
                                    <td><?php echo htmlspecialchars($venta['forma_pago'] ?? '-'); ?></td>
                                    <td><?php if ($venta['estado']): ?><span class="estado <?php echo strtolower($venta['estado']); ?>"><?php echo htmlspecialchars($venta['estado']); ?></span>
                                        <?php else: ?><span class="estado pendiente">Sin estado</span><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección: Mis pruebas de manejo -->
        <div class="section">
            <h2>Mis Pruebas de Manejo</h2>
            <?php if (empty($pruebas)): ?>
                <p class="empty">No tienes pruebas de manejo agendadas.</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pruebas as $prueba): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prueba['marca_nombre'] . ' ' . $prueba['modelo_nombre']); ?>
                                        <br><small style="color:#7f8c8d;"><?php echo htmlspecialchars($prueba['anio']); ?></small>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($prueba['fecha'])); ?></td>
                                    <td><?php echo htmlspecialchars(substr($prueba['hora'], 0, 5)); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($prueba['estado'])); ?></td>
                                    <td><?php echo htmlspecialchars($prueba['observaciones'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección: Agendar nueva prueba -->
        <div class="section">
            <h2>Agendar Nueva Prueba de Manejo</h2>
            <form method="POST">
                <?php echo csrf_campo_html(); ?>
                <div>
                    <label for="vehiculo_id">Vehículo *</label>
                    <select name="vehiculo_id" id="vehiculo_id" required>
                        <option value="">Seleccionar vehículo</option>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                            <option value="<?php echo $vehiculo['id']; ?>">
                                <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' ' . $vehiculo['anio'] . ' - ' . $vehiculo['color']); ?>
                                (<?php echo number_format($vehiculo['precio'], 2, ',', '.'); ?> €)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div>
                        <label for="fecha_prueba">Fecha *</label>
                        <input type="date"
                            name="fecha_prueba"
                            id="fecha_prueba"
                            required
                            min="<?php echo date('Y-m-d'); ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
                    </div>
                    <div>
                        <label for="hora_prueba">Hora *</label>
                        <select name="hora_prueba" id="hora_prueba" required>
                            <option value="">Seleccionar hora</option>
                            <option value="09:00:00">09:00 AM</option>
                            <option value="10:00:00">10:00 AM</option>
                            <option value="11:00:00">11:00 AM</option>
                            <option value="12:00:00">12:00 PM</option>
                            <option value="15:00:00">03:00 PM</option>
                            <option value="16:00:00">04:00 PM</option>
                            <option value="17:00:00">05:00 PM</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="observaciones">Observaciones (opcional)</label>
                    <textarea name="observaciones" id="observaciones" rows="3" placeholder="Ej: Quiero probar el sistema de frenos..."></textarea>
                </div>
                <button type="submit" name="agendar_prueba">Agendar Prueba</button>
            </form>
        </div>
    </div>

</body>

</html>