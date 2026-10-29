<?php require_once __DIR__ . '/../php8_compat.php'; require_once __DIR__ . '/cek_admin.php';
session_start();

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
    header("location:../index.html");
 exit; }

//cek level user
if($_SESSION['level']!="admin"){
   header("location:../index.html");
 exit; }

// tampilan: kerangka baru (menu samping responsif) menggantikan frameset header/tree/footer
require_once __DIR__ . '/../inc/kerangka.php';
kerangka_aplikasi(array('keluar' => '../Logout.php'));
