<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran CIF / kode kelompok untuk menu Kartu Angsuran (pengganti autosuggesst_kel.php, perilaku sama):
 * 1. anggota (CIF/nama), 2. bila kosong: loan, 3. bila kosong: kode/nama kelompok.
 * Catatan versi lama: pada tahap 1, pencarian nama tidak dibatasi unit (urutan AND/OR) dan jumlah hasil tidak dibatasi.
 * GET q. Hasil: JSON [{CIF, CUST_SHORT_NAME, UNIT, nama_kel, label}].
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo '[]';
    exit;
}
$q = isset($_GET['q']) ? (string) $_GET['q'] : '';
if (strlen($q) == 0) {
    echo '[]';
    exit;
}

$user = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $_SESSION['userid']);
$unit = $user ? (string) $user['kode_branch'] : '';
$awalan = $q . '%';

$hasil = db_semua("SELECT anggota.UNIT, anggota.CIF, anggota.NOREK, anggota.CUST_SHORT_NAME, nama_kel FROM anggota
    LEFT JOIN kelompok ON kelompok.code_kel = anggota.CODE_KEL
    WHERE anggota.CIF LIKE ? AND anggota.unit = ? OR anggota.CUST_SHORT_NAME LIKE ?", 'sss', $awalan, $unit, $awalan);
if (!$hasil) {
    $hasil = db_semua("SELECT loan.UNIT, loan.CIF, loan.no_anggota AS NOREK, loan.CUST_SHORT_NAME, nama_kel FROM loan
        LEFT JOIN kelompok ON kelompok.code_kel = loan.CODE_KEL
        WHERE loan.CIF LIKE ? AND loan.unit = ? OR loan.CUST_SHORT_NAME LIKE ? AND loan.unit = ?",
        'ssss', $awalan, $unit, $awalan, $unit);
}
if (!$hasil) {
    $hasil = db_semua("SELECT anggota.code_kel AS CIF, nama_kel AS CUST_SHORT_NAME FROM anggota
        LEFT JOIN kelompok ON kelompok.code_kel = anggota.CODE_KEL
        WHERE anggota.code_kel LIKE ? AND anggota.unit = ? OR kelompok.nama_kel LIKE ? AND anggota.unit = ?
        GROUP BY anggota.code_kel", 'ssss', $awalan, $unit, $awalan, $unit);
}

$keluar = array();
foreach ($hasil as $r) {
    $r += array('UNIT' => null, 'nama_kel' => null);
    $r['label'] = $r['CIF'] . '  ' . $r['CUST_SHORT_NAME'] . '  ' . $r['UNIT'] . '  ' . $r['nama_kel'];
    $keluar[] = $r;
}
echo json_encode($keluar);
