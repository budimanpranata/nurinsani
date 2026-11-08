<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Export .xlsx bertahap Laporan Bagi Hasil dan Eq Rate (fund/view/eq_rate.php, periode di sesi eqrate_filter).
 *
 *   POST aksi=mulai              -> hitung jumlah deposito, hasil {job, total}
 *   POST aksi=proses&job=...     -> tulis 500 deposito berikutnya
 *   POST aksi=rakit&job=...      -> baris TOTAL + rakit .xlsx (sheet "Baghas Eq Rate" + "Info"), hasil {unduh, ringkasan}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Kolom sama dengan laporan lama (baghas_eqrate.php, header dua tingkat dijadikan satu baris) + Status Jadwal
 * (Belum / REALISASI / BAYAR, keputusan user 2026-11-04). Satu baris per deposito (jadwal pertama dalam periode);
 * hitungan inc/eqrate_deposito.php dengan jumlah hari dari hari_dep. Judul, periode dan jumlah hari di sheet Info.
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/xlsx_writer.php';
require_once __DIR__ . '/../../inc/eqrate_deposito.php';

const EQRATE_PER_TAHAP = 500;

function jawab_json($data, $kode = 200)
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$aksi = isset($_REQUEST['aksi']) ? (string) $_REQUEST['aksi'] : '';
wajib_login($aksi === 'unduh' ? 'halaman' : 'json');
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "admin") {
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
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis'] !== 'eqrate_fund') {
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

$judul = array('Norek', 'Cif', 'Nama', 'No. Bilyet', 'Nominal', 'Tenor', 'Tanggal Kontrak', 'Jatuh tempo', 'Tgl Bayar', 'Status',
    'Nisbah Nasabah', 'Nisbah Koperasi', 'Bagi Hasil Nisbah', 'Gross Nisbah', 'PPh4 Ayat 2', 'Jml. Bayar', 'Eq Rate', 'Nisbah',
    'Rate', 'Gross Rate', 'PPh4 Ayat 2 Rate', 'Jml. Bayar Rate', 'Status Jadwal');
$lebar = array(18, 12, 30, 12, 16, 8, 20, 20, 20, 12, 10, 10, 16, 16, 14, 16, 8, 8, 16, 16, 14, 16, 14);
// kunci hasil eqrate_hitung yang dijumlahkan di baris TOTAL (kolom 13-16 dan 19-22)
const EQRATE_JUMLAH = array('nisbah', 'nisbah_gross', 'nisbah_pph4', 'nisbah_bayar', 'rate', 'rate_gross', 'rate_pph4', 'rate_bayar');

if ($aksi === 'mulai') {
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    $f = isset($_SESSION['eqrate_filter']) ? $_SESSION['eqrate_filter'] : null;
    if (!$f) {
        jawab_json(array('pesan' => 'Pilih periode lalu tekan Search terlebih dahulu.'), 400);
    }
    $total = count(eqrate_daftar($f['awal'], $f['akhir']));
    $meta = array('jenis' => 'eqrate_fund', 'userid' => $userid, 'filter' => $f, 'total' => $total, 'baris' => 1, 'selesai' => 0,
        'hari' => bhd_hari(), 'tot_nominal' => 0, 'tot' => array_fill_keys(EQRATE_JUMLAH, 0));
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
    $data = array_slice(eqrate_daftar($meta['filter']['awal'], $meta['filter']['akhir']), (int) $meta['selesai'], EQRATE_PER_TAHAP);
    $xml = '';
    $baris = (int) $meta['baris'];
    foreach ($data as $r) {
        $baris++;
        $h = eqrate_hitung($r);
        $nominal = compat_num($r['nominal']);
        $rasio = compat_num($r['rasio']);
        $xml .= xlsx_baris_xml($baris, array(
            array('s', $r['norek']),
            array('s', $r['cif']),
            array('s', $r['CUST_SHORT_NAME']),
            array('s', $r['bilyet']),
            array('n', $nominal),
            array('s', $r['tenor']),
            array('s', $r['star_date']),
            array('s', $r['maturity_date']),
            array('s', $r['tgl_jatuh_tempo']),
            array('s', $r['aro']),
            array('n', $rasio),
            array('n', 100 - $rasio),
            array('n', $h['nisbah']),
            array('n', $h['nisbah_gross']),
            array('n', $h['nisbah_pph4']),
            array('n', $h['nisbah_bayar']),
            array('s', $r['eq_rate']),
            array('s', $h['rasio_eks']),
            array('n', $h['rate']),
            array('n', $h['rate_gross']),
            array('n', $h['rate_pph4']),
            array('n', $h['rate_bayar']),
            array('s', $r['status_jadwal']),
        ));
        $meta['tot_nominal'] += $nominal;
        foreach (EQRATE_JUMLAH as $k) {
            $meta['tot'][$k] += $h[$k];
        }
    }
    if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . '/data.part', $xml)) {
        jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
    }
    $meta['baris'] = $baris;
    $meta['selesai'] += count($data);
    $habis = count($data) < EQRATE_PER_TAHAP;
    simpan_job($meta);
    jawab_json(array('selesai_baris' => $meta['selesai'], 'total' => $meta['total'], 'habis' => $habis));
}

if ($aksi === 'rakit') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    $f = $meta['filter'];
    list($hari, $dari_hari_dep) = $meta['hari'];
    $t = $meta['tot'];
    if (empty($meta['total_ditulis'])) {
        $kosong = array('s', '');
        if (!xlsx_tulis_baris($meta['dir'] . '/data.part', xlsx_baris_xml((int) $meta['baris'] + 2, array(
            array('s', 'TOTAL'), array('s', number_format($meta['selesai'], 0, ',', '.') . ' deposito'), $kosong, $kosong,
            array('n', $meta['tot_nominal']), $kosong, $kosong, $kosong, $kosong, $kosong, $kosong, $kosong,
            array('n', $t['nisbah']), array('n', $t['nisbah_gross']), array('n', $t['nisbah_pph4']), array('n', $t['nisbah_bayar']),
            $kosong, $kosong,
            array('n', $t['rate']), array('n', $t['rate_gross']), array('n', $t['rate_pph4']), array('n', $t['rate_bayar']), $kosong,
        ), 2))) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        $info = '';
        $isi = array(
            array('KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH', ''),
            array('LAPORAN BAGIHASIL DAN EQ RATE', ''),
            array('Periode', $f['awal'] . ' s.d ' . $f['akhir']),
            array('Jumlah hari bagi hasil', $hari . ' hari' . ($dari_hari_dep ? ' (hari_dep)' : ' (hari_dep belum diisi, cadangan)')),
            array('Jumlah deposito', (string) $meta['selesai']),
            array('Dibuat', date('Y-m-d H:i:s') . ' oleh ' . $userid),
        );
        foreach ($isi as $i => $baris_info) {
            $info .= xlsx_baris_xml($i + 2, array(array('s', $baris_info[0]), array('s', $baris_info[1])));
        }
        if (!xlsx_tulis_baris($meta['dir'] . '/info.part', $info)) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        $meta['total_ditulis'] = true;
        simpan_job($meta);
    }
    if (!xlsx_rakit_banyak($berkas, array(
        array('nama' => 'Baghas Eq Rate', 'judul' => $judul, 'lebar' => $lebar, 'bagian' => $meta['dir'] . '/data.part'),
        array('nama' => 'Info', 'judul' => array('Keterangan', 'Nilai'), 'lebar' => array(42, 50), 'bagian' => $meta['dir'] . '/info.part'),
    ))) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    jawab_json(array('unduh' => 'eqrate_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $meta['selesai'],
        'ringkasan_kolom' => array(array('nama', 'Keterangan'), array('nilai', 'Nilai')),
        'ringkasan' => array(
            array('nama' => 'Periode', 'nilai' => $f['awal'] . ' s.d ' . $f['akhir']),
            array('nama' => 'Jumlah hari', 'nilai' => $hari),
            array('nama' => 'Total nominal', 'nilai' => $meta['tot_nominal']),
            array('nama' => 'Total jml. bayar nisbah', 'nilai' => $t['nisbah_bayar']),
            array('nama' => 'Total jml. bayar rate', 'nilai' => $t['rate_bayar']),
        )));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    $f = $meta['filter'];
    $nama = 'baghas_eqrate_' . str_replace('-', '', $f['awal']) . '_' . str_replace('-', '', $f['akhir']) . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
