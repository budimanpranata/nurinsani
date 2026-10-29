<?php require_once __DIR__ . '/../php8_compat.php';
session_start();  
ob_start();  

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="audit"){
   header("location:index.html");
 exit; }  

include "../conn2.php";
?>
<html>
<head>

<style type="text/css">
<!--
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style1 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>
<body>
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
  <tr>
    <td width="100%" bgcolor="#2F6B4F"><span class="style1">Home</span></td>
  </tr>
</table>
</body>
</html>
<?php
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$result = mysql_query("SELECT * FROM user WHERE userid='$userid'");
while($row = mysql_fetch_array($result)){
echo "<h>Selamat Datang ".$row['nama']. " Di Aplikasi Sales Manajement Tools</h>";
}
//cek apakah user sudah login
?>	

