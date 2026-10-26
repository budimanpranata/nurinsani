<?php require_once __DIR__ . '/php8_compat.php';
session_start();
 include "conn2.php";
 include "function.php";
  $koneksi=open_connection2();
  $nopen=$_POST ['nopen'];
  $tanggl=$_POST ['tanggal'];
$buss_date=date("y-m-d");
$userid=$_SESSION['userid'];
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="unit"){
     header("location:index.html");//jika bukan user jangan lanjut
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
    <td width="237" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">KODE AO </div></td>
    <td width="10" nowrap> <div align="center"><strong>:</strong></div></td>
    <td><select name="nopen" id ="nopen" type="text">
		  <option value="">- Select Kode AO -</option>
	  <?php
              		$query = "select * from ao where kode_unit='$branch'";
					$result = mysql_query($query);
					while ($kanwil=mysql_fetch_array($result))
					{
			  		  	echo "<option value=$kanwil[0]>$kanwil[1]</option>";
					}
              		echo"</select>";?>    </td>
</tr>

  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal CS </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="tanggal" type="text" id="tanggal" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal);return true;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
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
    <th >Kode Ao</th>
	<th>Kelompok</th>
    <th>Tanggal Tagih</th>
	<th>Noa</th>
	<th>Total Tagihan</th>
<th>Excel </th>
    <th>Cetak </th>
  </tr>
  <?php
$genap = "#FAEBD7";

$ganjil="#FFFFFF";

  If ($_POST ['search'])
{
if (empty($nopen)){
	echo "<script>alert('Maaf,.isi KODE AO !!!');</script>";
         echo "<script language='javascript'>document.location.href='cetak.php'</script>";}
	else{
		  $_SESSION['jatuh'] = $tanggl;
		  $_SESSION['code_cao'] = $nopen;
$day = date('D', strtotime($tanggl));
$dayList = array(
	'Sun' => 'Minggu',
	'Mon' => 'Senin',
	'Tue' => 'Selasa',
	'Wed' => 'Rabu',
	'Thu' => 'Kamis',
	'Fri' => 'Jumat',
	'Sat' => 'Sabtu'
);

	$select = mysql_query ("select branch.unit,loan.cao,loan.code_kel as kel,nama_kel,count(loan.no_anggota)as noa,sum(loan.angsuran) as angsur from loan 
	LEFT join branch on branch.kode_branch = loan.unit
	LEFT join kelompok on kelompok.code_kel = loan.code_kel
	
	where loan.cao ='$nopen' and loan.unit='$userid' AND loan.hari ='$dayList[$day]' and os > 0 group by loan.code_kel");
		}

	if(mysql_num_rows($select)==0){
$select = mysql_query ("select branch.unit,loan.cao,loan.code_kel as kel,nama_kel,count(loan.no_anggota)as noa,sum(loan.angsuran) as angsur,pinjaman_detail_jatpo.tgl_jatuh_tempo as tgl_murab from loan 
	LEFT join branch on branch.kode_branch = loan.unit
	LEFT join kelompok on kelompok.code_kel = loan.code_kel
	LEFT join pinjaman_detail_jatpo on pinjaman_detail_jatpo.id_pinjam = loan.no_anggota
	where loan.cao ='$nopen' and loan.unit='$userid' AND pinjaman_detail_jatpo.tgl_jatuh_tempo ='$tanggl' and os > 0 group by loan.code_kel");
		
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
	<td class="style6"><?php echo $data['nama_kel']; ?></td>
	<td class="style6"><?php echo tgl_indo($data['tgl_murab']);?></td>
	<td class="style6"><?php echo $data['noa']; ?></td>
      <td class="style6" align="right"><?php echo "Rp.".number_format(compat_num($data['angsur'])); ?></td>
<td><div align="center"><a href="cetak_cs_excel.php?kode_kel=<?php echo $data['kel'];?>" target="_blank" class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong>excel</strong></a></div></td>
     <td><div align="center"><a href="cetak_cs_hari.php?kode_kel=<?php echo $data['kel'];?>" target="_blank" class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong>cetak</strong></a></div></td>
  </tr>
  <?php
		
	}
	}
else{
 $pilih = mysql_query ("select branch.unit,loan_detail.cao,loan_detail.code_kel as kel,nama_kel,hari_tagih,count(loan_detail.no_anggota),sum(loan_detail.angsuran) from loan_detail 
	inner join branch on branch.kode_branch = loan_detail.unit
	inner join loan on loan.no_anggota = loan_detail.no_anggota
	inner join kelompok on kelompok.code_kel = loan_detail.code_kel
	where loan_detail.code_kel ='$nopen' and loan_detail.USERID='$userid' AND loan.tgl_murab ='$buss_date' Group By loan_detail.unit");
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
