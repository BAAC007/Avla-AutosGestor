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
    <title>Inicio</title>
</head>

<body>
    <header>
        <nav>
            <div class="izquierda">
                <h1 class="logo">AVLA RACERS</h1>
            </div>
            <div class="centro">
                <a>Vehiculos</a>
                <a>Vender mi coche</a>
                <a>Alquilar coche</a>
            </div>
            <div class="derecha">
                <button class="registro" onclick="location.href='register.php'">
                    Registro
                </button>

                <button class="inicio" onclick="location.href='login.php'">
                    Login
                </button>
            </div>
        </nav>
    </header>
</body>

</html>