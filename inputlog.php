<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Simpan Entry Calon Anggota dari new2.php: calon_loan, anggota, barang, rekomendasi (+ nisbah untuk musyarokah).
 *
 * Perhitungan sama dengan versi lama (mysql_*). Yang berubah: prepared statement, token, validasi
 * dilakukan sebelum menulis, semua tabel disimpan dalam satu transaksi, dan hasil ditampilkan
 * kembali di new2.php (isian tidak hilang bila ditolak).
 */
date_default_timezone_set('Asia/Jakarta');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}
if ($_SESSION['level'] != 'unit') {
    header('location:index.html');
    exit;
}

/** Kembali ke form dengan pesan; $simpan_isian = isian dikembalikan ke form. */
function kembali($jenis, $pesan, $simpan_isian = true)
{
    $_SESSION['pesan_entry'] = array($jenis, $pesan);
    if ($simpan_isian) {
        $isian = $_POST;
        unset($isian['csrf']);
        $_SESSION['form_entry'] = $isian;
    }
    header('location:new2.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('location:new2.php');
    exit;
}
if (!isset($_POST['csrf'], $_SESSION['csrf_entry']) || !hash_equals($_SESSION['csrf_entry'], (string) $_POST['csrf'])) {
    kembali('danger', 'Sesi formulir sudah tidak berlaku, silakan simpan ulang.');
}

$db = db_ni();
$userid = $_SESSION['userid'];
$in = function ($nama) {
    return isset($_POST[$nama]) ? trim((string) $_POST[$nama]) : '';
};

$code_kel = $in('code_kel');
$cao = $in('code_ao');
$branch = $in('branch');
$cif = $in('cif');
$nama = $in('nama');
$nama1 = $in('wali');
$alamat = $in('alamat');
$rt = $in('rt');
$notlp = $in('no_tlp');
$home_tlp = $in('tlp_penjamin');
$tgl_lahir = $in('tgl_lahir');
$tgl_wakalah = $in('tgl_skep');
$tgl_akad = $in('tgl_akad');
$ibu = $in('ibu');
$buss_date = date('Y-m-d h:m:s'); // sama dengan versi lama ("m" = bulan)
$usaha = $in('usaha');
$agama = $in('agama');
$negara = $in('negara');
$pendidikan = $in('pendidikan');
$npwp = $in('npwp');
$jenis_kel = $in('jenis_kel');
$kode_pos = $in('kd_pos');
$lurah = $in('lurah');
$kec = $in('kec');
$rekomen = $in('num');
$kota = $in('kota');
$status_kawin = $in('status_kawin');
$id_expire = $in('id_expire');
$ktp = $in('id');
$kerja = $in('pekerjaan');
$tempat_lahir = $in('tempat_lahir');
$no_anggota = $in('no_anggota');
$nama_usaha = $in('nama_usaha');
$tenor = $in('tenor');
$pla = $in('pla');
$deal_type = $in('sumber');
$urutan = array('satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh');
$barang = array();
foreach ($urutan as $u) {
    $barang['barang_' . $u] = $in('barang_' . $u);
    $barang['harga_' . $u] = $in('harga_' . $u);
}

$_SESSION['tgl_wakalah'] = $tgl_wakalah;
$_SESSION['stgl_akad'] = $tgl_akad;

/* ---------------- validasi (isian wajib sama dengan form) ---------------- */
$wajib = array('branch' => 'KODE Branch', 'id' => 'NOMOR KTP', 'code_kel' => 'KODE Kelompok', 'cif' => 'CIF', 'nama' => 'Nama',
    'tempat_lahir' => 'TEMPAT LAHIR', 'wali' => 'NAMA PENJAMIN', 'tgl_lahir' => 'TANGGAL LAHIR', 'no_tlp' => 'NOMOR TLP',
    'tlp_penjamin' => 'NOMOR TLP Penjamin', 'alamat' => 'ALAMAT', 'npwp' => 'NPWP', 'rt' => 'RT/RW', 'ibu' => 'IBU KANDUNG',
    'kd_pos' => 'KODE POS', 'tgl_skep' => 'TANGGAL WAKALAH', 'tgl_akad' => 'TANGGAL AKAD MURABAHAH', 'lurah' => 'LURAH',
    'kec' => 'KECAMATAN', 'kota' => 'KOTA', 'pekerjaan' => 'Pekerjaan', 'id_expire' => 'ID Expire', 'pla' => 'PEMBIAYAAN',
    'pendidikan' => 'Pendidikan', 'usaha' => 'Usaha');
$kosong = array();
foreach ($wajib as $k => $label) {
    if ($in($k) === '') $kosong[] = $label;
}
if ($kosong) {
    kembali('danger', 'Masih kosong: ' . implode(', ', $kosong));
}
if (!ctype_digit($pla) || !in_array($tenor, array('25', '35', '50'), true)) {
    kembali('danger', 'Pembiayaan dan tenor harus berupa angka.');
}
if (!strtotime($tgl_wakalah) || !strtotime($tgl_lahir)) {
    kembali('danger', 'Format tanggal tidak valid.');
}

/** Satu baris hasil query (prepared) atau null. */
function satu($db, $sql, $tipe = '', ...$param)
{
    $st = $db->prepare($sql);
    if (!$st) return null;
    if ($tipe !== '') $st->bind_param($tipe, ...$param);
    if (!$st->execute()) return null;
    return $st->get_result()->fetch_assoc();
}

$pbiaya = satu($db, 'SELECT margin, tab, pla, jw FROM param_biaya WHERE jw = ? AND pla = ?', 'ss', $tenor, $pla);
if (!$pbiaya || $pbiaya['jw'] != $tenor || $pbiaya['pla'] != $pla) {
    kembali('danger', 'Parameter Pembiayaan tidak ditemukan');
}
if (satu($db, 'SELECT 1 AS ada FROM anggota WHERE ID_NUMBER = ?', 's', $ktp)) {
    kembali('danger', "data $ktp and $nama sudah ada dalam System");
}
if (satu($db, 'SELECT 1 AS ada FROM anggota WHERE CIF = ?', 's', $cif)) {
    kembali('danger', 'data sudah ada dalam database, isi form dengan No CIF yang berbeda');
}

/* ---------------- perhitungan (sama dengan versi lama) ---------------- */
$tgl_wakal = strtotime($tgl_wakalah);
$murab1 = date('Y-m-d', strtotime('+7 day', $tgl_wakal));
$Newmurab = date('Y-m-d', strtotime('+7 day', strtotime($tgl_wakalah)));
$tgl_m = strtotime($murab1);
$maturity_date = date('Y-m-d', strtotime('+' . $tenor . ' WEEK', $tgl_m));

$usia = (int) date('Y') - (int) date('Y', strtotime($tgl_lahir));
$status_usia = $usia > 55 ? 'YA' : 'NO';

$param_margin = $pbiaya['margin'];
$param_tabung = $pbiaya['tab'];
$pokok = $pla / $tenor;
$margin = $pla * ($param_margin / 100);
$ijaroh = $margin / $tenor;
$angsuran = $ijaroh + $pokok;
$uang = $param_tabung + $angsuran;
$os = $pla + $margin + (2500 * $tenor);
$margin = $pla * ($param_margin / 100) + (2500 * $tenor);

// Versi lama membaca kolom yang tidak ada di hasil query omzet, sehingga omzet selalu 1.000.000 / 2.
// Nisbah diproses ulang saat realisasi musyarokah.
$omzet = 1000000 / 2;

$jakad = satu($db, 'SELECT jenis_akad FROM jenis_akad WHERE unit = ?', 's', $branch);
$jenis_akad = $jakad ? $jakad['jenis_akad'] : '';

if ($jenis_akad == 'MURAB') {
    $status_app = 'PENDING';
    $deal_produk = '2';
    $nisbah = null;
} else {
    $ijaroh = (($param_margin / 100) * $pla) / $tenor;
    $pokok = $pla / $tenor;
    $angsuran = $pokok + $ijaroh + 2500;
    $os = $pla;
    $margin = 0;
    $nisbah = $ijaroh / $omzet;
    $status_app = 'MUSYAROKAH';
    $deal_produk = '3';
}

// nomor anggota: unit + yyyymmdd + urut 4 digit per unit per hari
$today = date('Ymd');
$awalan = $branch . $today . '%';
$hit = satu($db, 'SELECT MAX(no) AS last FROM anggota WHERE no LIKE ? AND UNIT = ?', 'ss', $awalan, $branch);
$lastNoUrut = substr((string) ($hit ? $hit['last'] : ''), strlen($branch) + 8, 4);
$no = $branch . $today . sprintf('%04s', (int) $lastNoUrut + 1);

$dayList = array('Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu',
    'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu');
$hari = $dayList[date('D', $tgl_wakal)];

// minggu libur pada hari angsuran memperpanjang jatuh tempo
$row_sal = satu($db, 'SELECT COUNT(tgl_libur) AS libur FROM param_libur WHERE tgl_libur BETWEEN ? AND ? AND Hari = ?',
    'sss', $Newmurab, $maturity_date, $hari);
$libur = $row_sal ? (int) $row_sal['libur'] : 0;
$new_tenor = $libur > 0 ? $tenor + $libur : $tenor;
$maturity_date = date('Y-m-d', strtotime('+' . $new_tenor . ' WEEK', $tgl_m));
$next_schedule = ''; // versi lama: variabel ini tidak pernah terisi

/* ---------------- simpan (satu transaksi) ---------------- */
/** Jalankan query tulis; lempar exception bila gagal supaya transaksi dibatalkan. */
function tulis($db, $nama_tabel, $sql, $tipe, ...$param)
{
    $st = $db->prepare($sql);
    if (!$st || !$st->bind_param($tipe, ...$param) || !$st->execute()) {
        throw new RuntimeException("Gagal menyimpan ke tabel $nama_tabel: " . $db->error);
    }
}

$db->begin_transaction();
try {
    if ($nisbah !== null) {
        tulis($db, 'nisbah', 'REPLACE INTO nisbah (cif, nisbah, omzet) VALUES (?, ?, ?)', 'sdd', $cif, $nisbah, $omzet);
    }
    tulis($db, 'calon_loan', "INSERT INTO calon_loan (buss_date, code_kel, no_anggota, cif, Cust_Short_name, Deal_type, suffix,
            bagi_hasil, Tenor, Plafond, os, saldo_margin, angsuran, pokok, ijaroh, bulat, ke, usaha, nama_usaha, unit,
            tgl_wakalah, tgl_akad, tgl_murab, next_schedule, maturity_date, hari, cao, USERID, status, status_usia,
            status_app, gol, deal_produk)
        VALUES (?, ?, ?, ?, ?, ?, '1', ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ANGGOTA', ?, ?, '0', ?)",
        'ssssss' . 'dssdddddd' . str_repeat('s', 11) . 'sss',
        $buss_date, $code_kel, $no_anggota, $cif, $nama, $deal_type, $margin, $tenor, $pla, $os, $margin, $angsuran,
        $pokok, $ijaroh, $uang, $usaha, $nama_usaha, $branch, $tgl_wakalah, $tgl_akad, $Newmurab, $next_schedule,
        $maturity_date, $hari, $cao, $userid, $status_usia, $status_app, $deal_produk);
    tulis($db, 'anggota', "INSERT INTO anggota (no, CODE_KEL, NOREK, BUSS_DATE, CIF, CUST_SHORT_NAME, UNIT, DEAL_TYPE,
            HADDRESS1, HADDRESS2, HADDRESS3, HPOST_CODE, HADDRESS4, HADDRESS5, MOBILE_PHONE, HOME_PHONE, GENDER,
            BIRTH_DATE, ID_NUMBER, CITIZENSHIP, MARITAL_STATUS, RELIGION, MOTHER_NAME, NPWP, SOURCE_INCOME, EDUCATION,
            TEMPAT_LAHIR, ID_EXPIRED, WARIS, CAO, USERID, status, pekerjaan_pasangan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ANGGOTA', ?)",
        str_repeat('s', 32),
        $no, $code_kel, $no_anggota, $buss_date, $cif, $nama, $branch, $deal_type, $alamat, $lurah, $kec, $kode_pos,
        $kota, $rt, $notlp, $home_tlp, $jenis_kel, $tgl_lahir, $ktp, $negara, $status_kawin, $agama, $ibu, $npwp,
        $usaha, $pendidikan, $tempat_lahir, $id_expire, $nama1, $cao, $userid, $kerja);
    $nilai_barang = array();
    foreach ($urutan as $u) {
        $nilai_barang[] = $barang['barang_' . $u];
        $nilai_barang[] = $barang['harga_' . $u];
    }
    tulis($db, 'barang', 'INSERT INTO barang (id_barang, unit, cif, code_kel, barang_satu, harga_satu, barang_dua, harga_dua,
            barang_tiga, harga_tiga, barang_empat, harga_empat, barang_lima, harga_lima, barang_enam, harga_enam,
            barang_tujuh, harga_tujuh, barang_delapan, harga_delapan, barang_sembilan, harga_sembilan, barang_sepuluh,
            harga_sepuluh, userid)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        str_repeat('s', 25), $no_anggota, $branch, $cif, $code_kel, ...array_merge($nilai_barang, array($userid)));
    tulis($db, 'rekomendasi', 'INSERT INTO rekomendasi (norek, cif, nominal) VALUES (?, ?, ?)', 'sss', $no_anggota, $cif, $rekomen);
    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    kembali('danger', $e->getMessage());
}

$teks = "data Anggota $nama sudah ditambahkan di kelompok $code_kel";
if ($status_usia === 'YA') {
    $teks .= "\nUsia lebih besar 55: proses selanjutnya menunggu approval Lead Admin.";
}
kembali($status_usia === 'YA' ? 'warning' : 'success', $teks, false);
