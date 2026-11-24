<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notifikasi.php';

require_role('penerima');
$user = current_user();
$user_id = $user['id'];

$pesanan_id = isset($_GET['pesanan_id']) ? (int) $_GET['pesanan_id'] : 0;
$error = '';
$sukses = '';

$stmt = $pdo->prepare("
    SELECT p.*, d.nama_makanan, d.user_id AS penyedia_id, u.nama AS nama_penyedia
    FROM pesanan p
    JOIN donasi d ON p.donasi_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->execute([$pesanan_id, $user_id]);
$pesanan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pesanan) {
    die("Data pesanan tidak ditemukan atau bukan milik Anda.");
}

if ($pesanan['status'] !== 'selesai') {
    die("Ulasan hanya dapat diberikan setelah pesanan berstatus selesai.");
}

$cek = $pdo->prepare("SELECT id FROM ulasan WHERE pesanan_id = ?");
$cek->execute([$pesanan_id]);
if ($cek->fetch()) {
    die("Pesanan ini sudah pernah Anda ulas sebelumnya. Terima kasih!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating   = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $komentar = trim($_POST['komentar'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = "Silakan pilih rating bintang antara 1 sampai 5.";
    } else {
        $insert = $pdo->prepare("
            INSERT INTO ulasan (pesanan_id, user_id, donasi_id, rating, komentar)
            VALUES (:pesanan_id, :user_id, :donasi_id, :rating, :komentar)
        ");
        $berhasil = $insert->execute([
            ':pesanan_id' => $pesanan_id,
            ':user_id'    => $user_id,
            ':donasi_id'  => $pesanan['donasi_id'],
            ':rating'     => $rating,
            ':komentar'   => $komentar
        ]);

        if ($berhasil) {
            kirim_notifikasi(
                $pdo,
                (int) $pesanan['penyedia_id'],
                'Ulasan Baru Diterima',
                $user['nama'] . ' memberi rating ' . $rating . '/5 untuk donasi "' . $pesanan['nama_makanan'] . '".',
                '/donasi/riwayat.php'
            );
            $sukses = "Terima kasih! Ulasan dan rating Anda berhasil disimpan.";
        } else {
            $error = "Terjadi kesalahan saat menyimpan ulasan.";
        }
    }
}

$page_title = 'Beri Ulasan — BagiPangan';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 640px; margin: 40px auto; padding: 0 20px;">
    <div style="background: #FFFFFF; border-radius: 24px; padding: 32px; box-shadow: 0 6px 24px rgba(0,0,0,0.05);">
        <h1 style="font-family: 'Anton', sans-serif; font-size: 2rem; margin: 0 0 8px;">
            BERI ULASAN & RATING
        </h1>
        <p style="color: #555; margin: 0 0 24px;">
            Bagikan pengalamanmu menerima donasi <strong><?= htmlspecialchars($pesanan['nama_makanan']) ?></strong> 
            dari <strong><?= htmlspecialchars($pesanan['nama_penyedia']) ?></strong>.
        </p>

        <?php if ($error): ?>
            <div style="background: #FFE5E5; color: #B00020; padding: 12px 16px; border-radius: 12px; margin-bottom: 18px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($sukses): ?>
            <div style="background: #E6F7ED; color: #1E7E48; padding: 16px; border-radius: 12px; margin-bottom: 18px;">
                <?= htmlspecialchars($sukses) ?>
                <div style="margin-top: 12px;">
                    <a href="../index.php#cerita" style="color: #FF6B3D; font-weight: 700; text-decoration: none;">
                        Lihat Ulasan di Beranda &rarr;
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px;">Rating Kualitas Makanan</label>
                    <select name="rating" required style="width: 100%; padding: 12px 14px; border-radius: 12px; border: 1.5px solid #DDD; font-size: 1rem;">
                        <option value="">-- Pilih Rating Bintang --</option>
                        <option value="5">★★★★★ (5 - Sangat Baik & Layak)</option>
                        <option value="4">★★★★☆ (4 - Baik)</option>
                        <option value="3">★★★☆☆ (3 - Cukup)</option>
                        <option value="2">★★☆☆☆ (2 - Kurang)</option>
                        <option value="1">★☆☆☆☆ (1 - Sangat Kurang)</option>
                    </select>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px;">Cerita / Komentar Ulasan</label>
                    <textarea name="komentar" rows="4" placeholder="Ceritakan kondisi makanan dan kemudahan proses pengambilan..."
                        style="width: 100%; padding: 12px 14px; border-radius: 12px; border: 1.5px solid #DDD; font-family: inherit; font-size: 0.95rem; box-sizing: border-box;"></textarea>
                </div>

                <button type="submit" 
                    style="width: 100%; background: #FF6B3D; color: #fff; border: none; padding: 14px; border-radius: 999px; font-weight: 700; font-size: 1rem; cursor: pointer;">
                    KIRIM ULASAN
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>