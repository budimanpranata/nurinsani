<?php require_once __DIR__ . '/php8_compat.php';
session_start();
$userid=$_SESSION['userid'];

//cek apakah user sudah login
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
		left: 550px;
		top:60px;
		margin: 26px 0px 0px 0px;
		width: 200px;
		padding:0px;
		background-color:#999999;
		border-top: 3px solid #999999;
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
    <td height="10" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style9">Realisasi Wakalah  <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
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
    <th width="45">UNIT</th>
    <th width="100">NAMA KELOMPOK</th>

	<th width="100">TOTAL NOA</th>
    <th width="200">NAMA AO</th>
    <th width="130">TANGGAL JATUH TEMPO</th>
      <th width="75">Detail</th>
  </tr>
  <?php
  include "conn2.php";
  $koneksi=open_connection2();
  $kelompok=$_POST['kelompok'];
  $Nama=$_POST['nama'];
  $tanggl=$_POST ['tanggal'];
  
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];
  If ($_POST ['search'])
{
if (empty($kelompok)){
	echo "<script>alert('Maaf,.Mohon Nomor Kelompok Yang Benar !!!');</script>";
         echo "<script language='javascript'>document.location.href='realisasi_wakalah.php'</script>";}
	else{
	$select = mysql_query ("select count(no_anggota)as noa,unit,kelompok.nama_kel,calon_loan.cao,tgl_wakalah from calon_loan 
	inner join kelompok on kelompok.code_kel = calon_loan.code_kel
	where calon_loan.code_kel ='$kelompok' AND calon_loan.unit='$branch' AND status_app='APPROVE'
	group by unit
	");
		}

	if(mysql_num_rows($select)==0){
			
			echo "<script>alert ('Maaf...,Kelompok $nopen sudah dilakukan realisasi!!!'); </script>";
         echo "<script language='javascript'>document.location.href='realisasi_wakalah.php' </script>";
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
	
     <td><div align="center"><a href="yra_list_wakalah.php?code_kel=<?php echo $_POST['kelompok'];?>" class="style6" onClick="return confirm('Apakah adan Yakin akan melakukan Realisasis Wakalah ?')"><strong>Detail</strong></a></div></td>
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
