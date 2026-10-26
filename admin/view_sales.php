<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();  
$userid=$_SESSION['userid'];
include "../conn2.php";
$koneksi=open_connection2();
$today=date("Ymd");

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
}

//cek level user
?>	

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Daily Activity</title>
<link href="css/bootstrap.min.css" rel="stylesheet">	
		<link href="css/font-awesome.min.css" rel="stylesheet">	
	<link href="css/style.css" rel="stylesheet">	
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.style3 {	color: #FFFFFF;
	font-size: 13px;
	font-weight: bold;
}
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 16px; margin: 0px;}
body {
	background-image: url();
}
a:link {
	text-decoration: none;
}
a:visited {
	text-decoration: none;
}
a:hover {
	text-decoration: none;
}
a:active {
	text-decoration: none;
}
.style6 {font-size: 15px}
.style8 {font-size: 14px}
a {
	font-size: 12px;
}
.style10 {font-size: 12px}
-->
</style>
</head>


<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC" hspace="20">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8" >Update Sales Feedback
      <div align="right"></div></td>
  </tr>
</table>
<table width="428" border="1" align="center">
</table>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table width="1141" class="table table-bordered">
  <tr>
    <th width="45"><span class="badge">NAMA AO</span></th>
	<th width="45"><span class="badge">NIK AO</span></th>
    <th width="100"><span class="badge">JUMLAH SALES</span></th>
	<th width="75"><span class="badge">DETAIL</span></th>
  </tr>
  <?php
 $pilih = mysql_query ("SELECT COUNT( person.NIK_atasan ) AS JUMLAH_MR, user.nama AS NAMA_AO, user.userid as NIK_AO
FROM  person
right  join user on user.userid = person.NIK_atasan
WHERE user.level = 'spvs'
GROUP BY person.NIK_atasan, user.nama
ORDER BY JUMLAH_MR");
while($data = mysql_fetch_array($pilih)){
//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));
?>
  <tr>
    <td class="style6"><?php echo $data['NAMA_AO']; ?></td>
	<td class="style6"><?php echo $data['NIK_AO']; ?></td>
	<td class="style6"><?php echo $data['JUMLAH_MR'];  ?></td>
	<td><div align="center"><a href="input_sales.php?sales=<?php echo $data['NIK_AO']?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Update data MR?')"><strong>Detail</strong></a></div></td>
  </tr>
<?php

}

 
?>	
</table>
<p>&nbsp;</p>

 <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
