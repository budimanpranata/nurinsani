<?php require_once __DIR__ . '/php8_compat.php';
session_start();
$userid=$_SESSION['userid'];
include "indo_tgl.php";
//cek apakah user sudah login
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
}
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

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
	font-size: 11px;
	font-weight: bold;
}
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 11px; margin: 0px;}
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
	font-size: 12px;
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
		left: 610px;
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
    <td height="10" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style9">Transaksi Restrukturisasi Terdampak Covid19
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform" >  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
  </tr>
<tr>
 <div id="suggest">
    <td width="237" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6"> Kelompok  </div></td>
    <td width="10" nowrap>   <div align="center"><strong>:</strong></div></td>
     <td colspan="2" nowrap><input type="text" onKeyUp="suggest(this.value);" name="kelompok" autocomplete="off" onBlur="fill2();" id="kode" size="15"/> 
	 <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
	 <div class="suggestionList" id="suggestionsList"> </div>
	 </div>
	
	 </div>
	  </td>
	</tr>

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
    <th >Unit</th>
    <th>Kelompok</th>

	<th> Noa</th>
    <th>Nama AO</th>
	<th>Setoran</th>
    <th>Jatpo</th>
	<th >Proses</th>
  </tr>
  <?php
  include "conn2.php";
  $koneksi=open_connection2();
  $kelompok=$_POST['kelompok'];
  $Nama=$_POST['nama'];
  $tanggl=$_POST ['tanggal'];
$buss_date=date("Y-m-d");
  
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];

  If ($_POST ['search'])
{
if (empty($kelompok)){
	echo "<script>alert('Maaf,isi Kode Kelompok Yang Benar!');</script>";
         echo "<script language='javascript'>document.location.href='rest_covid.php'</script>";}
	else{
	$select = mysql_query ("select count(no_anggota)as noa,sum(bulat) as tagihan,unit,kelompok.nama_kel,loan.cao,maturity_date as next_schedule from loan 
	inner join kelompok on kelompok.code_kel = loan.code_kel
	where loan.code_kel ='$kelompok' AND loan.unit='$branch' and os >0
	group by unit order by loan.cao
	");

	if(mysql_num_rows($select)==0){
			
			echo "<script>alert ('Maaf,Kelompok $nopen tidak ditemukan'); </script>";
         echo "<script language='javascript'>document.location.href='rest_covid.php' </script>";
		 }
		
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
	<td class="style6"><?php echo number_format (compat_num($data['tagihan'])); ?></td>
	<td class="style6"><?php echo tgl_indo ($data['next_schedule']);?></td>
	
	     <td><div align="center"><a href="rest_list_covid.php?code_kel=<?php echo $_POST['kelompok'];?>"img src="image/r.PNG"  width="20" height="20"  class="style6" onClick="return confirm('Anda akan melakukan Restrukturisasi Kelompok ?')"><strong>Proses</strong></a></div></td>
		 
  </tr>
  <?php
		
	}
	}
	}
	else{
	
		?>
  <tr>
    
  
  </tr>
  <?php
		
	
			
	}
	?>
  
</table>
<p>&nbsp;</p>
</body>
</html>
