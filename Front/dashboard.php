<?php
// DEBUG: Mostrar errores en pantalla (SOLO PARA DESARROLLO)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Procesar formulario de nueva prueba de manejo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agendar_prueba'])) {
    
    // Asegurar charset correcto en la conexión
    if (method_exists($conexion, 'set_charset')) {
        $conexion->set_charset("utf8mb4");
    }
    
    $vehiculo_id = isset($_POST['vehiculo_id']) ? intval($_POST['vehiculo_id']) : 0;
    $fecha_raw = isset($_POST['fecha_prueba']) ? trim($_POST['fecha_prueba']) : '';
    $hora = $_POST['hora_prueba'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    // Debug extremo
    error_log("=== PROCESANDO PRUEBA ===");
    error_log("fecha_raw: '" . $fecha_raw . "' (len: " . strlen($fecha_raw) . ")");
    error_log("hora: '" . $hora . "'");
    error_log("vehiculo_id: " . $vehiculo_id);
    
    // Validación estricta de fecha
    $fecha = '';
    if (!empty($fecha_raw) && strlen($fecha_raw) === 10 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_raw)) {
        $date_obj = DateTime::createFromFormat('Y-m-d', $fecha_raw);
        if ($date_obj && $date_obj->format('Y-m-d') === $fecha_raw) {
            $fecha = $fecha_raw;
            error_log("Fecha validada: '" . $fecha . "'");
        } else {
            error_log("Fecha inválida (no existe): '" . $fecha_raw . "'");
        }
    } else {
        error_log("Formato de fecha incorrecto: '" . $fecha_raw . "'");
    }
    
    // Validaciones
    if ($vehiculo_id <= 0) {
        $error = "Selecciona un vehículo válido";
    } elseif (empty($fecha)) {
        $error = "Fecha inválida. Recibido: '" . htmlspecialchars($fecha_raw) . "'";
    } elseif (empty($hora)) {
        $error = "La hora es obligatoria";
    } else {
        // DEBUG: Mostrar exactamente qué se va a insertar
        error_log("Valores para INSERT:");
        error_log("  cliente_id: " . $cliente_id);
        error_log("  vehiculo_id: " . $vehiculo_id);
        error_log("  fecha: '" . $fecha . "' (bytes: " . bin2hex($fecha) . ")");
        error_log("  hora: '" . $hora . "'");
        error_log("  observaciones: '" . $observaciones . "'");
        
        try {
            // Crear copias explícitas para bind_param (evita problema de referencias)
            $cid = (int)$cliente_id;
            $vid = (int)$vehiculo_id;
            $f = (string)$fecha;
            $h = (string)$hora;
            $o = (string)$observaciones;
            
            error_log("Valores para bind_param:");
            error_log("  fecha_bind: '" . $f . "' (hex: " . bin2hex($f) . ")");
            
            $stmt = $conexion->prepare("INSERT INTO prueba_manejo (cliente_id, vehiculo_id, fecha, hora, observaciones) VALUES (?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conexion->error);
            }
            
            // Usar las COPIAS, no las variables originales
            $stmt->bind_param("iiiss", $cid, $vid, $f, $h, $o);
            
            error_log("Ejecutando INSERT con bind_param...");
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $mensaje = "Prueba de manejo agendada exitosamente";
            error_log("INSERT exitoso");
            $stmt->close();
            
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
            error_log("ERROR EXCEPTION: " . $e->getMessage());
            error_log("Stack: " . $e->getTraceAsString());
            if (isset($stmt) && $stmt) $stmt->close();
        }
    }
}

// Obtener datos del cliente
$stmt = $conexion->prepare("SELECT nombre, usuario, email, telefono, DNI_NIE FROM cliente WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener ventas del cliente (sus compras)
$ventas = [];
$stmt = $conexion->prepare("
    SELECT v.id, ve.marca_id, ve.modelo_id, ve.año, ve.color, ve.precio, v.fecha, v.forma_pago, v.estado 
    FROM venta v
    INNER JOIN vehiculo ve ON v.vehiculo_id = ve.id
    WHERE v.cliente_id = ? 
    ORDER BY v.fecha DESC
");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $marca_stmt = $conexion->prepare("SELECT nombre FROM marca WHERE id = ?");
    $marca_stmt->bind_param("i", $fila['marca_id']);
    $marca_stmt->execute();
    $marca = $marca_stmt->get_result()->fetch_assoc();
    $marca_stmt->close();
    
    $modelo_stmt = $conexion->prepare("SELECT nombre FROM modelo WHERE id = ?");
    $modelo_stmt->bind_param("i", $fila['modelo_id']);
    $modelo_stmt->execute();
    $modelo = $modelo_stmt->get_result()->fetch_assoc();
    $modelo_stmt->close();
    
    $fila['marca_nombre'] = $marca['nombre'] ?? '';
    $fila['modelo_nombre'] = $modelo['nombre'] ?? '';
    $ventas[] = $fila;
}
$stmt->close();

// Obtener pruebas de manejo del cliente
$pruebas = [];
$stmt = $conexion->prepare("
    SELECT pm.id, pm.fecha, pm.hora, pm.observaciones, v.año, m.nombre as modelo_nombre, ma.nombre as marca_nombre
    FROM prueba_manejo pm
    INNER JOIN vehiculo v ON pm.vehiculo_id = v.id
    INNER JOIN modelo m ON v.modelo_id = m.id
    INNER JOIN marca ma ON v.marca_id = ma.id
    WHERE pm.cliente_id = ? 
    ORDER BY pm.fecha DESC
");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $pruebas[] = $fila;
}
$stmt->close();

// Obtener vehículos disponibles para prueba de manejo
$vehiculos = [];
$stmt = $conexion->prepare("
    SELECT v.id, m.nombre as marca, mo.nombre as modelo, v.año, v.color, v.precio
    FROM vehiculo v
    INNER JOIN marca m ON v.marca_id = m.id
    INNER JOIN modelo mo ON v.modelo_id = mo.id
    WHERE v.estado = 'nuevo' OR v.estado = 'usado'
    ORDER BY m.nombre, mo.nombre
");
$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $vehiculos[] = $fila;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionario AVLA</title>
    <link rel="stylesheet" href="css/dashboard.css">
    
    <!-- Flatpickr CSS - URLs corregidas SIN espacios -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Flatpickr JS - URLs corregidas SIN espacios -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
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
                <table>
                    <thead>
                        <tr><th>Vehículo</th><th>Fecha</th><th>Precio</th><th>Forma de pago</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($venta['marca_nombre'] . ' ' . $venta['modelo_nombre'] . ' (' . $venta['año'] . ')'); ?>
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
            <?php endif; ?>
        </div>
        
        <!-- Sección: Mis pruebas de manejo -->
        <div class="section">
            <h2>Mis Pruebas de Manejo</h2>
            <?php if (empty($pruebas)): ?>
                <p class="empty">No tienes pruebas de manejo agendadas.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Vehículo</th><th>Fecha</th><th>Hora</th><th>Observaciones</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pruebas as $prueba): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prueba['marca_nombre'] . ' ' . $prueba['modelo_nombre']); ?>
                                <br><small style="color:#7f8c8d;"><?php echo htmlspecialchars($prueba['año']); ?></small>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($prueba['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($prueba['hora']); ?></td>
                            <td><?php echo htmlspecialchars($prueba['observaciones'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Sección: Agendar nueva prueba -->
        <div class="section">
            <h2>Agendar Nueva Prueba de Manejo</h2>
            <form method="POST">
                <div>
                    <label for="vehiculo_id">Vehículo *</label>
                    <select name="vehiculo_id" id="vehiculo_id" required>
                        <option value="">Seleccionar vehículo</option>
                        <?php foreach ($vehiculos as $vehiculo): ?>
                        <option value="<?php echo $vehiculo['id']; ?>">
                            <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' ' . $vehiculo['año'] . ' - ' . $vehiculo['color']); ?>
                            (<?php echo number_format($vehiculo['precio'], 2, ',', '.'); ?> €)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div>
                        <label for="fecha_prueba">Fecha *</label>
                        <!-- Input visible: SIN name, solo para mostrar el calendario -->
                        <input type="text"
                            id="fecha_prueba"
                            placeholder="Selecciona una fecha"
                            required
                            autocomplete="off"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;">
                        <!-- Input oculto: CON name, este es el que se envía al servidor -->
                        <input type="hidden" name="fecha_prueba" id="fecha_prueba_hidden">
                        <small id="fecha_debug" style="color:#7f8c8d;display:block;margin-top:5px;"></small>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fechaInput = document.getElementById('fecha_prueba');
        const hiddenInput = document.getElementById('fecha_prueba_hidden');
        const fechaDebug = document.getElementById('fecha_debug');
        
        function esFechaValida(fecha) {
            return fecha && fecha.length === 10 && /^\d{4}-\d{2}-\d{2}$/.test(fecha);
        }
        
        if (typeof flatpickr !== 'undefined') {
            const fp = flatpickr("#fecha_prueba", {
                locale: "es",
                minDate: "today",
                dateFormat: "Y-m-d",
                defaultDate: null,
                disableMobile: true,
                allowInput: false,
                clickOpens: true,
                
                onChange: function(selectedDates, dateStr, instance) {
                    // Actualizar hidden field con el valor formateado
                    if (hiddenInput) {
                        hiddenInput.value = dateStr;
                    }
                    
                    // Actualizar debug visual
                    if (fechaDebug) {
                        fechaDebug.textContent = 'Seleccionada: ' + dateStr + ' (longitud: ' + dateStr.length + ')';
                        fechaDebug.style.color = '#27ae60';
                    }
                    
                    console.log('Flatpickr onChange - Fecha:', dateStr, 'Longitud:', dateStr.length);
                },
                
                onClose: function(selectedDates, dateStr, instance) {
                    // Asegurar que el hidden field tenga el valor al cerrar
                    if (hiddenInput && dateStr) {
                        hiddenInput.value = dateStr;
                    }
                    console.log('Flatpickr onClose - Valor final:', dateStr);
                }
            });
            
            // Guardar referencia para debug en consola
            window.fp_instance = fp;
            
        } else {
            console.error('Flatpickr no está disponible');
            if (fechaDebug) {
                fechaDebug.textContent = 'Error: No se pudo cargar el selector de fecha';
                fechaDebug.style.color = '#e74c3c';
            }
        }
        
        // Validación antes de enviar el formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const fechaValue = hiddenInput ? hiddenInput.value.trim() : '';
            
            console.log('=== ANTES DE ENVIAR ===');
            console.log('Hidden field value:', JSON.stringify(fechaValue));
            console.log('Longitud:', fechaValue.length);
            console.log('Input visible value:', fechaInput ? fechaInput.value : '');
            
            if (!esFechaValida(fechaValue)) {
                e.preventDefault();
                const msg = 'Error: La fecha no tiene formato válido.\n\n' +
                    'Valor recibido: "' + fechaValue + '"\n' +
                    'Longitud: ' + fechaValue.length + '\n\n' +
                    'Formato esperado: AAAA-MM-DD (ej: 2026-03-15)\n\n' +
                    'Por favor selecciona una fecha del calendario.';
                alert(msg);
                if (fechaInput) fechaInput.focus();
                if (fechaDebug) {
                    fechaDebug.textContent = 'Fecha inválida - Usa el calendario';
                    fechaDebug.style.color = '#e74c3c';
                }
                return false;
            }
            
            // Confirmación final
            if (!confirm('¿Confirmas que deseas agendar esta prueba de manejo?')) {
                e.preventDefault();
            }
        });
        
        // Bloquear escritura manual en el input visible
        if (fechaInput) {
            fechaInput.addEventListener('keydown', function(e) {
                e.preventDefault();
                if (fechaInput.click) fechaInput.click();
            });
            fechaInput.addEventListener('paste', function(e) {
                e.preventDefault();
            });
        }
    });
    </script>
</body>
</html>