<?php require_once __DIR__ . '/php8_compat.php';
$consol=$_GET['consol'];

if ($consol=='Neraca'){
 header("location:laporan/neraca_konsol.php");
}else{
 header("location:laporan/laba_konsol.php");
}
?>