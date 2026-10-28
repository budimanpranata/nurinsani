<?php require_once __DIR__ . '/php8_compat.php';
session_start();
 include "conn2.php";
 date_default_timezone_set('Asia/Jakarta');
  $koneksi=open_connection2();
 
  $tanggl=$_POST ['tanggal'];
//$buss_date=date("y-m-d");
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");//jika belum login jangan lanjut..
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
     header("location:index.html");//jika bukan user jangan lanjut
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
<title>Data Wakalah</title>
<link rel="stylesheet" href="tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.style3 {	color: #FFFFFF;
	font-size: 14px;
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
	font-size: 14px;
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


<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Cari Data Musyarakah 
      <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form autocomplete="off" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform" >  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
  </tr>
<tr>
 <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Musyarakah</span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="tanggal" type="text" id="tanggal" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tanggal);return true;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
</tr><tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  <td colspan="2"><input name="search" type="submit" id="submit2" onClick="clearform()" value="Search" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table width="100%" class="table1">
  <tr class="table1">
    <th width="74">Kode Unit</th>
	<th width="134">Nama Unit</th>
      <th width="141">Noa</th>
    <th width="162">Total Plafond</th>
	<th width="162">Cetak Approval</th>
	<th width="156">Cetak</th>
	<th width="156">Rekap</th>
  </tr>
  <?php
 $tanggal=$_POST['tanggal'];
  If ($_POST ['search'])
{
if (empty($tanggal)){
	echo "<script>alert('Maaf,isi Kode Kelompok yang benar!!!');</script>";
         echo "<script language='javascript'>document.location.href='musyarokah.php'</script>";}
	else{
	$_SESSION['smrb']=$tanggal;

	$select = mysql_query ("select count(no_anggota) as noa, sum(plafond) as pla,branch.unit as nama_unit,calon_loan.unit,kelompok.nama_kel,calon_loan.code_kel from calon_loan 
 left join kelompok on kelompok.code_kel = calon_loan.code_kel
 left join branch on branch.kode_branch= calon_loan.unit
 where calon_loan.USERID ='$userid' and status_usia <>'YA' AND calon_loan.tgl_akad='$tanggal' AND status_app ='MUSYAROKAH' group by calon_loan.unit,nama_unit");
		
if(mysql_num_rows($select)==0){
			echo "<script>alert('Maaf,Data tidak ditemukan!!!');</script>";
         echo "<script language='javascript'>document.location.href='musyarokah.php' </script>";
		 }

	//menampilkan data
while($data = mysql_fetch_array($select)){
$angka = $data['Plafond'];
$gaji= $data ['Gaji_Bersih'];

//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));

		?>
 <tr>
     <td class="style6"><?php echo $data['unit']; ?></td>
	<td class="style6"><?php echo $data['nama_unit']; ?></td>
	 <td class="style6"><?php echo $data['noa']; ?></td>
	<td class="style6"><?php echo number_format (compat_num($data['pla']))	;?></td>
	<td><div align="center"><a href="cetak_approval2.php?code_kel=<?php echo $data['code_kel'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Cetak Approval ?')"><strong>Cetak</strong></a></div></td>
<td><div align="center"><a href="cetak_musyarakah1.php?code_kel=<?php echo $data['code_kel'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Cetak Musyarakah ?')"><strong>Cetak</strong></a></div></td>
<td><div align="center"><a href="javascript:;" ><img src="excel-icon.jpeg" width="16" height="15" border="0" onClick="window.open('export_mus.php','scrollwindow','top=200,left=300,width=800,height=500');"><strong>  </strong></a></div>

  </tr>
  <?php
		}
	}
	}
else {
 $pilih = mysql_query ("select count(no_anggota) as noa, sum(plafond) as pla,branch.unit as nama_unit,calon_loan.unit,kelompok.nama_kel,calon_loan.code_kel,nama_ao from calon_loan 
 inner join kelompok on kelompok.code_kel = calon_loan.code_kel
 inner join branch on branch.kode_branch= calon_loan.unit
 inner join ao on ao.cao = calon_loan.cao
 where calon_loan.USERID ='$userid' and status_usia <>'YA' AND status_app ='MUSYAROKAH' group by calon_loan.unit,nama_unit");
 
while($data = mysql_fetch_array($pilih)){
//menghitung hitung
//$birthdate = $data['Birth_date'];
//$age = ((time() - strtotime($birthdate))/(60*60*24*365.2425));
?>
  <tr>
    <td class="style6"><?php echo $data['unit']; ?></td>
	<td class="style6"><?php echo $data['nama_unit']; ?></td>
	 <td class="style6"><?php echo $data['noa']; ?></td>
	<td class="style6"><?php echo number_format (compat_num($data['pla']))	;?></td>
	<td><div align="center"><a href="cetak_approval.php?code_kel=<?php echo $data['unit'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Cetak Approval ?')"><strong>Cetak</strong></a></div></td>
<td><div align="center"><a href="cetak_musyarakah1.php?code_kel=<?php echo $data['unit'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Cetak Musyarakah ?')"><strong>Cetak</strong></a></div></td>
<td><div align="center"><a href="javascript:;" ><img src="excel-icon.jpeg" width="16" height="15" border="0" onClick="window.open('export_simpana.php','scrollwindow','top=200,left=300,width=800,height=500');"><strong> </strong></a></div>

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
