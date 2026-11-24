<?php
// admin/donasi.php - Moderasi Donasi
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();
require_role('admin');

$root_path = '/Responsi-Praktikum-Pemweb';
$page_title = 'Moderasi Donasi | Panel Admin';

// Proses aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $donasi_id = (int)($_POST['donasi_id'] ?? 0);

    if ($action === 'delete' && $donasi_id > 0) {
        try {
            // Hapus file foto jika ada
            $stmt = $pdo->prepare("SELECT foto FROM donasi WHERE id = ?");
            $stmt->execute([$donasi_id]);
            $donasi = $stmt->fetch();
            
            if ($donasi && !empty($donasi['foto'])) {
                $filepath = __DIR__ . '/../uploads/makanan/' . $donasi['foto'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }

            // Hapus donasi (cascade akan hapus pesanan & ulasan terkait)
            $pdo->prepare("DELETE FROM donasi WHERE id = ?")->execute([$donasi_id]);
            $_SESSION['flash_success'] = 'Donasi berhasil dihapus.';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Gagal menghapus donasi.';
        }
        header('Location: ' . $root_path . '/admin/donasi.php');
        exit;
    }

    if ($action === 'toggle_status' && $donasi_id > 0) {
        $new_status = $_POST['new_status'] ?? '';
        $valid_status = ['tersedia', 'habis', 'kedaluwarsa', 'dibatalkan'];
        if (in_array($new_status, $valid_status)) {
            try {
                $pdo->prepare("UPDATE donasi SET status = ? WHERE id = ?")->execute([$new_status, $donasi_id]);
                $_SESSION['flash_success'] = 'Status donasi diperbarui.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Gagal memperbarui status.';
            }
        }
        header('Location: ' . $root_path . '/admin/donasi.php');
        exit;
    }
}

// Filter
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];

if ($status_filter !== '') {
    $where[] = "d.status = ?";
    $params[] = $status_filter;
}

if ($search !== '') {
    $where[] = "(d.nama_makanan LIKE ? OR u.nama LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term]);
}

$where_sql = implode(' AND ', $where);

// Ambil data donasi
$stmt = $pdo->prepare("
    SELECT d.*, k.nama_kategori, u.nama AS nama_penyedia
    FROM donasi d
    JOIN kategori k ON d.kategori_id = k.id
    JOIN users u ON d.user_id = u.id
    WHERE $where_sql
    ORDER BY d.created_at DESC
");
$stmt->execute($params);
$donasi_list = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <h1>Moderasi Donasi Makanan</h1>
        <a href="<?= $root_path ?>/admin/" class="btn">← Kembali ke Dashboard</a>
    </div>

    <!-- Filter Form -->
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; padding: 16px; background: var(--white); border-radius: 12px; border: 1px solid var(--line);">
        <input type="text" name="search" placeholder="Cari nama makanan atau penyedia..."
               value="<?= htmlspecialchars($search) ?>"
               style="flex: 1; min-width: 200px; padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 8px; font-family: inherit;">
        <select name="status" style="padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 8px; font-family: inherit; background: var(--white);">
            <option value="">Semua Status</option>
            <option value="tersedia" <?= $status_filter === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
            <option value="habis" <?= $status_filter === 'habis' ? 'selected' : '' ?>>Habis</option>
            <option value="kedaluwarsa" <?= $status_filter === 'kedaluwarsa' ? 'selected' : '' ?>>Kedaluwarsa</option>
            <option value="dibatalkan" <?= $status_filter === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
        </select>
        <button type="submit" class="btn fill">Filter</button>
        <a href="<?= $root_path ?>/admin/donasi.php" class="btn">Reset</a>
    </form>

    <!-- Tabel Donasi -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Nama Makanan</th>
                    <th>Kategori</th>
                    <th>Penyedia</th>
                    <th>Porsi</th>
                    <th>Status</th>
                    <th>Batas Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($donasi_list)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: var(--muted);">Tidak ada donasi ditemukan</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($donasi_list as $idx => $d): 
                        $foto_src = !empty($d['foto']) && file_exists(__DIR__ . '/../uploads/makanan/' . $d['foto'])
                            ? $root_path . '/uploads/makanan/' . htmlspecialchars($d['foto'])
                            : null;
                        $status_label = match($d['status']) {
                            'tersedia' => 'Tersedia',
                            'habis' => 'Habis',
                            'kedaluwarsa' => 'Kedaluwarsa',
                            'dibatalkan' => 'Dibatalkan',
                            default => ucfirst($d['status'])
                        };
                        $status_class = 'status-' . $d['status'];
                    ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td>
                                <?php if ($foto_src): ?>
                                    <img src="<?= $foto_src ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B3D, #f2b134); border-radius: 8px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($d['nama_makanan']) ?></td>
                            <td><?= htmlspecialchars($d['nama_kategori']) ?></td>
                            <td><?= htmlspecialchars($d['nama_penyedia']) ?></td>
                            <td><?= $d['porsi_tersisa'] ?> / <?= $d['porsi_awal'] ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="donasi_id" value="<?= $d['id'] ?>">
                                    <select name="new_status" onchange="this.form.submit()"
                                            style="padding: 4px 8px; border: 1px solid var(--line); border-radius: 4px; font-size: 11px; background: var(--white);">
                                        <option value="tersedia" <?= $d['status'] === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                        <option value="habis" <?= $d['status'] === 'habis' ? 'selected' : '' ?>>Habis</option>
                                        <option value="kedaluwarsa" <?= $d['status'] === 'kedaluwarsa' ? 'selected' : '' ?>>Kedaluwarsa</option>
                                        <option value="dibatalkan" <?= $d['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                    </select>
                                </form>
                            </td>
                            <td><?= date('d M H:i', strtotime($d['batas_waktu'])) ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus donasi ini? Foto dan data terkait (pesanan, ulasan) akan ikut terhapus.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="donasi_id" value="<?= $d['id'] ?>">
                                    <button type="submit" class="btn danger" style="font-size: 11px; padding: 6px 12px;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (empty($donasi_list)): ?>
        <div style="text-align: center; margin-top: 40px; color: var(--muted);">
            <p>Tidak ada donasi yang cocok dengan filter.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>