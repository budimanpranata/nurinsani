<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
 include "indo_tgl.php";
// wakalah.php mengirim kode unit sebagai ?code_kel=, versi lama membaca ?unit=
$code_kel = isset($_GET['unit']) ? $_GET['unit'] : (isset($_GET['code_kel']) ? $_GET['code_kel'] : '');
$kode = db_satu('SELECT nama_kel, code_kel FROM kelompok WHERE code_kel = ?', 's', $code_kel);
$nkel = $kode ? $kode['nama_kel'] : '';

$dsmo = db_satu('SELECT nama, kode_branch FROM user WHERE userid = ?', 's', $userid);
$nama_sales = $dsmo ? $dsmo['nama'] : '';
$kode_branch = $dsmo ? $dsmo['kode_branch'] : '';

$br = db_satu('SELECT person.nama, branch.unit, branch.alamat FROM branch
    INNER JOIN person ON person.unit = branch.kode_branch WHERE kode_branch = ?', 's', $kode_branch);
$nama_unit = $br ? $br['unit'] : '';
$alamat = $br ? $br['alamat'] : '';
$nama_um = $br ? $br['nama'] : '';

$mur = db_satu('SELECT tgl_murab FROM calon_loan WHERE unit = ?', 's', $kode_branch);
$tgl_mur = $mur ? $mur['tgl_murab'] : '';
$tgl_mur=tgl_indo($tgl_mur);
$buss_date=date("y-m-d");

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");//jika belum login jangan lanjut..
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
     header("location:../index.html");//jika bukan user jangan lanjut
 exit; }

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
$sql = db_semua("SELECT calon_loan.unit, calon_loan.cif, calon_loan.suffix, calon_loan.cust_Short_name, HADDRESS1,
        (SELECT nama_usaha FROM bidang_usaha WHERE code_bidang = calon_loan.usaha) AS nama_usaha, WARIS,
        calon_loan.Plafond AS rek, nama_kel, loan.Plafond AS sebelum, calon_loan.tgl_murab, calon_loan.tenor
    FROM calon_loan
    INNER JOIN anggota ON anggota.cif = calon_loan.cif
    INNER JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
    LEFT JOIN loan ON loan.cif = calon_loan.cif
    WHERE calon_loan.USERID = ? AND calon_loan.unit = ? AND calon_loan.status_app = 'PENDING' ORDER BY nama_kel", 'ss', $userid, $kode_branch);

// versi lama: status_app = 'PEDENDING' (salah ketik) sehingga ringkasan per kelompok selalu kosong
$sql2 = db_semua("SELECT SUM(calon_loan.Plafond) AS total, COUNT(calon_loan.cif) AS noa, nama_kel, MAX(nama_ao) AS nama_ao
    FROM calon_loan
    INNER JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
    INNER JOIN ao ON ao.cao = calon_loan.cao
    WHERE calon_loan.USERID = ? AND calon_loan.unit = ? AND status_app = 'PENDING' GROUP BY nama_kel ORDER BY nama_kel", 'ss', $userid, $kode_branch);

$sql3 = db_semua("SELECT SUM(calon_loan.Plafond) AS total_pla, COUNT(cif) AS total_noa FROM calon_loan
    WHERE calon_loan.USERID = ? AND calon_loan.unit = ? AND status_app = 'PENDING'", 'ss', $userid, $kode_branch);
$total_pla = 0;
$total_noa = 0;
foreach ($sql as $result) {
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
foreach ($sql3 as $result3) {
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
foreach ($sql2 as $result2) {
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
