<?php
/**
 * Pengaman login bersama.
 *
 *   require_once __DIR__ . '/inc/cek_login.php';
 *   wajib_login();          // halaman / proses / PDF: belum login -> ke halaman login
 *   wajib_login('json');    // endpoint AJAX: belum login -> HTTP 401 + []
 */

/** URL halaman login (index.html di root aplikasi), relatif dari DOCUMENT_ROOT. */
function url_halaman_login()
{
    $root = rtrim(str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $app = str_replace('\\', '/', dirname(__DIR__));
    $dasar = $root !== '' && strpos($app, $root) === 0 ? substr($app, strlen($root)) : '';
    return $dasar . '/index.html';
}

function wajib_login($mode = 'halaman')
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['userid']) && $_SESSION['userid'] !== '') {
        return;
    }
    if ($mode === 'json') {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo '[]';
        exit;
    }
    $login = url_halaman_login();
    if (!headers_sent()) {
        header('Location: ' . $login);
    }
    // bila header sudah terkirim atau halaman dibuka di dalam frame: buka login di jendela utama
    echo '<script>top.location.href=' . json_encode($login) . ';</script>';
    exit;
}

/**
 * Pengaman level (otorisasi). Pastikan user sudah login DAN level-nya termasuk salah satu yang diizinkan.
 *
 *   wajib_level('ho');              // hanya level ho
 *   wajib_level('fund', 'admin');   // fund atau admin
 *   wajib_level('unit', 'json');    // endpoint AJAX: level salah -> 403 + [] (mode selalu argumen TERAKHIR)
 *
 * Belum login -> diarahkan ke halaman login (seperti wajib_login).
 * Login tapi level tidak diizinkan -> HTTP 403 (halaman: pesan "Akses ditolak"; json: []), isi halaman tidak dimuat.
 */
function wajib_level($level)
{
    $arg = func_get_args();
    $mode = 'halaman';
    if (count($arg) > 1 && end($arg) === 'json') {
        $mode = 'json';
        array_pop($arg);
    }
    wajib_login($mode);
    $punya = isset($_SESSION['level']) ? (string) $_SESSION['level'] : '';
    if (in_array($punya, $arg, true)) {
        return;
    }
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(403);
        header('Cache-Control: no-store');
    }
    if ($mode === 'json') {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo '[]';
        exit;
    }
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Akses ditolak</title></head><body>'
        . '<p style="font-family:sans-serif">Akses ditolak: halaman ini tidak untuk level akun Anda.</p>'
        . '</body></html>';
    exit;
}

/**
 * Unit (user.kode_branch) milik user yang login; '' bila tidak ada. Butuh koneksi_db.php.
 */
function unit_user()
{
    static $unit = null;
    if ($unit === null) {
        $u = isset($_SESSION['userid'])
            ? db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $_SESSION['userid'])
            : null;
        $unit = $u ? (string) $u['kode_branch'] : '';
    }
    return $unit;
}

/**
 * Pengaman kepemilikan data (2026-11-16, perintah user): anggota (loan.unit) harus milik unit user login.
 * Untuk halaman yang menerima CIF dari URL/form; wajib_level('unit') hanya memastikan user berlevel unit,
 * bukan bahwa anggotanya milik unit user. Bukan milik unit / tidak ada di loan -> HTTP 403, isi halaman tidak dimuat.
 *
 *   wajib_cif_unit($cif);
 */
function wajib_cif_unit($cif)
{
    $unit = unit_user();
    $loan = db_satu('SELECT unit FROM loan WHERE cif = ? LIMIT 1', 's', (string) $cif);
    if ($unit !== '' && $loan && (string) $loan['unit'] === $unit) {
        return;
    }
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(403);
        header('Cache-Control: no-store');
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Akses ditolak</title></head><body>'
        . '<p style="font-family:sans-serif">Akses ditolak: anggota tidak terdaftar di unit Anda.</p>'
        . '</body></html>';
    exit;
}
