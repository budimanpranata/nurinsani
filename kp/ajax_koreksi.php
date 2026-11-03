<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Data JSON untuk menu kp > Koreksi (view_koreksi.php).
 *   type=koreksi   : tabel_transaksi dengan tanggal_transaksi >= hari ini - 2 hari (semua unit). Dengan parameter
 *                    DataTables server-side (draw, start, length, search[value], order[0][column|dir]) hanya satu
 *                    halaman yang dikirim, dicari/diurut di database (perintah user 2026-11-03: tampilan lebih cepat;
 *                    versi lama mengirim seluruh baris 3 hari semua unit sekaligus). Tanpa draw: seluruh baris seperti lama.
 *   type=calonloan : sisa versi lama (loan MUSYAROKAH unit = userid), tidak dipanggil dari halaman
 * Dimigrasi 2026-11-03: wajib login (belum login -> 401 JSON; dulu redirect ke index.html) dan prepared statement;
 * isi dan format data sama dengan versi lama.
 */
session_start();
ob_start();
error_reporting(0);
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login('json');
header('Content-Type: application/json; charset=utf-8');
// hanya level kp (perintah user 2026-11-03; dulu semua user yang login)
if ($_SESSION['level'] != 'kp') {
    http_response_code(403);
    echo json_encode(array('data' => array(), 'error' => 'Anda bukan user'));
    exit;
}
$userid = (string) $_SESSION['userid'];

/** Baris tabel_transaksi untuk tabel Koreksi (nilai teks, debet/kredit berformat ribuan seperti versi lama). */
function baris_koreksi($row)
{
    $row = array_map(function ($v) { return $v === null ? null : (string) $v; }, $row);
    return array(
        'id' => $row['id'],
        'tanggal' => $row['tanggal'],
        'unit' => $row['unit'],
        'kode_rekening' => $row['kode_rekening'],
        'kode_transaksi' => $row['kode_transaksi'],
        'jenis_transaksi' => $row['jenis_transaksi'],
        'keterangan_transaksi' => $row['keterangan_transaksi'],
        'debet' => number_format(compat_num($row['debet']), 0, ',', '.'),
        'kredit' => number_format(compat_num($row['kredit']), 0, ',', '.'),
    );
}

$type = isset($_GET['type']) ? $_GET['type'] : 'koreksi';
$data = array();
if ($type == 'koreksi') {
    $kolom = "id_transaksi as id, tanggal_transaksi as tanggal,unit, kode_transaksi, kode_rekening, jenis_transaksi, keterangan_transaksi, debet,kredit";
    $rentang = "tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 2 DAY)";
    if (!isset($_REQUEST['draw'])) {
        foreach (db_semua("SELECT $kolom from tabel_transaksi where $rentang") as $row) {
            $data[] = baris_koreksi($row);
        }
        echo json_encode(array('data' => $data));
        exit;
    }
    // DataTables server-side
    $draw = (int) $_REQUEST['draw'];
    $mulai = max(0, (int) (isset($_REQUEST['start']) ? $_REQUEST['start'] : 0));
    $jumlah = (int) (isset($_REQUEST['length']) ? $_REQUEST['length'] : 10);
    $jumlah = $jumlah < 1 || $jumlah > 500 ? 10 : $jumlah;
    $urut_kolom = array('id_transaksi', 'tanggal_transaksi', 'unit', 'kode_transaksi', 'kode_rekening', 'keterangan_transaksi',
        'jenis_transaksi', 'debet', 'kredit');
    $i = isset($_REQUEST['order'][0]['column']) ? (int) $_REQUEST['order'][0]['column'] : 0;
    $arah = isset($_REQUEST['order'][0]['dir']) && $_REQUEST['order'][0]['dir'] === 'desc' ? 'desc' : 'asc';
    $urut = (isset($urut_kolom[$i]) ? $urut_kolom[$i] : 'id_transaksi') . ' ' . $arah . ($i ? ', id_transaksi ' . $arah : '');
    $cari = isset($_REQUEST['search']['value']) ? trim((string) $_REQUEST['search']['value']) : '';
    $saring = '';
    $param = array();
    if ($cari !== '') {
        // cari di kolom teks (seperti kotak Cari DataTables versi lama); angka utuh juga dicocokkan dengan id/debet/kredit
        $saring = " and (unit like ? or kode_transaksi like ? or kode_rekening like ? or jenis_transaksi like ? or keterangan_transaksi like ?"
            . " or tanggal_transaksi like ?" . (ctype_digit($cari) ? " or id_transaksi = ? or debet = ? or kredit = ?" : '') . ")";
        $pola = '%' . $cari . '%';
        $param = array($pola, $pola, $pola, $pola, $pola, $pola);
        if (ctype_digit($cari)) {
            array_push($param, $cari, $cari, $cari);
        }
    }
    $n_total = db_satu("SELECT count(*) as n from tabel_transaksi where $rentang");
    $n_saring = $cari === '' ? $n_total : db_satu("SELECT count(*) as n from tabel_transaksi where $rentang$saring", str_repeat('s', count($param)), ...$param);
    foreach (db_semua("SELECT $kolom from tabel_transaksi where $rentang$saring order by $urut limit $mulai, $jumlah",
        str_repeat('s', count($param)), ...$param) as $row) {
        $data[] = baris_koreksi($row);
    }
    echo json_encode(array('draw' => $draw, 'recordsTotal' => (int) ($n_total ? $n_total['n'] : 0),
        'recordsFiltered' => (int) ($n_saring ? $n_saring['n'] : 0), 'data' => $data));
    exit;
} elseif ($type == 'calonloan') {
    foreach (db_semua("SELECT kelompok.nama_kel, ao.nama_ao, loan.cif, loan.Cust_Short_name, loan.Plafond, nisbah.nisbah, nisbah.omzet
        FROM loan
        LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
        LEFT JOIN ao ON ao.cao = loan.cao
        LEFT JOIN nisbah ON nisbah.cif = loan.cif
        WHERE loan.status_app = 'MUSYAROKAH' and loan.unit =?", 's', $userid) as $row) {
        $row = array_map(function ($v) { return $v === null ? null : (string) $v; }, $row);
        $data[] = array(
            'nama_kel' => $row['nama_kel'],
            'nama_ao' => $row['nama_ao'],
            'cif' => $row['cif'],
            'Cust_Short_name' => $row['Cust_Short_name'],
            'Plafond' => number_format(compat_num($row['Plafond']), 0, ',', '.'),
            'nisbah' => $row['nisbah'],
            'omzet' => number_format(compat_num($row['omzet']), 0, ',', '.'),
        );
    }
    echo json_encode(array('data' => $data));
    exit;
} else {
    echo json_encode(array('data' => array(), 'error' => 'Invalid type parameter'));
    exit;
}
