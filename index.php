<?php
require_once __DIR__ . '/config/config.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (is_logged_in()) {
    $role = $_SESSION['role'];
    redirect($role . '/dashboard.php');
}

// Redirect ke login jika belum login
redirect('auth/login.php');
?>
