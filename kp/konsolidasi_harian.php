<?php require_once __DIR__ . '/../php8_compat.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../inc/cek_login.php';
wajib_level('kp');
error_reporting(E_ALL);
$consol=$_GET['consol'];

if ($consol=='Neraca'){
 header("location:laporan/neraca_konsol_harian.php");
}else{
 header("location:laporan/laba_konsol.php");
}
?>
