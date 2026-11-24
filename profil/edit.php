<?php
// profil/edit.php
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (empty($nama) || empty($no_hp) || empty($alamat)) {
        $error = 'Nama, nomor HP, dan alamat wajib diisi!';
    } else {
        $foto_name = $profile['foto'];

        // Cek jika ada upload avatar baru
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_size = $_FILES['foto']['size'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed_ext)) {
                $error = 'Format file foto harus JPG, PNG, atau WEBP!';
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error = 'Ukuran file foto maksimal 2 MB!';
            } else {
                $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
                $target_dir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (move_uploaded_file($file_tmp, $target_dir . $new_filename)) {
                    // Hapus foto lama jika bukan default
                    if ($foto_name !== 'default-avatar.png' && file_exists($target_dir . $foto_name)) {
                        @unlink($target_dir . $foto_name);
                    }
                    $foto_name = $new_filename;
                } else {
                    $error = 'Gagal mengunggah foto avatar!';
                }
            }
        }

        if (empty($error)) {
            $update = $pdo->prepare("UPDATE users SET nama = ?, no_hp = ?, alamat = ?, foto = ? WHERE id = ?");
            if ($update->execute([$nama, $no_hp, $alamat, $foto_name, $user_id])) {
                $_SESSION['user_nama'] = $nama;
                $_SESSION['user_foto'] = $foto_name;
                $_SESSION['flash_success'] = 'Profil berhasil diperbarui!';
                header("Location: index.php");
                exit;
            } else {
                $error = 'Gagal menyimpan perubahan ke database!';
            }
        }
    }
}

$page_title = 'Ubah Profil';
require_once __DIR__ . '/../includes/header.php';

$avatar_path = !empty($profile['foto']) && file_exists(__DIR__ . '/../uploads/avatars/' . $profile['foto'])
    ? $root_path . 'uploads/avatars/' . htmlspecialchars($profile['foto'])
    : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23FF6B3D'/%3E%3Ctext x='50' y='58' font-family='sans-serif' font-size='38' fill='%23fff' text-anchor='middle'%3E" . strtoupper(substr($profile['nama'], 0, 1)) . "%3C/text%3E%3C/svg%3E";
?>

<div class="wrap">
    <div class="page-header">
        <h1>Edit Data Profil</h1>
        <a href="index.php" class="btn">Kembali ke Profil</a>
    </div>

    <div class="card-box" style="max-width: 640px; margin: 0 auto;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit.php" enctype="multipart/form-data">
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="<?= $avatar_path ?>" id="preview-avatar" class="avatar-preview" alt="Preview Foto">
                <label for="foto" class="btn sm" style="cursor: pointer; display: inline-block;">Pilih Foto Baru</label>
                <input type="file" id="foto" name="foto" accept="image/*" style="display: none;">
                <p style="font-size: 11px; color: var(--muted); margin-top: 6px;">Maksimal 2 MB (JPG, PNG, atau WEBP)</p>
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap / Nama Usaha</label>
                <input type="text" id="nama" name="nama" class="form-control" value="<?= htmlspecialchars($profile['nama']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Alamat Email (Tidak dapat diubah)</label>
                <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($profile['email']) ?>" disabled style="opacity: 0.7;">
            </div>

            <div class="form-group">
                <label for="no_hp">Nomor HP / WhatsApp</label>
                <input type="text" id="no_hp" name="no_hp" class="form-control" value="<?= htmlspecialchars($profile['no_hp']) ?>" required>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat Lengkap (Titik Temu / Penjemputan Makanan)</label>
                <textarea id="alamat" name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($profile['alamat']) ?></textarea>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn fill" style="flex: 1; padding: 12px;">Simpan Perubahan</button>
                <a href="index.php" class="btn" style="padding: 12px 24px;">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setupImagePreview('foto', 'preview-avatar');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
