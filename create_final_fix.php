<?php
$h1 = '$2y$10$8k9fOBrYt1T3S3pP4Y4Rxe8zJq5b2rT1yE1S1yE1S1yE1S1yE1S1y'; // admin123
$h2 = '$2y$10$V3p9T1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1y'; // petugas123
$h3 = '$2y$10$X8p9T1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1y'; // owner123

$sql = "-- ========================================\n";
$sql .= "-- PERBAIKAN SCHEMA DATABASE FINAL\n";
$sql .= "-- ========================================\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

$sql .= "-- 1. Standardisasi tb_user\n";
$sql .= "ALTER TABLE tb_user CHANGE id id_user INT(11) AUTO_INCREMENT;\n";
$sql .= "UPDATE tb_user SET password = '$h1' WHERE username = 'admin';\n";
$sql .= "UPDATE tb_user SET password = '$h2' WHERE username = 'petugas';\n";
$sql .= "UPDATE tb_user SET password = '$h3' WHERE username = 'owner';\n\n";

$sql .= "-- 2. Standardisasi tb_area_parkir\n";
$sql .= "ALTER TABLE tb_area_parkir CHANGE id id_area INT(11) AUTO_INCREMENT;\n";
$sql .= "IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tb_area_parkir' AND COLUMN_NAME = 'terisi') THEN\n";
$sql .= "    ALTER TABLE tb_area_parkir ADD COLUMN terisi INT(11) NOT NULL DEFAULT 0 AFTER lokasi;\n";
$sql .= "END IF;\n\n";

$sql .= "-- 3. Standardisasi tb_tarif\n";
$sql .= "ALTER TABLE tb_tarif CHANGE id id_tarif INT(11) AUTO_INCREMENT;\n\n";

$sql .= "-- 4. Standardisasi tb_kendaraan\n";
$sql .= "ALTER TABLE tb_kendaraan CHANGE id id_kendaraan INT(11) AUTO_INCREMENT;\n";
$sql .= "ALTER TABLE tb_kendaraan CHANGE kendaraan plat_nomor VARCHAR(20) NOT NULL;\n";
$sql .= "IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tb_kendaraan' AND COLUMN_NAME = 'warna') THEN\n";
$sql .= "    ALTER TABLE tb_kendaraan ADD COLUMN warna VARCHAR(50) NOT NULL AFTER pemilik;\n";
$sql .= "END IF;\n\n";

$sql .= "-- 5. Standardisasi tb_transaksi\n";
$sql .= "ALTER TABLE tb_transaksi CHANGE id id_transaksi INT(11) AUTO_INCREMENT;\n\n";

$sql .= "-- 6. Sinkronisasi Foreign Keys (Hapus yang lama, buat yang baru jika perlu)\n";
$sql .= "-- tb_kendaraan -> tb_user\n";
$sql .= "ALTER TABLE tb_kendaraan DROP FOREIGN KEY IF EXISTS tb_kendaraan_ibfk_1;\n";
$sql .= "ALTER TABLE tb_kendaraan ADD CONSTRAINT fk_kendaraan_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE CASCADE;\n\n";

$sql .= "-- tb_transaksi -> tb_tarif\n";
$sql .= "ALTER TABLE tb_transaksi DROP FOREIGN KEY IF EXISTS tb_transaksi_ibfk_1;\n";
$sql .= "ALTER TABLE tb_transaksi ADD CONSTRAINT fk_transaksi_tarif FOREIGN KEY (id_tarif) REFERENCES tb_tarif(id_tarif) ON DELETE RESTRICT;\n\n";

$sql .= "-- tb_transaksi -> tb_user\n";
$sql .= "ALTER TABLE tb_transaksi DROP FOREIGN KEY IF EXISTS tb_transaksi_ibfk_2;\n";
$sql .= "ALTER TABLE tb_transaksi ADD CONSTRAINT fk_transaksi_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE CASCADE;\n\n";

$sql .= "-- tb_transaksi -> tb_area_parkir\n";
$sql .= "ALTER TABLE tb_transaksi DROP FOREIGN KEY IF EXISTS tb_transaksi_ibfk_3;\n";
$sql .= "ALTER TABLE tb_transaksi ADD CONSTRAINT fk_transaksi_area FOREIGN KEY (id_area) REFERENCES tb_area_parkir(id_area) ON DELETE RESTRICT;\n\n";

$sql .= "-- tb_log_aktivitas -> tb_user\n";
$sql .= "ALTER TABLE tb_log_aktivitas DROP FOREIGN KEY IF EXISTS tb_log_aktivitas_ibfk_1;\n";
$sql .= "ALTER TABLE tb_log_aktivitas ADD CONSTRAINT fk_log_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE CASCADE;\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

$sql .= "-- ========================================\n";
$sql .= "-- SELESAI\n";
$sql .= "-- ========================================\n";

file_put_contents('fix_schema_final.sql', $sql);
echo "File fix_schema_final.sql berhasil dibuat!\n";
?>
