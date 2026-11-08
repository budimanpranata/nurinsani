<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Export Data Jatuh Tempo (fund/view/view_tempo.php, filter sesi tempo_filter), Data Break (?laporan=break,
 * fund/view/view_break.php, filter sesi break_filter), Cetak Bilyet (?laporan=bilyet, fund/view/view_cetak_bilyet.php,
 * filter sesi bilyet_filter), Cetak Advis (?laporan=advis, fund/view/view_advise.php, filter sesi advis_filter) dan
 * Reprint (?laporan=reprint, fund/view/view_reprint.php, filter sesi reprint_filter) dan Data Nominatif (?laporan=nomi,
 * fund/view/view_nomi.php, filter sesi nomi_filter) ke .xlsx secara bertahap.
 *
 *   POST aksi=mulai              -> hitung jumlah deposito, hasil {job, total}
 *   POST aksi=proses&job=...     -> tulis 1000 deposito berikutnya
 *   POST aksi=rakit&job=...      -> baris TOTAL + rakit .xlsx, hasil {unduh, ringkasan}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Kolom sama dengan export.php versi lama (NOMINATIVE DEPOSITO). Perbaikan 2026-11-04 (perintah user): kolom ALAMAT
 * (didekripsi), START DATE, RASIO dan TABUNGAN (saldo escrow_deposito norek itu) kini terisi; dulu kosong karena query
 * halaman tidak mengambil kolom tersebut. Pengganti export.php (menerima teks SQL dari form; dihapus 2026-11-04 setelah
 * Data Nominatif, pemakai terakhirnya, pindah ke sini).
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/xlsx_writer.php';
require_once __DIR__ . '/../../inc/enkripsi.php';
require_once __DIR__ . '/../../inc/jatuh_tempo_deposito.php';
require_once __DIR__ . '/../../inc/break_deposito.php';
require_once __DIR__ . '/../../inc/cetak_deposito.php';
require_once __DIR__ . '/../../inc/nominatif_deposito.php';

const TEMPO_PER_TAHAP = 1000;

function jawab_json($data, $kode = 200)
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$aksi = isset($_REQUEST['aksi']) ? (string) $_REQUEST['aksi'] : '';
wajib_login($aksi === 'unduh' ? 'halaman' : 'json');
if (!in_array(isset($_SESSION['level']) ? $_SESSION['level'] : '', array('fund', 'admin'), true)) {
    jawab_json(array('pesan' => 'Anda bukan user'), 403);
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
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis'] !== 'tempo_fund') {
        return null;
    }
    $meta['dir'] = $dir;
    return $meta;
}

function simpan_job($meta)
{
    $dir = $meta['dir'];
    unset($meta['dir']);
    file_put_contents($dir . '/meta.json', json_encode($meta), LOCK_EX);
}

function hapus_folder($dir)
{
    foreach (glob($dir . '/*') ?: array() as $f) {
        @unlink($f);
    }
    @rmdir($dir);
}

/** Laporan yang dikenal: kunci => array(nama sheet, awalan nama file). */
const EXPORT_LAPORAN = array('tempo' => array('Jatuh Tempo', 'jatuh_tempo'), 'break' => array('Break', 'break_deposito'),
    'bilyet' => array('Cetak Bilyet', 'cetak_bilyet'), 'advis' => array('Cetak Advis', 'cetak_advis'),
    'reprint' => array('Reprint', 'reprint_deposito'), 'nomi' => array('Nominatif', 'nominatif_deposito'));

/** Daftar baris laporan menurut filternya. */
function export_daftar($laporan, $filter, $kolom)
{
    if ($laporan === 'break') {
        return break_daftar($filter, $kolom);
    }
    if ($laporan === 'bilyet') {
        return bilyet_daftar($filter, $kolom);
    }
    if ($laporan === 'advis') {
        return advis_daftar($filter, $kolom);
    }
    if ($laporan === 'reprint') {
        return reprint_daftar($filter, $kolom);
    }
    if ($laporan === 'nomi') {
        return nomi_daftar($filter, $kolom);
    }
    return tempo_daftar($filter, $kolom);
}

/** Teks filter untuk ringkasan. */
function export_keterangan($laporan, $f)
{
    if ($laporan !== 'tempo') {
        $nama = EXPORT_LAPORAN[$laporan][0];
        return $f['cari'] !== '' ? $nama . ', cari "' . $f['cari'] . '"' : $nama . ', semua';
    }
    return tempo_keterangan_cari($f) !== '' ? 'Cari ' . tempo_keterangan_cari($f) : 'Bulan ' . $f['bulan'];
}

$kolom = "deposito.norek, deposito.bilyet, deposito.cif, CUST_SHORT_NAME, HADDRESS1, deposito.nominal, deposito.star_date,
    deposito.maturity_date, deposito.tenor, deposito.rasio,
    (select sum(KREDIT)-sum(DEBIT) from escrow_deposito where escrow_deposito.NOREK = deposito.norek) as saldo";
$judul = array('NOMOR', 'NOREK', 'BILYET', 'CIF', 'NAMA', 'ALAMAT', 'NOMINAL', 'START DATE', 'JATUH TEMPO', 'JANGKA WAKTU', 'RASIO', 'TABUNGAN');
$lebar = array(8, 18, 10, 12, 30, 40, 16, 20, 20, 12, 8, 16);

if ($aksi === 'mulai') {
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    $laporan = isset($_REQUEST['laporan']) && isset(EXPORT_LAPORAN[(string) $_REQUEST['laporan']]) ? (string) $_REQUEST['laporan'] : 'tempo';
    // Cetak Bilyet & Reprint pindah ke level admin (2026-11-07); laporan lain tetap fund. Tahap sesudahnya memakai job milik user ini.
    if ($_SESSION['level'] !== (in_array($laporan, array('bilyet', 'reprint'), true) ? 'admin' : 'fund')) {
        jawab_json(array('pesan' => 'Anda bukan user'), 403);
    }
    if ($laporan !== 'tempo') {
        $filter = isset($_SESSION[$laporan . '_filter']) ? $_SESSION[$laporan . '_filter'] : array('cari' => '');
    } else {
        $filter = isset($_SESSION['tempo_filter']) ? $_SESSION['tempo_filter'] : array('tanggal' => '', 'nama' => '', 'bulan' => date('Y-m'));
    }
    $total = count(export_daftar($laporan, $filter, 'deposito.norek'));
    $meta = array('jenis' => 'tempo_fund', 'laporan' => $laporan, 'userid' => $userid, 'filter' => $filter, 'total' => $total, 'baris' => 1,
        'selesai' => 0, 'tot_nominal' => 0, 'tot_saldo' => 0);
    $job = bin2hex(random_bytes(12));
    $meta['dir'] = $folder_induk . DIRECTORY_SEPARATOR . $job;
    if (!@mkdir($meta['dir'], 0700)) {
        jawab_json(array('pesan' => 'Folder sementara export tidak bisa dibuat.'), 500);
    }
    simpan_job($meta);
    jawab_json(array('job' => $job, 'total' => $total));
}

$meta = muat_job($folder_induk, $userid);
if (!$meta) {
    jawab_json(array('pesan' => 'Pekerjaan export tidak ditemukan atau sudah selesai. Silakan ulangi.'), 404);
}

if ($aksi === 'proses') {
    $data = array_slice(export_daftar($meta['laporan'], $meta['filter'], $kolom), (int) $meta['selesai'], TEMPO_PER_TAHAP);
    $xml = '';
    $baris = (int) $meta['baris'];
    foreach ($data as $r) {
        $baris++;
        $nominal = compat_num($r['nominal']);
        $saldo = compat_num($r['saldo']);
        $xml .= xlsx_baris_xml($baris, array(
            array('n', $baris - 1),
            array('s', $r['norek']),
            array('s', $r['bilyet']),
            array('s', $r['cif']),
            array('s', $r['CUST_SHORT_NAME']),
            array('s', dekripsi_data($r['HADDRESS1'])),
            array('n', $nominal),
            array('s', $r['star_date']),
            array('s', $r['maturity_date']),
            array('s', $r['tenor']),
            array('s', $r['rasio']),
            array('n', $saldo),
        ));
        $meta['tot_nominal'] += $nominal;
        $meta['tot_saldo'] += $saldo;
    }
    if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . '/data.part', $xml)) {
        jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
    }
    $meta['baris'] = $baris;
    $meta['selesai'] += count($data);
    $habis = count($data) < TEMPO_PER_TAHAP;
    simpan_job($meta);
    jawab_json(array('selesai_baris' => $meta['selesai'], 'total' => $meta['total'], 'habis' => $habis));
}

if ($aksi === 'rakit') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (empty($meta['total_ditulis'])) {
        if (!xlsx_tulis_baris($meta['dir'] . '/data.part', xlsx_baris_xml((int) $meta['baris'] + 2, array(
            array('s', 'TOTAL'), array('s', number_format($meta['selesai'], 0, ',', '.') . ' deposito'), array('s', ''), array('s', ''),
            array('s', ''), array('s', ''), array('n', $meta['tot_nominal']), array('s', ''), array('s', ''), array('s', ''), array('s', ''),
            array('n', $meta['tot_saldo']),
        ), 2))) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        $meta['total_ditulis'] = true;
        simpan_job($meta);
    }
    if (!xlsx_rakit($berkas, EXPORT_LAPORAN[$meta['laporan']][0], $judul, $lebar, $meta['dir'] . '/data.part')) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    $f = $meta['filter'];
    jawab_json(array('unduh' => 'tempo_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $meta['selesai'],
        'ringkasan_kolom' => array(array('nama', 'Keterangan'), array('nilai', 'Nilai')),
        'ringkasan' => array(
            array('nama' => 'Filter', 'nilai' => export_keterangan($meta['laporan'], $f)),
            array('nama' => 'Total nominal', 'nilai' => $meta['tot_nominal']),
            array('nama' => 'Total tabungan (escrow)', 'nilai' => $meta['tot_saldo']),
        )));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    $f = $meta['filter'];
    if ($meta['laporan'] !== 'tempo') {
        $nama = EXPORT_LAPORAN[$meta['laporan']][1] . '_' . date('Ymd_His') . '.xlsx';
    } else {
        $nama = 'jatuh_tempo_' . (tempo_keterangan_cari($f) !== '' ? 'cari' : preg_replace('/[^0-9-]/', '', $f['bulan'])) . '_' . date('Ymd_His') . '.xlsx';
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
