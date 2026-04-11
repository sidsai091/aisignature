<?php
session_start();

// Demo credentials — replace with DB lookup in production
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');

function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ../login.php');
        exit;
    }
}
