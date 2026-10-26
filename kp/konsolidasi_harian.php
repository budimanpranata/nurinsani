<?php require_once __DIR__ . '/../php8_compat.php';
error_reporting(E_ALL);
$consol=$_GET['consol'];

if ($consol=='Neraca'){
 header("location:laporan/neraca_konsol_harian.php");
}else{
 header("location:laporan/laba_konsol.php");
}
?>
