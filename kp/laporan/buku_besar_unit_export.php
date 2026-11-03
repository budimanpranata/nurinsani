<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Export Buku Besar Unit (menu kp) ke .xlsx secara bertahap (pengganti kp/laporan/excel_buku_besar_unit.php, yang membuat
 * tabel HTML bernama .xls). Pilihan dari sesi yang diisi kp/laporan/buku_besar_unit.php: noreks, bulans, tahuns,
 * checkboxs (All data), units (unit terpilih).
 *
 *   POST aksi=mulai              -> siapkan saldo awal dan hitung jumlah transaksi, hasil {job, total}
 *   POST aksi=proses&job=...     -> tulis 1000 transaksi berikutnya (urut id) dengan saldo berjalan
 *   POST aksi=rakit&job=...      -> baris TOTAL + sheet Info + rakit .xlsx, hasil {unduh, ringkasan}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Logika sama dengan excel_buku_besar_unit.php versi lama, kecuali saldo awal:
 *   - No/Nama Perkiraan dan saldo akhir kepala: baris terakhir tabel_master WHERE kode_rekening dan unit terpilih
 *   - saldo awal (dengan maupun tanpa All data) = saldo_awal tabel_master_eoy unit terpilih Desember tahun sebelum tahun
 *     terpilih, yaitu saldo per tanggal_awal = saldo akhir tahun sebelumnya; kolom sama dengan Buku Besar KP dan menu unit
 *     (perintah user 2026-11-03; versi lama saldo_akhir tabel_master_konsol_eoy Desember 2021 tetap, tabel itu tidak ada
 *     di DB lokal sehingga saldo awal selalu 0)
 *   - transaksi tabel_transaksi unit terpilih: bulan 1 s.d. bulan terpilih, kecuali rekening rugi-laba tanpa All data:
 *     hanya bulan terpilih; posisi selain Neraca / rugi-laba: tanpa transaksi; normal/posisi dari coa kode_rekening
 *   - saldo berjalan: normal debet = saldo + debet - kredit; normal kredit = saldo - debet + kredit
 *   - urutan transaksi = id_transaksi (versi lama tanpa ORDER BY)
 * Tambahan (permintaan user 2026-11-03, sama dengan menu unit): indikator progres, baris TOTAL, sheet Info (kepala laporan),
 * dan ringkasan dengan neraca pembanding (saldo_akhir tabel_master unit terpilih apa adanya). var_dump($pilih) lama dibuang.
 */
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/xlsx_writer.php';

const BUKU_BESAR_UNIT_PER_TAHAP = 1000;

function jawab_json($data, $kode = 200)
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

$aksi = isset($_REQUEST['aksi']) ? (string) $_REQUEST['aksi'] : '';
wajib_login($aksi === 'unduh' ? 'halaman' : 'json');
if ($_SESSION['level'] != "kp") {
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
    if (!$meta || $meta['userid'] !== $userid || $meta['jenis'] !== 'buku_besar_unit_kp') {
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
    $checkbox = isset($_SESSION['checkboxs']) ? $_SESSION['checkboxs'] : null;
    $unit = isset($_SESSION['units']) ? (string) $_SESSION['units'] : '';
    if (!ctype_digit($tahun)) {
        jawab_json(array('pesan' => 'Tahun belum dipilih. Pilih tahun lalu klik Cari lagi.'), 400);
    }

    // kepala laporan: baris terakhir tabel_master rekening dan unit terpilih (versi lama)
    $saldo_akhir = $kode_rekening = $nama_rekening = null;
    foreach (db_semua("select saldo_akhir,kode_rekening,nama_rekening from tabel_master WHERE kode_rekening =? and unit =?", 'ss', $norek, $unit) as $row) {
        $kode_rekening = $row['kode_rekening'];
        $nama_rekening = $row['nama_rekening'];
        $saldo_akhir = $row['saldo_akhir'];
    }
    // saldo awal: saldo_awal EOY Desember tahun sebelum tahun terpilih (= saldo per tanggal_awal), unit terpilih;
    // kolom sama dengan Buku Besar KP dan menu unit (perintah user 2026-11-03)
    $tahun_eoy = (string) ((int) $tahun - 1);
    $s_awal = db_satu("select saldo_awal from tabel_master_eoy WHERE kode_rekening =? and unit =? and month(tanggal_awal)='12' and Year(tanggal_awal)=?",
        'sss', $norek, $unit, $tahun_eoy);
    $saldo_awal = $s_awal ? $s_awal['saldo_awal'] : null;
    $normal = $pilih = null;
    foreach (db_semua("select normal,posisi from coa WHERE kode_rek =?", 's', $norek) as $row) {
        $normal = $row['normal'];
        $pilih = $row['posisi'];
    }
    $all = $checkbox == "checkbox";
    $saldo_mulai = compat_num($saldo_awal);
    $ada_query = $pilih === 'Neraca' || $pilih === 'rugi-laba';
    $meta = array('jenis' => 'buku_besar_unit_kp', 'userid' => $userid, 'unit' => $unit, 'norek' => $norek, 'bulan' => $bulan, 'tahun' => $tahun,
        'tahun_eoy' => $tahun_eoy,
        'debet_normal' => $normal == 'debet', 'bulan_saja' => !$all && $pilih === 'rugi-laba', 'ada_query' => $ada_query,
        'saldo' => $saldo_mulai, 'saldo_mulai' => $saldo_mulai, 'id_akhir' => 0, 'baris' => 1, 'selesai' => 0, 'tot_debet' => 0, 'tot_kredit' => 0,
        'kepala' => array('no' => $kode_rekening, 'nama' => $nama_rekening, 'saldo_awal' => compat_num($saldo_awal), 'saldo_akhir' => compat_num($saldo_akhir)),
        'all' => $all, 'posisi' => $pilih, 'normal' => $normal);
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
            FROM tabel_transaksi where $w and id_transaksi > ? order by id_transaksi limit " . BUKU_BESAR_UNIT_PER_TAHAP, 'sssss', ...$p);
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
    $habis = count($data) < BUKU_BESAR_UNIT_PER_TAHAP;
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
            array('Bulan', $meta['bulan']), array('Tahun', $meta['tahun']), array('Saldo awal dari', 'tabel_master_eoy Desember ' . $meta['tahun_eoy']),
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
    // neraca pembanding: saldo_akhir tabel_master rekening ini di unit terpilih (apa adanya)
    $nr = db_satu("select saldo_akhir from tabel_master where unit=? and kode_rekening=?", 'ss', $meta['unit'], $meta['norek']);
    $neraca = $nr ? compat_num($nr['saldo_akhir']) : null;
    jawab_json(array('unduh' => 'buku_besar_unit_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $meta['selesai'],
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
