<?php
// auth/login.php
$root_path = base_url('');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Jika sudah login, redirect
if (is_logged_in()) {
    header("Location: " . $root_path . "/index.php");
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_foto'] = $user['foto'];

            $_SESSION['flash_success'] = 'Selamat datang kembali, ' . $user['nama'] . '!';

            // Redirect berdasarkan role
            if ($user['role'] === 'penyedia') {
                header("Location: " . $root_path . "/donasi/riwayat.php");
            } elseif ($user['role'] === 'admin') {
                header("Location: " . $root_path . "/admin/index.php");
            } else {
                header("Location: " . $root_path . "/index.php");
            }
            exit;
        } else {
            $error = 'Email atau kata sandi yang kamu masukkan salah!';
        }
    }
}

$page_title = 'Masuk Akun';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div class="card-box auth-container">
        <div class="form-header">
            <h2>Masuk BagiPangan</h2>
            <p>Akses akun donatur dan penerima makanan surplus</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required>
            </div>

            <div style="margin-top: 14px;">
                <button type="submit" class="btn fill" style="width: 100%; padding: 14px;">Masuk ke Akun</button>
            </div>
        </form>

        <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--line); font-size: 12px; color: var(--muted);">
            <p><strong>Akun Uji Coba Cepat (dari database.sql):</strong></p>
            <p>Penyedia: <code>katering@bagipangan.com</code> (pass: <code>password123</code>)</p>
            <p>Penerima: <code>budi@bagipangan.com</code> (pass: <code>password123</code>)</p>
            <p>Admin: <code>admin@bagipangan.com</code> (pass: <code>password123</code>)</p>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--muted);">
            Belum punya akun? <a href="register.php" style="color: var(--orange); font-weight: 600;">Daftar di sini</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
