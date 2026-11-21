<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * PDF Rugi Laba unit (tombol PDF di New Report > Rugi Laba, laporan/new_laba.php), dibuat 2026-11-21 atas perintah user
 * (PEKERJAAN_TERTUNDA §32 A2). Dulu tombol PDF membuka neraca_pdf.php (PDF Neraca).
 * Isi dan perhitungan sama dengan layar new_laba.php: rekening rugi-laba tabel_rugi_laba unit (kecuali 59999), SHU
 * operasional, SHU non operasional, SHU sebelum / setelah pajak (estimasi pajak 0); angka bertanda (rugi = minus).
 * Hanya membaca.
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_level('unit');
ob_start();
$userid = (string) $_SESSION['userid'];

$unit = '';
foreach (db_semua("select kode_branch from user where userid=?", 's', $userid) as $id_user) {
    $unit = (string) $id_user['kode_branch'];
}
$cabang = db_satu("select unit, alamat from branch where kode_branch=?", 's', $unit);

// sama dengan laporan/new_laba.php
$rekening = db_semua("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,saldo_jalan,LINE_BALANCE
    FROM `tabel_rugi_laba`
    where unit =? and posisi='rugi-laba' and kode_rekening not in ('59999')
    GROUP BY gr_neraca,kode_rekening ORDER BY gr_neraca,kode_rekening ", 's', $unit);
$saldo_rl = function ($kode) use ($unit) {
    $r = db_satu("select saldo_akhir from tabel_rugi_laba where unit =? and kode_rekening=?", 'ss', $unit, $kode);
    return compat_num($r ? $r['saldo_akhir'] : null);
};
$shu_ops_akhir = $saldo_rl('40000') - $saldo_rl('50000');
$shu_ops_non_akhir = $saldo_rl('57000') - $saldo_rl('58000');
$shu_sb_pajak_akhir = $shu_ops_akhir + $shu_ops_non_akhir;
$estimasi_pajak = 0;
$shu_after_pajak_akhir = $shu_sb_pajak_akhir - $estimasi_pajak;

/** Teks untuk FPDF (font inti memakai Latin-1). */
function teks_pdf($s)
{
    $t = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string) $s);
    return $t === false ? (string) $s : $t;
}
function angka_pdf($v)
{
    return number_format(compat_num($v), 0, ',', '.');
}

require __DIR__ . '/../fpdf17/fpdf.php';
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(12, 12, 12);
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 5, teks_pdf('KOPERASI JASA KEUANGAN SYARIAH NUR INSANI UNIT ' . ($cabang ? $cabang['unit'] : $unit)), 0, 1);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, teks_pdf('ALAMAT ' . ($cabang ? $cabang['alamat'] : '')), 0, 1);
$pdf->SetFont('Arial', 'BU', 10);
$pdf->Cell(0, 8, 'LAPORAN RUGI LABA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 7);
$pdf->Cell(0, 4, teks_pdf('Dicetak ' . date('d-m-Y H:i')), 0, 1, 'R');

$lebar = array(22, 22, 102, 40);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(230, 230, 230);
foreach (array('TYPE COA', 'COA', 'Deskripsi', 'Saldo Akhir') as $i => $judul) {
    $pdf->Cell($lebar[$i], 6, $judul, 1, 0, $i === 3 ? 'R' : 'C', true);
}
$pdf->Ln();
foreach ($rekening as $row) {
    $heading = $row['LINE_BALANCE'] == 'HEADING';
    $pdf->SetFont('Arial', $heading ? 'B' : '', 8);
    $pdf->Cell($lebar[0], 5, teks_pdf($row['LINE_BALANCE']), 1);
    $pdf->Cell($lebar[1], 5, teks_pdf($row['kode_rekening']), 1);
    $pdf->Cell($lebar[2], 5, teks_pdf(substr((string) $row['nama_rekening'], 0, 70)), 1);
    $pdf->Cell($lebar[3], 5, angka_pdf($row['saldo_akhir']), 1, 1, 'R');
}
$pdf->SetFont('Arial', 'B', 8);
foreach (array(
    array('SHU OPERASIONAL', $shu_ops_akhir),
    array('SHU NON OPERASIONAL', $shu_ops_non_akhir),
    array('SHU TAHUN BERJALAN SEBELUM PAJAK', $shu_sb_pajak_akhir),
    array('ESTIMASI TAKSIRAN PAJAK PENGHASILAN', $estimasi_pajak),
    array('SHU TAHUN BERJALAN SETELAH PAJAK', $shu_after_pajak_akhir),
) as $shu) {
    $pdf->Cell($lebar[0] + $lebar[1] + $lebar[2], 6, $shu[0], 1);
    $pdf->Cell($lebar[3], 6, angka_pdf($shu[1]), 1, 1, 'R');
}
// FPDF 1.7: Output(nama, tujuan)
$pdf->Output('rugi_laba_' . preg_replace('/[^A-Za-z0-9_-]/', '', $unit) . '.pdf', 'I');
exit;
