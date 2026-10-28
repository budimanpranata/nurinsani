<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
 include "../../conn2.php";
 include "../../function.php";
  $koneksi=open_connection2();
  $tanggal=$_POST['tanggal'];
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
if($_SESSION['level']!="fund"){
     header("location:../../index.html");//jika bukan user jangan lanjut
 exit; }
$perode_bulan=date("Y-m-d");
		 $tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,2);
					$code_bln_f="NI$tahun-$bulan";
	$query_jml_aset=mysql_fetch_array(mysql_query("select nomi_murab,marigin_murab,DATEDIFF(sd,dari) as hari from param_finan group by code_param order by code_param DESC limit 1"));
	$tot_fin=$query_jml_aset['nomi_murab'];
	$margin=$query_jml_aset['marigin_murab'];
	$hari=$query_jml_aset['hari'];


$kalender =CAL_GREGORIAN;
$bulan =date('m');
$tahun =date('y');
$hari=cal_days_in_month($kalender,$bulan,$tahun);

$hari =30;

?>	

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Simulasi Kredit</title>
<link rel="stylesheet" href="../../tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.style3 {	color: #FFFFFF;
	font-size: 12px;
	font-weight: bold;
}
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 12px; margin: 0px;}
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
.style8 {font-size: 12px}
a {
	font-size: 12px;
}
.style9 {
	font-size: 12px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
-->
</style>
</head>


<table width="100%" >
  <tr>
    <td height="15" bgcolor="#669900" class="style3 style9">Inquiry Deposito <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
  </tr>

  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Jatuh Tempo </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="tanggal" type="text" id="tanggal" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal);return true;" ><img src="../../images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
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
	<th>Norek</th>
    <th>Nama</th>
    <th>Bilyet</th>
	<th>Status</th>
	<th>Rasio %</th>
	<th>Eqv %</th>
	<th>Nominal</th>
	<th>Jatuh Tempo</th>
	<th>Baghas</th>
	<th>Rate</th>
<th>hari</th>
    <th>Aksi </th>
  </tr>
  <?php
$genap = "#FAEBD7";

$ganjil="#FFFFFF";

  If ($_POST ['search'])
{
if (empty($tanggal)){
	echo "<script>alert('Maaf,.isi Tanggal Pencarian yang Benar !');</script>";
         echo "<script language='javascript'>document.location.href='view_jatuh.php'</script>";}
	else{
		  $_SESSION['jatuh'] = $tanggal;
		  
	$select = mysql_query ("select CUST_SHORT_NAME,bilyet,id_deposito,norek,tgl_jatuh_tempo,aro,tenor,deposito.nominal as nomi,maturity_date,rasio,deposito.code_bulan,norek_tujuan,param_dep.nominal,margin_murab,eq_rate,last_payment from deposito 
left join deposito_detail on deposito.norek=deposito_detail.id_deposito 
left join deposan on deposito.cif=deposan.cif 
left join param_dep on param_dep.code_bulan= deposito.code_bulan 
where deposito_detail.tgl_jatuh_tempo ='$tanggal' and  deposito_detail.id_deposito not in (select norek from dep_realisasi) and deposito_detail.ket ='' group by norek");
		}

	if(mysql_num_rows($select)==0){
			echo "<script>alert('Maaf...,Data tidak ditemukan!!!');</script>";
         echo "<script language='javascript'>document.location.href='view_jatuh.php' </script>";
		 }
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){
$last_payment=$data['last_payment'];
		$dt1 = new DateTime($last_payment);
		$dt2 = new DateTime($tanggal);
		$hari_stay =$dt2->diff($dt1)->days + 0;

	//if($hari_stay>31){
		//$hari=0;
	//}else{
		//$hari=$hari_stay;
	//}
$hari =$hari;
		$aro=$data['aro'];
		$norek=$data['id_deposito'];
		$tgl_jatuh_tempo=$data['tgl_jatuh_tempo'];
		$code_bulan=$data['code_bulan'];
		$jenis_simpanan=$data['jenis_simpanan'];
		$norek_tujuan=$data['norek_tujuan'];
		$nominal=$data['nominal'];
		$nomi=$data['nomi'];
		$margin_murab=$data['margin_murab'];
		$maturity_date=$data['maturity_date'];
		$tenor=$data['tenor'];
		$rasio=$data['rasio'];
		$eq_rate=$data['eq_rate'];
		$kali_eq=$eq_rate/100;

		$param_rasio = mysql_query ("select rasio_invest,eq from param_dep where rasio_invest = '$rasio'");
		$param = mysql_fetch_array($param_rasio);
		$eq=$param['eq'];
		$rasio_invest=$param['rasio_invest'];
		
		//Perhitungan Nisbah
		$bayar=($nomi*$eq)*$hari/365;
		$gross=$bayar/0.9;
		$pph4=$gross-$bayar;
		$jlbayar=$gross-$pph4;
		//perhitungan Rate 
		$bayar_rate=($nomi*$kali_eq)*$hari/365;
		$gross_rate=$bayar_rate/0.9;
		$pph4_rate=$gross_rate-$bayar_rate;
		$jlbayar_rate=$gross_rate-$pph4_rate;		
		
		
//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
 <tr>
    
	<td class="style6"><?php echo $data['norek'];  ?></td>
	<td class="style6"><?php echo $data['CUST_SHORT_NAME'];?></td>
	<td class="style6"><?php echo $data['bilyet']; ?></td>
	<td class="style6"><?php echo $data['aro']; ?></td>
	<td class="style6"><?php echo $data['rasio']; ?></td>
	<td class="style6"><?php echo number_format (compat_num($eq*100),2); ?></td>
	<td align="center" class="style6"> <?php echo "Rp.".number_format(compat_num($data['nomi'])); ?> </td>
	<td class="style6"><?php echo tgl_indo($data['tgl_jatuh_tempo']);?></td>
	<td class="style6"><?php echo "Rp.".number_format(compat_num($bayar)); ?></td>
	<td class="style6"><?php echo "Rp.".number_format(compat_num($bayar_rate)); ?></td>
	<td class="style6"><?php echo $hari; ?></td>
	 	<td><div align="center"><a href="../depo_list.php?jatuh=<?php echo $data['tgl_jatuh_tempo'];?>"  class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong>Realisasi</strong></a></div></td>
  </tr>
  <?php
		
	}
	}
else{
 $pilih = mysql_query ("select CUST_SHORT_NAME,bilyet,id_deposito,norek,tgl_jatuh_tempo,aro,tenor,deposito.nominal as nomi,maturity_date,rasio,deposito.code_bulan,norek_tujuan,param_dep.nominal,margin_murab,eq_rate 
  from deposito 
inner join deposito_detail on deposito.norek=deposito_detail.id_deposito 
inner join deposan on deposito.cif=deposan.cif 
inner join param_dep on param_dep.code_bulan= deposito.code_bulan 
where deposito_detail.tgl_jatuh_tempo ='$buss_date' and deposito_detail.id_deposito not in (select norek from dep_realisasi) and deposito_detail.ket ='' group by norek");
while($data = mysql_fetch_array($pilih)){
	$aro=$data['aro'];
		$norek=$data['id_deposito'];
		$tgl_jatuh_tempo=$data['tgl_jatuh_tempo'];
		$code_bulan=$data['code_bulan'];
		$jenis_simpanan=$data['jenis_simpanan'];
		$norek_tujuan=$data['norek_tujuan'];
		$nominal=$data['nominal'];
		$nomi=$data['nomi'];
		$margin_murab=$data['margin_murab'];
		$maturity_date=$data['maturity_date'];
		$tenor=$data['tenor'];
		$rasio=$data['rasio'];
		$eq_rate=$data['eq_rate'];
		$kali_eq=$eq_rate/100;
		

		$param_rasio = mysql_query ("select rasio_invest,eq from param_dep where rasio_invest = '$rasio'");
		$param = mysql_fetch_array($param_rasio);
		$eq=$param['eq'];
		$rasio_invest=$param['rasio_invest'];
		
		//Perhitungan Nisbah
		$bayar=($nomi*$eq)*$hari/365;
		$gross=$bayar/0.9;
		$pph4=$gross-$bayar;
		$jlbayar=$gross-$pph4;
		//perhitungan Rate 
		$bayar_rate=($nomi*$kali_eq)*$hari/365;
		$gross_rate=$bayar_rate/0.9;
		$pph4_rate=$gross_rate-$bayar_rate;
		$jlbayar_rate=$gross_rate-$pph4_rate;
	
?>
 <tr>
   <td class="style6"><?php echo $data['norek'];  ?></td>
	<td class="style6"><?php echo $data['CUST_SHORT_NAME'];?></td>
	<td class="style6"><?php echo $data['bilyet']; ?></td>
	<td class="style6"><?php echo $data['aro']; ?></td>
	<td class="style6"><?php echo $data['rasio']; ?></td>
	<td class="style6"><?php echo number_format (compat_num($eq*100),2); ?></td>
	<td class="style6" align ="Right"><?php echo "Rp.".number_format(compat_num($data['nomi'])); ?></td>
	<td class="style6"align ="center"><?php echo tgl_indo($data['tgl_jatuh_tempo']);?></td>
	<td class="style6"align ="Right"><?php echo "Rp.".number_format(compat_num($bayar)); ?></td>
	<td class="style6"align ="Right"><?php echo "Rp.".number_format(compat_num($bayar_rate)); ?></td>
<td class="style6"><?php echo $hari; ?></td>
	<td><div align="center"><a href="../depo_list.php?jatuh=<?php echo $data['tgl_jatuh_tempo'];?>"  class="style6" onClick="return confirm('Apakah Anda yakin ?')"><strong>Realisasi</strong></a></div></td>
  </tr>
<?php

}
}
?>	
</table>
<p>&nbsp;</p>

 <iframe width=174 height=189 name="gToday:normal:../../calender/normal.js" id="gToday:normal:../../calender/normal.js" src="../../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
