<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth_check.php';
require_admin();

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $kendaraan = clean_input($_POST['kendaraan']);
    $jenis_kendaraan = clean_input($_POST['jenis_kendaraan']);
    $pemilik = clean_input($_POST['pemilik']);
    $id_user = clean_input($_POST['id_user']);

    try {
        if ($id) {
            // Update
            $query = "UPDATE tb_kendaraan SET plat_nomor = :kendaraan, jenis_kendaraan = :jenis, 
                      pemilik = :pemilik, warna = :warna, id_user = :id_user WHERE id_kendaraan = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $message = 'Data kendaraan berhasil diupdate!';
            $activity = "Mengubah data kendaraan $kendaraan";
        } else {
            // Create
            $query = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, pemilik, warna, id_user) 
                      VALUES (:kendaraan, :jenis, :pemilik, :warna, :id_user)";
            $stmt = $db->prepare($query);
            $message = 'Kendaraan berhasil ditambahkan!';
            $activity = "Menambah kendaraan $kendaraan";
        }
        
        $stmt->bindParam(':kendaraan', $kendaraan);
        $stmt->bindParam(':jenis', $jenis_kendaraan);
        $stmt->bindParam(':pemilik', $pemilik);
        $warna = clean_input($_POST['warna'] ?? '');
        $stmt->bindParam(':warna', $warna);
        $stmt->bindParam(':id_user', $id_user);
        
        if ($stmt->execute()) {
            log_activity($db, $_SESSION['id_user'], $activity);
            set_flash('success', $message);
        }
    } catch(PDOException $e) {
        set_flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
    }
    redirect('admin/kendaraan/index.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $query = "DELETE FROM tb_kendaraan WHERE id_kendaraan = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            log_activity($db, $_SESSION['id_user'], "Menghapus data kendaraan");
            set_flash('success', 'Kendaraan berhasil dihapus!');
        }
    } catch(PDOException $e) {
        set_flash('danger', 'Gagal menghapus kendaraan: ' . $e->getMessage());
    }
    redirect('admin/kendaraan/index.php');
}

// Get all users untuk dropdown
$users = $db->query("SELECT id_user, nama_lengkap FROM tb_user ORDER BY nama_lengkap")->fetchAll();

// Get all kendaraan
$query = "SELECT k.*, u.nama_lengkap FROM tb_kendaraan k 
          JOIN tb_user u ON k.id_user = u.id_user 
          ORDER BY k.id_kendaraan DESC";
$kendaraan_list = $db->query($query)->fetchAll();

// Get kendaraan for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $query = "SELECT * FROM tb_kendaraan WHERE id_kendaraan = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['edit']);
    $stmt->execute();
    $edit_data = $stmt->fetch();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Manajemen Kendaraan</h1>
        <a href="../dashboard.php" class="btn btn-secondary">← Dashboard</a>
    </div>

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3><?php echo $edit_data ? 'Edit Kendaraan' : 'Tambah Kendaraan'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id_kendaraan']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="kendaraan">Nomor Polisi *</label>
                            <input type="text" id="kendaraan" name="kendaraan" class="form-control" 
                                   required maxlength="15" value="<?php echo $edit_data['plat_nomor'] ?? ''; ?>"
                                   placeholder="Contoh: B 1234 XYZ">
                        </div>

                        <div class="form-group">
                            <label for="jenis_kendaraan">Jenis Kendaraan *</label>
                            <input type="text" id="jenis_kendaraan" name="jenis_kendaraan" class="form-control" 
                                   required value="<?php echo $edit_data['jenis_kendaraan'] ?? ''; ?>"
                                   placeholder="Contoh: Motor, Mobil, Truk">
                        </div>

                        <div class="form-group">
                            <label for="pemilik">Nama Pemilik *</label>
                            <input type="text" id="pemilik" name="pemilik" class="form-control" 
                                   required value="<?php echo $edit_data['pemilik'] ?? ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="warna">Warna *</label>
                            <input type="text" id="warna" name="warna" class="form-control" 
                                   required value="<?php echo $edit_data['warna'] ?? ''; ?>"
                                   placeholder="Contoh: Hitam, Putih, Merah">
                        </div>
                        </div>

                        <div class="form-group">
                            <label for="id_user">User Terdaftar *</label>
                            <select id="id_user" name="id_user" class="form-control" required>
                                <option value="">-- Pilih User --</option>
                                <?php foreach($users as $user): ?>
                                    <option value="<?php echo $user['id_user']; ?>" 
                                        <?php echo ($edit_data['id_user'] ?? '') == $user['id_user'] ? 'selected' : ''; ?>>
                                        <?php echo $user['nama_lengkap']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_data ? 'Update' : 'Simpan'; ?>
                            </button>
                            <?php if ($edit_data): ?>
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Kendaraan</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Polisi</th>
                                <th>Jenis</th>
                                <th>Pemilik</th>
                                <th>Warna</th>
                                <th>User</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($kendaraan_list)): ?>
                                <?php foreach($kendaraan_list as $k): ?>
                                    <tr>
                                        <td><strong><?php echo $k['plat_nomor']; ?></strong></td>
                                        <td><?php echo $k['jenis_kendaraan']; ?></td>
                                        <td><?php echo $k['pemilik']; ?></td>
                                        <td><?php echo $k['warna']; ?></td>
                                        <td><?php echo $k['nama_lengkap']; ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $k['id_kendaraan']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $k['id_kendaraan']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus kendaraan ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data kendaraan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
