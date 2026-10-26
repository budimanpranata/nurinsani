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
    <td width="203" height="10" nowrap bgcolor="#B3B3FF"><div align="left" class="style5 style6">Nama /CIF </div></td>
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
$data = mysql_query ("select kode_branch from user where userid ='$userid'");
$r = mysql_fetch_array($data);
$branch=$r['kode_branch'];
$nama_brnach = mysql_query ("select * from branch where kode_branch ='$branch'");
$r = mysql_fetch_array($nama_brnach);
$nama_unit=$r['unit'];
$alamat=$r['alamat'];

$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$kode_branch=$dsmo['kode_branch'];



$datacif = mysql_query ("select cif from anggota where norek ='$norek'");
$d = mysql_fetch_array($datacif);
$cif=$d['cif'];
$data_loan = mysql_query ("select * from loan where cif ='$cif'");
while($data_l= mysql_fetch_array($data_loan)){
$os=number_format(compat_num($data_l['os']));
$plafond=number_format(compat_num($data_l['Plafond']));
$angsuran=$data_l['angsuran'];
$pokok=number_format(compat_num($data_l['pokok']));
$margin=number_format(compat_num($data_l['ijaroh']));
$tgl_akad=tgl_indo($data_l['tgl_akad']);
$tenor=$data_l['Tenor'];
$CUST_SHORT_NAME=$data_l['Cust_Short_name'];
$maturity_date=tgl_indo($data_l['maturity_date']);
}


$datapokok = mysql_query ("select SUM(KREDIT - DEBIT) as spokok from simpanan_pokok where cif='$cif'");
$sp = mysql_fetch_array($datapokok);
$spokok=number_format(compat_num($sp['spokok']));

$datawajib = mysql_query ("select SUM(KREDIT - DEBIT) as swajib from simpanan_wajib where cif='$cif'");
$sw = mysql_fetch_array($datawajib);
$swajib=number_format(compat_num($sw['swajib']));

$datas = mysql_query ("select SUM(KREDIT - DEBIT) as saldo from simpanan where cif='$cif' and type not in('04','05')");
$s = mysql_fetch_array($datas);
$simpanan=number_format(compat_num($s['saldo']));

If (isset($_POST ['search']))
{
require ('fpdf17/fpdf.php');
if (empty($norek)){
echo "<script>alert('Maaf,.isi Nomor REKENING !');</script>";
echo "<script language='javascript'>document.location.href='kartu_angsuran.php'</script>";}
else{

$sql3 =mysql_query ("select CUST_SHORT_NAME,id_pinjam,bulat,angsuran_pokok,bunga,tgl_jatuh_tempo,tgl_bayar,cicilan from pinjaman_detail
inner join loan on loan.no_anggota=pinjaman_detail.id_pinjam
where loan.cif='$cif' and tenor <=tenor order by cicilan");
//$pdf=new FPDF('P','mm', array(216, 275));
$pdf=new FPDF('P','mm','A4');
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetFont('Arial','B',8);
$pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',8);
$pdf->multicell(0,5,'DAFTAR KARTU ANGSURAN DAN TABUNGAN',0,'J',1);
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->cell(70,5,'Nomor Rekenig',0,'R',1);
$pdf->cell(1,5,':',0,'L',1);
$pdf->cell(9,5,'',0,'L',1);
$pdf->cell(15,5,''.$norek,0,'J',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Nama ',0,'L',1);
$pdf->cell(1,5,':',0,'L',1);
$pdf->cell(9,5,'',0,'L',1);
$pdf->cell(15,5,''.$CUST_SHORT_NAME,0,'L',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Plafond Pembiayaan',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$plafond,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Tanggal Realisasi ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$tgl_akad,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Janga Waktu ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$minggu=' Minggu';
$pdf->cell(15,5,''.$tenor .$minggu,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Tanggal Jatuh Tempo ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$maturity_date,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Simpanan Pokok ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$spokok,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Simpanan Wajib ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$swajib,0,'R',1);
$pdf->Ln(5);
//$pdf->cell(70,5,'Simpanan Wadiah ',0,'R',1);
//$pdf->cell(1,5,':',0,'R',1);
//$pdf->cell(9,5,'',0,'R',1);
//$pdf->cell(15,5,''.$simpanan,0,'R',1);
$pdf->Ln(5);


$pdf->SetFont('ARIAL','B',8);


$pdf->Cell(12,10,'KE',1,0,'C');

$pdf->Cell(26,10,'Tgl Transaksi',1,0,'C');
$pdf->Cell(22,10,'setoran',1,0,'C');
$pdf->Cell(20,10,'Angsuran',1,0,'C');
$pdf->Cell(60,5,'TABUNGAN',1,0,'C');
$pdf->Cell(20,5,'Paraf',1,0,'C');
$pdf->Ln();
$pdf->Cell(30,5,'',0,0,'C');
$pdf->Cell(50,5,'',0,0,'C');
$pdf->Cell(20,5,'Pencairan',1,0,'C');
$pdf->Cell(20,5,'wajib',1,0,'C');
$pdf->Cell(20,5,'Saldo',1,0,'C');
$pdf->Cell(20,5,'Nasabah',1,0,'C');


$pdf->Ln();
$no3 =0;
while($result3= mysql_fetch_array($sql3)){
$no3 ++;
$id_pinjam = $result3['id_pinjam'];
$CUST_SHORT_NAME = $result3['CUST_SHORT_NAME'];
$tgl_jatuh_tempo = $result3['tgl_jatuh_tempo'];
$angsuran_pokok = $result3['angsuran_pokok'];
$bunga = $result3['bunga'];
$jumlah_bayar=$result3['jumlah_bayar'];
$tgl_bayar = $result3['tgl_bayar'];
$cicilan = $result3['cicilan'];
$bulat= $result3['bulat'];
$angsuran=$angsuran_pokok  + $bunga;
$mingguan=$bulat-$angsuran-2500;

//query dan arraying
$pdf->SetFont('Arial','',8);
$pdf->Cell(12,5,''.$cicilan,1,'L',1);
$pdf->Cell(26,5,''.tgl_indo($tgl_jatuh_tempo),1,'L',1);
$pdf->Cell(22,5,''.number_format (compat_num($bulat)),1,'LR',0,'C');
$pdf->Cell(20,5,''.number_format (compat_num($angsuran)),1,'LR',0,'C');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Cell(20,5,''.number_format (compat_num($mingguan)),1,'LR',0,'R');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Cell(20,5,'',1,'LR',0,'R');
$pdf->Ln();
}   
$pdf->Output();
}

}
?>
<iframe width=174 height=189 name="gToday:normal:calender/normal.js" id="gToday:normal:calender/normal.js" src="calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
