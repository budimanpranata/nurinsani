<?php require_once __DIR__ . '/../php8_compat.php';
session_start();

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
     header("location:index.html");//jika belum login jangan lanjut..
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
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Simulasi
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
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis</span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="Jenis" type="text" id="tgl_trn23" value="" size="40"/>    </td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Lahir </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="tgl_lahir" type="text" id="tgl_trn24" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_lahir);return true;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
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
    <th width="45">Branch</th>
    <th width="100">Nopen</th>
	<th width="100">Deal Type</th>
    <th width="200">Nama</th>
    <th width="130">Tanggal Lahir</th>
	<th width="35">Usia</th>
    <th width="35">CIF</th>
    <th width="70">Outstanding</th>
    <th width="70">Palfound</th>
    <th width="50">Tenor</th>
    <th width="130">Maturity DAte </th>
    <th width="75">Detail</th>
  </tr>
  <?php
  include "../conn2.php";
  $koneksi=open_connection2();
  $nopen=$_POST ['nopen'];
  $Nama=$_POST['nama'];
  $tanggl=$_POST ['tanggal'];
  
  If ($_POST ['search'])
{
if (empty($nopen)){
	echo "<script>alert('Maaf,.isi Nomor Pegawai yang benar euy !!!');</script>";
         echo "<script language='javascript'>document.location.href='simulasi.php'</script>";}
	else{
	$select = mysql_query ("select BRANCH,No_Pegawai,DEAL_TYPE,CUST_SHORT_NAME,BIRTH_DATE,PLAFOND,TENOR,CIF,MATURITY_DATE FROM stgloan where No_Pegawai ='$nopen' ");
		}

	if(mysql_num_rows($select)==0){
			
			echo "<script>alert ('Maaf...,Nopen $nopen tidak ditemukan, system akan membuat Perhitungan NEW!!!'); </script>";
         echo "<script language='javascript'>document.location.href='new.php' </script>";
		 }
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){
$angka = $data['PLAFOND'];
$os= $data ['Outstanding'];
$gaji= $data ['Gaji_Bersih'];
//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
  <tr>
    <td class="style6"><?php echo $data['BRANCH']; ?></td>
	<td class="style6"><?php echo $data['No_Pegawai'];  ?></td>
	<td class="style6"><?php echo $data['DEAL_TYPE']; ?></td>
    <td class="style6"><?php echo $data['CUST_SHORT_NAME']; ?></td>
	<td class="style6"><?php echo $data['BIRTH_DATE'];?></td>
	<td class="style6"><?php echo $data['USIA']; ?></td>
    <td class="style6"><?php echo $data['CIF']; ?></td>
    <td class="style6"><?php echo "".number_format (compat_num($os)); ?></td>
    <td class="style6"><?php echo "".number_format(compat_num($angka)); ?></td>
    <td class="style6"><?php echo $data['TENOR'];?></td>
    <td class="style6"><?php echo $data['MATURITY_DATE'];?></td>
     <td><div align="center"><a href="simulasi.php?nopen=<?php echo $data['No_Pegawai'];?>" class="style6" onClick="return confirm('Maaf Renewal Belum bisa ?')"><strong>Detail</strong></a></div></td>
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
