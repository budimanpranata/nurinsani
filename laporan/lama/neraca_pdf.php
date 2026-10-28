<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
ob_start();
$userid=$_SESSION['userid'];
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
 exit; }

include "../conn2.php";
	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}
require ('../fpdf17/fpdf.php');
$sql =mysql_query ("select * from tabel_master where unit ='$unit'  and  nama_rekening not like '%total%' order by kode_rekening asc");
$pdf=new FPDF('P','mm', array(216, 275));
$pdf->AddPage();
//$pdf=new FPDF('L','mm', array(216, 330));
$pdf->setX(11);
$pdf->setFillColor(255,255,255);
$pdf->SetFont('Arial','B',9);
$pdf->SetFont('Arial','B',8);
$pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',8);
$pdf->multicell(0,5,'NERACA',0,'J',1);
$pdf->Ln();
$pdf->Ln(5);
$pdf->SetFont('ARIAL','B',7);

$pdf->Cell(12,6,'Account',1,0,'C');
$pdf->Cell(68,6,'Nama Account',1,0,'C');
$pdf->Cell(40,3,'Saldo Awal',1,0,'C');
$pdf->Cell(40,3,'Mutasi',1,0,'C');
$pdf->Cell(40,3,'Saldo Akhir',1,0,'C');
$pdf->Ln();
$pdf->Cell(30,3,'',0,0,'C');
$pdf->Cell(50,3,'',0,0,'C');
$pdf->Cell(20,3,'Debet',1,0,'C');
$pdf->Cell(20,3,'Kredit',1,0,'C');
$pdf->Cell(20,3,'Debet',1,0,'C');
$pdf->Cell(20,3,'Kredit',1,0,'C');
$pdf->Cell(20,3,'Debet',1,0,'C');
$pdf->Cell(20,3,'Kredit',1,0,'C');
$pdf->Ln();
$no =0;
while($row_mut= mysql_fetch_array($sql)){
$no ++;
$awal_debet=$row_mut['awal_debet'];
$awal_kredit=$row_mut['awal_kredit'];
$mutasi_debet=$row_mut['mut_debet'];
$mutasi_kredit=$row_mut['mut_kredit'];
$sisa_debet=$row_mut['sisa_debet'];
$sisa_kredit=$row_mut['sisa_kredit'];
//query dan arraying
$pdf->SetFont('Arial','',7);
$pdf->Cell(12,3,''.$row_mut['kode_rekening'],1,'L',1);
$pdf->Cell(68,3,''.$row_mut['nama_rekening'],1,'L',1);
$pdf->Cell(20,3,''.number_format (compat_num($awal_debet)),1,'LR',0,'R');
$pdf->Cell(20,3,''.number_format (compat_num($awal_kredit)),1,'LR',0,'R');
$pdf->Cell(20,3,''.number_format (compat_num($mutasi_debet)),1,'LR',0,'R');
$pdf->Cell(20,3,''.number_format (compat_num($mutasi_kredit)),1,'LR',0,'R');
$pdf->Cell(20,3,''.number_format (compat_num($sisa_debet)),1,'LR',0,'R');
$pdf->Cell(20,3,''.number_format (compat_num($sisa_kredit)),1,'LR',0,'R');
$pdf->Ln();
}
$pdf->Output();

?>