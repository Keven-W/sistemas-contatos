<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

// Logout somente via POST com CSRF (evita logout forçado por links externos)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido($_POST['csrf_token'] ?? null)) {
    redirecionar('contatos.php');
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => 'Strict',
    ]);
}

session_destroy();

redirecionar('login.php');
