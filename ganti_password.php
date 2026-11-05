<?php
/**
 * Proses ganti password, dipakai oleh changepass.php di semua folder.
 * - wajib login (atau sedang dalam alur "Password Expired" dari log.php)
 * - password lama harus benar, password baru = konfirmasi, minimal 8 karakter
 * - sesudah berhasil, session diakhiri dan user login ulang
 */
require_once __DIR__ . '/koneksi_db.php';
require_once __DIR__ . '/inc/password.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('PANJANG_MIN_PASSWORD', 8);

function selesai_ganti_password($pesan, $tujuan)
{
    echo "<script>alert('$pesan');</script>";
    echo "<script language='javascript'>$tujuan</script>";
    exit;
}

// URL halaman login aplikasi (folder tempat file ini berada)
$url_login = rtrim(str_replace('\\', '/', substr(__DIR__, strlen(rtrim(realpath($_SERVER['DOCUMENT_ROOT']), '\\/')))), '/') . '/index.html';

if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
} elseif (isset($_SESSION['ganti_pass_userid'])) {
    $userid = $_SESSION['ganti_pass_userid'];
} else {
    header('location:' . $url_login);
    exit;
}

$passlama = isset($_POST['passlama']) ? $_POST['passlama'] : '';
$passbaru = isset($_POST['passbaru']) ? $_POST['passbaru'] : '';
$passlagi = isset($_POST['passlagi']) ? $_POST['passlagi'] : '';
$kembali = "document.location.href='Gantipass.php'";

if ($passlama === '' || $passbaru === '' || $passlagi === '') {
    selesai_ganti_password('Maaf data Anda belum lengkap', $kembali);
}
if ($passbaru !== $passlagi) {
    selesai_ganti_password('Password baru dan konfirmasi password tidak sama', $kembali);
}
if (strlen($passbaru) < PANJANG_MIN_PASSWORD) {
    selesai_ganti_password('Password baru minimal ' . PANJANG_MIN_PASSWORD . ' karakter', $kembali);
}
if (strcasecmp($passbaru, $userid) === 0) {
    selesai_ganti_password('Password baru tidak boleh sama dengan user name', $kembali);
}

$db = db_ni();
$uid = mysqli_real_escape_string($db, $userid);
// password dicocokkan di PHP (bcrypt, inc/password.php); dulu pass='md5' di WHERE
$cek = db_query($db, "SELECT userid, pass FROM user WHERE userid='$uid'");
$baris = db_num_rows($cek) == 1 ? db_fetch_assoc($cek) : null;
if (!$baris || !password_cocok($passlama, $baris['pass'])) {
    selesai_ganti_password('Password lama salah', $kembali);
}
if (password_md5_lama($passbaru) === password_md5_lama($passlama)) {
    selesai_ganti_password('Password baru tidak boleh sama dengan password lama', $kembali);
}

$hash_baru = mysqli_real_escape_string($db, password_buat($passbaru));
if (!db_query($db, "UPDATE user SET pass='$hash_baru', last_update=NOW() WHERE userid='$uid'")) {
    selesai_ganti_password('Maaf periksa kembali password anda', $kembali);
}

$_SESSION = array();
session_destroy();
selesai_ganti_password('Password anda telah di ganti, silakan login kembali', "top.location.href='$url_login'");
