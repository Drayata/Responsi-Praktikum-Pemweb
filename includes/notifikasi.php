<?php
// includes/notifikasi.php - Helper untuk sistem notifikasi

/**
 * Kirim notifikasi ke user
 * 
 * @param PDO $pdo
 * @param int $user_id ID penerima notifikasi
 * @param string $judul Judul notifikasi
 * @param string $pesan Isi pesan notifikasi
 * @param string|null $link Link tujuan saat diklik
 * @return bool
 */
function kirim_notifikasi($pdo, $user_id, $judul, $pesan, $link = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifikasi (user_id, judul, pesan, link, is_read, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ");
        return $stmt->execute([$user_id, $judul, $pesan, $link]);
    } catch (PDOException $e) {
        error_log("Notifikasi gagal: " . $e->getMessage());
        return false;
    }
}

/**
 * Hitung jumlah notifikasi belum dibaca
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @return int
 */
function count_unread_notifikasi($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifikasi WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}