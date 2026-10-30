<?php require_once __DIR__ . '/koneksi_db.php';  
//error_reporting(0);
session_start();  
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();  
$userid=$_SESSION['userid'];
//cek apakah user sudah login

include "conn2.php";
include "indo_tgl.php";

$koneksi=open_connection2();
$code_kel = $_GET['code_kel'];  

$buss_date=date("y-m-d");
$buss_date1=getdate();
$today=date("Y-m-d");
$date=tgl_indo($today);



$dataum = db_semua(" Select Nama as um,jabatan from person  
where unit=?", 's', (string) $userid);
$um = $dataum ? $dataum[0] : false;
$nama_um=$um['um'];
$jabatan=$um['jabatan'];

require ('fpdf17/fpdf.php');
$pdf=new FPDF ('P','mm','A4');
$pdf->addPage();

$pot = db_semua("select loan.buss_date,loan.Cust_Short_name,Plafond,bagi_hasil,Tenor,angsuran,hari,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,WARIS,NOREK,tgl_murab,tgl_wakalah,tgl_akad,maturity_date,loan.nama_usaha,hari,
saldo_margin,ijaroh,angsuran,pokok,os,angsuran,bulat,tgl_akad,(os-saldo_margin) as saldo_os,nama_kel,run_tenor,no_anggota,loan.cif,
ID_NUMBER from loan 
left join anggota on anggota.cif=loan.cif 
left join kelompok on kelompok.code_kel = loan.code_kel
left join bidang_usaha on bidang_usaha.code_bidang=loan.usaha 
where loan.code_kel=? order by loan.cif", 's', (string) $code_kel);
$page=0;
foreach ($pot as $p){
$page++;
$nama=$p['Cust_Short_name'];
$plafond=$p['Plafond'];
$os=$p['os'];
$cif=$p['cif'];
$norek=$p['no_anggota'];
$ke=$p['run_tenor'];
$os=number_format(compat_num($p['os']));
$terang=ucwords (Terbilang($p['os']));
$angsuran =number_format (compat_num($p['angsuran']));
$ter_angsuran=ucwords (Terbilang($p['angsuran']));
$untung =$p['bagi_hasil'];
$nama_kel =$p['nama_kel'];
$margin = number_format (compat_num($p['bagi_hasil']));
$ter_margin=ucwords (Terbilang($p['bagi_hasil']));
$ktp=$p['ID_NUMBER'];
$usaha=$p['nama_usaha'];
$a=$p['HADDRESS1'];
$b=$p['HADDRESS2'];
$c=$p['HADDRESS3'];
$d=$p['HADDRESS4'];
$buss_date=tgl_indo ($p['buss_date']);
$waris=$p['WARIS'];
$harii=$p['hari'];
$tgl_res=tgl_indo($tanggal);
$maturity=tgl_indo($p['maturity_date']);
$tgl_wakalah=tgl_indo($p['tgl_wakalah']);
$alamat="$a $b $c $d";
$mat=$p['maturity_date'];
$ta ="'";
$tutup=")";
$piutangg =$plafond + $untung;
$piutang=number_format(compat_num($piutangg));
$ter_piutang=ucwords (Terbilang($piutangg));
$saldo_margin=$p['saldo_margin'];
$pokok_os=$p['saldo_os'];
$angsur=$p['angsuran'];
$row_tunggak=db_satu("select sum(KREDIT-DEBIT) as saldo_tunggak from tunggakan where cif =? ", 's', (string) $cif) or die(mysql_error());
 $saldo_tunggak=$row_tunggak['saldo_tunggak'];     
$tunggak=$saldo_tunggak;

$ft =compat_bagi($tunggak, $angsur); // PHP 5: pembagi 0/kosong -> false, bukan error

$os_res=$os;

  $bulat = $angsuran;



        $tgl_m    = strtotime($mat);
        $maturity_date_ress =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));


$cek_tanggal = db_semua("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between ? and ? and Hari =?", 'sss', (string) $mat, (string) $maturity_date_ress, (string) $harii);
        foreach ($cek_tanggal as $data_libur){
            $libur =$data_libur['libur'];
           
           
        }

if($libur>0){
$ft =$ft+$libur;

        $tgl_m    = strtotime($mat);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));



}else{
$maturity_date_res=$maturity_date_ress;
}

$maturity_date_res=tgl_indo($maturity_date_res);


 do {

$pdf->setX(20);
$pdf->setFillColor(255,255,255);
//$pdf-> Write (12,'User ID: ');
$pdf->SetFont ('Arial','BU',13);
$pdf->multicell(0,5,'NOTIFICATION ADENDUM PERJANJIAN JUAL BELI (AL MURABAHAH) ',0,'C',1);
$pdf->SetFont ('Arial','BI',9);
$pdf->Ln(4);
$pdf->multicell(0,5,'BISMILLAHIRAHMANIRROHIM',0,'C',1);
$pdf->Ln(1);
$pdf->multicell(0,5,'"Hai Orang-orang beriman penuhilah akad-akad perjanjian itu " (Q.S. Al Maidah 1) ',0,'C',1);
$pdf->multicell(0,5,'"Cukupkanlah takaran jangan kamu menjadi orang-orang yang merugikan " (Q.S. Asy-Syu'.$ta.'ara 181) ',0,'C',1);
$pdf->multicell(0,5,'"Dan Allah SWT telah menghalalkan jual-beli dan mengharamkan riba " (Q.S. Al-Baqorah 275) ',0,'C',1);
$pdf->Ln(1);
$pdf->multicell(0,5,'===================================================================================================== ',0,'J',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH NUR INSANI, berkedudukan di Tangerang Selatan, berdasarkan akte pendirian KJKS No : 02 Tanggal 07 Mei 2011 dihadapan Notaris Neilly Iralita Iswari, SH, MSi, M.Kn. dan telah memperoleh persetujuan Kementrian Koperasi dan Usaha Kecil dan Menengah No. 1024/BH/M.KUM.2/X1/2011 serta perubahannya selaku PIHAK PERTAMA memberitahukan adanya Perubahan Akad Murabahah(Selanjutnya disebut ADENDUM) atas PIHAK KEDUA ',0,'J',1);
$pdf->Ln(4);

$pdf->SetFont ('Arial','B',11);
$pdf->cell(55,5,'Nama',0,'L',1);
$pdf->cell(20,5,': '.$nama,0,'R',1);
$pdf->Ln(4);
$pdf->cell(55,5,'Nomor Rekening',0,'L',1);
$pdf->cell(20,5,': '.$norek,0,'R',1);
$pdf->Ln(4);
$pdf->cell(55,5,'Angsuran Ke ',0,'L',1);
$pdf->cell(20,5,': '.$ke,0,'R',1);
$pdf->Ln(4);
$pdf->cell(55,5,'Jatuh Tempo Pembiayaan',0,'R',1);
$pdf->cell(25,5,': '.$maturity,0,'R',1);
$pdf->Ln(6);
$pdf->multicell(0,5,'Dengan Perubahan Sebagai berikut :',0,'L',1);
$pdf->Ln(4);
$pdf->cell(55,5,'Jatuh Tempo Pembiayaan',0,'L',1);
$pdf->cell(25,5,': Tanggal '.$maturity_date_res,0,'L',1);
$pdf->Ln(10);

$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Demikian ADENDUM ini dibuat dan ditanda tangani di '. $d.' pada hari '.$harii.' tanggal '.$date.' ',0,'J',1);
$pdf->Ln(4);
$pdf->cell(70,5,'PIHAK PERTAMA ',0,'R',1);
$pdf->cell(70,5,'',0,'R',1);

$pdf->Ln(30);
$pdf->SetFont ('Arial','U',11);
$pdf->cell(70,5,'( '.$nama_um .$tutup,0,'C',1);
$pdf->Ln(4);

$pdf->ln (107);

 }
while ($page<$feed);
}
$pdf->Output();



?> 
<?php
function Terbilang($x)
{
  $abil = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
  if ($x < 12)
    return " " . $abil[$x];
  elseif ($x < 20)
    return Terbilang($x - 10) . " Belas";
  elseif ($x < 100)
    return Terbilang($x / 10) . " puluh" . Terbilang($x % 10);
  elseif ($x < 200)
    return " seratus" . Terbilang($x - 100);
  elseif ($x < 1000)
    return Terbilang($x / 100) . " ratus" . Terbilang($x % 100);
  elseif ($x < 2000)
    return " seribu" . Terbilang($x - 1000);
  elseif ($x < 1000000)
    return Terbilang($x / 1000) . " ribu" . Terbilang($x % 1000);
  elseif ($x < 1000000000)
    return Terbilang($x / 1000000) . " juta" . Terbilang($x % 1000000);
}

?>
