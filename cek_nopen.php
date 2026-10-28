<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

?>

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
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Cek Database Pensiun 
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
  </tr>
<tr>
    <td width="237" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">Nopen /No Pegawai </div></td>
    <td width="10" nowrap>        <div align="center"><strong>:</strong></div></td>
    <td width="241" colspan="2" nowrap><input name="nopen" type="text" id="tgl_trn4" value="" size="40" maxlength="40"/>      </td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Nama</div></td>
  <td nowrap>
    <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="nama" type="text" id="tgl_trn22" value="" size="40"/>    </td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Pensiun </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" size="1">
    <option>Pilih Salah satu</option>
    <option value="01">Taspen</option>
    <option value="02">Asabri</option>
  </select></td>
</tr>

<tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Search" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table width="1141" class="datatable">
  <tr class="header1">
    <th width="48">Nopen</th>
    <th width="98">kode</th>
    <th width="237">Nama</th>
    <th width="128">Tanggal Lahir</th>
	<th width="38">Usia</th>
    <th width="49">Alamat</th>
    <th width="70">Kota</th>
    <th width="70">No SK</th>
    <th width="70">Tgl Skep </th>
    <th width="116">Penerbit</th>
    <th width="98">Kantor Bayar </th>
    <th width="67">GAJI</th>
	<th width="67">SIMULASI</th>
	<th width="67">CETAK</th>

  </tr>
  <?php
  include "conn2.php";
  $koneksi=open_connection2();
  $nopen=$_POST ['nopen'];
  $Nama=$_POST['nama'];
  $tanggl=$_POST ['tanggal'];
  $select=$_POST['select'];
  If ($_POST ['search'])
{
if (empty($nopen)||empty($select)){
	echo "<script>alert('Maaf,.isi Nomor Pegawai dan Mitra yang benar  !!!');</script>";
         echo "<script language='javascript'>document.location.href='cek_nopen.php'</script>";}
	
	else{
	$select = mysql_query ("select NOPEN,JENIS,NAMA,TGLLAHIR,TIMESTAMPDIFF(YEAR, TGLLAHIR, CURDATE()) AS Usia,KBAYAR,GAJI,ALAMAT,KOTA,NOSKEP,TGLSKEP,SKEP FROM cim_dpm where NOPEN ='$nopen' AND KDMITRA='$select'");
		}

	if(mysql_num_rows($select)==0){
			echo "<script>alert('Maaf...,data tdak di temukan!!!');</script>";
         echo "<script language='javascript'>document.location.href='Cek_nopen.php'</script>";
		 }
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){
$angka = $data['Plafond'];
$gaji= $data ['Gaji_Bersih'];
//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
  <tr>
    <td class="style6"><?php echo $data['NOPEN']; ?></td>
	<td class="style6"><?php echo $data['JENIS'];  ?></td>
    <td class="style6"><?php echo $data['NAMA']; ?></td>
	<td class="style6"><?php echo $data['TGLLAHIR'];?></td>
	<td class="style6"><?php echo $data['Usia']; ?></td>
    <td class="style6"><?php echo $data['ALAMAT']; ?></td>
    <td class="style6"><?php echo $data['KOTA']; ?></td>
    <td class="style6"><?php echo $data['NOSKEP'];?></td>
    <td class="style6"><?php echo $data['TGLSKEP'];?></td>
    <td class="style6"><?php echo $data['SKEP'];?></td>
    <td class="style6"><?php echo $data['KBAYAR'];?></td>
	<td class="style6"><?php echo $data['GAJI'];?></td>
	    <td><div align="center"><a href="../new.php?nopen=<?php echo $data['NOPEN'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Perhitungan ?')"><strong>Simulasi</strong></a></div></td>
<td><div align="center"><a href="cetak_pen.php?nopen=<?php echo $data['NOPEN'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Perhitungan ?')"><strong>Cetak</strong></a></div></td>

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
