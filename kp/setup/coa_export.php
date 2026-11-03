<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Export daftar COA (tabel coa urut kode_rek) ke .xlsx secara bertahap, dari tombol Export di kp/setup/perkiraan.php.
 * Pengganti tombol copy/csv/excel/pdf/print DataTables CDN versi lama (permintaan user 2026-11-03: export kp seperti
 * menu unit). Kolom sama dengan tabel di halaman: Kode Rekening, Nama Rekening, Posisi, Normal.
 *
 *   POST aksi=mulai              -> buat pekerjaan export, hasil {job, total}
 *   POST aksi=proses&job=...     -> tulis 500 rekening berikutnya, hasil {selesai_baris, total, habis}
 *   POST aksi=rakit&job=...      -> rakit file .xlsx, hasil {unduh: url}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 */
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/xlsx_writer.php';

const COA_PER_TAHAP = 500;

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

/** Folder dan data pekerjaan; hanya milik user yang login. */
function muat_job($folder_induk, $userid)
{
    $job = isset($_REQUEST['job']) ? (string) $_REQUEST['job'] : '';
    if (!preg_match('/^[a-f0-9]{24}$/', $job)) {
        return null;
    }
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    $meta = @json_decode((string) @file_get_contents($dir . '/meta.json'), true);
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis'] !== 'coa') {
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

$judul = array('Kode Rekening', 'Nama Rekening', 'Posisi', 'Normal');
$lebar = array(16, 48, 14, 10);

if ($aksi === 'mulai') {
    // bersihkan pekerjaan lama (lebih dari 6 jam)
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    $n = db_satu("select count(*) as n from coa");
    $job = bin2hex(random_bytes(12));
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    if (!@mkdir($dir, 0700)) {
        jawab_json(array('pesan' => 'Folder sementara export tidak bisa dibuat.'), 500);
    }
    $meta = array('jenis' => 'coa', 'userid' => $userid, 'total' => (int) ($n ? $n['n'] : 0), 'selesai' => 0, 'baris' => 1, 'dir' => $dir);
    simpan_job($meta);
    jawab_json(array('job' => $job, 'total' => $meta['total']));
}

$meta = muat_job($folder_induk, $userid);
if (!$meta) {
    jawab_json(array('pesan' => 'Pekerjaan export tidak ditemukan atau sudah selesai. Silakan ulangi.'), 404);
}

if ($aksi === 'proses') {
    // coa tidak punya kunci unik: urut kode_rek lalu urutan isi tabel, diambil per halaman
    $data = db_semua("select kode_rek,nama_rek,posisi,normal from coa order by kode_rek asc limit " . (int) $meta['selesai'] . ", " . COA_PER_TAHAP);
    $xml = '';
    $baris = (int) $meta['baris'];
    foreach ($data as $d) {
        $baris++;
        $xml .= xlsx_baris_xml($baris, array(
            array('s', $d['kode_rek']),
            array('s', $d['nama_rek']),
            array('s', $d['posisi']),
            array('s', $d['normal']),
        ));
    }
    if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . '/data.part', $xml)) {
        jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
    }
    $meta['baris'] = $baris;
    $meta['selesai'] += count($data);
    $habis = count($data) < COA_PER_TAHAP;
    simpan_job($meta);
    jawab_json(array('selesai_baris' => $meta['selesai'], 'total' => $meta['total'], 'habis' => $habis));
}

if ($aksi === 'rakit') {
    if (!is_file($meta['dir'] . '/data.part')) {
        xlsx_tulis_baris($meta['dir'] . '/data.part', '');
    }
    if (!xlsx_rakit($meta['dir'] . '/hasil.xlsx', 'COA', $judul, $lebar, $meta['dir'] . '/data.part')) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    jawab_json(array('unduh' => 'coa_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $meta['selesai']));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="coa_' . date('Ymd_His') . '.xlsx"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
