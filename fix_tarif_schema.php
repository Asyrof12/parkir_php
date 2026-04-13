<?php
require_once __DIR__ . '/config/config.php';

try {
    echo "Starting database migration...\n";
    
    // 1. Alter tb_tarif to change jenis_kendaraan from ENUM to VARCHAR
    $sql = "ALTER TABLE tb_tarif MODIFY COLUMN jenis_kendaraan VARCHAR(50) NOT NULL";
    $db->exec($sql);
    echo "SUCCESS: tb_tarif.jenis_kendaraan changed to VARCHAR(50).\n";

    // 2. Also check tb_kendaraan just in case (it's already varchar(20) but let's make it consistent)
    $sql = "ALTER TABLE tb_kendaraan MODIFY COLUMN jenis_kendaraan VARCHAR(50) NOT NULL";
    $db->exec($sql);
    echo "SUCCESS: tb_kendaraan.jenis_kendaraan confirmed as VARCHAR(50).\n";

    echo "Migration completed successfully!\n";
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
