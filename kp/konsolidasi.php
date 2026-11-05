<?php require_once __DIR__ . '/../php8_compat.php';
error_reporting(E_ALL);
// 2026-11-05: wajib login level kp
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
if ($_SESSION['level'] != "kp") {
    header("location:../index.html");
    exit;
}
$consol=isset($_GET['consol']) ? $_GET['consol'] : '';

if ($consol=='Neraca'){
 header("location:laporan/neraca_konsol.php");
}else{
 header("location:laporan/laba_konsol.php");
}
?>
