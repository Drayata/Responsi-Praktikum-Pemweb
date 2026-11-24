<?php
// admin/users.php - Kelola Pengguna
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();
require_role('admin');

$root_path = '/Responsi-Praktikum-Pemweb';
$page_title = 'Kelola Pengguna | Panel Admin';

// Proses aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($action === 'delete' && $user_id > 0) {
        // Prevent deleting self
        $current_user = current_user();
        if ($user_id === $current_user['id']) {
            $_SESSION['flash_error'] = 'Tidak bisa menghapus akun sendiri.';
        } else {
            try {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                $_SESSION['flash_success'] = 'Pengguna berhasil dihapus.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Gagal menghapus pengguna.';
            }
        }
        header('Location: ' . $root_path . '/admin/users.php');
        exit;
    }

    if ($action === 'toggle_role' && $user_id > 0) {
        $new_role = $_POST['new_role'] ?? '';
        $valid_roles = ['penyedia', 'penerima', 'admin'];
        if (in_array($new_role, $valid_roles)) {
            try {
                $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $user_id]);
                $_SESSION['flash_success'] = 'Role pengguna diperbarui.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Gagal memperbarui role.';
            }
        }
        header('Location: ' . $root_path . '/admin/users.php');
        exit;
    }
}

// Filter
$role_filter = $_GET['role'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];

if ($role_filter !== '') {
    $where[] = "role = ?";
    $params[] = $role_filter;
}

if ($search !== '') {
    $where[] = "(nama LIKE ? OR email LIKE ? OR no_hp LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

$where_sql = implode(' AND ', $where);

// Ambil data pengguna
$stmt = $pdo->prepare("SELECT * FROM users WHERE $where_sql ORDER BY created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <h1>Kelola Pengguna</h1>
        <a href="<?= $root_path ?>/admin/" class="btn">← Kembali ke Dashboard</a>
    </div>

    <!-- Filter Form -->
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; padding: 16px; background: var(--white); border-radius: 12px; border: 1px solid var(--line);">
        <input type="text" name="search" placeholder="Cari nama, email, nomor HP..."
               value="<?= htmlspecialchars($search) ?>"
               style="flex: 1; min-width: 200px; padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 8px; font-family: inherit;">
        <select name="role" style="padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 8px; font-family: inherit; background: var(--white);">
            <option value="">Semua Role</option>
            <option value="penyedia" <?= $role_filter === 'penyedia' ? 'selected' : '' ?>>Penyedia</option>
            <option value="penerima" <?= $role_filter === 'penerima' ? 'selected' : '' ?>>Penerima</option>
            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <button type="submit" class="btn fill">Filter</button>
        <a href="<?= $root_path ?>/admin/users.php" class="btn">Reset</a>
    </form>

    <!-- Tabel Pengguna -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>Role</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--muted);">Tidak ada pengguna ditemukan</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $idx => $u): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= htmlspecialchars($u['nama']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['no_hp']) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_role">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <select name="new_role" onchange="this.form.submit()"
                                            style="padding: 6px 10px; border: 1px solid var(--line); border-radius: 6px; font-size: 12px; background: var(--white);">
                                        <option value="penyedia" <?= $u['role'] === 'penyedia' ? 'selected' : '' ?>>Penyedia</option>
                                        <option value="penerima" <?= $u['role'] === 'penerima' ? 'selected' : '' ?>>Penerima</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus pengguna ini? Semua data terkait (donasi, pesanan, ulasan) akan ikut terhapus.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn danger" style="font-size: 11px; padding: 6px 12px;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($users)): ?>
        <div style="text-align: center; margin-top: 40px; color: var(--muted);">
            <p>Tidak ada pengguna yang cocok dengan filter.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>