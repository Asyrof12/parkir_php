<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sistem Parkir</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php if (is_logged_in()): ?>
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="<?php echo BASE_URL; ?>">
                        <h2>🅿️ Sistem Parkir</h2>
                    </a>
                </div>
                
                <div class="nav-menu">
                    <?php $user = get_user_info(); ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="nav-link">Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>admin/users/index.php" class="nav-link">Users</a>
                        <a href="<?php echo BASE_URL; ?>admin/tarif/index.php" class="nav-link">Tarif</a>
                        <a href="<?php echo BASE_URL; ?>admin/area/index.php" class="nav-link">Area</a>
                        <a href="<?php echo BASE_URL; ?>admin/kendaraan/index.php" class="nav-link">Kendaraan</a>
                        <a href="<?php echo BASE_URL; ?>admin/log/index.php" class="nav-link">Log</a>
                    <?php elseif ($user['role'] === 'petugas'): ?>
                        <a href="<?php echo BASE_URL; ?>petugas/dashboard.php" class="nav-link">Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>petugas/cetak_struk.php" class="nav-link">Cetak Struk</a>
                        <a href="<?php echo BASE_URL; ?>petugas/transaksi.php" class="nav-link">Transaksi</a>
                    <?php elseif ($user['role'] === 'owner'): ?>
                        <a href="<?php echo BASE_URL; ?>owner/dashboard.php" class="nav-link">Dashboard</a>
                        <a href="<?php echo BASE_URL; ?>owner/rekap.php" class="nav-link">Rekap Transaksi</a>
                    <?php endif; ?>
                </div>

                <div class="nav-user">
                    <div class="user-info">
                        <span class="user-name"><?php echo $user['nama']; ?></span>
                        <span class="user-role"><?php echo ucfirst($user['role']); ?></span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <main class="main-content">
