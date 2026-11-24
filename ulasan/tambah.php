<?php
// ulasan/tambah.php - Tambah ulasan & rating
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notifikasi.php';

require_login();
require_role('penerima');

$root_path = '/Responsi-Praktikum-Pemweb';
$page_title = 'Beri Ulasan | BagiPangan';
$user = current_user();

$pesanan_id = $_GET['pesanan_id'] ?? 0;
$pesanan = null;
$donasi = null;

// Validasi pesanan
if ($pesanan_id > 0) {
    $stmt = $pdo->prepare("
        SELECT p.*, d.nama_makanan, d.foto, d.user_id AS penyedia_id
        FROM pesanan p
        JOIN donasi d ON p.donasi_id = d.id
        WHERE p.id = ? AND p.user_id = ? AND p.status = 'selesai'
    ");
    $stmt->execute([$pesanan_id, $user['id']]);
    $pesanan = $stmt->fetch();
}

if (!$pesanan) {
    $_SESSION['flash_error'] = 'Pesanan tidak ditemukan, belum selesai, atau bukan milikmu.';
    header('Location: ' . $root_path . '/pesanan/riwayat.php');
    exit;
}

// Cek apakah sudah diulas
$stmt = $pdo->prepare("SELECT id FROM ulasan WHERE pesanan_id = ?");
$stmt->execute([$pesanan_id]);
$existing = $stmt->fetch();

if ($existing) {
    $_SESSION['flash_error'] = 'Kamu sudah memberikan ulasan untuk pesanan ini.';
    header('Location: ' . $root_path . '/pesanan/riwayat.php');
    exit;
}

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 0)));
    $komentar = trim($_POST['komentar'] ?? '');

    if ($rating < 1) {
        $_SESSION['flash_error'] = 'Pilih rating bintang (1-5).';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO ulasan (pesanan_id, user_id, donasi_id, rating, komentar)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$pesanan_id, $user['id'], $pesanan['donasi_id'], $rating, $komentar]);

            // Notifikasi ke penyedia
            if (!empty($pesanan['penyedia_id'])) {
                kirim_notifikasi($pdo, $pesanan['penyedia_id'],
                    'Ulasan Baru',
                    $user['nama'] . ' memberikan rating ' . $rating . ' bintang untuk ' . $pesanan['nama_makanan'],
                    $root_path . '/donasi/riwayat.php'
                );
            }

            $_SESSION['flash_success'] = 'Terima kasih atas ulasanmu!';
            header('Location: ' . $root_path . '/pesanan/riwayat.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Gagal menyimpan ulasan.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 600px;">
    <h1>Beri Ulasan</h1>

    <div class="card" style="padding: 24px; margin-top: 16px;">
        <div style="display: flex; gap: 16px; align-items: start; margin-bottom: 24px;">
            <div style="width: 80px; height: 80px; border-radius: 12px; background: linear-gradient(135deg, #FF6B3D, #f2b134); flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                🍽️
            </div>
            <div style="flex: 1;">
                <h3 style="margin-bottom: 4px;"><?= htmlspecialchars($pesanan['nama_makanan']) ?></h3>
                <p class="text-muted" style="font-size: 13px;">Pesanan: <?= htmlspecialchars($pesanan['kode_pesanan']) ?></p>
                <p class="text-muted" style="font-size: 13px;">Diambil: <?= date('d M H:i', strtotime($pesanan['waktu_ambil'])) ?></p>
            </div>
        </div>

        <form method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 12px; font-weight: 600;">Rating</label>
                <div style="display: flex; gap: 8px;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="star-btn" data-value="<?= $i ?>"
                                style="background: none; border: none; font-size: 32px; cursor: pointer; color: #ddd; transition: color 0.2s;"
                                onclick="setRating(<?= $i ?>)">
                            ★
                        </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="rating_input" value="0">
                <p id="rating_text" class="text-muted" style="font-size: 13px; margin-top: 8px;">Klik bintang untuk memberi rating</p>
            </div>

            <div style="margin-bottom: 24px;">
                <label for="komentar" style="display: block; margin-bottom: 8px; font-weight: 600;">Komentar (opsional)</label>
                <textarea id="komentar" name="komentar" rows="4"
                          placeholder="Bagaimana pengalamanmu mengambil makanan ini? Rasa, pelayanan, kebersihan..."
                          style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--line); border-radius: 12px; font-family: inherit; font-size: 14px;"></textarea>
            </div>

            <div style="display: flex; gap: 12px;">
                <a href="<?= $root_path ?>/pesanan/riwayat.php" class="btn">Batal</a>
                <button type="submit" class="btn fill" style="flex: 1;">Kirim Ulasan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
const stars = document.querySelectorAll('.star-btn');
const ratingInput = document.getElementById('rating_input');
const ratingText = document.getElementById('rating_text');

const texts = {
    1: 'Sangat Buruk - Tidak memuaskan',
    2: 'Buruk - Banyak kekurangan',
    3: 'Cukup - Standar biasa',
    4: 'Baik - Memuaskan',
    5: 'Sangat Baik - Luar biasa!'
};

function setRating(value) {
    ratingInput.value = value;
    ratingText.textContent = texts[value];

    stars.forEach(star => {
        const starValue = parseInt(star.dataset.value);
        if (starValue <= value) {
            star.style.color = '#FF6B3D';
            star.style.transform = 'scale(1.2)';
        } else {
            star.style.color = '#ddd';
            star.style.transform = 'scale(1)';
        }
    });
}

// Keyboard support
stars.forEach(star => {
    star.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            setRating(parseInt(star.dataset.value));
        }
    });
});
</script>

<style>
.star-btn:focus-visible {
    outline: 2px solid var(--orange);
    outline-offset: 2px;
    border-radius: 4px;
}
</style>