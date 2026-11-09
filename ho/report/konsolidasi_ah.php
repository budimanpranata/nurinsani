<?php require_once __DIR__ . '/../../php8_compat.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('ho');
error_reporting(E_ALL);
$consol=$_GET['consol'];

if ($consol=='Neraca'){
 header("location:neraca_konsol.php");
}else{
 header("location:laba_konsol.php");
}
?>
