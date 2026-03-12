<?php
session_start();
require_once dirname(__DIR__) . '/Back/db.php';

// Validar que $conexion exista
if (!isset($conexion) || !$conexion) {
    die("❌ Error: No hay conexión a la base de datos");
}

$vehiculos = [];
$total_vehiculos = 0;

// Obtener vehículos disponibles (público) - CONVERTIDO A MYSQLI
$sql = "
    SELECT v.id, v.vin, v.precio, v.año, v.color, v.kilometraje, v.estado,
           m.nombre as marca_nombre,
           mo.nombre as modelo_nombre,
           mo.tipo as modelo_tipo
    FROM vehiculo v
    INNER JOIN marca m ON v.marca_id = m.id
    INNER JOIN modelo mo ON v.modelo_id = mo.id
    WHERE v.estado IN ('nuevo', 'usado')
    ORDER BY v.fecha_ingreso DESC
    LIMIT 12
";

$resultado = $conexion->query($sql);
if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $vehiculos[] = $fila;
    }
} else {
    error_log("Error cargando vehículos: " . $conexion->error);
}

// Contar total de vehículos
$sql_count = "SELECT COUNT(*) as total FROM vehiculo WHERE estado IN ('nuevo', 'usado')";
$resultado_count = $conexion->query($sql_count);
if ($resultado_count) {
    $total_vehiculos = $resultado_count->fetch_assoc()['total'];
}

// Verificar si el usuario está logueado
$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
$cliente_nombre = $_SESSION['cliente_nombre'] ?? 'Cliente';
$cliente_id = $_SESSION['cliente_id'] ?? null;

// Si está logueado, obtener sus estadísticas - CONVERTIDO A MYSQLI
$mis_compras = 0;
if ($logueado && $cliente_id) {
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total 
        FROM venta 
        WHERE cliente_id = ? AND estado = 'completada'
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $mis_compras = $res->fetch_assoc()['total'];
        $stmt->close();
    } else {
        error_log("Error cargando compras: " . $conexion->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionario AVLA - Vehículos Nuevos y Usados</title>
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1 onclick="window.location.href='index.php'" style="cursor: pointer;" id="avla-racers">Concesionario AVLA</h1>
        <div class="nav-links">
            <a href="#vehiculos">Vehículos</a>
            <a href="#servicios">Servicios</a>
            <a href="#contacto">Contacto</a>
            <?php if ($logueado): ?>
                <a href="#panel">Mi Panel</a>
            <?php endif; ?>
        </div>
        <div class="user-actions">
            <?php if ($logueado): ?>
                <span><?php echo htmlspecialchars($cliente_nombre); ?></span>
                <a href="logout.php" class="btn btn-logout">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-login">Iniciar Sesión</a>
                <a href="register.php" class="btn btn-login" style="background: #ffffff;">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero">
        <h2>Encuentra tu próximo vehículo</h2>
        <p>Tenemos una amplia selección de vehículos nuevos y usados para satisfacer todas tus necesidades</p>
        <a href="#vehiculos" class="btn-hero">Ver Vehículos</a>
        <?php if (!$logueado): ?>
            <a href="register.php" class="btn-hero" style="background: #d1831c;">Crear Cuenta</a>
        <?php endif; ?>
    </div>

    <!-- Stats Section -->
    <div class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_vehiculos; ?></div>
                <div class="stat-label">Vehículos Disponibles</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">15+</div>
                <div class="stat-label">Marcas</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5</div>
                <div class="stat-label">Años de Experiencia</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">⭐ 4.8</div>
                <div class="stat-label">Calificación</div>
            </div>
        </div>
    </div>

    <!-- Panel de Usuario (solo visible si está logueado) -->
    <?php if ($logueado): ?>
        <div class="section" id="panel">
            <div class="panel-usuario">
                <h3>👋 Bienvenido, <?php echo htmlspecialchars($cliente_nombre); ?>!</h3>
                <div class="panel-stats">
                    <div class="panel-stat">
                        <div class="panel-stat-number"><?php echo $mis_compras; ?></div>
                        <div class="panel-stat-label">Compras Realizadas</div>
                    </div>
                    <div class="panel-stat">
                        <div class="panel-stat-number">0</div>
                        <div class="panel-stat-label">Pruebas de Manejo</div>
                    </div>
                    <div class="panel-stat">
                        <div class="panel-stat-number">⭐</div>
                        <div class="panel-stat-label">Tu Calificación</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Vehículos Section -->
    <div class="section" id="vehiculos" style="background: #fff;">
        <div class="section-title">
            <h2>Nuestros Vehículos</h2>
            <p>Descubre nuestra selección de vehículos nuevos y usados</p>
        </div>

        <?php if (count($vehiculos) > 0): ?>
            <div class="vehiculos-grid">
                <?php foreach ($vehiculos as $vehiculo): ?>
                    <div class="vehiculo-card">
                        <div class="vehiculo-img">
                            🚗
                        </div>
                        <div class="vehiculo-info">
                            <div class="vehiculo-marca"><?php echo htmlspecialchars($vehiculo['marca_nombre']); ?></div>
                            <div class="vehiculo-modelo"><?php echo htmlspecialchars($vehiculo['modelo_nombre']); ?></div>

                            <div class="vehiculo-detalles">
                                <div class="detalle-item">
                                    <span class="detalle-label">Año</span>
                                    <span class="detalle-value"><?php echo htmlspecialchars($vehiculo['año']); ?></span>
                                </div>
                                <div class="detalle-item">
                                    <span class="detalle-label">Kilómetros</span>
                                    <span class="detalle-value"><?php echo number_format($vehiculo['kilometraje'], 0, ',', '.'); ?> km</span>
                                </div>
                                <div class="detalle-item">
                                    <span class="detalle-label">Color</span>
                                    <span class="detalle-value"><?php echo htmlspecialchars($vehiculo['color']); ?></span>
                                </div>
                                <div class="detalle-item">
                                    <span class="detalle-label">Estado</span>
                                    <span class="detalle-value" style="color: <?php echo $vehiculo['estado'] === 'nuevo' ? '#4CAF50' : '#FF9800'; ?>">
                                        <?php echo $vehiculo['estado'] === 'nuevo' ? 'Nuevo' : 'Usado'; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="vehiculo-precio">
                                €<?php echo number_format($vehiculo['precio'], 0, ',', '.'); ?>
                            </div>

                            <a href="#" class="btn-ver-mas">Ver Detalles</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #666;">No hay vehículos disponibles en este momento.</p>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <a href="vehiculos.php" class="btn-hero" style="background: linear-gradient(135deg, #0e1c5a 0%, #2d2f3b 100%);">
                Ver Todos los Vehículos
            </a>
        </div>
    </div>

    <!-- Servicios Section -->
    <div class="section" id="servicios" style="background: #f8f9fa;">
        <div class="section-title">
            <h2>Nuestros Servicios</h2>
            <p>Te ofrecemos más que solo vehículos</p>
        </div>

        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">🔧</div>
                <div class="stat-label">Financiación</div>
                <p style="color: #666; margin-top: 10px;">Opciones flexibles de pago</p>
            </div>
            <div class="stat-item">
                <div class="stat-number">🚗</div>
                <div class="stat-label">Pruebas de Manejo</div>
                <p style="color: #666; margin-top: 10px;">Prueba antes de comprar</p>
            </div>
            <div class="stat-item">
                <div class="stat-number">⭐</div>
                <div class="stat-label">Garantía</div>
                <p style="color: #666; margin-top: 10px;">Garantía en todos los vehículos</p>
            </div>
            <div class="stat-item">
                <div class="stat-number">📞</div>
                <div class="stat-label">Asistencia 24/7</div>
                <p style="color: #666; margin-top: 10px;">Soporte cuando lo necesites</p>
            </div>
        </div>
    </div>

    <!-- Contacto Section -->
    <div class="section" id="contacto" style="background: white;">
        <div class="section-title">
            <h2>Contáctanos</h2>
            <p>Estamos aquí para ayudarte</p>
        </div>

        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <p style="font-size: 18px; margin-bottom: 20px;">
                <strong>📍 Dirección:</strong> Calle del Concesionario 123, Valencia<br>
                <strong>📱 Teléfono:</strong> +34 96 123 45 67<br>
                <strong>📧 Email:</strong> info@concesionarioavla.com
            </p>

            <div style="margin-top: 30px;">
                <a href="https://wa.me/34612345678" class="btn-hero" style="background: #25D366;">
                    WhatsApp
                </a>
                <a href="mailto:info@concesionarioavla.com" class="btn-hero" style="background: #EA4335;">
                    Email
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2026 Concesionario AVLA. Todos los derechos reservados.</p>
        <p>Diseñado por Bryan Alejandro Avila Castro</p>
    </div>

    <script>
        // Smooth scroll para los enlaces del navbar
        document.querySelectorAll('.nav-links a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

</html>