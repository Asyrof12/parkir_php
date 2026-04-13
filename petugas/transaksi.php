<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_petugas();

$error = '';
$success = '';
$transaksi_detail = null;

// Handle pencarian transaksi
if (isset($_GET['cari'])) {
    $no_parkir = clean_input($_GET['no_parkir']);
    
    if (!empty($no_parkir)) {
        try {
            $query = "SELECT t.*, k.plat_nomor, a.nama_area, ta.jenis_kendaraan, ta.tarif_per_jam 
                      FROM tb_transaksi t 
                      JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                      JOIN tb_area_parkir a ON t.id_area = a.id_area 
                      JOIN tb_tarif ta ON t.id_tarif = ta.id_tarif 
                      WHERE k.plat_nomor = :plat AND t.status = 'masuk' 
                      LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':plat', $no_parkir);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $transaksi_detail = $stmt->fetch();
                
                // Hitung durasi dan biaya
                $waktu_keluar = date('Y-m-d H:i:s');
                $durasi = hitung_durasi($transaksi_detail['waktu_masuk'], $waktu_keluar);
                $biaya = $durasi * $transaksi_detail['tarif_per_jam'];
                
                $transaksi_detail['waktu_keluar_calc'] = $waktu_keluar;
                $transaksi_detail['durasi'] = $durasi;
                $transaksi_detail['biaya_total_calc'] = $biaya;
            } else {
                $error = 'Transaksi tidak ditemukan atau kendaraan sudah keluar!';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = 'Nomor parkir harus diisi!';
    }
}

// Handle proses keluar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaksi_id = $_POST['transaksi_id'];
    $waktu_keluar = $_POST['waktu_keluar'];
    $biaya_total = $_POST['biaya_total'];
    
    try {
        $db->beginTransaction();

        // 1. Dapatkan id_area dari transaksi
        $stmt_get = $db->prepare("SELECT id_area FROM tb_transaksi WHERE id_parkir = :id");
        $stmt_get->execute([':id' => $transaksi_id]);
        $trx = $stmt_get->fetch();
        $id_area = $trx['id_area'] ?? null;

        // 2. Update Status Transaksi
        $query = "UPDATE tb_transaksi SET waktu_keluar = :waktu_keluar, 
                  biaya_total = :biaya_total, durasi_jam = :durasi, status = 'keluar' 
                  WHERE id_parkir = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':waktu_keluar', $waktu_keluar);
        $stmt->bindParam(':biaya_total', $biaya_total);
        $stmt->bindParam(':durasi', $_POST['durasi']);
        $stmt->bindParam(':id', $transaksi_id);
        
        if ($stmt->execute()) {
            // 3. Update Slot di Area (Hitung Ulang agar Akurat)
            if ($id_area) {
                $db->prepare("UPDATE tb_area_parkir SET terisi = (SELECT COUNT(*) FROM tb_transaksi WHERE id_area = :id AND status = 'masuk') WHERE id_area = :id")->execute([':id' => $id_area]);
            }

            $db->commit();
            log_activity($db, $_SESSION['id_user'], "Proses kendaraan keluar: " . $_POST['no_parkir']);
            set_flash('success', 'Transaksi berhasil diproses! Biaya: ' . format_rupiah($biaya_total));
            redirect('petugas/transaksi.php');
        }
    } catch(Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Transaksi Kendaraan Keluar</h1>
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
    </div>

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3>Cari Transaksi</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" name="no_parkir" class="form-control" 
                               placeholder="Masukkan Nomor Polisi / ID Kendaraan" 
                               required autofocus
                               value="<?php echo $_GET['no_parkir'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="cari" class="btn btn-primary btn-block">🔍 Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($transaksi_detail): ?>
    <div class="card mt-3">
        <div class="card-header bg-success text-white">
            <h3>Detail Transaksi</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">No. Parkir</th>
                            <td><strong><?php echo $transaksi_detail['plat_nomor']; ?></strong></td>
                        </tr>
                        <tr>
                            <th>Jenis Kendaraan</th>
                            <td><?php echo ucfirst($transaksi_detail['jenis_kendaraan']); ?></td>
                        </tr>
                        <tr>
                            <th>Area Parkir</th>
                            <td><?php echo $transaksi_detail['nama_area']; ?></td>
                        </tr>
                        <tr>
                            <th>Waktu Masuk</th>
                            <td><?php echo format_datetime($transaksi_detail['waktu_masuk']); ?></td>
                        </tr>
                        <tr>
                            <th>Waktu Keluar</th>
                            <td><strong><?php echo format_datetime($transaksi_detail['waktu_keluar_calc']); ?></strong></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Durasi Parkir</th>
                            <td>
                                <strong><?php echo $transaksi_detail['durasi']; ?> Jam</strong>
                                <?php if ($transaksi_detail['durasi'] == 0): ?>
                                    <span class="badge badge-success ml-2">Grace Period</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Tarif per Jam</th>
                            <td><?php echo format_rupiah($transaksi_detail['tarif_per_jam']); ?></td>
                        </tr>
                        <tr>
                            <th>Total Biaya</th>
                            <td>
                                <h3 class="text-success mb-0">
                                    <?php 
                                    if ($transaksi_detail['durasi'] == 0) {
                                        echo "GRATIS";
                                    } else {
                                        echo format_rupiah($transaksi_detail['biaya_total_calc']); 
                                    }
                                    ?>
                                </h3>
                                <?php if ($transaksi_detail['durasi'] == 0): ?>
                                    <small class="text-muted">(Keluar dalam < 5 menit)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <form method="POST" action="">
                        <input type="hidden" name="transaksi_id" value="<?php echo $transaksi_detail['id_parkir']; ?>">
                        <input type="hidden" name="no_parkir" value="<?php echo $transaksi_detail['plat_nomor']; ?>">
                        <input type="hidden" name="waktu_keluar" value="<?php echo $transaksi_detail['waktu_keluar_calc']; ?>">
                        <input type="hidden" name="durasi" value="<?php echo $transaksi_detail['durasi']; ?>">
                        <input type="hidden" name="biaya_total" value="<?php echo $transaksi_detail['biaya_total_calc']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="cetak_struk_keluar.php?id=<?php echo $transaksi_detail['id_parkir']; ?>&waktu_keluar=<?php echo urlencode($transaksi_detail['waktu_keluar_calc']); ?>&durasi=<?php echo $transaksi_detail['durasi']; ?>&biaya=<?php echo $transaksi_detail['biaya_total_calc']; ?>" target="_blank" class="btn btn-info btn-lg btn-block w-100" style="padding: 10px; text-align: center;">
                                    🖨️ Cetak Struk
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="submit" class="btn btn-success btn-lg btn-block w-100" style="padding: 10px;"
                                        onclick="return confirm('Proses <?php echo $transaksi_detail['durasi'] == 0 ? 'parkir GRATIS' : 'pembayaran sebesar ' . format_rupiah($transaksi_detail['biaya_total_calc']); ?>?')">
                                    💰 Proses Pembayaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Daftar kendaraan yang sedang parkir -->
    <div class="card mt-3">
        <div class="card-header">
            <h3>Kendaraan Sedang Parkir</h3>
        </div>
        <div class="card-body">
            <?php
            $query = "SELECT t.*, k.plat_nomor, a.nama_area, a.kapasitas, a.terisi, ta.jenis_kendaraan 
                      FROM tb_transaksi t 
                      JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                      JOIN tb_area_parkir a ON t.id_area = a.id_area 
                      JOIN tb_tarif ta ON t.id_tarif = ta.id_tarif 
                      WHERE t.status = 'masuk' 
                      ORDER BY t.waktu_masuk DESC 
                      LIMIT 10";
            $parkir_aktif = $db->query($query)->fetchAll();
            ?>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Parkir</th>
                        <th>Jenis</th>
                        <th>Area</th>
                        <th>Waktu Masuk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($parkir_aktif)): ?>
                        <?php foreach($parkir_aktif as $p): ?>
                            <tr>
                                <td><strong><?php echo $p['plat_nomor']; ?></strong></td>
                                <td><?php echo ucfirst($p['jenis_kendaraan']); ?></td>
                                <td>
                                    <?php echo $p['nama_area']; ?>
                                    <br>
                                    <small class="text-muted">
                                        (Sisa: <?php echo $p['kapasitas'] - $p['terisi']; ?>)
                                    </small>
                                </td>
                                <td><?php echo format_datetime($p['waktu_masuk']); ?></td>
                                <td>
                                    <a href="?no_parkir=<?php echo $p['plat_nomor']; ?>&cari=1" 
                                       class="btn btn-sm btn-primary">Proses</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada kendaraan yang sedang parkir</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
