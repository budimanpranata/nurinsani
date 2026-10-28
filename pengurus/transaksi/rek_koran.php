<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();

$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

include "../../conn2.php";
include "../../function.php";


$koneksi=open_connection2();
$norek=$_GET ['norek'];
$cif=substr($norek,0,9);


$data_loan = mysql_query ("select * from anggota where cif ='$cif'");
while($data_l= mysql_fetch_array($data_loan)){
$CUST_SHORT_NAME=$data_l['CUST_SHORT_NAME'];
$maturity_date=tgl_indo($data_l['maturity_date']);
}

$data_simpan = mysql_query ("select max(buss_date) as buss_date, type from simpanan_kp where snorek ='$norek'");
while($data_l= mysql_fetch_array($data_simpan)){

$mutasi=tgl_indo($data_l['buss_date']);
$tipe=$data_l['type'];
if ($tipe =='04'){
$tipe='SIMPANAN POKOK';
}elseif($tipe =='05'){
$tipe='SIMPANAN WAJIB';
}elseif($tipe =='06'){
$tipe='SIMPANAN KHUSUS';
}elseif($tipe =='07'){
$tipe='SIMPANAN UMUM';
}
}





require ('../../fpdf17/fpdf.php');

$query1 = mysql_query("SET @bal = 0;");
$sql =mysql_query ("SELECT simpanan_kp.KET,type,kode_transaksi, simpanan_kp.SNOREK,simpanan_kp.BUSS_DATE,anggota.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=@bal+KREDIT-DEBIT as balance FROM simpanan_kp 
join anggota on anggota.cif = simpanan_kp.scif
where simpanan_kp.SNOREK='$norek'  order by simpanan_kp.REFF");
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
$pdf->multicell(0,5,'DAFTAR MUTASI SIMPANAN',0,'J',1);
$pdf->Ln();
$pdf->SetFont('Arial','',8);
$pdf->cell(70,5,'Nomor Rekenig',0,'R',1);
$pdf->cell(1,5,':',0,'L',1);
$pdf->cell(9,5,'',0,'L',1);
$pdf->cell(15,5,''.$norek,0,'J',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Nama ',0,'L',1);
$pdf->cell(1,5,':',0,'L',1);
$pdf->cell(9,5,'',0,'L',1);
$pdf->cell(15,5,''.$CUST_SHORT_NAME,0,'L',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Tanggal Buka ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$buss_date,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Mutasi Terakhir ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$mutasi,0,'R',1);
$pdf->Ln(5);
$pdf->cell(70,5,'Jenis Simpanan ',0,'R',1);
$pdf->cell(1,5,':',0,'R',1);
$pdf->cell(9,5,'',0,'R',1);
$pdf->cell(15,5,''.$tipe,0,'R',1);

$pdf->Ln(5);
$pdf->SetFont('ARIAL','B',7);
$pdf->Cell(8,3,'No',1,0,'C');
$pdf->Cell(25,3,'Tanggal',1,0,'C');
$pdf->Cell(25,3,'kode transaksi',1,0,'C');
$pdf->Cell(60,3,'Keterangan',1,0,'C');
$pdf->Cell(9,3,'Tipe',1,0,'C');
$pdf->Cell(15,3,'Debet',1,0,'C');
$pdf->Cell(15,3,'Kredit',1,0,'C');
$pdf->Cell(15,3,'Saldo',1,0,'C');

$pdf->Ln();
$no =0;
while($result= mysql_fetch_array($sql)){
$no ++;
$norek = $result['SNOREK'];
$CUST_SHORT_NAME = $result['CUST_SHORT_NAME'];
$BUSS_DATE = $result['BUSS_DATE'];
$KET = $result['KET'];
$tipe = $result['type'];
$DEBIT = $result['DEBIT'];
$KREDIT = $result['KREDIT'];
$Balance = $result['balance'];
$kode_transaksi = $result['kode_transaksi'];

//query dan arraying
$pdf->SetFont('Arial','',7);
$pdf->Cell(8,3,''.$no,1,'L',1);
$pdf->Cell(25,3,''.tgl_indo ($BUSS_DATE),1,'L',1);
$pdf->Cell(25,3,''.$kode_transaksi,1,'L',1);
$pdf->Cell(60,3,''.$KET,1,'L',1);
$pdf->Cell(9,3,''.$tipe,1,'L',1);
$pdf->Cell(15,3,''.number_format (compat_num($DEBIT)),1,'LR',0,'R');
$pdf->Cell(15,3,''.number_format (compat_num($KREDIT)),1,'LR',0,'R');
$pdf->Cell(15,3,''.number_format (compat_num($Balance)),1,'LR',0,'R');

$pdf->Ln();
}
$pdf->Output();

?>

