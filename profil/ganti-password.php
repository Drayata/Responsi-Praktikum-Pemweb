<?php
// profil/ganti-password.php
session_start();
$root_path = '../';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$user_id = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error = 'Semua kolom wajib diisi!';
    } elseif (strlen($new_pass) < 6) {
        $error = 'Kata sandi baru minimal harus 6 karakter!';
    } elseif ($new_pass !== $confirm_pass) {
        $error = 'Konfirmasi kata sandi baru tidak cocok!';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_pass, $user['password'])) {
            $new_hashed = password_hash($new_pass, PASSWORD_BCRYPT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update->execute([$new_hashed, $user_id])) {
                $_SESSION['flash_success'] = 'Kata sandi berhasil diperbarui!';
                header("Location: index.php");
                exit;
            } else {
                $error = 'Gagal memperbarui kata sandi di sistem.';
            }
        } else {
            $error = 'Kata sandi saat ini yang kamu masukkan salah!';
        }
    }
}

$page_title = 'Ganti Kata Sandi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div class="page-header">
        <h1>Ganti Kata Sandi</h1>
        <a href="index.php" class="btn">Kembali ke Profil</a>
    </div>

    <div class="card-box" style="max-width: 480px; margin: 0 auto;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="ganti-password.php">
            <div class="form-group">
                <label for="current_password">Kata Sandi Saat Ini</label>
                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Masukkan kata sandi lama" required>
            </div>

            <div class="form-group">
                <label for="new_password">Kata Sandi Baru</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Kata Sandi Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ketik ulang sandi baru" required>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn fill" style="flex: 1; padding: 12px;">Perbarui Sandi</button>
                <a href="index.php" class="btn" style="padding: 12px 24px;">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
