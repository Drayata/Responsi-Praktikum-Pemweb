<?php
// donasi/edit.php
session_start();
$root_path = '../';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role(['penyedia', 'admin'], '../index.php');

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: riwayat.php");
    exit;
}

// Cek hak akses ke donasi
if ($user_role === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM donasi WHERE id = ?");
    $stmt->execute([$id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM donasi WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
}

$donasi = $stmt->fetch();
if (!$donasi) {
    $_SESSION['flash_error'] = 'Donasi tidak ditemukan atau kamu tidak memiliki akses!';
    header("Location: riwayat.php");
    exit;
}

$kategori_list = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_makanan = trim($_POST['nama_makanan'] ?? '');
    $kategori_id = (int)($_POST['kategori_id'] ?? 0);
    $porsi_awal = (int)($_POST['porsi_awal'] ?? 0);
    $porsi_tersisa = (int)($_POST['porsi_tersisa'] ?? 0);
    $batas_waktu = trim($_POST['batas_waktu'] ?? '');
    $alamat_ambil = trim($_POST['alamat_ambil'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = $_POST['status'] ?? 'tersedia';

    if (empty($nama_makanan) || $kategori_id <= 0 || $porsi_tersisa < 0 || empty($batas_waktu) || empty($alamat_ambil)) {
        $error = 'Semua kolom yang ditandai bintang wajib diisi!';
    } elseif (!in_array($status, ['tersedia', 'habis', 'kedaluwarsa', 'dibatalkan'])) {
        $error = 'Status donasi tidak valid!';
    } else {
        $foto_name = $donasi['foto'];

        // Cek upload foto baru
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
                $new_filename = 'food_' . $donasi['user_id'] . '_' . time() . '.' . $ext;
                $target_dir = __DIR__ . '/../uploads/makanan/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (move_uploaded_file($file_tmp, $target_dir . $new_filename)) {
                    if (!empty($foto_name) && file_exists($target_dir . $foto_name)) {
                        @unlink($target_dir . $foto_name);
                    }
                    $foto_name = $new_filename;
                } else {
                    $error = 'Gagal menyimpan foto baru!';
                }
            }
        }

        if (empty($error)) {
            $update = $pdo->prepare("UPDATE donasi SET nama_makanan = ?, kategori_id = ?, porsi_awal = ?, porsi_tersisa = ?, batas_waktu = ?, alamat_ambil = ?, deskripsi = ?, foto = ?, status = ? WHERE id = ?");
            if ($update->execute([$nama_makanan, $kategori_id, $porsi_awal, $porsi_tersisa, $batas_waktu, $alamat_ambil, $deskripsi, $foto_name, $status, $id])) {
                $_SESSION['flash_success'] = 'Data donasi makanan berhasil diperbarui!';
                header("Location: riwayat.php");
                exit;
            } else {
                $error = 'Gagal memperbarui data donasi!';
            }
        }
    }
}

$page_title = 'Ubah Donasi Makanan';
require_once __DIR__ . '/../includes/header.php';

// Format datetime-local
$dt_value = date('Y-m-d\TH:i', strtotime($donasi['batas_waktu']));
?>

<div class="wrap">
    <div class="page-header">
        <h1>Ubah Donasi Makanan</h1>
        <a href="riwayat.php" class="btn">Kembali ke Daftar</a>
    </div>

    <div class="card-box" style="max-width: 680px; margin: 0 auto;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_makanan">Nama Makanan Surplus *</label>
                <input type="text" id="nama_makanan" name="nama_makanan" class="form-control" value="<?= htmlspecialchars($donasi['nama_makanan']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="kategori_id">Kategori *</label>
                    <select id="kategori_id" name="kategori_id" class="form-control" required>
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= $donasi['kategori_id'] == $kat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status Ketersediaan *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="tersedia" <?= $donasi['status'] === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="habis" <?= $donasi['status'] === 'habis' ? 'selected' : '' ?>>Habis</option>
                        <option value="kedaluwarsa" <?= $donasi['status'] === 'kedaluwarsa' ? 'selected' : '' ?>>Kedaluwarsa</option>
                        <option value="dibatalkan" <?= $donasi['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="porsi_tersisa">Porsi Tersisa *</label>
                    <input type="number" id="porsi_tersisa" name="porsi_tersisa" min="0" max="500" class="form-control" value="<?= htmlspecialchars($donasi['porsi_tersisa']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="porsi_awal">Porsi Awal Disumbangkan *</label>
                    <input type="number" id="porsi_awal" name="porsi_awal" min="1" max="500" class="form-control" value="<?= htmlspecialchars($donasi['porsi_awal']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="batas_waktu">Batas Waktu Pengambilan *</label>
                <input type="datetime-local" id="batas_waktu" name="batas_waktu" class="form-control" value="<?= $dt_value ?>" required>
            </div>

            <div class="form-group">
                <label for="alamat_ambil">Titik Pengambilan / Alamat *</label>
                <textarea id="alamat_ambil" name="alamat_ambil" class="form-control" rows="2" required><?= htmlspecialchars($donasi['alamat_ambil']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="deskripsi">Keterangan / Kondisi Makanan</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($donasi['deskripsi']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="foto">Ubah Foto Makanan (Opsional)</label>
                <?php if (!empty($donasi['foto']) && file_exists(__DIR__ . '/../uploads/makanan/' . $donasi['foto'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?= $root_path ?>uploads/makanan/<?= htmlspecialchars($donasi['foto']) ?>" alt="Foto Makanan" style="height: 100px; border-radius: 12px; object-fit: cover;">
                    </div>
                <?php endif; ?>
                <input type="file" id="foto" name="foto" accept="image/*" class="form-control">
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn fill" style="flex: 1; padding: 14px;">Simpan Perubahan</button>
                <a href="riwayat.php" class="btn" style="padding: 14px 28px;">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
