<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran rekening deposito di unit user untuk Setor/Penarikan level kp (kp/view/view_stor.php), pengganti
 * kp/view/autosuggesst_rek.php (query sama: awalan CIF atau nama deposan, deposito unit user).
 * GET q = awalan. Hasil: JSON [{norek, nama, unit, label}]
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
$user = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $_SESSION['userid']);
$unit = $user ? (string) $user['kode_branch'] : '';
$awalan = $q . '%';

// urutan AND/OR sama dengan versi lama
$baris = db_semua("SELECT deposan.UNIT,deposan.CIF,deposito.NOREK,deposan.CUST_SHORT_NAME FROM deposito
    left join deposan on deposan.cif=deposito.cif
    WHERE deposito.CIF LIKE ? and deposito.UNIT=? or deposan.CUST_SHORT_NAME LIKE ? and deposito.UNIT=?",
    'ssss', $awalan, $unit, $awalan, $unit);

$teks = function ($b, $k) {
    return isset($b[$k]) && $b[$k] !== null ? (string) $b[$k] : '';
};
$hasil = array();
foreach ($baris as $b) {
    $hasil[] = array(
        'norek' => $teks($b, 'NOREK'),
        'nama' => $teks($b, 'CUST_SHORT_NAME'),
        'unit' => $teks($b, 'UNIT'),
        'label' => trim(preg_replace('/\s+/', ' ', $teks($b, 'NOREK') . '  ' . $teks($b, 'CUST_SHORT_NAME') . '  ' . $teks($b, 'UNIT'))),
    );
}
echo json_encode($hasil);
