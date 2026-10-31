<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Export Rekap Nominative Pembiayaan ke .xlsx secara bertahap (pengganti export_nomi.php untuk tombol Rekap di Kr.php).
 *
 *   POST aksi=mulai              -> buat pekerjaan export, hasil {job, total}
 *   POST aksi=proses&job=...     -> tulis 500 baris berikutnya, hasil {selesai_baris, total, habis}
 *   POST aksi=rakit&job=...      -> rakit file .xlsx, hasil {unduh: url}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Isi kolom sama dengan export_nomi.php versi lama (termasuk kolom yang memang kosong di versi lama).
 * Tambahan 2026-10-31 (permintaan user): baris TOTAL di file, dan ringkasan total nominatif dengan neraca pembanding
 * (OS <-> 1411000+1413000, margin <-> 1423000) di jendela progres.
 * Percepatan: tunggakan pokok/margin diambil sekali per 500 anggota (versi lama: 2 query per anggota).
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
require_once __DIR__ . '/../inc/xlsx_writer.php';
include __DIR__ . '/../indo_tgl.php';

const NOMINATIF_PER_TAHAP = 500;

function jawab_json($data, $kode = 200)
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$aksi = isset($_REQUEST['aksi']) ? (string) $_REQUEST['aksi'] : '';
wajib_login($aksi === 'unduh' ? 'halaman' : 'json');
if ($_SESSION['level'] != "unit") {
    jawab_json(array('pesan' => 'Anda bukan user'), 403);
}
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
    if (!$meta || $meta['userid'] !== $userid) {
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

$judul = array('UNIT', 'NO. REKENING', 'NAMA', 'KELOMPOK', 'TANGGAL PK', 'TENOR', 'JATUH TEMPO', 'PLAFOND', 'SALDO PINJAMAN',
    'SALDO OS', 'SALDO MARGIN', 'KE', 'TUNGGAKAN POKOK', 'TUNGGAKAN MARGIN', 'GOL', 'AO', 'NAMA AO', 'NAMA KELOMPOK', 'CIF',
    'ALAMAT', 'BUDANG USAHA', 'NO KTP', 'STATUS', 'SUMBER DANA', 'TWM', 'Saldo TWM', 'Sisa TWM');
$lebar = array(8, 16, 28, 12, 18, 8, 18, 14, 16, 14, 14, 6, 16, 16, 8, 8, 22, 26, 10, 10, 22, 10, 12, 12, 8, 10, 10);

if ($aksi === 'mulai') {
    // bersihkan pekerjaan lama (lebih dari 6 jam)
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    $u = db_satu("select kode_branch from user where userid=?", 's', $userid);
    $unit = $u ? (string) $u['kode_branch'] : '';
    // versi lama: if ($status ='CURRENT') selalu benar, jadi yang diexport selalu data loan saat ini
    $n = db_satu("select count(*) as n from loan where unit=? and os > 0", 's', $unit);
    $job = bin2hex(random_bytes(12));
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    if (!@mkdir($dir, 0700)) {
        jawab_json(array('pesan' => 'Folder sementara export tidak bisa dibuat.'), 500);
    }
    $meta = array('userid' => $userid, 'unit' => $unit, 'total' => (int) ($n ? $n['n'] : 0), 'selesai' => 0,
        'cif_akhir' => '', 'baris' => 1, 'dir' => $dir);
    simpan_job($meta);
    jawab_json(array('job' => $job, 'total' => $meta['total']));
}

$meta = muat_job($folder_induk, $userid);
if (!$meta) {
    jawab_json(array('pesan' => 'Pekerjaan export tidak ditemukan atau sudah selesai. Silakan ulangi.'), 404);
}

if ($aksi === 'proses') {
    $data = db_semua("select loan.no_anggota, loan.unit, loan.Cust_Short_name, Plafond, loan.cif, os, saldo_margin, loan.code_kel,
            tenor, run_tenor, tgl_akad as tgl_murab, maturity_date, loan.cao, angsuran,
            bidang_usaha.nama_usaha as usaha, status_app, nama_kel, nama_ao, gol
        from loan
        left join kelompok on loan.code_kel=kelompok.code_kel
        left join ao on ao.cao = loan.cao
        left join bidang_usaha on bidang_usaha.code_bidang = loan.usaha
        where loan.unit =? and os > 0 and loan.cif > ?
        group by loan.cif order by loan.cif limit " . NOMINATIF_PER_TAHAP, 'ss', $meta['unit'], $meta['cif_akhir']);

    // tunggakan pokok (01) dan margin (02) untuk semua anggota tahap ini dalam satu query
    $tunggak = array();
    if ($data) {
        $cif = array_map(function ($d) { return (string) $d['cif']; }, $data);
        $tanda = implode(',', array_fill(0, count($cif), '?'));
        foreach (db_semua("select cif, type, sum(KREDIT-DEBIT) as n from tunggakan where type in ('01','02') and cif in ($tanda) group by cif, type",
            str_repeat('s', count($cif)), ...$cif) as $t) {
            $tunggak[(string) $t['cif']][(string) $t['type']] = $t['n'];
        }
    }

    $xml = '';
    $baris = (int) $meta['baris'];
    // total nominatif: rekening, plafond, saldo pinjaman (os), saldo os (pokok), saldo margin, tunggakan pokok, tunggakan margin
    $rk = isset($meta['ringkasan']) ? $meta['ringkasan'] : array(0, 0, 0, 0, 0, 0, 0);
    foreach ($data as $d) {
        $baris++;
        $c = (string) $d['cif'];
        $tp = isset($tunggak[$c]['01']) ? compat_num($tunggak[$c]['01']) : 0;
        $tm = isset($tunggak[$c]['02']) ? compat_num($tunggak[$c]['02']) : 0;
        $rk[0]++;
        $rk[1] += compat_num($d['Plafond']);
        $rk[2] += compat_num($d['os']);
        $rk[3] += compat_num($d['os']) - compat_num($d['saldo_margin']);
        $rk[4] += compat_num($d['saldo_margin']);
        $rk[5] += $tp;
        $rk[6] += $tm;
        $xml .= xlsx_baris_xml($baris, array(
            array('s', $d['unit']),
            array('s', $d['no_anggota']),
            array('s', $d['Cust_Short_name']),
            array('s', $d['code_kel']),
            array('s', tgl_indo((string) $d['tgl_murab'])),
            array('s', $d['tenor']),
            array('s', tgl_indo((string) $d['maturity_date'])),
            array('n', $d['Plafond']),
            array('n', $d['os']),
            array('n', compat_num($d['os']) - compat_num($d['saldo_margin'])),
            array('n', $d['saldo_margin']),
            array('n', $d['run_tenor']),
            array('n', isset($tunggak[$c]['01']) ? $tunggak[$c]['01'] : 0),
            array('n', isset($tunggak[$c]['02']) ? $tunggak[$c]['02'] : 0),
            array('s', $d['gol']),
            array('s', $d['cao']),
            array('s', $d['nama_ao']),
            array('s', $d['nama_kel']),
            array('s', $c),
            array('s', ''),               // ALAMAT: kosong di versi lama
            array('s', $d['usaha']),
            array('s', ''),               // NO KTP: kosong di versi lama
            array('s', $d['status_app']),
            array('s', ''),               // SUMBER DANA: versi lama membaca kunci 'deal_type' yang tidak ada, jadi kosong
            array('s', ''),
            array('s', ''),
            array('s', ''),
        ));
        $meta['cif_akhir'] = $c;
    }
    if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . '/data.part', $xml)) {
        jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
    }
    $meta['baris'] = $baris;
    $meta['ringkasan'] = $rk;
    $meta['selesai'] += count($data);
    $habis = count($data) < NOMINATIF_PER_TAHAP;
    simpan_job($meta);
    jawab_json(array('selesai_baris' => $meta['selesai'], 'total' => $meta['total'], 'habis' => $habis));
}

if ($aksi === 'rakit') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    $rk = isset($meta['ringkasan']) ? $meta['ringkasan'] : array(0, 0, 0, 0, 0, 0, 0);
    // baris TOTAL di bawah data, dipisah satu baris kosong agar tidak ikut terurut/terfilter (sekali saja)
    if (empty($meta['total_ditulis'])) {
        $sel = array_fill(0, count($judul), array('s', ''));
        $sel[0] = array('s', 'TOTAL');
        $sel[1] = array('s', number_format($rk[0], 0, ',', '.') . ' rekening');
        $sel[7] = array('n', $rk[1]);
        $sel[8] = array('n', $rk[2]);
        $sel[9] = array('n', $rk[3]);
        $sel[10] = array('n', $rk[4]);
        $sel[12] = array('n', $rk[5]);
        $sel[13] = array('n', $rk[6]);
        if (!xlsx_tulis_baris($meta['dir'] . '/data.part', xlsx_baris_xml((int) $meta['baris'] + 2, $sel, 2))) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        $meta['total_ditulis'] = true;
        simpan_job($meta);
    }
    if (!xlsx_rakit($berkas, 'Nominative', $judul, $lebar, $meta['dir'] . '/data.part')) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    // pembanding neraca (permintaan user 2026-10-31), pemetaan sama dengan Home:
    //   saldo pinjaman (os) <-> 1411000 + 1413000 (piutang murabahah, normal debet)
    //   saldo margin       <-> 1423000
    // nilai neraca = saldo_akhir tabel_master apa adanya (perintah user); selisih = nominatif - saldo_akhir
    $neraca = array();
    foreach (db_semua("select kode_rekening, saldo_akhir from tabel_master where unit=? and kode_rekening in ('1411000','1413000','1423000')", 's', $meta['unit']) as $n) {
        $neraca[(string) $n['kode_rekening']] = compat_num($n['saldo_akhir']);
    }
    $neraca_os = isset($neraca['1411000']) || isset($neraca['1413000'])
        ? (isset($neraca['1411000']) ? $neraca['1411000'] : 0) + (isset($neraca['1413000']) ? $neraca['1413000'] : 0) : null;
    $neraca_margin = isset($neraca['1423000']) ? $neraca['1423000'] : null;
    $baris_rk = function ($nama, $coa, $nominatif, $nilai_neraca) {
        return array('nama' => $nama, 'coa' => $coa, 'nominatif' => $nominatif, 'neraca' => $nilai_neraca,
            'selisih' => $nilai_neraca === null ? null : $nominatif - $nilai_neraca);
    };
    jawab_json(array('unduh' => 'nominatif_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $rk[0],
        'ringkasan_kolom' => array(array('nama', 'Keterangan'), array('nominatif', 'Nominatif'), array('neraca', 'Neraca (pembanding)'), array('selisih', 'Selisih')),
        'ringkasan' => array(
            $baris_rk('Saldo Pinjaman (OS)', '1411000+1413000', $rk[2], $neraca_os),
            $baris_rk('Saldo Margin', '1423000', $rk[4], $neraca_margin),
            array('nama' => 'Saldo OS (pokok)', 'nominatif' => $rk[3], 'neraca' => null, 'selisih' => null),
            array('nama' => 'Plafond', 'nominatif' => $rk[1], 'neraca' => null, 'selisih' => null),
            array('nama' => 'Tunggakan Pokok', 'nominatif' => $rk[5], 'neraca' => null, 'selisih' => null),
            array('nama' => 'Tunggakan Margin', 'nominatif' => $rk[6], 'neraca' => null, 'selisih' => null),
            array('nama' => 'Jumlah Rekening', 'nominatif' => $rk[0], 'neraca' => null, 'selisih' => null),
        )));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    $nama = 'rekap_nominative_' . preg_replace('/[^A-Za-z0-9_-]/', '', $meta['unit']) . '_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
