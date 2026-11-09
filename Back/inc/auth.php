<?php
// Back/inc/auth.php
// Control de acceso del panel de administración.
// Todo script que modifique datos del admin debe llamar a requerir_admin().

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function es_admin(): bool
{
    return !empty($_SESSION['admin_id']) && ($_SESSION['es_admin'] ?? false) === true;
}

/**
 * Corta la ejecución si no hay un administrador con sesión iniciada.
 */
function requerir_admin(): void
{
    if (es_admin()) {
        return;
    }
    header('Location: /admin/index.php?error=acceso_denegado');
    exit;
}

/**
 * true si el admin logueado tiene alguno de los roles indicados
 * (superadmin siempre tiene acceso).
 */
function admin_tiene_rol(string ...$roles): bool
{
    if (!es_admin()) {
        return false;
    }
    $rol = $_SESSION['admin_rol'] ?? 'gestor';
    return $rol === 'superadmin' || in_array($rol, $roles, true);
}

/**
 * Cierra la sesión por completo (datos + cookie).
 */
function cerrar_sesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
