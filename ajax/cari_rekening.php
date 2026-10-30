<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran rekening anggota di unit user (pengganti autosuggesst_rek.php, query sama).
 * GET q = awalan CIF atau nama. Hasil: JSON [{norek, nama, cif, unit, nama_kel}]
 * Catatan: query cadangan versi lama (dari tabel loan) selalu gagal karena memakai kolom anggota.UNIT,
 * sehingga bila anggota tidak ditemukan hasilnya kosong.
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

// urutan AND/OR sama dengan versi lama: (CIF cocok AND unit) OR (nama cocok AND unit)
$baris = db_semua("SELECT anggota.UNIT, anggota.CIF, anggota.NOREK, anggota.CUST_SHORT_NAME, nama_kel
    FROM anggota
    LEFT JOIN kelompok ON kelompok.code_kel = anggota.CODE_KEL
    WHERE anggota.CIF LIKE ? AND anggota.UNIT = ? OR anggota.CUST_SHORT_NAME LIKE ? AND anggota.UNIT = ?",
    'ssss', $awalan, $unit, $awalan, $unit);

$hasil = array();
foreach ($baris as $b) {
    $hasil[] = array(
        'norek' => (string) $b['NOREK'],
        'nama' => (string) $b['CUST_SHORT_NAME'],
        'cif' => (string) $b['CIF'],
        'unit' => (string) $b['UNIT'],
        'nama_kel' => (string) $b['nama_kel'],
    );
}
echo json_encode($hasil);
