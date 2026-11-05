<?php
/**
 * Shared session bootstrap.
 *
 * HttpOnly and SameSite=Lax always. Secure is set only for HTTPS so a local
 * XAMPP site served over HTTP still receives the session cookie.
 */

ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
