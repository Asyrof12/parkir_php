<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_petugas();

if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id_parkir = clean_input($_GET['id']);

try {
    $query = "SELECT t.*, k.plat_nomor, a.nama_area, ta.jenis_kendaraan, ta.tarif_per_jam, u.nama_lengkap as nama_petugas
              FROM tb_transaksi t 
              JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
              JOIN tb_area_parkir a ON t.id_area = a.id_area 
              JOIN tb_tarif ta ON t.id_tarif = ta.id_tarif 
              JOIN tb_user u ON t.id_user = u.id_user
              WHERE t.id_parkir = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id_parkir]);
    
    if ($stmt->rowCount() == 0) {
        die("Transaksi tidak ditemukan.");
    }
    
    $trx = $stmt->fetch();
    
    // Jika belum keluar, hitung data real-time, jika bukan ambil parameter jika ada.
    $waktu_keluar = $trx['waktu_keluar'];
    $durasi = $trx['durasi_jam'];
    $biaya = $trx['biaya_total'];
    
    if ($trx['status'] === 'masuk') {
        // Cek jika diset lewat param
        if (isset($_GET['waktu_keluar'])) {
            $waktu_keluar = clean_input($_GET['waktu_keluar']);
            $durasi = clean_input($_GET['durasi']);
            $biaya = clean_input($_GET['biaya']);
        } else {
            $waktu_keluar = date('Y-m-d H:i:s');
            $durasi = hitung_durasi($trx['waktu_masuk'], $waktu_keluar);
            $biaya = $durasi * $trx['tarif_per_jam'];
        }
    }
    
} catch(PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi Keluar #<?php echo $trx['plat_nomor']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        /* Struk styling for screen and print */
        .struk {
            padding: 20px;
            border: 2px dashed #333 !important;
            background: #ffffff !important;
            color: #111111 !important;
            margin: 20px auto;
            max-width: 400px;
            font-family: Arial, sans-serif;
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

        h2, h3, p {
            margin: 0 0 10px;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 15px;
            text-align: center;
        }

        table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }

        td {
            padding: 5px;
            color: #111111 !important;
            font-size: 14px;
        }

        .td-label {
            width: 45%;
        }

        .td-colon {
            width: 5%;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .border-top {
            border-top: 1px dashed #333;
            padding-top: 5px;
        }

        .border-bottom {
            border-bottom: 1px dashed #333;
            padding-bottom: 5px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body {
                display: flex !important;
                justify-content: center !important;
                align-items: flex-start !important;
                padding-top: 20px !important;
            }
            .struk {
                background: #ffffff !important;
                color: #000000 !important;
                border: 2px dashed #000000 !important;
                display: block !important;
                padding: 30px !important;
                width: 100% !important;
                max-width: 380px !important;
                margin: 0 auto !important;
                box-sizing: border-box !important;
            }
            .struk * {
                color: #000000 !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body>
    <div class="struk">
        <h2>🅿️ SISTEM PARKIR</h2>
        <p class="center" style="font-size: 14px; margin-bottom: 20px;">BUKTI PEMBAYARAN TRANSAKSI KELUAR</p>
        
        <table>
            <tr><td colspan="3" class="border-bottom"></td></tr>
            <tr>
                <td class="td-label">No. Parkir</td>
                <td class="td-colon">:</td>
                <td><strong><?php echo $trx['plat_nomor']; ?></strong></td>
            </tr>
            <tr>
                <td class="td-label">Jenis Kendaraan</td>
                <td class="td-colon">:</td>
                <td><?php echo ucfirst($trx['jenis_kendaraan']); ?></td>
            </tr>
            <tr>
                <td class="td-label">Area Parkir</td>
                <td class="td-colon">:</td>
                <td><?php echo $trx['nama_area']; ?></td>
            </tr>
            <tr><td colspan="3" class="border-bottom"></td></tr>
            
            <tr>
                <td class="td-label">Waktu Masuk</td>
                <td class="td-colon">:</td>
                <td><?php echo format_datetime($trx['waktu_masuk']); ?></td>
            </tr>
            <tr>
                <td class="td-label">Waktu Keluar</td>
                <td class="td-colon">:</td>
                <td><?php echo format_datetime($waktu_keluar); ?></td>
            </tr>
            <tr>
                <td class="td-label">Durasi</td>
                <td class="td-colon">:</td>
                <td><?php echo $durasi; ?> Jam</td>
            </tr>
            <tr>
                <td class="td-label">Tarif/Jam</td>
                <td class="td-colon">:</td>
                <td><?php echo format_rupiah($trx['tarif_per_jam']); ?></td>
            </tr>
            <tr><td colspan="3" class="border-top border-bottom"></td></tr>
            <tr>
                <td class="td-label"><strong>TOTAL BIAYA</strong></td>
                <td class="td-colon">:</td>
                <td><strong><?php echo $durasi == 0 ? 'GRATIS' : format_rupiah($biaya); ?></strong></td>
            </tr>
            <tr><td colspan="3" class="border-top"></td></tr>
        </table>
        
        <p class="center" style="margin-top: 20px;"><small>Petugas: <?php echo htmlspecialchars($trx['nama_petugas']); ?></small></p>
        <p class="center" style="margin-top: 10px;">*** TERIMA KASIH HATI-HATI DI JALAN ***</p>
    </div>

    <div class="center no-print" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-family: Arial, sans-serif; font-size: 14px; margin-right: 10px; background: #28a745; color: white; border: none; border-radius: 4px;">🖨️ Cetak Struk</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; font-family: Arial, sans-serif; font-size: 14px; background: #6c757d; color: white; border: none; border-radius: 4px;">Tutup</button>
    </div>

</body>
</html>
