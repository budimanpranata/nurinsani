<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
unset($_SESSION['pilih']);
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
    <td height="20" bordercolor="#FFFFFF" bgcolor="#669900" class="style3 style8"><span class="style8">Rekap Mutasi
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
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">Kode Kelompok </div></td>
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
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Jenis Transaksi </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  
  <td colspan="2" nowrap>
  <div class="select-editable">
  <select onchange="this.nextElementSibling.value=this.value">
    <option>Pilih Salah satu</option>
    <option value="01">Simpanan</option>
    <option value="02">Pembiayaan</option>
	<option value="03">Angsuran</option>
  </select>
  <input type="text" name="select" value=""/> 
  </td>
</tr>
<tr>
  <td height="10" nowrap bgcolor="#B3B3FF"><span class="style7">Tanggal Transaksi </span></td>
  <td nowrap><div align="center"><strong>:</strong></div></td>
  <td colspan="2" nowrap><input name="buss_date" type="text" id="tgl_trn" value="" size="20"/>
    <a href="javascript:void(0)" onClick="if(self.gfPop)gfPop.fPopCalendar(document.postform.tgl_trn);return true;" ><img src="images/calendarIcon.jpg" alt="" name="popcal" width="17" height="17" border="0" align="absmiddle" id="popcal" /></a></td>
</tr>
<tr>
  <td height="10" colspan="4">&nbsp;</td>
  </tr>
<tr bgcolor="#B3B3FF">
  <td height="10">&nbsp; </td>
  <td><div align="center"></div></td>
  
  <td colspan="2"><input name="search" type="submit" id="submit2" value="Cari" size="40"></td>
</tr>
</table>
</form>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
<?php
session_start();
ob_start();
include "conn2.php";
include "function.php";
$koneksi=open_connection2();
$norek=$_POST ['kode_rekening'];
$nominal=$_POST['nama'];
$tanggl=$_POST ['tanggal'];
$pilih=$_POST['select'];
$buss_date=date("Y-m-d H:i:s");
$keterangan=$_POST['ket'];
$tgl_tr=$_POST['buss_date'];
If (isset($_POST ['search']))
{
require ('fpdf17/fpdf.php');
if (empty($norek)||empty($pilih)){
echo "<script>alert('Maaf,.isi kode Kelompok dan Jenis Transaksi yang benar  !!!');</script>";
echo "<script language='javascript'>document.location.href='rek_koran.php'</script>";}
else{
switch($pilih)
{
case "01":
$query1 = mysql_query("SET @bal = 0;");
$sql =mysql_query ("SELECT simpanan.KET, simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=@bal+KREDIT-DEBIT as balance FROM simpanan 
join anggota on anggota.NOREK = simpanan.NOREK
where simpanan.code_kel='$norek' and BUSS_DATE ='$tgl_tr' order by simpanan.BUSS_DATE");
$pdf=new FPDF('P','mm', array(216, 275));
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetFont('Arial','B',8);
$pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',8);
$pdf->multicell(0,5,'DAFTAR REKENING KORAN',0,'J',1);
$pdf->Ln();
$pdf->SetFont('ARIAL','B',7);
$pdf->Cell(8,3,'No',1,0,'C');
$pdf->Cell(25,3,'No.Rek',1,0,'C');
$pdf->Cell(45,3,'Nama Nasabah',1,0,'C');
$pdf->Cell(25,3,'Tanggal',1,0,'C');
$pdf->Cell(45,3,'Keterangan',1,0,'C');
$pdf->Cell(18,3,'Debet',1,0,'C');
$pdf->Cell(15,3,'Kredit',1,0,'C');
$pdf->Cell(15,3,'Saldo',1,0,'C');
$pdf->Ln();
$no =0;
while($result= mysql_fetch_array($sql)){
$no ++;
$norek = $result['NOREK'];
$CUST_SHORT_NAME = $result['CUST_SHORT_NAME'];
$BUSS_DATE = $result['BUSS_DATE'];
$KET = $result['KET'];
$DEBIT = $result['DEBIT'];
$KREDIT = $result['KREDIT'];
$Balance = $result['balance'];
//query dan arraying
$pdf->SetFont('Arial','',7);
$pdf->Cell(8,3,''.$no,1,'L',1);
$pdf->Cell(25,3,''.$norek,1,'L',1);
$pdf->Cell(45,3,''.$CUST_SHORT_NAME,1,'L',1);
$pdf->Cell(25,3,''.$BUSS_DATE,1,'L',1);
$pdf->Cell(45,3,''.$KET,1,'L',1);
$pdf->Cell(18,3,''.number_format (compat_num($DEBIT)),1,'LR',0,'R');
$pdf->Cell(15,3,''.number_format (compat_num($KREDIT)),1,'LR',0,'R');
$pdf->Cell(15,3,''.number_format (compat_num($Balance)),1,'LR',0,'R');
$pdf->Cell(45,3,''.$_SESSION['pilih'],1,'L',1);
$pdf->Ln();
}
$pdf->Output();
break;
case "02":
$sql2 =mysql_query ("select CUST_SHORT_NAME,id_pinjam,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar from pinjaman_detail 
inner join loan on loan.no_anggota=pinjaman_detail.id_pinjam
where id_pinjam='$norek'");
$pdf=new FPDF('P','mm', array(216, 275));
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetFont('Arial','B',8);
$pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',8);
$pdf->multicell(0,5,'DAFTAR REKENING KORAN',0,'J',1);
$pdf->Ln();
$pdf->SetFont('ARIAL','B',7);
$pdf->Cell(8,3,'No',1,0,'C');
$pdf->Cell(25,3,'No.Rek',1,0,'C');
$pdf->Cell(45,3,'Nama Nasabah',1,0,'C');
$pdf->Cell(25,3,'Jatuh Tempo',1,0,'C');
$pdf->Cell(45,3,'Pokok',1,0,'C');
$pdf->Cell(18,3,'Margin',1,0,'C');
$pdf->Cell(15,3,'Tgl Bayar',1,0,'C');
$pdf->Cell(15,3,'KET',1,0,'C');
$pdf->Ln();
$no2 =0;
while($result2= mysql_fetch_array($sql2)){
$no2 ++;
$id_pinjam = $result2['id_pinjam'];
$CUST_SHORT_NAME = $result2['CUST_SHORT_NAME'];
$tgl_jatuh_tempo = $result2['tgl_jatuh_tempo'];
$angsuran_pokok = $result2['angsuran_pokok'];
$bunga = $result2['bunga'];
$tgl_bayar = $result2['tgl_bayar'];
$ket = $result['jumlah_bayar'];
//query dan arraying
$pdf->SetFont('Arial','',7);
$pdf->Cell(8,3,''.$no2,1,'L',1);
$pdf->Cell(25,3,''.$id_pinjam,1,'L',1);
$pdf->Cell(45,3,''.$CUST_SHORT_NAME,1,'L',1);
$pdf->Cell(25,3,''.$tgl_jatuh_tempo,1,'L',1);
$pdf->Cell(45,3,''.$angsuran_pokok,1,'L',1);
$pdf->Cell(18,3,''.number_format (compat_num($bunga)),1,'LR',0,'R');
$pdf->Cell(15,3,''.$tgl_bayar,1,'LR',0,'R');
$pdf->Cell(15,3,''.$ket,1,'LR',0,'R');
$pdf->Ln();
}	
$pdf->Output();
break;
case "03":
$sql3 =mysql_query ("select CUST_SHORT_NAME,id_pinjam,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,jumlah_bayar from pinjaman_detail 
inner join loan on loan.no_anggota=pinjaman_detail.id_pinjam
where id_pinjam='$norek'");
$pdf=new FPDF('P','mm', array(216, 275));
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetFont('Arial','B',8);
$pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',8);
$pdf->multicell(0,5,'DAFTAR REKENING KORAN',0,'J',1);
$pdf->Ln();
$pdf->SetFont('ARIAL','B',7);
$pdf->Cell(8,3,'No',1,0,'C');
$pdf->Cell(25,3,'No.Rek',1,0,'C');
$pdf->Cell(45,3,'Nama Nasabah',1,0,'C');
$pdf->Cell(25,3,'Tanggal',1,0,'C');
$pdf->Cell(45,3,'Cicilan',1,0,'C');
$pdf->Cell(18,3,'Tgl jatuh Tempo',1,0,'C');
$pdf->Cell(15,3,'Tgl Bayar',1,0,'C');
$pdf->Cell(15,3,'Saldo',1,0,'C');
$pdf->Ln();
$no3 =0;
while($result3= mysql_fetch_array($sql3)){
$no3 ++;
$id_pinjam = $result2['id_pinjam'];
$CUST_SHORT_NAME = $result2['CUST_SHORT_NAME'];
$tgl_jatuh_tempo = $result2['tgl_jatuh_tempo'];
$angsuran_pokok = $result2['angsuran_pokok'];
$bunga = $result2['bunga'];
$tgl_bayar = $result2['tgl_bayar'];
$ket = $result['jumlah_bayar'];
//query dan arraying
$pdf->SetFont('Arial','',7);
$pdf->Cell(8,3,''.$no2,1,'L',1);
$pdf->Cell(25,3,''.$id_pinjam,1,'L',1);
$pdf->Cell(45,3,''.$CUST_SHORT_NAME,1,'L',1);
$pdf->Cell(25,3,''.$tgl_jatuh_tempo,1,'L',1);
$pdf->Cell(45,3,''.$angsuran_pokok,1,'L',1);
$pdf->Cell(18,3,''.number_format (compat_num($bunga)),1,'LR',0,'R');
$pdf->Cell(15,3,''.$tgl_bayar,1,'LR',0,'R');
$pdf->Cell(15,3,''.$ket,1,'LR',0,'R');
$pdf->Ln();
}	
$pdf->Output();
break;
default: 
}
}
}
?>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
