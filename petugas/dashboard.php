<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_petugas();

$user = get_user_info();

// Get statistics
try {
    // Transaksi hari ini oleh petugas ini
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tb_transaksi 
                          WHERE DATE(waktu_masuk) = CURDATE() AND id_user = :id_user");
    $stmt->bindParam(':id_user', $_SESSION['id_user']);
    $stmt->execute();
    $total_transaksi_hari_ini = $stmt->fetch()['total'];

    // Kendaraan masih parkir (status masuk)
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_transaksi WHERE status = 'masuk'");
    $total_parkir_aktif = $stmt->fetch()['total'];

    // Total pendapatan hari ini
    $stmt = $db->query("SELECT SUM(biaya_total) as total FROM tb_transaksi 
                        WHERE DATE(waktu_keluar) = CURDATE() AND status = 'keluar'");
    $total_pendapatan = $stmt->fetch()['total'] ?? 0;

    // Status Area Parkir
    $areas = $db->query("SELECT * FROM tb_area_parkir ORDER BY nama_area")->fetchAll();

} catch(PDOException $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Dashboard Petugas</h1>
    <p class="text-muted">Selamat datang, <?php echo $user['nama']; ?>!</p>

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-info">
                <h3><?php echo $total_transaksi_hari_ini; ?></h3>
                <p>Transaksi Saya Hari Ini</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🚗</div>
            <div class="stat-info">
                <h3><?php echo $total_parkir_aktif; ?></h3>
                <p>Kendaraan Sedang Parkir</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3><?php echo format_rupiah($total_pendapatan); ?></h3>
                <p>Pendapatan Hari Ini</p>
                <a href="cetak_pendapatan.php" target="_blank" class="btn btn-sm btn-success mt-2" style="font-size: 12px; padding: 4px 8px;">🖨️ Cetak Struk</a>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Status Area Parkir</h2>
            <span class="badge badge-info">Real-time</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama Area</th>
                            <th>Total Kapasitas</th>
                            <th>Terisi</th>
                            <th>Sisa Slot</th>
                            <th>Status Space</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($areas as $area): 
                            $sisa = $area['kapasitas'] - $area['terisi'];
                            $persen = ($area['terisi'] / $area['kapasitas']) * 100;
                            $status_class = 'bg-success';
                            $status_text = 'Tersedia';
                            
                            if ($sisa <= 0) {
                                $status_class = 'bg-danger';
                                $status_text = 'Penuh';
                            } elseif ($persen > 80) {
                                $status_class = 'bg-warning';
                                $status_text = 'Hampir Penuh';
                            }
                        ?>
                            <tr>
                                <td><strong><?php echo $area['nama_area']; ?></strong></td>
                                <td><?php echo $area['kapasitas']; ?></td>
                                <td class="text-primary"><?php echo $area['terisi']; ?></td>
                                <td class="text-success font-weight-bold"><?php echo $sisa; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress mr-2" style="height: 10px; width: 100px; flex-grow: 1;">
                                            <div class="progress-bar <?php echo $status_class; ?>" role="progressbar" style="width: <?php echo $persen; ?>%"></div>
                                        </div>
                                        <span class="badge <?php echo $status_class; ?> text-white ml-2"><?php echo $status_text; ?></span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="quick-actions mt-4">
        <h2>Menu Utama</h2>
        <div class="action-grid">
            <a href="cetak_struk.php" class="action-card">
                <div class="action-icon">🎫</div>
                <h3>Cetak Struk Parkir</h3>
                <p>Input kendaraan masuk dan cetak struk</p>
            </a>

            <a href="transaksi.php" class="action-card">
                <div class="action-icon">💳</div>
                <h3>Transaksi Keluar</h3>
                <p>Input kendaraan keluar dan hitung biaya</p>
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
