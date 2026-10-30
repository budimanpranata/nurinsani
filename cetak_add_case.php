<?php require_once __DIR__ . '/koneksi_db.php';  
//error_reporting(0);
session_start();  
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
ob_start();  

$userid=$_SESSION['userid'];
$pilih=$_SESSION['spilih'];
$wadiah=$_SESSION['swadiah'];
$nominal=$_SESSION['snominal'];

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

$kel = db_semua(" Select cao from loan 
 where cif=?", 's', (string) $cif);
$datakel = $kel ? $kel[0] : false;
$cao=$datakel['cao'];

$dataao = db_semua(" Select person.Nama as um,person.jabatan as jabatan,cao from ao 
inner join person on ao.atasan = person.NIK where cao=?", 's', (string) $cao);
$ao = $dataao ? $dataao[0] : false;
$nama_um=$ao['um'];
$jabatan=$ao['jabatan'];

 $pbiaya=db_satu("select margin,tab,pla,jw  FROM param_biaya where jw =? and pla=?", 'ss', (string) $tenor, (string) $nominal) or die(mysql_error());
						
						$param_margin=$pbiaya['margin'];
						$param_tabung=$pbiaya['tab'];
						$param_tenor=$pbiaya['jw'];
						$param_pla=$pbiaya['pla'];
						
//$pdf=new FPDF ('P','mm','A4');
//$pdf=new FPDF('P','in',array(15,12)); 
require ('fpdf17/fpdf.php');
//$pdf=new FPDF('P','mm', array(216, 275));
$pdf=new FPDF ('P','mm','A4');

$pdf->addPage();

$pot = db_semua("select loan.buss_date,loan.Cust_Short_name,Plafond,bagi_hasil,Tenor,angsuran,hari,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,WARIS,NOREK,tgl_murab,tgl_wakalah,tgl_akad,maturity_date,loan.nama_usaha,
saldo_margin,ijaroh,angsuran,pokok,os,angsuran,bulat,tgl_akad,(os-saldo_margin) as saldo_os,nama_kel,
ID_NUMBER from loan 
left join anggota on anggota.cif=loan.cif 
left join kelompok on kelompok.code_kel = loan.code_kel
left join bidang_usaha on bidang_usaha.code_bidang=loan.usaha 
where loan.cao=? and loan.cif=? order by loan.cif", 'ss', (string) $cao, (string) $cif);

$row_saldo=db_satu("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif =? and type not in('04','05') ", 's', (string) $cif) or die(mysql_error());
       $saldo_simpanan=$row_saldo['saldo_simpanan'];     
$saldo_simpan=$saldo_simpanan;


						

$page=0;
foreach ($pot as $p){
$page++;
$nama=$p['Cust_Short_name'];
$plafond=$p['Plafond'];
$os=$p['os'];
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
$tgl_akad=tgl_indo($p['tgl_murab']);
$tgl_murab=tgl_indo($p['tgl_murab']);
$hari=namahari($p['tgl_murab']);
$hari_akad=namahari($p['tgl_murab']);
$tgl_res=tgl_indo($tanggal);
$hari_res=namahari($tanggal);
$maturity=tgl_indo($p['maturity_date']);
$tgl_wakalah=tgl_indo($p['tgl_wakalah']);
$alamat="$a $b $c $d";
$ta ="'";
$tutup=")";
$piutangg =$plafond + $untung;
$piutang=number_format(compat_num($piutangg));
$ter_piutang=ucwords (Terbilang($piutangg));
$saldo_margin=$p['saldo_margin'];

if($wadiah =='ya'){
	$saldo_simpan=$saldo_simpan;

} else{
	$saldo_simpan=0;
}
		
		$pokok_os=$p['saldo_os'];

$tgl_wakal    = strtotime($tanggal);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_res =  date("Y-m-d", strtotime("+$tenor WEEK", $tgl_m));

$maturity_date_res=tgl_indo($maturity_date_res);
//Hitung Restruturisasai

  $totalharga = $angsuran_res;

$pokok=compat_bagi($nominal, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
						$margin=$nominal * ($param_margin/100);
						$ijaroh=compat_bagi($margin, $tenor); // PHP 5: pembagi 0/kosong -> false, bukan error
						$angsuran_res=$ijaroh+$pokok;
						$bulat =$param_tabung+$angsuran_res;
						$os_r =$nominal + $margin;
						$ter_res=ucwords (Terbilang($nominal + $margin));
						


 do {
$pdf->setX(20);
$pdf->setFillColor(255,255,255);
//$pdf-> Write (12,'User ID: ');
$pdf->SetFont ('Arial','BU',15);
$pdf->multicell(0,5,'ADENDUM PERJANJIAN JUAL BELI (AL MURABAHAH) ',0,'C',1);
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
$pdf->multicell(0,5,'Mengacu pada Perjanjian Jual Beli (Al Murabahah) pada tanggal '.$tgl_akad.' pada kelompok '.$nama_kel.' mempertimbangkan adanya kesepakatan ulang antara kedua belah pihak bersepakat untuk membuat Adendum Perjanjian jual beli. Dengan memohon petunjuk dan Ridho Allah SWT, pada hari ini yang bertanda tangan di bawah ini : ',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','B',11);
$pdf->cell(25,5,'Nama',0,'L',1);
$pdf->cell(20,5,': '.$nama_um,0,'R',1);
$pdf->Ln(4);
$pdf->cell(25,5,'Jabatan',0,'R',1);
$pdf->cell(25,5,': '.$jabatan,0,'R',1);
$pdf->Ln(5);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Dalam hal ini bertindak untuk dan atas nama Koperasi Simpan Pinjam Syariah (KSPPS) Nur Insani berkedudukan di jakarta, berdasarkan akte pendirian KJKS No : 02 Tanggal 07 Mei 2011 dihadapan notaris Neilly Iralita Iswari, SH, Msi, MKn. dan telah memperoleh persetujuan kementrian Koperasi dan Usaha Kecil dan Menengah No. 1024/BH/MKUM2/XI/2011 serta perubahan-perubahannya, selanjutnya disebut PIHAK PERTAMA.',0,'J',1);
$pdf->Ln(2);
$pdf->SetFont ('Arial','B',11);
$pdf->cell(25,5,'Nama',0,'L',1);
$pdf->cell(20,5,': '.$nama,0,'R',1);
$pdf->Ln(4);
$pdf->cell(25,5,'Nomor KTP',0,'L',1);
$pdf->cell(20,5,': '.$ktp,0,'R',1);
$pdf->Ln(4);
$pdf->cell(25,5,'Usaha',0,'L',1);
$pdf->cell(20,5,': '.$usaha,0,'R',1);
$pdf->Ln(4);
$pdf->cell(25,5,'ALamat',0,'R',1);
$pdf->cell(25,5,': '.$alamat,0,'R',1);
$pdf->Ln(10);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Selanjutnya disebut PIHAK KEDUA.',0,'J',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Dalam hal ini telah mendapatkan persetujuan dari suami/istri sebagaimana ikut serta menanda-tangani perjanjian ini.',0,'J',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'PIHAK PERTAMA dan PIHAK KEDUA selanjutnya bersama-sama disebut "PARA PIHAK", menerangkan sebagai berikut : ',0,'J',1);
$pdf->multicell(0,5,'I. Bahwa PIHAK KEDUA telah mengajukan pembiayaan untuk pembelian barang sesuai dengan surat Permohonan pembiayaan pada tanggal '.$tgl_wakalah.'',0,'J',1);
$pdf->Ln(4);
$pdf->multicell(0,5,'II. Bahwa PIHAK PERTAMA telah menyatakan persetujuan untuk memberikan Pembiayaan Murabahah kepada PIHAK KEDUA ',0,'J',1);
$pdf->multicell(0,5,'Selanjutnya, Para Pihak sepakat untuk membuat dan menandatangani perubahan Akad Murabahah (selanjutnya disebut "Adendum") ini untuk dapat dan dilaksanakan dengan syarat dan ketentuan sebagai berikut :',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 1',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'KETENTUAN POKOK AKAD',0,'C',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Ketentuan-ketentuan pokok Akad ini meliputi sebagai berikut :',0,'J',1);
$pdf->Ln(5);
$pdf->cell(70,5,'a. Sisa Piutang (pokok+margin) ',0,'R',1);
$pdf->cell(28,5,': Rp. '.$os,0,'R',1);
$pdf->cell(20,5,' ('.$terang .$tutup,0,'R',1);

$pdf->Ln(5);
$pdf->cell(70,5,'b. Angsuran ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,' Rp. ',0,'R',1);
$pdf->cell(15,5,''.$angsuran,0,'R',1);
$pdf->cell(20,5,'per minggu',0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'c. Jangka Waktu Pembiayaan  ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(6,5,' ',0,'R',1);
$pdf->cell(18,5,''.$tenor,0,'R',1);
$pdf->cell(20,5,'Minggu',0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'d. Jatuh Tempo Pembiayaan',0,'L',1);
$pdf->cell(15,5,': Tanggal '.$maturity,0,'L',1);
$pdf->Ln(5);
$pdf->Ln(30);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 2',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'PERUBAHAN KETENTUAN POKOK AKAD',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'PIHAK KEDUA mengajukan perubahan ketentuan pokok akad sebagai berikut :',0,'J',1);
$pdf->Ln(5);
$pdf->cell(70,5,'a. Sisa Piutang (pokok+margin) ',0,'R',1);
$pdf->cell(28,5,': Rp. '.number_format(compat_num($os_r)),0,'R',1);
$pdf->cell(20,5,' ('.$ter_res .$tutup,0,'R',1);

$pdf->Ln(5);
$pdf->cell(70,5,'b. Angsuran ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,' Rp. ',0,'R',1);
$pdf->cell(15,5,''.number_format(compat_num($bulat)),0,'R',1);
$pdf->cell(20,5,'per minggu',0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'c. Jangka Waktu Pembiayaan  ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(6,5,' ',0,'R',1);
$pdf->cell(18,5,''.$tenor,0,'R',1);
$pdf->cell(20,5,'Minggu',0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'d. Jatuh Tempo Pembiayaan',0,'L',1);
$pdf->cell(15,5,': Tanggal '.$maturity_date_reshj,0,'L',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 3',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'KETENTUAN TAMBAHAN',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Hal-hal lain yang belum cukup diatur dalam Perjanjian ini, akan diatur berdasarkan kesepakatan para pihak kedalam surat/akad yang merupakan satu kesatuan dengan perjanjian ini ',0,'J',1);
$pdf->multicell(0,5,'Demikian Perjanjian ini dibuat dan ditanda tangani di '. $d.' pada hari '.$hari_res.' tanggal '.$tgl_res.' ',0,'J',1);
$pdf->Ln(4);
$pdf->cell(70,5,'PIHAK PERTAMA ',0,'R',1);
$pdf->cell(70,5,'',0,'R',1);
$pdf->cell(6,5,'PIHAK KEDUA',0,'R',1);
$pdf->Ln(30);
$pdf->SetFont ('Arial','U',11);
$pdf->cell(70,5,'( '.$nama_um .$tutup,0,'C',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','',11);
$pdf->cell(70,5,'Unit Manager',0,'C',1);
$pdf->cell(70,5,'',0,'R',1);
$pdf->cell(6,5,'('.$nama .$tutup, 0,'C',1);
$pdf->Ln(5);
$pdf->multicell(170,5,'SAKSI',0,'C',1);
$pdf->Ln(8);
//$pdf->Ln(4);
$pdf->SetFont ('Arial','U',11);
$pdf->cell(70,5,'',0,'C',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','',11);
$pdf->cell(70,5,'Ketua Kelompok',0,'C',1);
$pdf->cell(70,5,'',0,'R',1);
$pdf->cell(6,5,'( '.$waris .$tutup,0,'C',1);
$pdf->Ln(5);
$pdf->cell(140,5,'',0,'R',1);
$pdf->cell(70,5,'Suami/Penjamin',0,'C',1);
$pdf->Ln(0);
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
