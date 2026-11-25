<?php
// donasi/hapus.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role(['penyedia', 'admin'], base_url('index.php'));

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    if ($user_role === 'admin') {
        $stmt = $pdo->prepare("SELECT foto FROM donasi WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("SELECT foto FROM donasi WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    }
    
    $donasi = $stmt->fetch();
    if ($donasi) {
        // Hapus file foto jika ada
        if (!empty($donasi['foto'])) {
            $file_path = __DIR__ . '/../uploads/makanan/' . $donasi['foto'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        // Hapus record dari database
        $del = $pdo->prepare("DELETE FROM donasi WHERE id = ?");
        $del->execute([$id]);

        $_SESSION['flash_success'] = 'Donasi makanan berhasil dihapus!';
    } else {
        $_SESSION['flash_error'] = 'Data donasi tidak ditemukan atau kamu tidak memiliki akses!';
    }
}

header("Location: riwayat.php");
exit;
