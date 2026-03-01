<?php
require_once __DIR__ . '/../config/config.php';

// Log activity sebelum logout
if (is_logged_in()) {
    log_activity($db, $_SESSION['id_user'], 'Logout dari sistem');
}

// Destroy session
session_destroy();

// Redirect ke login
set_flash('success', 'Anda telah logout');
redirect('auth/login.php');
?>
