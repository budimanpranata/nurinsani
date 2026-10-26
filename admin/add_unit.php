<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
  include "../conn2.php";
    $koneksi=open_connection2();
  $today=date("Ymd");
$count = mysql_query ("select count(kode_branch) AS last FROM branch");
$hit = mysql_fetch_array($count);
$lastNoTransaksi = $hit['last'];
$lastNoUrut = $lastNoTransaksi;
$nextNoUrut = $lastNoUrut + 1;
$nextNoTransaksi = sprintf('%03s', $nextNoUrut);
$kode_unit="$nextNoTransaksi";
?>

	
</script>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Database Pensiun</title>
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

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#669900">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Pebukaan Unit Baru 
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Input Data </div></td>
  </tr>
<tr>
    <td width="237" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">Kode Unit </div></td>
    <td width="10" nowrap>        <div align="center"><strong>:</strong></div></td>
    <td width="241" colspan="2" nowrap><input name="unit" type="text" id="unit" value="<?php echo $kode_unit;?>" size="6" maxlength="40"/>      </td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Nama Unit</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="nama" type="text" id="nama" size="30"/> </td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Alamat</div></td>
  <td nowrap>
   <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="alamat" type="text" id="alamat"  size="45"/>    			
  </tr>

<tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Input" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table width="100%" class="datatable">
  <tr class="header1">
    <th width="48">NOREK</th>
    <th width="200">NAMA</th>
    <th width="100">UNIT </th>
	<th width="70">DEBIT</th>
    <th width="7-">CREDIT</th>
    <th width="70">SALDO</th>
  </tr>
  <?php

  include "function.php";

  $unit=$_POST ['unit'];
  $nama=$_POST['nama'];
   $alamat=$_POST['alamat'];
  $buss_date=date("Ymd");


  If ($_POST ['search'])
{
if (empty($unit)||empty($nama)||empty($alamat)){
	echo "<script>alert('Maaf,.isi form yang benar  !!!');</script>";
         echo "<script language='javascript'>document.location.href='add_unit.php'</script>";}
	
	else{
		$gl = mysql_query ("select * FROM coa");
		while($gl_r = mysql_fetch_array($gl)){
					$kode_rek=$gl_r['kode_rek'];
					$nama_rek=$gl_r['nama_rek'];
					$posisi	=$gl_r['posisi'];
					$normal=$gl_r['normal'];
					$gr_neraca = $gl_r['gr_neraca'];
$gr_head = $gl_r['gr_head'];
$gr_sub = $gl_r['gr_sub'];
$gr_neraca = $gl_r['gr_neraca'];
$LINE_BALANCE = $gl_r['LINE_BALANCE'];

					$rek_gl="$unit-$kode_rek";
		
	

		$query="insert into branch(kode_branch,unit,alamat,tgl_open) values('$unit','$nama','$alamat','$buss_date')";
		$t = mysql_query($query);
		$query2="insert into tabel_master(rekening_gl,kode_rekening,nama_rekening,unit,posisi,normal,gr_neraca,gr_head,gr_sub,LINE_BALANCE) values('$rek_gl','$kode_rek','$nama_rek','$unit','$posisi','$normal','$gr_neraca','$gr_head','$gr_sub','$LINE_BALANCE')";
		$y = mysql_query($query2);
	}
		if (($t)||($y)){
			echo "<script>alert('Unit $nama Sudah di Tambahkan Kedalam Systen !!!');</script>";
		
		}else{
			echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
		}
	}
	
  ?>
  
  <?php
	
	//menampilkan data
	$select = mysql_query ("select * FROM tabel_master where unit ='$unit'");
	while($data = mysql_fetch_array($select)){

//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
  <tr>
    <td class="style6"><?php echo $data['rekening_gl']; ?></td>
	<td class="style6"><?php echo $data['nama_rekening'];  ?></td>
    <td class="style6"><?php echo $data['unit'];?></td>
	<td class="style6"><?php echo number_format (compat_num($data['awal_debet'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['awal_kredit'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['awal_kredit'])); ?></td>
    
  </tr>
  <?php
		
	}
	}
	?>
  
</table>
<p>&nbsp;</p>

 <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
