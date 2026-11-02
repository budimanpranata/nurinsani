<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Proses Approve dari form new4.php (level lead): perbarui data anggota, tandai calon_loan gol='1' (sudah di-approve,
 * hilang dari daftar Approval Pengajuan), perbarui kelompok/AO di tunggakan, lalu kembali ke data_approval.php.
 * Logika sama dengan versi lama; query kini prepared statement. var_dump($cif) sisa debug dihapus.
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "lead") {
    header("location:index.html");
    exit;
}

$pos = function ($k) {
    return isset($_POST[$k]) ? (string) $_POST[$k] : '';
};
$code_kel = $pos('code_kel');
$cao = $pos('code_ao');
$cif = $pos('cif');
$nama = $pos('nama');
$nama1 = $pos('wali');

$i = db_jalankan("update anggota set
    CODE_KEL=?, CAO=?, CUST_SHORT_NAME=?, HADDRESS1=?, HADDRESS2=?, HADDRESS3=?, HPOST_CODE=?, HADDRESS4=?, HADDRESS5=?,
    MOBILE_PHONE=?, HOME_PHONE=?, BIRTH_DATE=?, ID_NUMBER=?, MOTHER_NAME=?, NPWP=?, CITIZENSHIP=?, ID_EXPIRED=?, WARIS=?
    where CIF=? ", str_repeat('s', 19),
    $code_kel, $cao, $nama, $pos('alamat'), $pos('lurah'), $pos('kec'), $pos('kd_pos'), $pos('kota'), $pos('rt'),
    $pos('no_tlp'), $pos('tlp_penjamin'), $pos('tgl_lahir'), $pos('id'), $pos('ibu'), $pos('npwp'), $pos('tempat_lahir'),
    $pos('id_expire'), $nama1, $cif);

$b = db_jalankan("update calon_loan set gol='1' where cif =?", 's', $cif);

db_jalankan("update tunggakan set CODE_KEL=?,CAO=? where CIF =?", 'sss', $code_kel, $cao, $cif);

if ($i || $b) {
    // versi lama memakai nama yang sudah di-escape untuk SQL; addslashes menjaga alert tetap benar bila nama berisi tanda kutip
    echo "<script> alert ('data Anggota " . addslashes($nama) . " " . addslashes($cif) . " sudah di Apporve ')</script>";
    echo "<script language='javascript'>document.location.href='data_approval.php'</script>";
} else {
    echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
}
