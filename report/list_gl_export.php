<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Export List Jurnal ke .xlsx secara bertahap (pengganti excel_gl.php, yang membuat tabel HTML bernama .xls).
 * Rentang tanggal dari sesi yang diisi list_gl.php (tgl_awal_pos / tgl_akhir_pos).
 *
 *   POST aksi=mulai              -> hitung jumlah transaksi, hasil {job, total}
 *   POST aksi=proses&job=...     -> tulis 1000 transaksi berikutnya
 *   POST aksi=rakit&job=...      -> baris TOTAL TRANSAKSI + rakit .xlsx, hasil {unduh}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Isi sama dengan versi lama: semua transaksi unit dengan tanggal_transaksi di antara kedua tanggal, urut tanggal lalu
 * nomor bukti (id_transaksi sebagai penentu urutan bila keduanya sama), kolom Tanggal, Nomor Bukti, Kode Rekening,
 * Keterangan, Jenis Transaksi, Debet, Kredit, dan baris TOTAL TRANSAKSI di akhir. Versi lama tidak memeriksa login.
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
require_once __DIR__ . '/../inc/xlsx_writer.php';

const LIST_GL_PER_TAHAP = 1000;

function jawab_json($data, $kode = 200)
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$aksi = isset($_REQUEST['aksi']) ? (string) $_REQUEST['aksi'] : '';
wajib_login($aksi === 'unduh' ? 'halaman' : 'json');
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
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis'] !== 'list_gl') {
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

$judul = array('Tanggal', 'Nomor Bukti', 'Kode Rekening', 'Keterangan', 'Jenis Transaksi', 'Debet', 'Kredit');
$lebar = array(20, 22, 14, 48, 16, 16, 16);

if ($aksi === 'mulai') {
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    $u = db_satu("select kode_branch from user where userid=?", 's', $userid);
    $unit = $u ? (string) $u['kode_branch'] : '';
    $t1 = isset($_SESSION['tgl_awal_pos']) ? (string) $_SESSION['tgl_awal_pos'] : '';
    $t2 = isset($_SESSION['tgl_akhir_pos']) ? (string) $_SESSION['tgl_akhir_pos'] : '';
    $n = db_satu("select count(*) as n from tabel_transaksi where tanggal_transaksi between ? and ? and unit =?", 'sss', $t1, $t2, $unit);
    $job = bin2hex(random_bytes(12));
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    if (!@mkdir($dir, 0700)) {
        jawab_json(array('pesan' => 'Folder sementara export tidak bisa dibuat.'), 500);
    }
    simpan_job(array('jenis' => 'list_gl', 'userid' => $userid, 'unit' => $unit, 't1' => $t1, 't2' => $t2,
        'total' => $n ? (int) $n['n'] : 0, 'selesai' => 0, 'baris' => 1, 'kunci' => null, 'dir' => $dir));
    jawab_json(array('job' => $job, 'total' => $n ? (int) $n['n'] : 0));
}

$meta = muat_job($folder_induk, $userid);
if (!$meta) {
    jawab_json(array('pesan' => 'Pekerjaan export tidak ditemukan atau sudah selesai. Silakan ulangi.'), 404);
}

if ($aksi === 'proses') {
    // urut tanggal, nomor bukti, id (keyset: lanjut sesudah baris terakhir tahap sebelumnya)
    $sql = "select id_transaksi, tanggal_transaksi, kode_transaksi, kode_rekening, keterangan_transaksi, jenis_transaksi, debet, kredit
        from tabel_transaksi where tanggal_transaksi between ? and ? and unit =?";
    $param = array($meta['t1'], $meta['t2'], $meta['unit']);
    if ($meta['kunci']) {
        list($k_tgl, $k_kode, $k_id) = $meta['kunci'];
        $sql .= " and (tanggal_transaksi > ? or (tanggal_transaksi = ? and (kode_transaksi > ? or (kode_transaksi = ? and id_transaksi > ?))))";
        array_push($param, $k_tgl, $k_tgl, $k_kode, $k_kode, (string) $k_id);
    }
    $sql .= " order by tanggal_transaksi, kode_transaksi, id_transaksi limit " . LIST_GL_PER_TAHAP;
    $data = db_semua($sql, str_repeat('s', count($param)), ...$param);
    $xml = '';
    $baris = (int) $meta['baris'];
    foreach ($data as $r) {
        $baris++;
        $xml .= xlsx_baris_xml($baris, array(
            array('s', $r['tanggal_transaksi']),
            array('s', $r['kode_transaksi']),
            array('s', $r['kode_rekening']),
            array('s', $r['keterangan_transaksi']),
            array('s', $r['jenis_transaksi']),
            array('n', $r['debet']),
            array('n', $r['kredit']),
        ));
        $meta['kunci'] = array((string) $r['tanggal_transaksi'], (string) $r['kode_transaksi'], (int) $r['id_transaksi']);
    }
    if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . '/data.part', $xml)) {
        jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
    }
    $meta['baris'] = $baris;
    $meta['selesai'] += count($data);
    simpan_job($meta);
    jawab_json(array('selesai_baris' => $meta['selesai'], 'total' => $meta['total'], 'habis' => count($data) < LIST_GL_PER_TAHAP));
}

if ($aksi === 'rakit') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (empty($meta['total_ditulis'])) {
        // baris TOTAL TRANSAKSI seperti versi lama (jumlah dari query terpisah, sama dengan versi lama)
        $tot = db_satu("select sum(debet) as tot_debet, sum(kredit) as tot_kredit from tabel_transaksi where tanggal_transaksi between ? and ? and unit =?",
            'sss', $meta['t1'], $meta['t2'], $meta['unit']);
        if (!xlsx_tulis_baris($meta['dir'] . '/data.part', xlsx_baris_xml((int) $meta['baris'] + 1, array(
            array('s', 'TOTAL TRANSAKSI'), array('s', ''), array('s', ''), array('s', ''), array('s', ''),
            array('n', $tot ? $tot['tot_debet'] : 0), array('n', $tot ? $tot['tot_kredit'] : 0),
        ), 2))) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        $meta['total_ditulis'] = true;
        simpan_job($meta);
    }
    if (!xlsx_rakit($berkas, 'List Jurnal', $judul, $lebar, $meta['dir'] . '/data.part')) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    jawab_json(array('unduh' => 'list_gl_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $meta['selesai']));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    $periode = preg_replace('/[^0-9]/', '', $meta['t1']) . '_' . preg_replace('/[^0-9]/', '', $meta['t2']);
    $nama = 'list_jurnal_' . preg_replace('/[^A-Za-z0-9_-]/', '', $meta['unit']) . '_' . $periode . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
