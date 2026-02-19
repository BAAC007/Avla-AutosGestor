 <?php
    session_start();

    if (!isset($_SESSION['usuario'])) {
        header("Location: index.php");
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
      <button><a href="index.php">Logout from admin</a></button>
      <button><a href="../Front/index.php">Ver sitio (Frontend)</a></button>
    </nav>
    <main>
    	<?php
      	// Esto se conoce como router (enrutador) /////////////
      	if(isset($_GET['accion'])){
        	if($_GET['accion'] == "nuevo"){
          	include "inc/create/formulario.php";
          }else if($_GET['accion'] == "eliminar"){ 					// Defino la acción eliminar
          	include "inc/delete/eliminar.php";							// En ese caso incluyo eliminar.php
          }else if($_GET['accion'] == "editar"){ 						// Defino la acción editar
          	include "inc/update/formularioactualizar.php";	// En ese caso incluyo el formulario de la edicion.php
          }
        }else{
      		include "inc/read/leer.php"; 
        }
      ?>
      <a href="?accion=nuevo" id="nuevo">+</a>
    </main>
  </body>
</html>