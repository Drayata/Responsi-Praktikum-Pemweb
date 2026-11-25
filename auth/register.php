<?php
// auth/register.php
$root_path = '../';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Jika sudah login, redirect
if (is_logged_in()) {
    header("Location: ../index.php");
    exit;
}

$error = '';
$nama = '';
$email = '';
$no_hp = '';
$alamat = '';
$role = 'penerima';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $role = $_POST['role'] ?? 'penerima';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validasi
    if (empty($nama) || empty($email) || empty($no_hp) || empty($alamat) || empty($password)) {
        $error = 'Semua kolom wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (!in_array($role, ['penerima', 'penyedia'])) {
        $error = 'Pilihan role tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal harus 6 karakter!';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok!';
    } else {
        // Cek duplikasi email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar, silakan gunakan email lain atau masuk.';
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare("INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, ?)");
            if ($insert->execute([$nama, $email, $hashed, $no_hp, $alamat, $role])) {
                $_SESSION['flash_success'] = 'Registrasi berhasil! Silakan masuk dengan akun barumu.';
                header("Location: login.php");
                exit;
            } else {
                $error = 'Terjadi kesalahan sistem saat menyimpan data.';
            }
        }
    }
}

$page_title = 'Daftar Akun';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div class="card-box auth-container">
        <div class="form-header">
            <h2>Daftar BagiPangan</h2>
            <p>Bergabung bersama gerakan penyelamatan makanan surplus</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="nama">Nama Lengkap / Nama Usaha</label>
                <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Katering Sari Boga / Budi" value="<?= htmlspecialchars($nama) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="no_hp">Nomor HP / WhatsApp</label>
                <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($no_hp) ?>" required>
            </div>

            <div class="form-group">
                <label for="role">Daftar Sebagai</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="penerima" <?= $role === 'penerima' ? 'selected' : '' ?>>Penerima Makanan (Warga / Mahasiswa / Panti)</option>
                    <option value="penyedia" <?= $role === 'penyedia' ? 'selected' : '' ?>>Penyedia Makanan (Resto / Kafe / Katering / Toko Roti)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat Lengkap</label>
                <textarea id="alamat" name="alamat" class="form-control" rows="3" placeholder="Alamat penjemputan donasi atau domisili kamu" required><?= htmlspecialchars($alamat) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Ulangi Kata Sandi</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Ketik ulang sandi" required>
                </div>
            </div>

            <div style="margin-top: 10px;">
                <button type="submit" class="btn fill" style="width: 100%; padding: 14px;">Daftar Sekarang</button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 13px; color: var(--muted);">
            Sudah punya akun? <a href="login.php" style="color: var(--orange); font-weight: 600;">Masuk di sini</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
