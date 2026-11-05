<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Export Neraca Konsolidasi (kp/laporan/konsolidasi.php, tombol Neraca) dengan indikator progres, perintah user 2026-11-04.
 * Isi file sama persis dengan laporan lama: neraca_konsol.php dijalankan dalam mode KONSOL_EXPORT_JOB (tanpa header
 * unduhan dan tanpa kunci sesi), keluarannya disimpan ke file sementara, lalu diunduh sebagai consol.xls.
 * Progres = jumlah query yang sudah dijalankan / 17 (jumlah query satu laporan; 294 sebelum neraca_konsol.php dibuat
 * dinamis 2026-11-05), dihitung lewat kait mysql_query di php8_compat.php.
 *
 *   POST aksi=mulai              -> buat pekerjaan dari tanggal & jenis di sesi (tgl_cari_konsols, jenis), hasil {job, total}
 *   POST aksi=buat&job=...       -> jalankan laporan (lama), hasil {selesai, ukuran} atau {pesan}
 *   GET  aksi=status&job=...     -> {query, total, selesai, pesan}
 *   GET  aksi=unduh&job=...      -> kirim consol.xls lalu hapus file sementara
 */
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';

const KONSOL_TOTAL_QUERY = 17;

function jawab_json($data, $kode = 200)
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$aksi = isset($_REQUEST['aksi']) ? (string) $_REQUEST['aksi'] : '';
wajib_login($aksi === 'unduh' ? 'halaman' : 'json');
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "kp") {
    jawab_json(array('pesan' => 'Anda bukan user kp'), 403);
}
$userid = (string) $_SESSION['userid'];

$folder_induk = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nurinsani_export';
if (!is_dir($folder_induk)) {
    @mkdir($folder_induk, 0700, true);
}

function muat_job($folder_induk, $userid)
{
    $job = isset($_REQUEST['job']) ? (string) $_REQUEST['job'] : '';
    if (!preg_match('/^[a-f0-9]{24}$/', $job)) {
        return null;
    }
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    $meta = @json_decode((string) @file_get_contents($dir . '/meta.json'), true);
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis_job'] !== 'neraca_konsol') {
        return null;
    }
    $meta['dir'] = $dir;
    return $meta;
}

function hapus_folder($dir)
{
    foreach (glob($dir . '/*') ?: array() as $f) {
        @unlink($f);
    }
    @rmdir($dir);
}

/** Simpan status progres (ditulis ke file supaya bisa dibaca permintaan status yang berjalan bersamaan). */
function tulis_status($dir, $status)
{
    file_put_contents($dir . '/status.json', json_encode($status), LOCK_EX);
}

if ($aksi === 'mulai') {
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    $tgl = isset($_SESSION['tgl_cari_konsols']) ? (string) $_SESSION['tgl_cari_konsols'] : '';
    $d = DateTime::createFromFormat('!Y-m-d', $tgl);
    // neraca_konsol.php masih menempel tanggal ke teks SQL (mysql_query compat, belum prepared): hanya tanggal sah
    if (!$d || $d->format('Y-m-d') !== $tgl) {
        jawab_json(array('pesan' => 'Pilih tanggal lalu tekan Search terlebih dahulu.'), 400);
    }
    $job = bin2hex(random_bytes(12));
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    if (!@mkdir($dir, 0700)) {
        jawab_json(array('pesan' => 'Folder sementara export tidak bisa dibuat.'), 500);
    }
    $meta = array('jenis_job' => 'neraca_konsol', 'userid' => $userid, 'tgl' => $tgl,
        'jenis' => isset($_SESSION['jenis']) ? (string) $_SESSION['jenis'] : '');
    file_put_contents($dir . '/meta.json', json_encode($meta), LOCK_EX);
    tulis_status($dir, array('query' => 0, 'selesai' => false, 'pesan' => ''));
    jawab_json(array('job' => $job, 'total' => KONSOL_TOTAL_QUERY));
}

$meta = muat_job($folder_induk, $userid);
if (!$meta) {
    jawab_json(array('pesan' => 'Pekerjaan export tidak ditemukan atau sudah selesai. Silakan ulangi.'), 404);
}
$dir = $meta['dir'];

if ($aksi === 'status') {
    session_write_close();
    $s = @json_decode((string) @file_get_contents($dir . '/status.json'), true) ?: array('query' => 0, 'selesai' => false, 'pesan' => '');
    $s['total'] = KONSOL_TOTAL_QUERY;
    jawab_json($s);
}

if ($aksi === 'buat') {
    // sesi ditutup supaya permintaan status (sesi yang sama) tidak menunggu laporan selesai; nilai sesi tetap terbaca
    session_write_close();
    set_time_limit(0);
    $_SESSION['tgl_cari_konsols'] = $meta['tgl'];
    $_SESSION['jenis'] = $meta['jenis'];
    $n = 0;
    $GLOBALS['__compat_kait_query'] = function () use (&$n, $dir) {
        $n++;
        if ($n % 5 === 0) {
            tulis_status($dir, array('query' => $n, 'selesai' => false, 'pesan' => ''));
        }
    };
    define('KONSOL_EXPORT_JOB', true);
    $level = ob_get_level();
    ob_start();
    $galat = '';
    try {
        (function () {
            // lingkup variabel sendiri, seperti saat laporan dibuka langsung
            chdir(__DIR__);
            include __DIR__ . '/neraca_konsol.php';
        })();
    } catch (Throwable $e) {
        $galat = 'Laporan gagal dibuat: ' . $e->getMessage();
    }
    // gabungkan buffer yang dibuka laporan (ob_start di neraca_konsol.php) ke buffer ini
    while (ob_get_level() > $level + 1) {
        ob_end_flush();
    }
    $isi = (string) ob_get_clean();
    unset($GLOBALS['__compat_kait_query']);
    if ($galat === '' && !file_put_contents($dir . '/consol.xls', $isi)) {
        $galat = 'Gagal menulis file sementara export.';
    }
    tulis_status($dir, array('query' => $n, 'selesai' => $galat === '', 'pesan' => $galat));
    if ($galat !== '') {
        jawab_json(array('pesan' => $galat), 500);
    }
    jawab_json(array('selesai' => true, 'query' => $n, 'ukuran' => strlen($isi)));
}

if ($aksi === 'unduh') {
    $berkas = $dir . '/consol.xls';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=consol.xls");
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($dir);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
