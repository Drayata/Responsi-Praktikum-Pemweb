<?php
// auth/logout.php
require_once __DIR__ . '/../config/database.php';
session_start();
session_unset();
session_destroy();

session_start();
$_SESSION['flash_success'] = 'Kamu berhasil keluar.';

header("Location: ../index.php");
exit;
