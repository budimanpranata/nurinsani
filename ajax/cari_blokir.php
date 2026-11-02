<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran CIF / kode kelompok untuk Buka Blokir Simpanan (lead/buka_blokir.php), pengganti lead/autosuggesst_rek.php
 * (query sama): cari anggota per awalan CIF atau nama; bila kosong cari di loan; bila masih kosong cari kode/nama
 * kelompok. Seperti versi lama, tidak disaring per unit/area.
 * GET q = awalan. Hasil: JSON [{cif, nama, unit, nama_kel, label}] (cif berisi kode kelompok untuk hasil kelompok).
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
$awalan = $q . '%';

$baris = db_semua("SELECT anggota.UNIT,anggota.CIF,anggota.NOREK,anggota.CUST_SHORT_NAME,nama_kel FROM anggota
    left join kelompok on kelompok.code_kel = anggota.CODE_KEL WHERE anggota.CIF LIKE ? or anggota.CUST_SHORT_NAME LIKE ? ",
    'ss', $awalan, $awalan);
if (count($baris) == 0) {
    $baris = db_semua(" SELECT loan.UNIT,loan.CIF,loan.no_anggota as NOREK,loan.CUST_SHORT_NAME,nama_kel FROM loan
        left join kelompok on kelompok.code_kel = loan.CODE_KEL WHERE loan.CIF LIKE ? or loan.CUST_SHORT_NAME LIKE ? ",
        'ss', $awalan, $awalan);
}
if (count($baris) == 0) {
    $baris = db_semua(" SELECT anggota.code_kel as CIF ,nama_kel as CUST_SHORT_NAME FROM anggota
        left join kelompok on kelompok.code_kel = anggota.CODE_KEL WHERE anggota.code_kel LIKE ? or kelompok.nama_kel LIKE ? group by anggota.code_kel ",
        'ss', $awalan, $awalan);
}

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
