<?php
// config/database.php
// Konfigurasi koneksi MySQL XAMPP menggunakan PHP Data Objects (PDO)

$db_host = 'localhost';
$db_port = '3306';
$db_name = 'bagipangan_db';
$db_user = 'root';
$db_pass = ''; // Default XAMPP tanpa password

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan ramah untuk setup XAMPP
    die("<h3>Koneksi Database Gagal!</h3>" .
        "<p>Pastikan MySQL di control panel XAMPP sudah dinyalakan (Running) dan database <code>bagipangan_db</code> sudah diimport dari <code>database.sql</code>.</p>" .
        "<p>Pesan Error: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Helper base url dinamis
if (!function_exists('base_url')) {
    function base_url($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Cari base folder project
        $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        // Normalisasi path agar mengarah ke root project
        // Jika script ada di subfolder seperti /auth atau /donasi
        $segments = explode('/', trim($script_dir, '/'));
        $base_folder = '';
        if (!empty($segments)) {
            // Ambil nama root folder di htdocs
            $base_folder = '/' . $segments[0];
        }
        
        $url = rtrim($protocol . $host . $base_folder, '/');
        return $url . '/' . ltrim($path, '/');
    }
}
