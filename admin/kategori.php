<?php
// admin/kategori.php - Kelola Kategori
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();
require_role('admin');

$root_path = base_url('');
$page_title = 'Kelola Kategori | Panel Admin';

// Proses aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $nama = trim($_POST['nama_kategori'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($nama && $slug) {
            try {
                $pdo->prepare("INSERT INTO kategori (nama_kategori, slug, deskripsi) VALUES (?, ?, ?)")
                    ->execute([$nama, $slug, $deskripsi]);
                $_SESSION['flash_success'] = 'Kategori berhasil ditambahkan.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Gagal menambah kategori. Slug mungkin sudah digunakan.';
            }
        } else {
            $_SESSION['flash_error'] = 'Nama dan slug kategori wajib diisi.';
        }
        header('Location: ' . $root_path . '/admin/kategori.php');
        exit;
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama_kategori'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($id > 0 && $nama && $slug) {
            try {
                $pdo->prepare("UPDATE kategori SET nama_kategori = ?, slug = ?, deskripsi = ? WHERE id = ?")
                    ->execute([$nama, $slug, $deskripsi, $id]);
                $_SESSION['flash_success'] = 'Kategori berhasil diperbarui.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Gagal memperbarui kategori.';
            }
        }
        header('Location: ' . $root_path . '/admin/kategori.php');
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                // Cek apakah kategori digunakan di donasi
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM donasi WHERE kategori_id = ?");
                $stmt->execute([$id]);
                $count = (int)$stmt->fetchColumn();

                if ($count > 0) {
                    $_SESSION['flash_error'] = "Kategori tidak bisa dihapus karena masih digunakan oleh $count donasi.";
                } else {
                    $pdo->prepare("DELETE FROM kategori WHERE id = ?")->execute([$id]);
                    $_SESSION['flash_success'] = 'Kategori berhasil dihapus.';
                }
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Gagal menghapus kategori.';
            }
        }
        header('Location: ' . $root_path . '/admin/kategori.php');
        exit;
    }
}

// Ambil semua kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY id ASC");
$kategori_list = $stmt->fetchAll();

// Data untuk edit
$edit_kategori = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_kategori = $stmt->fetch();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <h1>Kelola Kategori Makanan</h1>
        <a href="<?= $root_path ?>/admin/" class="btn">← Kembali ke Dashboard</a>
    </div>

    <!-- Form Tambah/Edit -->
    <div class="card" style="padding: 24px; margin-bottom: 32px;">
        <h3 style="margin-bottom: 20px;">
            <?= $edit_kategori ? 'Edit Kategori' : 'Tambah Kategori Baru' ?>
        </h3>
        <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; align-items: end;">
            <?php if ($edit_kategori): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $edit_kategori['id'] ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create">
            <?php endif; ?>

            <div>
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 12px;">Nama Kategori</label>
                <input type="text" name="nama_kategori" required
                       value="<?= htmlspecialchars($edit_kategori['nama_kategori'] ?? '') ?>"
                       placeholder="Contoh: Minuman"
                       style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 8px; font-family: inherit;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 12px;">Slug (URL)</label>
                <input type="text" name="slug" required
                       value="<?= htmlspecialchars($edit_kategori['slug'] ?? '') ?>"
                       placeholder="Contoh: minuman"
                       style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 8px; font-family: inherit;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 12px;">Deskripsi</label>
                <textarea name="deskripsi" rows="2"
                          placeholder="Deskripsi singkat kategori..."
                          style="width: 100%; padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 8px; font-family: inherit;"><?= htmlspecialchars($edit_kategori['deskripsi'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn fill"><?= $edit_kategori ? 'Simpan Perubahan' : 'Tambah Kategori' ?></button>
                <?php if ($edit_kategori): ?>
                    <a href="<?= $root_path ?>/admin/kategori.php" class="btn">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Daftar Kategori -->
    <div class="card" style="padding: 24px;">
        <h3 style="margin-bottom: 20px;">Daftar Kategori (<?= count($kategori_list) ?>)</h3>

        <?php if (empty($kategori_list)): ?>
            <p class="text-muted" style="text-align: center; padding: 40px 0;">Belum ada kategori. Tambahkan kategori baru di atas.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Deskripsi</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategori_list as $idx => $kat): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td><?= htmlspecialchars($kat['nama_kategori']) ?></td>
                                <td><code><?= htmlspecialchars($kat['slug']) ?></code></td>
                                <td><?= htmlspecialchars($kat['deskripsi'] ?? '-') ?></td>
                                <td><?= date('d M Y', strtotime($kat['created_at'])) ?></td>
                                <td>
                                    <a href="<?= $root_path ?>/admin/kategori.php?edit=<?= $kat['id'] ?>" class="btn" style="font-size: 11px; padding: 6px 12px;">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $kat['id'] ?>">
                                        <button type="submit" class="btn danger" style="font-size: 11px; padding: 6px 12px;">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>