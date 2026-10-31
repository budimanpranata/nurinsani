<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Export kolektabilitas pembiayaan (PPAP) ke .xlsx secara bertahap (pengganti export_npl.php untuk tombol Download di ppap.php).
 * Empat sheet seperti versi lama: kolek1..kolek4 (loan unit, os > 0, gol 1..4); gol dihitung saat Cari di ppap.php.
 *
 *   POST aksi=mulai              -> hitung jumlah baris tiap kolek, hasil {job, total}
 *   POST aksi=proses&job=...     -> tulis 500 baris berikutnya, hasil {persen, berikut, habis, jumlah}
 *   POST aksi=rakit&job=...      -> baris TOTAL + rakit file .xlsx, hasil {unduh, ringkasan, ringkasan_kolom}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Isi kolom sama dengan export_npl.php versi lama: tunggakan = jumlah seluruh tunggakan CIF; tunggakan pokok/margin =
 * (tunggakan / angsuran) x pokok / ijaroh (versi lama ditulis sebagai teks, kini angka). Versi lama: if ($status ='CURRENT')
 * selalu benar, jadi yang diexport selalu data loan saat ini.
 * Percepatan: data diambil per 500 baris (urut CIF) dan tunggakan dijumlah sekali per 500 CIF
 * (versi lama: join tunggakan ke setiap loan dalam satu query besar per sheet).
 * Tambahan (permintaan user): baris TOTAL tiap sheet, ringkasan di jendela progres dengan neraca pembanding
 * Saldo Pinjaman <-> 1411000+1413000 (saldo_akhir apa adanya).
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
require_once __DIR__ . '/../inc/xlsx_writer.php';
include __DIR__ . '/../indo_tgl.php';

const PPAP_PER_TAHAP = 500;

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

function muat_job($folder_induk, $userid)
{
    $job = isset($_REQUEST['job']) ? (string) $_REQUEST['job'] : '';
    if (!preg_match('/^[a-f0-9]{24}$/', $job)) {
        return null;
    }
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    $meta = @json_decode((string) @file_get_contents($dir . '/meta.json'), true);
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis'] !== 'ppap') {
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
    'SALDO OS', 'SALDO MARGIN', 'TUNGGAKAN POKOK', 'TUNGGAKAN MARGIN', 'GOL', 'AO', 'NAMA AO', 'NAMA KELOMPOK');
$lebar = array(8, 16, 28, 12, 18, 8, 18, 14, 16, 14, 14, 16, 16, 6, 8, 22, 26);
$nama_kolek = array(1 => 'Lancar', 2 => 'Kurang Lancar', 3 => 'Diragukan', 4 => 'Macet');

if ($aksi === 'mulai') {
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    $u = db_satu("select kode_branch from user where userid=?", 's', $userid);
    $unit = $u ? (string) $u['kode_branch'] : '';
    $jumlah = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);
    foreach (db_semua("select gol, count(*) as n from loan where unit=? and os > 0 and gol in ('1','2','3','4') group by gol", 's', $unit) as $r) {
        $jumlah[(int) $r['gol']] = (int) $r['n'];
    }
    $job = bin2hex(random_bytes(12));
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    if (!@mkdir($dir, 0700)) {
        jawab_json(array('pesan' => 'Folder sementara export tidak bisa dibuat.'), 500);
    }
    simpan_job(array('jenis' => 'ppap', 'userid' => $userid, 'unit' => $unit, 'jumlah' => $jumlah, 'total' => array_sum($jumlah),
        'gol' => 1, 'cif_akhir' => '', 'baris' => 1, 'selesai' => 0, 'ringkasan' => array(), 'dir' => $dir));
    jawab_json(array('job' => $job, 'total' => array_sum($jumlah)));
}

$meta = muat_job($folder_induk, $userid);
if (!$meta) {
    jawab_json(array('pesan' => 'Pekerjaan export tidak ditemukan atau sudah selesai. Silakan ulangi.'), 404);
}

/** Teks langkah berikutnya untuk jendela progres. */
function berikut_ppap($meta, $nama_kolek)
{
    if ($meta['gol'] > 4) {
        return 'Membuat file Excel…';
    }
    return 'kolek' . $meta['gol'] . ' (' . $nama_kolek[$meta['gol']] . '): memproses '
        . number_format($meta['selesai'], 0, ',', '.') . ' dari ' . number_format($meta['total'], 0, ',', '.') . ' baris…';
}

if ($aksi === 'proses') {
    // lewati kolek yang kosong / sudah habis
    while ($meta['gol'] <= 4 && $meta['jumlah'][$meta['gol']] == 0) {
        $meta['gol']++;
    }
    $g = (int) $meta['gol'];
    if ($g <= 4) {
        $data = db_semua("select loan.no_anggota, loan.unit, Cust_Short_name, Plafond, loan.cif, os, saldo_margin, loan.code_kel,
                tenor, tgl_murab, maturity_date, loan.cao, pokok, ijaroh, angsuran, nama_kel, nama_ao, gol, status_app
            from loan
            left join kelompok on loan.code_kel=kelompok.code_kel
            left join ao on ao.cao = loan.cao
            where loan.unit =? and os > 0 and gol=? and loan.cif > ?
            group by loan.cif order by loan.cif limit " . PPAP_PER_TAHAP, 'sss', $meta['unit'], (string) $g, $meta['cif_akhir']);

        // jumlah seluruh tunggakan per CIF (versi lama: sum(KREDIT-DEBIT) dari left join tunggakan, semua tipe)
        $tunggak = array();
        if ($data) {
            $cif = array_map(function ($d) { return (string) $d['cif']; }, $data);
            $tanda = implode(',', array_fill(0, count($cif), '?'));
            foreach (db_semua("select CIF, sum(KREDIT-DEBIT) as n from tunggakan where CIF in ($tanda) group by CIF", str_repeat('s', count($cif)), ...$cif) as $t) {
                $tunggak[(string) $t['CIF']] = $t['n'];
            }
        }

        $rk = isset($meta['ringkasan'][$g]) ? $meta['ringkasan'][$g] : array(0, 0, 0, 0, 0, 0, 0, 1);
        $xml = '';
        $baris = (int) $meta['baris'];
        foreach ($data as $d) {
            $c = (string) $d['cif'];
            // semantik PHP 5: angsuran 0 / tanpa tunggakan -> pengali 0 (versi lama di PHP 8 berhenti karena pembagian nol)
            $angsuran = compat_num($d['angsuran']);
            $kali = $angsuran == 0 ? 0 : compat_num(isset($tunggak[$c]) ? $tunggak[$c] : null) / $angsuran;
            $tp = $kali * compat_num($d['pokok']);
            $tm = $kali * compat_num($d['ijaroh']);
            $os = compat_num($d['os']);
            $margin = compat_num($d['saldo_margin']);
            $baris++;
            $sel_baris = array(
                array('s', $d['unit']),
                array('s', $d['no_anggota']),
                array('s', $d['Cust_Short_name']),
                array('s', $d['code_kel']),
                array('s', tgl_indo((string) $d['tgl_murab'])),
                array('s', $d['tenor']),
                array('s', tgl_indo((string) $d['maturity_date'])),
                array('n', $d['Plafond']),
                array('n', $os),
                array('n', $os - $margin),
                array('n', $margin),
                array('n', $tp),
                array('n', $tm),
                array('s', $d['gol']),
                array('s', $d['cao']),
                array('s', $d['nama_ao']),
                array('s', $d['nama_kel']),
            );
            if ($g >= 3) {
                // kolek3/kolek4 punya kolom STATUS APP. Versi lama menulis status_app ke kolom NAMA KELOMPOK (menimpa nama_kel)
                // dan membiarkan STATUS APP kosong; kini masing-masing di kolomnya.
                $sel_baris[] = array('s', $d['status_app']);
            }
            $xml .= xlsx_baris_xml($baris, $sel_baris);
            $rk[0]++;
            $rk[1] += compat_num($d['Plafond']);
            $rk[2] += $os;
            $rk[3] += $os - $margin;
            $rk[4] += $margin;
            $rk[5] += $tp;
            $rk[6] += $tm;
            $meta['cif_akhir'] = $c;
        }
        if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . "/data$g.part", $xml)) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        $rk[7] = $baris;
        $meta['ringkasan'][$g] = $rk;
        $meta['baris'] = $baris;
        $meta['selesai'] += count($data);
        if (count($data) < PPAP_PER_TAHAP) {
            // kolek ini selesai -> lanjut kolek berikutnya
            $meta['gol']++;
            $meta['cif_akhir'] = '';
            $meta['baris'] = 1;
        }
    }
    while ($meta['gol'] <= 4 && $meta['jumlah'][$meta['gol']] == 0) {
        $meta['gol']++;
    }
    $habis = $meta['gol'] > 4;
    simpan_job($meta);
    jawab_json(array('persen' => $meta['total'] > 0 ? round($meta['selesai'] / $meta['total'] * 95, 1) : 95,
        'berikut' => berikut_ppap($meta, $nama_kolek), 'habis' => $habis, 'jumlah' => $meta['selesai']));
}

if ($aksi === 'rakit') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    $sheets = array();
    $ringkasan = array();
    $jml = array(0, 0, 0, 0, 0, 0, 0);
    for ($g = 1; $g <= 4; $g++) {
        $rk = isset($meta['ringkasan'][$g]) ? $meta['ringkasan'][$g] : array(0, 0, 0, 0, 0, 0, 0, 1);
        if (empty($meta['total_ditulis'])) {
            $sel = array_fill(0, count($judul), array('s', ''));
            $sel[0] = array('s', 'TOTAL');
            $sel[1] = array('s', number_format($rk[0], 0, ',', '.') . ' rekening');
            foreach (array(7 => 1, 8 => 2, 9 => 3, 10 => 4, 11 => 5, 12 => 6) as $kol => $i) {
                $sel[$kol] = array('n', $rk[$i]);
            }
            if (!xlsx_tulis_baris($meta['dir'] . "/data$g.part", xlsx_baris_xml($rk[7] + 2, $sel, 2))) {
                jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
            }
        }
        // sheet kolek3 dan kolek4 punya judul tambahan "STATUS APP" (sama dengan versi lama)
        $judul_sheet = $g >= 3 ? array_merge($judul, array('STATUS APP')) : $judul;
        $lebar_sheet = $g >= 3 ? array_merge($lebar, array(12)) : $lebar;
        $sheets[] = array('nama' => 'kolek' . $g, 'judul' => $judul_sheet, 'lebar' => $lebar_sheet, 'bagian' => $meta['dir'] . "/data$g.part");
        $ringkasan[] = array('nama' => 'kolek' . $g . ' ' . $nama_kolek[$g], 'rekening' => $rk[0], 'plafond' => $rk[1], 'os' => $rk[2],
            'saldo_os' => $rk[3], 'margin' => $rk[4], 'tunggak_pokok' => $rk[5], 'tunggak_margin' => $rk[6], 'neraca' => null, 'selisih' => null);
        for ($i = 0; $i < 7; $i++) {
            $jml[$i] += $rk[$i];
        }
    }
    $meta['total_ditulis'] = true;
    simpan_job($meta);
    if (!xlsx_rakit_banyak($berkas, $sheets)) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    // neraca pembanding untuk total semua kolek: saldo pinjaman <-> 1411000+1413000 (saldo_akhir apa adanya, sama dengan Home)
    $nr = db_satu("select sum(saldo_akhir) as n, count(*) as ada from tabel_master where unit=? and kode_rekening in ('1411000','1413000')", 's', $meta['unit']);
    $neraca = $nr && $nr['ada'] > 0 ? compat_num($nr['n']) : null;
    $ringkasan[] = array('nama' => 'TOTAL', 'coa' => '1411000+1413000', 'rekening' => $jml[0], 'plafond' => $jml[1], 'os' => $jml[2],
        'saldo_os' => $jml[3], 'margin' => $jml[4], 'tunggak_pokok' => $jml[5], 'tunggak_margin' => $jml[6],
        'neraca' => $neraca, 'selisih' => $neraca === null ? null : $jml[2] - $neraca);
    jawab_json(array('unduh' => 'ppap_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $jml[0],
        'ringkasan_kolom' => array(array('nama', 'Kolektabilitas'), array('rekening', 'Rekening'), array('plafond', 'Plafond'),
            array('os', 'Saldo Pinjaman'), array('saldo_os', 'Saldo OS'), array('margin', 'Saldo Margin'),
            array('tunggak_pokok', 'Tunggakan Pokok'), array('tunggak_margin', 'Tunggakan Margin'),
            array('neraca', 'Neraca OS (pembanding)'), array('selisih', 'Selisih')),
        'ringkasan' => $ringkasan));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    $nama = 'kolektabilitas_' . preg_replace('/[^A-Za-z0-9_-]/', '', $meta['unit']) . '_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
