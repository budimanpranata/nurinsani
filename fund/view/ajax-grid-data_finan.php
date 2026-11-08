<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data daftar Param Financial (view_finan.php): param_finan per halaman (DataTables server-side, GET,
 * inc/datatables_server.php). Kolom: Kode Bulan, Nominal Murabahah, Nominal Margin, Bulan, Periode S.d, Jumlah Hari
 * (DATEDIFF(sd, dari)), Aksi (hapus = true hanya untuk periode terbaru / code_param terbesar).
 * Dimigrasi 2026-11-04 (perintah user): dulu tanpa cek login, pencarian/urut/limit disusun langsung dari input, tombol
 * Edit menuju edit deposan. Format angka (1,234,567) dan tanggal (tgl_indo) sama dengan versi lama.
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

$hasil = dt_halaman(array(
    'pilih' => "code_param, nomi_murab, marigin_murab, buss_date, sd, DATEDIFF(sd, dari) AS hari",
    'dari' => "FROM param_finan WHERE 1=1",
    'tipe' => '',
    'nilai' => array(),
    // angka dicari dalam bentuk tampilannya (1,234,567), tanggal sebagai yyyy-mm-dd atau dd-mm-yyyy
    'cari' => array('code_param', 'FORMAT(nomi_murab, 0)', 'FORMAT(marigin_murab, 0)',
        "DATE_FORMAT(buss_date, '%Y-%m-%d')", "DATE_FORMAT(buss_date, '%d-%m-%Y')", "DATE_FORMAT(sd, '%Y-%m-%d')", "DATE_FORMAT(sd, '%d-%m-%Y')"),
    'urut' => array('code_param', 'nomi_murab', 'marigin_murab', 'buss_date', 'sd', 'DATEDIFF(sd, dari)'),
    'urut_akhir' => 'code_param',
));
$terbaru = db_satu("SELECT max(code_param) AS kode FROM param_finan");
$terbaru = $terbaru ? (string) $terbaru['kode'] : '';

$data = array();
foreach ($hasil['rows'] as $r) {
    $data[] = array(
        'kode' => (string) $r['code_param'],
        'nominal' => number_format(compat_num($r['nomi_murab'])),
        'margin' => number_format(compat_num($r['marigin_murab'])),
        'bulan' => tgl_indo((string) $r['buss_date']),
        'sd' => tgl_indo((string) $r['sd']),
        'hari' => (string) $r['hari'],
        'hapus' => (string) $r['code_param'] === $terbaru,
    );
}
echo json_encode(array(
    'draw' => $hasil['draw'],
    'recordsTotal' => $hasil['recordsTotal'],
    'recordsFiltered' => $hasil['recordsFiltered'],
    'data' => $data,
));
