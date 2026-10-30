<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Cek kode kelompok untuk menu Kelompok (kelompok.php) sebelum disimpan.
 * GET code = kode kelompok. Hasil: JSON {ada, unit_sendiri, unit}.
 */
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login('json');
header('Content-Type: application/json; charset=utf-8');

$code = isset($_GET['code']) ? (string) $_GET['code'] : '';
$user = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $_SESSION['userid']);
$branch = $user ? (string) $user['kode_branch'] : '';
$kel = $code === '' ? null : db_satu('SELECT code_unit FROM kelompok WHERE code_kel = ?', 's', $code);

echo json_encode(array(
    'ada' => (bool) $kel,
    'unit_sendiri' => $kel ? (string) $kel['code_unit'] === $branch : false,
    'unit' => $kel ? (string) $kel['code_unit'] : '',
));
