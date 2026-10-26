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
