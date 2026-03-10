<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth_check.php';
require_admin();

$error = '';

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $password = $_POST['password'];
    $role = clean_input($_POST['role']);

    if (!empty($username) && !empty($nama_lengkap) && !empty($password) && !empty($role)) {
        try {
            // Check username sudah ada
            $query = "SELECT id_user FROM tb_user WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = 'Username sudah digunakan!';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $query = "INSERT INTO tb_user (username, nama_lengkap, password, role, status_aktif) 
                          VALUES (:username, :nama_lengkap, :password, :role, 1)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':nama_lengkap', $nama_lengkap);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                
                if ($stmt->execute()) {
                    log_activity($db, $_SESSION['id_user'], "Menambah user baru: $username");
                    set_flash('success', 'User berhasil ditambahkan!');
                    redirect('admin/users/index.php');
                }
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = 'Semua field harus diisi!';
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Tambah User Baru</h1>
        <a href="index.php" class="btn btn-secondary">← Kembali</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           autocomplete="off" required value="<?php echo $_POST['username'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap *</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" 
                           required value="<?php echo $_POST['nama_lengkap'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           autocomplete="new-password" required minlength="6">
                    <small class="form-text">Minimal 6 karakter</small>
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="petugas" <?php echo ($_POST['role'] ?? '') === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                        <option value="owner" <?php echo ($_POST['role'] ?? '') === 'owner' ? 'selected' : ''; ?>>Owner</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
