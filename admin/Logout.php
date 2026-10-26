<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
include "header.php";
include "../conn2.php";
$koneksi=open_connection2();
?>
<?php 
echo "<script language='javascript'>confirm('Apakah adan akan keluar dari System ?')</script>";
$userid=$_SESSION['userid'];
$update = mysql_query("UPDATE user SET status='0' WHERE userid='$userid'");
$result = mysql_query("SELECT * FROM user WHERE userid='$userid'");
while($row = mysql_fetch_array($result)){
echo "<p> anda ".$row['nama']." keluar dari sistem ESDEPE pada :</p>";
echo date ('l j F Y');
echo "<p>Untuk masuk kembali silahkan <a href='../index.html'>Klik Disini</a></p>";
}
session_destroy();
exit();
?> 