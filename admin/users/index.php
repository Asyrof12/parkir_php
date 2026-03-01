<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth_check.php';
require_admin();

// Get all users
$users = [];
$error = '';

try {
    $query = "SELECT * FROM tb_user ORDER BY created_at DESC";
    $stmt = $db->query($query);
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Gagal mengambil data user: " . $e->getMessage();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Manajemen User</h1>
        <a href="create.php" class="btn btn-primary">+ Tambah User</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id_user']; ?></td>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['nama_lengkap']; ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $user['role'] === 'admin' ? 'danger' : 
                                            ($user['role'] === 'petugas' ? 'primary' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['status_aktif'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $user['status_aktif'] ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td><?php echo format_datetime($user['created_at']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $user['id_user']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete.php?id=<?php echo $user['id_user']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data user</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
