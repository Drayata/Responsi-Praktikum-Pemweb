<?php
// notifikasi/index.php - Daftar notifikasi pengguna
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();
$root_path = '/Responsi-Praktikum-Pemweb';
$page_title = 'Notifikasi | BagiPangan';
$user = current_user();

// Mark all as read
try {
    $pdo->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ? AND is_read = 0")
        ->execute([$user['id']]);
} catch (PDOException $e) {
    // Non-critical
}

// Ambil notifikasi
$stmt = $pdo->prepare("
    SELECT n.*, d.nama_makanan, d.user_id AS penyedia_id
    FROM notifikasi n
    LEFT JOIN donasi d ON n.link LIKE CONCAT('%donasi_id=%', d.id, '%') AND d.is_active = 1
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 50
");
$stmt->execute([$user['id']]);
$notifikasi = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="wrap" style="max-width: 800px;">
    <h1>Notifikasi</h1>
    <p class="text-muted">Pusat informasi pesanan dan aktivitas kamu.</p>

    <?php if (empty($notifikasi)): ?>
        <div class="card" style="text-align: center; padding: 40px;">
            <p class="text-muted">Tidak ada notifikasi baru.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($notifikasi as $row):
                $is_unread = $row['is_read'] == 0;
                $bg_style = $is_unread ? 'background: rgba(255, 107, 61, 0.1);' : '';
            ?>
                <a href="<?= htmlspecialchars($row['link']) ?>" class="card" style="padding: 16px 20px; text-decoration: none; display: block; <?= $bg_style ?>">
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; background: <?= $is_unread ? 'var(--orange)' : 'var(--ink)'; ?>; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">
                            <?= $is_unread ? '!' : '✓' ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <h4 style="margin-bottom: 4px;"><?= htmlspecialchars($row['judul']) ?></h4>
                            <p style="color: var(--muted); font-size: 13px;"><?= htmlspecialchars($row['pesan']) ?></p>
                            <p style="color: var(--muted); font-size: 12px; margin-top: 6px;">
                                <?= date('d M H:i', strtotime($row['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// Refresh notifikasi badge setiap 60 detik
setInterval(() => {
    fetch('refresh.php' <?= $user ? ', { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ user_id: <?= $user['id'] ?> }) }' : '' ?>)
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('notifikasi-badge');
            if (badge && data.unread > 0) {
                badge.textContent = data.unread;
                badge.style.display = 'inline';
            }
        });
}, 60000);
</script>

<style>
#notifikasi-badge {
    display: inline;
    background: var(--orange);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: var(--radius-pill);
    margin-left: 4px;
}
</style>