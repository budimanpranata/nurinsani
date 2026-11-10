<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
include "../conn2.php";
$koneksi=open_connection2();
require_once __DIR__ . '/../inc/notif.php';

// konfirmasi "Apakah anda akan keluar dari System ?" ada di tombol Keluar sebelum halaman logout dibuka;
// notifikasi keluar kini memakai SweetAlert (dulu landing page header.php + alert bawaan).
$userid = isset($_SESSION['userid']) ? (string) $_SESSION['userid'] : '';
$nama = '';
if ($userid !== '') {
    $uid = mysql_real_escape_string($userid);
    mysql_query("UPDATE user SET status='0' WHERE userid='$uid'");
    $row = mysql_fetch_array(mysql_query("SELECT nama FROM user WHERE userid='$uid'"));
    $nama = $row ? $row['nama'] : '';
}
session_destroy();
notif_pergi('Anda ' . $nama . ' keluar dari sistem KSPPS Nur Insani pada ' . date('l j F Y') . '.', url_app('index.html'), 'success');
exit;
