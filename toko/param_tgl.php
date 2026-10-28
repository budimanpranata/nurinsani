<?php require_once __DIR__ . '/../php8_compat.php';
session_start();
$userid=$_SESSION['userid'];

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
    <td height="10" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style9">Parameter Tanggal Transaksi
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Parameter Tanggal Transaksi </div></td>
  </tr>

<tr>
 <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Paramter Tgl Transaksi</span></td>
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
    <th width="45">Cabang </th>
    <th width="130">Param Tgl Lama</th>
	<th width="130">Param Tgl Baru</th>
	<th width="130">Aksi</th>
    
  </tr>
  <?php
  include "conn2.php";
  $koneksi=open_connection2();
   $tanggal=$_POST ['tanggal'];
  

  If ($_POST ['search'])
{
if (empty($tanggal)){
	echo "<script>alert('Maaf,.Mohon isi tanggal transaksi !!!');</script>";
         echo "<script language='javascript'>document.location.href='param_tgl.php'</script>";}
	else{
	$_SESSION['stgl_system']=$tanggal;
	}	
	$param_tgl=strtotime($param_tgl);
	$tanggal=strtotime($tanggal);
	var_dump($param_tgl);
var_dump($tanggal);

	if ($tanggal < $param_tgl){
		echo "<script>alert('Maaf,.trasaksi tidak bisak back date ');</script>";
         echo "<script language='javascript'>document.location.href='param_tgl.php'</script>";}
	else {
		
	
  ?>
  
  <?php
	
	
		?>
  <tr>
    <td class="style6"><?php echo $userid; ?></td>
	
	<td class="style6"><?php echo $param_tgl;  ?></td>
	<td class="style6"><?php echo $_SESSION['stgl_system'];  ?></td>
	<td><div align="center"><a href="param_yrt_proses.php?area=<?php echo $branch;?>,tgl=<?php echo $tanggal;?>" class="style6" onClick="return confirm('Apakah adan Yakin ?')"><strong>Proses..</strong></a></div></td>
	
	
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
