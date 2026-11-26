<?php
// config/config.example.php
// SALIN file ini menjadi config.php DAN ISI KREDENSIAL HOSTING
// File config.php sudah ada di .gitignore dan TIDAK akan masuk ke GitHub

return [
    // Database InfinityFree / Hosting
    'db_host' => 'sqlXXX.infinityfree.com',  // Ganti dengan hostname database hosting Anda
    'db_port' => '3306',
    'db_name' => 'if0_XXXXXXXX_bagipangan',   // Ganti dengan nama database hosting Anda
    'db_user' => 'if0_XXXXXXXX',              // Ganti dengan username database hosting Anda
    'db_pass' => 'your_hosting_db_password',  // Ganti dengan password database hosting Anda

    // Base URL aplikasi (otomatis terdeteksi, tapi bisa di-override)
    // 'base_url' => 'https://yourdomain.infinityfreeapp.com/',

    // Konfigurasi upload
    'upload_max_size' => 3 * 1024 * 1024,     // 3 MB (sesuai batas InfinityFree)
    'avatar_max_size' => 2 * 1024 * 1024,     // 2 MB

    // Environment
    'app_env' => 'production',                 // 'development' atau 'production'
    'debug_mode' => false,                     // false di production

    // Session security (HTTPS di hosting)
    'session_secure' => true,                  // true jika pakai HTTPS
    'session_samesite' => 'Lax',
];