<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
unset($_SESSION['pilih']);
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
			$.post("autosuggesst_kel.php", {queryString: ""+inputString+""}, function(data){
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
</style>
<style type="text/css">
	
.header1 {color: #ffffff; background: #4682B4; font-weight: bold; font-family: Tahoma, Verdana; font-size: 13px; margin: 0px;}
.style1 {
	color: #FFFFFF;
	font-weight: bold;
}
.select-editable {
     position:relative;
     background-color:white;
     border:solid grey 1px;
     width:120px;
     height:18px;
 }
 .select-editable select {
     position:absolute;
     top:0px;
     left:0px;
     font-size:14px;
     border:none;
     width:120px;
     margin:0;
 }
 .select-editable input {
     position:absolute;
     top:0px;
     left:0px;
     width:100px;
     padding:1px;
     font-size:12px;
     border:none;
 }
 .select-editable select:focus, .select-editable input:focus {
     outline:none;
 }

       </style>
</head>

<table width="100%" >
  <tr>
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style9"><span class="style8">Cetak Kartu Angsuran
      </span>
    <div align="right"></div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" name="postform">  <table width="543" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Input Data </div></td>
  </tr>
  <tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Pencarian </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><select name="select" id="select" size="1">
    <option>Pilih Salah satu</option>
    <option value="01">Perorangan</option>
    <option value="02"> Kelompok</option>
    </select></td>
</tr>
<tr>
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">Kode Kelompok/cif </div></td>
    <td width="16" nowrap>        <div align="center"><strong>:</strong></div></td>
    <td width="310" colspan="2" nowrap>
	 <div id="suggest">
					   <input type="text" onKeyUp="suggest(this.value);" name="kode_rekening"  onBlur="fill2();" id="kode" size="15"/> 
					   <div class="suggestionsBox" id="suggestions" style="display: none;"> <img src="arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
					   <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
					   </div>
	    
      <input type="text" disabled="disabled" name="nama_rekening" onBlur="fill();" id="country" />
</div>
</tr>


<tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Cetak" size="40"></td>
</tr>
</table>
</form>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
<table width="100%" class="table1">
  <tr class="table1">
    <th width="45">Unit</th>
    <th width="100"> kelompok</th>

	<th width="100">Total NoA</th>
    <th width="200">Nama AO</th>
	
      <th width="75">Cetak</th>
  </tr>
  <?php
  include "conn2.php";
  $koneksi=open_connection2();
  $kode_rekening=$_POST['kode_rekening'];
  $pilih=$_POST['select'];

  $data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];
  
  

  If ($_POST ['search'])
{
if (empty($kode_rekening)){
	echo "<script>alert('Maaf,Kode kelompok atau CIF salah');</script>";
         echo "<script language='javascript'>document.location.href='kartu_angsuran_kel.php'</script>";}
	else{


		if ($pilih =='01'){

			$select = mysql_query ("select count(no_anggota)as noa,sum(bulat) as tagihan,unit,kelompok.nama_kel,loan.cao,maturity_date from loan 
			left join kelompok on kelompok.code_kel = loan.code_kel
			where loan.cif ='$kode_rekening' AND loan.unit='$branch' and os >0
			group by loan.cif
			");

		}else{
			$select = mysql_query ("select count(no_anggota)as noa,sum(bulat) as tagihan,unit,kelompok.nama_kel,loan.cao,maturity_date from loan 
			left join kelompok on kelompok.code_kel = loan.code_kel
			where loan.code_kel ='$kode_rekening' AND loan.unit='$branch' and os >0
			group by unit
			");


			
		}

		


	if(mysql_num_rows($select)==0){
			
			echo "<script>alert ('Maaf...,Kelompok $nopen tidak ditemukan atau sudah lunas!!!'); </script>";
         echo "<script language='javascript'>document.location.href='kartu_angsuran_kel.php' </script>";
		 }
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){

		?>
  <tr>
    <td class="style6"><?php echo $data['unit']; ?></td>
	<td class="style6"><?php echo $data['nama_kel'];  ?></td>
	<td class="style6"><?php echo $data['noa']; ?></td>
    <td class="style6"><?php echo $data['cao']; ?></td>

	     <td><div align="center"><a href="cetak_kartu_angsuran.php?code_kel=<?php echo $kode_rekening;?>&jenis=<?php echo $pilih;?>"img src="image/r.PNG"  width="20" height="20"  class="style6" onClick="return confirm('Anda akan melakukan Cetak Buku Angsuran ?')"><strong>Cetak</strong></a></div></td>
  </tr>
  <?php
		
}
	}
	}
	else{
$pilih = mysql_query ("select count(no_anggota)as noa,sum(bulat) as tagihan,unit,kelompok.nama_kel,loan.cao,maturity_date,loan.code_kel from loan 
left join kelompok on kelompok.code_kel = loan.code_kel
where loan.status='CETAK' AND loan.unit='$branch' and os >0
group by loan.code_kel
");
		while($data = mysql_fetch_array($pilih)){

		?>
  <tr>
  <td class="style6"><?php echo $data['unit']; ?></td>
	<td class="style6"><?php echo $data['nama_kel'];  ?></td>
	<td class="style6"><?php echo $data['noa']; ?></td>
    <td class="style6"><?php echo $data['cao']; ?></td>
	<td><div align="center"><a href="cetak_kartu_angsuran.php?code_kel=<?php echo $data['code_kel'];?>&jenis=03"img src="image/r.PNG"  width="20" height="20"  class="style6" onClick="return confirm('Anda akan melakukan Realsasi Angsuran ?')"><strong>cetak</strong></a></div></td>
  
  </tr>
  <?php
		
	}
			
	}
	?>
  
</table>
</body>
</html>
