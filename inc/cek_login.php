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
