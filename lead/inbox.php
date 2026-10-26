<?php require_once __DIR__ . '/../php8_compat.php';
session_start();

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
    die("Maaf Sesion anda habis,  Anda harus login lembali");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="lead"){
    die("Anda bukan user");//jika bukan user jangan lanjut
}
?>	

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Inbox SMS Blast</title>
<link rel="stylesheet" href="tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.style3 {	color: #FFFFFF;
	font-size: 13px;
	font-weight: bold;
}
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
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
.style5 {font-family: Verdana, Arial, Helvetica, sans-serif}
.style6 {font-size: 12px}
.style7 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; }
.style8 {font-size: 14px}
a {
	font-size: 12px;
}
.style9 {
	font-size: 16px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
-->
</style>
</head>


<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#776a56" class="style3 style8">Inbox 
      <div align="right"></div></td>
  </tr>
</table>
<br>
<table width="428" border="1" align="center">
</table>
  <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table width="1125" class="datatable">
  <tr class="header1">
    <th width="86">NAMA SMS </th>
    <th width="130">NIK </th>
    <th width="141">Tanggal Kirim</th>
	<th width="142">Pengirim</th>
    <th width="476">Isi Pesan</th>
        <th width="122">Detail</th>
  </tr>
  <?php
 include "conn.php";
	include "conn2.php";
	$koneksi=open_connection();
	$koneksi2=open_connection2();
$cek= "SELECT * FROM inbox  WHERE Processed ";
    $cek1= mysql_query($cek);
    while ($data = mysql_fetch_array($cek1)) {
    $noPengirim = $data['SenderNumber'];
	$ambil=substr($noPengirim,3,15);
	$t=0;
	$jadi="$t$ambil";
}

  $select = mysql_query ("SELECT
data_sms.nama As NAMA,
sms_blast.UserId AS NIK,
ReceivingDateTime,
SenderNumber,
TextDecoded 
 FROM  INBOX, sms_blast, data_sms
WHERE inbox.SenderNumber=sms_blast.no_tlp AND sms_blast.UserId=data_sms.nik
Order By sms_blast.UserId,ReceivingDateTime ");
		
  ?>
  
  <?php
	
	//menampilkan data
while($data = mysql_fetch_array($select)){
$angka = $data['ReceivingDateTime'];
$os= $data ['SenderNumber'];
$gaji= $data ['TextDecoded'];
		?>
  <tr>
    <td class="style6"><?php echo $data['NAMA']; ?></td>
	<td class="style6"><?php echo $data['NIK'];  ?></td>
	<td class="style6"><?php echo $data['ReceivingDateTime'];  ?></td>
	<td class="style6"><?php echo $data['SenderNumber']; ?></td>
    <td class="style6"><?php echo $data['TextDecoded']; ?></td>
	<td><div align="center"><a href="reply.php?nopen=<?php echo $data['SenderNumber'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan embalas Pesan ?')"><strong>Balas</strong></a> <a href="delete.php?nopen=<?php echo $data['SenderNumber'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan menghapus Pesan ?')"><strong>| Delete</strong></a></div></td>
 	 
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
