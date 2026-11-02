<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Export Rugi Laba ke .xlsx (versi lama: tabel HTML yang dinamai laba-rugi.xls). Data kecil, jadi dibuat dalam satu request.
 * Logika sama dengan versi lama (keputusan user: hanya migrasi kode). Perhatikan perbedaan versi lama dengan layar
 * (new_laba.php) yang dipertahankan:
 *   - daftar rekening ditambah filter gr_neraca='rugi-laba'
 *   - pendapatan/beban non operasional (57000/58000) selalu diambil dari unit '001'
 *   - SHU sebelum pajak = SHU operasional + nilai mutlak SHU non operasional
 * Saldo akhir negatif ditulis sebagai nilai mutlak; estimasi pajak 0 (tidak pernah diisi di versi lama).
 */
require_once __DIR__ . '/../inc/xlsx_writer.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "unit") {
    header("location:../index.html");
    exit;
}

$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit = $id_user['kode_branch'];
}
$unit = (string) $unit;

$rekening = db_semua("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,saldo_jalan,LINE_BALANCE
    FROM `tabel_rugi_laba`
    where unit =? and posisi='rugi-laba' and gr_neraca='rugi-laba' and kode_rekening not in ('59999')
    GROUP BY gr_neraca,kode_rekening ", 's', $unit);

function saldo_rl($unit, $kode)
{
    return db_satu("select saldo_jalan,saldo_awal,saldo_akhir from tabel_rugi_laba where unit =? and kode_rekening=?", 'ss', $unit, $kode);
}
$nilai = function ($baris, $kolom) {
    return compat_num($baris ? $baris[$kolom] : null);
};
$total_pendapan = saldo_rl($unit, '40000');
$total_biaya = saldo_rl($unit, '50000');
// versi lama: unit '001' tetap (bukan unit user)
$total_pendapan_n = saldo_rl('001', '57000');
$total_beban = saldo_rl('001', '58000');
$shu_ops_akhir = $nilai($total_pendapan, 'saldo_akhir') - $nilai($total_biaya, 'saldo_akhir');
$shu_ops_non_akhir = $nilai($total_pendapan_n, 'saldo_akhir') - $nilai($total_beban, 'saldo_akhir');
$shu_sb_pajak_akhir = $shu_ops_akhir + abs($shu_ops_non_akhir);
$estimasi_pajak = 0;
$shu_after_pajak_akhir = $shu_sb_pajak_akhir - $estimasi_pajak;

$dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nurinsani_export' . DIRECTORY_SEPARATOR . bin2hex(random_bytes(12));
if (!@mkdir($dir, 0700, true)) {
    die('Folder sementara export tidak bisa dibuat.');
}
$xml = '';
$baris = 1;
foreach ($rekening as $row) {
    $akhir = compat_num($row['saldo_akhir']);
    $baris++;
    $xml .= xlsx_baris_xml($baris, array(
        array('s', $row['LINE_BALANCE']),
        array('s', $row['kode_rekening']),
        array('s', $row['nama_rekening']),
        array('n', $akhir < 0 ? abs($akhir) : $akhir),
    ), $row['LINE_BALANCE'] == 'HEADING' ? 2 : 0);
}
foreach (array(
    array('SHU OPERASIONAL', $shu_ops_akhir),
    array('SHU NON OPERASIONAL', abs($shu_ops_non_akhir)),
    array('SHU TAHUN BERJALAN SEBELUM PAJAK', $shu_sb_pajak_akhir),
    array('ESTIMASI TAKSIRAN PAJAK PENGHASILAN', $estimasi_pajak),
    array('SHU TAHUN BERJALAN SETELAH PAJAK', $shu_after_pajak_akhir),
) as $shu) {
    $baris++;
    $xml .= xlsx_baris_xml($baris, array(array('s', $shu[0]), array('s', ''), array('s', ''), array('n', $shu[1])), 2);
}
xlsx_tulis_baris($dir . '/data.part', $xml);
$berkas = $dir . '/laba-rugi.xlsx';
$ok = xlsx_rakit($berkas, 'Laba Rugi', array('TYPE COA', 'COA', 'Deskripsi', 'Saldo Akhir'), array(12, 12, 48, 18), $dir . '/data.part');
while (ob_get_level() > 0) {
    ob_end_clean();
}
if (!$ok) {
    die('Gagal membuat file xlsx.');
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="laba-rugi.xlsx"');
header('Content-Length: ' . filesize($berkas));
header('Cache-Control: no-store');
readfile($berkas);
foreach (glob($dir . '/*') ?: array() as $f) {
    @unlink($f);
}
@rmdir($dir);
