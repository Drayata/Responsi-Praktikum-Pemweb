<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();
$user = current_user();
$user_id = $user['id'];

if (isset($_GET['baca'])) {
    $notif_id = (int) $_GET['baca'];
    $stmt = $pdo->prepare("SELECT * FROM notifikasi WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    $notif = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($notif) {
        $update = $pdo->prepare("UPDATE notifikasi SET is_read = 1 WHERE id = ?");
        $update->execute([$notif_id]);

        if (!empty($notif['link'])) {
            header("Location: " . $notif['link']);
            exit;
        }
    }
    header("Location: index.php");
    exit;
}

if (isset($_POST['baca_semua'])) {
    $stmt = $pdo->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
    header("Location: index.php?status=read_all");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$daftar_notifikasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Notifikasi Saya — BagiPangan';
$root_path = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="max-width: 900px; margin: 40px auto; padding: 0 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-family: 'Anton', sans-serif; font-size: 2.2rem; letter-spacing: 0.5px; margin: 0;">
                NOTIFIKASI & AKTIVITAS
            </h1>
            <p style="color: #555; margin: 4px 0 0;">Pantau pembaruan pesanan, verifikasi, dan ajakan ulasan.</p>
        </div>

        <?php if (!empty($daftar_notifikasi)): ?>
            <form method="POST">
                <button type="submit" name="baca_semua" 
                    style="background: transparent; border: 1.5px solid #222; padding: 10px 20px; border-radius: 999px; font-weight: 600; cursor: pointer;">
                    Tandai Semua Dibaca
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($daftar_notifikasi)): ?>
        <div style="background: #fff; border-radius: 20px; padding: 48px 24px; text-align: center; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
            <p style="font-size: 1.1rem; color: #666; margin: 0;">Belum ada notifikasi untuk saat ini.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($daftar_notifikasi as $item): ?>
                <div style="background: <?= $item['is_read'] ? '#FFFFFF' : '#FFF4EF' ?>; 
                            border-left: 5px solid <?= $item['is_read'] ? '#DDD' : '#FF6B3D' ?>;
                            border-radius: 16px; padding: 20px 24px; 
                            display: flex; justify-content: space-between; align-items: center; gap: 16px;
                            box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                            <strong style="font-size: 1.05rem; color: #1E1E1E;">
                                <?= htmlspecialchars($item['judul']) ?>
                            </strong>
                            <?php if (!$item['is_read']): ?>
                                <span style="background: #FF6B3D; color: #fff; font-size: 0.72rem; font-weight: 700; padding: 2px 10px; border-radius: 999px;">
                                    BARU
                                </span>
                            <?php endif; ?>
                        </div>
                        <p style="margin: 0 0 8px; color: #444; line-height: 1.5;">
                            <?= htmlspecialchars($item['pesan']) ?>
                        </p>
                        <small style="color: #888;">
                            <?= date('d M Y, H:i', strtotime($item['created_at'])) ?> WIB
                        </small>
                    </div>

                    <a href="index.php?baca=<?= $item['id'] ?>" 
                       style="background: #FF6B3D; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 999px; font-weight: 600; font-size: 0.88rem; white-space: nowrap;">
                        <?= !empty($item['link']) ? 'Lihat Detail' : 'Tandai Dibaca' ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>