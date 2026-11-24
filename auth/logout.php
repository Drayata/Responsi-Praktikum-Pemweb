<?php
// auth/logout.php
session_start();
session_unset();
session_destroy();

session_start();
$_SESSION['flash_success'] = 'Kamu berhasil keluar.';
header("Location: ../index.php");
exit;
