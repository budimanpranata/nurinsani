<?php
/**
 * php8_compat.php
 * Lapisan kompatibilitas agar kode lama (PHP 5.x) bisa berjalan di PHP 8.x.
 *
 * Menyediakan kembali fungsi-fungsi yang sudah dihapus dari PHP:
 *  - ekstensi mysql_*  (dihapus di PHP 7.0)  -> diimplementasikan di atas mysqli
 *  - ereg, eregi, ereg_replace, eregi_replace, split, spliti, sql_regcase (dihapus di PHP 7.0)
 *  - each (dihapus di PHP 8.0), create_function (dihapus di PHP 8.0)
 *  - get_magic_quotes_gpc / get_magic_quotes_runtime (dihapus di PHP 8.0), set_magic_quotes_runtime
 *  - session_register / session_is_registered / session_unregister (dihapus di PHP 5.4)
 *
 *  - mssql_* (dihapus di PHP 7.0) -> memakai ekstensi sqlsrv bila ada, kalau tidak gagal dengan aman (false)
 *
 * File ini di-include otomatis (require_once) di setiap file yang membutuhkannya.
 * Koneksi database ada di koneksi_db.php (yang juga meng-include file ini).
 * Aman di-include berkali-kali.
 */

if (defined('PHP8_COMPAT_LOADED')) {
    return;
}
define('PHP8_COMPAT_LOADED', true);

/* =====================================================================
 * Penanganan error
 * Kode lama banyak memakai variabel/index yang belum di-set. Di PHP 5 itu
 * hanya notice (biasanya tidak ditampilkan); di PHP 8 menjadi warning yang
 * tercetak ke layar dan merusak tampilan serta header() redirect.
 * Warning/notice/deprecated tidak ditampilkan; error fatal tetap tampil.
 * Isi COMPAT_WARNING_LOG dengan path file untuk mencatat warning tsb.
 * ===================================================================== */
if (!defined('COMPAT_WARNING_LOG')) {
    define('COMPAT_WARNING_LOG', false);
}
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE & ~E_USER_NOTICE & ~E_WARNING & ~E_USER_WARNING);
if (COMPAT_WARNING_LOG) {
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        // 4437 = nilai error_reporting() saat operator @ aktif
        if (error_reporting() !== 4437) {
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'cli';
            @error_log(date('Y-m-d H:i:s') . " [$errno] $uri | $errstr in $errfile:$errline\n", 3, COMPAT_WARNING_LOG);
        }
        return true;
    }, E_WARNING | E_USER_WARNING | E_NOTICE | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED);
}

/* =====================================================================
 * Cookie session: tidak bisa dibaca JavaScript (HttpOnly), tidak dikirim
 * dari situs lain (SameSite=Lax), id session buatan pihak luar ditolak
 * (use_strict_mode), dan hanya lewat HTTPS bila aplikasi diakses via HTTPS.
 * ===================================================================== */
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
}

// Charset koneksi untuk mysql_connect(). null = biarkan default mysqli (utf8mb4 di PHP 8).
// Jika data lama tampil aneh (karakter non-ASCII rusak), coba ganti menjadi 'latin1'.
if (!defined('COMPAT_MYSQL_CHARSET')) {
    define('COMPAT_MYSQL_CHARSET', null);
}

// Sejak PHP 8.1 mysqli melempar exception saat query gagal. Kode lama mengharapkan
// nilai false + mysql_error(), jadi kembalikan ke perilaku lama.
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

/* =====================================================================
 * mysql_* (di atas mysqli)
 * ===================================================================== */
if (!function_exists('mysql_connect') && extension_loaded('mysqli')) {

    if (!defined('MYSQL_ASSOC')) define('MYSQL_ASSOC', MYSQLI_ASSOC);
    if (!defined('MYSQL_NUM')) define('MYSQL_NUM', MYSQLI_NUM);
    if (!defined('MYSQL_BOTH')) define('MYSQL_BOTH', MYSQLI_BOTH);
    if (!defined('MYSQL_CLIENT_COMPRESS')) define('MYSQL_CLIENT_COMPRESS', MYSQLI_CLIENT_COMPRESS);
    if (!defined('MYSQL_CLIENT_SSL')) define('MYSQL_CLIENT_SSL', MYSQLI_CLIENT_SSL);
    if (!defined('MYSQL_CLIENT_INTERACTIVE')) define('MYSQL_CLIENT_INTERACTIVE', MYSQLI_CLIENT_INTERACTIVE);
    if (!defined('MYSQL_CLIENT_IGNORE_SPACE')) define('MYSQL_CLIENT_IGNORE_SPACE', MYSQLI_CLIENT_IGNORE_SPACE);

    $GLOBALS['__compat_mysql'] = array(
        'last_link'     => null,
        'links'         => array(),
        'connect_error' => '',
        'connect_errno' => 0,
    );

    function __compat_mysql_link($link = null, $func = 'mysql')
    {
        if ($link instanceof mysqli) {
            return $link;
        }
        if ($GLOBALS['__compat_mysql']['last_link'] instanceof mysqli) {
            return $GLOBALS['__compat_mysql']['last_link'];
        }
        // Perilaku lama: coba koneksi default dari php.ini
        $l = mysql_connect();
        if ($l instanceof mysqli) {
            return $l;
        }
        trigger_error($func . '(): A link to the server could not be established', E_USER_WARNING);
        return null;
    }

    // Dipakai koneksi_db.php: jadikan $link koneksi default untuk mysql_* tanpa parameter link.
    function __compat_mysql_use_link($link)
    {
        if ($link instanceof mysqli) {
            $GLOBALS['__compat_mysql']['last_link'] = $link;
        }
        return $link;
    }

    function __compat_mysql_is_result($result, $func)
    {
        if ($result instanceof mysqli_result) {
            return true;
        }
        trigger_error($func . '(): supplied argument is not a valid MySQL result resource', E_USER_WARNING);
        return false;
    }

    function mysql_connect($server = null, $username = null, $password = null, $new_link = false, $client_flags = 0)
    {
        if ($server === null || $server === '') $server = ini_get('mysqli.default_host') ?: 'localhost';
        if ($username === null) $username = ini_get('mysqli.default_user') ?: '';
        if ($password === null) $password = ini_get('mysqli.default_pw') ?: '';

        $host = $server;
        $port = (int) (ini_get('mysqli.default_port') ?: 3306);
        $socket = null;
        if (strpos($server, ':') !== false && substr_count($server, ':') === 1) {
            list($host, $p) = explode(':', $server, 2);
            if (is_numeric($p)) {
                $port = (int) $p;
            } else {
                $socket = $p;
            }
        }
        $key = $host . '|' . $port . '|' . $socket . '|' . $username . '|' . $password;
        if (!$new_link && isset($GLOBALS['__compat_mysql']['links'][$key])) {
            $link = $GLOBALS['__compat_mysql']['links'][$key];
            $GLOBALS['__compat_mysql']['last_link'] = $link;
            return $link;
        }

        $link = mysqli_init();
        try {
            $ok = @mysqli_real_connect($link, $host, $username, $password, '', $port, $socket, (int) $client_flags);
        } catch (Throwable $e) {
            $ok = false;
        }
        if (!$ok) {
            $GLOBALS['__compat_mysql']['connect_error'] = mysqli_connect_error();
            $GLOBALS['__compat_mysql']['connect_errno'] = mysqli_connect_errno();
            trigger_error('mysql_connect(): ' . mysqli_connect_error(), E_USER_WARNING);
            return false;
        }
        if (COMPAT_MYSQL_CHARSET) {
            mysqli_set_charset($link, COMPAT_MYSQL_CHARSET);
        }
        // Mode SQL lama yang lebih longgar (MySQL 5.7+/8 memakai STRICT mode secara default
        // yang menolak '' untuk kolom angka/tanggal). Set COMPAT_MYSQL_RELAX_SQL_MODE = false bila tidak diperlukan.
        if (!defined('COMPAT_MYSQL_RELAX_SQL_MODE') || COMPAT_MYSQL_RELAX_SQL_MODE) {
            $res = @mysqli_query($link, 'SELECT @@SESSION.sql_mode');
            if ($res instanceof mysqli_result) {
                $r = mysqli_fetch_row($res);
                $modes = array_filter(explode(',', (string) $r[0]), function ($m) {
                    return $m !== '' && !in_array($m, array('STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES',
                        'ONLY_FULL_GROUP_BY', 'NO_ZERO_DATE', 'NO_ZERO_IN_DATE', 'ERROR_FOR_DIVISION_BY_ZERO'), true);
                });
                @mysqli_query($link, "SET SESSION sql_mode = '" . implode(',', $modes) . "'");
            }
        }

        $GLOBALS['__compat_mysql']['connect_error'] = '';
        $GLOBALS['__compat_mysql']['connect_errno'] = 0;
        $GLOBALS['__compat_mysql']['links'][$key] = $link;
        $GLOBALS['__compat_mysql']['last_link'] = $link;
        return $link;
    }

    function mysql_pconnect($server = null, $username = null, $password = null, $client_flags = 0)
    {
        return mysql_connect($server, $username, $password, false, $client_flags);
    }

    function mysql_select_db($database_name, $link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_select_db');
        if (!$link) return false;
        try {
            return mysqli_select_db($link, (string) $database_name);
        } catch (Throwable $e) {
            return false;
        }
    }

    function mysql_query($query, $link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_query');
        if (!$link) return false;
        try {
            $res = mysqli_query($link, (string) $query);
        } catch (Throwable $e) {
            $res = false;
        }
        if ($res === false) {
            $retry = __compat_retry_empty_dates($link, (string) $query);
            if ($retry !== false) return $retry;
        }
        if ($res === false && COMPAT_WARNING_LOG) {
            trigger_error('mysql_query(): ' . mysqli_error($link) . ' | SQL: ' . substr(preg_replace('/\s+/', ' ', (string) $query), 0, 300), E_USER_WARNING);
        }
        return $res;
    }

    function mysql_unbuffered_query($query, $link_identifier = null)
    {
        return mysql_query($query, $link_identifier);
    }

    function mysql_db_query($database, $query, $link_identifier = null)
    {
        if (!mysql_select_db($database, $link_identifier)) return false;
        return mysql_query($query, $link_identifier);
    }

    function mysql_fetch_array($result, $result_type = MYSQL_BOTH)
    {
        if (!__compat_mysql_is_result($result, 'mysql_fetch_array')) return false;
        $row = mysqli_fetch_array($result, $result_type);
        return $row === null ? false : $row;
    }

    function mysql_fetch_assoc($result)
    {
        if (!__compat_mysql_is_result($result, 'mysql_fetch_assoc')) return false;
        $row = mysqli_fetch_assoc($result);
        return $row === null ? false : $row;
    }

    function mysql_fetch_row($result)
    {
        if (!__compat_mysql_is_result($result, 'mysql_fetch_row')) return false;
        $row = mysqli_fetch_row($result);
        return $row === null ? false : $row;
    }

    function mysql_fetch_object($result, $class_name = 'stdClass', $params = array())
    {
        if (!__compat_mysql_is_result($result, 'mysql_fetch_object')) return false;
        $row = $params ? mysqli_fetch_object($result, $class_name, $params) : mysqli_fetch_object($result, $class_name);
        return $row === null ? false : $row;
    }

    function mysql_fetch_lengths($result)
    {
        if (!__compat_mysql_is_result($result, 'mysql_fetch_lengths')) return false;
        return mysqli_fetch_lengths($result);
    }

    function mysql_num_rows($result)
    {
        if (!__compat_mysql_is_result($result, 'mysql_num_rows')) return false;
        return mysqli_num_rows($result);
    }

    function mysql_num_fields($result)
    {
        if (!__compat_mysql_is_result($result, 'mysql_num_fields')) return false;
        return mysqli_num_fields($result);
    }

    function mysql_affected_rows($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_affected_rows');
        return $link ? mysqli_affected_rows($link) : -1;
    }

    function mysql_insert_id($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_insert_id');
        return $link ? mysqli_insert_id($link) : false;
    }

    function mysql_error($link_identifier = null)
    {
        $link = $link_identifier instanceof mysqli ? $link_identifier : $GLOBALS['__compat_mysql']['last_link'];
        if ($link instanceof mysqli) {
            return mysqli_error($link);
        }
        return $GLOBALS['__compat_mysql']['connect_error'];
    }

    function mysql_errno($link_identifier = null)
    {
        $link = $link_identifier instanceof mysqli ? $link_identifier : $GLOBALS['__compat_mysql']['last_link'];
        if ($link instanceof mysqli) {
            return mysqli_errno($link);
        }
        return $GLOBALS['__compat_mysql']['connect_errno'];
    }

    function mysql_escape_string($unescaped_string)
    {
        return strtr((string) $unescaped_string, array(
            "\\" => "\\\\", "\0" => "\\0", "\n" => "\\n", "\r" => "\\r",
            "'" => "\\'", '"' => '\\"', "\x1a" => "\\Z",
        ));
    }

    function mysql_real_escape_string($unescaped_string, $link_identifier = null)
    {
        $link = $link_identifier instanceof mysqli ? $link_identifier : $GLOBALS['__compat_mysql']['last_link'];
        if ($link instanceof mysqli) {
            return mysqli_real_escape_string($link, (string) $unescaped_string);
        }
        return mysql_escape_string($unescaped_string);
    }

    function mysql_data_seek($result, $row_number)
    {
        if (!__compat_mysql_is_result($result, 'mysql_data_seek')) return false;
        return mysqli_data_seek($result, (int) $row_number);
    }

    function mysql_result($result, $row = 0, $field = 0)
    {
        if (!__compat_mysql_is_result($result, 'mysql_result')) return false;
        if ($row < 0 || $row >= mysqli_num_rows($result) || !mysqli_data_seek($result, (int) $row)) {
            trigger_error("mysql_result(): Unable to jump to row $row", E_USER_WARNING);
            return false;
        }
        $data = mysqli_fetch_array($result, MYSQLI_BOTH);
        if (!is_array($data)) return false;
        if (is_string($field) && strpos($field, '.') !== false && !array_key_exists($field, $data)) {
            $parts = explode('.', $field);
            $field = end($parts);
        }
        if (!array_key_exists($field, $data)) {
            trigger_error("mysql_result(): $field not found in MySQL result", E_USER_WARNING);
            return false;
        }
        return $data[$field];
    }

    function mysql_free_result($result)
    {
        if ($result instanceof mysqli_result) {
            mysqli_free_result($result);
            return true;
        }
        return false;
    }

    function mysql_close($link_identifier = null)
    {
        $link = $link_identifier instanceof mysqli ? $link_identifier : $GLOBALS['__compat_mysql']['last_link'];
        if (!($link instanceof mysqli)) return false;
        foreach ($GLOBALS['__compat_mysql']['links'] as $k => $l) {
            if ($l === $link) unset($GLOBALS['__compat_mysql']['links'][$k]);
        }
        if ($GLOBALS['__compat_mysql']['last_link'] === $link) {
            $GLOBALS['__compat_mysql']['last_link'] = null;
        }
        try {
            return mysqli_close($link);
        } catch (Throwable $e) {
            return false;
        }
    }

    function __compat_mysql_type_name($type)
    {
        static $map = null;
        if ($map === null) {
            $map = array(
                MYSQLI_TYPE_DECIMAL => 'real', MYSQLI_TYPE_NEWDECIMAL => 'real', MYSQLI_TYPE_FLOAT => 'real',
                MYSQLI_TYPE_DOUBLE => 'real', MYSQLI_TYPE_TINY => 'int', MYSQLI_TYPE_SHORT => 'int',
                MYSQLI_TYPE_LONG => 'int', MYSQLI_TYPE_LONGLONG => 'int', MYSQLI_TYPE_INT24 => 'int',
                MYSQLI_TYPE_YEAR => 'year', MYSQLI_TYPE_TIMESTAMP => 'timestamp', MYSQLI_TYPE_DATE => 'date',
                MYSQLI_TYPE_NEWDATE => 'date', MYSQLI_TYPE_TIME => 'time', MYSQLI_TYPE_DATETIME => 'datetime',
                MYSQLI_TYPE_TINY_BLOB => 'blob', MYSQLI_TYPE_MEDIUM_BLOB => 'blob', MYSQLI_TYPE_LONG_BLOB => 'blob',
                MYSQLI_TYPE_BLOB => 'blob', MYSQLI_TYPE_VAR_STRING => 'string', MYSQLI_TYPE_STRING => 'string',
                MYSQLI_TYPE_ENUM => 'string', MYSQLI_TYPE_SET => 'string', MYSQLI_TYPE_NULL => 'null',
                MYSQLI_TYPE_GEOMETRY => 'geometry', MYSQLI_TYPE_BIT => 'int',
            );
            if (defined('MYSQLI_TYPE_JSON')) $map[MYSQLI_TYPE_JSON] = 'string';
        }
        return isset($map[$type]) ? $map[$type] : 'unknown';
    }

    function mysql_fetch_field($result, $field_offset = null)
    {
        if (!__compat_mysql_is_result($result, 'mysql_fetch_field')) return false;
        if ($field_offset !== null) mysqli_field_seek($result, (int) $field_offset);
        $f = mysqli_fetch_field($result);
        if (!$f) return false;
        $o = new stdClass();
        $o->name = $f->name;
        $o->table = $f->table;
        $o->def = isset($f->def) ? $f->def : '';
        $o->max_length = $f->max_length;
        $o->not_null = ($f->flags & MYSQLI_NOT_NULL_FLAG) ? 1 : 0;
        $o->primary_key = ($f->flags & MYSQLI_PRI_KEY_FLAG) ? 1 : 0;
        $o->multiple_key = ($f->flags & MYSQLI_MULTIPLE_KEY_FLAG) ? 1 : 0;
        $o->unique_key = ($f->flags & MYSQLI_UNIQUE_KEY_FLAG) ? 1 : 0;
        $o->numeric = ($f->flags & MYSQLI_NUM_FLAG) ? 1 : 0;
        $o->blob = ($f->flags & MYSQLI_BLOB_FLAG) ? 1 : 0;
        $o->type = __compat_mysql_type_name($f->type);
        $o->unsigned = ($f->flags & MYSQLI_UNSIGNED_FLAG) ? 1 : 0;
        $o->zerofill = ($f->flags & MYSQLI_ZEROFILL_FLAG) ? 1 : 0;
        return $o;
    }

    function mysql_field_seek($result, $field_offset)
    {
        if (!__compat_mysql_is_result($result, 'mysql_field_seek')) return false;
        return mysqli_field_seek($result, (int) $field_offset);
    }

    function __compat_mysql_field($result, $offset, $func)
    {
        if (!__compat_mysql_is_result($result, $func)) return false;
        $f = mysqli_fetch_field_direct($result, (int) $offset);
        return $f ?: false;
    }

    function mysql_field_name($result, $field_offset)
    {
        $f = __compat_mysql_field($result, $field_offset, 'mysql_field_name');
        return $f ? $f->name : false;
    }

    function mysql_field_table($result, $field_offset)
    {
        $f = __compat_mysql_field($result, $field_offset, 'mysql_field_table');
        return $f ? $f->table : false;
    }

    function mysql_field_len($result, $field_offset)
    {
        $f = __compat_mysql_field($result, $field_offset, 'mysql_field_len');
        return $f ? $f->length : false;
    }

    function mysql_field_type($result, $field_offset)
    {
        $f = __compat_mysql_field($result, $field_offset, 'mysql_field_type');
        return $f ? __compat_mysql_type_name($f->type) : false;
    }

    function mysql_field_flags($result, $field_offset)
    {
        $f = __compat_mysql_field($result, $field_offset, 'mysql_field_flags');
        if (!$f) return false;
        $flags = array();
        $map = array(
            MYSQLI_NOT_NULL_FLAG => 'not_null', MYSQLI_PRI_KEY_FLAG => 'primary_key',
            MYSQLI_UNIQUE_KEY_FLAG => 'unique_key', MYSQLI_MULTIPLE_KEY_FLAG => 'multiple_key',
            MYSQLI_BLOB_FLAG => 'blob', MYSQLI_UNSIGNED_FLAG => 'unsigned', MYSQLI_ZEROFILL_FLAG => 'zerofill',
            MYSQLI_BINARY_FLAG => 'binary', MYSQLI_ENUM_FLAG => 'enum', MYSQLI_AUTO_INCREMENT_FLAG => 'auto_increment',
            MYSQLI_TIMESTAMP_FLAG => 'timestamp', MYSQLI_SET_FLAG => 'set',
        );
        foreach ($map as $bit => $name) {
            if ($f->flags & $bit) $flags[] = $name;
        }
        return implode(' ', $flags);
    }

    function mysql_list_dbs($link_identifier = null)
    {
        return mysql_query('SHOW DATABASES', $link_identifier);
    }

    function mysql_list_tables($database, $link_identifier = null)
    {
        return mysql_query('SHOW TABLES FROM `' . str_replace('`', '``', $database) . '`', $link_identifier);
    }

    function mysql_list_fields($database_name, $table_name, $link_identifier = null)
    {
        return mysql_query('SELECT * FROM `' . str_replace('`', '``', $database_name) . '`.`'
            . str_replace('`', '``', $table_name) . '` LIMIT 0', $link_identifier);
    }

    function mysql_tablename($result, $i)
    {
        return mysql_result($result, $i, 0);
    }

    function mysql_db_name($result, $row, $field = null)
    {
        return mysql_result($result, $row, $field === null ? 0 : $field);
    }

    function mysql_create_db($database_name, $link_identifier = null)
    {
        return (bool) mysql_query('CREATE DATABASE `' . str_replace('`', '``', $database_name) . '`', $link_identifier);
    }

    function mysql_drop_db($database_name, $link_identifier = null)
    {
        return (bool) mysql_query('DROP DATABASE `' . str_replace('`', '``', $database_name) . '`', $link_identifier);
    }

    function mysql_set_charset($charset, $link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_set_charset');
        return $link ? mysqli_set_charset($link, $charset) : false;
    }

    function mysql_client_encoding($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_client_encoding');
        return $link ? mysqli_character_set_name($link) : false;
    }

    function mysql_ping($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_ping');
        try {
            return $link ? mysqli_ping($link) : false;
        } catch (Throwable $e) {
            return false;
        }
    }

    function mysql_get_server_info($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_get_server_info');
        return $link ? mysqli_get_server_info($link) : false;
    }

    function mysql_get_host_info($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_get_host_info');
        return $link ? mysqli_get_host_info($link) : false;
    }

    function mysql_get_proto_info($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_get_proto_info');
        return $link ? mysqli_get_proto_info($link) : false;
    }

    function mysql_get_client_info()
    {
        return mysqli_get_client_info();
    }

    function mysql_info($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_info');
        return $link ? mysqli_info($link) : false;
    }

    function mysql_thread_id($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_thread_id');
        return $link ? mysqli_thread_id($link) : false;
    }

    function mysql_stat($link_identifier = null)
    {
        $link = __compat_mysql_link($link_identifier, 'mysql_stat');
        return $link ? mysqli_stat($link) : null;
    }
}

/* =====================================================================
 * POSIX regex: ereg, eregi, ereg_replace, eregi_replace, split, spliti, sql_regcase
 * ===================================================================== */
if (!function_exists('ereg')) {
    function __compat_posix_to_pcre($pattern, $flags = '')
    {
        // chr(1) dipakai sebagai delimiter supaya karakter di pola tidak perlu di-escape
        return "\x01" . $pattern . "\x01" . $flags;
    }

    function __compat_ereg($pattern, $string, &$regs, $flags)
    {
        $m = array();
        $r = preg_match(__compat_posix_to_pcre((string) $pattern, $flags), (string) $string, $m);
        if (!$r) {
            return false;
        }
        $regs = array();
        foreach ($m as $k => $v) {
            $regs[$k] = ($v === '') ? false : $v;
        }
        $len = strlen($m[0]);
        return $len ? $len : 1;
    }

    function ereg($pattern, $string, &$regs = null)
    {
        return __compat_ereg($pattern, $string, $regs, '');
    }

    function eregi($pattern, $string, &$regs = null)
    {
        return __compat_ereg($pattern, $string, $regs, 'i');
    }

    function ereg_replace($pattern, $replacement, $string)
    {
        return preg_replace(__compat_posix_to_pcre((string) $pattern), (string) $replacement, (string) $string);
    }

    function eregi_replace($pattern, $replacement, $string)
    {
        return preg_replace(__compat_posix_to_pcre((string) $pattern, 'i'), (string) $replacement, (string) $string);
    }
}

if (!function_exists('split')) {
    function split($pattern, $string, $limit = -1)
    {
        if ((string) $pattern === '') {
            return str_split((string) $string);
        }
        return preg_split(__compat_posix_to_pcre((string) $pattern), (string) $string, $limit > 0 ? $limit : -1);
    }

    function spliti($pattern, $string, $limit = -1)
    {
        if ((string) $pattern === '') {
            return str_split((string) $string);
        }
        return preg_split(__compat_posix_to_pcre((string) $pattern, 'i'), (string) $string, $limit > 0 ? $limit : -1);
    }
}

if (!function_exists('sql_regcase')) {
    function sql_regcase($string)
    {
        $out = '';
        $len = strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $c = $string[$i];
            $u = strtoupper($c);
            $l = strtolower($c);
            $out .= ($u !== $l) ? '[' . $u . $l . ']' : $c;
        }
        return $out;
    }
}

/* =====================================================================
 * each, create_function
 * ===================================================================== */
if (!function_exists('each')) {
    function each(&$array)
    {
        if (!is_array($array) && !is_object($array)) {
            return false;
        }
        $key = key($array);
        if ($key === null) {
            return false;
        }
        $value = current($array);
        next($array);
        return array(1 => $value, 'value' => $value, 0 => $key, 'key' => $key);
    }
}

if (!function_exists('create_function')) {
    function create_function($args, $code)
    {
        return eval('return function(' . $args . ') {' . $code . '};');
    }
}

/* =====================================================================
 * magic quotes
 * ===================================================================== */
if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc()
    {
        return false;
    }
}
if (!function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime()
    {
        return false;
    }
}
if (!function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($new_setting)
    {
        return false;
    }
}

/* =====================================================================
 * session_register & kawan-kawan
 * ===================================================================== */
if (!function_exists('session_register')) {
    function session_register(...$names)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        foreach ($names as $name) {
            foreach ((array) $name as $n) {
                if (!array_key_exists($n, $_SESSION)) {
                    $_SESSION[$n] = isset($GLOBALS[$n]) ? $GLOBALS[$n] : null;
                }
            }
        }
        return true;
    }

    function session_is_registered($name)
    {
        return isset($_SESSION) && array_key_exists($name, $_SESSION);
    }

    function session_unregister($name)
    {
        if (isset($_SESSION) && array_key_exists($name, $_SESSION)) {
            unset($_SESSION[$name]);
        }
        return true;
    }
}

/* =====================================================================
 * mssql_* (SQL Server). Memakai ekstensi sqlsrv bila terpasang; jika tidak,
 * fungsi mengembalikan false supaya halaman tidak berhenti dengan fatal error.
 * ===================================================================== */
if (!function_exists('mssql_connect')) {
    $GLOBALS['__compat_mssql'] = array('last_link' => null, 'server' => null, 'user' => null, 'pass' => null, 'message' => '');

    function mssql_connect($servername = null, $username = null, $password = null, $new_link = false)
    {
        $GLOBALS['__compat_mssql']['server'] = $servername;
        $GLOBALS['__compat_mssql']['user'] = $username;
        $GLOBALS['__compat_mssql']['pass'] = $password;
        if (!function_exists('sqlsrv_connect')) {
            $GLOBALS['__compat_mssql']['message'] = 'Ekstensi sqlsrv tidak terpasang';
            trigger_error('mssql_connect(): ekstensi sqlsrv tidak terpasang', E_USER_WARNING);
            return false;
        }
        // Koneksi sebenarnya dibuat di mssql_select_db() karena sqlsrv butuh nama database
        $link = sqlsrv_connect(str_replace(',', ', ', (string) $servername),
            array('UID' => $username, 'PWD' => $password, 'CharacterSet' => 'UTF-8'));
        if (!$link) {
            $GLOBALS['__compat_mssql']['message'] = print_r(sqlsrv_errors(), true);
            return false;
        }
        return $GLOBALS['__compat_mssql']['last_link'] = $link;
    }

    function mssql_pconnect($servername = null, $username = null, $password = null, $new_link = false)
    {
        return mssql_connect($servername, $username, $password, $new_link);
    }

    function mssql_select_db($database_name, $link_identifier = null)
    {
        if (!function_exists('sqlsrv_connect')) return false;
        $c = $GLOBALS['__compat_mssql'];
        $link = sqlsrv_connect((string) $c['server'],
            array('UID' => $c['user'], 'PWD' => $c['pass'], 'Database' => $database_name, 'CharacterSet' => 'UTF-8'));
        if (!$link) return false;
        $GLOBALS['__compat_mssql']['last_link'] = $link;
        return true;
    }

    function mssql_query($query, $link_identifier = null, $batch_size = 0)
    {
        if (!function_exists('sqlsrv_query')) return false;
        $link = $link_identifier ?: $GLOBALS['__compat_mssql']['last_link'];
        if (!$link) return false;
        $stmt = sqlsrv_query($link, (string) $query, array(), array('Scrollable' => 'static'));
        if ($stmt === false) {
            $GLOBALS['__compat_mssql']['message'] = print_r(sqlsrv_errors(), true);
            return false;
        }
        return $stmt;
    }

    function mssql_fetch_array($result, $result_type = 3)
    {
        if (!$result || !function_exists('sqlsrv_fetch_array')) return false;
        $map = array(1 => SQLSRV_FETCH_ASSOC, 2 => SQLSRV_FETCH_NUMERIC, 3 => SQLSRV_FETCH_BOTH);
        $row = sqlsrv_fetch_array($result, isset($map[$result_type]) ? $map[$result_type] : SQLSRV_FETCH_BOTH);
        return $row ?: false;
    }

    function mssql_fetch_assoc($result)
    {
        return mssql_fetch_array($result, 1);
    }

    function mssql_fetch_row($result)
    {
        return mssql_fetch_array($result, 2);
    }

    function mssql_fetch_object($result)
    {
        if (!$result || !function_exists('sqlsrv_fetch_object')) return false;
        return sqlsrv_fetch_object($result) ?: false;
    }

    function mssql_num_rows($result)
    {
        if (!$result || !function_exists('sqlsrv_num_rows')) return 0;
        return sqlsrv_num_rows($result);
    }

    function mssql_num_fields($result)
    {
        if (!$result || !function_exists('sqlsrv_num_fields')) return 0;
        return sqlsrv_num_fields($result);
    }

    function mssql_rows_affected($link_identifier = null)
    {
        return 0;
    }

    function mssql_free_result($result)
    {
        if ($result && function_exists('sqlsrv_free_stmt')) sqlsrv_free_stmt($result);
        return true;
    }

    function mssql_close($link_identifier = null)
    {
        $link = $link_identifier ?: $GLOBALS['__compat_mssql']['last_link'];
        if ($link && function_exists('sqlsrv_close')) sqlsrv_close($link);
        $GLOBALS['__compat_mssql']['last_link'] = null;
        return true;
    }

    function mssql_get_last_message()
    {
        return $GLOBALS['__compat_mssql']['message'];
    }

    function mssql_real_escape_string($string)
    {
        return str_replace("'", "''", (string) $string);
    }
}

/* =====================================================================
 * Tanggal kosong di WHERE.
 * MySQL 5 mengubah '' menjadi '0000-00-00' saat dibandingkan dengan kolom
 * DATE/DATETIME; MySQL 8 menolak query itu (error 1525 "Incorrect DATETIME value").
 * SELECT yang gagal karena hal ini diulang dengan '0000-00-00' khusus pada
 * kolom bertipe tanggal, sehingga hasilnya sama seperti dulu (biasanya kosong).
 * Query tulis (INSERT/UPDATE/DELETE) tidak pernah diulang.
 * ===================================================================== */
function __compat_date_columns($link)
{
    static $cache = array();
    $r = @mysqli_query($link, 'SELECT DATABASE()');
    $db = $r ? (string) current(mysqli_fetch_row($r)) : '';
    if (!isset($cache[$db])) {
        $cache[$db] = array();
        $r = @mysqli_query($link, "SELECT DISTINCT COLUMN_NAME FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND DATA_TYPE IN ('date','datetime','timestamp')");
        while ($r && ($row = mysqli_fetch_row($r))) {
            $cache[$db][] = preg_quote($row[0], '/');
        }
    }
    return $cache[$db];
}

function __compat_retry_empty_dates($link, $query, $result_mode = MYSQLI_STORE_RESULT)
{
    if (mysqli_errno($link) != 1525 || !preg_match('/^\s*\(?\s*select\b/i', $query) || strpos($query, "''") === false) {
        return false;
    }
    $cols = __compat_date_columns($link);
    if (!$cols) return false;
    $col = '((?:`?\w+`?\.)?`?(?:' . implode('|', $cols) . ')`?)';
    $fixed = preg_replace('/' . $col . '(\s*(?:=|<=>|<>|!=|<=|>=|<|>)\s*)\'\'/i', "$1$2'0000-00-00'", $query);
    $fixed = preg_replace_callback('/' . $col . '(\s+between\s+)\'([^\']*)\'(\s+and\s+)\'([^\']*)\'/i', function ($m) {
        return $m[1] . $m[2] . "'" . ($m[3] === '' ? '0000-00-00' : $m[3]) . "'" . $m[4] . "'" . ($m[5] === '' ? '0000-00-00' : $m[5]) . "'";
    }, $fixed);
    if ($fixed === $query) return false;
    try {
        return mysqli_query($link, $fixed, $result_mode);
    } catch (Throwable $e) {
        return false;
    }
}

/** Pengganti mysqli_query() (signature sama) dengan penanganan tanggal kosong. */
function db_query($link, $query, $result_mode = MYSQLI_STORE_RESULT)
{
    try {
        $res = mysqli_query($link, (string) $query, $result_mode);
    } catch (Throwable $e) {
        return false;
    }
    if ($res === false) {
        $retry = __compat_retry_empty_dates($link, (string) $query, $result_mode);
        if ($retry !== false) return $retry;
        if (COMPAT_WARNING_LOG) {
            trigger_error('mysqli_query(): ' . mysqli_error($link) . ' | SQL: ' . substr(preg_replace('/\s+/', ' ', (string) $query), 0, 300), E_USER_WARNING);
        }
    }
    return $res;
}

/* Pengganti mysqli_fetch_*() / mysqli_num_rows(): di PHP 5 hasil query yang gagal (false)
 * hanya memberi warning dan mengembalikan null; di PHP 8 menjadi fatal TypeError. */
function db_fetch_array($result, $mode = MYSQLI_BOTH)
{
    return $result instanceof mysqli_result ? mysqli_fetch_array($result, $mode) : null;
}

function db_fetch_assoc($result)
{
    return $result instanceof mysqli_result ? mysqli_fetch_assoc($result) : null;
}

function db_fetch_row($result)
{
    return $result instanceof mysqli_result ? mysqli_fetch_row($result) : null;
}

function db_fetch_object($result, $class = 'stdClass', $args = array())
{
    if (!($result instanceof mysqli_result)) return null;
    return $args ? mysqli_fetch_object($result, $class, $args) : mysqli_fetch_object($result, $class);
}

function db_num_rows($result)
{
    return $result instanceof mysqli_result ? mysqli_num_rows($result) : 0;
}

/** mysqli dengan query() yang menangani tanggal kosong; dipakai oleh koneksi_db.php. */
class CompatMysqli extends mysqli
{
    #[\ReturnTypeWillChange]
    public function query($query, $result_mode = MYSQLI_STORE_RESULT)
    {
        return db_query($this, $query, $result_mode);
    }
}

/* =====================================================================
 * Argumen angka. Di PHP 8 number_format('') / round('abc') dsb. = TypeError.
 * compat_num() mengubah nilai kosong/teks menjadi angka seperti PHP 5
 * ('' dan null -> 0, "12abc" -> 12); angka & string angka tidak diubah.
 * ===================================================================== */
function compat_num($value)
{
    if ($value === null || $value === '' || is_bool($value)) return (int) $value;
    if (is_string($value) && !is_numeric($value)) return (float) $value;
    return $value;
}
