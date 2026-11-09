<?php require_once __DIR__ . '/php8_compat.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit');
ini_set('display_errors',1);
echo "tests";

?>

