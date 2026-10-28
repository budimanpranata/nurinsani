<?php require_once __DIR__ . '/../../php8_compat.php';
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
if($_SESSION['level']!="unit"){
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
HeaderingExcel('kelektabilitas.xls');

// membuat workbook baru
$workbook = new Workbook("");
// membuat worksheet ke-1 (data Mutliguna)
$worksheet1 =& $workbook->add_worksheet('kolek1');
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
sum(os-saldo_margin) as saldo_os,
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
where loan.unit ='$unit' and os > 0 and gol='1' group by loan.cif ";

$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$kali=$data['tunggak']/$data['angsuran'];
$tunggak_pokok=$kali * $data['pokok'];
$tunggak_margin=$kali * $data['ijaroh'];


$worksheet1->write_string($baris, 0, $data['unit']);
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['code_kel']);
$worksheet1->write_string($baris, 4, tgl_indo ($data['tgl_murab']));
$worksheet1->write_string($baris, 5, $data['tenor']);
$worksheet1->write_string($baris, 6, tgl_indo ($data['maturity_date']));
$worksheet1->write_string($baris, 7, $data['Plafond']);
$worksheet1->write_string($baris, 8, $data['os']);
$worksheet1->write_string($baris, 9,  $data['saldo_os']);
$worksheet1->write_string($baris, 10, $data['saldo_margin']);
$worksheet1->write_string($baris, 11, $tunggak_pokok);
$worksheet1->write_string($baris, 12, $tunggak_margin);
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
$worksheet2 =& $workbook->add_worksheet('kolek2');
$worksheet2->set_row(0, 20);
$worksheet2->set_column(0, 0, 30);
$worksheet2->write_string(0, 0, "UNIT", $format);
$worksheet2->set_column(0, 1, 30);
$worksheet2->write_string(0, 1, "NO. REKENING", $format);
$worksheet2->set_column(0, 2, 45);
$worksheet2->write_string(0, 2, "NAMA", $format);
$worksheet2->set_column(0, 3, 30);
$worksheet2->write_string(0, 3, "KELOMPOK", $format);
$worksheet2->set_column(0, 4, 20);
$worksheet2->write_string(0, 4, "TANGGAL PK", $format);
$worksheet2->set_column(0, 5, 10);
$worksheet2->write_string(0, 5, "TENOR", $format);
$worksheet2->set_column(0, 6, 15);
$worksheet2->write_string(0, 6, "JATUH TEMPO", $format);
$worksheet2->set_column(0, 7, 20);
$worksheet2->write_string(0, 7, "PLAFOND", $format);
$worksheet2->set_column(0, 8, 45);
$worksheet2->write_string(0, 8, "SALDO PINJAMAN", $format);
$worksheet2->set_column(0, 9, 45);
$worksheet2->write_string(0, 9, "SALDO OS", $format);
$worksheet2->set_column(0, 10, 20);
$worksheet2->write_string(0, 10, "SALDO MARGIN", $format);
$worksheet2->set_column(0, 11, 20);
$worksheet2->write_string(0, 11, "TUNGGAKAN POKOK", $format);
$worksheet2->set_column(0, 12, 20);
$worksheet2->write_string(0, 12, "TUNGGAKAN MARGIN", $format);
$worksheet2->set_column(0, 13, 20);
$worksheet2->write_string(0, 13, "GOL", $format);
$worksheet2->set_column(0, 14, 20);
$worksheet2->write_string(0, 14, "AO", $format);
$worksheet2->set_column(0, 15, 20);
$worksheet2->write_string(0, 15, "NAMA AO", $format);
$worksheet2->set_column(0, 16, 20);
$worksheet2->write_string(0, 16, "NAMA KELOMPOK", $format);




if ($status ='CURRENT'){


$query = "select 
loan.no_anggota,
loan.unit,
Cust_Short_name,
Plafond,
sum(os-saldo_margin) as saldo_os,
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
where loan.unit ='$unit' and os > 0 and gol='2' group by loan.cif ";

$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$kali=$data['tunggak']/$data['angsuran'];
$tunggak_pokok=$kali * $data['pokok'];
$tunggak_margin=$kali * $data['ijaroh'];


$worksheet2->write_string($baris, 0, $data['unit']);
$worksheet2->write_string($baris, 1, $data['no_anggota']);
$worksheet2->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet2->write_string($baris, 3, $data['code_kel']);
$worksheet2->write_string($baris, 4, tgl_indo ($data['tgl_murab']));
$worksheet2->write_string($baris, 5, $data['tenor']);
$worksheet2->write_string($baris, 6, tgl_indo ($data['maturity_date']));
$worksheet2->write_string($baris, 7, $data['Plafond']);
$worksheet2->write_string($baris, 8, $data['os']);
$worksheet2->write_string($baris, 9,  $data['saldo_os']);
$worksheet2->write_string($baris, 10, $data['saldo_margin']);
$worksheet2->write_string($baris, 11, $tunggak_pokok);
$worksheet2->write_string($baris, 12, $tunggak_margin);
$worksheet2->write_string($baris, 13, $data['gol']);
$worksheet2->write_string($baris, 14, $data['cao']);
$worksheet2->write_string($baris, 15, $data['nama_ao']);
$worksheet2->write_string($baris, 16, $data['nama_kel']);
$baris++;
}

}else{
$query = " SELECT * from data_loan 
WHERE  MONTH(data_loan.buss_date)='$bulan' AND YEAR(data_loan.buss_date)='$tahun'";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet2->write_string($baris, 0, tgl_indo ($data['unit']));
$worksheet2->write_string($baris, 1, $data['no_anggota']);
$worksheet2->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet2->write_string($baris, 3, $data['kel']);
$worksheet2->write_string($baris, 4, $data['tgl_murab']);
$worksheet2->write_string($baris, 5, $data['tenor']);
$worksheet2->write_string($baris, 6, $data['maturity_date']);
$worksheet2->write_string($baris, 7, $data['PLAFOND']);
$worksheet2->write_string($baris, 8, $data['Plafond']);
$worksheet2->write_string($baris, 9, $data['saldo']);
$worksheet2->write_string($baris, 10, $data['pokok']);
$worksheet2->write_string($baris, 11, $data['saldo']);
$worksheet2->write_string($baris, 12, $data['bunga_join']);
$worksheet2->write_string($baris, 13, $data['run_tenor']);
$baris++;
}	
}
$worksheet3 =& $workbook->add_worksheet('kolek3');
$worksheet3->set_row(0, 20);
$worksheet3->set_column(0, 0, 30);
$worksheet3->write_string(0, 0, "UNIT", $format);
$worksheet3->set_column(0, 1, 30);
$worksheet3->write_string(0, 1, "NO. REKENING", $format);
$worksheet3->set_column(0, 2, 45);
$worksheet3->write_string(0, 2, "NAMA", $format);
$worksheet3->set_column(0, 3, 30);
$worksheet3->write_string(0, 3, "KELOMPOK", $format);
$worksheet3->set_column(0, 4, 20);
$worksheet3->write_string(0, 4, "TANGGAL PK", $format);
$worksheet3->set_column(0, 5, 10);
$worksheet3->write_string(0, 5, "TENOR", $format);
$worksheet3->set_column(0, 6, 15);
$worksheet3->write_string(0, 6, "JATUH TEMPO", $format);
$worksheet3->set_column(0, 7, 20);
$worksheet3->write_string(0, 7, "PLAFOND", $format);
$worksheet3->set_column(0, 8, 45);
$worksheet3->write_string(0, 8, "SALDO PINJAMAN", $format);
$worksheet3->set_column(0, 9, 45);
$worksheet3->write_string(0, 9, "SALDO OS", $format);
$worksheet3->set_column(0, 10, 20);
$worksheet3->write_string(0, 10, "SALDO MARGIN", $format);
$worksheet3->set_column(0, 11, 20);
$worksheet3->write_string(0, 11, "TUNGGAKAN POKOK", $format);
$worksheet3->set_column(0, 12, 20);
$worksheet3->write_string(0, 12, "TUNGGAKAN MARGIN", $format);
$worksheet3->set_column(0, 13, 20);
$worksheet3->write_string(0, 13, "GOL", $format);
$worksheet3->set_column(0, 14, 20);
$worksheet3->write_string(0, 14, "AO", $format);
$worksheet3->set_column(0, 15, 20);
$worksheet3->write_string(0, 15, "NAMA AO", $format);
$worksheet3->set_column(0, 16, 20);
$worksheet3->write_string(0, 16, "NAMA KELOMPOK", $format);




if ($status ='CURRENT'){


$query = "select 
loan.no_anggota,
loan.unit,
Cust_Short_name,
Plafond,
sum(os-saldo_margin) as saldo_os,
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
where loan.unit ='$unit' and os > 0 and gol='3' group by loan.cif ";

$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$kali=$data['tunggak']/$data['angsuran'];
$tunggak_pokok=$kali * $data['pokok'];
$tunggak_margin=$kali * $data['ijaroh'];


$worksheet3->write_string($baris, 0, $data['unit']);
$worksheet3->write_string($baris, 1, $data['no_anggota']);
$worksheet3->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet3->write_string($baris, 3, $data['code_kel']);
$worksheet3->write_string($baris, 4, tgl_indo ($data['tgl_murab']));
$worksheet3->write_string($baris, 5, $data['tenor']);
$worksheet3->write_string($baris, 6, tgl_indo ($data['maturity_date']));
$worksheet3->write_string($baris, 7, $data['Plafond']);
$worksheet3->write_string($baris, 8, $data['os']);
$worksheet3->write_string($baris, 9,  $data['saldo_os']);
$worksheet3->write_string($baris, 10, $data['saldo_margin']);
$worksheet3->write_string($baris, 11, $tunggak_pokok);
$worksheet3->write_string($baris, 12, $tunggak_margin);
$worksheet3->write_string($baris, 13, $data['gol']);
$worksheet3->write_string($baris, 14, $data['cao']);
$worksheet3->write_string($baris, 15, $data['nama_ao']);
$worksheet3->write_string($baris, 16, $data['nama_kel']);
$baris++;
}

}else{
$query = " SELECT * from data_loan 
WHERE  MONTH(data_loan.buss_date)='$bulan' AND YEAR(data_loan.buss_date)='$tahun'";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet3->write_string($baris, 0, tgl_indo ($data['unit']));
$worksheet3->write_string($baris, 1, $data['no_anggota']);
$worksheet3->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet3->write_string($baris, 3, $data['kel']);
$worksheet3->write_string($baris, 4, $data['tgl_murab']);
$worksheet3->write_string($baris, 5, $data['tenor']);
$worksheet3->write_string($baris, 6, $data['maturity_date']);
$worksheet3->write_string($baris, 7, $data['PLAFOND']);
$worksheet3->write_string($baris, 8, $data['Plafond']);
$worksheet3->write_string($baris, 9, $data['saldo']);
$worksheet3->write_string($baris, 10, $data['pokok']);
$worksheet3->write_string($baris, 11, $data['saldo']);
$worksheet3->write_string($baris, 12, $data['bunga_join']);
$worksheet3->write_string($baris, 13, $data['run_tenor']);
$baris++;
}	
}
$worksheet4 =& $workbook->add_worksheet('kolek4');
$worksheet4->set_row(0, 20);
$worksheet4->set_column(0, 0, 30);
$worksheet4->write_string(0, 0, "UNIT", $format);
$worksheet4->set_column(0, 1, 30);
$worksheet4->write_string(0, 1, "NO. REKENING", $format);
$worksheet4->set_column(0, 2, 45);
$worksheet4->write_string(0, 2, "NAMA", $format);
$worksheet4->set_column(0, 3, 30);
$worksheet4->write_string(0, 3, "KELOMPOK", $format);
$worksheet4->set_column(0, 4, 20);
$worksheet4->write_string(0, 4, "TANGGAL PK", $format);
$worksheet4->set_column(0, 5, 10);
$worksheet4->write_string(0, 5, "TENOR", $format);
$worksheet4->set_column(0, 6, 15);
$worksheet4->write_string(0, 6, "JATUH TEMPO", $format);
$worksheet4->set_column(0, 7, 20);
$worksheet4->write_string(0, 7, "PLAFOND", $format);
$worksheet4->set_column(0, 8, 45);
$worksheet4->write_string(0, 8, "SALDO PINJAMAN", $format);
$worksheet4->set_column(0, 9, 45);
$worksheet4->write_string(0, 9, "SALDO OS", $format);
$worksheet4->set_column(0, 10, 20);
$worksheet4->write_string(0, 10, "SALDO MARGIN", $format);
$worksheet4->set_column(0, 11, 20);
$worksheet4->write_string(0, 11, "TUNGGAKAN POKOK", $format);
$worksheet4->set_column(0, 12, 20);
$worksheet4->write_string(0, 12, "TUNGGAKAN MARGIN", $format);
$worksheet4->set_column(0, 13, 20);
$worksheet4->write_string(0, 13, "GOL", $format);
$worksheet4->set_column(0, 14, 20);
$worksheet4->write_string(0, 14, "AO", $format);
$worksheet4->set_column(0, 15, 20);
$worksheet4->write_string(0, 15, "NAMA AO", $format);
$worksheet4->set_column(0, 16, 20);
$worksheet4->write_string(0, 16, "NAMA KELOMPOK", $format);




if ($status ='CURRENT'){


$query = "select 
loan.no_anggota,
loan.unit,
Cust_Short_name,
Plafond,
sum(os-saldo_margin) as saldo_os,
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
where loan.unit ='$unit' and os > 0 and gol='4' group by loan.cif ";

$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$kali=$data['tunggak']/$data['angsuran'];
$tunggak_pokok=$kali * $data['pokok'];
$tunggak_margin=$kali * $data['ijaroh'];


$worksheet4->write_string($baris, 0, $data['unit']);
$worksheet4->write_string($baris, 1, $data['no_anggota']);
$worksheet4->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet4->write_string($baris, 3, $data['code_kel']);
$worksheet4->write_string($baris, 4, tgl_indo ($data['tgl_murab']));
$worksheet4->write_string($baris, 5, $data['tenor']);
$worksheet4->write_string($baris, 6, tgl_indo ($data['maturity_date']));
$worksheet4->write_string($baris, 7, $data['Plafond']);
$worksheet4->write_string($baris, 8, $data['os']);
$worksheet4->write_string($baris, 9,  $data['saldo_os']);
$worksheet4->write_string($baris, 10, $data['saldo_margin']);
$worksheet4->write_string($baris, 11, $tunggak_pokok);
$worksheet4->write_string($baris, 12, $tunggak_margin);
$worksheet4->write_string($baris, 13, $data['gol']);
$worksheet4->write_string($baris, 14, $data['cao']);
$worksheet4->write_string($baris, 15, $data['nama_ao']);
$worksheet4->write_string($baris, 16, $data['nama_kel']);
$baris++;
}

}else{
$query = " SELECT * from data_loan 
WHERE  MONTH(data_loan.buss_date)='$bulan' AND YEAR(data_loan.buss_date)='$tahun'";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$worksheet4->write_string($baris, 0, tgl_indo ($data['unit']));
$worksheet4->write_string($baris, 1, $data['no_anggota']);
$worksheet4->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet4->write_string($baris, 3, $data['kel']);
$worksheet4->write_string($baris, 4, $data['tgl_murab']);
$worksheet4->write_string($baris, 5, $data['tenor']);
$worksheet4->write_string($baris, 6, $data['maturity_date']);
$worksheet4->write_string($baris, 7, $data['PLAFOND']);
$worksheet4->write_string($baris, 8, $data['Plafond']);
$worksheet4->write_string($baris, 9, $data['saldo']);
$worksheet4->write_string($baris, 10, $data['pokok']);
$worksheet4->write_string($baris, 11, $data['saldo']);
$worksheet4->write_string($baris, 12, $data['bunga_join']);
$worksheet4->write_string($baris, 13, $data['run_tenor']);
$baris++;
}	
}

$workbook->close();
?>
