<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login - Sistem Parkir</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th, table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }
        table th {
            background: #f9fafb;
            font-weight: 600;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
        }
        .test-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Login - Sistem Parkir</h1>
        
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
            
            // Ambil semua user dari database
            echo '<h3>📊 Data User di Database:</h3>';
            $stmt = $conn->query("
                SELECT id_user, username, nama_lengkap, role, LEFT(password, 30) as password_preview 
                FROM tb_user
            ");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<table>';
            echo '<tr><th>ID</th><th>Username</th><th>Nama</th><th>Role</th><th>Password (30 char)</th></tr>';
            foreach($users as $user) {
                echo '<tr>';
                echo '<td>' . $user['id_user'] . '</td>';
                echo '<td><strong>' . $user['username'] . '</strong></td>';
                echo '<td>' . $user['nama_lengkap'] . '</td>';
                echo '<td>' . $user['role'] . '</td>';
                echo '<td><code>' . $user['password_preview'] . '...</code></td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Test password verification untuk setiap user
            echo '<h3>🔐 Test Password Verification:</h3>';
            
            $test_passwords = [
                'admin' => 'admin123',
                'petugas' => 'petugas123',
                'owner' => 'owner123'
            ];
            
            foreach($test_passwords as $username => $password) {
                echo '<div class="test-section">';
                echo '<strong>Testing: ' . $username . '</strong><br>';
                
                // Ambil data user
                $stmt = $conn->prepare("SELECT * FROM tb_user WHERE username = :username");
                $stmt->execute([':username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo '✅ User ditemukan di database<br>';
                    echo 'Password di DB: <code>' . substr($user['password'], 0, 50) . '...</code><br>';
                    echo 'Password length: ' . strlen($user['password']) . ' characters<br>';
                    
                    // Test password verify
                    $verify_result = password_verify($password, $user['password']);
                    
                    if ($verify_result) {
                        echo '<div class="success">✅ Password <code>' . $password . '</code> COCOK!</div>';
                    } else {
                        echo '<div class="error">❌ Password <code>' . $password . '</code> TIDAK COCOK!</div>';
                        
                        // Coba hash ulang dan update
                        echo '<strong>Mencoba hash ulang...</strong><br>';
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        echo 'New hash: <code>' . substr($new_hash, 0, 50) . '...</code><br>';
                        
                        // Update ke database
                        $update_stmt = $conn->prepare("UPDATE tb_user SET password = :password WHERE username = :username");
                        $update_stmt->execute([':password' => $new_hash, ':username' => $username]);
                        echo '<div class="success">✅ Password berhasil di-update!</div>';
                        
                        // Verify lagi
                        $verify_again = password_verify($password, $new_hash);
                        if ($verify_again) {
                            echo '<div class="success">✅ Verifikasi ulang: Password sekarang COCOK!</div>';
                        }
                    }
                } else {
                    echo '<div class="error">❌ User tidak ditemukan!</div>';
                }
                echo '</div>';
            }
            
            echo '<h3>✅ Kesimpulan:</h3>';
            echo '<div class="info">';
            echo '<strong>Silakan login dengan kredensial berikut:</strong><br><br>';
            echo '• <strong>Admin:</strong> username = <code>admin</code> | password = <code>admin123</code><br>';
            echo '• <strong>Petugas:</strong> username = <code>petugas</code> | password = <code>petugas123</code><br>';
            echo '• <strong>Owner:</strong> username = <code>owner</code> | password = <code>owner123</code><br><br>';
            echo '<a href="index.php" style="display:inline-block;padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;">🚀 Login Sekarang</a>';
            echo '</div>';
            
        } catch(PDOException $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            echo '<div class="info">';
            echo '<strong>Troubleshooting:</strong><br>';
            echo '1. Pastikan XAMPP MySQL sudah running<br>';
            echo '2. Pastikan database <code>parkir_wisnu_db</code> sudah dibuat<br>';
            echo '3. Pastikan tabel <code>tb_user</code> sudah ada<br>';
            echo '4. Import file <code>database.sql</code> jika belum';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
