<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
ob_start();
error_reporting(0);
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
header('Content-Type: application/json; charset=utf-8');
$host = "localhost";
$user = "root";       // isi user database kamu
$pass = "anaskorti";  // isi password DB kamu
$db   = "nur_insani"; // nama database kamu

$conn = db_ni();
if (!$conn) {
    echo json_encode(array('data' => array(), 'error' => mysqli_connect_error()));
    exit;
}

$type =isset($_GET['type'])? $_GET['type'] : 'koreksi';
$data = array();


if ($type == 'koreksi') {

    $query = "
        SELECT 
	id_transaksi as id, tanggal_transaksi as tanggal,unit, kode_transaksi, kode_rekening, jenis_transaksi, keterangan_transaksi, 
	debet,kredit from tabel_transaksi where tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 2 DAY)";

    $result = db_query($conn, $query);
    if (!$result) {
        echo json_encode(array('data' => array(), 'error' => mysqli_error($conn)));
        exit;
    }

    while ($row = db_fetch_assoc($result)) {
        $data[] = array(
            'id'        => $row['id'],
            'tanggal'   => $row['tanggal'],
	     'unit'      => $row['unit'],
            'kode_rekening'  => $row['kode_rekening'],
            'kode_transaksi'   => $row['kode_transaksi'],
            'jenis_transaksi'       => $row['jenis_transaksi'],
            'keterangan_transaksi'      => $row['keterangan_transaksi'],
	    
            'debet'     => number_format(compat_num($row['debet']), 0, ',', '.'),
	    'kredit'     => number_format(compat_num($row['kredit']), 0, ',', '.')
        );
    }

    echo json_encode(array('data' => $data));
    exit;
}

elseif ($type == 'calonloan') {

    $query = "
        SELECT 
            kelompok.nama_kel,
            ao.nama_ao,
            loan.cif,
            loan.Cust_Short_name,
            loan.Plafond,
            nisbah.nisbah,
            nisbah.omzet
        FROM loan
        LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
        LEFT JOIN ao ON ao.cao = loan.cao
        LEFT JOIN nisbah ON nisbah.cif = loan.cif
        WHERE loan.status_app = 'MUSYAROKAH' and loan.unit ='$userid'
    ";

    $result = db_query($conn, $query);
    if (!$result) {
        echo json_encode(array('data' => array(), 'error' => mysqli_error($conn)));
        exit;
    }

    while ($row = db_fetch_assoc($result)) {
        $data[] = array(
            'nama_kel'        => $row['nama_kel'],
            'nama_ao'         => $row['nama_ao'],
            'cif'             => $row['cif'],
            'Cust_Short_name' => $row['Cust_Short_name'],
            'Plafond'         => number_format(compat_num($row['Plafond']), 0, ',', '.'),
            'nisbah'          => $row['nisbah'],
            'omzet'           => number_format(compat_num($row['omzet']), 0, ',', '.')
        );
    }

    echo json_encode(array('data' => $data));
    exit;
}

else {
    echo json_encode(array('data' => array(), 'error' => 'Invalid type parameter'));
    exit;
}
?>
