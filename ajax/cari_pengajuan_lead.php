<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran CIF calon_loan di area lead untuk Status Pengajuan (lead/turun_pangajuan.php), pengganti
 * lead/autosuggesst_rek_aju.php (query sama: area = userid lead).
 * GET q = awalan CIF atau nama. Hasil: JSON [{cif, nama, unit, nama_kel, label}]
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login('json');
header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? (string) $_GET['q'] : '';
if (strlen($q) == 0) {
    echo '[]';
    exit;
}
$userid = (string) $_SESSION['userid'];
$awalan = $q . '%';

// urutan AND/OR sama dengan versi lama
$baris = db_semua(" SELECT calon_loan.UNIT,calon_loan.CIF,calon_loan.no_anggota as NOREK,calon_loan.CUST_SHORT_NAME,nama_kel FROM calon_loan
    left join kelompok on kelompok.code_kel = calon_loan.CODE_KEL
    inner join branch on branch.kode_branch = calon_loan.unit
    WHERE calon_loan.CIF LIKE ? and code_area =? or calon_loan.CUST_SHORT_NAME LIKE ? and code_area =?  ",
    'ssss', $awalan, $userid, $awalan, $userid);

$teks = function ($b, $k) {
    return isset($b[$k]) && $b[$k] !== null ? (string) $b[$k] : '';
};
$hasil = array();
foreach ($baris as $b) {
    // label sama dengan isi daftar saran versi lama: CIF, nama, unit, nama kelompok
    $hasil[] = array(
        'cif' => $teks($b, 'CIF'),
        'nama' => $teks($b, 'CUST_SHORT_NAME'),
        'unit' => $teks($b, 'UNIT'),
        'nama_kel' => $teks($b, 'nama_kel'),
        'label' => trim(preg_replace('/\s+/', ' ', $teks($b, 'CIF') . '  ' . $teks($b, 'CUST_SHORT_NAME') . '  ' . $teks($b, 'UNIT') . '  ' . $teks($b, 'nama_kel'))),
    );
}
echo json_encode($hasil);
