<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth_check.php';

$root_path = $root_path ?? './';
$page_title = isset($page_title) ? $page_title . ' | BagiPangan' : 'BagiPangan | Berbagi makanan surplus';
$user = current_user();

// Flash messages
$flash_error = $_SESSION['flash_error'] ?? null;
$flash_success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $root_path ?>assets/css/style.css">
</head>
<body>

<div class="wrap">
    <nav>
        <a class="logo" href="<?= $root_path ?>index.php"><i></i>BagiPangan</a>
        <div class="nav-links">
            <a href="<?= $root_path ?>index.php#makanan">Katalog Makanan</a>
            <?php if ($user && in_array($user['role'], ['penyedia', 'admin'])): ?>
                <a href="<?= $root_path ?>donasi/tambah.php">+ Donasi Makanan</a>
                <a href="<?= $root_path ?>donasi/riwayat.php">Kelola Donasi</a>
            <?php endif; ?>
            <a href="<?= $root_path ?>index.php#dampak">Dampak</a>
            <a href="<?= $root_path ?>index.php#cerita">Cerita</a>
        </div>
        
        <div class="nav-user">
            <?php if ($user): ?>
                <a href="<?= $root_path ?>profil/index.php" class="user-badge">
                    <span><?= htmlspecialchars($user['nama']) ?></span>
                    <span class="role-tag"><?= htmlspecialchars($user['role']) ?></span>
                </a>
                <a class="btn sm" href="<?= $root_path ?>profil/index.php">Profil</a>
                <a class="btn sm fill" href="<?= $root_path ?>auth/logout.php">Keluar</a>
            <?php else: ?>
                <a class="btn sm" href="<?= $root_path ?>auth/login.php">Masuk</a>
                <a class="btn sm fill" href="<?= $root_path ?>auth/register.php">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<div class="wrap" style="margin-top: 20px;">
    <?php if ($flash_error): ?>
        <div class="alert alert-danger">
            <strong>Gagal:</strong> <?= htmlspecialchars($flash_error) ?>
        </div>
    <?php endif; ?>
    <?php if ($flash_success): ?>
        <div class="alert alert-success">
            <strong>Sukses:</strong> <?= htmlspecialchars($flash_success) ?>
        </div>
    <?php endif; ?>
</div>

<main class="main-content">
