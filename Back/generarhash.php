<?php
$contrasena_actual = 'aqui_tu_contraseña_actual';
echo password_hash($contrasena_actual, PASSWORD_DEFAULT);
?>