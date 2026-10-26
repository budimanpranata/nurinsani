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
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:../index.html");
}
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
$worksheet1->write_string(0, 0, "Nama AO", $format);
$worksheet1->set_column(0, 1, 30);
$worksheet1->write_string(0, 1, "Saldo Awal noa", $format);
$worksheet1->set_column(0, 2, 45);
$worksheet1->write_string(0, 2, "Saldo Awal Kredit", $format);
$worksheet1->set_column(0, 3, 30);
$worksheet1->write_string(0, 3, "Real Baru", $format);
$worksheet1->set_column(0, 4, 20);
$worksheet1->write_string(0, 4, "Realisasi Pinjaman", $format);
$worksheet1->set_column(0, 5, 10);
$worksheet1->write_string(0, 5, "Lunas Baru", $format);
$worksheet1->set_column(0, 6, 15);
$worksheet1->write_string(0, 6, "Angsuran Pokok", $format);
$worksheet1->set_column(0, 7, 20);
$worksheet1->write_string(0, 7, "Angsuran Margin", $format);
$worksheet1->set_column(0, 8, 45);
$worksheet1->write_string(0, 8, "Debitur Akhir", $format);
$worksheet1->set_column(0, 9, 45);
$worksheet1->write_string(0, 9, "Saldo Akhir Kredit", $format);
$worksheet1->set_column(0, 10, 20);
$worksheet1->write_string(0, 10, "Tunggakan Pokok", $format);
$worksheet1->set_column(0, 11, 20);
$worksheet1->write_string(0, 11, "Saldo Tabungan", $format);
$worksheet1->set_column(0, 12, 20);
$worksheet1->write_string(0, 12, "Unit", $format);

if ($status ='CURRENT'){

$max= mysql_fetch_array(mysql_query("select max(buss_date) as lama from data_loan"));
$tgl_lama=$max['lama'];
$max_rel= mysql_fetch_array(mysql_query("select month(tgl_realisasi) as lama_bulan, YEAR(tgl_realisasi) as lama_tahun from rek_loan order by ref DESC limit 1"));
$bln_rel=$max_rel['lama_bulan'];
$year_rel=$max_rel['lama_tahun'];

$query = "Select loan.cao,nama_ao,sum(os)-sum(saldo_margin) as saldo_akhir,count(cif) as noa from loan 
inner join ao on ao.cao=loan.cao where os>0 and loan.unit='$unit'
group by cao";
$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{

$cao=$data['cao'];
$nama_ao=$data['nama_ao'];
$noa=$data['noa'];
$saldo_akhir=$data['saldo_akhir'];
$sal_lama= mysql_fetch_array(mysql_query("select count(cif) as noa_awal, sum(os)as os_awal  from data_loan WHERE cao='$cao' and buss_date='$tgl_lama'"));
$noa_awal=$sal_lama['noa_awal'];
$saldo_awal=$sal_lama['os_awal'];

$cek_pokok= mysql_fetch_array(mysql_query("select sum(debet) as pokok from rek_loan WHERE  AO='$cao' and month(tgl_realisasi)='$bln_rel' and YEAR(tgl_realisasi)='$year_rel' and tipe='03' group by ao"));
$pokok=$cek_pokok['pokok'];
$cek_margin= mysql_fetch_array(mysql_query("select sum(debet) as margin from rek_loan WHERE AO='$cao' and month(tgl_realisasi)='$bln_rel' and YEAR(tgl_realisasi)='$year_rel' and tipe='04' group by ao"));
$margin=$cek_margin['margin'];
$real= mysql_fetch_array(mysql_query("select sum(Plafond) as real_pinjam  from loan WHERE cao='$cao' and month(tgl_murab)= MONTH(NOW()) and YEAR(tgl_murab)=YEAR(NOW())"));
$real_pinjam=$real['real_pinjam'];
$real_baru= mysql_fetch_array(mysql_query("select count(cif) as baru  from loan WHERE cao='$cao' and month(tgl_murab)= MONTH(NOW()) and YEAR(tgl_murab)=YEAR(NOW()) and cif not in(select cif from data_loan) "));
$baru=$real_baru['baru'];
$lu_baru= mysql_fetch_array(mysql_query("select count(cif) as baru  from loan WHERE cao='$cao' and month(tgl_murab)= MONTH(NOW()) and YEAR(tgl_murab)=YEAR(NOW()) and cif in(select cif from data_loan) "));
$lunas_barus=$lu_baru['baru'];

$data_tunggak= mysql_fetch_array(mysql_query("select sum(kredit)-sum(debit) as tunggak  from tunggakan WHERE cao='$cao'"));
$tunggak=$data_tunggak['tunggak'];
$data_simpanan= mysql_fetch_array(mysql_query("select sum(kredit-debit) as saldo  from simpanan WHERE cao='$cao'"));
$simpanan=$data_simpanan['saldo'];

	
$worksheet1->write_string($baris, 0, $data['nama_ao']);
$worksheet1->write_string($baris, 1, $noa_awal);
$worksheet1->write_string($baris, 2, $saldo_awal);
$worksheet1->write_string($baris, 3, $baru);
$worksheet1->write_string($baris, 4, $real_pinjam);
$worksheet1->write_string($baris, 5, $lunas_baru);
$worksheet1->write_string($baris, 6, $pokok);
$worksheet1->write_string($baris, 7, $margin);
$worksheet1->write_string($baris, 8, $data['noa']);
$worksheet1->write_string($baris, 9,  $data['saldo_akhir']);
$worksheet1->write_string($baris, 10, $tunggak);
$worksheet1->write_string($baris, 11, $simpanan);
$worksheet1->write_string($baris, 12, $unit);

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
