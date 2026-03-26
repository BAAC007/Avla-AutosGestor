<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: index.php?error=acceso_denegado");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <title>AVLA Autosgestor - Admin</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="/back-css/escritorio.css">
</head>
<body>
    <nav>
        <h2>Avla Autosgestor</h2>
        <a href="escritorio.php" class="nav-link">Vehículos</a>
        <a href="escritorio.php?accion=nuevo" class="nav-link">Nuevo vehículo</a>
        <div class="nav-spacer"></div>
        <a href="/" class="nav-link nav-link-secondary">Ver sitio (Frontend)</a>
        <a href="logout.php" class="nav-link nav-link-danger">Salir de admin</a>
    </nav>

    <main>
        <?php
        if (isset($_GET['error'])) {
            echo "<div class='alerta error'>";
            $errores = [
                'id_invalido'                  => 'ID de vehículo no válido',
                'eliminacion_fallida'          => 'No se pudo eliminar el vehículo',
                'tiene_pruebas_manejo'         => 'No se puede eliminar: este vehículo tiene pruebas de manejo registradas',
                'tiene_registros_relacionados' => 'No se puede eliminar: el vehículo tiene registros asociados',
            ];
            echo htmlspecialchars($errores[$_GET['error']] ?? 'Ocurrió un error');
            echo "</div>";
        }

        if (isset($_GET['msg'])) {
            echo "<div class='alerta exito'>";
            $mensajes = [
                'vehiculo_eliminado'   => 'Vehículo eliminado correctamente',
                'vehiculo_creado'      => 'Vehículo registrado exitosamente',
                'vehiculo_actualizado' => 'Vehículo actualizado correctamente',
            ];
            echo htmlspecialchars($mensajes[$_GET['msg']] ?? 'Operación exitosa');
            echo "</div>";
        }

        if (isset($_GET['accion'])) {
            if ($_GET['accion'] == "nuevo") {
                include "../inc/create/formulario.php";
            } elseif ($_GET['accion'] == "eliminar") {
                include "../inc/delete/eliminar.php";
            } elseif ($_GET['accion'] == "editar") {
                include "../inc/update/formularioactualizar.php";
            }
        } else {
            include "../inc/read/leer.php";
        }
        ?>

        <?php if (!isset($_GET['accion'])): ?>
            <a href="?accion=nuevo" id="nuevo">+</a>
        <?php endif; ?>
    </main>
</body>
</html>