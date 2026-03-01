<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth_check.php';
require_admin();

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nama_area = clean_input($_POST['nama_area']);
    $kapasitas = clean_input($_POST['kapasitas']);

    try {
        if ($id) {
            // Update
            $query = "UPDATE tb_area_parkir SET nama_area = :nama, 
                      kapasitas = :kapasitas WHERE id_area = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $message = 'Area parkir berhasil diupdate!';
            $activity = "Mengubah area parkir $nama_area";
        } else {
            // Create
            $query = "INSERT INTO tb_area_parkir (nama_area, kapasitas) 
                      VALUES (:nama, :kapasitas)";
            $stmt = $db->prepare($query);
            $message = 'Area parkir berhasil ditambahkan!';
            $activity = "Menambah area parkir $nama_area";
        }
        
        $stmt->bindParam(':nama', $nama_area);
        $stmt->bindParam(':kapasitas', $kapasitas);
        
        if ($stmt->execute()) {
            log_activity($db, $_SESSION['id_user'], $activity);
            set_flash('success', $message);
        }
    } catch(PDOException $e) {
        set_flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
    }
    redirect('admin/area/index.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $query = "DELETE FROM tb_area_parkir WHERE id_area = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            log_activity($db, $_SESSION['id_user'], "Menghapus area parkir");
            set_flash('success', 'Area parkir berhasil dihapus!');
        }
    } catch(PDOException $e) {
        set_flash('danger', 'Gagal menghapus area parkir: ' . $e->getMessage());
    }
    redirect('admin/area/index.php');
}

// Get all area
$query = "SELECT * FROM tb_area_parkir ORDER BY id_area";
$area_list = $db->query($query)->fetchAll();

// Get area for edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $query = "SELECT * FROM tb_area_parkir WHERE id_area = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['edit']);
    $stmt->execute();
    $edit_data = $stmt->fetch();
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Manajemen Area Parkir</h1>
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
                    <h3><?php echo $edit_data ? 'Edit Area' : 'Tambah Area'; ?></h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id_area']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nama_area">Nama Area *</label>
                            <input type="text" id="nama_area" name="nama_area" class="form-control" 
                                   required value="<?php echo $edit_data['nama_area'] ?? ''; ?>"
                                   placeholder="Area A1">
                        </div>

                        <div class="form-group">
                            <label for="kapasitas">Kapasitas *</label>
                            <input type="number" id="kapasitas" name="kapasitas" class="form-control" 
                                   required min="1" value="<?php echo $edit_data['kapasitas'] ?? ''; ?>">
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
                    <h3>Daftar Area Parkir</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Area</th>
                                <th>Kapasitas</th>
                                <th>Terisi</th>
                                <th>Tersedia</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($area_list)): ?>
                                <?php foreach($area_list as $area): ?>
                                    <tr>
                                        <td><?php echo $area['id_area']; ?></td>
                                        <td><?php echo $area['nama_area']; ?></td>
                                        <td><?php echo $area['kapasitas']; ?> slot</td>
                                        <td><?php echo $area['terisi']; ?> slot</td>
                                        <td>
                                            <strong><?php echo $area['kapasitas'] - $area['terisi']; ?> slot</strong>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $area['id_area']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="?delete=<?php echo $area['id_area']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus area ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data area parkir</td>
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
