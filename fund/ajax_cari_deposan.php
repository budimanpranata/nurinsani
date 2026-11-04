<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran deposan (isian No Anggota di fund/transaksi/input_trns.php): awalan CIF atau nama deposan, sama dengan
 * fund/autosuggest.php versi lama. GET q. Hasil JSON [{cif, nama, label}]. Wajib login.
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login('json');
header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
if ($q === '') {
    echo '[]';
    exit;
}
$hasil = array();
foreach (db_semua("SELECT CUST_SHORT_NAME, cif FROM deposan WHERE cif LIKE ? or CUST_SHORT_NAME LIKE ? LIMIT 50", 'ss', $q . '%', $q . '%') as $r) {
    $hasil[] = array('cif' => (string) $r['cif'], 'nama' => (string) $r['CUST_SHORT_NAME'], 'label' => $r['cif'] . '  ' . $r['CUST_SHORT_NAME']);
}
echo json_encode($hasil);
