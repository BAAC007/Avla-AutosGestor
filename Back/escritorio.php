<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificar que exista la sesion de admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
  header("Location: index.php?error=acceso_denegado");
  exit;
}
?>

<!doctype html>
<html lang="es">

<head>
  <title>AVLA autosgestor</title>
  <meta charset="utf-8">
  <link rel="stylesheet" href="css/escritorio.css">
</head>

<body>
  <nav>
    <h2>Avla autosgestor</h2>

    <a href="escritorio.php" class="nav-link">Vehiculos</a>
    <a href="escritorio.php?accion=nuevo" class="nav-link">Nuevo vehiculo</a>

    <div class="nav-spacer"></div>

    <a href="../Front/index.php" class="nav-link nav-link-secondary">Ver sitio (Frontend)</a>
    <a href="logout.php" class="nav-link nav-link-danger">Salir de admin</a>
  </nav>

  <main>
    <?php
    // Mensajes de ERROR
    if (isset($_GET['error'])) {
      echo "<div class='alerta error'>";
      $errores = [
        'id_invalido'               => 'ID de vehiculo no valido',
        'eliminacion_fallida'       => 'No se pudo eliminar el vehiculo',
        'tiene_pruebas_manejo'      => 'No se puede eliminar: este vehiculo tiene pruebas de manejo registradas',
        'tiene_registros_relacionados' => 'No se puede eliminar: el vehiculo tiene registros asociados',
      ];
      echo htmlspecialchars($errores[$_GET['error']] ?? 'Ocurrio un error');
      echo "</div>";
    }

    // Mensajes de EXITO
    if (isset($_GET['msg'])) {
      echo "<div class='alerta exito'>";
      $mensajes = [
        'vehiculo_eliminado'   => 'Vehiculo eliminado correctamente',
        'vehiculo_creado'      => 'Vehiculo registrado exitosamente',
        'vehiculo_actualizado' => 'Vehiculo actualizado correctamente',
      ];
      echo htmlspecialchars($mensajes[$_GET['msg']] ?? 'Operacion exitosa');
      echo "</div>";
    }

    // Enrutador
    if (isset($_GET['accion'])) {
      if ($_GET['accion'] == "nuevo") {
        include "inc/create/formulario.php";
      } else if ($_GET['accion'] == "eliminar") {
        include "inc/delete/eliminar.php";
      } else if ($_GET['accion'] == "editar") {
        include "inc/update/formularioactualizar.php";
      }
    } else {
      include "inc/read/leer.php";
    }
    ?>

    <?php if (!isset($_GET['accion'])): ?>
      <a href="?accion=nuevo" id="nuevo">+</a>
    <?php endif; ?>
  </main>

</body>

</html>