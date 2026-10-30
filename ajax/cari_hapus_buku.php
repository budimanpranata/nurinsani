<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran anggota untuk menu Hapus Buku (pengganti autosuggesst_buku.php, query sama).
 * GET q = awalan CIF atau nama. Hasil: JSON [{cif, nama, pokok, margin, ke, saldo}]
 *   pokok = os - saldo_margin, margin = saldo_margin, saldo = sum(kredit-debit) simpanan selain tipe 04/05.
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

// urutan AND/OR sama dengan versi lama: (CIF cocok ...) OR (nama cocok ...)
$baris = db_semua("SELECT anggota.CIF, anggota.CUST_SHORT_NAME, os, saldo_margin, saldo_margin AS MARGIN, ke AS KE, sum(kredit-debit) AS SALDO
    FROM anggota
    LEFT JOIN loan ON anggota.CIF = loan.cif
    LEFT JOIN simpanan ON anggota.CIF = simpanan.cif
    WHERE anggota.CIF LIKE ? AND anggota.UNIT = ? AND TYPE NOT IN ('04','05')
       OR anggota.CUST_SHORT_NAME LIKE ? AND anggota.UNIT = ? AND TYPE NOT IN ('04','05')
    GROUP BY anggota.cif", 'ssss', $awalan, $unit, $awalan, $unit);

$hasil = array();
foreach ($baris as $b) {
    $hasil[] = array(
        'cif' => (string) $b['CIF'],
        'nama' => (string) $b['CUST_SHORT_NAME'],
        'pokok' => (string) ($b['os'] - $b['saldo_margin']),
        'margin' => (string) $b['MARGIN'],
        'ke' => (string) $b['KE'],
        'saldo' => (string) $b['SALDO'],
    );
}
echo json_encode($hasil);
