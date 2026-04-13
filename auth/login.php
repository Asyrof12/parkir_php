<?php
require_once __DIR__ . '/../config/config.php';

// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    $role = $_SESSION['role'];
    redirect($role . '/dashboard.php');
}

$error = '';
$success = '';
$show_forgot = false;

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- Proses Lupa Password ----
    if (isset($_POST['action']) && $_POST['action'] === 'forgot_password') {
        $uname = clean_input($_POST['forgot_username']);
        if (!empty($uname)) {
            try {
                $q = "SELECT * FROM tb_user WHERE username = :username LIMIT 1";
                $s = $db->prepare($q);
                $s->bindParam(':username', $uname);
                $s->execute();
                if ($s->rowCount() > 0) {
                    $u = $s->fetch();
                    // Tampilkan hint (nama lengkap) sebagai "petunjuk"
                    $success = 'Akun ditemukan! Nama: <b>' . htmlspecialchars($u['nama_lengkap']) . '</b>. Silakan hubungi admin untuk reset password.';
                } else {
                    $error = 'Username tidak ditemukan!';
                }
            } catch(PDOException $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        } else {
            $error = 'Masukkan username terlebih dahulu!';
        }
        $show_forgot = true;

    // ---- Proses Login Biasa ----
    } else {
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
                        $_SESSION['id_user']      = $user['id_user'];
                        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                        $_SESSION['username']     = $user['username'];
                        $_SESSION['role']         = $user['role'];

                        log_activity($db, $user['id_user'], 'Login ke sistem');

                        set_flash('success', 'Login berhasil! Selamat datang ' . $user['nama_lengkap']);
                        redirect($user['role'] . '/dashboard.php');
                    } else {
                        $error = 'Password yang Anda masukkan salah!';
                    }
                } else {
                    $error = 'Username tidak ditemukan!';
                }
            } catch(PDOException $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        } else {
            $error = 'Username dan password harus diisi!';
        }
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Pusatkan halaman login sepenuhnya */
        html, body {
            height: 100%;
        }
        body.login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: transparent !important;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 28px;
            padding: 52px 48px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.05), 0 4px 20px rgba(0,0,0,0.02);
            width: 100%;
            max-width: 440px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .login-header .logo-icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 12px;
        }
        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 900;
            letter-spacing: -0.05em;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .login-header p {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }
        /* Error merah */
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            color: #b91c1c;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
        }
        @keyframes shake {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-4px); }
            40%, 60% { transform: translateX(4px); }
        }
        /* Success hijau */
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #22c55e;
            color: #15803d;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        /* Form label & input */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.06em;
        }
        .form-control {
            width: 100%;
            padding: 13px 16px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            font-weight: 500;
            color: #0f172a;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .form-control:focus {
            outline: none;
            background: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.10);
        }
        .form-control.input-error {
            border-color: #ef4444;
            background: #fff8f8;
        }
        .form-control.input-error:focus {
            box-shadow: 0 0 0 4px rgba(239,68,68,0.10);
        }
        /* Forgot password link */
        .forgot-link {
            display: block;
            text-align: right;
            margin-top: -12px;
            margin-bottom: 24px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #3b82f6;
            cursor: pointer;
            text-decoration: none;
            transition: color 0.2s;
        }
        .forgot-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        /* Tombol login */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #1e293b;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.02em;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(15,23,42,0.18);
        }
        .btn-login:hover {
            background: #000;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.22);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        /* Panel lupa password */
        .forgot-panel {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 28px;
            margin-top: 20px;
        }
        .forgot-panel h3 {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .forgot-panel p {
            font-size: 0.82rem;
            color: #64748b;
            margin-bottom: 18px;
        }
        .btn-forgot-submit {
            width: 100%;
            padding: 12px;
            background: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-forgot-submit:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .back-to-login {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            text-decoration: none;
        }
        .back-to-login:hover {
            color: #0f172a;
        }
        .divider {
            text-align: center;
            margin: 24px 0 20px;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 600;
            position: relative;
        }
        .divider::before, .divider::after {
            content: '';
            display: inline-block;
            width: 60px;
            height: 1px;
            background: #e2e8f0;
            vertical-align: middle;
            margin: 0 10px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <span class="logo-icon">🅿️</span>
                <h1>Sistem Parkir</h1>
                <p>Silakan login untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-danger">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-success">
                    ✅ <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!$show_forgot): ?>
            <!-- Form Login -->
            <form method="POST" action="" id="loginForm" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           class="form-control <?php echo (!empty($error)) ? 'input-error' : ''; ?>"
                           autocomplete="off"
                           placeholder="Masukkan username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           class="form-control <?php echo (!empty($error)) ? 'input-error' : ''; ?>"
                           autocomplete="new-password"
                           placeholder="Masukkan password"
                           required>
                </div>

                <a class="forgot-link" onclick="toggleForgot()">Lupa password?</a>

                <button type="submit" class="btn-login">🔐 Masuk</button>
            </form>

            <!-- Panel Lupa Password (hidden by default) -->
            <div id="forgotPanel" class="forgot-panel" style="display:none;">
                <h3>🔑 Lupa Password</h3>
                <p>Masukkan username Anda. Kami akan membantu verifikasi akun Anda.</p>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="forgot_password">
                    <div class="form-group">
                        <label for="forgot_username">Username</label>
                        <input type="text" id="forgot_username" name="forgot_username"
                               class="form-control" placeholder="Masukkan username Anda" required>
                    </div>
                    <button type="submit" class="btn-forgot-submit">🔍 Cek Akun</button>
                    <a class="back-to-login" onclick="toggleForgot()">← Kembali ke Login</a>
                </form>
            </div>

            <?php else: ?>
            <!-- Panel Lupa Password sudah aktif (setelah submit) -->
            <div class="forgot-panel">
                <h3>🔑 Lupa Password</h3>
                <p>Masukkan username Anda. Kami akan membantu verifikasi akun Anda.</p>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="forgot_password">
                    <div class="form-group">
                        <label for="forgot_username">Username</label>
                        <input type="text" id="forgot_username" name="forgot_username"
                               class="form-control <?php echo (!empty($error)) ? 'input-error' : ''; ?>"
                               placeholder="Masukkan username Anda"
                               value="<?php echo isset($_POST['forgot_username']) ? htmlspecialchars($_POST['forgot_username']) : ''; ?>"
                               required autofocus>
                    </div>
                    <button type="submit" class="btn-forgot-submit">🔍 Cek Akun</button>
                </form>
                <a class="back-to-login" href="login.php">← Kembali ke Login</a>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        function toggleForgot() {
            var loginForm = document.getElementById('loginForm');
            var forgotPanel = document.getElementById('forgotPanel');
            if (forgotPanel.style.display === 'none') {
                loginForm.style.display = 'none';
                forgotPanel.style.display = 'block';
                document.getElementById('forgot_username').focus();
            } else {
                loginForm.style.display = 'block';
                forgotPanel.style.display = 'none';
                document.getElementById('username').focus();
            }
        }
    </script>
</body>
</html>
