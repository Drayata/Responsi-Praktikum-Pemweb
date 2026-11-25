<?php
// profil/index.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$root_path = base_url('');
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
<?php if (($profile['role'] ?? '') === 'penerima'): ?>
    <?php
    $stmt_riwayat = $pdo->prepare("
        SELECT p.*, d.nama_makanan, u.nama AS nama_penyedia,
               (SELECT COUNT(*) FROM ulasan ul WHERE ul.pesanan_id = p.id) AS sudah_diulas
        FROM pesanan p
        JOIN donasi d ON p.donasi_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE p.user_id = ?
        ORDER BY p.id DESC
    ");
    $stmt_riwayat->execute([$profile['id']]);
    $riwayat_pesanan = $stmt_riwayat->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="wrap" style="max-width: 1000px; margin: 30px auto;">
        <div style="background: #fff; padding: 28px; border-radius: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
            <h2 style="font-family: 'Anton', sans-serif; font-size: 1.8rem; margin-top: 0;">RIWAYAT KLAIM & ULASAN SAYA</h2>

            <?php if (empty($riwayat_pesanan)): ?>
                <p style="color: #666; margin: 0;">Belum ada riwayat pengambilan makanan.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px;">
                    <?php foreach ($riwayat_pesanan as $rp): ?>
                        <div style="border: 1px solid #eee; border-radius: 16px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <div>
                                <strong style="font-size: 1.05rem;"><?= htmlspecialchars($rp['nama_makanan']) ?></strong>
                                <span style="color: #666; font-size: 0.9rem;"> • <?= (int)$rp['jumlah_porsi'] ?> porsi dari <?= htmlspecialchars($rp['nama_penyedia']) ?></span>
                                <div style="margin-top: 4px; font-size: 0.85rem; color: #555;">
                                    Kode Ambil: <strong><?= htmlspecialchars($rp['kode_ambil'] ?? '-') ?></strong> |
                                    Status: <strong><?= strtoupper(htmlspecialchars($rp['status'])) ?></strong>
                                </div>
                            </div>

                            <div>
                                <?php if ($rp['status'] === 'selesai' && (int)$rp['sudah_diulas'] === 0): ?>
                                    <a class="btn sm fill" href="../ulasan/tambah.php?pesanan_id=<?= $rp['id'] ?>">Beri Ulasan</a>
                                <?php elseif ((int)$rp['sudah_diulas'] > 0): ?>
                                    <span style="color: #2E7D32; font-weight: 600; font-size: 0.9rem;">✓ Sudah Diulas</span>
                                <?php else: ?>
                                    <span style="color: #888; font-size: 0.85rem;">Menunggu Selesai</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
