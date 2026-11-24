<?php
// pesanan/riwayat.php - Riwayat pesanan penerima
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notifikasi.php';

require_login();
require_role('penerima');

$root_path = '/Responsi-Praktikum-Pemweb';
$page_title = 'Riwayat Pesanan | BagiPangan';
$user = current_user();

// Proses batalkan pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batalkan_id'])) {
    $pesanan_id = (int)$_POST['batalkan_id'];

    // Ambil info pesanan untuk restore porsi dan notifikasi
    $stmt = $pdo->prepare("SELECT p.*, d.nama_makanan, d.user_id AS penyedia_id, d.porsi_tersisa FROM pesanan p JOIN donasi d ON p.donasi_id = d.id WHERE p.id = ? AND p.user_id = ?");
    $stmt->execute([$pesanan_id, $user['id']]);
    $pesanan = $stmt->fetch();

    if ($pesanan && $pesanan['status'] === 'menunggu') {
        try {
            // Batalkan pesanan
            $stmt = $pdo->prepare("UPDATE pesanan SET status = 'dibatalkan' WHERE id = ?");
            $stmt->execute([$pesanan_id]);

            // Kembalikan porsi ke donasi
            $stmt = $pdo->prepare("UPDATE donasi SET porsi_tersisa = porsi_tersisa + ? WHERE id = ?");
            $stmt->execute([$pesanan['jumlah_porsi'], $pesanan['donasi_id']]);

            // Notifikasi ke penyedia
            if (!empty($pesanan['penyedia_id'])) {
                kirim_notifikasi($pdo, $pesanan['penyedia_id'],
                    'Pesanan Dibatalkan',
                    $user['nama'] . ' membatalkan pesanan ' . $pesanan['jumlah_porsi'] . ' porsi ' . $pesanan['nama_makanan'],
                    $root_path . '/pesanan/verifikasi.php'
                );
            }

            $_SESSION['flash_success'] = 'Pesanan berhasil dibatalkan. Porsi dikembalikan ke donasi.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Gagal membatalkan pesanan.';
        }
    } else {
        $_SESSION['flash_error'] = 'Pesanan tidak bisa dibatalkan.';
    }
    header('Location: ' . $root_path . '/pesanan/riwayat.php');
    exit;
}

// Ambil riwayat pesanan
$stmt = $pdo->prepare("
    SELECT p.*, d.nama_makanan, d.foto, d.batas_waktu, d.alamat_ambil, k.nama_kategori, u.nama AS nama_penyedia
    FROM pesanan p
    JOIN donasi d ON p.donasi_id = d.id
    JOIN kategori k ON d.kategori_id = k.id
    JOIN users u ON d.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY p.waktu_pesan DESC
");
$stmt->execute([$user['id']]);
$riwayat = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 900px;">
    <h1>Riwayat Pesanan</h1>
    <p class="text-muted">Kelola dan lacak pesanan makanan kamu.</p>

    <?php if (empty($riwayat)): ?>
        <div class="card" style="text-align: center; padding: 60px 20px;">
            <p class="text-muted" style="font-size: 18px; margin-bottom: 24px;">Belum ada pesanan</p>
            <a href="<?= $root_path ?>/index.php#makanan" class="btn fill">Cari Makanan</a>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 16px;">
            <?php foreach ($riwayat as $row): 
                $foto_src = !empty($row['foto']) && file_exists(__DIR__ . '/../uploads/makanan/' . $row['foto'])
                    ? $root_path . '/uploads/makanan/' . htmlspecialchars($row['foto'])
                    : null;
                $bg_style = $foto_src ? "background-image: url('{$foto_src}');" : "background: linear-gradient(135deg, #FF6B3D, #f2b134);";
                $status_class = $row['status'];
                $status_label = match($row['status']) {
                    'menunggu' => 'Menunggu Ambil',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                    'kedaluwarsa' => 'Kedaluwarsa',
                    default => ucfirst($row['status'])
                };
            ?>
                <article class="card" style="display: grid; grid-template-columns: 140px 1fr auto; gap: 16px; align-items: center; min-height: 140px;">
                    <div style="width: 140px; height: 120px; border-radius: 12px; <?= $bg_style ?> background-size: cover; background-position: center; position: relative;">
                        <span class="tag" style="position: absolute; top: 8px; left: 8px;"><?= htmlspecialchars($row['nama_kategori']) ?></span>
                    </div>
                    <div style="min-width: 0;">
                        <h3 style="margin-bottom: 4px;"><?= htmlspecialchars($row['nama_makanan']) ?></h3>
                        <p class="text-muted" style="margin-bottom: 4px;"><?= htmlspecialchars($row['nama_penyedia']) ?></p>
                        <p style="font-size: 13px; color: var(--muted); margin-bottom: 8px;">
                            <strong>Ambil di:</strong> <?= htmlspecialchars($row['alamat_ambil']) ?><br>
                            <strong>Batas waktu:</strong> <?= date('d M H.i', strtotime($row['batas_waktu'])) ?>
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="badge" style="background: var(--orange); color: white;"><?= $row['jumlah_porsi'] ?> porsi</span>
                            <span class="badge" style="background: 
                                <?= match($row['status']) {
                                    'menunggu' => 'var(--orange)',
                                    'selesai' => '#28a745',
                                    'dibatalkan' => '#dc3545',
                                    'kedaluwarsa' => '#6c757d',
                                    default => 'var(--ink)'
                                } ?>; color: white;">
                                <?= $status_label ?>
                            </span>
                        </div>
                    </div>
                    <div style="text-align: right; min-width: 180px;">
                        <div style="font-family: 'Anton', sans-serif; font-size: 18px; letter-spacing: 1px; color: var(--orange);">
                            <?= htmlspecialchars($row['kode_pesanan']) ?>
                        </div>
                        <p class="text-muted" style="font-size: 11px; margin-top: 4px;">Kode Pengambilan</p>
                        
                        <?php if ($row['status'] === 'menunggu'): ?>
                            <form method="POST" style="margin-top: 12px;" onsubmit="return confirm('Batalkan pesanan ini? Porsi akan dikembalikan.');">
                                <input type="hidden" name="batalkan_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn" style="background: #dc3545; color: white; border-color: #dc3545;">Batalkan</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>