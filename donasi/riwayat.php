<?php
// donasi/riwayat.php
$root_path = '../';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role(['penyedia', 'admin'], '../index.php');

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Jika admin, dapat melihat semua donasi. Jika penyedia, hanya miliknya.
if ($user_role === 'admin') {
    $sql = "SELECT d.*, k.nama_kategori, u.nama as nama_penyedia 
            FROM donasi d 
            JOIN kategori k ON d.kategori_id = k.id 
            JOIN users u ON d.user_id = u.id 
            ORDER BY d.created_at DESC";
    $stmt = $pdo->query($sql);
} else {
    $sql = "SELECT d.*, k.nama_kategori, u.nama as nama_penyedia 
            FROM donasi d 
            JOIN kategori k ON d.kategori_id = k.id 
            JOIN users u ON d.user_id = u.id 
            WHERE d.user_id = ? 
            ORDER BY d.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
}

$donasi_list = $stmt->fetchAll();

$page_title = 'Kelola Donasi Makanan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap">
    <div class="page-header">
        <div>
            <h1><?= $user_role === 'admin' ? 'Semua Donasi Makanan' : 'Donasi Makanan Saya' ?></h1>
            <p style="color: var(--muted); font-size: 14px; margin-top: 4px;">
                <?= count($donasi_list) ?> donasi terdaftar di sistem.
            </p>
        </div>
        <a href="tambah.php" class="btn fill">+ Tambah Donasi Baru</a>
    </div>

    <?php if (empty($donasi_list)): ?>
        <div class="card-box" style="text-align: center; padding: 60px 20px;">
            <h3 style="font-size: 28px; margin-bottom: 8px;">Belum Ada Donasi Makanan</h3>
            <p style="color: var(--muted); margin-bottom: 24px;">Kamu belum mengunggah donasi makanan berlebih.</p>
            <a href="tambah.php" class="btn fill">+ Buat Donasi Pertama</a>
        </div>
    <?php else: ?>
        <div class="card-box" style="padding: 24px;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Makanan</th>
                            <th>Kategori</th>
                            <?php if ($user_role === 'admin'): ?>
                                <th>Penyedia</th>
                            <?php endif; ?>
                            <th>Porsi</th>
                            <th>Batas Ambil</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donasi_list as $row): 
                            $is_expired = strtotime($row['batas_waktu']) < time() && $row['status'] === 'tersedia';
                            $display_status = $is_expired ? 'kedaluwarsa' : $row['status'];
                            $foto_url = !empty($row['foto']) && file_exists(__DIR__ . '/../uploads/makanan/' . $row['foto'])
                                ? $root_path . 'uploads/makanan/' . htmlspecialchars($row['foto'])
                                : null;
                        ?>
                            <tr>
                                <td style="width: 70px;">
                                    <?php if ($foto_url): ?>
                                        <img src="<?= $foto_url ?>" alt="<?= htmlspecialchars($row['nama_makanan']) ?>" style="width: 60px; height: 48px; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 48px; background: linear-gradient(135deg, #FF6B3D, #f2b134); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 11px; font-weight: 600;">Pangan</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_makanan']) ?></strong>
                                    <div style="color: var(--muted); font-size: 11px;"><?= htmlspecialchars(substr($row['alamat_ambil'], 0, 40)) ?>...</div>
                                </td>
                                <td><span class="tag"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                                <?php if ($user_role === 'admin'): ?>
                                    <td><?= htmlspecialchars($row['nama_penyedia']) ?></td>
                                <?php endif; ?>
                                <td>
                                    <strong><?= $row['porsi_tersisa'] ?></strong> / <?= $row['porsi_awal'] ?> porsi
                                </td>
                                <td>
                                    <?= date('d M, H:i', strtotime($row['batas_waktu'])) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $display_status ?>">
                                        <?= htmlspecialchars($display_status) ?>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn sm">Ubah</a>
                                    <button onclick="confirmAction('Apakah kamu yakin ingin menghapus donasi ini?', 'hapus.php?id=<?= $row['id'] ?>')" class="btn sm danger">Hapus</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
