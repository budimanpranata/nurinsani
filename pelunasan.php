<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
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

<head>
<title>Database Pensiun</title>
<link rel="stylesheet" href="tabel.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
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


<table width="100%">
  <tr bgcolor="#669900">
  <td height="20" colspan="4" nowrap><div align="left" class="style3 style9">Pelunasan</div></td>
  </tr>
</table>

<table width="428" border="1" align="center">
</table>
  <form method="post" action="<?php require_once __DIR__ . '/php8_compat.php'; echo $_SERVER['PHP_SELF']; ?>" name="postform">  <table width="502" border="0" align="center">
<tr bgcolor="#669900">
  <td height="10" colspan="4" nowrap><div align="center" class="style3 style9">Kriteria Pencarian </div></td>
  </tr>
<tr>
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">NOREK </div></td>
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
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Search" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<table width="100%" class="table1">
  <tr class="table1">
    <th width="70">No Anggota</th>
    <th width="160">Nama</th>
    <th width="70">Plafond</th>
	    <th width="49">Angsuran</th>
    <th width="116">Tgl Jatuh Tempo</th>
	<th width="55">Saldo OS</th>
	<th width="55">Saldo Rek</th>
    <th width="38">Unit </th>
    <th width="40">Action</th>

  </tr>
  <?php
    include "indo_tgl.php";
  include "conn2.php";
  $koneksi=open_connection2();
  $nopen=$_POST ['kode_rekening'];
  If ($_POST ['search'])
{
if (empty($nopen)){
	echo "<script>alert('Maaf,.isi Nomor Anggota yang benar  !!!');</script>";
         echo "<script language='javascript'>document.location.href='pelunasan.php'</script>";}
	
	else{
		$kel = mysql_query ("select CODE_KEL,CIF,UNIT,CUST_SHORT_NAME,CAO FROM anggota where  norek ='$nopen'");
$kkel = mysql_fetch_array($kel);
$code_kel = $kkel['CODE_KEL'];
$ciff = $kkel['CIF'];
$unit=$kkel['UNIT'];
$nama=mysql_real_escape_string ($kkel['CUST_SHORT_NAME']);
$ds=$kkel['CAO'];
		
		
	$select = mysql_query ("select loan.no_anggota,loan.Cust_Short_name,loan.Plafond,os,maturity_date,branch.unit,angsuran,os,(sum(KREDIT)-sum(DEBIT))as saldo
	from loan
	left join simpanan on simpanan.cif=loan.cif
	left join branch on branch.kode_branch=loan.unit
	 where loan.cif ='$ciff' and os > 0 and simpanan.TYPE not in ('04','05') group by no_anggota");
		}

	if(mysql_num_rows($select)==0){
			echo "<script>alert('Maaf...,data tdak di temukan!!!');</script>";
         echo "<script language='javascript'>document.location.href='pelunasan.php'</script>";
		 }
		
  ?>
  
  <?php
	
	//menampilkan data
	while($data = mysql_fetch_array($select)){

		?>
  <tr>
    <td class="style6"><?php echo $data['no_anggota']; ?></td>
    <td class="style6"><?php echo $data['Cust_Short_name']; ?></td>
	<td class="style6"><?php echo number_format (compat_num($data['Plafond'])); ?></td>
	<td class="style6"><?php echo number_format (compat_num($data['angsuran'])); ?></td>
	<td class="style6"><?php echo tgl_indo ($data['maturity_date']);?></td>
    <td class="style6"><?php echo number_format (compat_num($data['os']));?></td>
    <td class="style6"><?php echo number_format (compat_num($data['saldo']));?></td>
	   <td class="style6"><?php echo $data['unit'];?></td>
    <td><div align="center"><a href="lunasi.php?anggota=<?php echo $data['no_anggota'];?>" class="style6" onClick="return confirm('Apakah Anda yakin akan melakukan Pelunasan?')"><strong>Disburse</strong></a></div></td>

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
