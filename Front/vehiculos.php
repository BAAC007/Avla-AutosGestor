<?php
session_start();

if (isset($_GET['leng']) && in_array($_GET['leng'], ['es', 'en'])) {
    $_SESSION['leng'] = $_GET['leng'];
    header('Location: vehiculos.php');
    exit;
}

// Cargar idioma
$lang = $_SESSION['leng'] ?? 'es';
$t = json_decode(file_get_contents("leng/{$lang}.json"), true);

require_once dirname(__DIR__) . '/Back/db.php';

if (!isset($conexion) || !$conexion) {
    die("Error: No hay conexion a la base de datos");
}

$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
$cliente_nombre = $_SESSION['cliente_nombre'] ?? 'Cliente';
$cliente_id = $_SESSION['cliente_id'] ?? null;

// Filtros
$filtro_marca  = isset($_GET['marca'])  ? intval($_GET['marca'])  : 0;
$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado'])   : '';
$filtro_precio = isset($_GET['precio']) ? intval($_GET['precio']) : 0;
$filtro_buscar = isset($_GET['buscar']) ? trim($_GET['buscar'])   : '';

// Marcas para el selector
$marcas = [];
$res = $conexion->query("SELECT id, nombre FROM marca ORDER BY nombre");
if ($res) while ($f = $res->fetch_assoc()) $marcas[] = $f;

// Query con filtros dinamicos
$where  = ["v.estado IN ('nuevo', 'usado')"];
$params = [];
$tipos  = '';

if ($filtro_marca > 0) {
    $where[]  = 'v.marca_id = ?';
    $params[] = $filtro_marca;
    $tipos   .= 'i';
}
if ($filtro_estado !== '') {
    $where[]  = 'v.estado = ?';
    $params[] = $filtro_estado;
    $tipos   .= 's';
}
if ($filtro_precio > 0) {
    $where[]  = 'v.precio <= ?';
    $params[] = $filtro_precio;
    $tipos   .= 'i';
}
if ($filtro_buscar !== '') {
    $where[]       = '(m.nombre LIKE ? OR mo.nombre LIKE ? OR v.color LIKE ?)';
    $like          = '%' . $filtro_buscar . '%';
    $params[]      = $like;
    $params[]      = $like;
    $params[]      = $like;
    $tipos        .= 'sss';
}

$sql = "
    SELECT v.id, v.precio, v.anio, v.color, v.kilometraje, v.estado, v.imagen,
           m.nombre AS marca_nombre,
           mo.nombre AS modelo_nombre,
           mo.tipo AS tipo
    FROM vehiculo v
    INNER JOIN marca m   ON v.marca_id  = m.id
    INNER JOIN modelo mo ON v.modelo_id = mo.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY v.fecha_ingreso DESC
";

// MySQL usa el nombre de columna real: año (con tilde)
$sql = str_replace('v.anio', 'v.año', $sql);

$vehiculos = [];
if (!empty($params)) {
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tipos, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conexion->query($sql);
}
if ($res) while ($f = $res->fetch_assoc()) $vehiculos[] = $f;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehiculos - Concesionario AVLA</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="icon" href="imagenes/favicon/favicon.ico" type="image/x-icon">
    <link rel="icon" href="imagenes/Avlalogo.png" type="image/png">
    <style>
        .page-header {
            background: linear-gradient(135deg, #0e1c5a 0%, #2d2f3b 100%);
            color: white;
            padding: 50px 40px 40px;
            text-align: center;
        }

        .page-header h2 {
            font-size: 36px;
            margin-bottom: 8px;
        }

        .page-header p {
            font-size: 16px;
            opacity: .8;
        }

        .filtros-bar {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 20px 40px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
        }

        .filtros-bar label {
            display: flex;
            flex-direction: column;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            gap: 5px;
        }

        .filtros-bar select,
        .filtros-bar input[type="text"],
        .filtros-bar input[type="number"] {
            padding: 9px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            min-width: 160px;
            background: white;
        }

        .btn-filtrar {
            padding: 10px 24px;
            background: linear-gradient(135deg, #0e1c5a 0%, #2d2f3b 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: opacity .2s;
        }

        .btn-filtrar:hover {
            opacity: .85;
        }

        .btn-limpiar {
            padding: 10px 18px;
            background: white;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: border-color .2s;
        }

        .btn-limpiar:hover {
            border-color: #999;
        }

        .resultados-info {
            padding: 24px 40px 0;
            color: #666;
            font-size: 14px;
            max-width: 1400px;
            margin: 0 auto;
            border-top: 1px solid #e9ecef;
            margin-top: 0;
        }

        /* ── Grid ──────────────────────────────────────────── */
        .vehiculos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 28px;
            padding: 28px 40px 60px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ── Tarjeta ────────────────────────────────────────── */
        a.vehiculo-card {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8eaf0;
            transition: transform 0.25s, box-shadow 0.25s;
        }

        a.vehiculo-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.13);
        }

        /* Imagen */
        a.vehiculo-card .vehiculo-img {
            height: 210px;
            border-radius: 0;
            overflow: hidden;
            background: #e9ecef;
            flex-shrink: 0;
        }

        a.vehiculo-card .vehiculo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        a.vehiculo-card:hover .vehiculo-img img {
            transform: scale(1.04);
        }

        /* Info */
        a.vehiculo-card .vehiculo-info {
            padding: 18px 20px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
            border-top: 1px solid #f0f0f0;
        }

        a.vehiculo-card .vehiculo-marca {
            font-size: 22px;
            font-weight: 800;
            color: #1a1a2e;
            line-height: 1.1;
            margin-bottom: 2px;
        }

        a.vehiculo-card .vehiculo-modelo {
            font-size: 15px;
            color: #888;
            margin-bottom: 14px;
            font-weight: 400;
        }

        a.vehiculo-card .vehiculo-detalles {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 16px;
            margin-bottom: 16px;
        }

        a.vehiculo-card .detalle-label {
            font-size: 11px;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 2px;
        }

        a.vehiculo-card .detalle-value {
            font-size: 15px;
            font-weight: 700;
            color: #222;
        }

        a.vehiculo-card .vehiculo-precio {
            font-size: 30px;
            font-weight: 800;
            color: #667eea;
            margin: 4px 0 16px;
        }

        a.vehiculo-card .btn-ver-mas {
            display: block;
            background: linear-gradient(135deg, #0e1c5a 0%, #2d2f3b 100%);
            color: white;
            text-align: center;
            padding: 13px;
            border-radius: 7px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .3px;
            transition: opacity .2s;
            margin-top: auto;
        }

        a.vehiculo-card .btn-ver-mas:hover {
            opacity: .88;
        }

        /* Badge estado */
        .badge-estado {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .badge-nuevo {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-usado {
            background: #fff3e0;
            color: #e65100;
        }

        /* Sin resultados */
        .sin-resultados {
            text-align: center;
            padding: 80px 20px;
            color: #999;
            grid-column: 1 / -1;
        }

        .sin-resultados span {
            font-size: 48px;
            display: block;
            margin-bottom: 16px;
        }

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

    <div class="navbar">
        <button class="nav-toggle" aria-label="Menú" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <div class="nav-links">
            <a href="index.php#vehiculos"><?php echo $t['nav_vehiculos']; ?></a>
            <a href="index.php#servicios"><?php echo $t['nav_servicios']; ?></a>
            <a href="index.php#contacto"><?php echo $t['nav_contacto']; ?></a>
            <?php if ($logueado): ?>
                <a href="index.php#panel"><?php echo $t['nav_mi_panel']; ?></a>
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

    <div class="page-header">
        <h2>Nuestros Vehiculos</h2>
        <p>Encuentra el vehiculo perfecto entre toda nuestra oferta</p>
    </div>

    <form method="GET" action="vehiculos.php">
        <div class="filtros-bar">
            <label>Buscar
                <input type="text" name="buscar" placeholder="Marca, modelo, color..."
                    value="<?php echo htmlspecialchars($filtro_buscar); ?>">
            </label>
            <label>Marca
                <select name="marca">
                    <option value="0">Todas</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo $filtro_marca == $m['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Estado
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="nuevo" <?php echo $filtro_estado === 'nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                    <option value="usado" <?php echo $filtro_estado === 'usado' ? 'selected' : ''; ?>>Usado</option>
                </select>
            </label>
            <label>Precio maximo (euros)
                <input type="number" name="precio" min="0" step="1000"
                    placeholder="Sin limite"
                    value="<?php echo $filtro_precio > 0 ? $filtro_precio : ''; ?>">
            </label>
            <button type="submit" class="btn-filtrar">Filtrar</button>
            <a href="vehiculos.php" class="btn-limpiar">Limpiar</a>
        </div>
    </form>

    <div class="resultados-info">
        <?php $n = count($vehiculos);
        echo "$n veh" . ($n === 1 ? "iculo encontrado" : "iculos encontrados"); ?>
    </div>

    <div class="vehiculos-grid">
        <?php if (count($vehiculos) > 0): ?>
            <?php foreach ($vehiculos as $v): ?>
                <a href="vehiculo_detalle.php?id=<?php echo $v['id']; ?>" class="vehiculo-card">
                    <div class="vehiculo-img">
                        <?php if (!empty($v['imagen'])): ?>
                            <img src="<?php echo htmlspecialchars($v['imagen']); ?>"
                                alt="<?php echo htmlspecialchars($v['marca_nombre'] . ' ' . $v['modelo_nombre']); ?>">
                        <?php else: ?>
                            <span style="font-size:48px">&#x1F697;</span>
                        <?php endif; ?>
                    </div>
                    <div class="vehiculo-info">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px;">
                            <div class="vehiculo-marca"><?php echo htmlspecialchars($v['marca_nombre']); ?></div>
                            <span class="badge-estado badge-<?php echo $v['estado']; ?>">
                                <?php echo $v['estado'] === 'nuevo' ? 'Nuevo' : 'Usado'; ?>
                            </span>
                        </div>
                        <div class="vehiculo-modelo"><?php echo htmlspecialchars($v['modelo_nombre']); ?></div>
                        <div class="vehiculo-detalles">
                            <div class="detalle-item">
                                <span class="detalle-label">Año</span>
                                <span class="detalle-value"><?php echo $v['año']; ?></span>
                            </div>
                            <div class="detalle-item">
                                <span class="detalle-label">Kilometros</span>
                                <span class="detalle-value"><?php echo number_format($v['kilometraje'], 0, ',', '.'); ?> km</span>
                            </div>
                            <div class="detalle-item">
                                <span class="detalle-label">Color</span>
                                <span class="detalle-value"><?php echo htmlspecialchars($v['color']); ?></span>
                            </div>
                            <div class="detalle-item">
                                <span class="detalle-label">Tipo</span>
                                <span class="detalle-value"><?php echo htmlspecialchars($v['tipo'] ?? '-'); ?></span>
                            </div>
                        </div>
                        <div class="vehiculo-precio">&#8364;<?php echo number_format($v['precio'], 0, ',', '.'); ?></div>
                        <span class="btn-ver-mas">Ver Detalles</span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="sin-resultados">
                <span>&#x1F50D;</span>
                <p>No se encontraron vehiculos con esos filtros.</p>
                <a href="vehiculos.php" style="color:#0e1c5a;font-weight:600;">Ver todos los vehiculos</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; 2026 Concesionario AVLA. Todos los derechos reservados.</p>
        <p>Diseñado por Bryan Alejandro Avila Castro</p>
    </div>

    <script>
        (function() {
            var btn = document.querySelector('.nav-toggle');
            var nav = document.querySelector('.nav-links');
            if (btn && nav) {
                btn.addEventListener('click', function() {
                    nav.classList.toggle('open');
                    btn.setAttribute('aria-expanded', nav.classList.contains('open'));
                });
                nav.querySelectorAll('a').forEach(function(a) {
                    a.addEventListener('click', function() {
                        nav.classList.remove('open');
                        btn.setAttribute('aria-expanded', 'false');
                    });
                });
            }
        })();
    </script>
</body>

</html>