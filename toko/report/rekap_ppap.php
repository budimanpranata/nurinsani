<?php require_once __DIR__ . '/../../php8_compat.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl=$_SESSION['stgl_npl'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
 include "../conn2.php";
 include "../indo_tgl.php";
 $koneksi=open_connection2();
$code_kel=$_GET['kode_kel'];
$periode =tgl_indo($tgl);

$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$nama_sales=$dsmo['nama'];
$kode_branch=$dsmo['kode_branch'];

$sql2=mysql_query("Select * FROM branch WHERE kode_branch='$kode_branch'");
$br= mysql_fetch_array($sql2);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];

$buss_date=date("y-m-d");
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
     header("location:../index.html");//jika bukan user jangan lanjut
 exit; }

require ('../fpdf17/fpdf.php');
$pdf=new FPDF('L','mm', array(279.4, 381));
$pdf->addPage();
$pdf->setTopMargin(15);
$pdf->setLeftMargin(12);
$pdf->setFillColor(255,255,255);

$pdf->SetFont('Arial','B',12);

//membuat kop tabel
$pdf->SetFont('Arial','B',11);
$pdf->multicell(0,5,'KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,5,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->SetFont('Arial','BU',12);
$pdf->Ln(4);
$pdf->multicell(0,5,'REKAP KOLEKTABILITAS PEMBIAYAAN',0,'J',1);
$pdf->SetFont('Arial','B',12);
$pdf->multicell(0,5,'Tanggal '.$periode,0,'J',1);
$pdf->Ln(4);

$pdf->SetFont('ARIAL','B',10);
$pdf->Cell(15,5,'No',1,0,'C');
$pdf->Cell(55,5,'Kelompok Kolektabilitas',1,0,'C');
$pdf->Cell(66,5,'Jumlah Debitur',1,0,'C');
$pdf->Cell(44,5,'Nilai Anggunan',1,0,'C');
$pdf->Cell(44,5,'Plafond ',1,0,'C');
$pdf->Cell(44,5,'Saldo Pinjaman',1,0,'C');
$pdf->Cell(44,5,'Prosent NPL-Gross',1,0,'C');
$pdf->Cell(44,5,'PPAP',1,0,'C');


//query dan arraying coll 1 
	//$cek_saldo= mysql_fetch_array(mysql_query("SELECT sum(saldo_kredit) - sum(debet) as saldo from rek_loan where unit ='$kode_branch' "));
	$data_noa= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo from loan where gol='1' AND loan.unit ='$kode_branch' and os>0"));
	$noa=$data_noa['noa'];
	$saldo= number_format (compat_num($data_noa['saldo']));
$tot_saldo=($data_noa['saldo']);
	$pla=number_format (compat_num($data_noa['pla']));
	$bulat=floor(compat_num(0.5/100 * $data_noa['saldo']));
	$ppap= number_format (compat_num($bulat));
	$tot_ppap1= (0.5/100 * $data_noa['saldo']);	

		
//query dan arraying coll 2 
	//$cek_saldo2= mysql_fetch_array(mysql_query("SELECT sum(pokok)+sum(margin) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) between 91 and 120 AND unit  ='$kode_branch' group by cif "));
	$data_noa2= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(Plafond) as pla,sum(os-saldo_margin) as saldo from loan where gol='2' AND loan.unit ='$kode_branch' and os>0"));
	$noa2=$data_noa2['noa'];
	$saldo2=number_format (compat_num($data_noa2['saldo']));
$tot_saldo2=($data_noa2['saldo']);
	$pla2=number_format (compat_num($data_noa2['pla']));
$bulat2=floor(compat_num(10/100 * $data_noa2['saldo']));
	$ppap2= number_format (compat_num($bulat2));
	$tot_ppap2= (10/100 * $data_noa2['saldo']);
//query dan arraying coll 3 
	//$cek_saldo3= mysql_fetch_array(mysql_query("SELECT sum(pokok)+sum(margin) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) between 121 AND 365 AND unit  ='$kode_branch' group by cif "));
	$data_noa3= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(Plafond) as pla ,sum(os-saldo_margin) as saldo from loan where gol='3' AND loan.unit ='$kode_branch'and os>0"));
	$noa3=$data_noa3['noa'];
	$saldo3=number_format (compat_num($data_noa3['saldo']));
$tot_saldo3=($data_noa3['saldo']);
	$pla3=number_format (compat_num($data_noa3['pla']));
$bulat3=floor(compat_num(50/100 * $data_noa3['saldo']));
	$ppap3= number_format(compat_num($bulat3));
	$tot_ppap3= (50/100 * $data_noa3['saldo']);
//query dan arraying coll 4
	//$cek_saldo4= mysql_fetch_array(mysql_query("SELECT sum(pokok)+sum(margin) as saldo, datediff('$tgl', tgl_tunggak) as selisih from tunggakan where datediff('$tgl', tgl_tunggak) >365 AND unit  ='$kode_branch' group by cif "));
	$data_noa4= mysql_fetch_array(mysql_query("SELECT count(cif) as noa, sum(Plafond) as pla ,sum(os-saldo_margin) as saldo from loan where gol='4' AND loan.unit ='$kode_branch' and os>0"));
	$noa4=$data_noa4['noa'];
	$saldo4=number_format (compat_num($data_noa4['saldo']));
$tot_saldo4=($data_noa4['saldo']);
	$pla4=number_format (compat_num($data_noa4['pla']));
$bulat4=floor(compat_num(100/100 * $data_noa4['saldo']));
	$ppap4= number_format(compat_num($bulat4)); 
$tot_ppap4= (100/100 * $data_noa4['saldo']);
//porse hitung total 
	$totnoa=$noa+$noa2+$noa3+$noa4;
	$totsaldo=number_format (compat_num($tot_saldo+$tot_saldo2+$tot_saldo3+$tot_saldo4));
	$totpla=number_format (compat_num($data_noa['pla']+$data_noa2['pla']+$data_noa3['pla']+$data_noa4['pla']));
	$totppap=number_format (compat_num($tot_ppap1+$tot_ppap2+$tot_ppap3+$tot_ppap4));
	//$perlancar=($cek_saldo['saldo'/($cek_saldo['saldo']+$cek_saldo2['saldo']+$cek_saldo3['saldo']+$cek_saldo4['saldo']));
$pdf->Ln();
$pdf->Cell(15,5,'1',1,'L',1);
$pdf->Cell(55,5,'Kredit Lancar'.$norek,1,'L',1);
$pdf->Cell(66,5,''.$noa,1,0,'R');
$pdf->Cell(44,5,''.$Plafond,1,0,'R');
$pdf->Cell(44,5,''.$pla,1,0,'R');
$pdf->Cell(44,5,''.$saldo,1,0,'R');
$pdf->Cell(44,5,''.$perlancar,1,'L',1);
$pdf->Cell(44,5,''.$ppap,1,0,'R');
$pdf->Ln();
$pdf->Cell(15,5,'2',1,'L',1);
$pdf->Cell(55,5,'Kredit Kurang Lancar'.$norek,1,'L',1);
$pdf->Cell(66,5,''.$noa2,1,0,'R');
$pdf->Cell(44,5,''.$Plafond,1,0,'R');
$pdf->Cell(44,5,''.$pla2,1,0,'R');
$pdf->Cell(44,5,''.$saldo2,1,0,'R');
$pdf->Cell(44,5,''.$ke,1,'L',1);
$pdf->Cell(44,5,''.$ppap2,1,0,'R');
$pdf->Ln();
$pdf->Cell(15,5,'3',1,'L',1);
$pdf->Cell(55,5,'Kredit Diragukan'.$norek,1,'L',1);
$pdf->Cell(66,5,''.$noa3,1,0,'R');
$pdf->Cell(44,5,''.$Plafond,1,0,'R');
$pdf->Cell(44,5,''.$pla3,1,0,'R');
$pdf->Cell(44,5,''.$saldo3,1,0,'R');
$pdf->Cell(44,5,''.$ke,1,'L',1);
$pdf->Cell(44,5,''.$ppap3,1,0,'R');
$pdf->Ln();
$pdf->Cell(15,5,'4',1,'L',1);
$pdf->Cell(55,5,'Kredit Macet'.$norek,1,'L',1);
$pdf->Cell(66,5,''.$noa4,1,0,'R');
$pdf->Cell(44,5,''.$Plafond,1,0,'R');
$pdf->Cell(44,5,''.$pla4,1,0,'R');
$pdf->Cell(44,5,''.$saldo4,1,0,'R');
$pdf->Cell(44,5,''.$ke,1,'L',1);
$pdf->Cell(44,5,''.$ppap4,1,0,'R');
$pdf->Ln();
$pdf->Cell(70,5,'Jumlah Total'.$norek,1,'L',1);
$pdf->Cell(66,5,''.$totnoa,1,0,'R');
$pdf->Cell(44,5,''.$Plafond,1,0,'R');
$pdf->Cell(44,5,''.$totpla,1,0,'R');
$pdf->Cell(44,5,''.$totsaldo,1,0,'R');
$pdf->Cell(44,5,''.$ke,1,'L',1);
$pdf->Cell(44,5,''.$totppap,1,0,'R');
$pdf->Ln(9);
$pdf->SetFont('ARIAL','B',6);
$pdf->Cell(40,5,'DIBUAT OLEH',1,0,'C');
$pdf->Cell(40,5,'DIPERIKSA OLEH ',1,0,'C');
$pdf->Cell(40,5,'DI SETUJUI OLEH ',1,0,'C');
$pdf->Ln(20,1);
$pdf->Cell(40,5,'NAMA OFFICER',1,0,'C');
$pdf->Cell(40,5,'ATASAN LANGSUNG',1,0,'C');
$pdf->Cell(40,5,'ATASAN ATASAN LANGSUNG',1,0,'C');
$pdf->Output();

?>
