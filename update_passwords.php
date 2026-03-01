<?php
/**
 * Script untuk update password users dengan hash yang benar
 * JALANKAN FILE INI SEKALI SETELAH IMPORT DATABASE
 */

require_once __DIR__ . '/config/config.php';

echo "<h2>Update Password Users</h2>";

try {
    // Hash password
    $password_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $password_petugas = password_hash('petugas123', PASSWORD_DEFAULT);
    $password_owner = password_hash('owner123', PASSWORD_DEFAULT);

    // Update admin
    $query = "UPDATE tb_user SET password = :password WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':password', $password_admin);
    $stmt->execute();
    echo "<p>✅ Password admin berhasil diupdate (admin123)</p>";

    // Update petugas
    $query = "UPDATE tb_user SET password = :password WHERE username = 'petugas'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':password', $password_petugas);
    $stmt->execute();
    echo "<p>✅ Password petugas berhasil diupdate (petugas123)</p>";

    // Update owner
    $query = "UPDATE tb_user SET password = :password WHERE username = 'owner'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':password', $password_owner);
    $stmt->execute();
    echo "<p>✅ Password owner berhasil diupdate (owner123)</p>";

    echo "<hr>";
    echo "<h3>Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: admin | password: admin123</li>";
    echo "<li><strong>Petugas:</strong> username: petugas | password: petugas123</li>";
    echo "<li><strong>Owner:</strong> username: owner | password: owner123</li>";
    echo "</ul>";
    echo "<hr>";
    echo "<p><a href='index.php'>← Kembali ke Halaman Login</a></p>";
    echo "<p><strong>PENTING:</strong> Hapus file ini setelah selesai untuk keamanan!</p>";

} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
