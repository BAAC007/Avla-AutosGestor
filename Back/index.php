<?php
session_start();

/* Når login-formen sendes */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['usuario'] = $_POST['usuario'] ?? 'admin';
    header("Location: escritorio.php");
    exit;
}
?>




<!doctype html>
<html lang="es">

<head>
    <title>AVLA autosgestor</title>
    <meta charset="utf-8">
</head>

<body>
    <form method="POST">
        <h1>Avla ADMINS</h1>

        <input type="text" name="usuario" placeholder="usuario">
        <input type="password" name="contrasena" placeholder="contraseña">
        <input type="submit">
    </form>
</body>

</html>