<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_admin();

$user = get_user_info();

// Get statistics
try {
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_user");
    $total_users = $stmt->fetch()['total'];

    // Total transaksi hari ini
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_transaksi WHERE DATE(waktu_masuk) = CURDATE()");
    $total_transaksi_hari_ini = $stmt->fetch()['total'];

    // Total kendaraan terdaftar
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_kendaraan");
    $total_kendaraan = $stmt->fetch()['total'];

    // Total area parkir
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_area_parkir");
    $total_area = $stmt->fetch()['total'];

    // Total pendapatan hari ini
    $stmt = $db->query("SELECT SUM(biaya_total) as total FROM tb_transaksi 
                        WHERE DATE(waktu_keluar) = CURDATE() AND status = 'keluar'");
    $total_pendapatan_hari_ini = $stmt->fetch()['total'] ?? 0;

    // Transaksi terbaru
    $stmt = $db->query("SELECT t.*, k.plat_nomor, a.nama_area, u.nama_lengkap 
                        FROM tb_transaksi t 
                        JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                        JOIN tb_area_parkir a ON t.id_area = a.id_area 
                        JOIN tb_user u ON t.id_user = u.id_user 
                        ORDER BY t.waktu_masuk DESC LIMIT 5");
    $transaksi_terbaru = $stmt->fetchAll();

} catch(PDOException $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Dashboard Admin</h1>
    <p class="text-muted">Selamat datang, <?php echo $user['nama']; ?>!</p>

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo $total_users; ?></h3>
                <p>Total User</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🚗</div>
            <div class="stat-info">
                <h3><?php echo $total_kendaraan; ?></h3>
                <p>Kendaraan Terdaftar</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo $total_transaksi_hari_ini; ?></h3>
                <p>Transaksi Hari Ini</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🅿️</div>
            <div class="stat-info">
                <h3><?php echo $total_area; ?></h3>
                <p>Area Parkir</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3><?php echo format_rupiah($total_pendapatan_hari_ini); ?></h3>
                <p>Pendapatan Hari Ini</p>
                <a href="cetak_pendapatan.php" target="_blank" class="btn btn-sm btn-success mt-2" style="font-size: 12px; padding: 4px 8px;">🖨️ Cetak Struk</a>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3>Transaksi Terbaru</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Parkir</th>
                        <th>Area</th>
                        <th>Waktu Masuk</th>
                        <th>Status</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transaksi_terbaru)): ?>
                        <?php foreach($transaksi_terbaru as $t): ?>
                            <tr>
                                <td><?php echo $t['plat_nomor']; ?></td>
                                <td><?php echo $t['nama_area']; ?></td>
                                <td><?php echo format_datetime($t['waktu_masuk']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $t['status'] === 'masuk' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($t['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $t['nama_lengkap']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada transaksi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
