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

/**
 * Token SEKALI PAKAI (2026-11-20) untuk form proses uang yang tidak boleh terkirim dua kali (klik ganda, muat ulang,
 * tombol kembali): PB PerKelompok, Setoran PerKelompok / Beda Hari. Satu sesi menyimpan sampai 20 token per $kunci
 * sehingga beberapa tab daftar boleh terbuka bersamaan; tiap token hanya bisa dipakai sekali.
 *   halaman daftar : $t = token_sekali_buat('setoran_kel_token');  <input type="hidden" name="..." value="$t">
 *   halaman proses : if (!token_sekali_pakai('setoran_kel_token', $_POST['...'])) { tolak }
 */
function token_sekali_buat($kunci)
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $daftar = isset($_SESSION[$kunci]) && is_array($_SESSION[$kunci]) ? $_SESSION[$kunci] : array();
    $token = bin2hex(random_bytes(16));
    $daftar[] = $token;
    $_SESSION[$kunci] = array_slice($daftar, -20);
    return $token;
}

/** true (dan token dihapus) bila $token ada di daftar $kunci; false bila kosong, salah, atau sudah dipakai. */
function token_sekali_pakai($kunci, $token)
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $token = (string) $token;
    $daftar = isset($_SESSION[$kunci]) && is_array($_SESSION[$kunci]) ? $_SESSION[$kunci] : array();
    foreach ($daftar as $i => $t) {
        if ($token !== '' && hash_equals((string) $t, $token)) {
            unset($daftar[$i]);
            $_SESSION[$kunci] = array_values($daftar);
            return true;
        }
    }
    return false;
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
