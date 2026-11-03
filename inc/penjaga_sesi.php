<?php
/**
 * Penjaga sesi untuk SEMUA halaman PHP (dimuat oleh php8_compat.php, yang di-include di baris pertama hampir semua
 * file aplikasi). Pada setiap permintaan web:
 *   1. wajib login: tanpa $_SESSION['userid'] halaman ditolak dan diarahkan ke halaman login;
 *   2. logout otomatis bila tidak ada aktivitas selama SESI_IDLE_MENIT menit;
 *   3. satu sesi per user: login di perangkat/browser lain membuat sesi lama tidak berlaku.
 * Halaman yang memang dibuka tanpa login dikecualikan (lihat penjaga_sesi_publik()). Tidak berjalan di CLI
 * (cron dan skrip uji). Login yang berhasil wajib memanggil sesi_tandai_login() (log.php, toko/log.php).
 * Pencatatan sesi aktif per user disimpan sebagai berkas kecil di folder sementara server, bukan di database.
 */

if (!defined('SESI_IDLE_MENIT')) {
    define('SESI_IDLE_MENIT', 30);
}

/** Folder penyimpan id sesi aktif per user. */
function penjaga_sesi_folder()
{
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nurinsani_sesi';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    return $dir;
}

function penjaga_sesi_berkas($userid)
{
    return penjaga_sesi_folder() . DIRECTORY_SEPARATOR . md5(strtolower((string) $userid));
}

/** Dipanggil sesudah login berhasil (sesudah session_regenerate_id): sesi ini menjadi satu-satunya sesi user. */
function sesi_tandai_login($userid)
{
    @file_put_contents(penjaga_sesi_berkas($userid), session_id(), LOCK_EX);
    $_SESSION['waktu_akses'] = time();
}

/** Path skrip yang diminta, relatif dari folder aplikasi (huruf kecil, pemisah /); null bila di luar aplikasi. */
function penjaga_sesi_skrip()
{
    $skrip = isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : false;
    $app = realpath(dirname(__DIR__));
    if ($skrip === false || $app === false) {
        return null;
    }
    $skrip = str_replace('\\', '/', $skrip);
    $app = rtrim(str_replace('\\', '/', $app), '/') . '/';
    if (strncasecmp($skrip, $app, strlen($app)) !== 0) {
        return null;
    }
    return strtolower(substr($skrip, strlen($app)));
}

/**
 * Halaman yang boleh dibuka tanpa sesi login:
 *  - login, logout, dan ganti password saat password kedaluwarsa (punya pemeriksaan sendiri);
 *  - api/categories/: dipanggil lewat HTTP (curl) dari server lain tanpa sesi.
 * Proses EOD dan api/ lainnya dijalankan cron lewat php CLI (penjaga tidak berjalan di CLI), sehingga lewat web wajib
 * login; yang terdaftar di cron bahkan ditolak total oleh .htaccess (PEKERJAAN_TERTUNDA.md bagian 42).
 */
function penjaga_sesi_publik($rel)
{
    $tetap = array(
        'log.php', 'toko/log.php',
        'logout.php', 'admin/logout.php', 'lead/logout.php', 'toko/logout.php',
        'gantipass.php', 'changepass.php', 'ganti_password.php',
    );
    if (in_array($rel, $tetap, true)) {
        return true;
    }
    return strpos($rel, 'api/categories/') === 0;
}

/** true bila permintaan berasal dari JavaScript (fetch/XHR), bukan halaman yang dibuka browser. */
function penjaga_sesi_permintaan_data()
{
    if (isset($_SERVER['HTTP_SEC_FETCH_DEST'])) {
        return !in_array($_SERVER['HTTP_SEC_FETCH_DEST'], array('document', 'iframe', 'frame', 'embed', 'object'), true);
    }
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/** Tolak permintaan: halaman -> pesan (bila ada) lalu ke halaman login di jendela utama; data -> HTTP 401 JSON. */
function penjaga_sesi_tolak($pesan)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    require_once __DIR__ . '/cek_login.php';
    $login = url_halaman_login();
    if (!headers_sent()) {
        http_response_code(401);
        header('Cache-Control: no-store');
    }
    if (penjaga_sesi_permintaan_data()) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(array('error' => $pesan !== '' ? $pesan : 'Sesi login berakhir, silakan login kembali.', 'login' => $login));
        exit;
    }
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Login</title></head><body><script>';
    if ($pesan !== '') {
        echo 'alert(' . json_encode($pesan) . ');';
    }
    echo 'top.location.href=' . json_encode($login) . ';</script></body></html>';
    exit;
}

function penjaga_sesi()
{
    if (PHP_SAPI === 'cli' || defined('PENJAGA_SESI_SELESAI')) {
        return;
    }
    define('PENJAGA_SESI_SELESAI', true);
    $rel = penjaga_sesi_skrip();
    if ($rel === null || penjaga_sesi_publik($rel)) {
        return;
    }

    $buka_sendiri = session_status() === PHP_SESSION_NONE;
    if ($buka_sendiri) {
        @session_start();
    }
    $userid = isset($_SESSION['userid']) ? (string) $_SESSION['userid'] : '';
    $pesan = null;
    if ($userid === '') {
        $pesan = '';
    } elseif (isset($_SESSION['waktu_akses']) && time() - (int) $_SESSION['waktu_akses'] > SESI_IDLE_MENIT * 60) {
        $pesan = 'Sesi Anda berakhir karena tidak ada aktivitas selama ' . SESI_IDLE_MENIT . ' menit. Silakan login kembali.';
    } else {
        $berkas = penjaga_sesi_berkas($userid);
        $aktif = is_file($berkas) ? trim((string) @file_get_contents($berkas)) : '';
        if ($aktif === '') {
            // sesi yang dibuat sebelum penjaga ini dipasang: jadikan sesi aktif user
            @file_put_contents($berkas, session_id(), LOCK_EX);
        } elseif ($aktif !== session_id()) {
            $pesan = 'Akun Anda sudah login di perangkat atau browser lain. Silakan login kembali.';
        }
    }

    if ($pesan !== null) {
        if ($userid !== '') {
            // sesi tidak berlaku lagi: kosongkan supaya tidak bisa dipakai ulang
            $_SESSION = array();
            @session_destroy();
        }
        penjaga_sesi_tolak($pesan);
    }

    $_SESSION['waktu_akses'] = time();
    if ($buka_sendiri) {
        // tutup lagi; halaman membuka sesinya sendiri seperti biasa
        session_write_close();
    }
}

penjaga_sesi();
