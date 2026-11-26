<?php
// auth/logout.php
require_once __DIR__ . '/../config/database.php';
$root_path = base_url('');
session_start();
session_unset();
session_destroy();

session_start();
$_SESSION['flash_success'] = 'Kamu berhasil keluar.';

header("Location: " . $root_path . "/index.php");
exit;
