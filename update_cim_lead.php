<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Proses Approve dari form new4.php (level lead): perbarui data anggota, tandai calon_loan gol='1' (sudah di-approve,
 * hilang dari daftar Approval Pengajuan), perbarui kelompok/AO di tunggakan, lalu kembali ke data_approval.php.
 * Logika sama dengan versi lama; query kini prepared statement. var_dump($cif) sisa debug dihapus.
 * Perbaikan 2026-11-03 (laporan user): Tempat Lahir disimpan ke TEMPAT_LAHIR (dulu menimpa CITIZENSHIP), Pekerjaan
 * Pasangan ke pekerjaan_pasangan, Bidang Usaha ke calon_loan.usaha (hanya bila kodenya terdaftar di bidang_usaha).
 * Perbaikan 2026-11-21 (perintah user, §38): hanya CIF dengan pengajuan menunggu (calon_loan gol='0') yang diproses
 * (isian CIF di new4.php kini read-only); anggota + calon_loan + tunggakan dalam satu transaksi.
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
// 2026-11-16 (perintah user): lead hanya meng-approve pengajuan unit di areanya (branch.code_area = kode lead)
require_once __DIR__ . '/inc/cek_login.php';
wajib_cif_area($cif);
$nama = $pos('nama');
$nama1 = $pos('wali');

// 2026-11-21 (perintah user, PEKERJAAN_TERTUNDA §38): hanya CIF yang punya pengajuan menunggu approve (calon_loan gol='0').
// Dulu CIF yang diketik ulang di form (pengajuan lain / sudah di-approve / tanpa pengajuan) tetap diproses: data anggota
// itu tertimpa isian form dan pengajuannya ter-approve tanpa dibuka.
if (!db_satu("select 1 as ada from calon_loan where cif =? and gol ='0'", 's', $cif)) {
    echo "<script> alert (" . json_encode('Pengajuan CIF ' . $cif . ' tidak ditemukan atau sudah di-approve. Tidak ada data yang diubah.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ")</script>";
    echo "<script language='javascript'>document.location.href='data_approval.php'</script>";
    exit;
}
// satu transaksi: anggota, calon_loan, tunggakan (InnoDB); pesan sukses hanya bila semuanya berhasil
$db = db_ni();
mysqli_begin_transaction($db);
$gagal_awal = db_jumlah_gagal();

$i = db_jalankan("update anggota set
    CODE_KEL=?, CAO=?, CUST_SHORT_NAME=?, HADDRESS1=?, HADDRESS2=?, HADDRESS3=?, HPOST_CODE=?, HADDRESS4=?, HADDRESS5=?,
    MOBILE_PHONE=?, HOME_PHONE=?, BIRTH_DATE=?, ID_NUMBER=?, MOTHER_NAME=?, NPWP=?, TEMPAT_LAHIR=?, ID_EXPIRED=?, WARIS=?,
    pekerjaan_pasangan=?
    where CIF=? ", str_repeat('s', 20),
    $code_kel, $cao, $nama, $pos('alamat'), $pos('lurah'), $pos('kec'), $pos('kd_pos'), $pos('kota'), $pos('rt'),
    $pos('no_tlp'), $pos('tlp_penjamin'), $pos('tgl_lahir'), $pos('id'), $pos('ibu'), $pos('npwp'), $pos('tempat_lahir'),
    $pos('id_expire'), $nama1, $pos('pekerjaan'), $cif);

// bidang usaha hanya disimpan bila kodenya terdaftar ("Pilih Salah satu" tidak menimpa data)
$usaha = $pos('usaha');
if ($usaha !== '' && db_satu("select 1 as ada from bidang_usaha where code_bidang=?", 's', $usaha)) {
    $b = db_jalankan("update calon_loan set gol='1', usaha=? where cif =?", 'ss', $usaha, $cif);
} else {
    $b = db_jalankan("update calon_loan set gol='1' where cif =?", 's', $cif);
}

db_jalankan("update tunggakan set CODE_KEL=?,CAO=? where CIF =?", 'sss', $code_kel, $cao, $cif);

if (db_jumlah_gagal() > $gagal_awal) {
    mysqli_rollback($db);
    echo "<script> alert ('Approve GAGAL, data tidak disimpan. Silakan ulangi.')</script>";
    echo "<script language='javascript'>document.location.href='data_approval.php'</script>";
    exit;
}
mysqli_commit($db);

if ($i || $b) {
    // versi lama memakai nama yang sudah di-escape untuk SQL; addslashes menjaga alert tetap benar bila nama berisi tanda kutip
    echo "<script> alert ('data Anggota " . addslashes($nama) . " " . addslashes($cif) . " sudah di Apporve ')</script>";
    echo "<script language='javascript'>document.location.href='data_approval.php'</script>";
} else {
    echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
}
