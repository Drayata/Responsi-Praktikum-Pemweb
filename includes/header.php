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
            <a href="<?= $root_path ?>index.php#makanan">Katalog</a>
            <?php if ($user && $user['role'] === 'penerima'): ?>
                <a href="<?= $root_path ?>pesanan/riwayat.php">Pesanan Saya</a>
            <?php endif; ?>
            <?php if ($user && in_array($user['role'], ['penyedia', 'admin'])): ?>
                <a href="<?= $root_path ?>donasi/tambah.php">+ Donasi</a>
                <a href="<?= $root_path ?>donasi/riwayat.php">Kelola Donasi</a>
                <a href="<?= $root_path ?>pesanan/verifikasi.php">Verifikasi</a>
            <?php endif; ?>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <a href="<?= $root_path ?>admin/index.php" style="color: var(--orange);">Panel Admin</a>
            <?php endif; ?>
            <a href="<?= $root_path ?>dampak/index.php">Dampak</a>
        </div>
        
        <div class="nav-user">
            <?php if ($user): ?>
                <a href="<?= $root_path ?>notifikasi/index.php" class="nav-notif" aria-label="Notifikasi" style="position: relative; text-decoration: none; color: var(--ink);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: block;">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <?php if ($user['role'] !== 'penerima'): ?>
                        <span id="notifikasi-badge" style="display: none;"></span>
                    <?php endif; ?>
                </a>
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
            <button type="button" id="navToggle" class="nav-toggle" aria-label="Buka menu navigasi" aria-expanded="false">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
    </nav>

    <div id="mobileMenu" class="mobile-menu">
        <?php if ($user): ?>
            <div class="mobile-user-info">
                <span><?= htmlspecialchars($user['nama']) ?></span>
                <span class="role-tag" style="background: var(--cream); padding: 2px 10px; border-radius: 999px; font-size: 11px; color: var(--rust); text-transform: uppercase;"><?= htmlspecialchars($user['role']) ?></span>
            </div>
        <?php endif; ?>
        <a href="<?= $root_path ?>index.php#makanan">Katalog Makanan</a>
        <?php if ($user && $user['role'] === 'penerima'): ?>
            <a href="<?= $root_path ?>pesanan/riwayat.php">Pesanan Saya</a>
        <?php endif; ?>
        <?php if ($user && in_array($user['role'], ['penyedia', 'admin'])): ?>
            <a href="<?= $root_path ?>donasi/tambah.php" style="color: var(--orange);">+ Tambah Donasi Makanan</a>
            <a href="<?= $root_path ?>donasi/riwayat.php">Kelola Donasi</a>
            <a href="<?= $root_path ?>pesanan/verifikasi.php">Verifikasi Pengambilan</a>
        <?php endif; ?>
        <?php if ($user && $user['role'] === 'admin'): ?>
            <a href="<?= $root_path ?>admin/index.php" style="color: var(--orange);">Panel Admin</a>
        <?php endif; ?>
        <a href="<?= $root_path ?>dampak/index.php">Dashboard Dampak</a>
        <?php if ($user): ?>
            <a href="<?= $root_path ?>notifikasi/index.php">Notifikasi</a>
            <div class="mobile-actions">
                <a class="btn sm" href="<?= $root_path ?>profil/index.php">Profil</a>
                <a class="btn sm fill" href="<?= $root_path ?>auth/logout.php">Keluar</a>
            </div>
        <?php else: ?>
            <div class="mobile-actions">
                <a class="btn sm" href="<?= $root_path ?>auth/login.php">Masuk</a>
                <a class="btn sm fill" href="<?= $root_path ?>auth/register.php">Daftar</a>
            </div>
        <?php endif; ?>
    </div>
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
