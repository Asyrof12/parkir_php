<?php
require_once __DIR__ . '/config/config.php';

echo "<h1>Database Debug</h1>";
echo "Database Name: " . "parkir_wisnu_db" . "<br>";

try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_user");
    $count = $stmt->fetch()['total'];
    echo "Total users in tb_user: " . $count . "<br>";
    
    $stmt = $db->query("SELECT * FROM tb_user");
    $users = $stmt->fetchAll();
    
    echo "<h2>User List:</h2>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    
    // Check columns
    $stmt = $db->query("DESCRIBE tb_user");
    $columns = $stmt->fetchAll();
    echo "<h2>Table Structure:</h2>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
