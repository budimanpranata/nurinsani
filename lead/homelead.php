<?php require_once __DIR__ . '/../php8_compat.php';
session_start();

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
    die("Maf Anda belum login");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="lead"){
    die("Anda bukan user");//jika bukan user jangan lanjut
}

// tampilan: kerangka baru (menu samping responsif) menggantikan frameset header/tree/footer
require_once __DIR__ . '/../inc/kerangka.php';
kerangka_aplikasi(array('keluar' => '../Logout.php'));
