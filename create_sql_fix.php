<?php
$h1 = password_hash('admin123', PASSWORD_DEFAULT);
$h2 = password_hash('petugas123', PASSWORD_DEFAULT);
$h3 = password_hash('owner123', PASSWORD_DEFAULT);

$sql = "-- ========================================\n";
$sql .= "-- PERBAIKAN LOGIN & DATABASE\n";
$sql .= "-- ========================================\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
$sql .= "-- 1. Pastikan nama kolom adalah id_user\n";
$sql .= "ALTER TABLE tb_user CHANGE id id_user INT(11) AUTO_INCREMENT;\n\n";
$sql .= "-- 2. Update password user yang ada (tanpa hapus data lain)\n";
$sql .= "UPDATE tb_user SET password = '$h1' WHERE username = 'admin';\n";
$sql .= "UPDATE tb_user SET password = '$h2' WHERE username = 'petugas';\n";
$sql .= "UPDATE tb_user SET password = '$h3' WHERE username = 'owner';\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";
$sql .= "-- ========================================\n";

file_put_contents('update_users.sql', $sql);
echo "File update_users.sql berhasil diperbarui!\n";
?>
