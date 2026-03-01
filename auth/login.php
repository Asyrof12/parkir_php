<?php
require_once __DIR__ . '/../config/config.php';

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    $role = $_SESSION['role'];
    redirect($role . '/dashboard.php');
}

$error = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        try {
            $query = "SELECT * FROM tb_user WHERE username = :username LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session
                    // Set session
                    $_SESSION['id_user'] = $user['id_user'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Log activity
                    log_activity($db, $user['id_user'], 'Login ke sistem');


                    // Redirect berdasarkan role
                    set_flash('success', 'Login berhasil! Selamat datang ' . $user['nama_lengkap']);
                    redirect($user['role'] . '/dashboard.php');
                } else {
                    $error = 'Username atau password salah!';
                }
            } else {
                $error = 'Username atau password salah!';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = 'Username dan password harus diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Parkir</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🅿️ Sistem Parkir</h1>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <!-- <div class="login-footer">
                <p><small>Default: admin/petugas/owner | Password: admin123/petugas123/owner123</small></p>
                <p><small><a href="../debug_login.php" style="color: #667eea;">🔍 Debug Login</a></small></p>
            </div> -->
        </div>
    </div>
</body>
</html>
