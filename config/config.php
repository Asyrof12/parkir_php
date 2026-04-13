<?php
/**
 * Konfigurasi Aplikasi
 */

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL
define('BASE_URL', 'http://localhost/parkir/');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Include database connection
require_once __DIR__ . '/database.php';

// Include helper functions
require_once __DIR__ . '/../includes/functions.php';

// Database instance
$database = new Database();
$db = $database->connect();
?>
