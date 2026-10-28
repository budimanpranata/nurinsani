<?php require_once __DIR__ . '/../../php8_compat.php'; require_once __DIR__ . '/../cek_admin.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$bulan=$_SESSION['bulan'];
$tahun=$_SESSION['tahun'];
$status=$_SESSION['status'];

//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="admin"){
   header("location:../index.html");
 exit; }
require_once('../excel/Worksheet.php');
require_once('../excel/Workbook.php');
include "../indo_tgl.php";
include "../conn2.php";
$koneksi=open_connection2();
$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}

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
$worksheet1->write_string(0, 7, "PLAFOND", $format);
$worksheet1->set_column(0, 8, 45);
$worksheet1->write_string(0, 8, "SALDO PINJAMAN", $format);
$worksheet1->set_column(0, 9, 45);
$worksheet1->write_string(0, 9, "SALDO OS", $format);
$worksheet1->set_column(0, 10, 20);
$worksheet1->write_string(0, 10, "SALDO MARGIN", $format);
$worksheet1->set_column(0, 11, 20);
$worksheet1->write_string(0, 11, "TUNGGAKAN POKOK", $format);
$worksheet1->set_column(0, 12, 20);
$worksheet1->write_string(0, 12, "TUNGGAKAN MARGIN", $format);
$worksheet1->set_column(0, 13, 20);
$worksheet1->write_string(0, 13, "GOL", $format);
$worksheet1->set_column(0, 14, 20);
$worksheet1->write_string(0, 14, "AO", $format);
$worksheet1->set_column(0, 15, 20);
$worksheet1->write_string(0, 15, "NAMA AO", $format);
$worksheet1->set_column(0, 16, 20);
$worksheet1->write_string(0, 16, "NAMA KELOMPOK", $format);




if ($status ='CURRENT'){


$query = "select 
loan.no_anggota,
loan.unit,
Cust_Short_name,
Plafond,
loan.cif,
os,
saldo_margin,
loan.code_kel,
os,
tenor,run_tenor,Deal_type,tgl_murab,next_schedule,maturity_date,loan.cao,saldo_margin,pokok,ijaroh,angsuran,
sum(KREDIT-DEBIT) as tunggak,
nama_kel,
nama_ao,
gol
from loan
left join tunggakan on tunggakan.cif = loan.cif
left join kelompok on loan.code_kel=kelompok.code_kel 
left join ao on ao.cao = loan.cao
where loan.unit ='$unit' and os > 0 group by loan.cif ";

$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$kali=$data['tunggak']/$data['angsuran'];
$kali=ceil(compat_num($kali));
$cif=$data['cif'];
$cek_tg= mysql_fetch_array(mysql_query("select sum(KREDIT-DEBIT) as pokok from tunggakan where cif ='$cif' and type ='01'"));
$tunggak_pokok=$cek_tg['pokok'];
$cek_tg_wj= mysql_fetch_array(mysql_query("select sum(KREDIT-DEBIT) as margin from tunggakan where cif ='$cif' and type ='02'"));
$tunggak_margin=$cek_tg_wj['margin'];


//$tunggak_pokok=$kali * $data['pokok'];
//$tunggak_margin=$kali * $data['ijaroh'];
$saldo_os=$data['os']-$data['saldo_margin'];

$worksheet1->write_string($baris, 0, $data['unit']);
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['code_kel']);
$worksheet1->write_string($baris, 4, tgl_indo ($data['tgl_murab']));
$worksheet1->write_string($baris, 5, $data['tenor']);
$worksheet1->write_string($baris, 6, tgl_indo ($data['maturity_date']));
$worksheet1->write_number($baris, 7, $data['Plafond']);
$worksheet1->write_number($baris, 8, $data['os']);
$worksheet1->write_number($baris, 9,  $saldo_os);
$worksheet1->write_number($baris, 10, $data['saldo_margin']);
$worksheet1->write_number($baris, 11, $tunggak_pokok);
$worksheet1->write_number($baris, 12, $tunggak_margin);
$worksheet1->write_string($baris, 13, $data['gol']);
$worksheet1->write_string($baris, 14, $data['cao']);
$worksheet1->write_string($baris, 15, $data['nama_ao']);
$worksheet1->write_string($baris, 16, $data['nama_kel']);
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
