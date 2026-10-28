<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
 include "conn2.php";
  include "function.php";
  $koneksi=open_connection2();
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
			$.post("autosuggesst_rek.php", {queryString: ""+inputString+""}, function(data){
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
<script>
	function formatCurrency(num) {
    num = num.toString().replace(/\$|\,/g,'');
	num = num.toString().replace(/[^0-9]/g,'');
    if(isNaN(num))
    num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num*100+0.50000000001);
    cents = num%100;
    num = Math.floor(num/100).toString();
    if(cents<10)
    cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
    num = num.substring(0,num.length-(4*i+3))+'.'+
    num.substring(num.length-(4*i+3));
    return (((sign)?'':'-') + 'Rp. ' + num + '');
    }
	</script>
	
</script>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Transaksi NI</title>
<link rel="stylesheet" href="tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<script type="text/javascript" src="jquery.js"></script>
		<link href="../style.css" rel="stylesheet" type="text/css" media="screen" />
		<link rel="stylesheet" href="../css/bootstrap.min.css"/>
<script>

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
		left: 0px;
		top:40px;
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
		background-color: #FC3;
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
	.style9 {
	font-size: 16px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
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
	.style10 {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
}

    .style11 {font-size: small}
    </style>
</head>

<table width="100%" cellpadding="1" bordercolor="#CCCCCC" bgcolor="#CCCCCC">
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8">Posting Neraca
      <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
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
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Unit </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap> <select name="unit" id ="unit" type="text">
  	  <option value="">- Select Kode unit -</option>
	  <?php
              		$query = "select * from branch where code_area='$userid'";
					$result = mysql_query($query);
					while ($kanwil=mysql_fetch_array($result))
					{
			  		  	echo "<option value=$kanwil[0]>$kanwil[1]</option>";
					}
              		echo"</select>";?>
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

<table border="4" bgcolor="" width="100%" class="table1">
  <tr class="table1">
    <th>Unit</th>
    <th>Tgl Awal Transaksi</th>
	<th>Tgl Akhir Transaksi</th>
	<th>Total Debet</th>
    <th>Totak Kredit</th>
	<th>Detail </th>
  
  </tr>
  <?php
 
  $tanggal=date("Y-m-d");
  $buss_date1=$_POST['buss_date1'];
  $buss_date2=$_POST['buss_date2'];
  $unit=$_POST['unit'];
  

  If (isset($_POST ['search']))
{
if (empty($buss_date1)||empty($buss_date2)){
	echo "<script>alert('Maaf,.isi Tanggal Pencarian  !!!');</script>";
         echo "<script language='javascript'>document.location.href='mutasi_kas.php'</script>";}
	
	else{
		$_SESSION['tgl_awal_pos']=$buss_date1;
		$_SESSION['tgl_akhir_pos']=$buss_date2;
		$_SESSION['unit_lead']=$unit;
		
		 $select = mysql_query("Select sum(debet) as debet,sum(kredit) as kredit from tabel_transaksi where  tanggal_transaksi between '$buss_date1' and '$buss_date2' and unit='$unit'");
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
    <td class="style6"><?php echo $unit; ?></td>
	<td class="style6"><?php echo $buss_date1;  ?></td>
    <td class="style6"><?php echo $buss_date2;?></td>
	<td class="style6"><?php echo number_format (compat_num($data['debet'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['kredit'])); ?></td>
	<td><div align="center"><a href="posting_neraca_bulan.php?unit=<?php echo $unit;?>" class="style6" onClick="return confirm('Apakah adan Yakin akan melakukan Posting Neraca ?')"><strong>Detail</strong></a></div></td>

    
  </tr>
  <?php
		
	}
	}else{
		 $select1 = mysql_query("SET @bal = 0;");
		 $select = mysql_query("Select kode_rekening,DATE_FORMAT(tanggal_transaksi, '%d %M %Y') as tanggal_transaksi,keterangan_transaksi,debet,kredit,@bal:=(@bal+debet) - kredit as saldo from tabel_transaksi where kode_rekening='1120000' and tanggal_transaksi='$tgl_system'and unit='$unit'");
	while($data = mysql_fetch_array($select)){
			
		?>
  <tr>
    <td class="style6"><?php echo $data['kode_rekening']; ?></td>
	<td class="style6"><?php echo $data['keterangan_transaksi'];  ?></td>
    <td class="style6"><?php echo $data['tanggal_transaksi'];?></td>
	<td class="style6"><?php echo number_format (compat_num($data['debet'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['kredit'])); ?></td>
    <td class="style6"><?php echo number_format (compat_num($data['saldo'])); ?></td>
    
  </tr>
  <?php
	}
	}
	?>
  
</table>
<p>&nbsp;</p>

 <iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
<script>
$(document).ready(function(){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
$('#select').change(function(){
if($('#select').val()=='01'){
$('.hasil').hide();	
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='02'){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='03'){
$('.hasil').hide();
$('#tombol').hide();
$('#baki').hide();
$('#simpan').hide();
}else if($('#select').val()=='04'){
$('.hasil').show();
$('#tombol').show();
$('#baki').show();
$('#simpan').show();
}
});
});
</script>
</body>
</html>
