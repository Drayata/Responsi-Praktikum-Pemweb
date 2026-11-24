<?php
// migrate.php - Script inisialisasi database otomatis
$host = 'localhost';
$port = '3306';
$user = 'root';
$pass = '';

try {
    echo "Menghubungkan ke server MySQL...\n";
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Membaca file database.sql...\n";
    $sql = file_get_contents(__DIR__ . '/database.sql');

    echo "Mengeksekusi pembuatan database dan tabel...\n";
    $pdo->exec($sql);

    // Update password dummy user ke hash password123 yang valid
    $hash = password_hash('password123', PASSWORD_BCRYPT);
    $st = $pdo->prepare("UPDATE bagipangan_db.users SET password = ?");
    $st->execute([$hash]);

    echo "BERHASIL: Database dan password user dummy (password123) telah diperbarui!\n";
} catch (PDOException $e) {
    echo "GAGAL: " . $e->getMessage() . "\n";
}
