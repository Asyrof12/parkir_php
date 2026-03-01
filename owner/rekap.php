<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_owner();

// Get filter
$tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-d');
$tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');

// Get transaksi berdasarkan filter
try {
    $query = "SELECT t.*, k.plat_nomor, a.nama_area, ta.jenis_kendaraan, ta.tarif_per_jam, u.nama_lengkap 
              FROM tb_transaksi t 
              JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
              JOIN tb_area_parkir a ON t.id_area = a.id_area 
              JOIN tb_tarif ta ON t.id_tarif = ta.id_tarif 
              JOIN tb_user u ON t.id_user = u.id_user 
              WHERE DATE(t.waktu_masuk) BETWEEN :tanggal_dari AND :tanggal_sampai 
              ORDER BY t.waktu_masuk DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':tanggal_dari', $tanggal_dari);
    $stmt->bindParam(':tanggal_sampai', $tanggal_sampai);
    $stmt->execute();
    $transaksi_list = $stmt->fetchAll();

    // Hitung total
    $total_transaksi = count($transaksi_list);
    $total_pendapatan = 0;
    $total_selesai = 0;
    $total_masih_parkir = 0;

    foreach($transaksi_list as $t) {
        if ($t['status'] === 'keluar') {
            $total_pendapatan += $t['biaya_total'];
            $total_selesai++;
        } else {
            $total_masih_parkir++;
        }
    }

} catch(PDOException $e) {
    $error = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Rekap Transaksi</h1>
        <div class="header-actions">
            <button onclick="window.print()" class="btn btn-success mr-2 no-print">🖨️ Cetak Laporan</button>
            <a href="dashboard.php" class="btn btn-secondary no-print">← Dashboard</a>
        </div>
    </div>

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3>Filter Periode</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal_dari">Tanggal Dari</label>
                            <input type="date" id="tanggal_dari" name="tanggal_dari" 
                                   class="form-control" value="<?php echo $tanggal_dari; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal_sampai">Tanggal Sampai</label>
                            <input type="date" id="tanggal_sampai" name="tanggal_sampai" 
                                   class="form-control" value="<?php echo $tanggal_sampai; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">📊 Tampilkan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <h3><?php echo $total_transaksi; ?></h3>
                    <p>Total Transaksi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $total_selesai; ?></h3>
                    <p>Selesai</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">🚗</div>
                <div class="stat-info">
                    <h3><?php echo $total_masih_parkir; ?></h3>
                    <p>Masih Parkir</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-success text-white">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3><?php echo format_rupiah($total_pendapatan); ?></h3>
                    <p>Total Pendapatan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3>Detail Transaksi</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Parkir</th>
                            <th>Jenis</th>
                            <th>Area</th>
                            <th>Waktu Masuk</th>
                            <th>Waktu Keluar</th>
                            <th>Durasi</th>
                            <th>Biaya</th>
                            <th>Status</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transaksi_list)): ?>
                            <?php foreach($transaksi_list as $t): ?>
                                <tr>
                                    <td><strong><?php echo $t['plat_nomor']; ?></strong></td>
                                    <td><?php echo ucfirst($t['jenis_kendaraan']); ?></td>
                                    <td><?php echo $t['nama_area']; ?></td>
                                    <td><?php echo format_datetime($t['waktu_masuk']); ?></td>
                                    <td>
                                        <?php if ($t['waktu_keluar']): ?>
                                            <?php echo format_datetime($t['waktu_keluar']); ?>
                                        <?php else: ?>
                                            <em class="text-muted">-</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($t['waktu_keluar']): ?>
                                            <?php echo hitung_durasi($t['waktu_masuk'], $t['waktu_keluar']); ?> jam
                                        <?php else: ?>
                                            <em class="text-muted">-</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($t['biaya_total'] > 0): ?>
                                            <strong><?php echo format_rupiah($t['biaya_total']); ?></strong>
                                        <?php else: ?>
                                            <em class="text-muted">-</em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $t['status'] === 'keluar' ? 'secondary' : 'success'; ?>">
                                            <?php echo ucfirst($t['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $t['nama_lengkap']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada transaksi pada periode ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Sembunyikan elemen yang tidak perlu dicetak */
    .no-print, 
    .btn, 
    nav, 
    form, 
    .page-header a,
    .alert,
    .card-header h3::after {
        display: none !important;
    }

    /* Reset layout container */
    .container {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Perbaiki tampilan card */
    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .card-header {
        background: transparent !important;
        border-bottom: 2px solid #333 !important;
        padding-left: 0 !important;
    }

    /* Rapikan tabel */
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .table th, .table td {
        border: 1px solid #ddd !important;
        padding: 8px !important;
        font-size: 10pt !important;
    }

    .stat-card {
        border: 1px solid #ddd !important;
        margin-bottom: 10px !important;
        break-inside: avoid !important;
    }

    /* Header Laporan khusu cetak */
    body::before {
        content: "LAPORAN TRANSAKSI PARKIR";
        display: block;
        text-align: center;
        font-size: 18pt;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    body::after {
        content: "Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?>";
        display: block;
        text-align: right;
        font-size: 8pt;
        margin-top: 20px;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
