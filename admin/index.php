<?php
// admin/index.php - Dashboard Admin
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
session_start();

require_login();
require_role('admin');

$root_path = base_url('');
$page_title = 'Panel Admin | BagiPangan';
$user = current_user();

// Statistik untuk dashboard admin
try {
    $total_users = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_penyedia = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'penyedia'")->fetchColumn();
    $total_penerima = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'penerima'")->fetchColumn();
    $total_donasi = (int)$pdo->query("SELECT COUNT(*) FROM donasi")->fetchColumn();
    $donasi_aktif = (int)$pdo->query("SELECT COUNT(*) FROM donasi WHERE status = 'tersedia' AND batas_waktu > NOW()")->fetchColumn();
    $total_pesanan = (int)$pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
    $pesanan_selesai = (int)$pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'selesai'")->fetchColumn();
    $total_ulasan = (int)$pdo->query("SELECT COUNT(*) FROM ulasan")->fetchColumn();
} catch (Exception $e) {
    $total_users = $total_penyedia = $total_penerima = 0;
    $total_donasi = $donasi_aktif = 0;
    $total_pesanan = $pesanan_selesai = 0;
    $total_ulasan = 0;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h1>Panel Admin</h1>
        <a href="<?= $root_path ?>" class="btn">Kembali ke Beranda</a>
    </div>

    <p class="text-muted" style="margin-bottom: 32px;">Kelola pengguna, donasi, kategori, dan moderasi platform BagiPangan.</p>

    <!-- Stats Overview -->
    <div class="stats" style="margin-bottom: 40px;">
        <div class="stat">
            <small>Total Pengguna</small>
            <div>
                <div class="n"><?= number_format($total_users) ?></div>
                <p>Penyedia: <?= $total_penyedia ?> | Penerima: <?= $total_penerima ?></p>
            </div>
        </div>
        <div class="stat">
            <small>Total Donasi</small>
            <div>
                <div class="n"><?= number_format($total_donasi) ?></div>
                <p>Aktif: <?= $donasi_aktif ?> | Habis/Selesai: <?= $total_donasi - $donasi_aktif ?></p>
            </div>
        </div>
        <div class="stat">
            <small>Total Pesanan</small>
            <div>
                <div class="n"><?= number_format($total_pesanan) ?></div>
                <p>Selesai: <?= $pesanan_selesai ?> (<?= $total_pesanan > 0 ? round($pesanan_selesai / $total_pesanan * 100) : 0 ?>%)</p>
            </div>
        </div>
        <div class="stat">
            <small>Total Ulasan</small>
            <div>
                <div class="n"><?= number_format($total_ulasan) ?></div>
                <p>Rating komunitas</p>
            </div>
        </div>
    </div>

    <!-- Menu Admin -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <a href="<?= $root_path ?>/admin/users.php" class="card" style="padding: 28px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 28px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div style="font-size: 40px; margin-bottom: 12px;">👥</div>
            <h3 style="margin-bottom: 8px;">Kelola Pengguna</h3>
            <p class="text-muted">Lihat, filter, nonaktifkan, atau hapus akun pengguna</p>
            <div style="margin-top: 16px; font-size: 12px; font-weight: 600; color: var(--orange);">Total: <?= number_format($total_users) ?> pengguna</div>
        </a>

        <a href="<?= $root_path ?>/admin/donasi.php" class="card" style="padding: 28px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 28px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div style="font-size: 40px; margin-bottom: 12px;">🍽️</div>
            <h3 style="margin-bottom: 8px;">Moderasi Donasi</h3>
            <p class="text-muted">Hapus donasi tidak higienis atau melanggar aturan</p>
            <div style="margin-top: 16px; font-size: 12px; font-weight: 600; color: var(--orange);">Aktif: <?= number_format($donasi_aktif) ?> donasi</div>
        </a>

        <a href="<?= $root_path ?>/admin/kategori.php" class="card" style="padding: 28px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 28px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div style="font-size: 40px; margin-bottom: 12px;">🏷️</div>
            <h3 style="margin-bottom: 8px;">Kelola Kategori</h3>
            <p class="text-muted">Tambah, edit, atau hapus kategori makanan</p>
            <div style="margin-top: 16px; font-size: 12px; font-weight: 600; color: var(--orange);">Kelola master data</div>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>