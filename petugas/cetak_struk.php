<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_petugas();

$error = '';
$success = '';
$struk_data = null;

// Get area dan tarif untuk dropdown
$areas = $db->query("SELECT * FROM tb_area_parkir ORDER BY nama_area")->fetchAll();
$tarif_list = $db->query("SELECT * FROM tb_tarif ORDER BY jenis_kendaraan")->fetchAll();

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_parkir = clean_input($_POST['no_parkir']);
    $id_area = clean_input($_POST['id_area']);
    $id_tarif = clean_input($_POST['id_tarif']);
    $waktu_masuk = date('Y-m-d H:i:s');

    if (!empty($no_parkir) && !empty($id_area) && !empty($id_tarif)) {
        try {
            $db->beginTransaction();

            // 1. Cek Kapasitas Area
            $stmt_area = $db->prepare("SELECT kapasitas, terisi FROM tb_area_parkir WHERE id_area = :id FOR UPDATE");
            $stmt_area->execute([':id' => $id_area]);
            $area_info = $stmt_area->fetch();

            if ($area_info['terisi'] >= $area_info['kapasitas']) {
                throw new Exception("Area parkir sudah penuh!");
            }

            // 2. Cek/Simpan Kendaraan
            $stmt_k = $db->prepare("SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = :plat LIMIT 1");
            $stmt_k->execute([':plat' => $no_parkir]);
            $kendaraan = $stmt_k->fetch();

            if ($kendaraan) {
                $id_kendaraan = $kendaraan['id_kendaraan'];
            } else {
                // Buat kendaraan baru otomatis jika belum ada
                $stmt_new_k = $db->prepare("INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, pemilik, id_user) 
                                           VALUES (:plat, 'lainnya', '-', 'Umum', :id_user)");
                $stmt_new_k->execute([
                    ':plat' => $no_parkir,
                    ':id_user' => $_SESSION['id_user']
                ]);
                $id_kendaraan = $db->lastInsertId();
            }

            // 2. Insert transaksi
            $query = "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, id_user, id_area, status) 
                      VALUES (:id_kendaraan, :waktu_masuk, :id_tarif, :id_user, :id_area, 'masuk')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_kendaraan', $id_kendaraan);
            $stmt->bindParam(':waktu_masuk', $waktu_masuk);
            $stmt->bindParam(':id_tarif', $id_tarif);
            $stmt->bindParam(':id_user', $_SESSION['id_user']);
            $stmt->bindParam(':id_area', $id_area);
            
            if ($stmt->execute()) {
                $transaksi_id = $db->lastInsertId();
                
                // 4. Update Slot Terisi di Area (Hitung Ulang agar Akurat)
                $db->prepare("UPDATE tb_area_parkir SET terisi = (SELECT COUNT(*) FROM tb_transaksi WHERE id_area = :id AND status = 'masuk') WHERE id_area = :id")->execute([':id' => $id_area]);

                $db->commit();
                
                // Get data untuk struk
                $query = "SELECT t.*, k.plat_nomor, a.nama_area, ta.jenis_kendaraan, ta.tarif_per_jam 
                          FROM tb_transaksi t 
                          JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
                          JOIN tb_area_parkir a ON t.id_area = a.id_area 
                          JOIN tb_tarif ta ON t.id_tarif = ta.id_tarif 
                          WHERE t.id_parkir = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $transaksi_id);
                $stmt->execute();
                $struk_data = $stmt->fetch();
                
                log_activity($db, $_SESSION['id_user'], "Input kendaraan masuk: $no_parkir");
                $success = 'Transaksi berhasil! Silakan cetak struk.';
            }
        } catch(PDOException $e) {
            $db->rollBack();
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = 'Semua field harus diisi!';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Cetak Struk Parkir</h1>
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Input Kendaraan Masuk</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="no_parkir">Nomor Polisi / ID Kendaraan *</label>
                            <input type="text" id="no_parkir" name="no_parkir" class="form-control" 
                                   required autofocus placeholder="Contoh: B 1234 XYZ">
                        </div>

                        <div class="form-group">
                            <label for="id_tarif">Jenis Kendaraan *</label>
                            <select id="id_tarif" name="id_tarif" class="form-control" required>
                                <option value="">-- Pilih Jenis Kendaraan --</option>
                                <?php foreach($tarif_list as $tarif): ?>
                                    <option value="<?php echo $tarif['id_tarif']; ?>">
                                        <?php echo ucfirst($tarif['jenis_kendaraan']); ?> - 
                                        <?php echo format_rupiah($tarif['tarif_per_jam']); ?>/jam
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_area">Area Parkir *</label>
                            <select id="id_area" name="id_area" class="form-control" required onchange="checkCapacity(this)">
                                <option value="">-- Pilih Area --</option>
                                <?php foreach($areas as $area): 
                                    $sisa = $area['kapasitas'] - $area['terisi'];
                                    $is_full = $sisa <= 0;
                                ?>
                                    <option value="<?php echo $area['id_area']; ?>" 
                                            data-sisa="<?php echo $sisa; ?>"
                                            <?php echo $is_full ? 'disabled class="text-danger"' : ''; ?>>
                                        <?php echo $area['nama_area']; ?> 
                                        (Sisa: <?php echo $sisa; ?>) 
                                        <?php echo $is_full ? '- PENUH' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small id="capacity-warning" class="text-danger" style="display:none;"></small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Simpan & Cetak Struk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($struk_data): ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Struk Parkir</h3>
                </div>
                <div class="card-body" id="struk-content">
                    <div class="struk">
                        <h2 class="text-center">🅿️ STRUK PARKIR</h2>
                        <hr>
                        <table class="struk-table">
                            <tr>
                                <td>No. Parkir</td>
                                <td>:</td>
                                <td><strong><?php echo $struk_data['plat_nomor']; ?></strong></td>
                            </tr>
                            <tr>
                                <td>Jenis Kendaraan</td>
                                <td>:</td>
                                <td><?php echo ucfirst($struk_data['jenis_kendaraan']); ?></td>
                            </tr>
                            <tr>
                                <td>Area Parkir</td>
                                <td>:</td>
                                <td><?php echo $struk_data['nama_area']; ?></td>
                            </tr>
                            <tr>
                                <td>Waktu Masuk</td>
                                <td>:</td>
                                <td><strong><?php echo format_datetime($struk_data['waktu_masuk']); ?></strong></td>
                            </tr>
                            <tr>
                                <td>Tarif per Jam</td>
                                <td>:</td>
                                <td><?php echo format_rupiah($struk_data['tarif_per_jam']); ?></td>
                            </tr>
                        </table>
                        <hr>
                        <p class="text-center"><small>Simpan struk ini untuk pembayaran</small></p>
                    </div>
                </div>
                <div class="card-footer">
                    <button onclick="window.print()" class="btn btn-success btn-block">🖨️ Print Struk</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
        color: #000 !important;
        background: #fff !important;
    }
    #struk-content, #struk-content * {
        visibility: visible !important;
        color: #000 !important;
        background: transparent !important;
    }
    #struk-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: #fff !important;
    }
    .struk {
        border: 2px dashed #000 !important;
        background: #fff !important;
    }
}

.struk {
    padding: 20px;
    border: 2px dashed #333 !important;
    background: #ffffff !important;
    color: #111111 !important;
}

.struk *,
.struk td,
.struk th,
.struk p,
.struk h2,
.struk small,
.struk strong {
    color: #111111 !important;
    background: transparent !important;
}

.struk-table {
    width: 100%;
    margin: 15px 0;
}

.struk-table td {
    padding: 5px;
    color: #111111 !important;
}

.struk-table td:first-child {
    width: 40%;
}

.struk-table td:nth-child(2) {
    width: 5%;
}
</style>

<script>
function checkCapacity(select) {
    const selectedOption = select.options[select.selectedIndex];
    const sisa = parseInt(selectedOption.getAttribute('data-sisa'));
    const warning = document.getElementById('capacity-warning');
    
    if (sisa <= 0) {
        warning.innerText = "⚠️ AREA PENUH! Silakan pilih area lain.";
        warning.style.display = 'block';
        alert("Konfirmasi: Area parkir yang Anda pilih sudah penuh!");
    } else if (sisa <= 5) {
        warning.innerText = "⚠️ Slot hampir habis (Sisa " + sisa + ").";
        warning.style.display = 'block';
    } else {
        warning.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
