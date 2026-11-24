<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();
$user = current_user();
if (($user['role'] ?? '') !== 'admin') {
    $_SESSION['flash_error'] = 'Akses khusus Admin.';
    header('Location: ../index.php');
    exit;
}

if (isset($_POST['tambah_kategori'])) {
    $nama = trim($_POST['nama_kategori'] ?? '');
    if ($nama !== '') {
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        $stmt->execute([$nama]);
        $_SESSION['flash_success'] = 'Kategori baru berhasil ditambahkan.';
    }
    header('Location: kategori.php');
    exit;
}

if (isset($_GET['hapus'])) {
    $kid = (int) $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->execute([$kid]);
    $_SESSION['flash_success'] = 'Kategori berhasil dihapus.';
    header('Location: kategori.php');
    exit;
}

$kategori = $pdo->query("SELECT * FROM kategori ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Admin — Kelola Kategori';
$root_path = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 1000px; margin: 30px auto;">
    <div style="display: flex; gap: 10px; margin-bottom: 24px;">
        <a class="btn sm" href="users.php">Kelola Pengguna</a>
        <a class="btn sm" href="donasi.php">Moderasi Donasi</a>
        <a class="btn sm fill" href="kategori.php">Kelola Kategori</a>
    </div>

    <div style="background: #fff; padding: 24px; border-radius: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
        <h2 style="margin-top: 0;">Kelola Kategori Makanan</h2>

        <form method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <input type="text" name="nama_kategori" placeholder="Nama kategori baru (misal: Minuman Segar)" required
                   style="flex: 1; padding: 10px 14px; border: 1px solid #ddd; border-radius: 999px;">
            <button type="submit" name="tambah_kategori" class="btn sm fill">+ Tambah Kategori</button>
        </form>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #eee; text-align: left;">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Nama Kategori</th>
                    <th style="padding: 10px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kategori as $k): ?>
                    <tr style="border-bottom: 1px solid #f2f2f2;">
                        <td style="padding: 10px;"><?= $k['id'] ?></td>
                        <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($k['nama_kategori']) ?></td>
                        <td style="padding: 10px;">
                            <a href="kategori.php?hapus=<?= $k['id'] ?>" onclick="return confirm('Hapus kategori ini?')" style="color: #d32f2f; font-weight: 600; text-decoration: none;">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>