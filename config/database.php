<?php
// config/database.php
// Konfigurasi koneksi MySQL menggunakan PHP Data Objects (PDO)
// Mendukung XAMPP lokal dan InfinityFree hosting melalui config/config.php

// Load konfigurasi hosting jika ada
$config_file = __DIR__ . '/config.php';
$hosting_config = [];

if (file_exists($config_file)) {
    $hosting_config = require $config_file;
}

// Default konfigurasi XAMPP (development)
$defaults = [
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'bagipangan_db',
    'db_user' => 'root',
    'db_pass' => '',
    'base_url' => null,
    'upload_max_size' => 3 * 1024 * 1024,
    'avatar_max_size' => 2 * 1024 * 1024,
    'app_env' => 'development',
    'debug_mode' => true,
    'session_secure' => false,
    'session_samesite' => 'Lax',
];

// Merge: hosting config meng-override defaults
$cfg = array_merge($defaults, $hosting_config);

// Session security config (harus sebelum session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', $cfg['session_samesite']);
    ini_set('session.use_strict_mode', '1');
    
    // Secure cookie hanya jika HTTPS atau dikonfigurasi manual
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    if ($cfg['session_secure'] || $is_https) {
        ini_set('session.cookie_secure', '1');
    }
}

// Error reporting berdasarkan environment
if ($cfg['debug_mode']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php_error.log');
}

// Buat folder logs jika belum ada
if (!$cfg['debug_mode']) {
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
}

try {
    $dsn = "mysql:host={$cfg['db_host']};port={$cfg['db_port']};dbname={$cfg['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ]);
} catch (PDOException $e) {
    // Jangan tampilkan detail error database ke user di production
    if ($cfg['debug_mode']) {
        die("<h3>Koneksi Database Gagal!</h3>" .
            "<p>Pastikan MySQL sudah berjalan dan database <code>{$cfg['db_name']}</code> sudah dibuat.</p>" .
            "<p>Pesan Error: " . htmlspecialchars($e->getMessage()) . "</p>");
    } else {
        error_log("Database connection failed: " . $e->getMessage());
        die("<h3>Layanan Sedang Tidak Tersedia</h3>" .
            "<p>Terjadi kesalahan koneksi ke database. Silakan coba lagi nanti atau hubungi administrator.</p>");
    }
}

// Helper base_url dinamis (bekerja di localhost, XAMPP subfolder, dan hosting)
if (!function_exists('base_url')) {
    function base_url($path = '') {
        global $cfg;
        
        // Jika base_url dikonfigurasi manual (untuk hosting dengan subdomain/path khusus)
        if (!empty($cfg['base_url'])) {
            $url = rtrim($cfg['base_url'], '/');
            return $url . '/' . ltrim($path, '/');
        }
        
        // Auto-detect
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
                    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'))
                    ? "https://" : "http://";
        
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

// Helper untuk upload path
if (!function_exists('upload_path')) {
    function upload_path($type = 'makanan') {
        $base = realpath(__DIR__ . '/..') . '/uploads/' . $type . '/';
        if (!is_dir($base)) {
            @mkdir($base, 0755, true);
        }
        return $base;
    }
}

// Helper untuk upload URL
if (!function_exists('upload_url')) {
    function upload_url($filename, $type = 'makanan') {
        if (empty($filename)) return null;
        return base_url("uploads/{$type}/" . ltrim($filename, '/'));
    }
}

// Konfigurasi global untuk akses mudah
define('UPLOAD_MAX_SIZE', $cfg['upload_max_size']);
define('AVATAR_MAX_SIZE', $cfg['avatar_max_size']);
define('APP_ENV', $cfg['app_env']);
define('DEBUG_MODE', $cfg['debug_mode']);