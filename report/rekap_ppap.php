<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Report > PPAP > Rekap (PDF): rekap kolektabilitas pembiayaan unit, bagian MURABAHAH (status_app bukan MUSYAROKAH) dan
 * MUSYARAKAH, per golongan (gol 1 Lancar, 2 Kurang Lancar, 3 Diragukan, 4 Macet): jumlah debitur, plafond, saldo pinjaman
 * (os - saldo_margin), PPAP (0,5% / 10% / 50% / 100% saldo, dibulatkan ke bawah per baris; total = jumlah tanpa pembulatan).
 * Query, angka, dan tata letak sama dengan versi lama, kecuali (2026-11-21, perintah user, PEKERJAAN_TERTUNDA §27 A6):
 *   - kolom "Nilai Anggunan" dihapus (selalu kosong: variabel tidak pernah diisi dan tidak ada data agunan di database)
 *   - kolom "Prosent NPL-Gross" diisi (dulu selalu kosong): per golongan = saldo golongan / total saldo bagian itu;
 *     baris Jumlah Total = NPL-Gross = (saldo Kurang Lancar + Diragukan + Macet) / total saldo
 *   - migrasi: prepared statement, wajib login level unit (versi lama memakai conn2 / mysql_*).
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_level('unit');
ob_start();
include __DIR__ . '/../indo_tgl.php';
$userid = (string) $_SESSION['userid'];
$tgl = isset($_SESSION['stgl_npl']) ? (string) $_SESSION['stgl_npl'] : '';
$periode = tgl_indo($tgl);

$dsmo = db_satu("Select nama,kode_branch FROM user WHERE userid=?", 's', $userid);
$kode_branch = $dsmo ? (string) $dsmo['kode_branch'] : '';
$br = db_satu("Select unit, alamat FROM branch WHERE kode_branch=?", 's', $kode_branch);
$nama_unit = $br ? $br['unit'] : '';
$alamat = $br ? $br['alamat'] : '';

/** Data satu bagian: [gol => [noa, pla, saldo]] untuk musyarakah (true) atau murabahah (false). */
function rekap_ppap_data($unit, $musyarakah)
{
    $hasil = array();
    foreach (array(1, 2, 3, 4) as $gol) {
        $d = db_satu("SELECT count(cif) as noa, sum(Plafond) as pla, sum(os-saldo_margin) as saldo from loan
            where gol=? AND loan.unit =? and os>0 and " . ($musyarakah ? "status_app ='MUSYAROKAH'" : "status_app not in ('MUSYAROKAH')"),
            'ss', (string) $gol, (string) $unit);
        $hasil[$gol] = array(compat_num($d ? $d['noa'] : 0), compat_num($d ? $d['pla'] : 0), compat_num($d ? $d['saldo'] : 0));
    }
    return $hasil;
}
function persen_ppap($a, $b)
{
    return $b != 0 ? number_format($a / $b * 100, 2, ',', '.') . '%' : '0,00%';
}

require __DIR__ . '/../fpdf17/fpdf.php';
while (ob_get_level() > 0) {
    ob_end_clean();
}
$pdf = new FPDF('L', 'mm', array(279.4, 381));
$pdf->AddPage();
$pdf->SetTopMargin(15);
$pdf->SetLeftMargin(12);
$pdf->SetFillColor(255, 255, 255);

$pdf->SetFont('Arial', 'B', 11);
$pdf->MultiCell(0, 5, 'KOPERASI SIMPAN PINJAM DAN PEMBIAYAAN SYARIAH NUR INSANI UNIT ' . $nama_unit, 0, 'J', 1);
$pdf->MultiCell(0, 5, 'ALAMAT ' . $alamat, 0, 'J', 1);
$pdf->SetFont('Arial', 'BU', 12);
$pdf->Ln(4);
$pdf->MultiCell(0, 5, 'REKAP KOLEKTABILITAS PEMBIAYAAN', 0, 'J', 1);
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0, 5, 'Tanggal ' . $periode, 0, 'J', 1);
$pdf->Ln(4);

$nama_gol = array(1 => 'Kredit Lancar', 2 => 'Kredit Kurang Lancar', 3 => 'Kredit Diragukan', 4 => 'Kredit Macet');
$tarif = array(1 => 0.5, 2 => 10, 3 => 50, 4 => 100);
foreach (array('MURABAHAH' => false, 'MUSYARAKAH' => true) as $judul => $musyarakah) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->MultiCell(0, 5, 'KOLEKTABILITAS ' . $judul, 0, 'J', 1);
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15, 5, 'No', 1, 0, 'C');
    $pdf->Cell(55, 5, 'Kelompok Kolektabilitas', 1, 0, 'C');
    $pdf->Cell(66, 5, 'Jumlah Debitur', 1, 0, 'C');
    $pdf->Cell(44, 5, 'Plafond ', 1, 0, 'C');
    $pdf->Cell(44, 5, 'Saldo Pinjaman', 1, 0, 'C');
    $pdf->Cell(44, 5, 'Prosent NPL-Gross', 1, 0, 'C');
    $pdf->Cell(44, 5, 'PPAP', 1, 0, 'C');

    $data = rekap_ppap_data($kode_branch, $musyarakah);
    $tot_noa = $tot_pla = $tot_saldo = $tot_ppap = 0;
    foreach ($data as $g) {
        $tot_saldo += $g[2];
    }
    foreach ($data as $gol => $g) {
        $ppap = $tarif[$gol] / 100 * $g[2];
        $tot_noa += $g[0];
        $tot_pla += $g[1];
        $tot_ppap += $ppap;
        $pdf->Ln();
        $pdf->Cell(15, 5, (string) $gol, 1, 0, 'L', 1);
        $pdf->Cell(55, 5, $nama_gol[$gol], 1, 0, 'L', 1);
        $pdf->Cell(66, 5, (string) $g[0], 1, 0, 'R');
        $pdf->Cell(44, 5, number_format($g[1]), 1, 0, 'R');
        $pdf->Cell(44, 5, number_format($g[2]), 1, 0, 'R');
        $pdf->Cell(44, 5, persen_ppap($g[2], $tot_saldo), 1, 0, 'R');
        $pdf->Cell(44, 5, number_format(floor($ppap)), 1, 0, 'R');
    }
    $pdf->Ln();
    $pdf->Cell(70, 5, 'Jumlah Total', 1, 0, 'L', 1);
    $pdf->Cell(66, 5, (string) $tot_noa, 1, 0, 'R');
    $pdf->Cell(44, 5, number_format($tot_pla), 1, 0, 'R');
    $pdf->Cell(44, 5, number_format($tot_saldo), 1, 0, 'R');
    // NPL-Gross = (Kurang Lancar + Diragukan + Macet) / total saldo
    $pdf->Cell(44, 5, persen_ppap($data[2][2] + $data[3][2] + $data[4][2], $tot_saldo), 1, 0, 'R');
    $pdf->Cell(44, 5, number_format($tot_ppap), 1, 0, 'R');
    $pdf->Ln(9);
}

$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell(40, 5, 'DIBUAT OLEH', 1, 0, 'C');
$pdf->Cell(40, 5, 'DIPERIKSA OLEH ', 1, 0, 'C');
$pdf->Cell(40, 5, 'DI SETUJUI OLEH ', 1, 0, 'C');
$pdf->Ln(20);
$pdf->Cell(40, 5, 'NAMA OFFICER', 1, 0, 'C');
$pdf->Cell(40, 5, 'ATASAN LANGSUNG', 1, 0, 'C');
$pdf->Cell(40, 5, 'ATASAN ATASAN LANGSUNG', 1, 0, 'C');
$pdf->Output();
exit;
