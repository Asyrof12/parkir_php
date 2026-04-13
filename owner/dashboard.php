<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_owner();

$user = get_user_info();

// Get statistics
try {
    // Total pendapatan keseluruhan
    $stmt = $db->query("SELECT SUM(biaya_total) as total FROM tb_transaksi WHERE status = 'keluar'");
    $total_pendapatan = $stmt->fetch()['total'] ?? 0;

    // Total transaksi
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_transaksi");
    $total_transaksi = $stmt->fetch()['total'];

    $pendapatan_bulan_ini = $stmt->fetch()['total'] ?? 0;

    // Pendapatan hari ini
    $stmt = $db->query("SELECT SUM(biaya_total) as total FROM tb_transaksi 
                        WHERE status = 'keluar' AND DATE(waktu_keluar) = CURDATE()");
    $pendapatan_hari_ini = $stmt->fetch()['total'] ?? 0;

} catch(PDOException $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Dashboard Owner</h1>
    <p class="text-muted">Selamat datang, <?php echo $user['nama']; ?>!</p>

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3><?php echo format_rupiah($total_pendapatan); ?></h3>
                <p>Total Pendapatan</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3><?php echo format_rupiah($pendapatan_hari_ini); ?></h3>
                <p>Pendapatan Hari Ini</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <h3><?php echo format_rupiah($pendapatan_bulan_ini); ?></h3>
                <p>Pendapatan Bulan Ini</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
                <h3><?php echo $total_transaksi; ?></h3>
                <p>Total Transaksi</p>
            </div>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Menu Utama</h2>
        <div class="action-grid">
            <a href="rekap.php" class="action-card">
                <div class="action-icon">📋</div>
                <h3>Rekap Transaksi</h3>
                <p>Lihat rekap transaksi berdasarkan periode waktu</p>
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
