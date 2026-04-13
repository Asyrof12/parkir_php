<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth_check.php';
require_admin();

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $jenis_kendaraan = clean_input($_POST['jenis_kendaraan']);
    $tarif_per_jam = clean_input($_POST['tarif_per_jam']);

    try {
        if ($id) {
            // Update
            $query = "UPDATE tb_tarif SET jenis_kendaraan = :jenis, tarif_per_jam = :tarif_per_jam WHERE id_tarif = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $message = 'Tarif berhasil diupdate!';
            $activity = "Mengubah tarif $jenis_kendaraan";
        } else {
            // Create
            $tarif_name = "Tarif " . ucfirst($jenis_kendaraan);
            $query = "INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) 
                      VALUES (:jenis, :tarif_per_jam)";
            $stmt = $db->prepare($query);
            $message = 'Tarif berhasil ditambahkan!';
            $activity = "Menambah tarif $jenis_kendaraan";
        }
        
        $stmt->bindParam(':jenis', $jenis_kendaraan);
        $stmt->bindParam(':tarif_per_jam', $tarif_per_jam);
        
        if ($stmt->execute()) {
            log_activity($db, $_SESSION['id_user'], $activity);
            set_flash('success', $message);
        }
    } catch(PDOException $e) {
        set_flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
    }
    redirect('admin/tarif/index.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $query = "DELETE FROM tb_tarif WHERE id_tarif = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            log_activity($db, $_SESSION['id_user'], "Menghapus tarif parkir");
            set_flash('success', 'Tarif berhasil dihapus!');
        }
    } catch(PDOException $e) {
        set_flash('danger', 'Gagal menghapus tarif: ' . $e->getMessage());
    }
    redirect('admin/tarif/index.php');
}

// Get all tarif
$query = "SELECT * FROM tb_tarif ORDER BY jenis_kendaraan";
$tarif_list = $db->query($query)->fetchAll();

// Get tarif for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $query = "SELECT * FROM tb_tarif WHERE id_tarif = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['edit']);
    $stmt->execute();
    $edit_data = $stmt->fetch();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Manajemen Tarif Parkir</h1>
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
                    <h3><?php echo $edit_data ? 'Edit Tarif' : 'Tambah Tarif'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id_tarif']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="jenis_kendaraan">Jenis Kendaraan *</label>
                            <input type="text" id="jenis_kendaraan" name="jenis_kendaraan" class="form-control" 
                                   required placeholder="Contoh: Motor, Mobil, Truk"
                                   value="<?php echo $edit_data['jenis_kendaraan'] ?? ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="tarif_per_jam">Tarif per Jam (Rp) *</label>
                            <input type="number" id="tarif_per_jam" name="tarif_per_jam" class="form-control" 
                                   required min="0" step="500" value="<?php echo $edit_data['tarif_per_jam'] ?? ''; ?>">
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
                    <h3>Daftar Tarif</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jenis Kendaraan</th>
                                <th>Tarif per Jam</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tarif_list)): ?>
                                <?php foreach($tarif_list as $tarif): ?>
                                    <tr>
                                        <td><?php echo $tarif['id_tarif']; ?></td>
                                        <td><strong><?php echo ucfirst($tarif['jenis_kendaraan']); ?></strong></td>
                                        <td><?php echo format_rupiah($tarif['tarif_per_jam']); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $tarif['id_tarif']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $tarif['id_tarif']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus tarif ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data tarif</td>
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
