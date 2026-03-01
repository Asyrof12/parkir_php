<?php
require_once __DIR__ . '/config/config.php';

echo "<h1>Occupancy Repair</h1>";

try {
    // Sync 'terisi' with actual 'masuk' status in tb_transaksi
    echo "Resyncing occupancy from transactions...<br>";
    
    // Reset all to 0 first
    $db->query("UPDATE tb_area_parkir SET terisi = 0");
    
    // Recalculate based on 'masuk' status
    $query = "UPDATE tb_area_parkir a 
              SET a.terisi = (
                  SELECT COUNT(*) 
                  FROM tb_transaksi t 
                  WHERE t.id_area = a.id_area AND t.status = 'masuk'
              )";
    $db->query($query);
    
    echo "✅ Success! Occupancy has been resynced with active transactions.<br>";
    
    $areas = $db->query("SELECT * FROM tb_area_parkir")->fetchAll();
    echo "<h2>Current State:</h2><table border='1'><tr><th>Area</th><th>Kapasitas</th><th>Terisi</th></tr>";
    foreach($areas as $a) {
        echo "<tr><td>{$a['nama_area']}</td><td>{$a['kapasitas']}</td><td>{$a['terisi']}</td></tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
