<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Database - Sistem Parkir</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #667eea; }
        h3 { color: #374151; margin-top: 20px; }
        .success { 
            background: #d1fae5; 
            color: #065f46; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
        }
        .error { 
            background: #fee2e2; 
            color: #991b1b; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }
        pre {
            background: #1f2937;
            color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Database & Password - Sistem Parkir</h1>
        
        <?php
        // Koneksi database
        $host = "localhost";
        $db_name = "parkir_wisnu_db";
        $db_username = "root";
        $db_password = "";

        try {
            $conn = new PDO("mysql:host=$host;dbname=$db_name", $db_username, $db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo '<div class="success">✅ Koneksi database berhasil!</div>';
            echo '<div class="info">PHP Version: ' . phpversion() . '</div>';
            
            // Cek struktur tabel tb_user
            echo '<h3>📊 Mengecek Struktur Tabel tb_user:</h3>';
            $stmt = $conn->query("DESCRIBE tb_user");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<pre>';
            foreach($columns as $col) {
                echo $col['Field'] . ' (' . $col['Type'] . ')' . "\n";
            }
            echo '</pre>';
            
            // Cek apakah ada data user
            echo '<h3>👥 Data User di Database:</h3>';
            $stmt = $conn->query("SELECT * FROM tb_user");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($users) > 0) {
                echo '<div class="info">Ditemukan ' . count($users) . ' user</div>';
                
                // Update password untuk setiap user
                echo '<h3>🔐 Updating Passwords:</h3>';
                
                $passwords = [
                    'admin' => 'admin123',
                    'petugas' => 'petugas123',
                    'owner' => 'owner123'
                ];
                
                foreach($passwords as $username => $password) {
                    // Cek apakah user ada
                    $check_stmt = $conn->prepare("SELECT * FROM tb_user WHERE username = ?");
                    $check_stmt->execute([$username]);
                    
                    if ($check_stmt->rowCount() > 0) {
                        // Hash password
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Update
                        $update_stmt = $conn->prepare("UPDATE tb_user SET password = ? WHERE username = ?");
                        $update_stmt->execute([$hashed, $username]);
                        
                        echo '<div class="success">✅ Password untuk <strong>' . $username . '</strong> berhasil diupdate!</div>';
                        
                        // Verify
                        $verify_stmt = $conn->prepare("SELECT password FROM tb_user WHERE username = ?");
                        $verify_stmt->execute([$username]);
                        $user_data = $verify_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (password_verify($password, $user_data['password'])) {
                            echo '<div class="success">✅ Verifikasi: Password <code>' . $password . '</code> COCOK!</div>';
                        } else {
                            echo '<div class="error">❌ Verifikasi gagal!</div>';
                        }
                    } else {
                        echo '<div class="warning">⚠️ User <strong>' . $username . '</strong> tidak ditemukan!</div>';
                    }
                }
                
            } else {
                echo '<div class="warning">⚠️ Tidak ada data user! Silakan import database.sql terlebih dahulu.</div>';
            }
            
            echo '<h3>✅ Selesai!</h3>';
            echo '<div class="info">';
            echo '<strong>Kredensial Login:</strong><br><br>';
            echo '🔑 <strong>Admin:</strong> <code>admin</code> / <code>admin123</code><br>';
            echo '🔑 <strong>Petugas:</strong> <code>petugas</code> / <code>petugas123</code><br>';
            echo '🔑 <strong>Owner:</strong> <code>owner</code> / <code>owner123</code><br>';
            echo '</div>';
            
            echo '<a href="index.php" class="btn">🚀 Login Sekarang</a>';
            echo '<a href="auth/login.php" class="btn">🔐 Ke Halaman Login</a>';
            
        } catch(PDOException $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                echo '<div class="warning">';
                echo '<h3>Database belum dibuat!</h3>';
                echo '<p>Silakan:</p>';
                echo '<ol>';
                echo '<li>Buka phpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></li>';
                echo '<li>Buat database baru dengan nama: <code>parkir_wisnu_db</code></li>';
                echo '<li>Import file <code>database.sql</code></li>';
                echo '<li>Refresh halaman ini</li>';
                echo '</ol>';
                echo '</div>';
            } else if (strpos($e->getMessage(), "Table") !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
                echo '<div class="warning">';
                echo '<h3>Tabel belum ada!</h3>';
                echo '<p>Silakan import file <code>database.sql</code> di phpMyAdmin</p>';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
