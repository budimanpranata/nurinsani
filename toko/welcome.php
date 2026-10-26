<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
include "conn2.php";
?>
<html>
<head>

<style type="text/css">
<!--
.header1 {color: #ffffff; background: #00b050; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style1 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>
<body>
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#00b050">
  <tr>
    <td width="100%" bgcolor="#00b050"><span class="style1">Home</span></td>
  </tr>
</table>
</body>
</html>
<?php
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$tgl=$_SESSION['stgl_system'];   
$result = mysql_query("SELECT * FROM user WHERE userid='$userid'");
while($row = mysql_fetch_array($result)){
echo "<h>Selamat Datang ".$row['nama']. " Di Sistem Informasi KSPPS Nur Insani";
echo "<br>";
echo "<h> Parameter Tanggal anda adalah ".$tgl." </h>";
}
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
    die("Anda belum login");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="toko"){
    die("Anda bukan user");//jika bukan user jangan lanjut
}
?>	

