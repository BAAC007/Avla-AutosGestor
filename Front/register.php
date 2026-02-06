/*

Aqui debe ir la parte en php

*/

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
</head>

<body>
    <form action="register.php" method="POST">
        <div id="input">
            <input type="text" name="nombre" placeholder="Nombre">
        </div>
        <div id="input">
            <input type="text" name="apellidos" placeholder="Apellidos">
        </div>
        <div id="input">
            <input type="text" name="usuario" placeholder="Nombre de usuario">
        </div>
        <div id="input">
            <input type="password" name="clave" placeholder="Clave">
        </div>
        <div class="button">
            <button type="submit">Registrarse</button>
        </div>
    </form>
</body>

</html>