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

if (isset($_POST['ubah_role'])) {
    $uid = (int) $_POST['user_id'];
    $role_baru = $_POST['role'];
    if (in_array($role_baru, ['penerima', 'penyedia', 'admin']) && $uid !== (int)$user['id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role_baru, $uid]);
        $_SESSION['flash_success'] = 'Role pengguna berhasil diperbarui.';
    }
    header('Location: users.php');
    exit;
}

if (isset($_GET['hapus'])) {
    $uid = (int) $_GET['hapus'];
    if ($uid !== (int)$user['id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $_SESSION['flash_success'] = 'Pengguna berhasil dihapus.';
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query("SELECT id, nama, email, role, created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Admin — Kelola Pengguna';
$root_path = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 1000px; margin: 30px auto;">
    <div style="display: flex; gap: 10px; margin-bottom: 24px;">
        <a class="btn sm fill" href="users.php">Kelola Pengguna</a>
        <a class="btn sm" href="donasi.php">Moderasi Donasi</a>
        <a class="btn sm" href="kategori.php">Kelola Kategori</a>
    </div>

    <div style="background: #fff; padding: 24px; border-radius: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
        <h2 style="margin-top: 0;">Daftar Pengguna Terdaftar</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #eee; text-align: left;">
                    <th style="padding: 10px;">Nama</th>
                    <th style="padding: 10px;">Email</th>
                    <th style="padding: 10px;">Role</th>
                    <th style="padding: 10px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr style="border-bottom: 1px solid #f2f2f2;">
                        <td style="padding: 10px; font-weight: 600;"><?= htmlspecialchars($u['nama']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($u['email']) ?></td>
                        <td style="padding: 10px;">
                            <form method="POST" style="display: flex; gap: 6px;">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="role" style="padding: 4px 8px; border-radius: 8px;">
                                    <?php foreach (['penerima', 'penyedia', 'admin'] as $r): ?>
                                        <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="ubah_role" class="btn sm">Simpan</button>
                            </form>
                        </td>
                        <td style="padding: 10px;">
                            <?php if ($u['id'] !== $user['id']): ?>
                                <a href="users.php?hapus=<?= $u['id'] ?>" onclick="return confirm('Hapus pengguna ini?')" style="color: #d32f2f; font-weight: 600; text-decoration: none;">Hapus</a>
                            <?php else: ?>
                                <small style="color: #888;">Akun Aktif</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>