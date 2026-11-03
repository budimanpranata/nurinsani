<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Cetak bukti Jurnal Umum (level kp), dibuka dari kp/transaksi/umum.php: ?file=<nomor bukti>.
 * Perbaikan 2026-11-03 (perintah user: PDF berantakan, samakan dengan cetak Jurnal Umum unit transaksi/cetak.php):
 *   - versi lama mencetak angka debet/kredit di sel selebar 1 mm (menimpa kolom sebelah) dan memasangkan debet/kredit
 *     per rekening dalam dua kolom; kini tabel No | Kode | Nama | Debet | Kredit dengan baris total
 *   - nama rekening dari tabel_master unit 1010 (rekening_gl), tanggal & jumlah dari DB, semua keterangan ditampilkan
 *   - hanya nomor bukti unit user yang login; buffer dibersihkan sebelum PDF dikirim
 */
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:../../index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "kp") {
    header("location:../../index.html");
    exit;
}
include "../../indo_tgl.php";

$kode_kirim = isset($_GET['file']) ? (string) $_GET['file'] : '';
$u = db_satu("select kode_branch from user where userid=?", 's', (string) $userid);
$unit = $u ? (string) $u['kode_branch'] : '';

// satu baris per rekening & posisi (debet dulu), nama rekening dari tabel_master unit 1010
$baris = db_semua("select t.kode_rekening,
        (select tm.nama_rekening from tabel_master tm where tm.rekening_gl = t.kode_rekening and tm.unit = '1010' limit 1) as nama_rek,
        sum(t.debet) as debet, sum(t.kredit) as kredit,
        case when sum(t.debet) > 0 then 0 else 1 end as urut
    from tabel_transaksi t
    where t.kode_transaksi = ? and t.unit = ?
    group by t.kode_rekening, t.debet > 0
    order by urut, t.kode_rekening", 'ss', $kode_kirim, $unit);

$info = db_satu("select min(tanggal_transaksi) as tgl, coalesce(sum(debet),0) as tot_debet, coalesce(sum(kredit),0) as tot_kredit
    from tabel_transaksi where kode_transaksi = ? and unit = ?", 'ss', $kode_kirim, $unit);
$tgl = tgl_indo($info && $info['tgl'] ? substr((string) $info['tgl'], 0, 10) : '');
$tot_debet = $info ? compat_num($info['tot_debet']) : 0;
$tot_kredit = $info ? compat_num($info['tot_kredit']) : 0;

$keterangan = array();
foreach (db_semua("select distinct keterangan_transaksi from tabel_transaksi where kode_transaksi = ? and unit = ? and keterangan_transaksi <> ''",
        'ss', $kode_kirim, $unit) as $k) {
    $keterangan[] = $k['keterangan_transaksi'];
}

require('../../fpdf17/fpdf.php');

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(12, 15, 12);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();
$pdf->SetFillColor(235, 235, 235);

$pdf->Image('../../Image/nii.png');
$pdf->Ln(3);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'BUKTI JURNAL UMUM', 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(28, 5, 'Nomor Bukti', 0, 0);
$pdf->Cell(0, 5, ': ' . $kode_kirim, 0, 1);
$pdf->Cell(28, 5, 'Tanggal', 0, 0);
$pdf->Cell(0, 5, ': ' . $tgl, 0, 1);
$pdf->Cell(28, 5, 'Unit', 0, 0);
$pdf->Cell(0, 5, ': ' . $unit, 0, 1);
$pdf->Ln(3);

// lebar kolom: total 186 mm (A4 210 - margin 2 x 12)
$w = array(10, 32, 74, 35, 35);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($w[0], 7, 'No', 1, 0, 'C', true);
$pdf->Cell($w[1], 7, 'Kode Rekening', 1, 0, 'C', true);
$pdf->Cell($w[2], 7, 'Nama Rekening', 1, 0, 'C', true);
$pdf->Cell($w[3], 7, 'Debet', 1, 0, 'C', true);
$pdf->Cell($w[4], 7, 'Kredit', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$no = 0;
foreach ($baris as $r) {
    $no++;
    $debet = compat_num($r['debet']);
    $kredit = compat_num($r['kredit']);
    $nama = (string) $r['nama_rek'];
    // potong nama yang lebih panjang dari kolomnya
    while ($nama !== '' && $pdf->GetStringWidth($nama) > $w[2] - 3) {
        $nama = substr($nama, 0, -1);
    }
    $pdf->Cell($w[0], 6, $no, 1, 0, 'C');
    $pdf->Cell($w[1], 6, $r['kode_rekening'], 1, 0, 'L');
    $pdf->Cell($w[2], 6, $nama, 1, 0, 'L');
    $pdf->Cell($w[3], 6, $debet > 0 ? number_format($debet, 0, ',', '.') : '', 1, 0, 'R');
    $pdf->Cell($w[4], 6, $kredit > 0 ? number_format($kredit, 0, ',', '.') : '', 1, 1, 'R');
}
if (!$baris) {
    $pdf->Cell(array_sum($w), 6, 'Nomor bukti tidak ditemukan', 1, 1, 'C');
}

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($w[0] + $w[1] + $w[2], 7, 'Jumlah', 1, 0, 'R', true);
$pdf->Cell($w[3], 7, number_format($tot_debet, 0, ',', '.'), 1, 0, 'R', true);
$pdf->Cell($w[4], 7, number_format($tot_kredit, 0, ',', '.'), 1, 1, 'R', true);
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 6, 'Keterangan', 1, 1, 'L', true);
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 5, $keterangan ? implode("\n", $keterangan) : '-', 1, 'L');
$pdf->Ln(6);

$kolom_ttd = array_sum($w) / 3;
$pdf->SetFont('Arial', 'B', 9);
foreach (array('Dibuat', 'Diperiksa', 'Disetujui') as $i => $judul) {
    $pdf->Cell($kolom_ttd, 6, $judul, 1, $i == 2 ? 1 : 0, 'C', true);
}
foreach (array(0, 1, 2) as $i) {
    $pdf->Cell($kolom_ttd, 22, '', 1, $i == 2 ? 1 : 0);
}

// buang keluaran lain (spasi/peringatan) supaya berkas PDF tidak rusak
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf->Output();
