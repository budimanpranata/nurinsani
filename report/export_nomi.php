<?php require_once __DIR__ . '/../php8_compat.php';
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
$worksheet1->write_string(0, 11, "KE", $format);

$worksheet1->set_column(0, 12, 20);
$worksheet1->write_string(0, 12, "TUNGGAKAN POKOK", $format);
$worksheet1->set_column(0, 13, 20);
$worksheet1->write_string(0, 13, "TUNGGAKAN MARGIN", $format);
$worksheet1->set_column(0, 14, 20);
$worksheet1->write_string(0, 14, "GOL", $format);
$worksheet1->set_column(0, 15, 20);
$worksheet1->write_string(0, 15, "AO", $format);
$worksheet1->set_column(0, 16, 20);
$worksheet1->write_string(0, 16, "NAMA AO", $format);
$worksheet1->set_column(0, 17, 20);
$worksheet1->write_string(0, 17, "NAMA KELOMPOK", $format);
$worksheet1->set_column(0, 18, 8);
$worksheet1->write_string(0, 18, "CIF", $format);
$worksheet1->set_column(0, 19, 8);
$worksheet1->write_string(0, 19, "ALAMAT", $format);
$worksheet1->set_column(0, 20, 8);
$worksheet1->write_string(0, 20, "BUDANG USAHA", $format);
$worksheet1->set_column(0, 21, 8);
$worksheet1->write_string(0, 21, "NO KTP", $format);
$worksheet1->set_column(0, 22, 8);
$worksheet1->write_string(0, 22, "STATUS", $format);
$worksheet1->set_column(0, 23, 8);
$worksheet1->write_string(0, 23, "SUMBER DANA", $format);
$worksheet1->set_column(0, 24, 8);
$worksheet1->write_string(0, 24, "TWM", $format);
$worksheet1->set_column(0, 25, 8);
$worksheet1->write_string(0, 25, "Saldo TWM", $format);
$worksheet1->set_column(0, 26, 8);
$worksheet1->write_string(0, 26, "Sisa TWM", $format);









if ($status ='CURRENT'){


$query = "select 
loan.no_anggota,
loan.unit,
loan.Cust_Short_name as Cust_Short_name,
Plafond,
loan.cif,
os,
saldo_margin,
loan.code_kel,
os,
tenor,run_tenor,loan.Deal_type as Deal_type,tgl_akad as tgl_murab,next_schedule,maturity_date,loan.cao,saldo_margin,pokok,ijaroh,angsuran,bidang_usaha.nama_usaha as usaha,CONCAT(HADDRESS1,HADDRESS5,HADDRESS2,HADDRESS3,HADDRESS4) as alamat,ID_NUMBER,status_app,SOURCE_INCOME,bulat,run_tenor,
nama_kel,
nama_ao,
gol
from loan
left join anggota on anggota.cif = loan.cif
left join kelompok on loan.code_kel=kelompok.code_kel 
left join ao on ao.cao = loan.cao
left join bidang_usaha on bidang_usaha.code_bidang = loan.usaha
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
$bulat =$data['bulat'];
        $angsuran =$data['angsuran'];
        $minggon = $bulat-($angsuran+2500);
 $run_tenor=$data['run_tenor'];
        $total_twm = $minggon * $run_tenor;
      
        //$row_sal= mysql_fetch_array(mysql_query("select(sum(KREDIT) - sum(DEBIT))as twm from simpanan WHERE type not in ('05','04') and cif ='$cif'"));
        //$twm=$row_sal['twm'];
        //$saldo_twm=$twm-$total_twm;
$kosong='';

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
$worksheet1->write_number($baris, 11, $data['run_tenor']);
$worksheet1->write_number($baris, 12, $tunggak_pokok);
$worksheet1->write_number($baris, 13, $tunggak_margin);
$worksheet1->write_string($baris, 14, $data['gol']);
$worksheet1->write_string($baris, 15, $data['cao']);
$worksheet1->write_string($baris, 16, $data['nama_ao']);
$worksheet1->write_string($baris, 17, $data['nama_kel']);
$worksheet1->write_string($baris, 18, $data['cif']);
$worksheet1->write_string($baris, 19, $kosong);
$worksheet1->write_string($baris, 20, $data['usaha']);
$worksheet1->write_string($baris, 21, $kosong);
$worksheet1->write_string($baris, 22, $data['status_app']);
$worksheet1->write_string($baris, 23, $data['deal_type']);
$worksheet1->write_string($baris, 24, $kosong);
$worksheet1->write_string($baris, 25, $kosong);
$worksheet1->write_string($baris, 26, $kosong);



$baris++;
}

}else{
$query2 = "select 
data_loan.no_anggota,
data_loan.unit,
data_loan.Cust_Short_name as Cust_Short_name,
Plafond,
data_loan.cif,
os,
saldo_margin,
data_loan.code_kel,
tenor,run_tenor,data_loan.Deal_type as Deal_type,tgl_akad as tgl_murab,next_schedule,maturity_date,loan.cao,saldo_margin,pokok,ijaroh,angsuran,bidang_usaha.nama_usaha as usaha,CONCAT(HADDRESS1,HADDRESS5,HADDRESS2,HADDRESS3,HADDRESS4) as alamat,
nama_kel,
nama_ao,
gol
from data_loan
left join anggota on anggota.cif = data_loan.cif
left join kelompok on data_loan.code_kel=kelompok.code_kel 
left join ao on ao.cao = data_loan.cao
left join bidang_usaha on bidang_usaha.code_bidang = data_loan.usaha

WHERE  MONTH(data_loan.buss_date)='$bulan' AND YEAR(data_loan.buss_date)='$tahun' and data_loan.unit ='$unit' and os > 0";
$hasil2 = mysql_query($query2);
$baris = 1;
$kosong ='';
while ($data = mysql_fetch_array($hasil2))
{
$worksheet1->write_string($baris, 0, $data['unit']);
$worksheet1->write_string($baris, 1, $data['no_anggota']);
$worksheet1->write_string($baris, 2, $data['Cust_Short_name']);
$worksheet1->write_string($baris, 3, $data['code_kel']);
$worksheet1->write_string($baris, 4, tgl_indo ($data['tgl_murab']));
$worksheet1->write_string($baris, 5, $data['tenor']);
$worksheet1->write_string($baris, 6, tgl_indo ($data['maturity_date']));
$worksheet1->write_number($baris, 7, $data['Plafond']);
$worksheet1->write_number($baris, 8, $data['os']);
$worksheet1->write_number($baris, 9,  $data['os'] - $data['saldo_margin']);
$worksheet1->write_number($baris, 10, $data['saldo_margin']);
$worksheet1->write_number($baris, 11, $tunggak_pokok);
$worksheet1->write_number($baris, 12, $tunggak_margin);
$worksheet1->write_string($baris, 13, $data['gol']);
$worksheet1->write_string($baris, 14, $data['cao']);
$worksheet1->write_string($baris, 15, $data['nama_ao']);
$worksheet1->write_string($baris, 16, $data['nama_kel']);
$worksheet1->write_string($baris, 17, $data['cif']);
$worksheet1->write_string($baris, 18, $kosong);
$worksheet1->write_string($baris, 19, $data['usaha']);
$baris++;
}	
}
$workbook->close();
?>
