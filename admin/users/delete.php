<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth_check.php';
require_admin();

// Get user ID
$id = $_GET['id'] ?? null;
if (!$id) {
    set_flash('danger', 'ID user tidak valid!');
    redirect('admin/users/index.php');
}

try {
    // Get user data first
    $query = "SELECT username FROM tb_user WHERE id_user = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        set_flash('danger', 'User tidak ditemukan!');
        redirect('admin/users/index.php');
    }
    
    $user_data = $stmt->fetch();

    // Delete user
    $query = "DELETE FROM tb_user WHERE id_user = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        log_activity($db, $_SESSION['id_user'], "Menghapus user: " . $user_data['username']);
        set_flash('success', 'User berhasil dihapus!');
    } else {
        set_flash('danger', 'Gagal menghapus user!');
    }
} catch(PDOException $e) {
    set_flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
}

redirect('admin/users/index.php');
?>
