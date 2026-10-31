<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Export Buku Besar ke .xlsx secara bertahap (pengganti excel_buku_besar.php, yang membuat tabel HTML bernama .xls).
 * Pilihan diambil dari sesi yang diisi buku_besar.php: noreks, bulans, tahuns, js (eom/current), checkboxs (All data).
 *
 *   POST aksi=mulai              -> siapkan saldo awal dan hitung jumlah transaksi, hasil {job, total}
 *   POST aksi=proses&job=...     -> tulis 1000 transaksi berikutnya (urut id) dengan saldo berjalan
 *   POST aksi=rakit&job=...      -> baris TOTAL + sheet Info + rakit .xlsx, hasil {unduh, ringkasan}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Logika sama dengan versi lama (lihat PEKERJAAN_TERTUNDA.md bagian 28 untuk keanehannya):
 *   - current : saldo dari tabel_master; eom : saldo awal dari tabel_master_eoy Desember tahun sebelumnya, saldo akhir dari
 *     tabel_master_konsol bulan dan tahun terpilih (perbaikan 2026-10-31; versi lama memakai 2025/2026 tetap)
 *   - saldo awal saldo berjalan: All data -> saldo awal "min"; tanpa All data -> saldo awal tabel_master (current) / 0 (eom)
 *   - transaksi: bulan 1 s.d. bulan terpilih, kecuali rekening rugi-laba tanpa All data: hanya bulan terpilih
 *   - saldo berjalan: normal debet = saldo + debet - kredit; normal kredit = saldo - debet + kredit
 *   - urutan transaksi = id_transaksi (versi lama tanpa ORDER BY, mengikuti index rekening)
 * Tambahan (permintaan user): baris TOTAL dan ringkasan dengan neraca pembanding (saldo_akhir tabel_master apa adanya).
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
require_once __DIR__ . '/../inc/xlsx_writer.php';

const BUKU_BESAR_PER_TAHAP = 1000;

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
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis'] !== 'buku_besar') {
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

/** Klausa WHERE transaksi (rekening, unit, rentang bulan, tahun) dan parameternya. */
function filter_transaksi($meta)
{
    $sql = "kode_rekening=? and unit =? and " . ($meta['bulan_saja'] ? "month(tanggal_transaksi)=?" : "month(tanggal_transaksi) between '1' and ?")
        . " and year(tanggal_transaksi)=?";
    return array($sql, array($meta['norek'], $meta['unit'], $meta['bulan'], $meta['tahun']));
}

$judul = array('Tanggal', 'Nomor Bukti', 'Kode Rekening', 'Keterangan', 'Debet', 'Kredit', 'Saldo');
$lebar = array(20, 22, 14, 48, 16, 16, 18);

if ($aksi === 'mulai') {
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    $norek = isset($_SESSION['noreks']) ? (string) $_SESSION['noreks'] : '';
    $bulan = isset($_SESSION['bulans']) ? (string) $_SESSION['bulans'] : '';
    $tahun = isset($_SESSION['tahuns']) ? (string) $_SESSION['tahuns'] : '';
    $report = isset($_SESSION['js']) ? (string) $_SESSION['js'] : '';
    $checkbox = isset($_SESSION['checkboxs']) ? $_SESSION['checkboxs'] : null;
    $u = db_satu("select kode_branch from user where userid=?", 's', $userid);
    $unit = $u ? (string) $u['kode_branch'] : '';

    // nilai kepala laporan dan saldo awal, sama dengan versi lama
    $saldo_awal = null;          // saldo awal untuk perhitungan tanpa "All data"
    $saldo_awal_min = null;      // saldo awal untuk perhitungan dengan "All data"
    $saldo_akhir = null;         // ditampilkan sebagai "Saldo Awal" di kepala laporan
    $kode_2021 = $nama_2021 = $saldo_akhir_2021 = null;   // "No/Nama Perkiraan" dan "Saldo Akhir" di kepala laporan
    if ($report == 'current') {
        foreach (db_semua("select saldo_awal,saldo_akhir,kode_rekening,nama_rekening from tabel_master WHERE kode_rekening =? and unit =?", 'ss', $norek, $unit) as $row) {
            $saldo_awal = $row['saldo_awal'];
            $saldo_akhir = $row['saldo_akhir'];
        }
        $s = db_satu("select saldo_awal from tabel_master WHERE kode_rekening =? and unit =?", 'ss', $norek, $unit);
        $saldo_awal_min = $s ? $s['saldo_awal'] : null;
    } else {
        // perbaikan 2026-10-31 (perintah user): versi lama memakai tahun tetap (EOY Desember 2025, konsol 2026);
        // kini EOY = Desember tahun sebelum tahun terpilih, konsol = tahun terpilih
        if (!ctype_digit($tahun)) {
            jawab_json(array('pesan' => 'Tahun belum dipilih. Pilih tahun lalu klik Cari lagi.'), 400);
        }
        $tahun_eoy = (string) ((int) $tahun - 1);
        foreach (db_semua("select saldo_awal,saldo_akhir,kode_rekening,nama_rekening from tabel_master_eoy WHERE kode_rekening =? and unit =? and month(tanggal_awal)='12' and Year(tanggal_awal)=?", 'sss', $norek, $unit, $tahun_eoy) as $row) {
            $saldo_awal_min = $row['saldo_awal'];
            $saldo_akhir = $row['saldo_akhir'];
        }
        foreach (db_semua("select saldo_akhir,kode_rekening,nama_rekening from tabel_master_konsol WHERE kode_rekening =? and unit =? and month(tanggal_awal)=? and Year(tanggal_awal)=?", 'ssss', $norek, $unit, $bulan, $tahun) as $row) {
            $saldo_akhir_2021 = $row['saldo_akhir'];
            $kode_2021 = $row['kode_rekening'];
            $nama_2021 = $row['nama_rekening'];
        }
    }
    $normal = $pilih = null;
    foreach (db_semua("select normal,posisi from coa WHERE kode_rek =?", 's', $norek) as $row) {
        $normal = $row['normal'];
        $pilih = $row['posisi'];
    }
    $all = $checkbox == "checkbox";
    $saldo_mulai = compat_num($all ? $saldo_awal_min : $saldo_awal);
    // posisi selain Neraca / rugi-laba: versi lama tidak menjalankan query transaksi sama sekali
    $ada_query = $pilih === 'Neraca' || $pilih === 'rugi-laba';
    $meta = array('jenis' => 'buku_besar', 'userid' => $userid, 'unit' => $unit, 'norek' => $norek, 'bulan' => $bulan, 'tahun' => $tahun,
        'debet_normal' => $normal == 'debet', 'bulan_saja' => !$all && $pilih === 'rugi-laba', 'ada_query' => $ada_query,
        'saldo' => $saldo_mulai, 'saldo_mulai' => $saldo_mulai, 'id_akhir' => 0, 'baris' => 1, 'selesai' => 0, 'tot_debet' => 0, 'tot_kredit' => 0,
        'kepala' => array('no' => $kode_2021, 'nama' => $nama_2021, 'saldo_awal' => compat_num($saldo_akhir), 'saldo_akhir' => compat_num($saldo_akhir_2021)),
        'report' => $report, 'all' => $all, 'posisi' => $pilih, 'normal' => $normal);
    $total = 0;
    if ($ada_query) {
        list($w, $p) = filter_transaksi($meta);
        $n = db_satu("select count(*) as n from tabel_transaksi where $w", 'ssss', ...$p);
        $total = $n ? (int) $n['n'] : 0;
    }
    $meta['total'] = $total;
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
    $data = array();
    if ($meta['ada_query']) {
        list($w, $p) = filter_transaksi($meta);
        $p[] = (string) $meta['id_akhir'];
        $data = db_semua("SELECT id_transaksi, tanggal_transaksi as tanggal, kode_transaksi as no_bukti, kode_rekening, keterangan_transaksi, debet, kredit
            FROM tabel_transaksi where $w and id_transaksi > ? order by id_transaksi limit " . BUKU_BESAR_PER_TAHAP, 'sssss', ...$p);
    }
    $xml = '';
    $baris = (int) $meta['baris'];
    $saldo = $meta['saldo'];
    foreach ($data as $r) {
        $debet = compat_num($r['debet']);
        $kredit = compat_num($r['kredit']);
        $saldo = $meta['debet_normal'] ? ($saldo + $debet) - $kredit : ($saldo - $debet) + $kredit;
        $baris++;
        $xml .= xlsx_baris_xml($baris, array(
            array('s', $r['tanggal']),
            array('s', $r['no_bukti']),
            array('s', $r['kode_rekening']),
            array('s', $r['keterangan_transaksi']),
            array('n', $debet),
            array('n', $kredit),
            array('n', $saldo),
        ));
        $meta['tot_debet'] += $debet;
        $meta['tot_kredit'] += $kredit;
        $meta['id_akhir'] = (int) $r['id_transaksi'];
    }
    if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . '/data.part', $xml)) {
        jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
    }
    $meta['saldo'] = $saldo;
    $meta['baris'] = $baris;
    $meta['selesai'] += count($data);
    $habis = count($data) < BUKU_BESAR_PER_TAHAP;
    simpan_job($meta);
    jawab_json(array('selesai_baris' => $meta['selesai'], 'total' => $meta['total'], 'habis' => $habis));
}

if ($aksi === 'rakit') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    $k = $meta['kepala'];
    if (empty($meta['total_ditulis'])) {
        if (!xlsx_tulis_baris($meta['dir'] . '/data.part', xlsx_baris_xml((int) $meta['baris'] + 2, array(
            array('s', 'TOTAL'),
            array('s', number_format($meta['selesai'], 0, ',', '.') . ' transaksi'),
            array('s', ''),
            array('s', 'Saldo awal perhitungan: ' . number_format($meta['saldo_mulai'], 0, ',', '.')),
            array('n', $meta['tot_debet']),
            array('n', $meta['tot_kredit']),
            array('n', $meta['saldo']),
        ), 2))) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        // sheet Info: kepala laporan versi lama
        $info = array(
            array('Unit', $meta['unit']), array('No Perkiraan', $k['no']), array('Nama Perkiraan', $k['nama']),
            array('Saldo Awal', $k['saldo_awal']), array('Saldo Akhir', $k['saldo_akhir']),
            array('Report', $meta['report']), array('Bulan', $meta['bulan']), array('Tahun', $meta['tahun']),
            array('All data', $meta['all'] ? 'ya' : 'tidak'), array('Posisi / Normal', $meta['posisi'] . ' / ' . $meta['normal']),
        );
        $xml = '';
        foreach ($info as $i => $baris_info) {
            $xml .= xlsx_baris_xml($i + 2, array(array('s', $baris_info[0]), is_float($baris_info[1]) || is_int($baris_info[1]) ? array('n', $baris_info[1]) : array('s', $baris_info[1])));
        }
        xlsx_tulis_baris($meta['dir'] . '/info.part', $xml);
        $meta['total_ditulis'] = true;
        simpan_job($meta);
    }
    if (!xlsx_rakit_banyak($berkas, array(
        array('nama' => 'Buku Besar', 'judul' => $judul, 'lebar' => $lebar, 'bagian' => $meta['dir'] . '/data.part'),
        array('nama' => 'Info', 'judul' => array('Keterangan', 'Nilai'), 'lebar' => array(22, 40), 'bagian' => $meta['dir'] . '/info.part'),
    ))) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    // neraca pembanding: saldo_akhir tabel_master rekening ini (apa adanya)
    $nr = db_satu("select saldo_akhir from tabel_master where unit=? and kode_rekening=?", 'ss', $meta['unit'], $meta['norek']);
    $neraca = $nr ? compat_num($nr['saldo_akhir']) : null;
    jawab_json(array('unduh' => 'buku_besar_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $meta['selesai'],
        'ringkasan_kolom' => array(array('nama', 'Keterangan'), array('nilai', 'Nilai'), array('neraca', 'Neraca (pembanding)'), array('selisih', 'Selisih')),
        'ringkasan' => array(
            array('nama' => 'Rekening', 'nilai' => $meta['norek'] . ' ' . (string) $k['nama'], 'neraca' => null, 'selisih' => null),
            array('nama' => 'Saldo awal perhitungan', 'nilai' => $meta['saldo_mulai'], 'neraca' => null, 'selisih' => null),
            array('nama' => 'Total debet', 'nilai' => $meta['tot_debet'], 'neraca' => null, 'selisih' => null),
            array('nama' => 'Total kredit', 'nilai' => $meta['tot_kredit'], 'neraca' => null, 'selisih' => null),
            array('nama' => 'Saldo akhir berjalan', 'coa' => $meta['norek'], 'nilai' => $meta['saldo'], 'neraca' => $neraca,
                'selisih' => $neraca === null ? null : $meta['saldo'] - $neraca),
        )));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    $nama = 'buku_besar_' . preg_replace('/[^A-Za-z0-9_-]/', '', $meta['norek']) . '_' . preg_replace('/[^A-Za-z0-9_-]/', '', $meta['unit'])
        . '_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
