<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data menu fund > Report > View Realisasi Baghas (view_baghas.php): jadwal bagi hasil deposito_detail + nama deposan,
 * per halaman (DataTables server-side, GET draw/start/length/search/order, inc/datatables_server.php).
 *   status = '' (semua, bawaan - sama dengan versi lama) | belum (ket kosong) | REALISASI | BAYAR
 * Status dari deposito_detail.ket: kosong = jadwal belum dibayar, REALISASI = Realisasi Bagi Hasil fund
 * (fund/realisasi_dep.php), BAYAR = Disburse kp (kp/transaksi/engine_update_dep.php).
 * Dimigrasi 2026-11-04 (perintah user): dulu tanpa cek login, protokol lama sEcho, error MySQL dikirim mentah, peta
 * kolom urut/cari salah (urut Nama = norek, cari Id Deposito = id internal, cari Nama = norek); kini prepared statement,
 * satu pencarian per kata di semua kolom, kolom Status + saringan status (perintah user).
 */
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/datatables_server.php';
wajib_login('json');
header('Content-Type: application/json; charset=utf-8');
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "fund") {
    http_response_code(403);
    echo '[]';
    exit;
}
include_once __DIR__ . '/../../function.php';

const BAGHAS_STATUS = array('belum' => '', 'REALISASI' => 'REALISASI', 'BAYAR' => 'BAYAR');

$status = isset($_GET['status']) ? (string) $_GET['status'] : '';
$dari = "from deposito_detail inner join deposan on deposan.cif = deposito_detail.cif";
$tipe = '';
$nilai = array();
if (isset(BAGHAS_STATUS[$status])) {
    $dari .= " where IFNULL(deposito_detail.ket, '') = ?";
    $tipe = 's';
    $nilai[] = BAGHAS_STATUS[$status];
} else {
    $dari .= " where 1=1";
}
$teks_status = "IF(IFNULL(deposito_detail.ket, '') = '', 'Belum', deposito_detail.ket)";
$hasil = dt_halaman(array(
    'pilih' => "deposito_detail.id_deposito, deposan.CUST_SHORT_NAME, deposito_detail.ke, deposito_detail.jumlah_nisbah,
        deposito_detail.tgl_jatuh_tempo, deposito_detail.tgl_bayar, $teks_status as status",
    'dari' => $dari,
    'tipe' => $tipe,
    'nilai' => $nilai,
    // tanggal bisa dicari sebagai dd-mm-yyyy atau yyyy-mm-dd; bagi hasil dalam bentuk tampilannya (1.234.567)
    'cari' => array('deposito_detail.id_deposito', 'deposan.CUST_SHORT_NAME', 'deposito_detail.ke', dt_format_angka('deposito_detail.jumlah_nisbah'),
        "DATE_FORMAT(deposito_detail.tgl_jatuh_tempo, '%d-%m-%Y')", "DATE_FORMAT(deposito_detail.tgl_jatuh_tempo, '%Y-%m-%d')",
        "DATE_FORMAT(deposito_detail.tgl_bayar, '%d-%m-%Y')", "DATE_FORMAT(deposito_detail.tgl_bayar, '%Y-%m-%d')", $teks_status),
    'urut' => array('deposito_detail.id_deposito', 'deposan.CUST_SHORT_NAME', 'deposito_detail.ke', 'deposito_detail.jumlah_nisbah',
        'deposito_detail.tgl_jatuh_tempo', 'deposito_detail.tgl_bayar', $teks_status),
    'urut_akhir' => 'deposito_detail.ke, deposito_detail.id',
));

$data = array();
foreach ($hasil['rows'] as $r) {
    $data[] = array(
        'norek' => (string) $r['id_deposito'],
        'nama' => (string) $r['CUST_SHORT_NAME'],
        'ke' => (string) $r['ke'],
        'nisbah' => number_format(compat_num($r['jumlah_nisbah']), 0, ',', '.'),
        'tempo' => tgl_indo($r['tgl_jatuh_tempo']),
        'bayar' => tgl_indo($r['tgl_bayar']),
        'status' => (string) $r['status'],
    );
}
echo json_encode(array(
    'draw' => $hasil['draw'],
    'recordsTotal' => $hasil['recordsTotal'],
    'recordsFiltered' => $hasil['recordsFiltered'],
    'data' => $data,
));
