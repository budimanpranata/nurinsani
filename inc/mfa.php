<?php
/**
 * MFA login dengan kode 6 digit dari aplikasi authenticator (Google Authenticator, Microsoft Authenticator, Authy):
 * TOTP RFC 6238 (HMAC-SHA1, 6 digit, periode 30 detik). Wajib untuk SEMUA user (perintah user 2026-11-09,
 * PEKERJAAN_TERTUNDA.md bagian 101). Tidak butuh internet/layanan luar; jam server harus akurat (NTP).
 *
 * Kunci rahasia per user disimpan terenkripsi (inc/enkripsi.php) di tabel user_mfa (db/parameter/2026-11-09_mfa.sql).
 * Alur: log.php (password benar) -> mfa.php (daftar / masukkan kode) -> log.php?op=lanjut (sesi login dibuat).
 * Reset MFA hanya lewat Admin > Reset Password, atau darurat lewat CLI db/parameter/reset_mfa.php.
 * Perangkat tepercaya 30 hari: lihat mfa_perangkat_*() di bawah.
 * Membutuhkan koneksi_db.php.
 */
require_once __DIR__ . '/enkripsi.php';

const MFA_PERIODE = 30;
const MFA_DIGIT = 6;
const MFA_TOLERANSI = 1;          // terima kode 1 langkah sebelum/sesudah (selisih jam HP +- 30 detik)
const MFA_MAKS_GAGAL = 5;         // kode salah berturut-turut sebelum dikunci
const MFA_LAMA_KUNCI = 900;       // detik (15 menit)
const MFA_BATAS_TUNGGU = 300;     // detik antara password benar dan kode MFA dimasukkan
const MFA_PENERBIT = 'KSPPS Nur Insani';

/** Kunci rahasia baru: 20 byte acak (160 bit, sesuai RFC 4226) dalam base32 tanpa padding (32 karakter). */
function mfa_rahasia_baru()
{
    return mfa_base32_encode(random_bytes(20));
}

function mfa_base32_encode($biner)
{
    $abjad = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bit = '';
    foreach (str_split($biner) as $c) {
        $bit .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    }
    $hasil = '';
    foreach (str_split($bit, 5) as $potong) {
        $hasil .= $abjad[bindec(str_pad($potong, 5, '0', STR_PAD_RIGHT))];
    }
    return $hasil;
}

/** Base32 -> biner; false bila ada karakter tidak sah. Spasi, tanda '-' dan '=' diabaikan, huruf kecil diterima. */
function mfa_base32_decode($teks)
{
    $abjad = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $teks = strtoupper(preg_replace('/[\s=-]/', '', (string) $teks));
    $bit = '';
    for ($i = 0, $n = strlen($teks); $i < $n; $i++) {
        $p = strpos($abjad, $teks[$i]);
        if ($p === false) {
            return false;
        }
        $bit .= str_pad(decbin($p), 5, '0', STR_PAD_LEFT);
    }
    $hasil = '';
    foreach (str_split($bit, 8) as $b) {
        if (strlen($b) === 8) {
            $hasil .= chr(bindec($b));
        }
    }
    return $hasil;
}

/** Langkah waktu TOTP untuk sebuah detik unix. */
function mfa_langkah($waktu = null)
{
    return intdiv($waktu === null ? time() : (int) $waktu, MFA_PERIODE);
}

/** Kode 6 digit untuk langkah waktu tertentu (RFC 6238 / RFC 4226). */
function mfa_kode($rahasia, $langkah)
{
    $kunci = mfa_base32_decode($rahasia);
    $hash = hash_hmac('sha1', pack('J', (int) $langkah), (string) $kunci, true);
    $geser = ord($hash[19]) & 0x0f;
    $angka = ((ord($hash[$geser]) & 0x7f) << 24) | (ord($hash[$geser + 1]) << 16)
        | (ord($hash[$geser + 2]) << 8) | ord($hash[$geser + 3]);
    return str_pad((string) ($angka % (10 ** MFA_DIGIT)), MFA_DIGIT, '0', STR_PAD_LEFT);
}

/**
 * Cocokkan kode yang diketik user. Mengembalikan langkah waktu yang cocok, atau false.
 * Hanya langkah SESUDAH $langkah_terakhir yang diterima, supaya kode yang sama tidak bisa dipakai dua kali.
 */
function mfa_cocok($rahasia, $kode, $langkah_terakhir = 0, $waktu = null)
{
    $kode = preg_replace('/\D/', '', (string) $kode);
    if (strlen($kode) !== MFA_DIGIT) {
        return false;
    }
    $sekarang = mfa_langkah($waktu);
    for ($d = -MFA_TOLERANSI; $d <= MFA_TOLERANSI; $d++) {
        $l = $sekarang + $d;
        if ($l > (int) $langkah_terakhir && hash_equals(mfa_kode($rahasia, $l), $kode)) {
            return $l;
        }
    }
    return false;
}

/** Alamat otpauth:// untuk QR code aplikasi authenticator. */
function mfa_uri($userid, $rahasia)
{
    $label = rawurlencode(MFA_PENERBIT) . ':' . rawurlencode((string) $userid);
    return 'otpauth://totp/' . $label . '?secret=' . $rahasia . '&issuer=' . rawurlencode(MFA_PENERBIT)
        . '&algorithm=SHA1&digits=' . MFA_DIGIT . '&period=' . MFA_PERIODE;
}

/** Kunci rahasia dikelompokkan per 4 huruf supaya mudah diketik manual. */
function mfa_rahasia_tampil($rahasia)
{
    return trim(chunk_split($rahasia, 4, ' '));
}

/** Baris user_mfa (rahasia sudah didekripsi) atau null bila user belum mendaftar. */
function mfa_data($userid)
{
    $b = db_satu("select userid, rahasia, langkah_terakhir, gagal, kunci_sampai, didaftar, terakhir_masuk,
        (kunci_sampai is not null and kunci_sampai > now()) as terkunci
        from user_mfa where userid = ?", 's', (string) $userid);
    if (!$b) {
        return null;
    }
    $b['rahasia'] = dekripsi_data($b['rahasia']);
    return $b;
}

/** Simpan pendaftaran baru (kode pertama sudah terbukti cocok). */
function mfa_daftar($userid, $rahasia, $langkah)
{
    db_jalankan("insert into user_mfa (userid, rahasia, langkah_terakhir, gagal, kunci_sampai, didaftar, terakhir_masuk)
        values (?, ?, ?, 0, null, now(), now())
        on duplicate key update rahasia = values(rahasia), langkah_terakhir = values(langkah_terakhir), gagal = 0,
            kunci_sampai = null, didaftar = now(), terakhir_masuk = now()",
        'sss', (string) $userid, enkripsi_data($rahasia), (string) $langkah);
}

/** Kode benar: catat langkah terakhir (anti pakai ulang), nolkan hitungan gagal. */
function mfa_berhasil($userid, $langkah)
{
    db_jalankan("update user_mfa set langkah_terakhir = ?, gagal = 0, kunci_sampai = null, terakhir_masuk = now()
        where userid = ?", 'ss', (string) $langkah, (string) $userid);
}

/** Kode salah: tambah hitungan; sesudah MFA_MAKS_GAGAL kali dikunci MFA_LAMA_KUNCI detik. Mengembalikan true bila kini terkunci. */
function mfa_gagal($userid)
{
    db_jalankan("update user_mfa set gagal = gagal + 1,
        kunci_sampai = if(gagal >= ?, now() + interval ? second, kunci_sampai) where userid = ?",
        'sss', (string) MFA_MAKS_GAGAL, (string) MFA_LAMA_KUNCI, (string) $userid);
    $b = db_satu("select gagal from user_mfa where userid = ?", 's', (string) $userid);
    if ($b && (int) $b['gagal'] >= MFA_MAKS_GAGAL) {
        db_jalankan("update user_mfa set gagal = 0 where userid = ?", 's', (string) $userid);
        return true;
    }
    return false;
}

/** Hapus MFA user (Admin > Reset Password / CLI): user mendaftar ulang dengan QR baru pada login berikutnya. */
function mfa_hapus($userid)
{
    $st = db_ni()->prepare("delete from user_mfa where userid = ?");
    $u = (string) $userid;
    $st->bind_param('s', $u);
    $st->execute();
    return $st->affected_rows;
}

/*
 * Perangkat tepercaya (2026-11-09, perintah user): sesudah kode MFA benar, browser itu diingat MFA_INGAT_HARI hari (sampai
 * 23.59 WIB hari ke-30; semula sampai akhir hari, diubah atas perintah user karena user memakai PC yang tetap). Login ulang
 * di browser yang sama selama itu (sesudah logout / idle 30 menit) cukup dengan password.
 * Cookie per user, isinya: batas waktu + HMAC-SHA256 (kunci turunan dari kunci enkripsi server) atas userid, batas
 * waktu dan "sidik" akun = hash password + waktu daftar MFA. Tidak bisa dipalsukan / dipindah ke user lain, dan
 * otomatis tidak berlaku sesudah Reset Password, ganti password, atau Reset MFA (sidik berubah). Tidak ada tabel baru.
 */
const MFA_COOKIE_AWALAN = 'ni_mfa_';
const MFA_INGAT_HARI = 30;

function mfa_perangkat_kunci()
{
    return hash_hmac('sha256', 'nurinsani:mfa-perangkat:v1', enkripsi_kunci('kunci'), true);
}

/** Nama cookie per user (userid tidak terlihat di nama cookie). */
function mfa_perangkat_nama($userid)
{
    return MFA_COOKIE_AWALAN . substr(hash_hmac('sha256', strtolower((string) $userid), mfa_perangkat_kunci()), 0, 16);
}

/** Sidik akun: berubah bila password atau pendaftaran MFA berubah. null bila user belum mendaftar MFA. */
function mfa_perangkat_sidik($userid)
{
    $b = db_satu("select user.pass, user_mfa.didaftar from user join user_mfa on user_mfa.userid = user.userid
        where user.userid = ?", 's', (string) $userid);
    return $b ? $b['pass'] . '|' . $b['didaftar'] : null;
}

function mfa_perangkat_tag($userid, $sampai, $sidik)
{
    return hash_hmac('sha256', strtolower((string) $userid) . '|' . (int) $sampai . '|' . $sidik, mfa_perangkat_kunci());
}

/** Detik unix pukul 23:59:59 WIB hari ini. */
function mfa_akhir_hari()
{
    $besok = new DateTime('tomorrow', new DateTimeZone('Asia/Jakarta'));
    return $besok->getTimestamp() - 1;
}

/** Folder aplikasi untuk path cookie (dihitung seperti URL halaman login, inc/cek_login.php). */
function mfa_cookie_path()
{
    require_once __DIR__ . '/cek_login.php';
    $p = rtrim(str_replace('\\', '/', dirname(url_halaman_login())), '/') . '/';
    // path cookie tidak boleh memuat spasi/;/, (setcookie melempar ValueError): pakai seluruh situs
    return preg_match('#^/[^,; \t\r\n\x0b\x0c]*$#', $p) ? $p : '/';
}

function mfa_cookie_kirim($nama, $nilai, $sampai)
{
    setcookie($nama, $nilai, array(
        'expires' => $sampai,
        'path' => mfa_cookie_path(),
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ));
}

/** Batas perangkat tepercaya: 23:59:59 WIB, MFA_INGAT_HARI hari dari hari ini. */
function mfa_batas_ingat()
{
    return mfa_akhir_hari() + MFA_INGAT_HARI * 86400;   // WIB tanpa pergantian jam musim panas
}

/** Ingat browser ini untuk $userid selama MFA_INGAT_HARI hari. */
function mfa_perangkat_ingat($userid)
{
    $sidik = mfa_perangkat_sidik($userid);
    if ($sidik === null) {
        return;
    }
    $sampai = mfa_batas_ingat();
    mfa_cookie_kirim(mfa_perangkat_nama($userid), $sampai . '.' . mfa_perangkat_tag($userid, $sampai, $sidik), $sampai);
}

/** Hapus tanda perangkat tepercaya $userid di browser ini. */
function mfa_perangkat_lupakan($userid)
{
    $nama = mfa_perangkat_nama($userid);
    if (isset($_COOKIE[$nama])) {
        mfa_cookie_kirim($nama, '', 1);
    }
}

/** true bila browser ini sudah diingat untuk $userid dan tandanya masih sah (hari ini, sidik akun sama). */
function mfa_perangkat_dikenal($userid)
{
    $nama = mfa_perangkat_nama($userid);
    $nilai = isset($_COOKIE[$nama]) ? (string) $_COOKIE[$nama] : '';
    if (!preg_match('/^(\d{1,12})\.([0-9a-f]{64})$/', $nilai, $m)) {
        return false;
    }
    $sampai = (int) $m[1];
    if ($sampai < time() || $sampai > mfa_batas_ingat()) {
        return false;
    }
    $sidik = mfa_perangkat_sidik($userid);
    return $sidik !== null && hash_equals(mfa_perangkat_tag($userid, $sampai, $sidik), $m[2]);
}

// uji cepat dari CLI: php inc/mfa.php uji  -> vektor uji RFC 6238 (SHA1)
if (PHP_SAPI === 'cli' && isset($argv[1]) && $argv[1] === 'uji' && realpath($argv[0]) === __FILE__) {
    $r = mfa_base32_encode('12345678901234567890');
    $ok = true;
    foreach (array(59 => '287082', 1111111109 => '081804', 1111111111 => '050471', 1234567890 => '005924',
        2000000000 => '279037', 20000000000 => '353130') as $t => $harap) {
        $k = mfa_kode($r, mfa_langkah($t));
        $ok = $ok && $k === $harap;
        echo "$t $k " . ($k === $harap ? 'OK' : "SALAH (harus $harap)") . "\n";
    }
    echo $ok ? "SEMUA OK\n" : "ADA YANG SALAH\n";
}
