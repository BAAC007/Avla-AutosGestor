<?php
session_start();
require_once dirname(__DIR__) . '/Back/db.php';

if (!isset($conexion) || !$conexion) {
    die("Error: No hay conexion a la base de datos");
}

$logueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
$cliente_nombre = $_SESSION['cliente_nombre'] ?? '';

// Validar ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: vehiculos.php');
    exit();
}

// Obtener datos completos del vehiculo
$stmt = $conexion->prepare("
    SELECT v.id, v.precio, v.año, v.color, v.kilometraje, v.estado, v.imagen, v.vin, v.fecha_ingreso,
           m.nombre  AS marca_nombre,
           m.pais_origen,
           mo.nombre AS modelo_nombre,
           mo.tipo   AS tipo
    FROM vehiculo v
    INNER JOIN marca m   ON v.marca_id  = m.id
    INNER JOIN modelo mo ON v.modelo_id = mo.id
    WHERE v.id = ? AND v.estado IN ('nuevo', 'usado')
");
$stmt->bind_param('i', $id);
$stmt->execute();
$v = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$v) {
    header('Location: vehiculos.php');
    exit();
}

// Vehiculos relacionados (misma marca, distinto id)
$stmt2 = $conexion->prepare("
    SELECT v.id, v.precio, v.año, v.color, v.estado, v.imagen,
           m.nombre AS marca_nombre, mo.nombre AS modelo_nombre
    FROM vehiculo v
    INNER JOIN marca m   ON v.marca_id  = m.id
    INNER JOIN modelo mo ON v.modelo_id = mo.id
    WHERE v.marca_id = (SELECT marca_id FROM vehiculo WHERE id = ?)
      AND v.id != ?
      AND v.estado IN ('nuevo', 'usado')
    LIMIT 3
");
$stmt2->bind_param('ii', $id, $id);
$stmt2->execute();
$relacionados = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($v['marca_nombre'] . ' ' . $v['modelo_nombre']); ?> - Concesionario AVLA</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        /* ── Detalle vehiculo ───────────────────────────── */
        .detalle-hero {
            background: #f8f9fa;
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .breadcrumb {
            font-size: 13px;
            color: #999;
            margin-bottom: 24px;
        }

        .breadcrumb a {
            color: #0e1c5a;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .detalle-layout {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 40px;
            align-items: start;
        }

        /* Imagen grande */
        .detalle-imagen {
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(135deg, #0e1c5a 0%, #2d2f3b 100%);
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .detalle-imagen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .detalle-imagen span {
            font-size: 80px;
        }

        /* Panel lateral */
        .detalle-panel {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 80px;
        }

        .detalle-panel .badge-estado {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .badge-nuevo {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-usado {
            background: #fff3e0;
            color: #e65100;
        }

        .detalle-panel .titulo-marca {
            font-size: 32px;
            font-weight: 800;
            color: #0e1c5a;
            line-height: 1;
        }

        .detalle-panel .titulo-modelo {
            font-size: 22px;
            color: #666;
            margin-bottom: 20px;
        }

        .detalle-panel .precio-grande {
            font-size: 42px;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 24px;
        }

        .specs-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 28px;
        }

        .spec-item {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
        }

        .spec-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 4px;
        }

        .spec-value {
            font-size: 16px;
            font-weight: 700;
            color: #333;
        }

        .btn-agendar {
            display: block;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #0e1c5a 0%, #2d2f3b 100%);
            color: white;
            text-align: center;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .2s, transform .2s;
            margin-bottom: 12px;
        }

        .btn-agendar:hover {
            opacity: .9;
            transform: translateY(-2px);
        }

        .btn-contactar {
            display: block;
            width: 100%;
            padding: 14px;
            background: white;
            color: #0e1c5a;
            text-align: center;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border: 2px solid #0e1c5a;
            transition: background .2s, color .2s;
            box-sizing: border-box;
        }

        .btn-contactar:hover {
            background: #0e1c5a;
            color: white;
        }

        /* Seccion info adicional */
        .detalle-info-extra {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px 60px;
        }

        .detalle-info-extra h3 {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .ficha-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        .ficha-table tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .ficha-table tr:last-child {
            border-bottom: none;
        }

        .ficha-table td {
            padding: 12px 16px;
        }

        .ficha-table td:first-child {
            color: #999;
            width: 200px;
            font-weight: 500;
        }

        .ficha-table td:last-child {
            color: #333;
            font-weight: 600;
        }

        .ficha-table tr:nth-child(even) td {
            background: #f9f9f9;
        }

        /* Relacionados */
        .relacionados {
            max-width: 1200px;
            margin: 0 auto 60px;
            padding: 0 40px;
        }

        .relacionados h3 {
            font-size: 22px;
            color: #333;
            margin-bottom: 24px;
        }

        .relacionados-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        a.vehiculo-card {
            text-decoration: none;
            color: inherit;
            display: block;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <h1 onclick="window.location.href='index.php'" style="cursor:pointer" id="avla-racers">Concesionario AVLA</h1>
        <div class="nav-links">
            <a href="index.php">Inicio</a>
            <a href="vehiculos.php">Vehiculos</a>
            <a href="index.php#servicios">Servicios</a>
            <a href="index.php#contacto">Contacto</a>
        </div>
        <div class="user-actions">
            <?php if ($logueado): ?>
                <span><?php echo htmlspecialchars($cliente_nombre); ?></span>
                <a href="dashboard.php" class="btn btn-login">Mi Panel</a>
                <a href="logout.php" class="btn btn-logout">Cerrar Sesion</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-login">Iniciar Sesion</a>
                <a href="register.php" class="btn btn-login" style="background:#fff">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="detalle-hero">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Inicio</a> &rsaquo;
            <a href="vehiculos.php">Vehiculos</a> &rsaquo;
            <?php echo htmlspecialchars($v['marca_nombre'] . ' ' . $v['modelo_nombre']); ?>
        </div>

        <div class="detalle-layout">

            <!-- Imagen -->
            <div>
                <div class="detalle-imagen">
                    <?php if (!empty($v['imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($v['imagen']); ?>"
                            alt="<?php echo htmlspecialchars($v['marca_nombre'] . ' ' . $v['modelo_nombre']); ?>">
                    <?php else: ?>
                        <span>&#x1F697;</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Panel lateral -->
            <div class="detalle-panel">
                <span class="badge-estado badge-<?php echo $v['estado']; ?>">
                    <?php echo $v['estado'] === 'nuevo' ? 'Nuevo' : 'Usado'; ?>
                </span>
                <div class="titulo-marca"><?php echo htmlspecialchars($v['marca_nombre']); ?></div>
                <div class="titulo-modelo"><?php echo htmlspecialchars($v['modelo_nombre']); ?></div>
                <div class="precio-grande">&#8364;<?php echo number_format($v['precio'], 0, ',', '.'); ?></div>

                <div class="specs-grid">
                    <div class="spec-item">
                        <div class="spec-label">Año</div>
                        <div class="spec-value"><?php echo $v['año']; ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Kilometros</div>
                        <div class="spec-value"><?php echo number_format($v['kilometraje'], 0, ',', '.'); ?> km</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Color</div>
                        <div class="spec-value"><?php echo htmlspecialchars($v['color']); ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Tipo</div>
                        <div class="spec-value"><?php echo htmlspecialchars($v['tipo'] ?? '-'); ?></div>
                    </div>
                </div>

                <?php if ($logueado): ?>
                    <a href="dashboard.php#agendar" class="btn-agendar">&#x1F697; Agendar Prueba de Manejo</a>
                <?php else: ?>
                    <a href="login.php" class="btn-agendar">Iniciar Sesion para Agendar</a>
                <?php endif; ?>
                <a href="index.php#contacto" class="btn-contactar">Contactar con un Asesor</a>
            </div>

        </div>
    </div>

    <!-- Ficha tecnica completa -->
    <div class="detalle-info-extra">
        <h3>Ficha Tecnica</h3>
        <table class="ficha-table">
            <tr>
                <td>Marca</td>
                <td><?php echo htmlspecialchars($v['marca_nombre']); ?></td>
            </tr>
            <tr>
                <td>Modelo</td>
                <td><?php echo htmlspecialchars($v['modelo_nombre']); ?></td>
            </tr>
            <tr>
                <td>Año</td>
                <td><?php echo $v['año']; ?></td>
            </tr>
            <tr>
                <td>Tipo</td>
                <td><?php echo htmlspecialchars($v['tipo'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td>Color</td>
                <td><?php echo htmlspecialchars($v['color']); ?></td>
            </tr>
            <tr>
                <td>Kilometraje</td>
                <td><?php echo number_format($v['kilometraje'], 0, ',', '.'); ?> km</td>
            </tr>
            <tr>
                <td>Estado</td>
                <td><?php echo $v['estado'] === 'nuevo' ? 'Nuevo' : 'Usado'; ?></td>
            </tr>
            <tr>
                <td>Pais de origen</td>
                <td><?php echo htmlspecialchars($v['pais_origen'] ?? '-'); ?></td>
            </tr>
            <tr>
                <td>VIN</td>
                <td><?php echo htmlspecialchars($v['vin']); ?></td>
            </tr>
            <tr>
                <td>Fecha de ingreso</td>
                <td><?php echo date('d/m/Y', strtotime($v['fecha_ingreso'])); ?></td>
            </tr>
            <tr>
                <td>Precio</td>
                <td style="color:#667eea;font-size:18px;">&#8364;<?php echo number_format($v['precio'], 2, ',', '.'); ?></td>
            </tr>
        </table>
    </div>

    <!-- Vehiculos relacionados -->
    <?php if (count($relacionados) > 0): ?>
        <div class="relacionados">
            <h3>Otros <?php echo htmlspecialchars($v['marca_nombre']); ?> disponibles</h3>
            <div class="relacionados-grid">
                <?php foreach ($relacionados as $r): ?>
                    <a href="vehiculo_detalle.php?id=<?php echo $r['id']; ?>" class="vehiculo-card">
                        <div class="vehiculo-img">
                            <?php if (!empty($r['imagen'])): ?>
                                <img src="<?php echo htmlspecialchars($r['imagen']); ?>"
                                    alt="<?php echo htmlspecialchars($r['marca_nombre'] . ' ' . $r['modelo_nombre']); ?>">
                            <?php else: ?>
                                <span style="font-size:48px">&#x1F697;</span>
                            <?php endif; ?>
                        </div>
                        <div class="vehiculo-info">
                            <div class="vehiculo-marca"><?php echo htmlspecialchars($r['marca_nombre']); ?></div>
                            <div class="vehiculo-modelo"><?php echo htmlspecialchars($r['modelo_nombre']); ?></div>
                            <div class="vehiculo-precio">&#8364;<?php echo number_format($r['precio'], 0, ',', '.'); ?></div>
                            <span class="btn-ver-mas">Ver Detalles</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>&copy; 2026 Concesionario AVLA. Todos los derechos reservados.</p>
        <p>Diseñado por Bryan Alejandro Avila Castro</p>
    </div>

</body>

</html>