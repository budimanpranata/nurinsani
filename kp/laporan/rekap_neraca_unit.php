<?php require_once __DIR__ . '/../../php8_compat.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
$consol=$_GET['posisi'];

if ($consol=='Neraca'){
 header("location:neraca_excel.php");
}else{
 header("location:laba_excel.php");
}
?>