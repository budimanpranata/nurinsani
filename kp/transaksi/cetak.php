<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Cetak bukti Jurnal Umum (level kp), dibuka dari kp/transaksi/umum.php: ?file=<nomor bukti>.
 * Tata letak PDF sama dengan versi lama; query diubah ke prepared statement dan buffer dibersihkan sebelum PDF
 * dikirim. Dibuang: loop kosong yang tidak dipakai dan teks "ks" sesudah penutup PHP (dulu ikut terkirim di
 * belakang berkas PDF). Perbaikan 2026-11-03 (perintah user): jumlah = total debet nomor bukti di DB (dulu ?total).
 */
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "kp") {
    header("location:index.html");
    exit;
}
include "../../indo_tgl.php";
$kode_kirim = isset($_GET['file']) ? (string) $_GET['file'] : '';
$jumlah = db_satu("select coalesce(sum(debet),0) as total from tabel_transaksi where kode_transaksi =?", 's', $kode_kirim);
$total = $jumlah ? $jumlah['total'] : 0;

// tanggal dari baris transaksi pertama nomor bukti ini (menimpa ?tgl)
$sal_simpan = db_satu("select tanggal_transaksi from tabel_transaksi where kode_transaksi =?", 's', $kode_kirim);
$tgl = tgl_indo($sal_simpan ? (string) $sal_simpan['tanggal_transaksi'] : '');

require('../../fpdf17/fpdf.php');

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

foreach (db_semua("select kode_rekening from tabel_transaksi where kode_transaksi =? group by kode_rekening ", 's', $kode_kirim) as $result) {
	$no ++;
 $kode= $result['kode_rekening'];

 $sal_simpan= db_satu("select sum(kredit) as kredit,tabel_transaksi.kode_rekening, nama_rekening from tabel_transaksi
 left join tabel_master on tabel_master.rekening_gl = tabel_transaksi.kode_rekening
 where tabel_transaksi.kode_rekening =? and kredit > 0 and  tabel_transaksi.kode_transaksi =? and tabel_master.unit='1010' ", 'ss', (string) $kode, $kode_kirim);
$kredit= $sal_simpan ? $sal_simpan['kredit'] : null;
$kode_kredit= $sal_simpan ? $sal_simpan['kode_rekening'] : null;
$nama=$sal_simpan ? $sal_simpan['nama_rekening'] : null;
$kode_kredit ="$kode_kredit - $nama";

$sal_debet= db_satu("select sum(tabel_transaksi.debet) as debet,tabel_transaksi.kode_rekening,nama_rekening  from tabel_transaksi
left join tabel_master on tabel_master.rekening_gl = tabel_transaksi.kode_rekening
where tabel_transaksi.kode_rekening =? and kode_transaksi =? and tabel_transaksi.debet > 0 and tabel_master.unit ='1010' ", 'ss', (string) $kode, $kode_kirim);
$debet= $sal_debet ? $sal_debet['debet'] : null;
$kode_debet= $sal_debet ? $sal_debet['kode_rekening'] : null;
$nama=$sal_debet ? $sal_debet['nama_rekening'] : null;
$kode_debet ="$kode_debet - $nama";

//query dan arraying
$pdf->SetFont('ARIAL','',8);
$pdf->cell(100,5,''.$kode_debet,1,'L',1);
$pdf->cell(1,5,''.number_format(compat_num($debet)),0,'L',0);
$pdf->cell(90,5,''.$kode_kredit,1,'L',1);
$pdf->cell(1,5,''.number_format(compat_num($kredit)),0,'L',0);

$pdf->Ln();
}

// keterangan dari baris terakhir nomor bukti ini
$keterangan = '';
foreach (db_semua("select keterangan_transaksi from tabel_transaksi where kode_transaksi =?", 's', $kode_kirim) as $result) {
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

// buang keluaran lain (spasi/peringatan) supaya berkas PDF tidak rusak
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output();
