<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../middleware/auth_check.php';
require_admin();

// Get filter
$filter_user = $_GET['user'] ?? '';
$filter_date = $_GET['date'] ?? '';

// Build query
$query = "SELECT l.*, u.nama_lengkap, u.role 
          FROM tb_log_aktivitas l 
          JOIN tb_user u ON l.id_user = u.id_user 
          WHERE 1=1";

if ($filter_user) {
    $query .= " AND l.id_user = :user";
}
if ($filter_date) {
    $query .= " AND DATE(l.waktu_aktivitas) = :date";
}

$query .= " ORDER BY l.waktu_aktivitas DESC LIMIT 100";

$stmt = $db->prepare($query);
if ($filter_user) {
    $stmt->bindParam(':user', $filter_user);
}
if ($filter_date) {
    $stmt->bindParam(':date', $filter_date);
}
$stmt->execute();
$logs = $stmt->fetchAll();

// Get all users untuk filter
$users = $db->query("SELECT id_user, nama_lengkap FROM tb_user ORDER BY nama_lengkap")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Log Aktivitas</h1>
        <div class="header-actions">
            <button onclick="window.print()" class="btn btn-success mr-2 no-print">🖨️ Cetak Log</button>
            <a href="../dashboard.php" class="btn btn-secondary no-print">← Dashboard</a>
        </div>
    </div>

    <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3>Filter Log</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="filter-form">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="user">User</label>
                            <select id="user" name="user" class="form-control">
                                <option value="">-- Semua User --</option>
                                <?php foreach($users as $user): ?>
                                    <option value="<?php echo $user['id_user']; ?>" 
                                        <?php echo $filter_user == $user['id_user'] ? 'selected' : ''; ?>>
                                        <?php echo $user['nama_lengkap']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date">Tanggal</label>
                            <input type="date" id="date" name="date" class="form-control" 
                                   value="<?php echo $filter_date; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="index.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3>Daftar Aktivitas (100 Terbaru)</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach($logs as $log): ?>
                            <tr>
                                <td><?php echo format_datetime($log['waktu_aktivitas']); ?></td>
                                <td><?php echo $log['nama_lengkap']; ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $log['role'] === 'admin' ? 'danger' : 
                                            ($log['role'] === 'petugas' ? 'primary' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($log['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo $log['aktivitas']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Belum ada log aktivitas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, 
    .btn, 
    nav, 
    form, 
    .page-header a,
    .alert {
        display: none !important;
    }

    .container {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .table th, .table td {
        border: 1px solid #ddd !important;
        padding: 8px !important;
        font-size: 10pt !important;
    }

    body::before {
        content: "LOG AKTIVITAS SISTEM PARKIR";
        display: block;
        text-align: center;
        font-size: 18pt;
        font-weight: bold;
        margin-bottom: 10px;
    }
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
