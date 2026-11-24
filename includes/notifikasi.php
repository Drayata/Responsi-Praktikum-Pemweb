<?php
// includes/notifikasi.php

function kirim_notifikasi(PDO $pdo, int $user_id, string $judul, string $pesan, ?string $link = null): bool
{
    $stmt = $pdo->prepare("
        INSERT INTO notifikasi (user_id, judul, pesan, link) 
        VALUES (:user_id, :judul, :pesan, :link)
    ");
    return $stmt->execute([
        ':user_id' => $user_id,
        ':judul'   => $judul,
        ':pesan'   => $pesan,
        ':link'    => $link
    ]);
}

function hitung_notifikasi_belum_dibaca(PDO $pdo, int $user_id): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifikasi WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int) $stmt->fetchColumn();
}