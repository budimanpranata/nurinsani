<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
include "header.php";
include "conn2.php";
$koneksi=open_connection2();
?>
<?php 
// konfirmasi "Apakah anda akan keluar dari System ?" kini di tombol Keluar (inc/kerangka.php) sebelum halaman logout dibuka;
// dulu confirm() di sini muncul sesudah proses keluar berjalan sehingga Cancel tidak berpengaruh (perintah user 2026-11-04)
$userid=$_SESSION['userid'];
$update = mysql_query("UPDATE user SET status='0' WHERE userid='$userid'");
$result = mysql_query("SELECT * FROM user WHERE userid='$userid'");
while($row = mysql_fetch_array($result)){
echo "<p> anda ".$row['nama']." keluar dari sistem Informasi Kanwil Jawa Barat pada :</p>";
echo date ('l j F Y');
echo "<p>Untuk masuk kembali silahkan <a href='index.html'>Klik Disini</a></p>";
}
session_destroy();
exit();
?> 