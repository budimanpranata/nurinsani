<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Sumber data DataTables untuk View Data (view_data_n.php). Pencarian dan LIMIT memakai prepared statement.
 * Perbaikan 2026-10-30 (perintah user):
 *   - wajib login (versi lama bisa diakses tanpa login);
 *   - recordsTotal/recordsFiltered dihitung dari query yang sama dengan data yang tampil
 *     (versi lama selalu memakai jumlah seluruh tabel anggota, sehingga info & jumlah halaman saat mencari salah).
 * Catatan: data belum difilter per unit (lihat PEKERJAAN_TERTUNDA.md).
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(array(
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => array(),
        "error" => "Sesi login berakhir, silakan login kembali.",
    ));
    exit;
}

$unit = $_SESSION['userid']; // tidak dipakai (versi lama)

$conn = db_ni();

$search = isset($_POST['search']['value']) ? (string) $_POST['search']['value'] : '';
$start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
$length = isset($_POST['length']) ? (int) $_POST['length'] : 0;

$dari = " from anggota left join loan on loan.cif = anggota.cif where (plafond > 0 or plafond IS Null)";
$saring = '';
$tipe = '';
$param = array();
if (!empty($search)) {
    $saring = " AND (ID_NUMBER LIKE ? OR anggota.Cust_Short_name LIKE ? OR anggota.CIF LIKE ?)";
    $pola = '%' . $search . '%';
    $tipe = 'sss';
    $param = array($pola, $pola, $pola);
}

// jumlah seluruh data (tanpa kata kunci) dan jumlah hasil pencarian, dari query yang sama dengan data yang tampil
$total = db_satu("SELECT COUNT(*) as total" . $dari);
$totalData = $total ? $total['total'] : 0;
if ($saring === '') {
    $totalFiltered = $totalData;
} else {
    $hasil = db_satu("SELECT COUNT(*) as total" . $dari . $saring, $tipe, ...$param);
    $totalFiltered = $hasil ? $hasil['total'] : 0;
}

$sql = " SELECT anggota.unit,anggota.code_kel,norek,ID_NUMBER,anggota.Cust_Short_name,saldo_margin,os,Plafond,no_anggota,loan.cif" . $dari . $saring . " LIMIT ?, ?";
$data = array();
foreach (db_semua($sql, $tipe . 'ii', ...array_merge($param, array($start, $length))) as $row) {
    // nilai dikirim sebagai teks seperti versi lama (mysqli_query)
    foreach ($row as $k => $v) {
        $row[$k] = $v === null ? null : (string) $v;
    }
    $row['action'] = '<a href="new3.php?cif=' . htmlspecialchars((string) $row['cif'], ENT_QUOTES) . '" class="btn btn-sm btn-ni">Edit</a>';
    $data[] = $row;
}

$json_data = array(
    "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
);

echo json_encode($json_data);
