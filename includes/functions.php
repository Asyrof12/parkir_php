<?php
/**
 * Helper Functions
 */

/**
 * Redirect ke URL tertentu
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

/**
 * Set flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Get dan hapus flash message
 */
function get_flash() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'];
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return ['type' => $type, 'message' => $message];
    }
    return null;
}

/**
 * Format currency Rupiah
 */
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Format datetime untuk Indonesia
 */
function format_datetime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Format date untuk Indonesia
 */
function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Check apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['id_user']) && isset($_SESSION['role']);
}

/**
 * Check role user
 */
function check_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Get user info dari session
 */
function get_user_info() {
    return [
        'id_user' => $_SESSION['id_user'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'nama' => $_SESSION['nama_lengkap'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}


/**
 * Sanitize input
 */
function clean_input($data) {
    $data = trim((string)$data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Log aktivitas user
 */
function log_activity($db, $id_user, $aktivitas) {
    try {
        $query = "INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (:id_user, :aktivitas)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_user', $id_user);
        $stmt->bindParam(':aktivitas', $aktivitas);
        $stmt->execute();
    } catch(PDOException $e) {
        // Silent fail untuk log
    }
}

/**
 * Hitung durasi parkir dalam jam (dibulatkan ke atas)
 */
function hitung_durasi($waktu_masuk, $waktu_keluar) {
    $masuk = strtotime($waktu_masuk);
    $keluar = strtotime($waktu_keluar);
    $selisih = $keluar - $masuk;

    // Gratis jika keluar dalam waktu <= 5 menit (300 detik)
    if ($selisih <= 300) {
        return 0;
    }

    $jam = ceil($selisih / 3600); // Dibulatkan ke atas
    return $jam > 0 ? $jam : 1; // Minimal 1 jam
}
?>
