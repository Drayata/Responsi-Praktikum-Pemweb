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

if (isset($_GET['hapus'])) {
    $did = (int) $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM donasi WHERE id = ?");
    $stmt->execute([$did]);
    $_SESSION['flash_success'] = 'Postingan donasi berhasil dihapus.';
    header('Location: donasi.php');
    exit;
}

$daftar_donasi = $pdo->query("
    SELECT d.*, u.nama AS nama_penyedia, k.nama_kategori
    FROM donasi d
    LEFT JOIN users u ON d.penyedia_id = u.id
    LEFT JOIN kategori k ON d.kategori_id = k.id
    ORDER BY d.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Admin — Moderasi Donasi';
$root_path = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 1000px; margin: 30px auto;">
    <div style="display: flex; gap: 10px; margin-bottom: 24px;">
        <a class="btn sm" href="users.php">Kelola Pengguna</a>
        <a class="btn sm fill" href="donasi.php">Moderasi Donasi</a>
        <a class="btn sm" href="kategori.php">Kelola Kategori</a>
    </div>

    <div style="background: #fff; padding: 24px; border-radius: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
        <h2 style="margin-top: 0;">Moderasi Postingan Donasi Makanan</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #eee; text-align: left;">
                    <th style="padding: 10px;">Nama Makanan</th>
                    <th style="padding: 10px;">Penyedia</th>
                    <th style="padding: 10px;">Kategori</th>
                    <th style="padding: 10px;">Porsi</th>
                    <th style="padding: 10px;">Status</th>
                    <th style="padding: 10px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar_donasi as $d): ?>
                    <tr style="border-bottom: 1px solid #f2f2f2;">
                        <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($d['nama_makanan']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($d['nama_penyedia'] ?? '-') ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($d['nama_kategori'] ?? '-') ?></td>
                        <td style="padding: 10px;"><?= (int)$d['jumlah_porsi'] ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($d['status'] ?? 'tersedia') ?></td>
                        <td style="padding: 10px;">
                            <a href="donasi.php?hapus=<?= $d['id'] ?>" onclick="return confirm('Hapus donasi ini?')" style="color: #d32f2f; font-weight: 600; text-decoration: none;">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>