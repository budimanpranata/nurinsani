<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Sumber data DataTables untuk View Data (view_data_n.php). Pencarian dan LIMIT memakai prepared statement.
 * Perbaikan 2026-10-30 (perintah user):
 *   - wajib login (versi lama bisa diakses tanpa login);
 *   - recordsTotal/recordsFiltered dihitung dari query yang sama dengan data yang tampil
 *     (versi lama selalu memakai jumlah seluruh tabel anggota, sehingga info & jumlah halaman saat mencari salah).
 * Perbaikan 2026-11-18: klik judul kolom kini mengurutkan data (kolom dari daftar tetap, KTP tidak bisa diurutkan).
 * Catatan: data sengaja tidak difilter per unit (keputusan user, lihat PEKERJAAN_TERTUNDA.md).
 * Enkripsi (PEKERJAAN_TERTUNDA.md bagian 87): NIK terenkripsi (anggota pasangan deposan) tampil tersamar; NIK lengkap
 * yang diketik juga dicocokkan lewat ID_NUMBER_IDX (NIK terenkripsi tidak bisa dicari sebagian dengan LIKE).
 */
require_once __DIR__ . '/inc/enkripsi.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit', 'json');
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
    $saring = " AND (ID_NUMBER LIKE ? OR anggota.Cust_Short_name LIKE ? OR anggota.CIF LIKE ? OR ID_NUMBER_IDX = ?)";
    $pola = '%' . $search . '%';
    $tipe = 'ssss';
    $param = array($pola, $pola, $pola, indeks_nik($search));
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

// Urutan kolom (perbaikan 2026-11-18, PEKERJAAN_TERTUNDA.md 2i): hanya kolom di daftar ini yang bisa diurutkan
// (indeks = urutan kolom di view_data.js); KTP tidak (sebagian NIK terenkripsi) dan Action tidak.
// Tanpa permintaan urutan, query sama dengan versi lama (tanpa ORDER BY).
$kolom_urut = array(
    1 => 'anggota.unit',
    2 => 'anggota.code_kel',
    3 => 'no_anggota',
    4 => 'anggota.CIF',
    5 => 'anggota.Cust_Short_name',
    6 => 'os',
    7 => 'Plafond',
    8 => 'saldo_margin',
);
$urut = '';
if (isset($_POST['order'][0]['column']) && !is_array($_POST['order'][0]['column'])) {
    $idx_urut = (string) $_POST['order'][0]['column'];
    if (ctype_digit($idx_urut) && isset($kolom_urut[(int) $idx_urut])) {
        $arah_urut = isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';
        $urut = " ORDER BY " . $kolom_urut[(int) $idx_urut] . " " . $arah_urut;
        if ($kolom_urut[(int) $idx_urut] !== 'anggota.CIF') {
            $urut .= ", anggota.CIF " . $arah_urut; // urutan kedua supaya pindah halaman tidak dobel/terlewat
        }
    }
}

$sql = " SELECT anggota.unit,anggota.code_kel,norek,ID_NUMBER,anggota.Cust_Short_name,saldo_margin,os,Plafond,no_anggota,loan.cif" . $dari . $saring . $urut . " LIMIT ?, ?";
$data = array();
foreach (db_semua($sql, $tipe . 'ii', ...array_merge($param, array($start, $length))) as $row) {
    // nilai dikirim sebagai teks seperti versi lama (mysqli_query)
    foreach ($row as $k => $v) {
        $row[$k] = $v === null ? null : (string) $v;
    }
    if ($row['ID_NUMBER'] !== null) {
        $row['ID_NUMBER'] = tampil_nik($row['ID_NUMBER']);
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
