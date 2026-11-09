<?php
/**
 * Herramienta de línea de comandos para las contraseñas de administrador.
 *
 *   php Back/tools/hash_admin_passwords.php            -> convierte a hash las contraseñas
 *                                                         que sigan en texto plano
 *   php Back/tools/hash_admin_passwords.php USUARIO    -> pide una contraseña nueva para USUARIO
 *
 * Lee las variables DB_* del entorno o, si existe, del archivo .env de la raíz del proyecto.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// Cargar .env local si existe (solo para uso en tu máquina)
$envFile = dirname(__DIR__, 2) . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        if ($linea[0] === '#' || strpos($linea, '=') === false) continue;
        [$k, $v] = explode('=', $linea, 2);
        if (getenv(trim($k)) === false) putenv(trim($k) . '=' . trim($v));
    }
}

require dirname(__DIR__) . '/db.php';

function es_hash(string $s): bool
{
    return (bool)preg_match('/^\$(2y|2a|2b|argon2i|argon2id)\$/', $s);
}

function leer_oculto(string $prompt): string
{
    echo $prompt;
    if (DIRECTORY_SEPARATOR === '/') system('stty -echo');
    $v = trim((string)fgets(STDIN));
    if (DIRECTORY_SEPARATOR === '/') system('stty echo');
    echo PHP_EOL;
    return $v;
}

$usuario = $argv[1] ?? null;

if ($usuario === null) {
    $res = $conexion->query("SELECT id, usuario, contrasena FROM administrador");
    $n = 0;
    while ($a = $res->fetch_assoc()) {
        if (es_hash($a['contrasena'])) {
            echo "  = {$a['usuario']}: ya tiene hash\n";
            continue;
        }
        $hash = password_hash($a['contrasena'], PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE administrador SET contrasena = ? WHERE id = ?");
        $stmt->bind_param('si', $hash, $a['id']);
        $stmt->execute();
        echo "  ✔ {$a['usuario']}: contraseña convertida a hash (sigue siendo la misma; cámbiala)\n";
        $n++;
    }
    echo "Hecho. $n contraseña(s) convertida(s).\n";
    exit(0);
}

$stmt = $conexion->prepare("SELECT id FROM administrador WHERE usuario = ?");
$stmt->bind_param('s', $usuario);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
if (!$fila) {
    fwrite(STDERR, "No existe el administrador '$usuario'.\n");
    exit(1);
}

$p1 = leer_oculto("Nueva contraseña para $usuario (mín. 10 caracteres): ");
$p2 = leer_oculto("Repite la contraseña: ");
if ($p1 !== $p2 || strlen($p1) < 10) {
    fwrite(STDERR, "Las contraseñas no coinciden o son demasiado cortas.\n");
    exit(1);
}
$hash = password_hash($p1, PASSWORD_DEFAULT);
$stmt = $conexion->prepare("UPDATE administrador SET contrasena = ? WHERE id = ?");
$stmt->bind_param('si', $hash, $fila['id']);
$stmt->execute();
echo "✔ Contraseña de $usuario actualizada.\n";
