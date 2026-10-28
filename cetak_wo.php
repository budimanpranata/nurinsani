<?php require_once __DIR__ . '/php8_compat.php';
session_start();
 include "conn2.php";
 include "function.php";
  $koneksi=open_connection2();
  $ao=$_POST ['ao'];
 
  $hari=$_POST ['hari'];
$buss_date=date("y-m-d");
$userid=$_SESSION['userid'];
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");//jika belum login jangan lanjut..
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
     header("location:index.html");//jika bukan user jangan lanjut
 exit; }
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
.style5 {font-family: Verdana, Arial, Helvetica, sans-serif}
.style6 {font-size: 15px}
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


<table width="100%" >
  <tr>
    <td height="15" bgcolor="#669900" class="style3 style9">Cetak Dokumen CS <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
  </tr>
<tr>
    <td width="237" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style8 style6">Nama Ao </div></td>
    <td width="10" nowrap> <div align="center"><strong>:</strong></div></td>
    <td><select name="ao" id ="ao" type="text">
		  <option value="">- Select Nama AO -</option>
	  <?php
              		$query = "select * from ao where kode_unit='$branch'";
					$result = mysql_query($query);
					while ($kanwil=mysql_fetch_array($result))
					{
			  		  	echo "<option value=$kanwil[0]>$kanwil[1]</option>";
					}
              		echo"</select>";?>    </td>
</tr>

<tr>
    <td width="237" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style8 style6">Hari Tagih </div></td>
    <td width="10" nowrap> <div align="center"><strong>:</strong></div></td>
    <td><select name="hari" id ="hari" type="text">
		  <option value="">- Select Hari --</option>
          <option value="Senin">  Senin </option>
          <option value="Selasa">  Selasa </option>
          <option value="Rabu">  Rabu </option>
          <option value="Kamis">  Kamis </option>
	  
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

<table width="100%" class="table1">
  <tr class="table1">
    <th>Unit</span></th>
    <th >Kode AO</th>
	<th>Hari</th>
  	<th>NOA</th>
	<th>Total Tagihan</th>
    <th>Excel </th>
	<th>Cetak </th>
  </tr>
  <?php
$genap = "#FAEBD7";

$ganjil="#FFFFFF";

  If ($_POST ['search'])
{
if (empty($ao)){
	echo "<script>alert('Maaf,.isi KODE AO!');</script>";
         echo "<script language='javascript'>document.location.href='cetak.php'</script>";}
	else{
		  $_SESSION['hari'] = $hari;
		  $_SESSION['code_cao'] = $ao;



	$select = mysql_query ("select hari,branch.unit,loan_wo.cao,loan_wo.code_kel as kel,nama_kel,count(loan_wo.no_anggota)as noa,sum(loan_wo.angsuran) as angsur from loan_wo 
	LEFT join branch on branch.kode_branch = loan_wo.unit
	LEFT join kelompok on kelompok.code_kel = loan_wo.code_kel
	
	where loan_wo.cao ='$ao' and loan_wo.USERID='$userid' AND loan_wo.hari ='$hari' and os > 0 group by loan_wo.hari");
		}

	if(mysql_num_rows($select)==0){

        $select = mysql_query ("select hari,branch.unit,loan_wo.cao,loan_wo.code_kel as kel,nama_kel,count(loan_wo.no_anggota)as noa,sum(loan_wo.angsuran) as angsur from loan_wo 
	LEFT join branch on branch.kode_branch = loan_wo.unit
	LEFT join kelompok on kelompok.code_kel = loan_wo.code_kel
	
	where loan_wo.cao ='$ao' and loan_wo.USERID='$userid' AND loan_wo.hari ='$hari' and os > 0 group by loan_wo.hari");
		
		 }
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){
		
//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
 <tr>
    <td class="style6"><?php echo $data['unit']; ?></td>
	<td class="style6"><?php echo $data['cao'];  ?></td>
	<td class="style6"><?php echo $data['hari']; ?></td>
	<td class="style6"><?php echo $data['noa']; ?></td>
      <td class="style6" align="right"><?php echo "Rp.".number_format(compat_num($data['angsur'])); ?></td>
	   <td><div align="center"><a href="cetak_cs_excel.php?hari=<?php echo $data['hari'];?>" target="_blank" class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong>excel</strong></a></div></td>
     <td><div align="center"><a href="cetak_cs_wo.php?hari=<?php echo $data['hari'];?>" target="_blank" class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong><img src="Image/print.png" width="26" height="22" border="0" longdesc="cetak"></strong></a></div></td>
  </tr>
  <?php
		
	}
	}
else{
 $pilih = mysql_query ("select branch.unit,loan_detail.cao,loan_detail.code_kel as kel,nama_kel,hari_tagih,count(loan_detail.no_anggota),sum(loan_detail.angsuran) from loan_detail 
	inner join branch on branch.kode_branch = loan_detail.unit
	inner join loan_wo on loan_wo.no_anggota = loan_detail.no_anggota
	inner join kelompok on kelompok.code_kel = loan_detail.code_kel
	where loan_detail.code_kel ='$nopen' and loan_detail.USERID='$userid' AND loan_wo.tgl_murab ='$buss_date' Group By loan_detail.unit");
while($data = mysql_fetch_array($pilih)){
?>
 <tr>
    <td class="style6"><?php echo $data['unit']; ?></td>
	<td class="style6"><?php echo $data['cao'];  ?></td>
	<td class="style6"><?php echo $data['nama_kel']; ?></td>
    <td class="style6"><?php echo $data['hari_tagih']; ?></td>
	<td class="style6"><?php echo $data['tgl_murab'];?></td>
	<td class="style6"><?php echo $data['noa']; ?></td>
     <td class="style6" align="Right"><?php echo "Rp.".number_format(compat_num($data['angsur'])); ?></td>
       <td><div align="center"><a href="cetak_cs_hari.php?code_kel=<?php echo $data['kel'];?>" class="style6" onClick="return confirm('Apakah Anda yakin?')"><strong><img src="Image/print.png" width="26" height="22" border="0" longdesc="cetak"></strong></a></div></td>
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
