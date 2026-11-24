<?php
// profil/index.php
session_start();
$root_path = '../';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if (!$profile) {
    header("Location: ../auth/logout.php");
    exit;
}

// Hitung statistik user jika penyedia
$total_donasi = 0;
$donasi_aktif = 0;
if ($profile['role'] === 'penyedia') {
    $st_total = $pdo->prepare("SELECT COUNT(*) FROM donasi WHERE user_id = ?");
    $st_total->execute([$user_id]);
    $total_donasi = $st_total->fetchColumn();

    $st_aktif = $pdo->prepare("SELECT COUNT(*) FROM donasi WHERE user_id = ? AND status = 'tersedia'");
    $st_aktif->execute([$user_id]);
    $donasi_aktif = $st_aktif->fetchColumn();
}

$page_title = 'Profil Saya';
require_once __DIR__ . '/../includes/header.php';

$avatar_path = !empty($profile['foto']) && file_exists(__DIR__ . '/../uploads/avatars/' . $profile['foto'])
    ? $root_path . 'uploads/avatars/' . htmlspecialchars($profile['foto'])
    : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23FF6B3D'/%3E%3Ctext x='50' y='58' font-family='sans-serif' font-size='38' fill='%23fff' text-anchor='middle'%3E" . strtoupper(substr($profile['nama'], 0, 1)) . "%3C/text%3E%3C/svg%3E";
?>

<div class="wrap">
    <div class="page-header">
        <h1>Profil Pengguna</h1>
        <div style="display: flex; gap: 10px;">
            <a href="edit.php" class="btn fill">Ubah Profil</a>
            <a href="ganti-password.php" class="btn">Ganti Sandi</a>
        </div>
    </div>

    <div class="profile-grid">
        <!-- Sidebar Kartu Profil -->
        <div class="card-box profile-sidebar">
            <img src="<?= $avatar_path ?>" alt="Foto Profil" class="avatar-preview">
            <h3 style="font-size: 26px;"><?= htmlspecialchars($profile['nama']) ?></h3>
            <p style="color: var(--muted); font-size: 13px; margin: 4px 0 14px;"><?= htmlspecialchars($profile['email']) ?></p>
            <span class="status-badge status-tersedia" style="text-transform: uppercase;">
                Role: <?= htmlspecialchars($profile['role']) ?>
            </span>

            <?php if ($profile['role'] === 'penyedia'): ?>
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--line); text-align: left;">
                    <small style="color: var(--muted); text-transform: uppercase; font-size: 11px; font-weight: 600;">Kontribusi Makanan</small>
                    <div style="display: flex; justify-content: space-between; margin-top: 8px;">
                        <span>Total Donasi Dibuat:</span>
                        <strong><?= $total_donasi ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                        <span>Donasi Aktif:</span>
                        <strong style="color: var(--orange);"><?= $donasi_aktif ?></strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Detail Data Akun -->
        <div class="card-box">
            <h3 style="font-size: 24px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--line);">
                Informasi Kontak & Titik Ambil
            </h3>

            <div class="form-group">
                <label>Nama Lengkap / Nama Entitas</label>
                <div class="form-control" style="background: var(--white); font-weight: 500;">
                    <?= htmlspecialchars($profile['nama']) ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Alamat Email</label>
                    <div class="form-control" style="background: var(--white);">
                        <?= htmlspecialchars($profile['email']) ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp / HP</label>
                    <div class="form-control" style="background: var(--white);">
                        <?= htmlspecialchars($profile['no_hp']) ?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat Penjemputan / Lokasi Domisili</label>
                <div class="form-control" style="background: var(--white); min-height: 80px; white-space: pre-wrap;">
                    <?= htmlspecialchars($profile['alamat']) ?>
                </div>
            </div>

            <div class="form-group">
                <label>Bergabung Sejak</label>
                <p style="color: var(--muted); font-size: 13px;">
                    <?= date('d F Y, H:i', strtotime($profile['created_at'])) ?> WIB
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
