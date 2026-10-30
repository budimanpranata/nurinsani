<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Simpan Update Kelompok (dari kelompok.php): data kelompok disimpan, lalu kode AO / hari minggon
 * diturunkan ke loan, anggota, simpanan, simpanan_pokok, simpanan_wajib, dan tunggakan kelompok tersebut.
 *
 * Perbaikan atas perintah user (2026-10-30), lihat PEKERJAAN_TERTUNDA.md bagian 10:
 *   - hanya level unit; kode, nama, dan AO wajib; AO harus terdaftar di unit user
 *   - kelompok milik unit lain tidak boleh diubah; kode baru harus dikonfirmasi (kelompok_baru=1)
 *   - INSERT ... ON DUPLICATE KEY UPDATE menggantikan REPLACE (tlp dan cif tidak lagi terhapus)
 *   - semua query dalam satu transaksi; gagal satu -> dibatalkan semua (kecuali tabel MyISAM)
 */
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
//cek level user
if ($_SESSION['level'] != 'unit') {
    header('location:index.html');
    exit;
}

/** Tampilkan alert lalu kembali ke form (isian tetap ada). */
function batal_kembali($pesan)
{
    echo '<script>alert(' . json_encode($pesan) . '); history.back();</script>';
    exit;
}

$userid=$_SESSION['userid'];
$kel= isset($_POST['code_kel']) ? trim((string) $_POST['code_kel']) : '';
$ds= isset($_POST['cao']) ? (string) $_POST['cao'] : '';
$alamat= isset($_POST['alamat']) ? (string) $_POST['alamat'] : '';
$nama= isset($_POST['nama']) ? (string) $_POST['nama'] : '';
$hari= isset($_POST['hari']) ? (string) $_POST['hari'] : '';

$r = db_satu("select kode_branch from user where userid =?", 's', (string) $userid);
$branch= $r ? (string) $r['kode_branch'] : '';

if ($kel === '') {
    batal_kembali('Masukkan KODE KELOMPOK');
}
if ($nama === '') {
    batal_kembali('Masukkan NAMA KELOMPOK');
}
if ($ds === '') {
    batal_kembali('Pilih KODE AO');
}
if (!db_satu('select cao from ao where cao = ? and kode_unit = ?', 'ss', $ds, $branch)) {
    batal_kembali("Kode AO $ds tidak terdaftar di unit $branch");
}
$lama = db_satu('select code_unit from kelompok where code_kel = ?', 's', $kel);
if ($lama && (string) $lama['code_unit'] !== $branch) {
    batal_kembali("Kelompok $kel milik unit {$lama['code_unit']}, tidak bisa diubah dari unit $branch");
}
if (!$lama && empty($_POST['kelompok_baru'])) {
    batal_kembali("Kode kelompok $kel belum terdaftar. Pilih kelompok dari daftar, atau konfirmasi pembuatan kelompok baru.");
}

$koneksi_transaksi = db_ni();
mysqli_begin_transaction($koneksi_transaksi);
$gagal_awal = db_jumlah_gagal();

db_jalankan("insert into kelompok(code_kel,code_unit,nama_kel,alamat,cao,cif) values(?,?,?,?,?,'0')
    on duplicate key update nama_kel=values(nama_kel), alamat=values(alamat), cao=values(cao)", 'sssss', $kel, $branch, $nama, $alamat, $ds);
db_jalankan("Update loan set cao =?, hari =? where code_kel=?", 'sss', $ds, $hari, $kel);
db_jalankan("Update anggota set cao =? where code_kel=?", 'ss', $ds, $kel);
db_jalankan("Update simpanan set CAO =? where code_kel=?", 'ss', $ds, $kel);
db_jalankan("Update simpanan_pokok set CAO =? where code_kel=?", 'ss', $ds, $kel);
db_jalankan("Update simpanan_wajib set CAO =? where code_kel=?", 'ss', $ds, $kel);
db_jalankan("Update tunggakan set CAO =? where CODE_KEL=?", 'ss', $ds, $kel);

if (db_jumlah_gagal() > $gagal_awal) {
    mysqli_rollback($koneksi_transaksi);
    echo "gagal input <a href=\"javascript:history.back()\">kembali</a>";
    exit;
}
mysqli_commit($koneksi_transaksi);

echo "<script> alert ('data kelompk sudah Update ')</script>";
echo "<script language='javascript'>document.location.href='kelompok.php'</script>";
