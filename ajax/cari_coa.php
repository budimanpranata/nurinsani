<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran rekening COA (LINE_BALANCE DETAIL) untuk menu jurnal; pengganti autosuggesst.php.
 * GET q = awalan kode rekening atau bagian nama rekening. Hasil: JSON [{kode_rek, nama_rek}].
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo '[]';
    exit;
}

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
if ($q === '') {
    echo '[]';
    exit;
}

// kriteria sama dengan autosuggesst.php: kode diawali q, atau nama mengandung q
$hasil = db_semua("SELECT kode_rek, nama_rek FROM coa
    WHERE LINE_BALANCE='DETAIL' AND (kode_rek LIKE ? OR nama_rek LIKE ?)
    ORDER BY kode_rek LIMIT 50", 'ss', $q . '%', '%' . $q . '%');
echo json_encode($hasil);
