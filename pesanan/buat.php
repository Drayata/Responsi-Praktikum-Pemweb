<?php
// pesanan/buat.php - Membuat pesanan makanan
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notifikasi.php';

$root_path = base_url('');
$page_title = 'Buat Pesanan | BagiPangan';

$donasi_id = $_GET['id'] ?? 0;
$donasi = null;
$porsi_tersisa = 0;

// Validasi donasi
if ($donasi_id > 0) {
    $stmt = $pdo->prepare("SELECT d.*, k.nama_kategori, u.nama AS nama_penyedia, u.alamat AS alamat_penyedia
                          FROM donasi d
                          JOIN kategori k ON d.kategori_id = k.id
                          JOIN users u ON d.user_id = u.id
                          WHERE d.id = ? AND d.status = 'tersedia' AND d.batas_waktu > NOW()");
    $stmt->execute([$donasi_id]);
    $donasi = $stmt->fetch();

    if ($donasi) {
        $porsi_tersisa = (int)$donasi['porsi_tersisa'];
    }
}

// Cek role penerima
if ($donasi) {
    $user = current_user();
    if (!$user['role'] === 'penerima') {
        $_SESSION['flash_error'] = 'Kamu harus memiliki akun penerima untuk memesan makanan.';
        header('Location: ' . $root_path . '/index.php#makanan');
        exit;
    }

    // Cek sisa porsi cukup
    if ($porsi_tersisa < 1) {
        $_SESSION['flash_error'] = 'Maaf, porsi makanan ini sudah habis atau kedaluwarsa.';
        header('Location: ' . $root_path . '/index.php#makanan');
        exit;
    }

    // Proses form submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $jumlah_porsi = max(1, min($_POST['jumlah_porsi'], $porsi_tersisa));
        $catatan = trim($_POST['catatan'] ?? '');

        try {
            // Generate kode pesanan unik
            $kode_pesanan = 'BP-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            // Insert pesanan
            $stmt = $pdo->prepare("
                INSERT INTO pesanan (kode_pesanan, user_id, donasi_id, jumlah_porsi, catatan, status)
                VALUES (?, ?, ?, ?, ?, 'menunggu')
            ");
            $stmt->execute([$kode_pesanan, $user['id'], $donasi_id, $jumlah_porsi, $catatan]);

            // Kurangi porsi tersisa
            $stmt = $pdo->prepare("UPDATE donasi SET porsi_tersisa = porsi_tersisa - ? WHERE id = ?");
            $stmt->execute([$jumlah_porsi, $donasi_id]);

            // Notifikasi ke penyedia
            $penyedia_id = $donasi['user_id'] ?? 0;
            if ($penyedia_id) {
                kirim_notifikasi($pdo, $penyedia_id,
                    'Pesanan Baru: ' . $donasi['nama_makanan'],
                    $user['nama'] . ' memesan ' . $jumlah_porsi . ' porsi ' . $donasi['nama_makanan'],
                    "$root_path/pesanan/verifikasi.php"
                );
            }

            // Redirect ke riwayat
            $_SESSION['flash_success'] = "Pesanan berhasil dibuat! Kode pengambilan: $kode_pesanan";
            header('Location: ' . $root_path . '/pesanan/riwayat.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Gagal membuat pesanan. Mungkin pesanan sudah dibuat sebelumnya.';
        }
    }
} else {
    $_SESSION['flash_error'] = 'Makanan tidak tersedia atau tidak valid.';
    header('Location: ' . $root_path . '/index.php#makanan');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 600px;">
    <h1>Buat Pesanan</h1>

    <div class="card">
        <div class="card-body">
            <?php if ($donasi): ?>
                <h3><?= htmlspecialchars($donasi['nama_makanan']) ?></h3>
                <p class="text-muted"><?= htmlspecialchars($donasi['nama_penyedia']) ?></p>
                <p>Porsi tersisa: <strong><?= $porsi_tersisa ?></strong></p>
                <p>Untuk ofiset ambil, silakan permisi terlebih dahulu dengan penyedia.</p>

                <form method="POST" style="margin-top: 24px;">
                    <div style="margin-bottom: 16px;">
                        <label>Jumlah Porsi</label>
                        <input type="number" name="jumlah_porsi" min="1" max="<?= $porsi_tersisa ?>"
                               value="1" style="max-width: 150px;">
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan" rows="3"
                                  placeholder="Contoh: Izinkan saya membawa wadah sendiri..."
                                  style="width: 100%;"></textarea>
                    </div>

                    <div style="display: flex; gap: 12px;">
                        <a href="javascript:history.back()" class="btn">Batal</a>
                        <button type="submit" class="btn fill">Buat Pesanan</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>