<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth_check.php';
require_admin();

$error = '';
$user_data = null;

// Get user ID
$id = $_GET['id'] ?? null;
if (!$id) {
    set_flash('danger', 'ID user tidak valid!');
    redirect('admin/users/index.php');
}

// Get user data
try {
    $query = "SELECT * FROM tb_user WHERE id_user = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        set_flash('danger', 'User tidak ditemukan!');
        redirect('admin/users/index.php');
    }
    
    $user_data = $stmt->fetch();
} catch(PDOException $e) {
    set_flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
    redirect('admin/users/index.php');
}

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username']);
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $password = $_POST['password'];
    $role = clean_input($_POST['role']);

    if (!empty($username) && !empty($nama_lengkap) && !empty($role)) {
        try {
            // Check username sudah ada (kecuali username sendiri)
            $query = "SELECT id_user FROM tb_user WHERE username = :username AND id_user != :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = 'Username sudah digunakan!';
            } else {
                // Update user
                if (!empty($password)) {
                    // Jika password diisi, update password juga
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE tb_user SET username = :username, 
                              nama_lengkap = :nama_lengkap, password = :password, role = :role, 
                              status_aktif = :status 
                              WHERE id_user = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':password', $hashed_password);
                } else {
                    // Jika password kosong, tidak update password
                    $query = "UPDATE tb_user SET username = :username, 
                              nama_lengkap = :nama_lengkap, role = :role, 
                              status_aktif = :status 
                              WHERE id_user = :id";
                    $stmt = $db->prepare($query);
                }
                
                $status = $_POST['status_aktif'];
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':nama_lengkap', $nama_lengkap);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    log_activity($db, $_SESSION['id_user'], "Mengubah data user: $username");
                    set_flash('success', 'User berhasil diupdate!');
                    redirect('admin/users/index.php');
                }
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = 'Username, nama lengkap, dan role harus diisi!';
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Edit User</h1>
        <a href="index.php" class="btn btn-secondary">← Kembali</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           required value="<?php echo $_POST['username'] ?? $user_data['username']; ?>">
                </div>

                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap *</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" 
                           required value="<?php echo $_POST['nama_lengkap'] ?? $user_data['nama_lengkap']; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="6">
                    <small class="form-text">Kosongkan jika tidak ingin mengubah password</small>
                </div>

                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="admin" <?php echo ($user_data['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="petugas" <?php echo ($user_data['role'] ?? '') === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                        <option value="owner" <?php echo ($user_data['role'] ?? '') === 'owner' ? 'selected' : ''; ?>>Owner</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status_aktif">Status *</label>
                    <select id="status_aktif" name="status_aktif" class="form-control" required>
                        <option value="1" <?php echo ($user_data['status_aktif'] ?? '') == '1' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?php echo ($user_data['status_aktif'] ?? '') == '0' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
