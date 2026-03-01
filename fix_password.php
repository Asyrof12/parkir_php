<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Password - Sistem Parkir</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
            border-radius: 5px;
            margin: 20px 0;
        }
        .credentials ul {
            list-style: none;
            padding: 0;
        }
        .credentials li {
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #5568d3;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Password Hash - Sistem Parkir</h1>
        
        <?php
        // Koneksi database
        $host = "localhost";
        $db_name = "parkir_wisnu_db";
        $username = "root";
        $password = "";

        try {
            $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo '<div class="success">✅ Koneksi database berhasil!</div>';
            
            // Hash passwords yang benar
            $hash_admin = password_hash('admin123', PASSWORD_DEFAULT);
            $hash_petugas = password_hash('petugas123', PASSWORD_DEFAULT);
            $hash_owner = password_hash('owner123', PASSWORD_DEFAULT);
            
            echo '<div class="info"><strong>Password Hash Generated:</strong><br>';
            echo 'PHP Version: ' . phpversion() . '</div>';
            
            // Update password admin
            $stmt = $conn->prepare("UPDATE tb_user SET password = :password WHERE username = 'admin'");
            $stmt->execute([':password' => $hash_admin]);
            echo '<div class="success">✅ Password <strong>admin</strong> berhasil diupdate!</div>';
            
            // Update password petugas
            $stmt = $conn->prepare("UPDATE tb_user SET password = :password WHERE username = 'petugas'");
            $stmt->execute([':password' => $hash_petugas]);
            echo '<div class="success">✅ Password <strong>petugas</strong> berhasil diupdate!</div>';
            
            // Update password owner
            $stmt = $conn->prepare("UPDATE tb_user SET password = :password WHERE username = 'owner'");
            $stmt->execute([':password' => $hash_owner]);
            echo '<div class="success">✅ Password <strong>owner</strong> berhasil diupdate!</div>';
            
            echo '<div class="credentials">';
            echo '<h3>🔑 Kredensial Login Baru:</h3>';
            echo '<ul>';
            echo '<li><strong>Admin</strong><br>Username: <code>admin</code> | Password: <code>admin123</code></li>';
            echo '<li><strong>Petugas</strong><br>Username: <code>petugas</code> | Password: <code>petugas123</code></li>';
            echo '<li><strong>Owner</strong><br>Username: <code>owner</code> | Password: <code>owner123</code></li>';
            echo '</ul>';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<strong>⚠️ PENTING:</strong><br>';
            echo '1. Silakan login dengan kredensial di atas<br>';
            echo '2. Hapus file <code>fix_password.php</code> ini setelah selesai untuk keamanan!';
            echo '</div>';
            
            echo '<a href="index.php" class="btn">🚀 Login Sekarang</a>';
            
        } catch(PDOException $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            echo '<div class="info">';
            echo '<strong>Troubleshooting:</strong><br>';
            echo '1. Pastikan XAMPP MySQL sudah running<br>';
            echo '2. Pastikan database <code>parkir_wisnu_db</code> sudah dibuat<br>';
            echo '3. Pastikan file <code>database.sql</code> sudah di-import<br>';
            echo '4. Cek username/password MySQL di file ini (default: root/kosong)';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
