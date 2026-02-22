<?php
require_once __DIR__ . '/../config/config.php';
// Set session cookie to root path so it works everywhere (prevents redirect loops)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../app/router.php';

dispatch();
