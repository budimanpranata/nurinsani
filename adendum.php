<?php require_once __DIR__ . '/php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

?>	
	<script type="text/javascript" src="jquery.js"></script>
	<script>
	function suggest(inputString){
		if(inputString.length == 0) {
			$('#suggestions').fadeOut();
		} else {
		$('#country').addClass('load');
			$.post("autosuggest.php", {queryString: ""+inputString+""}, function(data){
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
		$('#kode').val(thisValue);
		setTimeout("$('#suggestions').fadeOut();", 100);
	}
	
	</script>
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
    <td height="10" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Cetak Adendum Murabahah
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style0">Kriteria Pencarian </div></td>
  </tr>
<tr>
 <div id="suggest">
    <td width="237" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">Nomor Kelompok  </div></td>
    <td width="10" nowrap>   <div align="center"><strong>:</strong></div></td>
    <td colspan="2" nowrap><input type="text" onKeyUp="suggest(this.value);" name="kelompok"  onBlur="fill2();" id="kode" size="15"/> 
	<div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
	<div class="suggestionList" id="suggestionsList"> </div>
</div>
	
	 </div>
	  </td>
	</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style7">Nama Kelompok</div></td>
  <td nowrap>
    <div align="center"><strong>:      </strong></div></td>
  <td colspan="2" nowrap><input name="nama" type="text" id="country" value="" size="40"/>    </td>
</tr>
  <tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Adendum </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" id="select" size="1">
    <option> </option>
    <option value="REST">Restrukturisasi</option>
    <option value="JATPO">Restrukturisasi Angsuran </option>
    <option value="REST_POKOK_MARGIN">Restrukturisasi POKOK MARGIN </option>

	<option value="COVID">Covid </option>
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

<table width="100%" class="table1">
  <tr class="table1">
    <th width="45">Unit</th>
    <th width="100">Kelompok</th>

	<th width="100">Moa</th>
    <th width="200">Nama Ao</th>
    <th width="130">Jatpo</th>
      <th width="75">Proses</th>
  </tr>
  <?php
set_time_limit(0);
  include "conn2.php";
  $koneksi=open_connection2();
  $kelompok=$_POST['kelompok'];
  $Nama=$_POST['nama'];
  $tanggl=$_POST ['tanggal'];
  $jenis=$_POST ['select'];
  
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];

$data_hari = mysql_query ("select hari,no_anggota,cao from loan where code_kel ='$kelompok'");
$h = mysql_fetch_array($data_hari);
$hari=$h['hari'];
$no_anggota=$h['no_anggota'];
$cao=$h['cao'];

  If ($_POST ['search'])
{
if (empty($kelompok)||empty($jenis)){
	echo "<script>alert('Maaf,isi kode kelompok dan Jenis cetak');</script>";
         echo "<script language='javascript'>document.location.href='adendum.php'</script>";}
	else{
				
		
	$select = mysql_query ("select count(no_anggota)as noa,unit,kelompok.nama_kel,loan.cao,tgl_wakalah from loan 
	inner join kelompok on kelompok.code_kel = loan.code_kel
	left join rest_covid on loan.cif = rest_covid.cif
	where loan.code_kel ='$kelompok' AND loan.unit='$branch' AND status_app ='$jenis'
	
	group by unit
	
	");
	
			if($select){
		$update_matur= mysql_query("SELECT cif,tenor_baru,pokok_baru,ijaroh_baru from rest_covid where code_kel ='$kelompok'");
        while($data_matur = mysql_fetch_array($update_matur)){
        	$cif =$data_matur['cif'];
		$ft =$data_matur['tenor_baru'];
		$pokok =$data_matur['pokok_baru'];
		$ijaroh =$data_matur['ijaroh_baru'];
			
			
		
			$tgl_m    = strtotime($tgl_system);
			$maturity_date_ress =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));
		
			$cek_tanggal= mysql_fetch_array(mysql_query("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between '$tgl_system' and '$maturity_date_ress' and Hari ='$hari'"));
			$libur =$cek_tanggal['libur'];
            
		
				if($libur>0){
				$ft =$ft+$libur;
				$tgl_m    = strtotime($tgl_system);
				$maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));
				}else{
				$maturity_date_res=$maturity_date_ress;
				}
				
			mysql_query ("update loan set maturity_date='$maturity_date_res' where cif ='$cif'");
			mysql_query ("update rest_covid set jatpo_baru='$maturity_date_res' where cif ='$cif'");
			mysql_query ("delete from pinjaman_detail where cif ='$cif'");
			mysql_query ("delete from pinjaman_detail_covid where cif ='$cif'");

		$awal_hari = $tgl_system;
                $akhir_hari = $maturity_date_res;
                $x1=0;
                while (strtotime($awal_hari) < strtotime($akhir_hari)){
                $x1 ++;
                $reff="$no_anggota$x1";
                $awal_hari= date ("Y-m-d", strtotime("+7 DAY", strtotime($awal_hari)));
                $update= mysql_query("INSERT INTO pinjaman_detail_covid (id,id_pinjam,cicilan,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar,ket,cif,unit,ao)
                values('$reff','$no_anggota','$x1','$pokok','$ijaroh','$awal_hari','$awal','0','','$cif','$unit','$cao')");
                           
                }
	
		}
	}
	if(mysql_num_rows($select)==0){
			
			echo "<script>alert ('Maaf...,Kelompok $nopen tidak ditemukan!!!'); </script>";
         echo "<script language='javascript'>document.location.href='adendum.php' </script>";
		 }
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
	<td class="style6"><?php echo $data['nama_kel'];  ?></td>
	<td class="style6"><?php echo $data['noa']; ?></td>
    <td class="style6"><?php echo $data['cao']; ?></td>
	<td class="style6"><?php echo $data['tgl_wakalah'];?></td>
	
     <td><div align="center"><a href="cetak_adendum.php?jenis=<?php echo $jenis;?>&code_kel=<?php echo $_POST['kelompok'];?>" class="style6" onClick="return confirm('anda yakin akan Cetak Murabahah ?')"><strong>Detail</strong></a></div></td>
  </tr>
  <?php
		
	}
	}
	?>
  
</table>
<p>&nbsp;</p>
</body>
</html>
