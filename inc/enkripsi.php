<?php
/**
 * Enkripsi data pribadi (NIK, alamat) sebelum disimpan ke database: AES-256-GCM (OpenSSL) dengan nonce acak dan
 * tag keutuhan, plus indeks buta (HMAC-SHA256) untuk mencari/menolak NIK dobel tanpa menyimpan NIK terbuka.
 *
 *   $isi  = enkripsi_data($nik);           // 'enc:v1:...' untuk disimpan
 *   $nik  = dekripsi_data($isi);            // teks asli; teks lama yang belum terenkripsi dikembalikan apa adanya
 *   $idx  = indeks_nik($nik);               // 64 karakter hex, simpan di kolom indeks untuk WHERE ... = ?
 *   $tampil = samarkan_nik($nik);           // 3201********0001
 *
 * Kunci TIDAK ikut git: diisi di koneksi_db.lokal.php, bagian 'enkripsi' => array('kunci' => ..., 'kunci_indeks' => ...),
 * masing-masing 32 byte acak dalam base64 (buat dengan: php inc/enkripsi.php buat-kunci). Simpan cadangan kunci
 * terpisah dari backup database: tanpa kunci, data terenkripsi tidak dapat dibuka lagi.
 * Format: 'enc:v1:' + base64(nonce 12 byte + tag 16 byte + ciphertext). Awalan versi memungkinkan ganti kunci kelak.
 * Membutuhkan koneksi_db.php (yang memuat koneksi_db.lokal.php ke $GLOBALS['DB_CONFIG']).
 */

const ENKRIPSI_AWALAN = 'enc:v1:';
const ENKRIPSI_CIPHER = 'aes-256-gcm';
const ENKRIPSI_AAD = 'nurinsani:v1';

/** Kunci (biner 32 byte) dari konfigurasi; $jenis 'kunci' (enkripsi) atau 'kunci_indeks' (indeks buta). */
function enkripsi_kunci($jenis)
{
    static $cache = array();
    if (isset($cache[$jenis])) {
        return $cache[$jenis];
    }
    $cfg = isset($GLOBALS['DB_CONFIG']['enkripsi']) ? $GLOBALS['DB_CONFIG']['enkripsi'] : array();
    $kunci = isset($cfg[$jenis]) ? base64_decode((string) $cfg[$jenis], true) : false;
    if ($kunci === false || strlen($kunci) !== 32) {
        $pesan = "Kunci enkripsi '$jenis' belum diatur di koneksi_db.lokal.php (32 byte base64).";
        if (PHP_SAPI === 'cli') {
            throw new RuntimeException($pesan);
        }
        // halaman web: berhenti tanpa menyimpan apa pun (tidak pernah menyimpan teks terbuka), detail hanya di log server
        error_log('nurinsani enkripsi: ' . $pesan);
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        exit('<p style="font-family:sans-serif;color:#842029">Proses dihentikan: kunci enkripsi data pribadi belum diatur di server. '
            . 'Data tidak disimpan. Hubungi administrator sistem.</p>');
    }
    return $cache[$jenis] = $kunci;
}

/** true bila nilai sudah berformat terenkripsi. */
function data_terenkripsi($nilai)
{
    return is_string($nilai) && strncmp($nilai, ENKRIPSI_AWALAN, strlen(ENKRIPSI_AWALAN)) === 0;
}

/** Enkripsi teks; null/'' tetap '' (tidak dienkripsi) agar kolom kosong tetap kosong. Nilai sudah terenkripsi tidak diulang. */
function enkripsi_data($teks)
{
    $teks = (string) $teks;
    if ($teks === '' || data_terenkripsi($teks)) {
        return $teks;
    }
    $nonce = random_bytes(12);
    $tag = '';
    $isi = openssl_encrypt($teks, ENKRIPSI_CIPHER, enkripsi_kunci('kunci'), OPENSSL_RAW_DATA, $nonce, $tag, ENKRIPSI_AAD, 16);
    if ($isi === false) {
        throw new RuntimeException('Enkripsi gagal.');
    }
    return ENKRIPSI_AWALAN . base64_encode($nonce . $tag . $isi);
}

/**
 * Dekripsi nilai dari database. Nilai tanpa awalan (data lama, belum terenkripsi) dikembalikan apa adanya, sehingga
 * pembaca tetap jalan selama masa peralihan. Hasil null bila data rusak/diubah atau kunci salah.
 */
function dekripsi_data($nilai)
{
    if ($nilai === null) {
        return null;
    }
    $nilai = (string) $nilai;
    if (!data_terenkripsi($nilai)) {
        return $nilai;
    }
    $biner = base64_decode(substr($nilai, strlen(ENKRIPSI_AWALAN)), true);
    if ($biner === false || strlen($biner) < 29) {
        return null;
    }
    $teks = openssl_decrypt(substr($biner, 28), ENKRIPSI_CIPHER, enkripsi_kunci('kunci'), OPENSSL_RAW_DATA,
        substr($biner, 0, 12), substr($biner, 12, 16), ENKRIPSI_AAD);
    return $teks === false ? null : $teks;
}

/**
 * Teks dipotong ke $maks karakter sebelum dienkripsi, agar hasil enkripsi (+-1,7x lebih panjang) muat di kolom
 * varchar(255): teks terenkripsi yang terpotong oleh MySQL tidak dapat dibuka lagi. Alamat 100, NIK 40 karakter.
 */
function potong_data($teks, $maks)
{
    return mb_substr(trim((string) $teks), 0, $maks, 'UTF-8');
}

/** NIK dinormalkan: hanya angka dan huruf, huruf besar (spasi, titik, tanda hubung dibuang). */
function normal_nik($nik)
{
    return strtoupper(preg_replace('/[^0-9A-Za-z]/', '', (string) $nik));
}

/** Indeks buta NIK (HMAC-SHA256 hex, 64 karakter) untuk pencarian persis / cek dobel; '' bila NIK kosong. */
function indeks_nik($nik)
{
    $n = normal_nik(dekripsi_data($nik));
    return $n === '' ? '' : hash_hmac('sha256', $n, enkripsi_kunci('kunci_indeks'));
}

/** NIK tersamar untuk tampilan layar: 4 digit awal dan akhir terlihat (3201********0001). */
function samarkan_nik($nik)
{
    $n = (string) dekripsi_data($nik);
    $p = strlen($n);
    return $p <= 8 ? str_repeat('*', $p) : substr($n, 0, 4) . str_repeat('*', $p - 8) . substr($n, -4);
}

/**
 * NIK untuk daftar/label di layar: NIK yang tersimpan terenkripsi ditampilkan tersamar (keputusan user 2026-11-04);
 * NIK lama yang belum terenkripsi (anggota pembiayaan, menunggu tahap 2) tampil apa adanya agar layar lama tidak berubah.
 */
function tampil_nik($nilai)
{
    return data_terenkripsi($nilai) ? samarkan_nik($nilai) : (string) $nilai;
}

// php inc/enkripsi.php buat-kunci  -> cetak dua kunci baru untuk koneksi_db.lokal.php
if (PHP_SAPI === 'cli' && isset($argv[1]) && $argv[1] === 'buat-kunci' && realpath($argv[0]) === __FILE__) {
    echo "'enkripsi' => array(\n";
    echo "    'kunci' => '" . base64_encode(random_bytes(32)) . "',\n";
    echo "    'kunci_indeks' => '" . base64_encode(random_bytes(32)) . "',\n";
    echo "),\n";
}
