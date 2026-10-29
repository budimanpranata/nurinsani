<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran anggota (Entry Data / new2.php) berdasarkan awalan CIF atau nama, di unit user yang login.
 * GET q. Hasil: JSON berisi label + nilai untuk tiap isian form (kunci = id isian).
 * Query sama dengan autosuggesst_sim.php: data dari calon_loan, bila kosong dari loan.
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

$kolom = 'anggota.CIF, anggota.CUST_SHORT_NAME, anggota.CODE_KEL, anggota.NOREK, anggota.UNIT, HADDRESS1, HADDRESS2,
    HADDRESS3, HADDRESS4, HADDRESS5, HPOST_CODE, MOBILE_PHONE, BIRTH_DATE, ID_NUMBER, TEMPAT_LAHIR, MARITAL_STATUS,
    MOTHER_NAME, NPWP, SOURCE_INCOME, EDUCATION, ID_EXPIRED, WARIS, GENDER, %1$s.CAO, tgl_wakalah, Plafond, usaha,
    barang_satu, barang_dua, barang_tiga, barang_empat, barang_lima, barang_enam, barang_tujuh, barang_delapan,
    barang_sembilan, barang_sepuluh, harga_satu, harga_dua, harga_tiga, harga_empat, harga_lima, harga_enam,
    harga_tujuh, harga_delapan, harga_sembilan, harga_sepuluh, SUM(KREDIT - DEBIT) AS saldo';
$sumber = array(
    "SELECT $kolom FROM anggota
        LEFT JOIN calon_loan ON anggota.CIF = calon_loan.cif
        LEFT JOIN barang ON barang.cif = calon_loan.cif
        INNER JOIN simpanan ON simpanan.cif = anggota.cif
        WHERE anggota.UNIT = ? AND (anggota.CIF LIKE ? OR anggota.CUST_SHORT_NAME LIKE ?)
        GROUP BY anggota.CIF LIMIT 10",
    "SELECT $kolom FROM anggota
        LEFT JOIN loan ON loan.cif = anggota.cif
        LEFT JOIN barang ON barang.cif = loan.cif
        INNER JOIN simpanan ON simpanan.cif = anggota.cif
        INNER JOIN kelompok ON loan.code_kel = kelompok.code_kel
        WHERE anggota.UNIT = ? AND (anggota.CIF LIKE ? OR anggota.CUST_SHORT_NAME LIKE ?)
        GROUP BY anggota.CIF LIMIT 10",
);
$baris = array();
foreach (array('calon_loan', 'loan') as $i => $tabel) {
    $st = $db->prepare(sprintf($sumber[$i], $tabel));
    if ($st && $st->bind_param('sss', $unit, $awalan, $awalan) && $st->execute()) {
        $baris = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    if ($baris) break;
}

$urutan = array('satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh');
$tanggal = function ($v) {
    return ($v && substr($v, 0, 4) !== '0000') ? substr($v, 0, 10) : '';
};
$hasil = array();
foreach ($baris as $r) {
    $data = array(
        'label' => trim($r['HADDRESS1'] . '  ' . $r['CIF'] . '  ' . $r['CUST_SHORT_NAME'] . '  ' . number_format((float) $r['saldo'])),
        'cif' => $r['CIF'], 'nama' => $r['CUST_SHORT_NAME'], 'no_anggota' => $r['NOREK'], 'branch' => $r['UNIT'],
        'ibu' => $r['MOTHER_NAME'], 'alamat' => $r['HADDRESS1'], 'lurah' => $r['HADDRESS2'], 'kec' => $r['HADDRESS3'],
        'kota' => $r['HADDRESS4'], 'rt' => $r['HADDRESS5'], 'code_kel' => $r['CODE_KEL'], 'wali' => $r['WARIS'],
        'no_tlp' => $r['MOBILE_PHONE'], 'tgl_lahir' => $tanggal($r['BIRTH_DATE']), 'tempat_lahir' => $r['TEMPAT_LAHIR'],
        'pendidikan' => $r['EDUCATION'], 'npwp' => $r['NPWP'], 'kd_pos' => $r['HPOST_CODE'],
        'status_kawin' => $r['MARITAL_STATUS'], 'id_expire' => $r['ID_EXPIRED'], 'id' => $r['ID_NUMBER'],
        'sumber' => $r['SOURCE_INCOME'], 'jenis_kel' => $r['GENDER'], 'code_ao' => $r['CAO'],
        'tgl_skep' => $tanggal($r['tgl_wakalah']), 'pla' => $r['Plafond'], 'usaha' => $r['usaha'],
    );
    foreach ($urutan as $u) {
        $data['barang_' . $u] = $r['barang_' . $u];
        $data['harga_' . $u] = $r['harga_' . $u];
    }
    $hasil[] = $data;
}
echo json_encode($hasil);
