<?php
// pesanan/verifikasi.php - Verifikasi pengambilan oleh penyedia
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notifikasi.php';

require_login();
require_role(['penyedia', 'admin']);

$root_path = base_url('');
$page_title = 'Verifikasi Pengambilan | BagiPangan';
$user = current_user();

$pesanan = null;
$donasi = null;
$error = '';
$success = '';

// Proses verifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = trim($_POST['kode_pesanan'] ?? '');

    if ($kode) {
        // Cari pesanan berdasarkan kode
        $stmt = $pdo->prepare("
            SELECT p.*, d.nama_makanan, d.user_id AS penyedia_id
            FROM pesanan p
            JOIN donasi d ON p.donasi_id = d.id
            WHERE p.kode_pesanan = ? AND p.status = 'menunggu'
        ");
        $stmt->execute([$kode]);
        $pesanan = $stmt->fetch();

        if ($pesanan) {
            // Cek apakah user adalah pemilik donasi (kecuali admin)
            if ($user['role'] !== 'admin' && $pesanan['penyedia_id'] != $user['id']) {
                $error = 'Kode pesanan tidak ditemukan untuk donasi milikmu.';
            } else {
                try {
                    // Update status pesanan
                    $stmt = $pdo->prepare("UPDATE pesanan SET status = 'selesai', waktu_ambil = NOW() WHERE id = ?");
                    $stmt->execute([$pesanan['id']]);

                    // Notifikasi ke penerima untuk ulasan
                    kirim_notifikasi($pdo, $pesanan['user_id'],
                        'Pengambilan Selesai',
                        'Makanan ' . $pesanan['nama_makanan'] . ' telah diambil. Silakan berikan ulasan!',
                        $root_path . '/ulasan/tambah.php?pesanan_id=' . $pesanan['id']
                    );

                    $success = 'Verifikasi berhasil! Pesanan ' . htmlspecialchars($pesanan['kode_pesanan']) . ' untuk ' . htmlspecialchars($pesanan['nama_makanan']) . ' sekarang selesai.';
                } catch (PDOException $e) {
                    $error = 'Gagal memverifikasi pengambilan.';
                }
            }
        } else {
            $error = 'Kode pesanan tidak valid, sudah selesai, atau dibatalkan.';
        }
    } else {
        $error = 'Masukkan kode pesanan.';
    }
}

// Ambil daftar pesanan menunggu untuk donasi milik penyedia
$stmt = $pdo->prepare("
    SELECT p.*, d.nama_makanan, u.nama AS nama_penerima, u.no_hp AS hp_penerima
    FROM pesanan p
    JOIN donasi d ON p.donasi_id = d.id
    JOIN users u ON p.user_id = u.id
    WHERE d.user_id = ? AND p.status = 'menunggu'
    ORDER BY p.waktu_pesan ASC
");
$stmt->execute([$user['id']]);
$pesanan_menunggu = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 800px;">
    <h1>Verifikasi Pengambilan</h1>
    <p class="text-muted">Masukkan kode pengambilan yang ditunjukkan penerima untuk mengonfirmasi serah terima.</p>

    <div class="card" style="margin-top: 24px; padding: 24px;">
        <h3 style="margin-bottom: 16px;">Verifikasi Kode</h3>
        <form method="POST" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: end;">
            <div style="flex: 1; min-width: 200px;">
                <label for="kode_pesanan" style="display: block; margin-bottom: 8px;">Kode Pengambilan</label>
                <input type="text" id="kode_pesanan" name="kode_pesanan" placeholder="Contoh: BP-4892"
                       style="text-transform: uppercase; font-family: 'Anton', sans-serif; letter-spacing: 2px; font-size: 18px;"
                       autocomplete="off" required>
            </div>
            <button type="submit" class="btn fill" style="width: 180px;">Verifikasi</button>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-top: 16px;">
                <strong>Gagal:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-top: 16px;">
                <strong>Berhasil:</strong> <?= $success ?>
            </div>
        <?php endif; ?>
    </div>

    <h3 style="margin: 32px 0 16px;">Pesanan Menunggu Ambil</h3>

    <?php if (empty($pesanan_menunggu)): ?>
        <div class="card" style="text-align: center; padding: 40px;">
            <p class="text-muted">Tidak ada pesanan yang menunggu verifikasi.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; gap: 16px;">
            <?php foreach ($pesanan_menunggu as $row): ?>
                <article class="verify-card">
                    <div>
                        <h3 style="margin-bottom: 4px;"><?= htmlspecialchars($row['nama_makanan']) ?></h3>
                        <p class="text-muted" style="margin-bottom: 4px;">
                            Penerima: <strong><?= htmlspecialchars($row['nama_penerima']) ?></strong>
                            (<?= htmlspecialchars($row['hp_penerima']) ?>)
                        </p>
                        <p class="text-muted" style="font-size: 13px; margin-bottom: 8px;">
                            Porsi: <?= $row['jumlah_porsi'] ?> | Waktu pesan: <?= date('d M H:i', strtotime($row['waktu_pesan'])) ?>
                        </p>
                        <div style="font-family: 'Anton', sans-serif; font-size: 18px; letter-spacing: 1px; color: var(--orange);">
                            <?= htmlspecialchars($row['kode_pesanan']) ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <form method="POST" style="display: inline;" onsubmit="document.getElementById('kode_pesanan').value = '<?= htmlspecialchars($row['kode_pesanan']) ?>'; return false;">
                            <button type="button" class="btn fill"
                                    onclick="document.getElementById('kode_pesanan').value='<?= htmlspecialchars($row['kode_pesanan']) ?>'; document.querySelector('button[type=submit]').click();">
                                Gunakan Kode Ini
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>