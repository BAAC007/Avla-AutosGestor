<?php
session_start();

// Cambio de idioma
if (isset($_GET['leng']) && in_array($_GET['leng'], ['es', 'en'])) {
    $_SESSION['leng'] = $_GET['leng'];
    header('Location: index.php');
    exit;
}

// Cargar idioma
$lang = $_SESSION['leng'] ?? 'es';
$t = json_decode(file_get_contents("leng/{$lang}.json"), true);

require_once dirname(__DIR__) . '/Back/db.php';

if (!isset($conexion) || !$conexion) {
    die("Error: No hay conexion a la base de datos");
}

$vehiculos = [];
$total_vehiculos = 0;

$marcas = [];
$total_marcas = 0;

$sql = "
    SELECT v.id, v.vin, v.precio, v.año, v.color, v.kilometraje, v.estado, v.imagen,
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
}

$sql_count = "SELECT COUNT(*) as total FROM vehiculo WHERE estado IN ('nuevo', 'usado')";
$resultado_count = $conexion->query($sql_count);
if ($resultado_count) {
    $total_vehiculos = $resultado_count->fetch_assoc()['total'];
}

$sql_count_marcas = "SELECT COUNT(*) as total FROM marca";
$resultado_count_marcas = $conexion->query($sql_count_marcas);
if ($resultado_count_marcas) {
    $total_marcas = $resultado_count_marcas->fetch_assoc()['total'];
}

$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
$cliente_nombre = $_SESSION['cliente_nombre'] ?? 'Cliente';
$cliente_id = $_SESSION['cliente_id'] ?? null;

$mis_compras = 0;
if ($logueado && $cliente_id) {
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM venta WHERE cliente_id = ? AND estado = 'completada'");
    if ($stmt) {
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $mis_compras = $res->fetch_assoc()['total'];
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionario AVLA</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="icon" type="image/png" href="imagenes/Avlalogo.png">
    <style>
        .leng-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .leng-selector a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-weight: 600;
            transition: color .2s;
        }

        .leng-selector a:hover {
            color: white;
        }

        .leng-selector a.active {
            color: white;
        }

        .leng-selector span {
            color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <button class="nav-toggle"
            aria-label="Menú"
            aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="nav-links">
            <a href="#vehiculos"><?php echo $t['nav_vehiculos']; ?></a>
            <a href="#servicios"><?php echo $t['nav_servicios']; ?></a>
            <a href="#contacto"><?php echo $t['nav_contacto']; ?></a>
            <?php if ($logueado): ?>
                <a href="#panel"><?php echo $t['nav_mi_panel']; ?></a>
            <?php endif; ?>
        </div>

        <a href="index.php" class="navbar-logo">
            <img src="imagenes/Avlalogo.png" alt="AVLA">
        </a>

        <div class="user-actions">
            <div class="leng-selector">
                <a href="?leng=es" <?php echo $lang === 'es' ? 'class="active"' : ''; ?>>ES</a>
                <span>|</span>
                <a href="?leng=en" <?php echo $lang === 'en' ? 'class="active"' : ''; ?>>EN</a>
            </div>
            <?php if ($logueado): ?>
                <a href="dashboard.php" class="btn-nombre-usuario"><?php echo htmlspecialchars($cliente_nombre); ?></a>
                <a href="logout.php" class="btn btn-logout"><?php echo $t['nav_cerrar']; ?></a>
            <?php else: ?>
                <a href="login.php" class="btn btn-login"><?php echo $t['nav_iniciar']; ?></a>
                <a href="register.php" class="btn btn-login" style="background:#ffffff;"><?php echo $t['nav_registrar']; ?></a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hero -->
    <div class="hero">
        <h2><?php echo $t['hero_titulo']; ?></h2>
        <p><?php echo $t['hero_subtitulo']; ?></p>
        <a href="#vehiculos" class="btn-hero"><?php echo $t['hero_btn_ver']; ?></a>
        <?php if (!$logueado): ?>
            <a href="register.php" class="btn-hero" style="background:#d1831c;"><?php echo $t['hero_btn_cuenta']; ?></a>
        <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_vehiculos; ?></div>
                <div class="stat-label"><?php echo $t['stats_disponibles']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_marcas; ?></div>
                <div class="stat-label"><?php echo $t['stats_marcas']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5</div>
                <div class="stat-label"><?php echo $t['stats_experiencia']; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">&#11088; 4.8</div>
                <div class="stat-label"><?php echo $t['stats_calificacion']; ?></div>
            </div>
        </div>
    </div>

    <!-- Panel usuario -->
    <?php if ($logueado): ?>
        <div class="section" id="panel">
            <div class="panel-usuario">
                <h3><?php echo $t['panel_bienvenido']; ?><?php echo htmlspecialchars($cliente_nombre); ?>!</h3>
                <div class="panel-stats">
                    <div class="panel-stat">
                        <div class="panel-stat-number"><?php echo $mis_compras; ?></div>
                        <div class="panel-stat-label"><?php echo $t['panel_compras']; ?></div>
                    </div>
                    <div class="panel-stat">
                        <div class="panel-stat-number">0</div>
                        <div class="panel-stat-label"><?php echo $t['panel_pruebas']; ?></div>
                    </div>
                    <div class="panel-stat">
                        <div class="panel-stat-number">&#11088;</div>
                        <div class="panel-stat-label"><?php echo $t['panel_calificacion']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Vehiculos -->
    <div class="section" id="vehiculos" style="background:#fff;">
        <div class="section-title">
            <h2><?php echo $t['vehiculos_titulo']; ?></h2>
            <p><?php echo $t['vehiculos_subtitulo']; ?></p>
        </div>
        <?php if (count($vehiculos) > 0): ?>
            <div class="Bryancarrusel">
                <?php foreach ($vehiculos as $vehiculo): ?>
                    <div class="vehiculo-card">
                        <div class="vehiculo-img">
                            <?php if (!empty($vehiculo['imagen'])): ?>
                                <img src="<?php echo htmlspecialchars($vehiculo['imagen']); ?>"
                                    alt="<?php echo htmlspecialchars($vehiculo['marca_nombre'] . ' ' . $vehiculo['modelo_nombre']); ?>">
                            <?php else: ?>
                                <span style="font-size:48px;">&#x1F697;</span>
                            <?php endif; ?>
                        </div>
                        <div class="vehiculo-info">
                            <div class="vehiculo-marca"><?php echo htmlspecialchars($vehiculo['marca_nombre']); ?></div>
                            <div class="vehiculo-modelo"><?php echo htmlspecialchars($vehiculo['modelo_nombre']); ?></div>
                            <div class="vehiculo-detalles">
                                <div class="detalle-item">
                                    <span class="detalle-label"><?php echo $t['detalle_anio']; ?></span>
                                    <span class="detalle-value"><?php echo htmlspecialchars($vehiculo['año']); ?></span>
                                </div>
                                <div class="detalle-item">
                                    <span class="detalle-label"><?php echo $t['detalle_km']; ?></span>
                                    <span class="detalle-value"><?php echo number_format($vehiculo['kilometraje'], 0, ',', '.'); ?> km</span>
                                </div>
                                <div class="detalle-item">
                                    <span class="detalle-label"><?php echo $t['detalle_color']; ?></span>
                                    <span class="detalle-value"><?php echo htmlspecialchars($vehiculo['color']); ?></span>
                                </div>
                                <div class="detalle-item">
                                    <span class="detalle-label"><?php echo $t['detalle_estado']; ?></span>
                                    <span class="detalle-value" style="color:<?php echo $vehiculo['estado'] === 'nuevo' ? '#4CAF50' : '#FF9800'; ?>">
                                        <?php echo $vehiculo['estado'] === 'nuevo' ? $t['estado_nuevo'] : $t['estado_usado']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="vehiculo-precio">
                                &euro;<?php echo number_format($vehiculo['precio'], 0, ',', '.'); ?>
                            </div>
                            <a href="vehiculo_detalle.php?id=<?php echo $vehiculo['id']; ?>" class="btn-ver-mas">
                                <?php echo $t['vehiculos_btn_detalles']; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;color:#666;"><?php echo $t['vehiculos_no_hay']; ?></p>
        <?php endif; ?>
        <div style="text-align:center;margin-top:40px;">
            <a href="vehiculos.php" class="btn-hero" style="background:linear-gradient(135deg,#0e1c5a 0%,#2d2f3b 100%);">
                <?php echo $t['vehiculos_btn_todos']; ?>
            </a>
        </div>
    </div>

    <!-- Servicios -->
    <div class="section" id="servicios" style="background:#f8f9fa;">
        <div class="section-title">
            <h2><?php echo $t['servicios_titulo']; ?></h2>
            <p><?php echo $t['servicios_subtitulo']; ?></p>
        </div>
        <div class="servicios-grid">
            <div class="stat-item">
                <div class="stat-number"><img src="imagenes/financiacion.png"></div>
                <div class="stat-label"><?php echo $t['servicio_financiacion']; ?></div>
                <p><?php echo $t['servicio_financiacion_desc']; ?></p>
            </div>
            <div class="stat-item">
                <div class="stat-number"><img src="imagenes/prueba_manejo.png"></div>
                <div class="stat-label"><?php echo $t['servicio_pruebas']; ?></div>
                <p><?php echo $t['servicio_pruebas_desc']; ?></p>
            </div>
            <div class="stat-item">
                <div class="stat-number"><img src="imagenes/garantia.png"></div>
                <div class="stat-label"><?php echo $t['servicio_garantia']; ?></div>
                <p><?php echo $t['servicio_garantia_desc']; ?></p>
            </div>
            <div class="stat-item">
                <div class="stat-number"><img src="imagenes/asistencia.png"></div>
                <div class="stat-label"><?php echo $t['servicio_asistencia']; ?></div>
                <p><?php echo $t['servicio_asistencia_desc']; ?></p>
            </div>
        </div>
    </div>

    <!-- Contacto -->
    <div class="section" id="contacto" style="background:white;">
        <div class="section-title">
            <h2><?php echo $t['contacto_titulo']; ?></h2>
            <p><?php echo $t['contacto_subtitulo']; ?></p>
        </div>
        <div style="max-width:600px;margin:0 auto;text-align:center;">
            <p style="font-size:18px;margin-bottom:20px;">
                <strong><?php echo $t['contacto_direccion']; ?>:</strong> Calle del Concesionario 123, Valencia<br>
                <strong><?php echo $t['contacto_telefono']; ?>:</strong> +34 96 123 45 67<br>
                <strong><?php echo $t['contacto_email']; ?>:</strong> info@concesionarioavla.com
            </p>
            <div style="margin-top:30px;">
                <a href="https://wa.me/34612345678" class="btn-hero" style="background:#25D366;">WhatsApp</a>
                <a href="mailto:info@concesionarioavla.com" class="btn-hero" style="background:#EA4335;">Email</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2026 Concesionario AVLA. <?php echo $t['footer_derechos']; ?></p>
        <p><?php echo $t['footer_disenado']; ?></p>
    </div>

    <style>
        .Bryancarrusel {
            width: 100%;
            overflow: hidden;
            position: relative;
            border-radius: 10px;
            margin: 0 auto;
        }

        .Bryancarrusel section {
            display: flex;
            flex-direction: row;
            transition: left 0.5s ease;
            position: relative;
            left: 0px;
        }

        .Bryancarrusel section .vehiculo-card {
            flex: 0 0 calc(33.333% - 20px);
            min-width: calc(33.333% - 20px);
            margin-right: 30px;
            display: block !important;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .Bryancarrusel section .vehiculo-card:last-child {
            margin-right: 0;
        }

        .Bryancarrusel section .vehiculo-card .vehiculo-img {
            display: block !important;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .Bryancarrusel section .vehiculo-card .vehiculo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .Bryancarrusel section .vehiculo-card .vehiculo-info {
            display: block !important;
            padding: 20px;
        }

        .Bryancarrusel section .vehiculo-card .btn-ver-mas {
            display: block;
            margin-top: 15px;
        }

        .carrusel-nav-btn {
            border: none;
            background: white;
            width: 46px;
            height: 46px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 50%;
            font-size: 28px;
            line-height: 1;
            text-align: center;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            border: 2px solid #0e1c5a;
            color: #0e1c5a;
            transition: background 0.2s, color 0.2s;
        }

        .carrusel-nav-btn:hover {
            background: #0e1c5a;
            color: white;
        }

        .carrusel-nav-btn:disabled {
            opacity: 0.25;
            cursor: default;
        }

        .carrusel-nav-btn.btn-prev {
            left: -20px;
        }

        .carrusel-nav-btn.btn-next {
            right: -20px;
        }
    </style>

    <script>
        (function() {
            var contenedor = document.querySelector('.Bryancarrusel');
            if (!contenedor) return;
            var tarjetas = Array.from(contenedor.querySelectorAll('.vehiculo-card'));
            tarjetas.forEach(function(t) {
                t.remove();
            });
            var track = document.createElement('section');
            track.style.left = '0px';
            tarjetas.forEach(function(t) {
                track.appendChild(t);
            });
            contenedor.appendChild(track);
            var VISIBLES = 3,
                contador = 0;

            function maxContador() {
                return Math.max(0, tarjetas.length - VISIBLES);
            }

            function mover(n) {
                contador = Math.max(0, Math.min(n, maxContador()));
                track.style.left = -(contador * (tarjetas[0].offsetWidth + 30)) + 'px';
                btnPrev.disabled = contador === 0;
                btnNext.disabled = contador >= maxContador();
            }
            var btnPrev = document.createElement('button');
            btnPrev.textContent = '\u2039';
            btnPrev.className = 'carrusel-nav-btn btn-prev';
            var btnNext = document.createElement('button');
            btnNext.textContent = '\u203a';
            btnNext.className = 'carrusel-nav-btn btn-next';
            btnPrev.onclick = function() {
                mover(contador - 1);
            };
            btnNext.onclick = function() {
                mover(contador + 1);
            };
            contenedor.appendChild(btnPrev);
            contenedor.appendChild(btnNext);
            mover(0);
        })();

        document.querySelectorAll('.nav-links a').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                var target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
    </script>
    <script>
        (function() {
            var btn = document.querySelector(
                '.nav-toggle');
            var nav = document.querySelector(
                '.nav-links');
            if (btn && nav) {
                btn.addEventListener('click',
                    function() {
                        nav.classList.toggle('open');
                    });
                nav.querySelectorAll('a')
                    .forEach(function(a) {
                        a.addEventListener('click',
                            function() {
                                nav.classList
                                    .remove('open');
                            });
                    });
            }
        })();
    </script>
</body>

</html>