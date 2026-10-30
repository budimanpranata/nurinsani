<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran anggota untuk Pemeliharaan CIM (dim.php), pengganti autosuggesst_dim.php (query sama).
 * GET q = awalan CIF, nama, atau nomor KTP. Hasil: JSON berisi label + nilai tiap isian form (kunci = id isian).
 * Sama dengan versi lama: no_anggota diambil dari calon_loan (menimpa NOREK), barang/harga ke-8 tidak ikut di-SELECT.
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

// urutan AND/OR sama dengan versi lama
$baris = db_semua("SELECT nama_kel,calon_loan.no_anggota,tgl_wakalah, anggota.CIF,anggota.CUST_SHORT_NAME,anggota.CODE_KEL,anggota.NOREK,anggota.UNIT,calon_loan.DEAL_TYPE,HADDRESS1,HADDRESS2,HADDRESS3,HADDRESS4,HADDRESS5,HPOST_CODE,MOBILE_PHONE,HOME_PHONE,GENDER,BIRTH_DATE,
    ID_NUMBER,CITIZENSHIP,TEMPAT_LAHIR,MARITAL_STATUS,RELIGION,MOTHER_NAME,NPWP,SOURCE_INCOME,EDUCATION,ID_EXPIRED,WARIS,calon_loan.CAO,anggota.USERID,tgl_wakalah,Plafond,usaha,
    barang_satu,barang_dua,barang_tiga,barang_empat,barang_lima, barang_enam, barang_tujuh, barang_sembilan, barang_sepuluh,Plafond,
    harga_satu,harga_dua,harga_tiga,harga_empat,harga_lima,harga_enam,harga_tujuh, harga_sembilan,harga_sepuluh,nama_usaha,tgl_akad,tenor,pekerjaan_pasangan,suffix
    FROM anggota
    left join calon_loan on anggota.CIF=calon_loan.cif
    left join barang on barang.id_barang=calon_loan.no_anggota
    left join kelompok on calon_loan.code_kel = kelompok.code_kel
    WHERE anggota.CIF LIKE ? and anggota.UNIT=? or anggota.CUST_SHORT_NAME LIKE ? and anggota.UNIT =? or ID_NUMBER LIKE ? and anggota.UNIT=?",
    'ssssss', $awalan, $unit, $awalan, $unit, $awalan, $unit);

$teks = function ($v) {
    return $v === null ? '' : (string) $v;
};
// tanggal ke bentuk YYYY-MM-DD untuk isian tanggal (nilai tersimpan tetap sama)
$tanggal = function ($v) {
    return ($v && substr((string) $v, 0, 4) !== '0000') ? substr((string) $v, 0, 10) : '';
};
$urutan = array('satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh');

$hasil = array();
foreach ($baris as $r) {
    $data = array(
        'label' => trim($teks($r['nama_kel']) . '  ' . $r['CIF'] . '  ' . $r['CUST_SHORT_NAME'] . '  ' . $teks($r['ID_NUMBER'])),
        'nama' => $teks($r['CUST_SHORT_NAME']), 'cif' => $teks($r['CIF']),
        'no_anggota' => $teks($r['no_anggota']), // versi lama: fill3(NOREK) lalu ditimpa fill50(no_anggota)
        'branch' => $teks($r['UNIT']), 'ibu' => $teks($r['MOTHER_NAME']), 'alamat' => $teks($r['HADDRESS1']),
        'lurah' => $teks($r['HADDRESS2']), 'kec' => $teks($r['HADDRESS3']), 'kota' => $teks($r['HADDRESS4']),
        'wali' => $teks($r['WARIS']), 'no_tlp' => $teks($r['MOBILE_PHONE']), 'tgl_lahir' => $tanggal($r['BIRTH_DATE']),
        'tempat_lahir' => $teks($r['TEMPAT_LAHIR']), 'code_kel' => $teks($r['CODE_KEL']), 'pendidikan' => $teks($r['EDUCATION']),
        'npwp' => $teks($r['NPWP']), 'kd_pos' => $teks($r['HPOST_CODE']), 'status_kawin' => $teks($r['MARITAL_STATUS']),
        'id_expire' => $teks($r['ID_EXPIRED']), 'id' => $teks($r['ID_NUMBER']), 'sumber' => $teks($r['SOURCE_INCOME']),
        'jenis_kel' => $teks($r['GENDER']), 'code_ao' => $teks($r['CAO']), 'tgl_skep' => $tanggal($r['tgl_wakalah']),
        'rt' => $teks($r['HADDRESS5']), 'pla' => $teks($r['Plafond']), 'usaha' => $teks($r['usaha']),
        'nama_usaha' => $teks($r['nama_usaha']), 'tgl_akad' => $tanggal($r['tgl_akad']), 'tenor' => $teks($r['tenor']),
        'pekerjaan' => $teks($r['pekerjaan_pasangan']), 'suffix' => $teks($r['suffix']), 'tlp_penjamin' => $teks($r['HOME_PHONE']),
    );
    foreach ($urutan as $u) {
        // barang/harga ke-8 tidak ada di SELECT versi lama sehingga selalu kosong
        $data['barang_' . $u] = $u === 'delapan' ? '' : $teks($r['barang_' . $u]);
        $data['harga_' . $u] = $u === 'delapan' ? '' : $teks($r['harga_' . $u]);
    }
    $hasil[] = $data;
}
echo json_encode($hasil);
