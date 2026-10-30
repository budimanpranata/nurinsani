<?php
/**
 * koneksi_db.php
 * SATU-SATUNYA tempat konfigurasi dan koneksi database aplikasi Nur Insani.
 * Ubah host/user/password di $DB_CONFIG saja; semua file lain memakai fungsi di bawah.
 *
 * Cara pakai di file lain:
 *   require_once __DIR__ . '/koneksi_db.php';     // sesuaikan path relatifnya
 *   $conn   = db_ni();       // mysqli ke database nur_insani
 *   $mobcol = db_mobcol();   // mysqli ke database mobcol
 *
 * Fungsi mysql_* lama (dari php8_compat.php) yang dipanggil tanpa parameter link
 * otomatis memakai koneksi terakhir yang dibuka lewat db_ni()/db_mobcol(),
 * sama seperti perilaku mysql_connect() dulu.
 */
require_once __DIR__ . '/php8_compat.php';

if (defined('KONEKSI_DB_LOADED')) {
    return;
}
define('KONEKSI_DB_LOADED', true);

$GLOBALS['DB_CONFIG'] = array(
    'nur_insani' => array(
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'name' => 'nur_insani',
    ),
    'mobcol' => array(
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'name' => 'mobcol',
    ),
);

// Charset koneksi. null = default mysqli (utf8mb4). Ganti ke 'latin1' bila huruf non-ASCII tampil rusak.
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', null);
}

// true = semua koneksi read-only (untuk pengujian); query tulis ditolak oleh MySQL.
if (!defined('DB_READONLY')) {
    define('DB_READONLY', false);
}

/**
 * Buka (atau pakai ulang) koneksi ke database yang terdaftar di $DB_CONFIG.
 * Koneksi dipakai ulang selama satu request; dibuat ulang bila sudah ditutup.
 */
function db_connect($key)
{
    static $links = array();

    if (!isset($GLOBALS['DB_CONFIG'][$key])) {
        die("Konfigurasi database '$key' tidak ada di koneksi_db.php");
    }
    $cfg = $GLOBALS['DB_CONFIG'][$key];

    if (isset($links[$key])) {
        try {
            // Kode lama kadang memanggil mysql_select_db()/mysqli_select_db() ke database lain
            if (@mysqli_select_db($links[$key], $cfg['name'])) {
                return __compat_mysql_use_link($links[$key]);
            }
        } catch (Throwable $e) {
            // koneksi sudah ditutup oleh kode pemanggil, buat ulang
        }
        unset($links[$key]);
    }

    $link = new CompatMysqli(); // tanpa argumen = mysqli_init()
    $ok = @mysqli_real_connect($link, $cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name'], $cfg['port']);
    if (!$ok) {
        die('Gagal melakukan koneksi ke database ' . $cfg['name'] . ' : ' . mysqli_connect_error());
    }
    if (DB_CHARSET) {
        mysqli_set_charset($link, DB_CHARSET);
    }
    // Query lama mengandalkan sql_mode longgar (tanpa STRICT / ONLY_FULL_GROUP_BY)
    @mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
    if (DB_READONLY) {
        // Mode uji: server menolak semua INSERT/UPDATE/DELETE dari koneksi ini
        mysqli_query($link, 'SET SESSION TRANSACTION READ ONLY');
    }

    $links[$key] = $link;
    return __compat_mysql_use_link($link);
}

/** Koneksi mysqli ke database nur_insani. */
function db_ni()
{
    return db_connect('nur_insani');
}

/** Koneksi mysqli ke database mobcol. */
function db_mobcol()
{
    return db_connect('mobcol');
}

/**
 * Semua baris hasil query (prepared statement) ke nur_insani.
 *   db_semua('SELECT * FROM loan WHERE unit = ? AND cao = ?', 'ss', $unit, $cao)
 * Hasil array kosong bila query gagal.
 */
function db_semua($sql, $tipe = '', ...$param)
{
    $db = db_ni();
    $st = $db->prepare($sql);
    if (!$st || ($tipe !== '' && !$st->bind_param($tipe, ...$param)) || !$st->execute()) {
        trigger_error('db_semua(): ' . ($st ? $st->error : $db->error) . ' | SQL: '
            . substr(preg_replace('/\s+/', ' ', $sql), 0, 300), E_USER_WARNING);
        return array();
    }
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

/** Jalankan query tulis (INSERT/UPDATE/DELETE, prepared statement); true bila berhasil. */
function db_jalankan($sql, $tipe = '', ...$param)
{
    $db = db_ni();
    $st = $db->prepare($sql);
    if (!$st || ($tipe !== '' && !$st->bind_param($tipe, ...$param)) || !$st->execute()) {
        trigger_error('db_jalankan(): ' . ($st ? $st->error : $db->error) . ' | SQL: '
            . substr(preg_replace('/\s+/', ' ', $sql), 0, 300), E_USER_WARNING);
        $GLOBALS['__db_jumlah_gagal'] = db_jumlah_gagal() + 1;
        return false;
    }
    return true;
}

/** Jumlah db_jalankan() yang gagal sejak awal request; dipakai untuk memutuskan commit/rollback transaksi. */
function db_jumlah_gagal()
{
    return isset($GLOBALS['__db_jumlah_gagal']) ? $GLOBALS['__db_jumlah_gagal'] : 0;
}

/** Baris pertama hasil query (prepared statement), atau null. */
function db_satu($sql, $tipe = '', ...$param)
{
    $baris = db_semua($sql, $tipe, ...$param);
    return $baris ? $baris[0] : null;
}

/** Koneksi PDO (untuk kode yang memakai PDO). $key: 'nur_insani' atau 'mobcol'. */
function db_pdo($key = 'nur_insani')
{
    static $pdo = array();
    if (!isset($pdo[$key])) {
        $cfg = $GLOBALS['DB_CONFIG'][$key];
        $dsn = 'mysql:host=' . $cfg['host'] . ';port=' . $cfg['port'] . ';dbname=' . $cfg['name'];
        $pdo[$key] = new PDO($dsn, $cfg['user'], $cfg['pass']);
        $pdo[$key]->exec("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
        if (DB_READONLY) {
            $pdo[$key]->exec('SET SESSION TRANSACTION READ ONLY');
        }
    }
    return $pdo[$key];
}
