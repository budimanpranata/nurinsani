<?php require_once __DIR__ . '/php8_compat.php';
session_start();

$userid=$_SESSION['userid'];

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
}
 include "conn2.php";
 include "indo_tgl.php";
 $koneksi=open_connection2();	
$norek=$_GET['rekening'];


require ('fpdf17/fpdf.php');
	
if ($_SESSION['pilih']!=="01"){
			$query1 = mysql_query("SET @bal = 0;");
				$sql =mysql_query ("SELECT simpanan.KET, simpanan.NOREK,simpanan.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=@bal+KREDIT-DEBIT as balance FROM simpanan 
				join anggota on anggota.NOREK = simpanan.NOREK
				where simpanan.NOREK='$norek' order by simpanan.BUSS_DATE");
				
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
$pdf->multicell(0,5,'DAFTAR REKENING KORAN',0,'J',1);
$pdf->Ln();
$pdf->SetFont('ARIAL','B',7);
$pdf->Cell(8,3,'No',1,0,'C');
$pdf->Cell(25,3,'No.Rek',1,0,'C');
$pdf->Cell(45,3,'Nama Nasabah',1,0,'C');
$pdf->Cell(25,3,'Tanggal',1,0,'C');
$pdf->Cell(45,3,'Keterangan',1,0,'C');
$pdf->Cell(18,3,'Debet',1,0,'C');
$pdf->Cell(15,3,'Kredit',1,0,'C');
$pdf->Cell(15,3,'Saldo',1,0,'C');
$pdf->Ln();
$no =0;

	

//membuat kop tabel

while($result= mysql_fetch_array($sql)){
	$no ++;
	$norek = $result['NOREK'];
	$CUST_SHORT_NAME = $result['CUST_SHORT_NAME'];
	$BUSS_DATE = $result['BUSS_DATE'];
	$KET = $result['KET'];
	$DEBIT = $result['DEBIT'];
	$KREDIT = $result['KREDIT'];
	$Balance = $result['balance'];


//query dan arraying
$pdf->SetFont('Arial','',7);
$pdf->Cell(8,3,''.$no,1,'L',1);
$pdf->Cell(25,3,''.$norek,1,'L',1);
$pdf->Cell(45,3,''.$CUST_SHORT_NAME,1,'L',1);
$pdf->Cell(25,3,''.$BUSS_DATE,1,'L',1);
$pdf->Cell(45,3,''.$KET,1,'L',1);
$pdf->Cell(18,3,''.number_format (compat_num($DEBIT)),1,'LR',0,'R');
$pdf->Cell(15,3,''.number_format (compat_num($KREDIT)),1,'LR',0,'R');
$pdf->Cell(15,3,''.number_format (compat_num($Balance)),1,'LR',0,'R');
$pdf->Cell(45,3,''.$_SESSION['pilih'],1,'L',1);
$pdf->Ln();
}


//$pdf->AutoPrint(true);
 
$pdf->Output();
				 
			}
			
			elseif($_SESSION['pilih']!=="02"){
				$sql =mysql_query ("select * from pinjaman_detail where id_pinjam=''$norek");

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
$pdf->multicell(0,5,'DAFTAR REKENING KORAN',0,'J',1);
$pdf->Ln();
$pdf->SetFont('ARIAL','B',7);
$pdf->Cell(8,3,'No',1,0,'C');
$pdf->Cell(25,3,'No.Rek',1,0,'C');
$pdf->Cell(45,3,'Nama Nasabah',1,0,'C');
$pdf->Cell(25,3,'Tanggal',1,0,'C');
$pdf->Cell(45,3,'Cicilan',1,0,'C');
$pdf->Cell(18,3,'Tgl jatuh Tempo',1,0,'C');
$pdf->Cell(15,3,'Tgl Bayar',1,0,'C');
$pdf->Cell(15,3,'Saldo',1,0,'C');
$pdf->Ln();
$no =0;
while($result= mysql_fetch_array($sql)){
	$no ++;
	$id_pinjam = $result['id_pinjam'];
	$CUST_SHORT_NAME = $result['CUST_SHORT_NAME'];
	$tgl_jatuh_tempo = $result['tgl_jatuh_tempo'];
	$angsuran_pokok = $result['angsuran_pokok'];
	$bunga = $result['bunga'];
	$tgl_bayar = $result['tgl_bayar'];
	$ket = $result['ket'];


//query dan arraying
$pdf->SetFont('Arial','',7);
$pdf->Cell(8,3,''.$no,1,'L',1);
$pdf->Cell(25,3,''.$norek,1,'L',1);
$pdf->Cell(45,3,''.$CUST_SHORT_NAME,1,'L',1);
$pdf->Cell(25,3,''.$BUSS_DATE,1,'L',1);
$pdf->Cell(45,3,''.$KET,1,'L',1);
$pdf->Cell(18,3,''.number_format (compat_num($DEBIT)),1,'LR',0,'R');
$pdf->Cell(15,3,''.number_format (compat_num($KREDIT)),1,'LR',0,'R');
$pdf->Cell(15,3,''.number_format (compat_num($Balance)),1,'LR',0,'R');

$pdf->Ln();

}	
$pdf->Output();
 
			}
			else{
				
				
			}


unset($_SESSION['pilih']);

?>