<?php require_once __DIR__ . '/php8_compat.php';  
//error_reporting(0);
session_start();  
ob_start();  

$userid=$_SESSION['userid'];
$pilih=$_SESSION['spilih'];
$wadiah=$_SESSION['swadiah'];
$muqosah=$_SESSION['snominal'];
$tenor=$_SESSION['stenor'];
$tanggal=$_SESSION['stanggal'];
//cek apakah user sudah login

include "conn2.php";
include "function.php";
$koneksi=open_connection2();
$cif = $_GET['cif'];  

$buss_date=date("y-m-d");
$buss_date1=getdate();
$today=date("Y-m-d");
$date=tgl_indo($today);

$kel= mysql_query(" Select cao from loan 
 where cif='$cif'");
$datakel= mysql_fetch_array($kel);
$cao=$datakel['cao'];

$dataao= mysql_query(" Select person.Nama as um,person.jabatan as jabatan,cao from ao 
inner join person on ao.atasan = person.NIK where cao='$cao'");
$ao= mysql_fetch_array($dataao);
$nama_um=$ao['um'];
$jabatan=$ao['jabatan'];

//$query = mysql_query("SELECT COUNT(cao) as counter FROM calon_loan WHERE cao='$cao'");
//$check = mysql_fetch_array($query);
//$feed=$check['counter'];

//$pdf=new FPDF ('P','mm','A4');
//$pdf=new FPDF('P','in',array(15,12)); 
require ('fpdf17/fpdf.php');
//$pdf=new FPDF('P','mm', array(216, 275));
$pdf=new FPDF ('P','mm','A4');

$pdf->addPage();

$pot = mysql_query("select loan.buss_date,loan.Cust_Short_name,Plafond,bagi_hasil,Tenor,angsuran,hari,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,WARIS,NOREK,tgl_murab,tgl_wakalah,tgl_akad,maturity_date,loan.nama_usaha,hari,
saldo_margin,ijaroh,angsuran,pokok,os,angsuran,bulat,tgl_akad,(os-saldo_margin) as saldo_os,nama_kel,run_tenor,no_anggota,
ID_NUMBER from loan 
left join anggota on anggota.cif=loan.cif 
left join kelompok on kelompok.code_kel = loan.code_kel
left join bidang_usaha on bidang_usaha.code_bidang=loan.usaha 
where loan.cao='$cao' and loan.cif='$cif' order by loan.cif");

$row_saldo = mysql_fetch_array(mysql_query("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif ='$cif' and type not in('04','05') ")) or die(mysql_error());
       $saldo_simpanan=$row_saldo['saldo_simpanan'];     
$saldo_simpan=$saldo_simpanan;

$row_tunggak = mysql_fetch_array(mysql_query("select sum(KREDIT-DEBIT) as saldo_tunggak from tunggakan where cif ='$cif' ")) or die(mysql_error());
       $saldo_tunggak=$row_tunggak['saldo_tunggak'];     
$tunggak=$saldo_tunggak;



$row_covid_mar = mysql_fetch_array(mysql_query("select gol from data_loan where cif ='$cif' and buss_date ='2020-03-31'")) or die(mysql_error());
       $covid_mar=$row_covid_mar['gol'];     

$row_covid_feb = mysql_fetch_array(mysql_query("select gol from data_loan where cif ='$cif' and buss_date ='2020-02-28'")) or die(mysql_error());
       $covid_feb=$row_covid_feb['gol'];   


$page=0;
while($p = mysql_fetch_array($pot)){
$page++;
$nama=$p['Cust_Short_name'];
$plafond=$p['Plafond'];
$os=$p['os'];
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
$tgl_akad=tgl_indo($p['tgl_murab']);
$tgl_murab=tgl_indo($p['tgl_murab']);
$hari=namahari($p['tgl_murab']);
$hari_akad=namahari($p['tgl_murab']);
$tgl_res=tgl_indo($tanggal);
$hari_res=namahari($tanggal);
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

$ft =$tunggak/$angsur;
switch($pilih){
case "01":

 if($wadiah =='ya'){
	$saldo_simpan=$saldo_simpanan;
			$os_res=$pokok_os - $saldo_simpan;

			$pokok=$os_res/$tenor;
			$angsuran_res=$pokok;
  $totalharga = $angsuran_res;
			$totalharga=ceil(compat_num($totalharga));
                            if (substr($totalharga,-3)>499){
                                $bulat=round(compat_num($totalharga),-3);
                            } else {
                                $bulat=round(compat_num($totalharga),-3)+1000;
                            } 


} else{
	$saldo_simpan=0;
		
	//Hitung Restruturisasai
			$os_res=$pokok_os - $saldo_simpan;
			
			$pokok=$os_res/$tenor;
			$angsuran_res=$pokok;
  $totalharga = $angsuran_res;
			$totalharga=ceil(compat_num($totalharga));
                            if (substr($totalharga,-3)>499){
                                $bulat=round(compat_num($totalharga),-3);
                            } else {
                                $bulat=round(compat_num($totalharga),-3)+1000;
                            } 

}
break;
case "02";
 if($wadiah =='ya'){
	$saldo_simpan=$saldo_simpanan;
			$os_res=$os - $saldo_simpan;

			$pokok=$os_res/$tenor;
			$angsuran_res=$pokok;
  $totalharga = $angsuran_res;
			$totalharga=ceil(compat_num($totalharga));
                            if (substr($totalharga,-3)>499){
                                $bulat=round(compat_num($totalharga),-3);
                            } else {
                                $bulat=round(compat_num($totalharga),-3)+1000;
                            } 


$tgl_wakal    = strtotime($tanggal);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_res =  date("Y-m-d", strtotime("+$tenor WEEK", $tgl_m));

$maturity_date_res=tgl_indo($maturity_date_res);


} else{
	$saldo_simpan=0;
		
	//Hitung Restruturisasai
			$os_res=$os - $saldo_simpan;
			
			$pokok=$os_res/$tenor;
			$angsuran_res=$pokok;
  $totalharga = $angsuran_res;
			$totalharga=ceil(compat_num($totalharga));
                            if (substr($totalharga,-3)>499){
                                $bulat=round(compat_num($totalharga),-3);
                            } else {
                                $bulat=round(compat_num($totalharga),-3)+1000;
                            } 


$tgl_wakal    = strtotime($tanggal);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_res =  date("Y-m-d", strtotime("+$tenor WEEK", $tgl_m));

$maturity_date_res=tgl_indo($maturity_date_res);
}
break;
case "03";

break;
case "04";

$os_res=$os;

  $bulat = $angsuran;


$tgl_wakal    = strtotime($mat);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_ress =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));


$cek_tanggal= mysql_query ("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between '$mat' and '$maturity_date_ress' and Hari ='$harii'");
        while($data_libur = mysql_fetch_array($cek_tanggal)){
            $libur =$data_libur['libur'];
           
           
        }

if($libur>0){
$ft =$ft+$libur;

$tgl_wakal    = strtotime($mat);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));



}else{
$maturity_date_res=$maturity_date_ress;
}

$maturity_date_res=tgl_indo($maturity_date_res);

break;
default:
}
			
		



			$ter_res=ucwords (Terbilang($os_res));
			

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
$pdf->multicell(0,5,'Demikian ADENDUM ini dibuat dan ditanda tangani di '. $d.' pada hari '.$hari_res.' tanggal '.$tgl_res.' ',0,'J',1);
$pdf->Ln(4);
$pdf->cell(70,5,'PIHAK PERTAMA ',0,'R',1);
$pdf->cell(70,5,'',0,'R',1);

$pdf->Ln(30);
$pdf->SetFont ('Arial','U',11);
$pdf->cell(70,5,'( '.$nama_um .$tutup,0,'C',1);
$pdf->Ln(4);

$pdf->ln (52);

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
