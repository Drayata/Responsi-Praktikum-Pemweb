<?php
// config/database.php
// Konfigurasi koneksi MySQL XAMPP menggunakan PHP Data Objects (PDO)

// Session security config (harus sebelum session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
}

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

// Helper base url dinamis (bekerja baik di php -S maupun XAMPP subfolder)
if (!function_exists('base_url')) {
    function base_url($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $doc_root = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $proj_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
        $base_folder = '';
        if ($doc_root && $proj_root && strpos($proj_root, $doc_root) === 0) {
            $base_folder = substr($proj_root, strlen($doc_root));
        }

        $url = rtrim($protocol . $host . $base_folder, '/');
        return $url . '/' . ltrim($path, '/');
    }
}
