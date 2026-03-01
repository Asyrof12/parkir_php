<?php
/**
 * Authentication Middleware
 * Check apakah user sudah login dan memiliki role yang sesuai
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Check apakah user sudah login
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('danger', 'Silakan login terlebih dahulu!');
        redirect('auth/login.php');
    }
}

/**
 * Check apakah user memiliki role tertentu
 */
function require_role($allowed_roles) {
    require_login();
    
    if (!in_array($_SESSION['role'], (array)$allowed_roles)) {
        set_flash('danger', 'Anda tidak memiliki akses ke halaman ini!');
        redirect('index.php');
    }
}

/**
 * Check role Admin
 */
function require_admin() {
    require_role('admin');
}

/**
 * Check role Petugas
 */
function require_petugas() {
    require_role('petugas');
}

/**
 * Check role Owner
 */
function require_owner() {
    require_role('owner');
}
?>
