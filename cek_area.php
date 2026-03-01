<?php
require_once __DIR__ . '/config/config.php';

echo "<h1>🔍 Database Diagnostic & Occupancy Fix</h1>";

try {
    // 1. Check tb_area_parkir structure
    echo "<h3>1. Checking Table Schema:</h3>";
    $stmt = $db->query("DESCRIBE tb_area_parkir");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in tb_area_parkir: " . implode(", ", $columns) . "<br>";
    
    if (!in_array('terisi', $columns)) {
        echo "<b style='color:red;'>MISSING 'terisi' column! Fixing...</b><br>";
        $db->query("ALTER TABLE tb_area_parkir ADD COLUMN terisi INT DEFAULT 0 AFTER kapasitas");
        echo "✅ Column 'terisi' added.<br>";
    } else {
        echo "✅ Column 'terisi' exists.<br>";
    }

    // 2. Check active transactions
    echo "<h3>2. Checking Active Transactions:</h3>";
    $stmt = $db->query("SELECT id_area, COUNT(*) as count FROM tb_transaksi WHERE status = 'masuk' GROUP BY id_area");
    $active_trx = $stmt->fetchAll();
    
    echo "Active 'masuk' transactions found:<br>";
    if (empty($active_trx)) {
        echo "<i>No active 'masuk' transactions found in tb_transaksi.</i><br>";
    } else {
        foreach ($active_trx as $row) {
            echo "Area ID {$row['id_area']}: {$row['count']} vehicles<br>";
        }
    }

    // 3. Resync Count
    echo "<h3>3. Resyncing 'terisi' Count:</h3>";
    // Reset all
    $db->query("UPDATE tb_area_parkir SET terisi = 0");
    
    // Sync from scratch
    $db->query("
        UPDATE tb_area_parkir a 
        SET a.terisi = (
            SELECT COUNT(*) 
            FROM tb_transaksi t 
            WHERE t.id_area = a.id_area AND t.status = 'masuk'
        )
    ");
    echo "✅ Occupancy resynced successfully!<br>";

    // 4. Final Verification
    $areas = $db->query("SELECT * FROM tb_area_parkir")->fetchAll();
    echo "<h3>4. Current Occupancy Status:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background:#f4f4f4;'><th>ID</th><th>Nama Area</th><th>Kapasitas</th><th>Terisi</th><th>Sisa Slot</th></tr>";
    foreach ($areas as $a) {
        $sisa = $a['kapasitas'] - $a['terisi'];
        echo "<tr>
                <td>{$a['id_area']}</td>
                <td>{$a['nama_area']}</td>
                <td>{$a['kapasitas']}</td>
                <td style='color:blue; font-weight:bold;'>{$a['terisi']}</td>
                <td style='color:green;'>{$sisa}</td>
              </tr>";
    }
    echo "</table>";
    
    echo "<br><a href='admin/area/index.php' style='padding:10px; background:blue; color:white; text-decoration:none;'>Go to Admin Area</a>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
