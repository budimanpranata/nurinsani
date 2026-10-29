<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran kelompok (yang sudah punya pinjaman) di unit user yang login.
 * GET q = awalan kode atau nama kelompok. Hasil: JSON [{code_kel, nama_kel, cao, alamat, hari}].
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

$db = db_ni();
$st = $db->prepare('SELECT kode_branch FROM user WHERE userid = ?');
$st->bind_param('s', $_SESSION['userid']);
$st->execute();
$user = $st->get_result()->fetch_assoc();
$unit = $user ? $user['kode_branch'] : '';

$awalan = $q . '%';
$st = $db->prepare('SELECT kelompok.code_kel, kelompok.nama_kel, kelompok.cao, kelompok.alamat, MAX(loan.hari) AS hari
    FROM kelompok INNER JOIN loan ON loan.code_kel = kelompok.code_kel
    WHERE kelompok.code_unit = ? AND (kelompok.code_kel LIKE ? OR kelompok.nama_kel LIKE ?)
    GROUP BY kelompok.code_kel, kelompok.nama_kel, kelompok.cao, kelompok.alamat
    ORDER BY kelompok.code_kel LIMIT 10');
if (!$st || !$st->bind_param('sss', $unit, $awalan, $awalan) || !$st->execute()) {
    echo '[]';
    exit;
}
echo json_encode($st->get_result()->fetch_all(MYSQLI_ASSOC));
