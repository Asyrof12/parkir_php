<?php
require_once __DIR__ . '/config/database.php';

function columnExists($db, $table, $column) {
    $stmt = $db->query("SHOW COLUMNS FROM $table LIKE '$column'");
    return $stmt->rowCount() > 0;
}

try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. tb_user
    if (columnExists($db, 'tb_user', 'id')) {
        $db->exec("ALTER TABLE tb_user CHANGE id id_user INT(11) AUTO_INCREMENT;");
        echo "Renamed id to id_user in tb_user<br>";
    }
    
    // Update passwords
    $h1 = '$2y$10$8k9fOBrYt1T3S3pP4Y4Rxe8zJq5b2rT1yE1S1yE1S1yE1S1yE1S1y';
    $h2 = '$2y$10$V3p9T1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1y';
    $h3 = '$2y$10$X8p9T1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1yE1S1y';
    
    $stmt = $db->prepare("UPDATE tb_user SET password = :p WHERE username = :u");
    $stmt->execute([':p' => $h1, ':u' => 'admin']);
    $stmt->execute([':p' => $h2, ':u' => 'petugas']);
    $stmt->execute([':p' => $h3, ':u' => 'owner']);
    echo "Passwords updated for admin, petugas, owner<br>";

    // 2. tb_area_parkir
    if (columnExists($db, 'tb_area_parkir', 'id')) {
        $db->exec("ALTER TABLE tb_area_parkir CHANGE id id_area INT(11) AUTO_INCREMENT;");
        echo "Renamed id to id_area in tb_area_parkir<br>";
    }
    if (!columnExists($db, 'tb_area_parkir', 'terisi')) {
        $db->exec("ALTER TABLE tb_area_parkir ADD COLUMN terisi INT(11) NOT NULL DEFAULT 0 AFTER lokasi;");
        echo "Added column terisi to tb_area_parkir<br>";
    }

    // 3. tb_tarif
    if (columnExists($db, 'tb_tarif', 'id')) {
        $db->exec("ALTER TABLE tb_tarif CHANGE id id_tarif INT(11) AUTO_INCREMENT;");
        echo "Renamed id to id_tarif in tb_tarif<br>";
    }

    // 4. tb_kendaraan
    if (columnExists($db, 'tb_kendaraan', 'id')) {
        $db->exec("ALTER TABLE tb_kendaraan CHANGE id id_kendaraan INT(11) AUTO_INCREMENT;");
        echo "Renamed id to id_kendaraan in tb_kendaraan<br>";
    }
    if (columnExists($db, 'tb_kendaraan', 'kendaraan')) {
        $db->exec("ALTER TABLE tb_kendaraan CHANGE kendaraan plat_nomor VARCHAR(20) NOT NULL;");
        echo "Renamed kendaraan to plat_nomor in tb_kendaraan<br>";
    }
    if (!columnExists($db, 'tb_kendaraan', 'warna')) {
        $db->exec("ALTER TABLE tb_kendaraan ADD COLUMN warna VARCHAR(50) NOT NULL AFTER pemilik;");
        echo "Added column warna to tb_kendaraan<br>";
    }

    // 5. tb_transaksi
    if (columnExists($db, 'tb_transaksi', 'id')) {
        $db->exec("ALTER TABLE tb_transaksi CHANGE id id_transaksi INT(11) AUTO_INCREMENT;");
        echo "Renamed id to id_transaksi in tb_transaksi<br>";
    }

    // 6. Constraints refreshing
    // Drop all possible FK names to avoid errors
    $fks_to_drop = [
        ['tb_kendaraan', 'tb_kendaraan_ibfk_1'],
        ['tb_kendaraan', 'fk_kendaraan_user'],
        ['tb_transaksi', 'tb_transaksi_ibfk_1'],
        ['tb_transaksi', 'fk_transaksi_tarif'],
        ['tb_transaksi', 'tb_transaksi_ibfk_2'],
        ['tb_transaksi', 'fk_transaksi_user'],
        ['tb_transaksi', 'tb_transaksi_ibfk_3'],
        ['tb_transaksi', 'fk_transaksi_area'],
        ['tb_log_aktivitas', 'tb_log_aktivitas_ibfk_1'],
        ['tb_log_aktivitas', 'fk_log_user']
    ];

    foreach($fks_to_drop as $fk) {
        try {
            $db->exec("ALTER TABLE {$fk[0]} DROP FOREIGN KEY {$fk[1]};");
        } catch(Exception $e) {}
    }

    // Add new constraints
    $db->exec("ALTER TABLE tb_kendaraan ADD CONSTRAINT fk_kendaraan_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE CASCADE;");
    $db->exec("ALTER TABLE tb_transaksi ADD CONSTRAINT fk_transaksi_tarif FOREIGN KEY (id_tarif) REFERENCES tb_tarif(id_tarif) ON DELETE RESTRICT;");
    $db->exec("ALTER TABLE tb_transaksi ADD CONSTRAINT fk_transaksi_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE CASCADE;");
    $db->exec("ALTER TABLE tb_transaksi ADD CONSTRAINT fk_transaksi_area FOREIGN KEY (id_area) REFERENCES tb_area_parkir(id_area) ON DELETE RESTRICT;");
    $db->exec("ALTER TABLE tb_log_aktivitas ADD CONSTRAINT fk_log_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE CASCADE;");
    echo "Constraints updated successfully<br>";

    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "<h3>Database Schema standardized successfully!</h3>";
    echo "<a href='admin/area/index.php'>Back to Area Management</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
