<?php require_once __DIR__ . '/php8_compat.php';
session_start();
ob_start();
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
$code_kel=$_GET['unit'];
$sql4=mysql_query("Select nama_kel,code_kel FROM kelompok WHERE code_kel='$code_kel'");
$kode= mysql_fetch_array($sql4);
$nkel=$kode['nama_kel'];

$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$nama_sales=$dsmo['nama'];
$kode_branch=$dsmo['kode_branch'];

$sql2=mysql_query("Select person.nama, branch.unit,branch.alamat FROM branch inner join person on person.unit = branch.kode_branch WHERE kode_branch='$kode_branch'");
$br= mysql_fetch_array($sql2);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];
$nama_um=$br['nama'];

$sqlm=mysql_query("Select tgl_murab FROM calon_loan WHERE unit='$kode_branch'");
$mur= mysql_fetch_array($sqlm);
$tgl_mur=$mur['tgl_murab'];
$tgl_mur=tgl_indo($tgl_mur);
$buss_date=date("y-m-d");

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
}

//cek level user
if($_SESSION['level']!="unit"){
     header("location:../index.html");//jika bukan user jangan lanjut
}

require ('fpdf17/fpdf.php');
$pdf=new FPDF('L','mm', array(216, 275));
$pdf->addPage();
$pdf->setTopMargin(15);
$pdf->setLeftMargin(12);
$pdf->setFillColor(255,255,255);

 
$pdf->SetFont('Arial','B',9);

//membuat kop tabel
$pdf->SetFont('Arial','B',9);
$pdf->multicell(0,4,'KOPERASI SIMPAN PINJAM DAN PEMBIAYAAN SYARIAH NUR INSANI AREA PEMASARAN '.$nama_unit.'',0,'J',1);
$pdf->multicell(0,4,'ALAMAT '.$alamat.'',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont('Arial','BU',9);
$pdf->multicell(0,4,'REKAP PERMOHONAN DAN REKOMENDASI PEMBIAYAAN KELOMPOK',0,'J',1);
$pdf->Ln(4);
$pdf->SetFont('ARIAL','B',8);
$pdf->Cell(40,4,'Kode Area Pemasaran',0,0,'L');
$pdf->Cell(40,4,': '.$kode_branch,0,0,'L');
$pdf->Ln(4);
$pdf->Cell(40,4,'Tanggal Cair',0,0,'L');
$pdf->Cell(40,4,': '.$tgl_mur,0,0,'L');
$pdf->Ln(4);
$pdf->Cell(40,4,'Nama MM',0,0,'L');
$pdf->Cell(40,4,': '.$nama_um,0,0,'L');
$pdf->Ln(5);

$pdf->SetFont('ARIAL','B',8);
$pdf->Cell(8,4,'No',1,0,'C');
$pdf->Cell(35,4,'Nama Anggota',1,0,'C');
$pdf->Cell(30,4,'Nama KEL',1,0,'C');
$pdf->Cell(30,4,'Nama Suami',1,0,'C');
$pdf->Cell(30,4,'Alamat',1,0,'C');
$pdf->Cell(30,4,'Jenis Usaha',1,0,'C');
$pdf->Cell(19,4,'Pengajuan Ao',1,0,'C');
$pdf->Cell(25,4,'Tanggal PK',1,0,'C');
$pdf->Cell(8,4,'JML',1,0,'C');
$pdf->Cell(19,4,'Sebelumnya',1,0,'C');
$pdf->Cell(17,4,'Disetujui MM',1,0,'C');
$pdf->Cell(10,4,'Pinj.ke',1,0,'C');

$pdf->Ln();

//query dan arraying
$no=0;
$tot_blt=0;
  $sql=mysql_query("select calon_loan.unit,calon_loan.cif,calon_loan.suffix,calon_loan.cust_Short_name,HADDRESS1,(select nama_usaha from bidang_usaha where code_bidang=calon_loan.usaha) as nama_usaha,WARIS,calon_loan.Plafond as rek,nama_kel,loan.Plafond as sebelum,calon_loan.tgl_murab,calon_loan.tenor
from calon_loan
inner join anggota on anggota.cif = calon_loan.cif
inner join kelompok on kelompok.code_kel = calon_loan.code_kel
left join loan on loan.cif=calon_loan.cif
where calon_loan.USERID='$userid' AND calon_loan.unit='$kode_branch' and calon_loan.status_app ='PENDING' order by nama_kel ");

$sql2=mysql_query("select sum(calon_loan.Plafond) as total, count(calon_loan.cif) as noa,nama_kel,nama_ao from calon_loan
inner join kelompok on kelompok.code_kel = calon_loan.code_kel
inner join ao on ao.cao=calon_loan.cao
where calon_loan.USERID='$userid' AND calon_loan.unit='$kode_branch' and status_app ='PEDENDING' group by nama_kel order by nama_kel ");

$sql3=mysql_query("select sum(calon_loan.Plafond) as total_pla, count(cif) as total_noa from calon_loan
where calon_loan.USERID='$userid' AND calon_loan.unit='$kode_branch'  and status_app ='PENDING'");
while($result= mysql_fetch_array($sql)){
	$no ++;
 $TGL_KUNJUNGAN = $result['nama_ao'];
$cif = $result['CIF'];
$cao = $result['cao'];
$cust_Short_name= $result['cust_Short_name'];
$Plafond= number_format (compat_num($result['rek']));  
$angsuran = number_format (compat_num($result['angsuran']));
$bulat = number_format (compat_num($result['bulat']));
$unit = $result['unit'];
$ke = $result['ke'];
$suffix = $result['suffix'];
$bulat = number_format (compat_num($result['bulat']));
$saldo = number_format (compat_num($result['sebelum']));
$run_tenor = $result['run_tenor'];
$os =number_format (compat_num($result['os']));
$norek="$unit-$cao-$cif";
$bagi_hasil = number_format(compat_num($result['bagi_hasil']));
$tot_blt +=($result['tot_bulat']);
$tot_pla=$result['tot_pla'];
$alamat=$result['HADDRESS1'];
$waris=$result['WARIS'];
$usaha=$result['nama_usaha'];
$tenor=$result['tenor'];
$nama_kel=$result['nama_kel'];
$tgl_murab=tgl_indo ($result['tgl_murab']);

$pdf->SetFont('Arial','',6);
$pdf->Cell(8,3,''.$no,1,'L',1);
$pdf->Cell(35,3,''.$cust_Short_name,1,'L',1);
$pdf->Cell(30,3,''.$nama_kel,1,'L',1);
$pdf->Cell(30,3,''.$waris,1,0,'L');
$pdf->Cell(30,3,''.$alamat,1,0,'L');
$pdf->Cell(30,3,''.$usaha,1,'L',1);
$pdf->Cell(19,3,''.$Plafond,1,'L',1);
$pdf->Cell(25,3,''.$tgl_murab,1,0,'R');
$pdf->Cell(8,3,''.$tenor,1,0,'R');
$pdf->Cell(19,3,''.$saldo,1,'L',1);
$pdf->Cell(17,3,''.$Plafond,1,0,'R');
$pdf->Cell(10,3,''.$suffix,1,0,'R');
$pdf->Ln();
}
while($result3= mysql_fetch_array($sql3)){
	 $total_pla = $result3['total_pla'];
		$total_noa = $result3['total_noa'];
$pdf->SetFont('ARIAL','B',6);
	$pdf->Cell(234,3,'Total',1,'L',1);
$pdf->Cell(17,3,''.number_format (compat_num($total_pla)),1,0,'R');
$pdf->Ln();
}
$pdf->Ln(8);
$pdf->SetFont('ARIAL','B',6);
$pdf->Cell(18,3,'No',1,0,'C');
$pdf->Cell(40,3,'Nama Kelompok',1,0,'C');
$pdf->Cell(40,3,'Total Pembiayaan',1,0,'C');
$pdf->Cell(40,3,'Noa',1,0,'C');
$pdf->Cell(40,3,'Nama AO',1,0,'C');
$pdf->Ln();

$no2=0;
while($result2= mysql_fetch_array($sql2)){
	$no2 ++;
 $TGL_KUNJUNGAN = $result['nama_ao'];
$nama_kel = $result2['nama_kel'];
$total = $result2['total'];
$noa = $result2['noa'];
$nama_ao = $result2['nama_ao'];


$pdf->SetFont('Arial','',6);
$pdf->Cell(18,3,''.$no2,1,'L',1);
$pdf->Cell(40,3,''.$nama_kel,1,'L',1);
$pdf->Cell(40,3,''.number_format (compat_num($total)),1,0,'R');
$pdf->Cell(40,3,''.number_format (compat_num($noa)),1,0,'C');
$pdf->Cell(40,3,''.$nama_ao,1,0,'L');

$pdf->Ln();
}
$pdf->SetFont('ARIAL','B',6);
$pdf->Cell(58,3,'Total',1,'L',1);
$pdf->Cell(40,3,''.number_format (compat_num($total_pla)),1,0,'R');
$pdf->Cell(40,3,''.number_format (compat_num($total_noa)),1,0,'C');
$pdf->Ln();

//$pdf->Cell(248,5,'',1,'L',1);
//$pdf->Cell(18,5,''.$tot_blt,1,0,'R');

$pdf->Ln(9);
$pdf->SetFont('ARIAL','B',7);
$pdf->Cell(160,3,'Dibuat Oleh,',0,0,'C');
$pdf->Cell(25,3,'Direkomendasi oleh, ',0,0,'C');
$pdf->Cell(40,3,'Disetujui oleh, ',0,0,'C');
$pdf->Ln(20,1);

$pdf->Cell(160,3,'Admin Leader',0,0,'C');
$pdf->Cell(25,3,'Marketing Manager',0,0,'C');
$pdf->Cell(40,3,'BM',0,0,'C');
$pdf->Output();

?>
