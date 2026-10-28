<?php require_once __DIR__ . '/../php8_compat.php';
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
$worksheet1->set_row(0, 20);
$worksheet1->set_column(0, 0, 30);
$worksheet1->write_string(0, 0, "UNIT", $format);
$worksheet1->set_column(0, 1, 30);
$worksheet1->write_string(0, 1, "NO. REKENING", $format);
$worksheet1->set_column(0, 2, 45);
$worksheet1->write_string(0, 2, "NAMA", $format);
$worksheet1->set_column(0, 3, 30);
$worksheet1->write_string(0, 3, "KELOMPOK", $format);
$worksheet1->set_column(0, 4, 20);
$worksheet1->write_string(0, 4, "TANGGAL PK", $format);
$worksheet1->set_column(0, 5, 10);
$worksheet1->write_string(0, 5, "TENOR", $format);
$worksheet1->set_column(0, 6, 15);
$worksheet1->write_string(0, 6, "JATUH TEMPO", $format);
$worksheet1->set_column(0, 7, 20);
$worksheet1->write_string(0, 7, "MARGIN", $format);
$worksheet1->set_column(0, 8, 45);
$worksheet1->write_string(0, 8, "SEBELUMNYA", $format);
$worksheet1->set_column(0, 9, 20);
$worksheet1->write_string(0, 9, "DISETUJUI", $format);
$worksheet1->set_column(0, 10, 20);
$worksheet1->write_string(0, 10, "SISA PINJAMAN", $format);
$worksheet1->set_column(0, 11, 20);
$worksheet1->write_string(0, 11, "SISA MARGIN", $format);
$worksheet1->set_column(0, 12, 20);
$worksheet1->write_string(0, 12, "AO", $format);


if ($status ='CURRENT'){

$query = "select loan.no_anggota,loan.unit,Cust_Short_name,Plafond,os,code_kel,tenor,run_tenor,Deal_type,tgl_murab,next_schedule,maturity_date,cao ,sum(saldo_kredit) - sum(angsuran_pokok) as saldo from loan

inner join rek_loan on rek_loan.no_anggota = loan.no_anggota where loan.unit ='$unit' group by no_anggota  ";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet1->write_string($baris, 0, $data['unit']);
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['code_kel']);
$worksheet1->write_string($baris, 4, tgl_indo ($data['tgl_murab']));
$worksheet1->write_string($baris, 5, $data['tenor']);
$worksheet1->write_string($baris, 6, tgl_indo ($data['maturity_date']));
$worksheet1->write_string($baris, 7, $data['PLAFOND']);
$worksheet1->write_string($baris, 8, $data['Plafond']);
$worksheet1->write_string($baris, 9, $data['saldo']);
$worksheet1->write_string($baris, 10, $data['pokok']);
$worksheet1->write_string($baris, 11, $data['saldo']);
$worksheet1->write_string($baris, 12, $data['bunga_join']);
$worksheet1->write_string($baris, 13, $data['cao']);
$baris++;
}

}else{
$query = " SELECT * from data_loan 
WHERE  MONTH(data_loan.buss_date)='$bulan' AND YEAR(data_loan.buss_date)='$tahun'";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet1->write_string($baris, 0, tgl_indo ($data['unit']));
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['kel']);
$worksheet1->write_string($baris, 4, $data['tgl_murab']);
$worksheet1->write_string($baris, 5, $data['tenor']);
$worksheet1->write_string($baris, 6, $data['maturity_date']);
$worksheet1->write_string($baris, 7, $data['PLAFOND']);
$worksheet1->write_string($baris, 8, $data['Plafond']);
$worksheet1->write_string($baris, 9, $data['saldo']);
$worksheet1->write_string($baris, 10, $data['pokok']);
$worksheet1->write_string($baris, 11, $data['saldo']);
$worksheet1->write_string($baris, 12, $data['bunga_join']);
$worksheet1->write_string($baris, 13, $data['run_tenor']);
$baris++;
}	
}
$workbook->close();
?>