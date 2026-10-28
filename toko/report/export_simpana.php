<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$bulan=$_SESSION['bulan'];
$tahun=$_SESSION['tahun'];
$status=$_SESSION['status'];
$unit=$_SESSION['unt'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:../index.html");
 exit; }
require_once('../excel/Worksheet.php');
require_once('../excel/Workbook.php');
include "../indo_tgl.php";
include "../conn2.php";
$koneksi=open_connection2();
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
HeaderingExcel('rekap_nominative_simpanan.xls');

// membuat workbook baru
$workbook = new Workbook("");
// membuat worksheet ke-1 (data Mutliguna)
$worksheet1 =& $workbook->add_worksheet('Simpanan');
// setting format header tabel data
$format =& $workbook->add_format();
$format->set_align('vcenter');
$format->set_align('center');
$format->set_color('white');
$format->set_bold();
$format->set_pattern();
$format->set_fg_color('green');

// membuat header tabel dengan format
//$worksheet1->set_column(0, 0, 20);
//$worksheet1->write_string(0, 0, "KOPERASI JASA KEUANGAN SYARIAH NUR INSANI CABANG .$unit");

$worksheet1->set_row(0, 20);
$worksheet1->set_column(0, 0, 30);
$worksheet1->write_string(0, 0, "UNIT", $format);
$worksheet1->set_column(0, 1, 30);
$worksheet1->write_string(0, 1, "NO. REKENING", $format);
$worksheet1->set_column(0, 2, 45);
$worksheet1->write_string(0, 2, "NAMA", $format);
$worksheet1->set_column(0, 3, 30);
$worksheet1->write_string(0, 3, "ALAMAT", $format);
$worksheet1->set_column(0, 4, 20);
$worksheet1->write_string(0, 4, "SALDO AWAL", $format);
$worksheet1->set_column(0, 5, 10);
$worksheet1->write_string(0, 5, "MUTASI DEBET", $format);
$worksheet1->set_column(0, 6, 15);
$worksheet1->write_string(0, 6, "MUTASI KREDIT", $format);
$worksheet1->set_column(0, 7, 20);
$worksheet1->write_string(0, 7, "SALDO AKHIR", $format);




if ($status ='CURRENT'){

//$qdate="select LAST_DAY(MAX(BUSS_DATE)) as ddata from simpanan where buss_date<> '0000-00-00' group by buss_date";
$qdate="select LAST_DAY(MAX(BUSS_DATE)) - INTERVAL 1 MONTH as ddata from simpanan "; 
$dhasil = mysql_query($qdate);
$baris = 1;
while ($ddata = mysql_fetch_array($dhasil))
{
$dat=$ddata['ddata'];	
}
	
//$qdate1="select LAST_DAY('$dataa') as ddata "; 
//$dhasil1 = mysql_query($qdate1);
//while ($ddata1 = mysql_fetch_array($dhasil1))
//{
//$dat=$ddata1['ddata'];	
//}
$dat='2018-02-28';
$query = "SELECT
simpanan.UNIT as unit,
CONCAT(simpanan.UNIT,'-',CIF,'-',CUST_SHORT_NAME) as rek,
anggota.CUST_SHORT_NAME,
anggota.HADDRESS1,  
SUM(if(date(simpanan.BUSS_DATE)<='$dat',kredit,0))-SUM(if(date(simpanan.BUSS_DATE)<='$dat',DEBIT,0)) AS saldo_awal,
SUM(if(date(simpanan.BUSS_DATE)>'$dat',kredit,0)) AS mutasi_kredit,
SUM(if(date(simpanan.BUSS_DATE)>'$dat',DEBIT,0)) mutasi_debet,
(SUM(if(date(simpanan.BUSS_DATE)<='$dat',kredit,0))-SUM(if(date(simpanan.BUSS_DATE)<='$dat',DEBIT,0)) 
+ SUM(if(date(simpanan.BUSS_DATE)>'$dat',kredit,0)))- SUM(if(date(simpanan.BUSS_DATE)>'$dat',DEBIT,0)) as saldo_akhir
FROM simpanan
LEFT JOIN anggota USING(CIF)
where simpanan.unit='$unit' and TYPE not in ('04','05') group By simpanan.CIF order by CUST_SHORT_NAME ";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet1->write_string($baris, 0, $data['unit']);
$worksheet1->write_string($baris, 1, $data['rek']);
$worksheet1->write_string($baris, 2, $data['CUST_SHORT_NAME']);
$worksheet1->write_string($baris, 3, $data['HADDRESS1']);
$worksheet1->write_string($baris, 4, $data['saldo_awal']);
$worksheet1->write_string($baris, 5, $data['mutasi_debet']);
$worksheet1->write_string($baris, 6, $data['mutasi_kredit']);
$worksheet1->write_string($baris, 7, $data['saldo_akhir']);

$baris++;
}
}else{
$query = " SELECT * from data_loan 
WHERE  MONTH(data_loan.buss_date)='$bulan' AND YEAR(data_loan.buss_date)='$tahun'";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet1->write_string($baris, 0, $data['unit']);
$worksheet1->write_string($baris, 1, $data['rek']);
$worksheet1->write_string($baris, 2, $data['CUST_SHORT_NAME']);
$worksheet1->write_string($baris, 3, $data['HADDRESS1']);
$worksheet1->write_string($baris, 4, $data['saldo_awal']);
$worksheet1->write_string($baris, 5, $data['mutasi_debet']);
$worksheet1->write_string($baris, 6, $data['mutasi_kredit']);
$worksheet1->write_string($baris, 7, $data['saldo_akhir']);
$baris++;
}	
}
$worksheet2 =& $workbook->add_worksheet('Simpaan Wajib');
//$worksheet2->set_column(0, 0, 20);
//$worksheet2->write_string(0, 0, "KOPERASI JASA KEUANGAN SYARIAH NUR INSANI CABANG .$unit");

$worksheet2->set_row(0, 20);
$worksheet2->set_column(0, 0, 30);
$worksheet2->write_string(0, 0, "UNIT", $format);
$worksheet2->set_column(0, 1, 30);
$worksheet2->write_string(0, 1, "NO. REKENING", $format);
$worksheet2->set_column(0, 2, 45);
$worksheet2->write_string(0, 2, "NAMA", $format);
$worksheet2->set_column(0, 3, 30);
$worksheet2->write_string(0, 3, "ALAMAT", $format);
$worksheet2->set_column(0, 4, 20);
$worksheet2->write_string(0, 4, "SALDO AWAL", $format);
$worksheet2->set_column(0, 5, 10);
$worksheet2->write_string(0, 5, "MUTASI DEBET", $format);
$worksheet2->set_column(0, 6, 15);
$worksheet2->write_string(0, 6, "MUTASI KREDIT", $format);
$worksheet2->set_column(0, 7, 20);
$worksheet2->write_string(0, 7, "SALDO AKHIR", $format);




if ($status ='CURRENT'){

$qdate="select max(MONTH(BUSS_DATE)) as ddata from simpanan"; 
$dhasil = mysql_query($qdate);
$baris = 1;
while ($ddata = mysql_fetch_array($dhasil))
{
$dat=$ddata['ddata'];	
}	
$dat='2018-02-28';
$query = "SELECT
simpanan_wajib.UNIT as unit,
CONCAT(simpanan_wajib.UNIT,'-',CIF,'-',CUST_SHORT_NAME) as rek,
anggota.CUST_SHORT_NAME,
anggota.HADDRESS1,  
SUM(if(MONTH(simpanan_wajib.BUSS_DATE)<='$dat',kredit,0))-SUM(if(MONTH(simpanan_wajib.BUSS_DATE)<='$dat',DEBIT,0)) AS saldo_awal,
SUM(if(MONTH(simpanan_wajib.BUSS_DATE)>'$dat',kredit,0)) AS mutasi_kredit,
SUM(if(MONTH(simpanan_wajib.BUSS_DATE)>'$dat',DEBIT,0)) mutasi_debet,
(SUM(if(MONTH(simpanan_wajib.BUSS_DATE)<='$dat',kredit,0))-SUM(if(MONTH(simpanan_wajib.BUSS_DATE)<='$dat',DEBIT,0)) + SUM(if(MONTH(simpanan_wajib.BUSS_DATE)>'$dat',kredit,0)))- SUM(if(MONTH(simpanan_wajib.BUSS_DATE)>'$dat',DEBIT,0)) as saldo_akhir
FROM simpanan_wajib
LEFT JOIN anggota USING(cif)
where simpanan_wajib.unit='$unit' group By simpanan_wajib.cif ";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet2->write_string($baris, 0, $data['unit']);
$worksheet2->write_string($baris, 1, $data['rek']);
$worksheet2->write_string($baris, 2, $data['CUST_SHORT_NAME']);
$worksheet2->write_string($baris, 3, $data['HADDRESS1']);
$worksheet2->write_string($baris, 4, $data['saldo_awal']);
$worksheet2->write_string($baris, 5, $data['mutasi_debet']);
$worksheet2->write_string($baris, 6, $data['mutasi_kredit']);
$worksheet2->write_string($baris, 7, $data['saldo_akhir']);

$baris++;
}
}else{
$query = " SELECT * from data_loan 
WHERE  MONTH(data_loan.buss_date)='$bulan' AND YEAR(data_loan.buss_date)='$tahun'";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet2->write_string($baris, 0, $data['unit']);
$worksheet2->write_string($baris, 1, $data['rek']);
$worksheet2->write_string($baris, 2, $data['CUST_SHORT_NAME']);
$worksheet2->write_string($baris, 3, $data['HADDRESS1']);
$worksheet2->write_string($baris, 4, $data['saldo_awal']);
$worksheet2->write_string($baris, 5, $data['mutasi_debet']);
$worksheet2->write_string($baris, 6, $data['mutasi_kredit']);
$worksheet2->write_string($baris, 7, $data['saldo_akhir']);
$baris++;
}	
}
$worksheet3 =& $workbook->add_worksheet('Simpaan Pokok');
//$worksheet3->set_column(0, 0, 20);
//$worksheet3->write_string(0, 0, "KOPERASI JASA KEUANGAN SYARIAH NUR INSANI CABANG .$unit");

$worksheet3->set_row(0, 20);
$worksheet3->set_column(0, 0, 30);
$worksheet3->write_string(0, 0, "UNIT", $format);
$worksheet3->set_column(0, 1, 30);
$worksheet3->write_string(0, 1, "NO. REKENING", $format);
$worksheet3->set_column(0, 2, 45);
$worksheet3->write_string(0, 2, "NAMA", $format);
$worksheet3->set_column(0, 3, 30);
$worksheet3->write_string(0, 3, "ALAMAT", $format);
$worksheet3->set_column(0, 4, 20);
$worksheet3->write_string(0, 4, "SALDO AWAL", $format);
$worksheet3->set_column(0, 5, 10);
$worksheet3->write_string(0, 5, "MUTASI DEBET", $format);
$worksheet3->set_column(0, 6, 15);
$worksheet3->write_string(0, 6, "MUTASI KREDIT", $format);
$worksheet3->set_column(0, 7, 20);
$worksheet3->write_string(0, 7, "SALDO AKHIR", $format);




if ($status ='CURRENT'){

$qdate="select max(MONTH(BUSS_DATE)) as ddata from simpanan"; 
$dhasil = mysql_query($qdate);
$baris = 1;
while ($ddata = mysql_fetch_array($dhasil))
{
$dat=$ddata['ddata'];	
}	
$dat='2018-02-28';
$query = "SELECT
simpanan_pokok.UNIT as unit,
CONCAT(simpanan_pokok.UNIT,'-',CIF,'-',CUST_SHORT_NAME) as rek,
anggota.CUST_SHORT_NAME,
anggota.HADDRESS1,  
SUM(if(MONTH(simpanan_pokok.BUSS_DATE)<='$dat',kredit,0))-SUM(if(MONTH(simpanan_pokok.BUSS_DATE)<='$dat',DEBIT,0)) AS saldo_awal,
SUM(if(MONTH(simpanan_pokok.BUSS_DATE)>'$dat',kredit,0)) AS mutasi_kredit,
SUM(if(MONTH(simpanan_pokok.BUSS_DATE)>'$dat',DEBIT,0)) mutasi_debet,
(SUM(if(MONTH(simpanan_pokok.BUSS_DATE)<='$dat',kredit,0))-SUM(if(MONTH(simpanan_pokok.BUSS_DATE)<='$dat',DEBIT,0)) + SUM(if(MONTH(simpanan_pokok.BUSS_DATE)>'$dat',kredit,0)))- SUM(if(MONTH(simpanan_pokok.BUSS_DATE)>'$dat',DEBIT,0)) as saldo_akhir
FROM simpanan_pokok
LEFT JOIN anggota USING(cif)
where simpanan_pokok.unit='$unit' group By simpanan_pokok.cif ";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet3->write_string($baris, 0, $data['unit']);
$worksheet3->write_string($baris, 1, $data['rek']);
$worksheet3->write_string($baris, 2, $data['CUST_SHORT_NAME']);
$worksheet3->write_string($baris, 3, $data['HADDRESS1']);
$worksheet3->write_string($baris, 4, $data['saldo_awal']);
$worksheet3->write_string($baris, 5, $data['mutasi_debet']);
$worksheet3->write_string($baris, 6, $data['mutasi_kredit']);
$worksheet3->write_string($baris, 7, $data['saldo_akhir']);

$baris++;
}
}else{
$query = " SELECT * from data_loan 
WHERE  MONTH(data_loan.buss_date)='$bulan' AND YEAR(data_loan.buss_date)='$tahun'";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet3->write_string($baris, 0, $data['unit']);
$worksheet3->write_string($baris, 1, $data['rek']);
$worksheet3->write_string($baris, 2, $data['CUST_SHORT_NAME']);
$worksheet3->write_string($baris, 3, $data['HADDRESS1']);
$worksheet3->write_string($baris, 4, $data['saldo_awal']);
$worksheet3->write_string($baris, 5, $data['mutasi_debet']);
$worksheet3->write_string($baris, 6, $data['mutasi_kredit']);
$worksheet3->write_string($baris, 7, $data['saldo_akhir']);
$baris++;
}	
}
$workbook->close();
?>
