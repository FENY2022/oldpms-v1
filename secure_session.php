<?php
// Force HTTPS cookies and prevent JavaScript access
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'], 
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Start secure session
session_start();

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}
?>