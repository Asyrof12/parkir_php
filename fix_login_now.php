<?php
require_once __DIR__ . '/config/config.php';

echo "<h2>🔧 Fixing Login Issues...</h2>";

try {
    // 1. Rename column id to id_user if it exists
    $stmt = $db->query("SHOW COLUMNS FROM tb_user LIKE 'id'");
    if ($stmt->rowCount() > 0) {
        $db->exec("ALTER TABLE tb_user CHANGE id id_user INT(11) AUTO_INCREMENT PRIMARY KEY");
        echo "✅ Column 'id' renamed to 'id_user' in 'tb_user'.<br>";
    } else {
        echo "ℹ️ Column 'id_user' already exists or 'id' not found.<br>";
    }

    // 2. Hash existing passwords
    $stmt = $db->query("SELECT id_user, username, password FROM tb_user");
    $users = $stmt->fetchAll();

    foreach ($users as $user) {
        // Check if already hashed
        if (substr($user['password'], 0, 4) !== '$2y$') {
            $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE tb_user SET password = :password WHERE id_user = :id");
            $update->execute([':password' => $hashed, ':id' => $user['id_user']]);
            echo "✅ Password for user '{$user['username']}' hashed.<br>";
        } else {
            echo "ℹ️ Password for user '{$user['username']}' is already hashed.<br>";
        }
    }

    echo "<h3>✨ All fixes applied!</h3>";
    echo "<a href='auth/login.php'>Go to Login Page</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
