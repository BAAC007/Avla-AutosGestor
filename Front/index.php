<?php
session_start();
require_once 'config/database.php';

// Verificar si está logueado
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: login.php');
    exit();
}

$cliente_nombre = $_SESSION['cliente_nombre'] ?? 'Cliente';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Concesionario AVLA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-logout {
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #666;
            font-size: 18px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #667eea;
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 16px;
        }
        
        .quick-links {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .quick-links h3 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .link-btn {
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            transition: transform 0.2s;
            display: block;
        }
        
        .link-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🚗 Concesionario AVLA</h1>
        <div class="user-info">
            <span>👋 Bienvenido, <?php echo htmlspecialchars($cliente_nombre); ?></span>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h2>Panel de Control</h2>
            <p>Gestiona tus vehículos, compras y más</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3 id="vehiculos-count">0</h3>
                <p>Vehículos Disponibles</p>
            </div>
            <div class="stat-card">
                <h3 id="mis-compras">0</h3>
                <p>Mis Compras</p>
            </div>
            <div class="stat-card">
                <h3>⭐</h3>
                <p>Calificación</p>
            </div>
        </div>
        
        <div class="quick-links">
            <h3>Accesos Rápidos</h3>
            <div class="links-grid">
                <a href="#" class="link-btn">🔍 Buscar Vehículos</a>
                <a href="#" class="link-btn">📋 Mis Compras</a>
                <a href="#" class="link-btn">📅 Pruebas de Manejo</a>
                <a href="#" class="link-btn">⚙️ Mi Perfil</a>
            </div>
        </div>
    </div>
    
    <script>
        // Simular carga de datos
        document.addEventListener('DOMContentLoaded', function() {
            // Aquí cargarías los datos reales desde la base de datos
            document.getElementById('vehiculos-count').textContent = '156';
            document.getElementById('mis-compras').textContent = '3';
        });
    </script>
</body>
</html>