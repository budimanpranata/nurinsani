<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Export Rekap Nominative Simpanan ke .xlsx secara bertahap (pengganti export_simpana.php untuk tombol Rekap di krt.php).
 * Tiga sheet seperti versi lama: Simpanan (simpanan, TYPE selain 04/05), Simpaan Wajib, Simpaan Pokok.
 *
 *   POST aksi=mulai              -> buat pekerjaan export, hasil {job, berikut}
 *   POST aksi=proses&job=...     -> satu langkah: hitung saldo satu sheet, atau tulis 500 rekening berikutnya;
 *                                   hasil {persen, teks, berikut, habis, jumlah}
 *   POST aksi=rakit&job=...      -> rakit file .xlsx, hasil {unduh: url}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Periode (perbaikan 2026-10-31, perintah user; lihat PEKERJAAN_TERTUNDA.md bagian 26):
 *   CURRENT : saldo awal s.d. tanggal EOM terakhir (param_tgl EOM), mutasi sesudahnya s.d. hari ini.
 *   EOM X/Y : saldo awal s.d. akhir bulan sebelum X, mutasi selama bulan X, saldo akhir s.d. akhir bulan X.
 * Versi lama selalu memakai CURRENT ($status ='CURRENT'), dan sheet Wajib/Pokok membandingkan MONTH(tanggal) dengan
 * tanggal EOM sehingga mutasi selalu 0; kini ketiga sheet memakai date(BUSS_DATE).
 * Angka saldo/mutasi ditulis sebagai angka (versi lama: teks).
 * Percepatan: saldo tiap sheet dihitung dengan satu query tanpa join anggota (versi lama men-join anggota ke setiap
 * baris simpanan sebelum dijumlah; uji 300 ribu baris: 3,2 -> 1,6 detik); nama/alamat diambil per 500 CIF lewat primary key.
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
require_once __DIR__ . '/../inc/xlsx_writer.php';

const SIMPANAN_PER_TAHAP = 500;

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
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis'] !== 'simpanan') {
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

// sheet: tabel sumber dan filter tambahan (ketiganya kini membandingkan date(BUSS_DATE))
$sheet_def = array(
    // 'coa': rekening neraca pembanding (tabel_master), permintaan user 2026-10-31
    array('nama' => 'Simpanan', 'tabel' => 'simpanan', 'filter' => " and TYPE not in ('04','05')", 'tgl' => 'date(BUSS_DATE)', 'coa' => '2101000'),
    array('nama' => 'Simpaan Wajib', 'tabel' => 'simpanan_wajib', 'filter' => '', 'tgl' => 'date(BUSS_DATE)', 'coa' => '3202000'),
    array('nama' => 'Simpaan Pokok', 'tabel' => 'simpanan_pokok', 'filter' => '', 'tgl' => 'date(BUSS_DATE)', 'coa' => '3102000'),
);
$judul = array('UNIT', 'NO. REKENING', 'NAMA', 'ALAMAT', 'SALDO AWAL', 'MUTASI DEBET', 'MUTASI KREDIT', 'SALDO AKHIR');
$lebar = array(8, 34, 28, 34, 16, 16, 16, 16);

/** Persentase progres: tiap sheet mendapat bagian sama; hitung saldo = 60% bagian, tulis rekening = 40%. */
function persen_simpanan($meta, $jumlah_sheet)
{
    $bagian = 95 / $jumlah_sheet;
    $p = $meta['sheet'] * $bagian;
    if ($meta['sheet'] < $jumlah_sheet && $meta['dihitung']) {
        $n = max(1, $meta['jumlah']);
        $p += $bagian * (0.6 + 0.4 * min(1, $meta['posisi'] / $n));
    }
    return round($p, 1);
}

/** Teks langkah berikutnya (ditampilkan selama request berikutnya berjalan). */
function berikut_simpanan($meta, $sheet_def)
{
    if ($meta['sheet'] >= count($sheet_def)) {
        return 'Membuat file Excel…';
    }
    $nama = $sheet_def[$meta['sheet']]['nama'];
    if (!$meta['dihitung']) {
        return $nama . ': menghitung saldo (langkah ' . ($meta['sheet'] + 1) . ' dari ' . count($sheet_def) . ')…';
    }
    return $nama . ': menulis ' . number_format(min($meta['jumlah'], $meta['posisi'] + SIMPANAN_PER_TAHAP), 0, ',', '.')
        . ' dari ' . number_format($meta['jumlah'], 0, ',', '.') . ' rekening…';
}

if ($aksi === 'mulai') {
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    // unit, status, bulan, tahun dari sesi (diisi saat Cari di krt.php, sama dengan versi lama)
    $unit = isset($_SESSION['unt']) ? (string) $_SESSION['unt'] : '';
    $status = isset($_SESSION['status']) ? (string) $_SESSION['status'] : '';
    if ($status === 'EOM') {
        $bulan = isset($_SESSION['bulan']) ? (int) $_SESSION['bulan'] : 0;
        $tahun = isset($_SESSION['tahun']) ? (int) $_SESSION['tahun'] : 0;
        if ($bulan < 1 || $bulan > 12 || $tahun < 2000) {
            jawab_json(array('pesan' => 'Bulan dan tahun EOM belum dipilih. Pilih lalu klik Cari Report lagi.'), 400);
        }
        $awal_bulan = sprintf('%04d-%02d-01', $tahun, $bulan);
        $dat = date('Y-m-d', strtotime($awal_bulan . ' -1 day'));   // akhir bulan sebelumnya
        $sampai = date('Y-m-t', strtotime($awal_bulan));              // akhir bulan terpilih
        $periode = sprintf('EOM_%04d%02d', $tahun, $bulan);
    } else {
        // CURRENT: batas = tanggal EOM terakhir di param_tgl (bawaan versi lama 2018-04-30), tanpa batas akhir
        $dat = '2018-04-30';
        foreach (db_semua("select param_tgl as ddata from param_tgl  where nama_param_tgl ='EOM'") as $d) {
            $dat = (string) $d['ddata'];
        }
        $sampai = null;
        $periode = 'CURRENT';
    }
    $job = bin2hex(random_bytes(12));
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    if (!@mkdir($dir, 0700)) {
        jawab_json(array('pesan' => 'Folder sementara export tidak bisa dibuat.'), 500);
    }
    $meta = array('jenis' => 'simpanan', 'userid' => $userid, 'unit' => $unit, 'dat' => $dat, 'sampai' => $sampai, 'periode' => $periode,
        'sheet' => 0, 'dihitung' => false, 'jumlah' => 0, 'posisi' => 0, 'baris' => 1, 'total' => 0, 'dir' => $dir);
    simpan_job($meta);
    jawab_json(array('job' => $job, 'berikut' => berikut_simpanan($meta, $sheet_def)));
}

$meta = muat_job($folder_induk, $userid);
if (!$meta) {
    jawab_json(array('pesan' => 'Pekerjaan export tidak ditemukan atau sudah selesai. Silakan ulangi.'), 404);
}

if ($aksi === 'proses') {
    $i = $meta['sheet'];
    $teks = '';
    if ($i < count($sheet_def)) {
        $s = $sheet_def[$i];
        if (!$meta['dihitung']) {
            // langkah berat: satu query penjumlahan untuk seluruh sheet (rumus versi lama, tanpa join anggota)
            $dat = $meta['dat'];
            $tgl = $s['tgl'];
            $param = array($dat, $dat, $dat, $dat, $meta['unit']);
            $batas = '';
            if (!empty($meta['sampai'])) {
                // EOM: transaksi sesudah akhir bulan terpilih tidak dihitung
                $batas = ' and BUSS_DATE < ?';
                $param[] = date('Y-m-d', strtotime($meta['sampai'] . ' +1 day'));
            }
            $hasil = array();
            foreach (db_semua("SELECT CIF,
                    SUM(if($tgl<=?,KREDIT,0))-SUM(if($tgl<=?,DEBIT,0)) AS saldo_awal,
                    SUM(if($tgl>?,KREDIT,0)) AS mutasi_kredit,
                    SUM(if($tgl>?,DEBIT,0)) AS mutasi_debet
                FROM {$s['tabel']} where UNIT=?{$s['filter']}$batas group by CIF order by CIF",
                str_repeat('s', count($param)), ...$param) as $r) {
                $hasil[] = array((string) $r['CIF'], compat_num($r['saldo_awal']), compat_num($r['mutasi_debet']), compat_num($r['mutasi_kredit']));
            }
            file_put_contents($meta['dir'] . "/saldo$i.json", json_encode($hasil));
            $meta['dihitung'] = true;
            $meta['jumlah'] = count($hasil);
            $meta['posisi'] = 0;
            $meta['baris'] = 1;
            $teks = $s['nama'] . ': saldo ' . number_format(count($hasil), 0, ',', '.') . ' rekening dihitung';
        } else {
            $hasil = json_decode((string) file_get_contents($meta['dir'] . "/saldo$i.json"), true);
            $potong = array_slice($hasil, $meta['posisi'], SIMPANAN_PER_TAHAP);
            $anggota = array();
            if ($potong) {
                $cif = array_map(function ($h) { return $h[0]; }, $potong);
                $tanda = implode(',', array_fill(0, count($cif), '?'));
                foreach (db_semua("select CIF, CUST_SHORT_NAME, HADDRESS1 from anggota where CIF in ($tanda)", str_repeat('s', count($cif)), ...$cif) as $a) {
                    $anggota[(string) $a['CIF']] = $a;
                }
            }
            $xml = '';
            $baris = (int) $meta['baris'];
            // total nominatif sheet ini: rekening, saldo awal, mutasi debet, mutasi kredit, saldo akhir, baris terakhir
            $rk = isset($meta['ringkasan'][$i]) ? $meta['ringkasan'][$i] : array(0, 0, 0, 0, 0, 1);
            foreach ($potong as $h) {
                list($c, $awal, $debet, $kredit) = $h;
                $rk[0]++;
                $rk[1] += $awal;
                $rk[2] += $debet;
                $rk[3] += $kredit;
                $rk[4] += $awal + $kredit - $debet;
                $a = isset($anggota[$c]) ? $anggota[$c] : null;
                $nama = $a ? $a['CUST_SHORT_NAME'] : null;
                $baris++;
                $xml .= xlsx_baris_xml($baris, array(
                    array('s', $meta['unit']),
                    // CONCAT(UNIT,'-',CIF,'-',CUST_SHORT_NAME): kosong bila anggota tidak ditemukan (CONCAT dengan NULL)
                    array('s', $nama === null ? '' : $meta['unit'] . '-' . $c . '-' . $nama),
                    array('s', $nama),
                    array('s', $a ? $a['HADDRESS1'] : ''),
                    array('n', $awal),
                    array('n', $debet),
                    array('n', $kredit),
                    array('n', $awal + $kredit - $debet),
                ));
            }
            if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . "/data$i.part", $xml)) {
                jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
            }
            $meta['baris'] = $baris;
            $rk[5] = $baris;
            $meta['ringkasan'][$i] = $rk;
            $meta['posisi'] += count($potong);
            $meta['total'] += count($potong);
            $teks = $s['nama'] . ': ' . number_format($meta['posisi'], 0, ',', '.') . ' dari ' . number_format($meta['jumlah'], 0, ',', '.') . ' rekening ditulis';
        }
        // sheet selesai -> lanjut sheet berikutnya
        if ($meta['dihitung'] && $meta['posisi'] >= $meta['jumlah']) {
            @unlink($meta['dir'] . "/saldo$i.json");
            $meta['sheet']++;
            $meta['dihitung'] = false;
            $meta['jumlah'] = 0;
            $meta['posisi'] = 0;
        }
    }
    $habis = $meta['sheet'] >= count($sheet_def);
    simpan_job($meta);
    jawab_json(array('persen' => persen_simpanan($meta, count($sheet_def)), 'teks' => $teks,
        'berikut' => berikut_simpanan($meta, $sheet_def), 'habis' => $habis, 'jumlah' => $meta['total']));
}

if ($aksi === 'rakit') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    $sheets = array();
    $ringkasan = array();
    // neraca pembanding: hanya posisi saat ini (tabel_master), jadi hanya untuk CURRENT
    $neraca = array();
    if (empty($meta['sampai'])) {
        foreach (db_semua("select kode_rekening, saldo_akhir from tabel_master where unit=? and kode_rekening in ('2101000','3202000','3102000')", 's', $meta['unit']) as $n) {
            $neraca[(string) $n['kode_rekening']] = compat_num($n['saldo_akhir']);
        }
    }
    foreach ($sheet_def as $i => $s) {
        $rk = isset($meta['ringkasan'][$i]) ? $meta['ringkasan'][$i] : array(0, 0, 0, 0, 0, 1);
        // baris TOTAL di bawah data, dipisah satu baris kosong agar tidak ikut terurut/terfilter (sekali saja)
        if (empty($meta['total_ditulis']) && !xlsx_tulis_baris($meta['dir'] . "/data$i.part", xlsx_baris_xml($rk[5] + 2, array(
            array('s', 'TOTAL'),
            array('s', number_format($rk[0], 0, ',', '.') . ' rekening'),
            array('s', ''),
            array('s', ''),
            array('n', $rk[1]),
            array('n', $rk[2]),
            array('n', $rk[3]),
            array('n', $rk[4]),
        ), 2))) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        $sheets[] = array('nama' => $s['nama'], 'judul' => $judul, 'lebar' => $lebar, 'bagian' => $meta['dir'] . "/data$i.part");
        $nilai_neraca = isset($neraca[$s['coa']]) ? $neraca[$s['coa']] : null;
        $ringkasan[] = array('nama' => $s['nama'], 'rekening' => $rk[0], 'saldo_awal' => $rk[1], 'mutasi_debet' => $rk[2],
            'mutasi_kredit' => $rk[3], 'saldo_akhir' => $rk[4], 'coa' => $s['coa'],
            'neraca' => $nilai_neraca, 'selisih' => $nilai_neraca === null ? null : $rk[4] - $nilai_neraca);
    }
    $meta['total_ditulis'] = true;
    simpan_job($meta);
    if (!xlsx_rakit_banyak($berkas, $sheets)) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    jawab_json(array('unduh' => 'simpanan_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $meta['total'],
        'ringkasan' => $ringkasan));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    $nama = 'rekap_nominative_simpanan_' . preg_replace('/[^A-Za-z0-9_-]/', '', $meta['unit']) . '_'
        . preg_replace('/[^A-Za-z0-9_-]/', '', isset($meta['periode']) ? $meta['periode'] : '') . '_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
