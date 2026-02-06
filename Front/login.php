<?

/*

En este apartado debe ir el codigo php, que permitira
conectarnos con la base de datos

*/

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de sesion</title>
</head>

<body>
    <form action="login.php" method="POST">
        <h1>Inicio de sesion</h1>
        <div id="input">
            <input type="text" name="usuario" placeholder="Nombre de usuario">
        </div>
        <div id="input">
            <input type="password" name="clave" placeholder="Clave">
        </div>
        <div class="button">
            <button type="submit">Iniciar sesion</button>
        </div>
    </form>
</body>

</html>