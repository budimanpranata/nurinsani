<?php require_once __DIR__ . '/../php8_compat.php';

session_start();
ob_start();
include "../conn2.php";
$koneksi=open_connection2();
$userid=$_SESSION['userid'];
$bulan=$_SESSION['bulan'];
$tahun=$_SESSION['tahun'];
$npl=$_SESSION['jenis_npl'];

//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

$sql1=mysql_query("Select kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$unit=$dsmo['kode_branch'];
//cek level user

if($_SESSION['level']!="unit"){
   header("location:index.html");
}
require_once('../excel/Worksheet.php');
require_once('../excel/Workbook.php');
include "../indo_tgl.php";

// koneksi ke mysql
// function untuk membuat header file excel
function HeaderingExcel($filename) {
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$filename" );
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");
}
// membuat header file excel dan nama filenya
HeaderingExcel('rekap_nominative.xls');
// membuat workbook baru
$workbook = new Workbook("");
// membuat worksheet ke-1 (data Mutliguna)
$worksheet1 =& $workbook->add_worksheet('Nominative');
// setting format header tabel data
$format =& $workbook->add_format();
$format->set_align('vcenter');
$format->set_align('center');
$format->set_color('white');
$format->set_bold();
$format->set_pattern();
$format->set_fg_color('green');
// membuat header tabel dengan format

$worksheet1->write_string(0, 0, "No", $format);
$worksheet1->set_column(0, 1, 30);
$worksheet1->write_string(0, 1, "NO. ANGGOTA", $format);
$worksheet1->set_column(0, 2, 45);
$worksheet1->write_string(0, 2, "NAMA", $format);
$worksheet1->set_column(0, 3, 30);
$worksheet1->write_string(0, 3, "ALAMAT", $format);
$worksheet1->set_column(0, 4, 20);
$worksheet1->write_string(0, 4, "PLAFOND", $format);
$worksheet1->set_column(0, 5, 10);
$worksheet1->write_string(0, 5, "MARGIN", $format);
$worksheet1->set_column(0, 6, 15);
$worksheet1->write_string(0, 6, "TANGGAL JATUH TEMPO", $format);
$worksheet1->set_column(0, 7, 20);
$worksheet1->write_string(0, 7, "JANGKA WAKTU", $format);
$worksheet1->set_column(0, 8, 45);
$worksheet1->write_string(0, 8, "SALDO PINJAMAN", $format);
$worksheet1->set_column(0, 9, 20);
$worksheet1->write_string(0, 9, "PPAP", $format);
$worksheet1->set_column(0, 10, 20);
$worksheet1->write_string(0, 10, "TUNGGAKAN", $format);


if ($npl ='01'){
$query = "SELECT loan.no_anggota,loan.Cust_Short_name,Tenor,HADDRESS1,Plafond as pla,sum(saldo_kredit) - sum(angsuran_pokok) as saldo,maturity_date from loan left join rek_loan on rek_loan.no_anggota=loan.no_anggota inner join anggota on anggota.cif = loan.cif where loan.cif not in (select tunggakan.cif from tunggakan where datediff('$tgl', tgl_tunggak) < 30 AND unit  ='$unit') AND loan.unit ='$unit' group by loan.cif";
$hasil = mysql_query($query);
$baris = 1;
	$no=0;
	$margin=18;
while ($data = mysql_fetch_array($hasil))
{

	$no ++;
	$ppap=$data['saldo']*0.5/100;
$worksheet1->write_string($baris, 0,$no);
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['HADDRESS1']);
$worksheet1->write_string($baris, 4, $data['pla']);
$worksheet1->write_string($baris, 5, $margin);
$worksheet1->write_string($baris, 6, $data['maturity_date']);
$worksheet1->write_string($baris, 7, $data['Tenor']);
$worksheet1->write_string($baris, 8, $data['saldo']);
$worksheet1->write_string($baris, 9, $ppap);
$worksheet1->write_string($baris, 10,$data['nomi'] );


$baris++;
}
}elseif ($npl ='02'){
$query = "SELECT loan.no_anggota,Cust_Short_name,Tenor,HADDRESS1,Plafond as pla,sum(saldo_kredit) - sum(angsuran_pokok) as saldo,maturity_date, sum(nomianl) as nomi from loan left join rek_loan on rek_loan.no_anggota=loan.no_anggota inner join anggota on anggota.cif = loan.cif where cif not in (select cif from tunggakan where datediff('$tgl', tgl_tunggak) between 90 and 120 AND unit  ='$unit') AND loan.unit ='$unit' group by cif";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$no ++;
	$ppap=$data['saldo']*10/100;
$worksheet1->write_string($baris, 0,$no);
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['HADDRESS1']);
$worksheet1->write_string($baris, 4, $data['pla']);
$worksheet1->write_string($baris, 5, $margin);
$worksheet1->write_string($baris, 6, $data['maturity_date']);
$worksheet1->write_string($baris, 7, $data['Tenor']);
$worksheet1->write_string($baris, 8, $data['saldo']);
$worksheet1->write_string($baris, 9, $ppap);
$worksheet1->write_string($baris, 10,$data['nomi']);


$baris++;
}	
}elseif ($npl ='03'){
$query = "SELECT loan.no_anggota,Cust_Short_name,Tenor,HADDRESS1,Plafond as pla,sum(saldo_kredit) - sum(angsuran_pokok) as saldo,maturity_date,sum(nomianl) as nomi  from loan left join rek_loan on rek_loan.no_anggota=loan.no_anggota inner join anggota on anggota.cif = loan.cif where cif not in (select cif from tunggakan where datediff('$tgl', tgl_tunggak) between 90 and 120 AND unit  ='$unit') AND loan.unit ='$unit' group by cif";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$no ++;
	$ppap=$data['saldo']*50/100;
$worksheet1->write_string($baris, 0,$no);
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['HADDRESS1']);
$worksheet1->write_string($baris, 4, $data['pla']);
$worksheet1->write_string($baris, 5, $margin);
$worksheet1->write_string($baris, 6, $data['maturity_date']);
$worksheet1->write_string($baris, 7, $data['Tenor']);
$worksheet1->write_string($baris, 8, $data['saldo']);
$worksheet1->write_string($baris, 9, $ppap);
$worksheet1->write_string($baris, 10,$data['nomi']);


$baris++;
}	
}elseif ($npl ='04'){
$query = "SELECT loan.no_anggota,Cust_Short_name,Tenor,HADDRESS1,Plafond as pla,sum(saldo_kredit) - sum(angsuran_pokok) as saldo, maturity_date, sum(nominal) as nomi from loan left join rek_loan on rek_loan.no_anggota=loan.no_anggota inner join anggota on anggota.cif = loan.cif where cif in (select cif from tunggakan where datediff('$tgl', tgl_tunggak) between 90 and 120 AND unit  ='$unit') AND loan.unit ='$unit' group by cif";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$no ++;
$ppap=$data['saldo']*100/100;
$worksheet1->write_string($baris, 0,$no);
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['HADDRESS1']);
$worksheet1->write_string($baris, 4, $data['pla']);
$worksheet1->write_string($baris, 5, $margin);
$worksheet1->write_string($baris, 6, $data['maturity_date']);
$worksheet1->write_string($baris, 7, $data['Tenor']);
$worksheet1->write_string($baris, 8, $data['saldo']);
$worksheet1->write_string($baris, 9, $ppap);
$worksheet1->write_string($baris, 10,$data['nomi']);


$baris++;
}	
}
$workbook->close();

?>