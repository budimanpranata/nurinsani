<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Proses pengajuan pembiayaan dari pengajuan_ex.php.
 *   POST ?p=update  -> "Sukses" atau daftar pesan error (satu per baris)
 *
 * Perhitungan dan query sama dengan versi lama (mysql_*); yang berubah:
 * prepared statement, wajib login + token, CIF harus milik unit user,
 * dan semua validasi dilakukan sebelum ada data yang ditulis.
 */
session_start();
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    exit('Sesi login sudah berakhir, silakan login kembali.');
}

$p = isset($_GET['p']) ? $_GET['p'] : '';

if ($p === 'hapus') {
    $_SESSION['info'] = 'GAGAL';
    exit('Maaf ada tidak bisa mengkases menu ini silahkan hubungi atasan anda');
}
if ($p !== 'update') {
    exit('Halaman tidak ditemukan');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf'], $_SESSION['csrf_pengajuan'])
    || !hash_equals($_SESSION['csrf_pengajuan'], (string) $_POST['csrf'])) {
    exit('Sesi formulir sudah tidak berlaku, muat ulang halaman lalu coba lagi.');
}

$db = db_ni();

/** Satu baris hasil query (prepared), atau null. */
function satu_baris($db, $sql, $tipe = '', ...$param)
{
    $st = $db->prepare($sql);
    if (!$st) return null;
    if ($tipe !== '') $st->bind_param($tipe, ...$param);
    if (!$st->execute()) return null;
    $baris = null;
    $hasil = $st->get_result();
    while ($r = $hasil->fetch_assoc()) {
        $baris = $r; // versi lama memakai baris terakhir
    }
    return $baris;
}

/** Jalankan query tulis (prepared); hasil true/false. */
function jalankan($db, $sql, $tipe = '', ...$param)
{
    $st = $db->prepare($sql);
    if (!$st) return false;
    if ($tipe !== '') $st->bind_param($tipe, ...$param);
    return $st->execute();
}

$id = isset($_POST['id']) ? trim((string) $_POST['id']) : '';
$tgl_wakalah = isset($_POST['inn']) ? trim((string) $_POST['inn']) : '';
$pla = isset($_POST['pla']) ? trim((string) $_POST['pla']) : '';
$cif = isset($_POST['cif']) ? trim((string) $_POST['cif']) : '';
$akad = isset($_POST['akad']) ? trim((string) $_POST['akad']) : '';
$tenor = isset($_POST['tenor']) ? trim((string) $_POST['tenor']) : '';
$jenis_akad = isset($_POST['jenisAkad']) ? trim((string) $_POST['jenisAkad']) : '';
$err = array();

// CIF harus anggota di unit user yang login
$user = satu_baris($db, 'SELECT kode_branch FROM user WHERE userid = ?', 's', $_SESSION['userid']);
$branch = $user ? $user['kode_branch'] : '';
if ($cif === '' || !satu_baris($db, 'SELECT cif FROM anggota WHERE cif = ? AND UNIT = ?', 'ss', $cif, $branch)) {
    exit('Anggota tidak ditemukan di unit Anda.');
}
if ($tenor === '' || !ctype_digit($tenor) || (int) $tenor <= 0) {
    $err[] = '* TENOR harus diisi angka lebih dari 0';
}
if ($pla !== '' && !is_numeric($pla)) {
    $err[] = '* PLAFOND harus berupa angka';
}
if ($jenis_akad === '') {
    $err[] = '* JENIS AKAD belum dipilih';
}

$tgl_wakal = strtotime($tgl_wakalah);
$murab1 = date('Y-m-d', strtotime('+7 day', (int) $tgl_wakal));
// Sama seperti versi lama: strtotime() atas timestamp angka -> false -> 0, hasilnya 1970-01-08.
$Newmurab = date('Y-m-d', strtotime('+7 day', (int) strtotime((string) $tgl_wakal)));
$tgl_m = strtotime($murab1);
$maturity_date1 = date('Y-m-d', strtotime('+' . (int) $tenor . ' WEEK', $tgl_m));
$dayList = array('Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu',
    'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu');
$hari = $tgl_wakal ? $dayList[date('D', $tgl_wakal)] : '';

$r = satu_baris($db, 'SELECT unit FROM loan WHERE cif = ?', 's', $cif);
$unit = $r ? $r['unit'] : '';
$r = satu_baris($db, 'SELECT nominal FROM omzet WHERE cif = ?', 's', $cif);
$omzet = $r ? $r['nominal'] : 0;
if ($jenis_akad === 'MUSYAROKAH' && $omzet == 0) {
    $err[] = '* Omzet tidak di Temukan';
}

$param_margin = 0;
$param_tabung = 0;
if (!$err) {
    $pbiaya = satu_baris($db, 'SELECT margin, tab, pla, jw FROM param_biaya WHERE jw = ? AND pla = ?', 'ss', $tenor, $pla);
    if ($pbiaya && $pbiaya['jw'] == $tenor && $pbiaya['pla'] == $pla) {
        $param_margin = $pbiaya['margin'];
        $param_tabung = $pbiaya['tab'];
        $pokok = $pla / $tenor;
        $margin = $pla * ($param_margin / 100);
        $ijaroh = $margin / $tenor;
        $angsuran = $ijaroh + $pokok;
        $uang = $param_tabung + $angsuran;
        $os = $pla + $margin + (2500 * $tenor);
        $margin = $pla * ($param_margin / 100) + (2500 * $tenor);
    } else {
        $err[] = '* parameter pembiyaan tidak ditemukan';
    }
}

// status approve unit tidak mengubah golongan (sama seperti versi lama)
$gol = '0';

if ($pla === '') $err[] = '* PLAFOND YANG DISETUJUI Masih Kosong';
if ($tgl_wakalah === '') $err[] = '* TANGGAL WAKALAH masih kosong';
if ($akad === '') $err[] = '* TANGGAL AKAD masih kosong';

// akad selain MURAB (musyarokah) memerlukan omzet > 0 untuk menghitung nisbah
if (!$err && $jenis_akad !== 'MURAB') {
    $r = satu_baris($db, 'SELECT MIN(nominal) AS omzet FROM omzet WHERE cif = ? AND nominal > 0', 's', $cif);
    $omzet_min = $r ? $r['omzet'] : null;
    if ($omzet_min == 0 || $omzet_min === null || $omzet_min === '') {
        $err[] = '* Belum memiliki omzet';
    }
}

if ($err) {
    exit(implode("\n", $err));
}

$_SESSION['sakadd'] = $akad;
$_SESSION['swakall'] = $tgl_wakalah;

if ($jenis_akad === 'MURAB') {
    jalankan($db, 'REPLACE INTO calon_loan SELECT * FROM loan WHERE cif = ?', 's', $cif);
    jalankan($db, "UPDATE calon_loan SET Plafond = ?, tgl_wakalah = ?, maturity_date = ?, tgl_murab = ?,
            status = 'CALON_ANGGOTA', saldo_margin = ?, angsuran = ?, pokok = ?, ijaroh = ?, ke = '1', suffix = suffix + 1,
            deal_produk = '2', os = ?, tgl_akad = ?, bulat = ?, hari = ?, tenor = ?, status_app = 'PENDING',
            run_tenor = '0', gol = ? WHERE cif = ?",
        'ssssddddssdssss', $pla, $tgl_wakalah, $maturity_date1, $Newmurab, $margin, $angsuran, $pokok, $ijaroh,
        $os, $akad, $uang, $hari, $tenor, $gol, $cif);
    jalankan($db, "UPDATE calon_loan SET status = 'CALON_ANGGOTA' WHERE cif = ?", 's', $cif);

    $d = satu_baris($db, 'SELECT suffix, tgl_murab, maturity_date, next_schedule FROM calon_loan WHERE cif = ?', 's', $cif);
    $suffix = $d ? $d['suffix'] : '';
    $murab = $d ? $d['tgl_murab'] : '';
    $maturity_date = $d ? $d['maturity_date'] : '';
    $next_schedule = ''; // versi lama: variabel ini tidak pernah terisi

    $anggota = $unit . $cif . '0' . $suffix;
    jalankan($db, 'UPDATE calon_loan SET no_anggota = ? WHERE cif = ?', 'ss', $anggota, $cif);
    jalankan($db, "UPDATE anggota SET status = 'CALON_ANGGOTA' WHERE cif = ?", 's', $cif);

    // jadwal sementara untuk menghitung jumlah hari libur pada masa angsuran
    $st = $db->prepare("INSERT INTO pinjaman_detail_c (id_pinjam, cicilan, angsuran_pokok, bunga, tgl_jatuh_tempo, tgl_bayar, jumlah_bayar, ket)
        VALUES (?, ?, ?, ?, ?, ?, '0', ?)");
    $awal_hari = $murab;
    $x = 0;
    while ($st && strtotime($awal_hari) < strtotime($maturity_date)) {
        $x++;
        $awal_hari = date('Y-m-d', strtotime('+7 day', strtotime($awal_hari)));
        $st->bind_param('sisssss', $anggota, $x, $pokok, $ijaroh, $awal_hari, $next_schedule, $next_schedule);
        $st->execute();
    }
    $c = satu_baris($db, 'SELECT COUNT(tgl_libur) AS libur FROM param_libur
        INNER JOIN pinjaman_detail_c ON pinjaman_detail_c.tgl_jatuh_tempo = param_libur.tgl_libur');
    $libur = $c ? (int) $c['libur'] : 0;
    jalankan($db, 'DELETE FROM pinjaman_detail_c WHERE id_pinjam = ?', 's', $anggota);

    $new_akhir_hari = date('Y-m-d', strtotime('+' . $libur . ' WEEK', strtotime($maturity_date)));
    jalankan($db, 'UPDATE calon_loan SET maturity_date = ? WHERE cif = ?', 'ss', $new_akhir_hari, $cif);

    $_SESSION['info'] = 'Proses Pengajuan Berhasil';
    echo 'Sukses';
} else {
    jalankan($db, "UPDATE anggota SET status = 'CALON_ANGGOTA' WHERE cif = ?", 's', $cif);
    jalankan($db, 'REPLACE INTO calon_loan SELECT * FROM loan WHERE cif = ?', 's', $cif);

    $omzet = $omzet_min / 2;
    $ijaroh = (($param_margin / 100) * $pla) / $tenor;
    $angsuran_pokok = $pla / $tenor;
    $angsuran = $angsuran_pokok + $ijaroh + 2500;
    $nisbah = $ijaroh / $omzet;

    jalankan($db, "UPDATE calon_loan SET Plafond = ?, tgl_wakalah = ?, maturity_date = ?, tgl_murab = ?,
            status = 'CALON_ANGGOTA', saldo_margin = '0', angsuran = ?, pokok = ?, ijaroh = ?, ke = '1', suffix = suffix + 1,
            deal_produk = '3', gol = '0', os = ?, tgl_akad = ?, bulat = ?, hari = ?, tenor = ?,
            status_app = 'MUSYAROKAH', run_tenor = '0' WHERE cif = ?",
        'ssssdddssdsss', $pla, $akad, $maturity_date1, $akad, $angsuran, $angsuran_pokok, $ijaroh,
        $pla, $akad, $uang, $hari, $tenor, $cif);
    jalankan($db, "UPDATE calon_loan SET status = 'CALON_ANGGOTA', status_app = 'MUSYAROKAH' WHERE cif = ?", 's', $cif);
    jalankan($db, 'UPDATE nisbah SET nisbah = ?, omzet = ? WHERE cif = ?', 'dds', $nisbah, $omzet, $cif);

    $_SESSION['info'] = 'Musarokah sukses di input';
    echo 'Sukses';
}
