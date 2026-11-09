<?php require_once __DIR__ . '/../../koneksi_db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('fund', 'admin', 'json');
/**
 * Sumber data DataTables (server-side, POST draw/start/length/search/order) untuk Data Transaksi level fund
 * (fund/view/master_trans.php): deposito left join deposan.
 * Query dan pencarian (awalan norek, bilyet, nama, nominal, tenor, nisbah, rate, cara hitung, start, maturity) sama
 * dengan versi lama; kini wajib login, prepared statement, isi sel di-escape, tombol Break berupa form POST.
 * Perbaikan atas perintah user 2026-11-04 (F): peta kolom urut sesuai urutan kolom tabel (dulu indeks 7 dan 8 dobel
 * sehingga klik Rate mengurutkan Maturity, Cara Hitung mengurutkan Norek, Start/Maturity gagal).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login('json');
include "../../indo_tgl.php";
header('Content-Type: application/json; charset=utf-8');

$req = $_REQUEST;
$kolom = array(
    // indeks kolom DataTables => kolom database (sama dengan urutan kolom di tabel)
    0 => 'deposito.unit',
    1 => 'norek',
    2 => 'bilyet',
    3 => 'CUST_SHORT_NAME',
    4 => 'nominal',
    5 => 'tenor',
    6 => 'rasio',
    7 => 'eq_rate',
    8 => 'cara_hitung',
    9 => 'star_date',
    10 => 'maturity_date',
);
$cari = isset($req['search']['value']) ? (string) $req['search']['value'] : '';
$urut_kolom = isset($req['order'][0]['column']) ? (int) $req['order'][0]['column'] : 0;
$urut_kolom = isset($kolom[$urut_kolom]) ? $kolom[$urut_kolom] : 'deposito.unit';
$urut_arah = isset($req['order'][0]['dir']) && strtolower($req['order'][0]['dir']) === 'desc' ? 'desc' : 'asc';
$mulai = isset($req['start']) ? max(0, (int) $req['start']) : 0;
$panjang = isset($req['length']) ? (int) $req['length'] : 10;
$panjang = $panjang < 1 || $panjang > 500 ? 10 : $panjang;

$dari = " FROM deposito left join deposan on deposito.cif = deposan.CIF";
$pilih = "SELECT norek,bilyet,deposito.unit, deposan.CUST_SHORT_NAME,nominal,tenor,star_date,maturity_date, rasio, eq_rate,cara_hitung" . $dari;
$n = db_satu("SELECT count(*) as n" . $dari);
$totalData = $n ? (int) $n['n'] : 0;
$totalFiltered = $totalData;

if ($cari !== '') {
    $saring = " WHERE norek LIKE ? OR bilyet LIKE ? OR CUST_SHORT_NAME LIKE ? OR nominal LIKE ? OR tenor LIKE ? OR rasio LIKE ?"
        . " OR eq_rate LIKE ? OR cara_hitung LIKE ? OR star_date LIKE ? OR maturity_date LIKE ?";
    $param = array_fill(0, 10, $cari . '%');
    $n = db_satu("SELECT count(*) as n" . $dari . $saring, str_repeat('s', 10), ...$param);
    $totalFiltered = $n ? (int) $n['n'] : 0;
    $baris = db_semua($pilih . $saring . " ORDER BY $urut_kolom $urut_arah LIMIT ?, ?", str_repeat('s', 10) . 'ii', ...array_merge($param, array($mulai, $panjang)));
} else {
    $baris = db_semua($pilih . " ORDER BY $urut_kolom $urut_arah LIMIT ?, ?", 'ii', $mulai, $panjang);
}

$data = array();
foreach ($baris as $row) {
    $norek = (string) $row['norek'];
    $data[] = array(
        h($row['unit']),
        h($norek),
        h($row['bilyet']),
        h($row['CUST_SHORT_NAME']),
        number_format(compat_num($row['nominal'])),
        h($row['tenor']),
        h($row['rasio']),
        h($row['eq_rate']),
        h($row['cara_hitung']),
        h(tgl_indo((string) $row['star_date'])),
        h(tgl_indo((string) $row['maturity_date'])),
        '<a href="../transaksi/edit_nisbah.php?kd=' . h(urlencode($norek)) . '" title="Edit" class="btn btn-sm btn-outline-warning">Edit</a> '
        . '<form method="post" action="master_trans.php" class="d-inline"><button type="submit" name="break" value="' . h($norek) . '"'
        . ' class="btn btn-sm btn-outline-danger" data-konfirmasi="Proses Break deposito ' . h($norek) . '?">Break</button></form>',
    );
}

echo json_encode(array(
    "draw" => intval(isset($req['draw']) ? $req['draw'] : 0),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data,
));
