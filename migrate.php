<?php
// migrate.php - Script inisialisasi database otomatis (HANYA UNTUK CLI / LOCALHOST)
// JANGAN akses via browser di production!
// Jalankan: php migrate.php

// Security: Hanya izinkan dari CLI atau localhost
$is_cli = (php_sapi_name() === 'cli');
$is_local = isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost']);

if (!$is_cli && !$is_local) {
    http_response_code(403);
    die("Akses ditolak. Script ini hanya untuk inisialisasi database lokal via CLI.");
}

require_once __DIR__ . '/config/database.php';

echo "=== BagiPangan Database Migration ===\n";
echo "Environment: " . APP_ENV . "\n";
echo "Debug Mode: " . (DEBUG_MODE ? 'ON' : 'OFF') . "\n\n";

try {
    // Test koneksi
    echo "✓ Terhubung ke database: {$cfg['db_name']} @ {$cfg['db_host']}:{$cfg['db_port']}\n";
    
    // Baca dan eksekusi SQL
    $sql_file = __DIR__ . '/database.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("File database.sql tidak ditemukan!");
    }
    
    echo "✓ Membaca file database.sql...\n";
    $sql = file_get_contents($sql_file);
    
    // Split statements (handle multi-statement)
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "✓ Mengeksekusi " . count($statements) . " pernyataan SQL...\n";
    $pdo->exec($sql);
    
    echo "\n✓ BERHASIL: Database dan tabel telah dibuat!\n";
    echo "✓ Data seed development (user test) telah di-insert.\n";
    echo "\nAkun test (HANYA LOKAL - password: password123):\n";
    echo "  - Penyedia: katering@bagipangan.com\n";
    echo "  - Penerima: budi@bagipangan.com\n";
    echo "  - Admin:    admin@bagipangan.com\n";
    echo "\n⚠ PERINGATAN: Ganti password default sebelum deploy ke hosting!\n";
    
} catch (PDOException $e) {
    echo "\n✗ GAGAL (PDO): " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ GAGAL: " . $e->getMessage() . "\n";
    exit(1);
}