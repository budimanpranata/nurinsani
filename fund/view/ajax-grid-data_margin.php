<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data daftar Param Margin (view_margin.php): param_deposito + jenis_dana per halaman (DataTables server-side, GET,
 * inc/datatables_server.php). Kolom: Kode Bulan, Jenis Dana, Nominal, Margin, Bulan, Status Posting (ket), Aksi
 * (hapus = true bila belum diposting).
 * Dimigrasi 2026-11-04 (perintah user): dulu tanpa cek login, pencarian/urut/limit disusun langsung dari input, peta
 * urut dobel indeks 3 (urut Margin = Bulan, urut Bulan error), Margin 0 saat mencari (kolom tidak diambil), tombol
 * Edit/Hapus menuju halaman deposan. Format tampilan angka (1,234,567) dan tanggal (tgl_indo) sama dengan versi lama.
 */
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/datatables_server.php';
wajib_login('json');
header('Content-Type: application/json; charset=utf-8');
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "admin") {
    http_response_code(403);
    echo '[]';
    exit;
}
include "../../indo_tgl.php";

$status = "IF(param_deposito.ket = '', 'Belum', param_deposito.ket)";
$hasil = dt_halaman(array(
    'pilih' => "param_deposito.code_bulan, jenis_dana.nama_jenis, param_deposito.nominal, param_deposito.margin_murab,
        param_deposito.buss_date, param_deposito.ket",
    'dari' => "FROM param_deposito inner join jenis_dana on jenis_dana.code_jenis = param_deposito.code_jenis WHERE 1=1",
    'tipe' => '',
    'nilai' => array(),
    // angka dicari dalam bentuk tampilannya (1,234,567), tanggal sebagai yyyy-mm-dd atau dd-mm-yyyy
    'cari' => array('param_deposito.code_bulan', 'jenis_dana.nama_jenis', 'FORMAT(param_deposito.nominal, 0)', 'FORMAT(param_deposito.margin_murab, 0)',
        "DATE_FORMAT(param_deposito.buss_date, '%Y-%m-%d')", "DATE_FORMAT(param_deposito.buss_date, '%d-%m-%Y')", $status),
    'urut' => array('param_deposito.code_bulan', 'jenis_dana.nama_jenis', 'param_deposito.nominal', 'param_deposito.margin_murab',
        'param_deposito.buss_date', $status),
    'urut_akhir' => 'param_deposito.code_bulan',
));

$data = array();
foreach ($hasil['rows'] as $r) {
    $data[] = array(
        'kode' => (string) $r['code_bulan'],
        'jenis' => (string) $r['nama_jenis'],
        'nominal' => number_format(compat_num($r['nominal'])),
        'margin' => number_format(compat_num($r['margin_murab'])),
        'bulan' => tgl_indo((string) $r['buss_date']),
        'status' => (string) $r['ket'] === '' ? 'Belum' : (string) $r['ket'],
        'hapus' => (string) $r['ket'] === '',
    );
}
echo json_encode(array(
    'draw' => $hasil['draw'],
    'recordsTotal' => $hasil['recordsTotal'],
    'recordsFiltered' => $hasil['recordsFiltered'],
    'data' => $data,
));
