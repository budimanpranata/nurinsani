<?php require_once __DIR__ . '/../../php8_compat.php';
$consol=$_GET['posisi'];

if ($consol=='Neraca'){
 header("location:neraca_excel.php");
}else{
 header("location:laba_excel.php");
}
?>