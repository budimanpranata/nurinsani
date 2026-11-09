<?php
/**
 * Token anti-CSRF bersama (2026-11-09). Cookie sesi sudah SameSite=Lax (php8_compat) sehingga POST lintas-situs
 * sudah terblokir; token ini lapis tambahan untuk form yang mengubah data (terutama transaksi/uang) dan untuk
 * aksi penting.
 *
 *   require_once __DIR__ . '/inc/csrf.php';
 *   <form method="post" ...> <?php echo csrf_field(); ?> ... </form>
 *   // di awal pemrosesan POST:
 *   csrf_wajib();            // tolak (419) bila token tidak cocok
 *   csrf_wajib('json');      // untuk endpoint AJAX: 419 + JSON
 *
 * Token per sesi (satu nilai selama sesi login), dibandingkan dengan hash_equals.
 */

function csrf_token()
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input untuk disisipkan di dalam <form>. */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/** true bila token yang dikirim cocok. */
function csrf_cek()
{
    $kirim = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token']
        : (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? (string) $_SERVER['HTTP_X_CSRF_TOKEN'] : '');
    return $kirim !== '' && hash_equals(csrf_token(), $kirim);
}

/** Hentikan permintaan (419) bila token tidak cocok. Panggil di awal pemrosesan POST. */
function csrf_wajib($mode = 'halaman')
{
    if (csrf_cek()) {
        return;
    }
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(419);
        header('Cache-Control: no-store');
    }
    if ($mode === 'json') {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(array('error' => 'Sesi formulir kedaluwarsa, muat ulang halaman lalu coba lagi.'));
    } else {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Sesi kedaluwarsa</title></head><body>'
            . '<p style="font-family:sans-serif">Sesi formulir kedaluwarsa. Muat ulang halaman sebelumnya lalu kirim lagi.</p>'
            . '</body></html>';
    }
    exit;
}
