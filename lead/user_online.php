<?php require_once __DIR__ . '/../php8_compat.php';
include "conn.php";
$koneksi=open_connection();
@session_start();
$username= $_SESSION['userid'];
if ($username){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Member Online</title>
<style type="text/css">
<!--
.style14 {	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>
</head>

<body>
<div >
<table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#4682B4">
  <tr>
    <td width="100%" bgcolor="#776a56" class="style14">Report User Online </td>
  </tr>
</table>
<div class="center">
<div>
<div class="online"><h3>User Online</h3></div>
<div>
<?php 
//menampilkan member yang sedang login
$get_on =mssql_query("SELECT * FROM tabeluser WHERE status='1'");
while ($row=mssql_fetch_assoc($get_on)){
	$nama= $row['nama'];
	echo"<img src=Image/online.gif width='12' height='12'> $nama";
}
?>
</div>
</div>

<div>
<div ><h3>User Offline</h3></div>
<div>
<?php
//untuk menampilkan member yang sudah logout
$get_out =mssql_query("SELECT * FROM tabeluser WHERE status='0'");
while ($row=mssql_fetch_assoc($get_out)){
	$nama= $row['nama'];
	echo"<img src=Image/offline.gif width='12' height='12'> $nama,";
}
?>
</div>
</div>


</div>
</body>
</html>
<?php
}
	else {
		header("location:../index.html");
	}

?>