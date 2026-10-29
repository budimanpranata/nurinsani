<?php require_once __DIR__ . '/koneksi_db.php';  
//error_reporting(0);
session_start();  
ob_start();  
$userid=$_SESSION['userid'];
//cek apakah user sudah login

include "conn2.php";
include "function.php";
$koneksi=open_connection2();
$code_kel = $_GET['code_kel'];  



$datakel = db_satu(" Select cao from kelompok 
 where code_kel=?", 's', $code_kel);
$cao=$datakel['cao'];

$ao = db_satu(" Select person.Nama as um,person.jabatan as jabatan,cao from ao 
inner join person on ao.atasan = person.NIK where cao=?", 's', $cao);
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

$pot = db_semua("select calon_loan.buss_date,calon_loan.Cust_Short_name,norek,Plafond,bagi_hasil,Tenor,angsuran,hari,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,WARIS,no_anggota,tgl_murab,tgl_wakalah,tgl_akad,maturity_date,calon_loan.nama_usaha,ID_NUMBER, bulat from calon_loan 
left join anggota on anggota.cif=calon_loan.cif 
left join bidang_usaha on bidang_usaha.code_bidang=calon_loan.usaha 
where calon_loan.cao=? and calon_loan.code_kel=? order by calon_loan.code_kel", 'ss', $cao, $code_kel);
$page=0;
foreach ($pot as $p){
$page++;
$nama=$p['Cust_Short_name'];
$plafond=$p['Plafond'];
$norek=$p['no_anggota'];
$pla=number_format (compat_num($p['Plafond']));
$terang=ucwords (Terbilang($plafond));
$tenor =$p['Tenor'];
$taawunn = $tenor * 2500;

$angsuran =number_format (compat_num($p['angsuran'] + (2500)));
$angsuran =number_format (compat_num($p['bulat']));
$ter_angsuran=ucwords (Terbilang($p['bulat']));
$untung =$p['bagi_hasil'];

$margin = number_format (compat_num($p['bagi_hasil']-($taawunn)));
$ter_margin=ucwords (Terbilang($p['bagi_hasil']-($taawunn)));
$ktp=$p['ID_NUMBER'];
$usaha=$p['nama_usaha'];
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
$piutangg =$plafond + $untung - $taawunn;
$piutang=number_format(compat_num($piutangg));
$ter_piutang=ucwords (Terbilang($piutangg));
$taawun=number_format(compat_num($taawunn));
$ter_taawun=ucwords (Terbilang($taawunn));

 do {
$pdf->setX(20);
$pdf->setFillColor(255,255,255);
//$pdf-> Write (12,'User ID: ');
$pdf->SetFont ('Arial','BU',15);
$pdf->multicell(0,5,'PEMBIAYAAN AKAD MURABAHAH',0,'C',1);
$pdf->SetFont ('Arial','B',13);
$pdf->multicell(0,5,'NO : '.$norek.'',0,'C',1);

$pdf->SetFont ('Arial','BI',9);
$pdf->multicell(0,5,'===================================================================================================== ',0,'J',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Dengan memohon petunjuk dan Ridho Allah SWT, pada Hari ini '.$hari_akad.' tangggal '.$tgl_akad.' yang bertanda tangan di bawah ini : ',0,'J',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'I.	Nama '.$nama_um.', jabatan '.$jabatan.' dalam hal ini bertindak untuk dan atas nama Koperasi Simpan Pinjam dan Pembiayaan Syariah (KSPPS) Nur Insani berkedudukan di Kota Tangerang Selatan berdasarkan Akta Pendirian No. 02 tanggal 7 Mei 2011 melalui notaries Neilly Iralita Iswari, SH, MSi, MKn dan telah memperoleh persetujuan Kementerian Koperasi dan Usaha Kecil dan Menengah No. 1024/BH/M.KUM.2/XI/2011 serta perubahan-perubahannya, selanjutnya disebut sebagai PIHAK PERTAMA.',0,'J',1);
$pdf->Ln(10);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'II. Nyonya '.$nama.' bertempat tinggal di '.$alamat.',  pemegang KTP  dengan NIK '.$ktp.'  Selanjutnya disebut sebagai PIHAK KEDUA.',0,'J',1);
$pdf->Ln(5);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'PIHAK PERTAMA dan PIHAK KEDUA selanjutnya bersama-sama disebut '.$ta.'Para Pihak'.$ta.', sepakat untuk membuat dan menandatangani Pembiayaan Akad Murabahah (selanjutnya disebut '.$ta.'Akad'.$ta.') dengan syarat dan ketentuan sebagai berikut:',0,'J',1);
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
$pdf->cell(70,5,'c. Keuntungan ',0,'R',1);
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
$pdf->cell(70,5,'f.	Jumlah Setoran Mingguan',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,' Rp. ',0,'R',1);
$pdf->cell(15,5,''.$angsuran,0,'R',1);
$pdf->cell(20,5,' ('.$ter_angsuran .$tutup,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'g. Hari Pembayaran Angsuran ',0,'R',1);
$pdf->cell(20,5,': Setiap hari '.$hari,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'h. Jangka Waktu Pembiayaan',0,'L',1);
$pdf->cell(15,5,': '.$tenor,0,'L',1);
$pdf->cell(18,5,'Minggu',0,'L',1);
$pdf->Ln(5);
$pdf->cell(70,5,'i. Tanggal Jatuh Tempo Pembiayaan',0,'L',1);
$pdf->cell(15,5,': Tanggal '.$maturity,0,'L',1);
$pdf->Ln(5);
$pdf->MultiCell(0, 5, "Setoran mingguan terdiri atas porsi pemgembalian pokok,margin pihak pertama, dana ta'awun, dan tabungan wajib mingguan ", 0);
$pdf->MultiCell(0, 5, "2. Ta'awun adalah dana bersama yang dikelola oleh Pihak Pertama untuk kemaslahatan anggota ", 0);
$pdf->Ln(5);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 2',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'BARANG',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Barang sebagaimana dimaksud Pasal 1 huruf a. pada Akad ini diuraikan dalam Tanda Terima Barang yang menjadi satu kesatuan yang tidak terpisahkan dari Akad ini.',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 3',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'PELAKSANAAN PRINSIP MURABAHAH',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Pelaksanaan prinsip Murabahah yang berlangsung antara PIHAK PERTAMA sebagai Penjual dengan PIHAK KEDUA sebagai Pembeli dilaksanakan sebagai berikut:',0,'J',1);
$pdf->multicell(0,5,'1. PIHAK KEDUA meminta kepada PIHAK PERTAMA untuk menyediakan barang.',0,'J',1);
$pdf->multicell(0,5,'2. PIHAK PERTAMA menyediakan barang sesuai dengan kebutuhan PIHAK KEDUA.',0,'J',1);
$pdf->multicell(0,5,'3. PIHAK PERTAMA menjual barang kepada PIHAK KEDUA sebesar harga pokok ditambah keuntungan sebagaimana diterangkan pada pasal 1 Akad ini.',0,'J',1);
$pdf->multicell(0,5,'4. PIHAK KEDUA membeli dengan membayar secara angsuran sebesar Harga Jual sesuai kesepakatan yang diterangkan pada Pasal 1 Akad ini.',0,'J',1);
$pdf->Ln(4);
$pdf->Ln(4);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 4',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'SYARAT JUAL BELI',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'PIHAK PERTAMA dapat merealisasikan Akad apabila PIHAK KEDUA telah memenuhi ketentuan sebagai berikut :',0,'J',1);
$pdf->multicell(0,5,'1.	PIHAK KEDUA telah menyerahkan semua persyaratan yang diminta kepada PIHAK PERTAMA;',0,'J',1);
$pdf->multicell(0,5,'2.	PIHAK PERTAMA  dan PIHAK KEDUA telah menandatangani perjanjian ini;',0,'J',1);
$pdf->Ln(4);
$pdf->multicell(0,5,'Selanjutnya PIHAK KEDUA dengan ini mengakui dengan sebenarnya dan secara sah menerima barang tersebut dan menyatakan diri secara sah berutang kepada PIHAK PERTAMA sejumlah harga jual barang yang telah disepakati.',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 5',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'JANGKA WAKTU',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Akad ini diberikan untuk jangka waktu '.$tenor.' minggu, terhitung sejak tanggal '.$tgl_murab.' sehingga berakhir pada tanggal '.$maturity.' ',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 6',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'KUASA',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'PIHAK KEDUA memberi kuasa kepada PIHAK PERTAMA untuk mengurangi simpanannya yang ada pada KSPPS Nur Insani untuk memenuhi seluruh kewajibannya.'.$maturityy.' ',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 7',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'PERNYATAAN',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'PIHAK KEDUA menyatakan dengan sukarela dan kesadaran sendiri untuk :',0,'J',1);
$pdf->multicell(0,5,'1.	Akan membayar angsuran tepat waktu sesuai tempat dan waktu yang telah disepakati.',0,'J',1);
$pdf->multicell(0,5,'2.	Tidak menggunakan pembiayaan ini untuk kepentingan pihak lain. Apabila dikemudian hari terbukti digunakan pihak lain,akan tetap bertanggung jawab membayar angsuran tepat waktu.',0,'J',1);
$pdf->multicell(0,5,'3.	Tidak melibatkan pihak luar (LSM, Ormas atau sejenisnya) untuk tidak membayar angsuran.',0,'J',1);
$pdf->multicell(0,5,'Apabila hal diatas tidak dipenuhi, maka PIHAK KEDUA bersedia diiaporkan kepada pihak berwajib sesuai undang-undang yang berlaku.',0,'J',1);
$pdf->Ln(4);

$pdf->SetFont ('Arial','BU',11);
$pdf->multicell(0,5,'PASAL 8',0,'C',1);
$pdf->SetFont ('Arial','B',11);
$pdf->multicell(0,5,'KETENTUAN TAMBAHAN',0,'C',1);
$pdf->SetFont ('Arial','',11);
$pdf->multicell(0,5,'Hal-hal lain yang belum cukup diatur dalam perjanjian ini, akan diatur berdasarkan kesepakatan PARA PIHAK ke dalam surat/akta yang merupakan satu kesatuan dengan perjanjian ini.',0,'J',1);
$pdf->multicell(0,5,'Demikian Perjanjian ini dibuat dan ditandatangani di '. $d.' pada hari '.$hari_akad.' tanggal '.$tgl_akad.' ',0,'J',1);
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

//$pdf->Ln(4);
$pdf->SetFont ('Arial','U',11);
$pdf->cell(70,5,'',0,'C',1);
$pdf->Ln(4);
$pdf->SetFont ('Arial','',11);
$pdf->cell(70,5,'Ketua Kelompok',0,'C',1);
$pdf->cell(70,5,'',0,'R',1);
$pdf->cell(6,5,'',0,'C',1);

$pdf->ln (52);
$pdf->ln (37);
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
