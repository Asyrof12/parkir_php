<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hash & Update Password - Sistem Parkir</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { color: #667eea; margin: 0 0 10px 0; }
        .success { 
            background: #d1fae5; 
            color: #065f46; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
            border-left: 4px solid #10b981;
        }
        .error { 
            background: #fee2e2; 
            color: #991b1b; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
            border-left: 4px solid #ef4444;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #3b82f6;
        }
        .credentials {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .credentials h3 {
            margin-top: 0;
            color: #374151;
        }
        code {
            background: #f3f4f6;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #1f2937;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px 10px 0;
            font-weight: 500;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Hash & Update Password</h1>
        <p>Script ini akan meng-hash semua password plain text di database</p>
        
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
            
            // Ambil semua user
            $stmt = $conn->query("SELECT * FROM tb_user");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="info">Ditemukan ' . count($users) . ' user di database</div>';
            
            $updated = 0;
            
            foreach($users as $user) {
                // Cek apakah password sudah di-hash (hash bcrypt dimulai dengan $2y$)
                if (substr($user['password'], 0, 4) !== '$2y$') {
                    // Password masih plain text, hash sekarang
                    $plain_password = $user['password'];
                    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
                    
                    // Update ke database
                    $update_stmt = $conn->prepare("UPDATE tb_user SET password = ? WHERE username = ?");
                    $update_stmt->execute([$hashed_password, $user['username']]);
                    
                    echo '<div class="success">';
                    echo '✅ User <strong>' . $user['username'] . '</strong> berhasil di-hash!<br>';
                    echo 'Password: <code>' . $plain_password . '</code><br>';
                    echo 'Hash: <code>' . substr($hashed_password, 0, 40) . '...</code>';
                    echo '</div>';
                    
                    $updated++;
                } else {
                    echo '<div class="info">';
                    echo 'ℹ️ User <strong>' . $user['username'] . '</strong> sudah menggunakan hash';
                    echo '</div>';
                }
            }
            
            if ($updated > 0) {
                echo '<div class="success">';
                echo '<strong>✅ Selesai! ' . $updated . ' password berhasil di-hash</strong>';
                echo '</div>';
            }
            
            // Tampilkan kredensial
            echo '<div class="credentials">';
            echo '<h3>🔑 Kredensial Login:</h3>';
            
            $stmt = $conn->query("SELECT username, role FROM tb_user ORDER BY role");
            $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($all_users as $u) {
                $default_pass = $u['username'] . '123';
                echo '<p>👤 <strong>' . ucfirst($u['role']) . ':</strong> ';
                echo '<code>' . $u['username'] . '</code> / <code>' . $default_pass . '</code></p>';
            }
            echo '</div>';
            
            echo '<a href="index.php" class="btn">🚀 Login Sekarang</a>';
            echo '<a href="auth/login.php" class="btn">🔐 Halaman Login</a>';
            
        } catch(PDOException $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                echo '<div class="info">';
                echo '<h3>Database belum dibuat!</h3>';
                echo '<p><strong>Langkah-langkah:</strong></p>';
                echo '<ol>';
                echo '<li>Buka <a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a></li>';
                echo '<li>Buat database baru: <code>parkir_wisnu_db</code></li>';
                echo '<li>Import file <code>database.sql</code></li>';
                echo '<li>Refresh halaman ini</li>';
                echo '</ol>';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
