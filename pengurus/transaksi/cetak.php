<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_jatuh_tempo=$_SESSION['jatuh'];
$cao=$_SESSION['code_cao'];

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="kp"){
   header("location:index.html");
}
 include "../conn2.php";
 include "../../indo_tgl.php";
 $koneksi=open_connection2();	
$kode_kirim=$_GET['file'];
$total=$_GET['total'];
$tgl=($_GET['tgl']);

 $sal_simpan= mysql_fetch_array(mysql_query("select tanggal_transaksi from tabel_transaksi where kode_transaksi ='$kode_kirim'"));
$tgl= $sal_simpan['tanggal_transaksi'];

$tgl=tgl_indo($tgl);
$buss_date=date("y-m-d");
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="kp"){
     header("location:../../index.html");//jika bukan user jangan lanjut
}

require ('../../fpdf17/fpdf.php');

//$pdf=new PDF_AutoPrint('L','mm', array(216, 275));
$pdf=new FPDF ('P','mm','A4');
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->SetFont ('Arial','',8);
$pdf->setTopMargin(15);
$pdf->setLeftMargin(12);
$pdf->setFillColor(255,255,255);

$pdf->Image('../../Image/nii.png');
//$pdf-> Write (12,'User ID: ');
$pdf->Ln(3);

$pdf->multicell(0,5,'no :' .$kode_kirim ,0,'L',1);
$pdf->multicell(0,5,'tgl :' .$tgl ,0,'L',1);
$pdf->Ln(3);
$pdf->multicell(0,5,'DEBET',0,'L',1);
$pdf->Ln(4);
$fill=false;
$pdf->SetFont('ARIAL','B',8);
$pdf->cell(100,5,'No.Rek',1,0,'C',1);
$pdf->cell(91,5,'Perkiraann Lawan',1,0,'C',1);

$pdf->Ln();
$no=0;
$tot_blt=0;

$sql=mysql_query("select kode_rekening from tabel_transaksi where kode_transaksi ='$kode_kirim' group by kode_rekening ");

		
		 
while($result= mysql_fetch_array($sql)){

}


$sql=mysql_query("select kode_rekening from tabel_transaksi where kode_transaksi ='$kode_kirim' group by kode_rekening ");

		
		 
while($result= mysql_fetch_array($sql)){
	$no ++;
 $kode= $result['kode_rekening'];


 $sal_simpan= mysql_fetch_array(mysql_query("select sum(kredit) as kredit,tabel_transaksi.kode_rekening, nama_rekening from tabel_transaksi 
 left join tabel_master on tabel_master.rekening_gl = tabel_transaksi.kode_rekening
 where tabel_transaksi.kode_rekening ='$kode'and kredit > 0 and  tabel_transaksi.kode_transaksi ='$kode_kirim' and tabel_master.unit='1010' "));
$kredit= $sal_simpan['kredit'];
$kode_kredit= $sal_simpan['kode_rekening'];
$nama=$sal_simpan['nama_rekening'];
$kode_kredit ="$kode_kredit - $nama";

$sal_debet= mysql_fetch_array(mysql_query("select sum(tabel_transaksi.debet) as debet,tabel_transaksi.kode_rekening,nama_rekening  from tabel_transaksi 
left join tabel_master on tabel_master.rekening_gl = tabel_transaksi.kode_rekening
where tabel_transaksi.kode_rekening ='$kode' and kode_transaksi ='$kode_kirim' and tabel_transaksi.debet > 0 and tabel_master.unit ='1010' "));
$debet= $sal_debet['debet'];
$kode_debet= $sal_debet['kode_rekening'];
$nama=$sal_debet['nama_rekening'];
$kode_debet ="$kode_debet - $nama";



//query dan arraying
$pdf->SetFont('ARIAL','',8);
$pdf->cell(100,5,''.$kode_debet,1,'L',1);
$pdf->cell(1,5,''.number_format(compat_num($debet)),0,'L',0);
$pdf->cell(90,5,''.$kode_kredit,1,'L',1);
$pdf->cell(1,5,''.number_format(compat_num($kredit)),0,'L',0);

$pdf->Ln();
}




$sql=mysql_query("select keterangan_transaksi from tabel_transaksi where kode_transaksi ='$kode_kirim'");

		
		 
while($result= mysql_fetch_array($sql)){
$keterangan=$result['keterangan_transaksi'];
}


$pdf->Ln();
$pdf->SetFont('ARIAL','B',8);
$pdf->cell(96,5,'Keterangan :',1,0,'L',1);
$pdf->cell(60,5,'jumlah : ',1,0,'L',1);
$pdf->cell(0,5,'Rp.'.number_format(compat_num($total)),1,0,'R',1);
$pdf->Ln();
$pdf->cell(0,5,''.$keterangan,1,1,'L',1);



$pdf->Ln();

$pdf->SetFont('ARIAL','B',8);
$pdf->cell(96,5,'Dibuat :',1,0,'L',1);
$pdf->cell(60,5,'Diperiksa : ',1,0,'L',1);
$pdf->Ln();
$pdf->cell(96,20,'',1,0,'L',1);
$pdf->cell(60,20,' ',1,0,'L',1);
//$pdf->AutoPrint(true);
 
$pdf->Output();

?>ks
