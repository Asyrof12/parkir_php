<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/auth_check.php';
require_petugas();

$user = get_user_info();

try {
    // Total transaksi keluar hari ini
    $stmt = $db->query("SELECT COUNT(*) as total FROM tb_transaksi WHERE DATE(waktu_keluar) = CURDATE() AND status = 'keluar'");
    $total_transaksi = $stmt->fetch()['total'];

    // Total pendapatan hari ini
    $stmt = $db->query("SELECT SUM(biaya_total) as total FROM tb_transaksi 
                        WHERE DATE(waktu_keluar) = CURDATE() AND status = 'keluar'");
    $total_pendapatan = $stmt->fetch()['total'] ?? 0;

    // Rincian per jenis kendaraan
    $stmt = $db->query("
        SELECT ta.jenis_kendaraan, COUNT(t.id_parkir) as jumlah, SUM(t.biaya_total) as total
        FROM tb_transaksi t
        JOIN tb_tarif ta ON t.id_tarif = ta.id_tarif
        WHERE DATE(t.waktu_keluar) = CURDATE() AND t.status = 'keluar'
        GROUP BY ta.id_tarif
    ");
    $rincian = $stmt->fetchAll();

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pendapatan <?php echo date('d-m-Y'); ?></title>
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

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .border-top {
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        .border-bottom {
            border-bottom: 1px solid #333;
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
        <h2>SISTEM PARKIR</h2>
        <p>LAPORAN PENDAPATAN HARIAN</p>
        <p>Tanggal: <?php echo date('d-m-Y'); ?></p>
        <p>Dicetak oleh: <?php echo htmlspecialchars($user['nama'] ?? 'Petugas'); ?></p>
        
        <table>
            <tr><td colspan="2" class="border-bottom"></td></tr>
            <tr>
                <td>Kendaraan Keluar:</td>
                <td class="right"><?php echo $total_transaksi; ?></td>
            </tr>
            <tr><td colspan="2" class="border-top"></td></tr>
            
            <?php if (!empty($rincian)): ?>
                <tr>
                    <td colspan="2" class="center"><br><strong>Rincian by Kendaraan:</strong></td>
                </tr>
                <?php foreach($rincian as $r): ?>
                    <tr>
                        <td><?php echo ucfirst($r['jenis_kendaraan']); ?> (<?php echo $r['jumlah']; ?>)</td>
                        <td class="right"><?php echo format_rupiah($r['total']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr><td colspan="2" class="border-bottom"></td></tr>
            <?php endif; ?>
            
            <tr>
                <td><strong>Total Pendapatan:</strong></td>
                <td class="right"><strong><?php echo format_rupiah($total_pendapatan); ?></strong></td>
            </tr>
            <tr><td colspan="2" class="border-top"></td></tr>
        </table>
        
        <p class="center"><br>Waktu Cetak:<br><?php echo date('d-m-Y H:i:s'); ?></p>
        <p class="center">*** TERIMA KASIH ***</p>
    </div>

    <div class="center no-print" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-family: monospace; font-size: 14px; margin-right: 10px; background: #28a745; color: white; border: none; border-radius: 4px;">🖨️ Cetak</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; font-family: monospace; font-size: 14px; background: #6c757d; color: white; border: none; border-radius: 4px;">Tutup</button>
    </div>
</body>
</html>
