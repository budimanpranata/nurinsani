<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
 include "../../conn2.php";
 include "../../function.php";
  $koneksi=open_connection2();
  $tanggal=$_POST ['tanggal'];
  $expt=$_POST ['expt'];
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
if($_SESSION['level']!="fund"){
     header("location:../../index.html");//jika bukan user jangan lanjut
}
$perode_bulan=date("Ymd");
		 $tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,2);
					$code_bln_f="NI$tahun-$bulan";
	$query_jml_aset=mysql_fetch_array(mysql_query("select nomi_murab,marigin_murab,DATEDIFF(sd,dari) as hari from param_finan where code_param='NI2018-07'"));
	$tot_fin=$query_jml_aset['nomi_murab'];
	$margin=$query_jml_aset['marigin_murab'];
	$hari=$query_jml_aset['hari'];
	

	
?>	
<script type="text/javascript" src="../../jquery.js"></script>
	<script>
	function suggest(inputString){
		if(inputString.length == 0) {
			$('#suggestions').fadeOut();
		} else {
		$('#country').addClass('load');
			$.post("../autosuggest_tgl.php", {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions').fadeIn();
					$('#suggestionsList').html(data);
					$('#country').removeClass('load');
				}
			});
		}
	}
	
	function fill(thisValue) {
		$('#country').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	
	function fill2(thisValue) {
		$('#tanggal').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	
	</script>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Nisbah</title>
<link rel="stylesheet" href="../../tabel.css" />
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
.style7 {font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; }
a {
	font-size: 12px;
}
.style9 {
	font-size: 16px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
-->
</style>
	<style>
	#result {
		height:20px;
		font-size:12px;
		font-family:Arial, Helvetica, sans-serif;
		color:#333;
		padding:5px;
		margin-bottom:10px;
		background-color:#FFFF99;
	}
	#country{
		padding:3px;
		border:1px #CCC solid;
		font-size:12px;
	}
	.suggestionsBox {
		position: absolute;
		left: 640px;
		top:60px;
		margin: 26px 0px 0px 0px;
		width: 200px;
		padding:0px;
		background-color:#669900;
		border-top: 3px solid #669900;
		color: #fff;
	}
	.suggestionList {
		margin: 0px;
		padding: 0px;
	}
	.suggestionList ul li {
		list-style:none;
		margin: 0px;
		padding: 6px;
		border-bottom:1px dotted #666;
		cursor: pointer;
	}
	.suggestionList ul li:hover {
		background-color: #b3b3ff;
		color:#000;
	}
	ul {
		font-family:Arial, Helvetica, sans-serif;
		font-size:11px;
		color:#FFF;
		padding:0;
		margin:0;
	}
	
	.load{
	background-image:url(loader.gif);
	background-position:right;
	background-repeat:no-repeat;
	}
	
	#suggest {
		position:relative;
	}
	</style>
</head>


<table width="100%" >
  <tr>
    <td height="15" bgcolor="#669900" class="style3 style9"><div align="left">Perhitungan Nisbah </div>
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="402" border="0" align="center">
<tr bgcolor="#669900">
 <td height="10" colspan="5"><div id="suggest">
   <div align="center" class="style3 style9">Kriteria Input </div> 
 </tr>

  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Periode </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="tanggal" type="text" id="tanggal" onKeyUp="suggest(this.value);"  onBlur="fill2();" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal);return true;" ><img src="../../images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a>
	
	 <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="List Kelompok" />
	 <div class="suggestionList" id="suggestionsList"> </div>
	 </div>
	
	 </div>	  </td>

	</tr>
<tr>
<td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">ekspektasi Nisbah 20% </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><div align="left">
  <input name="expt_20" type="text" id="expt_20" value="15" size="10" colspan="4">
  &nbsp;%</div></td>
  </tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">ekspektasi Nisbah 19% </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><div align="left">
  <input name="expt_19" type="text" id="expt_19" value="14" size="10" colspan="4">
  &nbsp;%</div></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">ekspektasi Nisbah 18% </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><div align="left">
  <input name="expt_18" type="text" id="expt_18" value="13" size="10" colspan="4">
  &nbsp;%</div></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">ekspektasi Nisbah 17% </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><div align="left">
  <input name="expt_17" type="text" id="expt_17" value="12" size="10" colspan="4">
  &nbsp;%</div></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">ekspektasi Nisbah 16% </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><div align="left">
  <input name="expt_16" type="text" id="expt_16" value="11" size="10" colspan="4">
  &nbsp;%</div></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">ekspektasi Nisbah 15% </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><div align="left">
  <input name="expt_16" type="text" id="expt_16" value="11" size="10" colspan="4">
  &nbsp;%</div></td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">ekspektasi Nisbah 14% </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><div align="left">
  <input name="expt_14" type="text" id="expt_14" value="11" size="10" colspan="4">
  &nbsp;%</div></td>
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
    <th width="168" rowspan="2" class="style3">Periode</th>
    <th width="284" rowspan="2" class="style3">Dana</th>
    <th width="172" rowspan="2"  class="style3">Margin Murabahah</th>
    <th colspan="3" class="style3">INVESTOR PORTION</th>
    <th colspan="2" class="style3">COOPERATVE PORTION</th>
  </tr>
  <tr class="datatable">
	<th width="76" class="style3">Ratio</th>
	<th width="105"  class="style3">Nominal</th>
	<th width="70" class="style3">Eq in % </th>
	<th width="121" class="style3">Ratio</th>
    <th width="182" class="style3">Nominal</th>
  </tr>
  <?php
$genap = "#FAEBD7";

$ganjil="#FFFFFF";

  If ($_POST ['search'])
{
if (empty($tanggal)){
	echo "<script>alert('Maaf,.isi Tanggal Pencarian yang Benar !');</script>";
         echo "<script language='javascript'>document.location.href='view_xpec.php'</script>";}
	else{
		  $_SESSION['jatuh'] = $tanggal;
		  
	$select = mysql_query ("SELECT code_bulan, nama_jenis, nominal,margin_bagi,rasio_invest, nomi_invest,eq,rasio_cor,nomi_cor,buss_date
FROM param_rasio inner join jenis_dana on jenis_dana.code_jenis = param_rasio.code_jenis
WHERE buss_date ='$tanggal' order by rasio_invest Desc ");
		}

	if(mysql_num_rows($select)==0){
			echo "<script>alert('Maaf...,Data tidak ditemukan!');</script>";
         echo "<script language='javascript'>document.location.href='view_expec.php' </script>";
		 }
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){
		$buss_date=$data['buss_date'];
		$nama_jenis=$data['nama_jenis'];
		$nominal=$data['nominal'];
		$margin_bagi=$data['margin_bagi'];
		$rasio_invest=$data['rasio_invest'];
		$nomi_invest=$data['nomi_invest'];
		$eq=$data['eq'];
		$sama=$eq * 100;
		$rasio_cor=$data['rasio_cor'];
		$nomi_cor=$data['nomi_cor'];
		

		?>
 <tr>
    
	<td class="style6"><?php echo tgl_indo ($data['buss_date']);  ?></td>
	<td class="style6"><?php echo $data['nama_jenis'];?></td>
	<td class="style6" align ="Right"><?php echo number_format (compat_num($data['nominal'])); ?></td>
	<td class="style6"align="center"><?php echo $data['rasio_invest'];?></td>
	<td class="style6" align ="Right"><?php echo number_format (compat_num($data['nomi_invest'])); ?></td>
	<td class="style6" align ="center"> <?php echo number_format (compat_num($sama),2); ?> </td>
	<td class="style6"align="center"><?php echo $rasio_cor;?></td>
	<td class="style6" align ="Right"><?php echo number_format (compat_num($nomi_cor)); ?></td>
	
  </tr>
  <?php
		
	}
	}

	
?>

</table>
<br>
<strong>ekspektasi  Nisbah</strong> : <span class="style6"><?php echo $expt;?></span><strong> %</strong>
<iframe width=174 height=189 name="gToday:normal:../../calender/normal.js" id="gToday:normal:../../calender/normal.js" src="../../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
<table width="100%" class="table1">
  <tr class="table1">
    <th width="157" rowspan="2" class="style3">Periode</th>
    <th width="294" rowspan="2" class="style3">Dana</th>
    <th width="171" rowspan="2"  class="style3">Margin Murabahah</th>
    <th colspan="3" class="style3">INVESTOR PORTION</th>
    <th colspan="3" class="style3">EKSPEKTASI </th>
  </tr>
  <tr class="table1">
	<th width="78" class="style3">Ratio</th>
	<th width="109"  class="style3">Nominal</th>
	<th width="64" class="style3">Eq in % </th>
	<th width="85" class="style3">Eq in % </th>
    <th width="107" class="style3">Nominal</th>
    <th width="109" class="style3">Selisih</th>
  </tr>
  
  <?php
  
  $query_jml_aset=mysql_fetch_array(mysql_query("select nomi_murab,marigin_murab,DATEDIFF(sd,dari) as hari from param_finan where buss_date='$tanggal'"));
	$tot_fin=$query_jml_aset['nomi_murab'];
	$margin=$query_jml_aset['marigin_murab'];
	$hari=$query_jml_aset['hari'];

	$pilih = mysql_query ("SELECT code_bulan, nama_jenis, nominal,margin_bagi,rasio_invest, nomi_invest,eq,rasio_cor,nomi_cor,buss_date
FROM param_rasio inner join jenis_dana on jenis_dana.code_jenis = param_rasio.code_jenis
WHERE buss_date ='$tanggal' and  param_rasio.code_jenis='DP' order by rasio_invest Desc ");
		
	//menampilkan data
	while($data = mysql_fetch_array($pilih)){
		$buss_date=$data['buss_date'];
		$nama_jenis=$data['nama_jenis'];
		$nominal=$data['nominal'];
		$margin_bagi=$data['margin_bagi'];
		$rasio_invest=$data['rasio_invest'];
		$nomi_invest=$data['nomi_invest'];
		$eq=$data['eq'];
		$sama=$eq * 100;
		$rasio_cor=$data['rasio_cor'];
		$nomi_cor=$data['nomi_cor'];
		
		if ($rasio_invest=="20"){
		$expt=$_POST['expt_20'];
		}elseif ($rasio_invest=="19"){
		$expt=$_POST['expt_19'];
		}elseif ($rasio_invest=="18"){
		$expt=$_POST['expt_18'];
		}elseif ($rasio_invest=="17"){
		$expt=$_POST['expt_17'];
		}elseif ($rasio_invest=-"16"){
		$expt=$_POST['expt_16'];
		}elseif ($rasio_invest=="15"){
		$expt=$_POST['expt_15'];
		}elseif($rasio_invest=="14"){
		$expt=$_POST['expt_14'];
		}
		
		$harapan=($expt/100*$nominal*$hari/365);
		$new_rasio=($harapan/$margin_bagi)*100;
		$selisih =$nomi_invest - $harapan;

		?>
 <tr>
    
	<td class="style6"><?php echo tgl_indo ($data['buss_date']);  ?></td>
	<td class="style6"><?php echo $data['nama_jenis'];?></td>
	<td class="style6" align ="Right"><?php echo number_format (compat_num($data['nominal'])); ?></td>
	<td class="style6"align="center"><?php echo $data['rasio_invest'];?></td>
	<td class="style6" align ="Right"><?php echo number_format (compat_num($nomi_invest)); ?></td>
	<td class="style6" align ="center"> <?php echo number_format (compat_num($sama),2); ?> </td>
	<td class="style6"align="center"><?php echo number_format (compat_num($expt));?></td>
	<td class="style6" align ="Right"><?php echo number_format (compat_num($harapan));?></td>
	<td class="style6" align ="Right"><?php echo number_format (compat_num($selisih)); ?></td>
  </tr>
  <?php
		
	} 
	

	
?>
 </table>
</body>
</html>
