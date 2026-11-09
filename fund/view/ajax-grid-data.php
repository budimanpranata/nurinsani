<?php require_once __DIR__ . '/../../koneksi_db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('fund', 'admin', 'json');
/**
 * Sumber data DataTables (server-side, POST draw/start/length/search/order) untuk Data Master Deposan level fund
 * (fund/view/master_fund.php): tabel deposan.
 * Query, urutan, dan isi sama dengan versi lama (cari awalan CIF, nama, tanggal lahir, alamat, telepon); kini wajib login,
 * prepared statement, dan isi sel di-escape HTML. Tombol Hapus dibuang (perintah user 2026-11-04).
 * Enkripsi (PEKERJAAN_TERTUNDA.md bagian 87): alamat didekripsi untuk ditampilkan; pencarian tidak lagi mencocokkan
 * alamat (teks terenkripsi tidak bisa dicari dengan LIKE). Urut kolom Alamat mengikuti teks tersimpan.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/enkripsi.php';
wajib_login('json');
include "../../indo_tgl.php";
header('Content-Type: application/json; charset=utf-8');

$req = $_REQUEST;
$kolom = array(
    // indeks kolom DataTables => kolom database
    0 => 'UNIT',
    1 => 'CIF',
    2 => 'CUST_SHORT_NAME',
    3 => 'BIRTH_DATE',
    4 => 'HADDRESS1',
    5 => 'MOBILE_PHONE',
);
$cari = isset($req['search']['value']) ? (string) $req['search']['value'] : '';
$urut_kolom = isset($req['order'][0]['column']) ? (int) $req['order'][0]['column'] : 0;
$urut_kolom = isset($kolom[$urut_kolom]) ? $kolom[$urut_kolom] : 'UNIT';
$urut_arah = isset($req['order'][0]['dir']) && strtolower($req['order'][0]['dir']) === 'desc' ? 'desc' : 'asc';
$mulai = isset($req['start']) ? max(0, (int) $req['start']) : 0;
$panjang = isset($req['length']) ? (int) $req['length'] : 10;
$panjang = $panjang < 1 || $panjang > 500 ? 10 : $panjang;

$pilih = "SELECT UNIT, CIF, CUST_SHORT_NAME, BIRTH_DATE, HADDRESS1, MOBILE_PHONE FROM deposan";
$n = db_satu("SELECT count(*) as n FROM deposan");
$totalData = $n ? (int) $n['n'] : 0;
$totalFiltered = $totalData;

if ($cari !== '') {
    $saring = " WHERE CIF LIKE ? OR CUST_SHORT_NAME LIKE ? OR BIRTH_DATE LIKE ? OR MOBILE_PHONE LIKE ?";
    $awalan = $cari . '%';
    $param = array($awalan, $awalan, $awalan, $awalan);
    $n = db_satu("SELECT count(*) as n FROM deposan" . $saring, 'ssss', ...$param);
    $totalFiltered = $n ? (int) $n['n'] : 0;
    $baris = db_semua($pilih . $saring . " ORDER BY $urut_kolom $urut_arah LIMIT ?, ?", 'ssssii', ...array_merge($param, array($mulai, $panjang)));
} else {
    $baris = db_semua($pilih . " ORDER BY $urut_kolom $urut_arah LIMIT ?, ?", 'ii', $mulai, $panjang);
}

$data = array();
foreach ($baris as $row) {
    $data[] = array(
        h($row['UNIT']),
        h($row['CIF']),
        h($row['CUST_SHORT_NAME']),
        h(tgl_indo((string) $row['BIRTH_DATE'])),
        h(dekripsi_data($row['HADDRESS1'])),
        h($row['MOBILE_PHONE']),
        '<a href="../transaksi/edit.php?kd=' . h(urlencode((string) $row['CIF'])) . '" title="Edit" class="btn btn-sm btn-outline-warning">Edit</a>',
    );
}

echo json_encode(array(
    "draw" => intval(isset($req['draw']) ? $req['draw'] : 0),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data,
));
