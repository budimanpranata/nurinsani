<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Export Rekap Nominative Acrual kp ke .xlsx secara bertahap (pengganti export_nomi_kp.php untuk tombol Rekap di Kr_kp.php).
 *
 *   POST aksi=mulai              -> buat pekerjaan export, hasil {job, total}
 *   POST aksi=proses&job=...     -> langkah berikutnya, hasil {persen, berikut, habis}:
 *                                   isi (kosongkan accrual/loan_accrual, isi dari loan/data_loan, hitung day/accrual),
 *                                   api (tambah musawamah dari API 192.168.88.44), lalu tulis 500 baris per panggilan
 *   POST aksi=rakit&job=...      -> baris TOTAL + rakit file .xlsx, hasil {unduh: url, ringkasan}
 *   GET  aksi=unduh&job=...      -> kirim file .xlsx lalu hapus file sementara
 *
 * Pengisian loan_accrual dan isi kolom sama dengan export_nomi_kp.php versi lama (lihat catatan di file itu):
 * loan_accrual tetap dipakai accrual.php untuk posting jurnal.
 * Tambahan 2026-11-03 (permintaan user, mengikuti menu unit): indikator progres, baris TOTAL di file, dan ringkasan
 * total (termasuk accrual gol 1 = nilai yang akan diposting tombol Accrual) di jendela progres.
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
require_once __DIR__ . '/../inc/xlsx_writer.php';

const NOMINATIF_KP_PER_TAHAP = 500;

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

/** Kosongkan accrual/loan_accrual lalu isi dari loan (CURRENT) atau data_loan (EOM), hitung day dan accrual. */
function isi_loan_accrual($status, $buss_date)
{
    $kolom = 'buss_date,code_kel,no_anggota,cif,Cust_Short_name,Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,saldo_margin,
angsuran,pokok,ijaroh,bulat,run_tenor,ke,usaha,nama_usaha,unit,tgl_wakalah,tgl_akad,tgl_murab,next_schedule,
maturity_date,last_payment,hari,cao,USERID,status,status_usia,status_app,gol';
    $dayList = array('Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis',
        'Fri' => 'Jumat', 'Sat' => 'Sabtu');
    $day = date('D', strtotime($buss_date));

    db_jalankan("TRUNCATE TABLE accrual");
    db_jalankan("TRUNCATE TABLE loan_accrual");
    switch ($status) {
        case "CURRENT":
            db_jalankan("insert into loan_accrual select $kolom, '' as day, '' as accrual from loan where os > 0 group by loan.cif");
            break;
        case "EOM":
            db_jalankan("insert into loan_accrual select $kolom, '' as day, '' as accrual from data_loan where os > 0 and buss_date = ?", 's', $buss_date);
            break;
        default:
    }
    db_jalankan("update loan_accrual set day = (select total_hari from param_hari where hari_eom = ? and hari_tagih = hari)", 's',
        isset($dayList[$day]) ? $dayList[$day] : '');
    db_jalankan("update loan_accrual set accrual = Plafond * 18/100/tenor/7*day");
}

/**
 * Tambah baris musawamah dari API ke loan_accrual (day 0, accrual 1 lalu dihitung ulang seperti versi lama).
 * Hasil: buss_date baris API terakhir (dipakai kolom buss_date di file, seperti versi lama), atau null bila tidak ada.
 */
function tambah_musawamah()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://192.168.88.44/ni/api/categories/musawamah.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $curl = curl_exec($ch);
    curl_close($ch);
    $data = is_string($curl) ? json_decode($curl, true) : null;
    if (!is_array($data)) {
        $data = array();
    }
    $kolom = 'buss_date,code_kel,no_anggota,cif,Cust_Short_name,Deal_type,suffix,bagi_hasil,Tenor,Plafond,os,saldo_margin,
angsuran,pokok,ijaroh,bulat,run_tenor,ke,usaha,nama_usaha,unit,tgl_wakalah,tgl_akad,tgl_murab,next_schedule,
maturity_date,last_payment,hari,cao,USERID,status,status_usia,status_app,gol,day,accrual';
    $kolom_api = array(
        'buss_date', 'code_kel', 'no_anggota', 'id_member', 'Cust_Short_name', 'Deal_type', 'suffix', 'bagi_hasil', 'Tenor',
        'Plafond', 'os', 'saldo_margin', 'angsuran', 'pokok', 'ijaroh', 'bulat', 'run_tenor', 'ke', 'usaha', 'nama_usaha',
        'unit', 'tgl_wakalah', 'tgl_akad', 'tgl_murab', 'next_schedule', 'maturity_date', 'last_payment', 'hari', 'cao',
        'USERID', 'status', 'status_usia', 'status_app', 'gol',
    );
    $tanda = implode(',', array_fill(0, count($kolom_api) + 2, '?'));
    $tgl = null;
    foreach ($data as $row) {
        $nilai = array();
        foreach ($kolom_api as $k) {
            $nilai[] = isset($row[$k]) ? (string) $row[$k] : '';
        }
        $tgl = $nilai[0];
        $nilai[] = '0'; // day
        $nilai[] = '1'; // accrual
        db_jalankan("insert into loan_accrual($kolom) values($tanda)", str_repeat('s', count($nilai)), ...$nilai);
    }
    // versi lama juga menghitung ulang day baris API ("where acrual ='1'", salah ketik kolom) yang selalu gagal: tidak dijalankan
    db_jalankan("update loan_accrual set accrual = day/7*ijaroh where accrual='1'");
    return $tgl;
}

/** Sel angka; nilai kosong/NULL atau bukan angka tetap ditulis apa adanya sebagai teks (seperti tabel versi lama). */
function sel_angka($v)
{
    return is_numeric($v) ? array('n', $v) : array('s', (string) $v);
}

$judul = array('buss_date', 'code_kel', 'no_anggota', 'cif', 'Cust_Short_name', 'Deal_type', 'suffix', 'bagi_hasil',
    'Tenor', 'Plafond', 'Pembiayaan', 'os', 'saldo_margin', 'angsuran', 'pokok', 'Margin_minggon', 'bulat', 'run_tenor', 'ke',
    'usaha', 'nama_usaha', 'unit', 'tgl_wakalah', 'tgl_akad', 'tgl_murab', 'next_schedule', 'maturity_date', 'last_payment',
    'hari', 'cao', 'USERID', 'status', 'status_usia', 'status_app', 'gol', 'Day', 'Acrual');
$lebar = array(18, 12, 16, 10, 28, 8, 6, 8, 6, 14, 14, 14, 14, 12, 12, 12, 10, 8, 6, 8, 22, 6, 18, 18, 18, 18, 18, 18,
    8, 8, 10, 8, 10, 10, 6, 6, 12);

if ($aksi === 'mulai') {
    // bersihkan pekerjaan lama (lebih dari 6 jam)
    foreach (glob($folder_induk . '/*', GLOB_ONLYDIR) ?: array() as $lama) {
        if (filemtime($lama) < time() - 6 * 3600) {
            hapus_folder($lama);
        }
    }
    // isian dari pencarian di Kr_kp.php (sama dengan versi lama)
    $status = isset($_SESSION['status']) ? (string) $_SESSION['status'] : '';
    $buss_date = isset($_SESSION['buss_dates']) ? (string) $_SESSION['buss_dates'] : '';
    $job = bin2hex(random_bytes(12));
    $dir = $folder_induk . DIRECTORY_SEPARATOR . $job;
    if (!@mkdir($dir, 0700)) {
        jawab_json(array('pesan' => 'Folder sementara export tidak bisa dibuat.'), 500);
    }
    $meta = array('userid' => $userid, 'status' => $status, 'buss_date' => $buss_date, 'langkah' => 'isi',
        'tgl_tampil' => $buss_date, 'total' => 0, 'selesai' => 0, 'baris' => 1, 'dir' => $dir);
    simpan_job($meta);
    jawab_json(array('job' => $job, 'total' => 0, 'berikut' => 'Mengisi ulang data accrual (' . $status . ')…'));
}

$meta = muat_job($folder_induk, $userid);
if (!$meta) {
    jawab_json(array('pesan' => 'Pekerjaan export tidak ditemukan atau sudah selesai. Silakan ulangi.'), 404);
}

if ($aksi === 'proses') {
    if ($meta['langkah'] === 'isi') {
        isi_loan_accrual($meta['status'], $meta['buss_date']);
        $meta['langkah'] = 'api';
        simpan_job($meta);
        jawab_json(array('persen' => 10, 'berikut' => 'Mengambil data musawamah (API)…', 'habis' => false));
    }
    if ($meta['langkah'] === 'api') {
        $tgl = tambah_musawamah();
        if ($tgl !== null) {
            $meta['tgl_tampil'] = $tgl;
        }
        $n = db_satu("select count(*) as n from loan_accrual");
        $meta['total'] = (int) ($n ? $n['n'] : 0);
        $meta['langkah'] = 'tulis';
        simpan_job($meta);
        jawab_json(array('persen' => 20, 'jumlah' => 0, 'habis' => $meta['total'] === 0,
            'berikut' => 'Menulis 0 dari ' . number_format($meta['total'], 0, ',', '.') . ' rekening…'));
    }

    // urutan baris sama dengan "Select * from loan_accrual" versi lama (urutan isi tabel)
    $data = db_semua("Select * from loan_accrual limit " . (int) $meta['selesai'] . ", " . NOMINATIF_KP_PER_TAHAP);
    $xml = '';
    $baris = (int) $meta['baris'];
    // total: rekening, plafond, pembiayaan, os, saldo margin, accrual semua, accrual gol 1
    $rk = isset($meta['ringkasan']) ? $meta['ringkasan'] : array(0, 0, 0, 0, 0, 0, 0);
    foreach ($data as $d) {
        $baris++;
        $pembiayaan = compat_num($d['os']) - compat_num($d['saldo_margin']);
        $rk[0]++;
        $rk[1] += compat_num($d['Plafond']);
        $rk[2] += $pembiayaan;
        $rk[3] += compat_num($d['os']);
        $rk[4] += compat_num($d['saldo_margin']);
        $rk[5] += compat_num($d['accrual']);
        if ((string) $d['gol'] === '1') {
            $rk[6] += compat_num($d['accrual']);
        }
        $sel = array(array('s', $meta['tgl_tampil']));
        foreach (array('code_kel', 'no_anggota', 'cif', 'Cust_Short_name', 'Deal_type', 'suffix') as $k) {
            $sel[] = array('s', $d[$k]);
        }
        foreach (array('bagi_hasil', 'Tenor', 'Plafond') as $k) {
            $sel[] = sel_angka($d[$k]);
        }
        // versi lama: judul "Pembiayaan" di atas kolom os dan judul "os" di atas os - saldo_margin (urutan dipertahankan)
        $sel[] = sel_angka($d['os']);
        $sel[] = array('n', $pembiayaan);
        foreach (array('saldo_margin', 'angsuran', 'pokok', 'ijaroh', 'bulat', 'run_tenor', 'ke') as $k) {
            $sel[] = sel_angka($d[$k]);
        }
        foreach (array('usaha', 'nama_usaha', 'unit', 'tgl_wakalah', 'tgl_akad', 'tgl_murab', 'next_schedule', 'maturity_date',
            'last_payment', 'hari', 'cao', 'USERID', 'status', 'status_usia', 'status_app', 'gol') as $k) {
            $sel[] = array('s', $d[$k]);
        }
        $sel[] = sel_angka($d['day']);
        $sel[] = sel_angka($d['accrual']);
        $xml .= xlsx_baris_xml($baris, $sel);
    }
    if ($xml !== '' && !xlsx_tulis_baris($meta['dir'] . '/data.part', $xml)) {
        jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
    }
    $meta['baris'] = $baris;
    $meta['ringkasan'] = $rk;
    $meta['selesai'] += count($data);
    $habis = count($data) < NOMINATIF_KP_PER_TAHAP || $meta['selesai'] >= $meta['total'];
    simpan_job($meta);
    $persen = $meta['total'] > 0 ? 20 + $meta['selesai'] / $meta['total'] * 75 : 95;
    jawab_json(array('persen' => $persen, 'jumlah' => $meta['selesai'], 'habis' => $habis,
        'berikut' => 'Menulis ' . number_format($meta['selesai'], 0, ',', '.') . ' dari '
            . number_format($meta['total'], 0, ',', '.') . ' rekening…'));
}

if ($aksi === 'rakit') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    $rk = isset($meta['ringkasan']) ? $meta['ringkasan'] : array(0, 0, 0, 0, 0, 0, 0);
    // baris TOTAL di bawah data, dipisah satu baris kosong agar tidak ikut terurut/terfilter (sekali saja)
    if (empty($meta['total_ditulis'])) {
        $sel = array_fill(0, count($judul), array('s', ''));
        $sel[0] = array('s', 'TOTAL');
        $sel[1] = array('s', number_format($rk[0], 0, ',', '.') . ' rekening');
        $sel[9] = array('n', $rk[1]);
        $sel[10] = array('n', $rk[3]);
        $sel[11] = array('n', $rk[2]);
        $sel[12] = array('n', $rk[4]);
        $sel[36] = array('n', $rk[5]);
        if (!xlsx_tulis_baris($meta['dir'] . '/data.part', xlsx_baris_xml((int) $meta['baris'] + 2, $sel, 2))) {
            jawab_json(array('pesan' => 'Gagal menulis file sementara export.'), 500);
        }
        $meta['total_ditulis'] = true;
        simpan_job($meta);
    }
    if (!xlsx_rakit($berkas, 'Accrual', $judul, $lebar, $meta['dir'] . '/data.part')) {
        jawab_json(array('pesan' => 'Gagal membuat file xlsx.'), 500);
    }
    jawab_json(array('unduh' => 'nominatif_kp_export.php?aksi=unduh&job=' . basename($meta['dir']), 'jumlah' => $rk[0],
        'ringkasan_kolom' => array(array('nama', 'Keterangan'), array('nominatif', 'Total')),
        'ringkasan' => array(
            array('nama' => 'Jumlah Rekening', 'nominatif' => $rk[0]),
            array('nama' => 'Plafond', 'nominatif' => $rk[1]),
            array('nama' => 'OS', 'nominatif' => $rk[3]),
            array('nama' => 'OS - Saldo Margin', 'nominatif' => $rk[2]),
            array('nama' => 'Saldo Margin', 'nominatif' => $rk[4]),
            array('nama' => 'Accrual (semua)', 'nominatif' => round($rk[5])),
            array('nama' => 'Accrual gol 1 (nilai posting tombol Accrual)', 'nominatif' => round($rk[6])),
        )));
}

if ($aksi === 'unduh') {
    $berkas = $meta['dir'] . '/hasil.xlsx';
    if (!is_file($berkas)) {
        jawab_json(array('pesan' => 'File export belum siap.'), 404);
    }
    $nama = 'rekap_accrual_' . preg_replace('/[^A-Za-z0-9_-]/', '', $meta['status']) . '_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    hapus_folder($meta['dir']);
    exit;
}

jawab_json(array('pesan' => 'Aksi tidak dikenal.'), 400);
