<?php
// includes/auth_check.php
// Helper autentikasi dan otorisasi role native PHP

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'     => $_SESSION['user_id'] ?? null,
        'nama'   => $_SESSION['user_nama'] ?? 'Pengguna',
        'email'  => $_SESSION['user_email'] ?? '',
        'role'   => $_SESSION['user_role'] ?? 'penerima',
        'foto'   => $_SESSION['user_foto'] ?? 'default-avatar.png',
    ];
}

function require_login($redirect_url = '../auth/login.php') {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Silakan masuk terlebih dahulu untuk mengakses halaman ini.';
        header("Location: " . $redirect_url);
        exit;
    }
}

function require_role($allowed_roles = [], $redirect_url = '../index.php') {
    require_login();
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    $role = $_SESSION['user_role'] ?? '';
    if (!in_array($role, $allowed_roles)) {
        $_SESSION['flash_error'] = 'Kamu tidak memiliki izin untuk mengakses halaman tersebut.';
        header("Location: " . $redirect_url);
        exit;
    }
}
