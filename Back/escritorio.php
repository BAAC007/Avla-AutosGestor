 <?php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  session_start();

  // Verificar que exista la sesión de admin
  if (!isset($_SESSION['admin_id']) || !isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    // No es admin → redirigir al login
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
     <button>Buzon Cliente</button>
     <button>Promociones</button>
     <button>si?</button>
     <button onclick="window.location.href='logout.php'">Salir de admin</button>
     <button onclick="window.location.href='../Front/index.php'">Ver sitio (Frontend)</button>
   </nav>
   <main>
     <?php
      // Mensajes de ERROR
      if (isset($_GET['error'])) {
        echo "<div class='alerta error'>";
        $errores = [
          'id_invalido' => '❌ ID de vehículo no válido',
          'eliminacion_fallida' => '❌ No se pudo eliminar el vehículo',
          'tiene_pruebas_manejo' => '⚠️ No se puede eliminar: este vehículo tiene pruebas de manejo registradas',
          'tiene_registros_relacionados' => '⚠️ No se puede eliminar: el vehículo tiene registros asociados',
        ];
        echo htmlspecialchars($errores[$_GET['error']] ?? '❌ Ocurrió un error');
        echo "</div>";
      }

      // Mensajes de ÉXITO
      if (isset($_GET['msg'])) {
        echo "<div class='alerta exito'>";
        $mensajes = [
          'vehiculo_eliminado' => '✅ Vehículo eliminado correctamente',
          'vehiculo_creado' => '✅ Vehículo registrado exitosamente',
          'vehiculo_actualizado' => '✅ Vehículo actualizado correctamente',
        ];
        echo htmlspecialchars($mensajes[$_GET['msg']] ?? '✅ Operación exitosa');
        echo "</div>";
      }

      // Enrutador
      if (isset($_GET['accion'])) {
        if ($_GET['accion'] == "nuevo") {
          include "inc/create/formulario.php";
        } else if ($_GET['accion'] == "eliminar") {           // Defino la acción eliminar
          include "inc/delete/eliminar.php";              // En ese caso incluyo eliminar.php
        } else if ($_GET['accion'] == "editar") {             // Defino la acción editar
          include "inc/update/formularioactualizar.php";  // En ese caso incluyo el formulario de la edicion.php
        }
      } else {
        include "inc/read/leer.php";
      }
      ?>
     <!-- Solo mostrar botón + en la vista principal -->
     <?php if (!isset($_GET['accion'])): ?>
       <a href="?accion=nuevo" id="nuevo">+</a>
     <?php endif; ?>
   </main>
 </body>

 </html>