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
    <style>
        body,
        html {
            width: 100%;
            height: 100%;
            padding: 0px;
            margin: 0px;
            background: linear-gradient(135deg, #0e1c5a 0%, #2d2f3b 100%);
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 40px;
            background: #5e6065c6;
            justify-content: center;
            align-items: center;
            border-radius: 10%;
            width: 250px;
            height: 250px;
        }

        form h1{
            color: white;
        }

        form input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border-radius: 10px;
        }
    </style>
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