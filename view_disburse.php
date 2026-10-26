<?php require_once __DIR__ . '/php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
}
 include "conn2.php";
  $koneksi=open_connection2();
  $branch=$_POST ['branch'];
  $tanggl=$_POST ['tgl_cari'];
$buss_date=date("y-m-d");
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");//jika belum login jangan lanjut..
}
?>	

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Simulasi Kredit</title>
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
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Cari Data Disbursement 
      <div align="right"></div></td>
  </tr>
</table>

<table width="100%" border="1" align="center">
</table>
  <form autocomplete="off" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
  </tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Pencarian </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="tgl_cari" type="text" id="tgl_trn24" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_cari);return true;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
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

<table width="100%" class="datatable">
  <tr class="header1">
    <th width="45">Unit</th>
    <th width="100">NOREK</th>
	<th width="100">Deal Type</th>
    <th width="200">Nama</th>
    <th width="130">Nama Kelompok</th>
	<th width="70">CIF</th>
	<th width="70">Palfound</th>
    <th width="50">Tenor</th>
    <th width="75">Detail</th>
  </tr>
  <?php
 $pilih = mysql_query ("select unit,no_anggota,Deal_type,nama_kel,Cust_Short_name,loan.cif, nama,Plafond,Tenor from loan 
 inner join prod on prod.code=loan.Deal_type 
 inner join kelompok on kelompok.code_kel = loan.code_kel
 where BUSS_DATE='$buss_date' ");
while($data = mysql_fetch_array($pilih)){
$angka = $data['Plafond'];
//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));
?>
  <tr>
    <td class="style6"><?php echo $data['unit']; ?></td>
	<td class="style6"><?php echo $data['no_anggota'];  ?></td>
	<td class="style6"><?php echo $data['nama']; ?></td>
    <td class="style6"><?php echo $data['Cust_Short_name']; ?></td>
	<td class="style6"><?php echo $data['nama_kel'];?></td>
	<td class="style6"><?php echo $data['cif']; ?></td>
      <td class="style6"><?php echo "".number_format(compat_num($angka)); ?></td>
    <td class="style6"><?php echo $data['Tenor'];?></td>
    <td><div align="center"><a href="ctk_dok.php?anggota=<?php echo $data['No_Pegawai'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Input Data?')"><strong>Detail</strong></a></div></td>
  </tr>
<?php

}

  If (isset($_POST ['search']))
{
if (empty($tgl_cari)){
	echo "<script>alert('Maaf,.isi Tanggal Tansaksi yang benar  !!!');</script>";
         echo "<script language='javascript'>document.location.href='view_disburse.php'</script>";}
	else{
	$select = mysql_query ("select unit,no_anggota,Deal_type,nama_kel,Cust_Short_name,loan.cif, nama,Plafond,Tenor from loan 
 inner join prod on prod.code=loan.Deal_type 
 inner join kelompok on kelompok.code_kel = loan.code_kel
 where BUSS_DATE='$buss_date'  and unit ='$unit' ");
		}

	if(mysql_num_rows($select)==0){
			echo "<script>alert('Maaf...,data tidak ditemukan!!');</script>";
         echo "<script language='javascript'>document.location.href='view_disburse.php' </script>";
		 }
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){
$angka = $data['Plafond'];
$os= $data ['os'];
$gaji= $data ['Gaji_Bersih'];

//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
  <tr>
    <td class="style6"><?php echo $data['Branch']; ?></td>
	<td class="style6"><?php echo $data['no_anggota'];  ?></td>
	<td class="style6"><?php echo $data['Deal_type']; ?></td>
    <td class="style6"><?php echo $data['Cust_Short_name ']; ?></td>
	<td class="style6"><?php echo $data['tgl_lahir'];?></td>
	<td class="style6"><?php echo $data['CIF']; ?></td>
    <td class="style6"><?php echo "".number_format(compat_num($angka)); ?></td>
    <td class="style6"><?php echo $data['Tenor'];?></td>
   
    <td><div align="center"><a href="view_disburse.php?nopen=<?php echo $data['No_Pegawai'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Entry Data?')"><strong>Detail</strong></a></div></td>
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
