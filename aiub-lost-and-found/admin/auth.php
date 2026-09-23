<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('ADMIN_USERNAME', 'vogoban');
define('ADMIN_PASSWORD', 'Ai*Q*U*VAEW*#@K');

function admin_is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function admin_require_login() {
    if (!admin_is_logged_in()) {
        header('Location: ' . str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']) . 'login.php');
        exit;
    }
}
