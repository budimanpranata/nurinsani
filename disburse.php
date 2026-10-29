<?php require_once __DIR__ . '/php8_compat.php';
session_start();
 include "conn2.php";
$koneksi=open_connection2();

   $buss_date1=$_POST ['buss_date1'];
$buss_date2=$_POST ['buss_date2'];
$userid=$_SESSION['userid'];
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
    <td height="20" bordercolor="#FFFFFF" bgcolor="#2F6B4F" class="style3 style8">Report Disbursement
      <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#2F6B4F">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9"> Pencarian Berdasarkan Tanggal </div></td>
  </tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tangggal Awal </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="buss_date1" type="text" id="tgl_lahir" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_lahir);return true;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Akhir </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="buss_date2" type="text" id="tgl_trn" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_trn);return true;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
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

<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;"> </iframe>


<table width="100%" class="table1">
  <tr class="table1">
    <th width="45">Unit</th>
    <th width="100">Pembiayaan</th>
	<th width="100">NoA</th>
	<th width="100">Download</th>
    </tr>
<?php

  If (isset($_POST ['search']))
{
if (empty($buss_date1)){
	echo "<script>alert('Maaf,.isi tanggal pencarian yang benar euy !!!');</script>";
         echo "<script language='javascript'>document.location.href='disburse.php'</script>";}
	else{
		$_SESSION['sbuss1']=$buss_dat1;
		$_SESSION['sbuss2']=$buss_date2;

	$select = mysql_query ("select unit, 
SUM(Plafond) AS PLA, 
Count(cif) AS NOA 
FROM loan_detail where buss_date BETWEEN '$buss_date1' AND '$buss_date2' and unit='$userid' Group By unit");
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){
$angka = $data['PLA'];
$Branch= $data ['unit'];
$noa= $data ['NOA'];

//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
  <tr>
    <td class="style6"><?php echo $data['unit']; ?></td>
	<td class="style6"><?php echo "".number_format(compat_num($angka)); ?></td>
    <td class="style6"><?php echo $noa;?></td>
	<td><div align="center"><a href="javascript:;" ><img src="excel-icon.jpeg" width="16" height="15" border="0" onClick="window.open('export_disburse.php','scrollwindow','top=200,left=300,width=800,height=500');"><strong> Disburse </strong></a></div> 
   </tr>
  <?php
		
	}
	}

}
?>	

  
</table>
<p>&nbsp;</p>

 <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
