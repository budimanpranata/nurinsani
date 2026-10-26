<?php require_once __DIR__ . '/php8_compat.php';  
//error_reporting(0);
session_start();  
ob_start();  
$tgl_mrb=$_SESSION['smrb'];
$userid=$_SESSION['userid'];
//cek apakah user sudah login

include "conn2.php";
include "indo_tgl.php";

$koneksi=open_connection2();
$unit = $userid;  

$buss_date=date("y-m-d");
$buss_date1=getdate();
$today=date("Y-m-d");
$date=tgl_indo($today);

$ckel= mysql_query(" Select cao  from kelompok where code_unit='$unit'");
$cckel= mysql_fetch_array($ckel);
$cao=$cckel['cao'];


$dataum= mysql_query(" Select Nama as um,jabatan from person  
where unit='$unit'");
$um= mysql_fetch_array($dataum);
$nama_um=$um['um'];
$jabatan=$um['jabatan'];

//$query = mysql_query("SELECT COUNT(cao) as counter FROM calon_loan WHERE cao='$cao'");
//$check = mysql_fetch_array($query);
//$feed=$check['counter'];

//$pdf=new FPDF ('P','mm','A4');
//$pdf=new FPDF('P','in',array(15,12)); 
require ('fpdf17/fpdf.php');
$pdf=new FPDF('P','mm', array(241,279.4));
//$pdf=new FPDF ('P','mm','A4');
$pdf->addPage();

$pot = mysql_query("select calon_loan.cif as CIF, calon_loan.Cust_Short_name,tgl_wakalah,Plafond,bagi_hasil,Tenor,angsuran,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,month(tgl_wakalah) as bulan,year(tgl_wakalah) as tahun, day(tgl_wakalah) as hari
 from calon_loan left join anggota on anggota.cif = calon_loan.cif where calon_loan.unit='$userid' and calon_loan.status_app='PENDING' and tgl_murab='$tgl_mrb' order by calon_loan.code_kel,calon_loan.cust_Short_name");
$page=0;
while($p = mysql_fetch_array($pot)){
$page++;
$nama=$p['Cust_Short_name'];
$plafond=$p['Plafond'];
$terang=ucwords (Terbilang($plafond));
$pla=number_format (compat_num($p['Plafond']));
$tenor =$p['Tenor'];
$cif =$p['CIF'];
$hari=$p['hari'];
$bulan=$p['bulan'];
$tahun=$p['tahun'];
$tahun=substr($tahun,2);
$nomor="$unit.$cif.$hari$bulan$tahun";
$angsuran =number_format (compat_num($p['angsuran']));
$untung =$p['bagi_hasil'];
$margin = number_format (compat_num($p['bagi_hasil']));
$alamat1=$p['HADDRESS1'];
$alamat2=$p['HADDRESS2'];
$alamat3=$p['HADDRESS3'];
$alamat4=$p['HADDRESS4'];
$alamat ="$alamat1 $alamat2 $alamat3 $alamat4";
$tgl_wakalah=$p['tgl_wakalah'];
$tgl_wakalah=tgl_indo($tgl_wakalah);
$piutang =$plafond + $untung;

 do {


$pdf->SetFont ('Arial','',10);
$pdf->setTopMargin(12);
$pdf->setLeftMargin(12);
$pdf->setRightMargin(12);
$pdf->setAutoPageBreak(true);
$pdf->setFillColor(255,255,255);
$pdf->cell(74,5,' ',0,'L',1);
$pdf->Image('Image/nii.png');
//$pdf-> Write (12,'User ID: ');
$pdf->Ln(2);
$pdf->multicell(0,5,'PERJANJIAN PEMBERIAN KUASA (WAKALAH)',0,'C',1);
$pdf->multicell(0,5,'Nomor : '.$nomor.'',0,'C',1);
$pdf->Ln(3);
$pdf->multicell(0,5,'Perjanjian Pemberian Kuasa (Wakalah) (selanjutnya disebut Perjanjian) dibuat dan ditandatangani pada tanggal '.$tgl_wakalah.' oleh dan antara :',0,'J',1);
$pdf->Ln(2);
$pdf->cell(5,5,'I.',0,'L',1);
$pdf->multicell(0,5,'Nama '.$nama_um.' Jabatan '.$jabatan.' Dalam hal ini bertindak untuk dan atas nama KSPPS Nur Insani berkedudukan di Jakarta berdasarkan Surat Ijin Usaha Simpan Pinjam No.276/SISP/Dept.1/XI/2015 dan pengesahaan Akta Pendirian Koperasi No.1024/BH/M.KUM.2/XI/2011, selanjutnya disebut PIHAK PERTAMA.',0,'J',1);
$pdf->Ln(2);
$pdf->cell(5,5,'II.',0,'L',1);
$pdf->multicell(0,5,'NYONYA '.$nama.' bertempat tinggal di '.$alamat.' selanjutnya disebut PIHAK KEDUA',0,'J',1);
//$pdf->Ln(5);
//$pdf->cell(5,5,'',0,'L',1);
//$pdf->cell(5,5,'selanjutnya disebut PIHAK KEDUA.',0,'L',1);
$pdf->Ln(2);
$pdf->multicell(0,5,'Selanjutnya PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama disebut "PARA PIHAK"',0,'J',1);
$pdf->Ln(2);
$pdf->multicell(0,5,'Para Pihak ini terlebih dahulu menerangkan sebagai berikut :',0,'J',1);
$pdf->Ln(2);
$pdf->cell(5,5,'-.',0,'L',1);
$pdf->multicell(0,5,'Bahwa PIHAK KEDUA mengajukan Permohonan Pembiayaan kepada PIHAK PERTAMA.',0,'J',1);
$pdf->Ln(2);
$pdf->cell(5,5,'-.',0,'L',1);
$pdf->multicell(0,5,'PIHAK PERTAMA memberi kuasa (wakalah) kepada PIHAK KEDUA untuk membeli barang sesuai dengan kebutuhan PIHAK KEDUA (selanjutnya disebut Barang)',0,'J',1);
$pdf->cell(5,5,'-.',0,'L',1);
$pdf->multicell(0,5,'PIHAK KEDUA akan menyimpan dan merawat barang yang sudah dibeli dengan menggunakan uang PIHAK PERTAMA. PIHAK PERTAMA memberi ijin kepada PIHAK KEDUA menjual barang yang sudah dibeli apabila barang tersebut berupa barang modal usaha.',0,'J',1);
$pdf->Ln(2);
$pdf->cell(5,5,'-.',0,'L',1);
$pdf->multicell(0,5,'PIHAK PERTAMA dan PIHAK KEDUA selanjutnya sepakat akan menandatangani Akad PEMBIAYAAN MURABAHAH dengan membawa bukti pembelian barang oleh PIHAK KEDUA',0,'J',1);
$pdf->Ln(2);
$pdf->multicell(0,5,'Berdasarkan hal-hal tersebut diatas, PARA PIHAK telah sepakat untuk membuat, menetapkan, melaksanakan dan mematuhi perjanjian dengan persyaratan dan ketentuan sebagai berikut :',0,'J',1);
$pdf->Ln(2);
$pdf->cell(2,5,' ',0,'L',1);
$pdf->cell(4,5,'1.',0,'L',1);
$pdf->multicell(0,5,'Sebelum PEMBIAYAAN AKAD MURABAHAH ditandatangani, PIHAK PERTAMA melalui perjanjian ini memberikan kuasa kepada PIHAK KEDUA untuk membeli barang atas nama PIHAK PERTAMA. Barang yang sudah dibeli PIHAK KEDUA adalah milik sepenuhnya PIHAK PERTAMA',0,'J',1);
$pdf->Ln(2);
$pdf->cell(2,5,' ',0,'L',1);
$pdf->cell(4,5,'2.',0,'L',1);
$pdf->multicell(0,5,'PIHAK PERTAMA akan memberikan sejumlah uang sebesar Rp.'.$pla.' ('.$terang.' Rupiah) kepada PIHAK KEDUA dan hanya akan digunakan untuk membeli barang sebagaimana diatur dalam poin 1 perjanjian ini.',0,'J',1);

$pdf->cell(2,5,' ',0,'L',1);
$pdf->cell(4,5,'3.',0,'L',1);
$pdf->multicell(0,5,'Setelah barang dibeli, PIHAK KEDUA wajib menyimpan bukti pembelian barang yang akan diserahkan kepada PIHAK PERTAMA pada saat PEMBIAYAAN AKAD MURABAHAH akan ditandatangani ',0,'J',1);

$pdf->cell(2,5,' ',0,'L',1);
$pdf->cell(4,5,'4.',0,'L',1);
$pdf->multicell(0,5,'PARA PIHAK sepakat untuk menandatangani PEMBIAYAAN AKAD MURABAHAH pada tempat dan waktu yang telah disepakati',0,'J',1);
$pdf->Ln(2);

$pdf->cell(2,5,' ',0,'L',1);
$pdf->cell(4,5,'5.',0,'L',1);
$pdf->multicell(0,5,'Apabila perjanjian ini dibatalkan dengan sebab apapun dan disetujui oleh PIHAK PERTAMA maka PIHAK KEDUA harus mengembalikan uang yang telah diterima kepada PIHAK PERTAMA berserta biaya-biaya yang timbul akibat pembatalan perjanjian ini',0,'J',1);
$pdf->Ln(2);
$pdf->multicell(0,5,'Demikian Perjanjian ini dibuat oleh PIHAK PERTAMA dan disetujui oleh PIHAK KEDUA di '.$alamat4.'  Pada tanggal '.$tgl_wakalah.' ',0,'J',1);
$pdf->Ln(2);

$pdf->cell(140,5,'PIHAK PERTAMA ',0,'R',1);
$pdf->cell(6,5,'PIHAK KEDUA',0,'R',1);
$pdf->Ln(3);
$pdf->cell(70,5,'KSPPS NURINSANI ',0,'R',1);
$pdf->cell(70,5,'',0,'R',1);
$pdf->cell(70,5,'ANGGOTA ',0,'R',1);
$pdf->Ln(28);
$pdf->SetFont ('Arial','U',9);
$pdf->cell(70,5,''.$nama_um,0,'C',1);
$pdf->Ln(3);
$pdf->SetFont ('Arial','',9);
$pdf->cell(70,5,'Marketing Manager',0,'C',1);
$pdf->cell(70,5,'',0,'R',1);
$pdf->cell(6,5,''.$nama,0,'C',1);
$pdf->Ln(20);





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
