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
    tolak_akses('anggota tidak terdaftar di unit Anda.');
}

/**
 * Pengaman area lead (2026-11-16, perintah user: lead hanya pengajuan unit di areanya, branch.code_area = kode lead).
 * calon_loan.unit harus unit di area lead (kode area = userid lead, sama dengan modul lead/ dan hitungan total
 * Approval Pengajuan). Selain itu HTTP 403, isi halaman tidak dimuat.
 *
 *   wajib_cif_area($cif);
 */
function wajib_cif_area($cif)
{
    $area = isset($_SESSION['userid']) ? (string) $_SESSION['userid'] : '';
    $ada = $area === '' ? null : db_satu('SELECT 1 AS ada FROM calon_loan c INNER JOIN branch b ON b.kode_branch = c.unit
        WHERE c.cif = ? AND b.code_area = ? LIMIT 1', 'ss', (string) $cif, $area);
    if ($ada) {
        return;
    }
    tolak_akses('pengajuan anggota ini bukan dari unit di area Anda.');
}

/** Hentikan request dengan HTTP 403 dan pesan "Akses ditolak: ...". */
function tolak_akses($pesan)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(403);
        header('Cache-Control: no-store');
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Akses ditolak</title></head><body>'
        . '<p style="font-family:sans-serif">Akses ditolak: ' . htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</body></html>';
    exit;
}
