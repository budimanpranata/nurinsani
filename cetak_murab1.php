<?php require_once __DIR__ . '/php8_compat.php';  
//error_reporting(0);
session_start();  
ob_start();  
$userid=$_SESSION['userid'];
//cek apakah user sudah login

include "conn2.php";
include "function.php";
$koneksi=open_connection2();
$code_kel = $_GET['code_kel'];  

$buss_date=date("y-m-d");
$buss_date1=getdate();
$today=date("Y-m-d");
$date=tgl_indo($today);

$kel= mysql_query(" Select cao from kelompok 
 where code_kel='$code_kel'");
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

$pot = mysql_query("select calon_loan.buss_date,calon_loan.Cust_Short_name,Plafond,bagi_hasil,Tenor,angsuran,hari,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,WARIS,bidang_usaha.nama_usaha as nusaha,NOREK,tgl_murab,tgl_wakalah,tgl_akad,maturity_date,calon_loan.nama_usaha,ID_NUMBER, bulat from calon_loan 
inner join anggota on anggota.cif=calon_loan.cif 
left join bidang_usaha on bidang_usaha.code_bidang=calon_loan.usaha 
where calon_loan.cao='$cao' and calon_loan.code_kel='$code_kel' order by calon_loan.code_kel");
$page=0;
while($p = mysql_fetch_array($pot)){
$page++;
$nama=$p['Cust_Short_name'];
$plafond=$p['Plafond'];
$pla=number_format (compat_num($p['Plafond']));
$terang=ucwords (Terbilang($plafond));
$tenor =$p['Tenor'];
$taawunn = $tenor * 2500;

$bagihasil=$p['bagi_hasil'];
$angsuran =number_format (compat_num($p['bulat']));
$ter_angsuran=ucwords (Terbilang($p['bulat']));
$untung =$p['bagi_hasil'];

$bagi = $bagihasil - $taawunn; 
$margin = number_format (compat_num($bagi));
$ter_margin=ucwords (Terbilang($bagi));
$ktp=$p['ID_NUMBER'];

$usahaa=$p['nama_usaha'];
$busaha=$p['nusaha'];
$usaha="$busaha $usahaa"; 
$a=$p['HADDRESS1'];
$b=$p['HADDRESS2'];
$c=$p['HADDRESS3'];
$d=$p['HADDRESS4'];
$buss_date=tgl_indo ($p['buss_date']);
$waris=$p['WARIS'];
$tgl_akad=tgl_indo($p['tgl_akad']);
$tgl_murab=tgl_indo($p['tgl_murab']);
$hari=namahari($p['tgl_murab']);
$hari_akad=namahari($p['tgl_akad']);
$maturity=tgl_indo($p['maturity_date']);
$tgl_wakalah=tgl_indo($p['tgl_wakalah']);
$alamat="$a $b $c $d";
$ta ="'";
$tutup=")";

$taawun=number_format(compat_num($taawunn));
$piutangg =$plafond + $untung - $taawunn;
$piutang=number_format(compat_num($piutangg));
$ter_piutang=ucwords (Terbilang($piutangg));

$ter_taawun=ucwords (Terbilang($taawunn));
 do {
$pdf->setX(20);
$pdf->setFillColor(255,255,255);
//$pdf-> Write (12,'User ID: ');
$pdf->SetFont ('Arial','BU',15);
$pdf->multicell(0,5,'PERJANJIAN JUAL BELI (AL MURABAHAH) ',0,'C',1);
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
$pdf->multicell(0,5,'Dengan memohon petunjuk dan Ridho Allah SWT, pada Hari ini '.$hari_akad.' tangggal '.$tgl_akad.' yang bertanda tangan di bawah ini : ',0,'J',1);
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
$pdf->multicell(0,5,'Selanjutnya, Para Pihak sepakat untuk membuat dan menandatangani Akad Murabahah (selanjut nya disebut "Akad") ini untuk dapat dan dilaksanakan dengan syarat dan ketentuan sebagai berikut :',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 1',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'KETENTUAN POKOK AKAD',0,'C',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'1. Ketentuan-ketentuan pokok Akad ini meliputi sebagai berikut :',0,'J',1);
$pdf->Ln(5);
$pdf->cell(70,5,'a. Barang (Object Murabahah) ',0,'L',1);
$pdf->cell(20,5,': (Diuraikan dalam Tanda Terima Barang) ',0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'b. Harga Beli ',0,'R',1);
$pdf->cell(28,5,': Rp. '.$pla,0,'R',1);
$pdf->cell(20,5,' ('.$terang .$tutup,0,'R',1);

$pdf->Ln(5);
$pdf->cell(70,5,'c. Margin Keuntungan ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,' Rp. ',0,'R',1);
$pdf->cell(15,5,''.$margin,0,'R',1);
$pdf->cell(20,5,' ('.$ter_margin .$tutup,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,"d. Ta'awun ",0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,' Rp. ',0,'R',1);
$pdf->cell(15,5,''.$taawun,0,'R',1);
$pdf->cell(20,5,' ('.$ter_taawun .$tutup,0,'R',1);
$pdf->Ln(5);

$pdf->cell(70,5,'e. Harga Jual/Piutang Murabahah ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(6,5,' Rp. ',0,'R',1);
$pdf->cell(18,5,''.$piutang,0,'R',1);
$pdf->cell(20,5,' ('.$ter_piutang .$tutup,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'f. Setoran ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,' Rp. ',0,'R',1);
$pdf->cell(15,5,''.$angsuran,0,'R',1);
$pdf->cell(20,5,' ('.$ter_angsuran .$tutup,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'g. Jatuh tempo Pembayaran Setoran ',0,'R',1);
$pdf->cell(20,5,': Setiap hari '.$hari,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'h. Jangka Waktu Pembayaran',0,'L',1);
$pdf->cell(15,5,': '.$tenor,0,'L',1);
$pdf->cell(18,5,'Minggu',0,'L',1);
$pdf->Ln(5);
$pdf->cell(70,5,'i. Jatuh Tempo Pembiayaan',0,'L',1);
$pdf->cell(15,5,': Tanggal '.$maturity,0,'L',1);
$pdf->Ln(5);
$pdf->MultiCell(0, 5, "Setoran mingguan terdiri atas porsi pemgembalian pokok,margin pihak pertama, dana ta'awun, dan tabungan wajib mingguan ", 0);
$pdf->MultiCell(0, 5, "2. Ta'awun adalah dana bersama yang dikelola oleh Pihak Pertama untuk kemaslahatan anggota ", 0);
$pdf->Ln(4);

$pdf->Ln(30);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 2',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'BARANG',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Barang sebagaimana dimaksud Pasal 1 Akad ini dipesan oleh PIHAK KEDUA dengan spesifikasi sebagaimana diuraikan dalam Tanda Terima Barang pada dan karenanya menjadi satu kesatuan yang tidak terpisahkan dari Akad ini yang diadakan oleh PIHAK PERTAMA untuk dijual kepada PIHAK KEDUA.',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 3',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'PELAKSANAAN PRINSIP MURABAHAH',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Pelaksanaan prinsip Murabahah yang berlangsung antara PIHAK PERTAMA sebagai penjual dan Pihak kedua sebagai pembeli dilaksanakan berdasarkan ketentuan syariah dan diatur menurut ketentuan-ketentuan dan persyaratan sebagai berikut :',0,'J',1);
$pdf->multicell(0,5,'1. PIHAK KEDUA membutuhkan barang dan meminta kepada PIHAK PERTAMA untuk menyediakan pembiayaan Murabahah',0,'J',1);
$pdf->multicell(0,5,'2. PIHAK PERTAMA menyediakan fasilitas pembiayaan Murabahah sesuai dengan surat Permohonan Pembiayaan PIHAK KEDUA ',0,'J',1);
$pdf->multicell(0,5,'3. PIHAK KEDUA membeli barang dan setuju serta mengikatkan diri untuk membayar sebesar harga Jual Barang sesuai kesepakatan Akad ini',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 4',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'SYARAT JUAL BELI',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'PIHAK PERTAMA dapat merealisasikan pembiayaan apabila PIHAK KEDUA telah memenuhi ketentuan sebagai berikut :',0,'J',1);
$pdf->multicell(0,5,'1. PIHAK KEDUA telah menyerahkan kepada PIHAK PERTAMA semua persyaratan yang diminta; ',0,'J',1);
$pdf->multicell(0,5,'2. PIHAK PERTAMA dan PIHAK KEDUA telah menandatangani perjanjian ini; ',0,'J',1);
$pdf->Ln(4);
$pdf->multicell(0,5,'Selanjutnya PIHAK KEDUA dengan ini mengakui dengan sebenarnya dan secara sah menerima barang tersebut diatas, dengan ini menyatakan secara sah berutang kepada PIHAK PERTAMA sejumlah harga jual barang yang telah disepakati.',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 5',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'JANGKA WAKTU',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Perjanjian Jual beli ini diberikan untuk jangka waktu 25(Dua Puluh Lima) minggu, terhitung sejak tanggal '.$tgl_murab.' sehingga berakhir pada tanggal '.$maturity.' ',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 6',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'KETENTUAN TAMBAHAN',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Hal-hal lain yang belum cukup diatur dalam Perjanjian ini, akan diatur berdasarkan kesepakatan para pihak kedalam surat/akta yang merupakan satu kesatuan dengan perjanjian ini  ',0,'J',1);
$pdf->multicell(0,5,'Demikian Perjanjian ini dibuat dan ditanda tangani di '. $d.' pada hari '.$hari_akad.' tanggal '.$tgl_akad.' ',0,'J',1);
$pdf->Ln(4);
$pdf->cell(70,5,'PIHAK PERTAMA ',0,'R',1);
$pdf->cell(70,5,'',0,'R',1);
$pdf->cell(6,5,'PIHAK KEDUA',0,'R',1);
$pdf->Ln(30);
$pdf->SetFont ('Arial','U',11);
$pdf->cell(70,5,'( '.$nama_um .$tutup,0,'C',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','',11);
$pdf->cell(70,5,'Marketing Manager',0,'C',1);
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
