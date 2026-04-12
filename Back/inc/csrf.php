<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_generar() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verificar() {
    $token_form    = $_POST['csrf_token'] ?? '';
    $token_session = $_SESSION['csrf_token'] ?? '';

    if (empty($token_form) || empty($token_session)) {
        return false;
    }

    return hash_equals($token_session, $token_form);
}

function csrf_campo_html() {
    $token = csrf_generar();
    return "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($token) . "'>";
}
?>