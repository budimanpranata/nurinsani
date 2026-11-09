<?php
/**
 * Token bersama untuk endpoint api/categories/* yang dipanggil ANTAR-SERVER tanpa login (list.php, musawamah.php,
 * transaksi*.php). Ini melapis batasan IP di api/categories/.htaccess (2026-11-09, perintah user).
 *
 * Nilai token disimpan di koneksi_db.lokal.php (TIDAK ikut git), dan HARUS SAMA di semua server yang saling memanggil:
 *     'api' => array('token' => '<rahasia acak panjang>'),
 * Buat token: php -r "echo bin2hex(random_bytes(32)), \"\n\";"
 *
 * Endpoint memanggil api_token_wajib() di baris awal; pemanggil (curl di kp/*, report/*) mengirim header
 *     X-Api-Token: <token>
 * Bila token belum diatur di server (kosong), akses DITOLAK (fail-closed): isi dulu koneksi_db.lokal.php.
 */
require_once __DIR__ . '/../koneksi_db.php';

/** Token API yang dikonfigurasi di server ini ('' bila belum diatur). */
function api_token()
{
    $cfg = isset($GLOBALS['DB_CONFIG']['api']) ? $GLOBALS['DB_CONFIG']['api'] : array();
    return isset($cfg['token']) ? (string) $cfg['token'] : '';
}

/** Header untuk curl pemanggil: array('X-Api-Token: ...'). */
function api_token_header()
{
    return array('X-Api-Token: ' . api_token());
}

/** Endpoint: hentikan dengan 403 bila token tak diatur / tak cocok. Panggil di awal tiap api/categories/*.php. */
function api_token_wajib()
{
    $benar = api_token();
    $kirim = isset($_SERVER['HTTP_X_API_TOKEN']) ? (string) $_SERVER['HTTP_X_API_TOKEN']
        : (isset($_GET['token']) ? (string) $_GET['token'] : '');
    if ($benar === '' || !hash_equals($benar, $kirim)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'forbidden'));
        exit;
    }
}
