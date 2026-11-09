<?php require_once __DIR__ . '/../../koneksi_db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp', 'json');
/**
 * Sumber data DataTables (server-side, POST draw/start/length/search/order) untuk menu Transaksi > Simpanan level kp
 * (kp/view/master_trans.php): saldo simpanan_kp per norek dan jenis.
 * Query, urutan, dan isi sama dengan versi lama; kini prepared statement dan isi sel di-escape HTML.
 * Perilaku lama yang dipertahankan (PEKERJAAN_TERTUNDA.md bagian 43): pada pencarian, GROUP BY berada di tengah
 * rangkaian OR sehingga pengelompokan menjadi "SNOREK, (TYPE OR sunit LIKE ..) OR type LIKE .." (saldo jenis
 * simpanan bisa tergabung) dan syarat unit/jenis tidak menyaring baris.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
include "../../indo_tgl.php";
header('Content-Type: application/json; charset=utf-8');

$req = $_REQUEST;
$kolom = array(
    // indeks kolom DataTables => kolom database
    0 => 'sunit',
    1 => 'snorek',
    2 => 'scif',
    3 => 'CUST_SHORT_NAME',
    4 => 'type',
    5 => 'DEBIT',
    6 => 'KREDIT',
);
$cari = isset($req['search']['value']) ? (string) $req['search']['value'] : '';
$urut_kolom = isset($req['order'][0]['column']) ? (int) $req['order'][0]['column'] : 0;
$urut_kolom = isset($kolom[$urut_kolom]) ? $kolom[$urut_kolom] : 'sunit';
$urut_arah = isset($req['order'][0]['dir']) && strtolower($req['order'][0]['dir']) === 'desc' ? 'desc' : 'asc';
$mulai = isset($req['start']) ? (int) $req['start'] : 0;
$panjang = isset($req['length']) ? (int) $req['length'] : 10;

$pilih = "SELECT snorek,scif,sunit, CUST_SHORT_NAME,type, sum(KREDIT - DEBIT) as SALDO
    FROM simpanan_kp left join anggota on simpanan_kp.scif = anggota.CIF";

// jumlah seluruh data tanpa pencarian (jumlah kelompok norek+jenis)
$totalData = count(db_semua($pilih . " GROUP BY SNOREK,TYPE ORDER BY SNOREK,TYPE"));
$totalFiltered = $totalData;

if ($cari !== '') {
    // susunan WHERE / GROUP BY / OR sama persis dengan versi lama
    $sql = $pilih . " WHERE snorek LIKE ? OR scif LIKE ? OR CUST_SHORT_NAME LIKE ? GROUP BY SNOREK,TYPE OR sunit LIKE ? OR type LIKE ? ";
    $awalan = $cari . '%';
    $param = array($awalan, $awalan, $awalan, $awalan, $awalan);
    $totalFiltered = count(db_semua($sql, 'sssss', ...$param));
    $baris = db_semua($sql . " ORDER BY $urut_kolom $urut_arah LIMIT ?, ?", 'sssssii', ...array_merge($param, array($mulai, $panjang)));
} else {
    $baris = db_semua($pilih . " GROUP BY SNOREK,TYPE ORDER BY $urut_kolom $urut_arah LIMIT ?, ?", 'ii', $mulai, $panjang);
}

$jenis = array('04' => 'POKOK', '05' => 'WAJIB', '06' => 'KHUSUS');
$data = array();
foreach ($baris as $row) {
    $data[] = array(
        h($row['sunit']),
        h($row['snorek']),
        h($row['scif']),
        h($row['CUST_SHORT_NAME']),
        isset($jenis[(string) $row['type']]) ? $jenis[(string) $row['type']] : 'UMUM',
        number_format(compat_num($row['SALDO'])),
        // tautan sama dengan versi lama (termasuk "& type=")
        '<a href="../transaksi/input_trns.php?cif=' . h($row['scif']) . '& type=' . h($row['type']) . '" title="Edit" class="btn btn-sm btn-outline-warning">Edit</a> '
        . '<a href="../transaksi/rek_koran.php?norek=' . h($row['snorek']) . '" title="Cetak" class="btn btn-sm btn-outline-success">Cetak</a>',
    );
}

echo json_encode(array(
    "draw" => intval(isset($req['draw']) ? $req['draw'] : 0),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data,
));
