<?php require_once __DIR__ . '/../../php8_compat.php';
error_reporting(E_ALL);
$consol=$_GET['consol'];

if ($consol=='Neraca'){
 header("location:neraca_konsol.php");
}else{
 header("location:laba_konsol.php");
}
?>
