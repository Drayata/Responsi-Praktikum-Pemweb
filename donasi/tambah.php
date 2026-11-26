<?php
// donasi/tambah.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$root_path = base_url('');
require_role(['penyedia', 'admin'], base_url('index.php'));

$user_id = $_SESSION['user_id'];
$error = '';

// Ambil daftar kategori
$kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

// Ambil default alamat dari profil user
$st_user = $pdo->prepare("SELECT alamat FROM users WHERE id = ?");
$st_user->execute([$user_id]);
$user_data = $st_user->fetch();
$alamat_default = $user_data['alamat'] ?? '';

$nama_makanan = '';
$kategori_id = '';
$porsi = '';
$batas_waktu = '';
$alamat_ambil = $alamat_default;
$deskripsi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_makanan = trim($_POST['nama_makanan'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    $porsi = (int)($_POST['porsi'] ?? 0);
    $batas_waktu = trim($_POST['batas_waktu'] ?? '');
    $alamat_ambil = trim($_POST['alamat_ambil'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($nama_makanan) || $kategori_id <= 0 || $porsi <= 0 || empty($batas_waktu) || empty($alamat_ambil)) {
        $error = 'Mohon lengkapi semua kolom yang wajib diisi!';
    } elseif (strtotime($batas_waktu) <= time()) {
        $error = 'Batas waktu pengambilan harus di waktu yang akan datang!';
    } else {
        $foto_name = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_size = $_FILES['foto']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed_ext)) {
                $error = 'Format file foto harus JPG, PNG, atau WEBP!';
            } elseif ($file_size > 3 * 1024 * 1024) {
                $error = 'Ukuran file foto maksimal 3 MB!';
            } else {
                $new_filename = 'food_' . $user_id . '_' . time() . '.' . $ext;
                $target_dir = upload_path('makanan');
                if (move_uploaded_file($file_tmp, $target_dir . $new_filename)) {
                    $foto_name = $new_filename;
                } else {
                    $error = 'Gagal menyimpan foto makanan!';
                }
            }
        }

        if (empty($error)) {
            $insert = $pdo->prepare("INSERT INTO donasi (user_id, kategori_id, nama_makanan, deskripsi, porsi_awal, porsi_tersisa, batas_waktu, alamat_ambil, foto, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'tersedia')");
            if ($insert->execute([$user_id, $kategori_id, $nama_makanan, $deskripsi, $porsi, $porsi, $batas_waktu, $alamat_ambil, $foto_name])) {
                $_SESSION['flash_success'] = 'Donasi makanan berhasil dipublikasikan!';
                header("Location: riwayat.php");
                exit;
            } else {
                $error = 'Gagal menyimpan data donasi makanan ke database!';
            }
        }
    }
}

$page_title = 'Tambah Donasi Makanan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div class="page-header">
        <h1>Tambah Donasi Makanan</h1>
        <a href="riwayat.php" class="btn">Daftar Donasi Saya</a>
    </div>

    <div class="card-box" style="max-width: 680px; margin: 0 auto;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="tambah.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_makanan">Nama Makanan Surplus *</label>
                <input type="text" id="nama_makanan" name="nama_makanan" class="form-control" placeholder="Contoh: Nasi Kotak Ayam Bakar, Roti Manis Isi Keju" value="<?= htmlspecialchars($nama_makanan) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="kategori_id">Kategori *</label>
                    <select id="kategori_id" name="kategori_id" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= $kategori_id == $kat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="porsi">Jumlah Porsi Tersedia *</label>
                    <input type="number" id="porsi" name="porsi" min="1" max="500" class="form-control" placeholder="Jumlah porsi" value="<?= htmlspecialchars($porsi) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="batas_waktu">Batas Waktu Pengambilan (Expired) *</label>
                <input type="datetime-local" id="batas_waktu" name="batas_waktu" class="form-control" value="<?= htmlspecialchars($batas_waktu) ?>" required>
                <small style="color: var(--muted); font-size: 11px;">Tentukan jam sebelum makanan melewati batas layak konsumsi.</small>
            </div>

            <div class="form-group">
                <label for="alamat_ambil">Titik Pengambilan / Alamat *</label>
                <textarea id="alamat_ambil" name="alamat_ambil" class="form-control" rows="2" placeholder="Nama outlet, alamat, atau patokan penjemputan" required><?= htmlspecialchars($alamat_ambil) ?></textarea>
            </div>

            <div class="form-group">
                <label for="deskripsi">Keterangan / Kondisi Makanan</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan kondisi makanan, kehangatan, atau himbauan (misal: harap bawa tempat makan sendiri)"><?= htmlspecialchars($deskripsi) ?></textarea>
            </div>

            <div class="form-group">
                <label for="foto">Foto Makanan</label>
                <input type="file" id="foto" name="foto" accept="image/*" class="form-control">
                <small style="color: var(--muted); font-size: 11px;">Opsional. Format JPG, PNG, WEBP maks 3 MB.</small>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn fill" style="flex: 1; padding: 14px;">Publikasikan Donasi</button>
                <a href="riwayat.php" class="btn" style="padding: 14px 28px;">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
