<?php
// Back/inc/throttle.php
// Limita los intentos de login fallidos por usuario (requiere la tabla intento_login
// creada en migracion_v2.sql). Si la tabla aún no existe, no bloquea nada para no
// romper el login antes de migrar, pero deja el error en el log.

const LOGIN_MAX_FALLOS   = 5;   // fallos permitidos...
const LOGIN_VENTANA_MIN  = 15;  // ...dentro de estos minutos

function login_clave(string $usuario): string
{
    return substr(strtolower(trim($usuario)), 0, 50);
}

function login_ip(): string
{
    // Tras el proxy de Render REMOTE_ADDR es la IP del proxy; se guarda solo a efectos
    // de auditoría. El bloqueo se hace por usuario, no por IP.
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}

function login_bloqueado(mysqli $db, string $ambito, string $usuario): bool
{
    try {
        $clave = login_clave($usuario);
        $ventana = LOGIN_VENTANA_MIN;
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM intento_login
              WHERE ambito = ? AND usuario = ? AND fecha > (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->bind_param('ssi', $ambito, $clave, $ventana);
        $stmt->execute();
        $stmt->bind_result($n);
        $stmt->fetch();
        $stmt->close();
        return $n >= LOGIN_MAX_FALLOS;
    } catch (Throwable $e) {
        error_log('throttle: ' . $e->getMessage());
        return false;
    }
}

function login_registrar_fallo(mysqli $db, string $ambito, string $usuario): void
{
    try {
        $clave = login_clave($usuario);
        $ip = login_ip();
        $stmt = $db->prepare("INSERT INTO intento_login (ambito, usuario, ip) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $ambito, $clave, $ip);
        $stmt->execute();
        $stmt->close();
        // Mantener la tabla pequeña
        $db->query("DELETE FROM intento_login WHERE fecha < (NOW() - INTERVAL 1 DAY)");
    } catch (Throwable $e) {
        error_log('throttle: ' . $e->getMessage());
    }
}

function login_limpiar(mysqli $db, string $ambito, string $usuario): void
{
    try {
        $clave = login_clave($usuario);
        $stmt = $db->prepare("DELETE FROM intento_login WHERE ambito = ? AND usuario = ?");
        $stmt->bind_param('ss', $ambito, $clave);
        $stmt->execute();
        $stmt->close();
    } catch (Throwable $e) {
        error_log('throttle: ' . $e->getMessage());
    }
}

const LOGIN_MSG_BLOQUEO = 'Demasiados intentos fallidos. Espera unos minutos e inténtalo de nuevo.';
