<?php require_once __DIR__ . '/koneksi_db.php';
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

$type =isset($_GET['type'])? $_GET['type'] : 'omzet';
$data = array();


if ($type == 'omzet') {

    $query = "
        SELECT 
            id,nama_kel,nama_ao, loan.Cust_Short_name as nama,
	    pinjaman_detail_mus.cif as cif, cicilan, pinjaman_detail_mus.nisbah as nisbah, pinjaman_detail_mus.nisbah_anggota as nisbah_anggota,
	    tgl_bayar as tanggal from pinjaman_detail_mus
 	    left join loan on loan.cif = pinjaman_detail_mus.cif
	    left join kelompok on kelompok.code_kel = loan.code_kel
	    left join ao on ao.cao = loan.cao
	    where os > 0 and loan.unit ='$userid' order by pinjaman_detail_mus.cif, id, cicilan ASC";

    $result = db_query($conn, $query);
    if (!$result) {
        echo json_encode(array('data' => array(), 'error' => mysqli_error($conn)));
        exit;
    }

    while ($row = db_fetch_assoc($result)) {
        $data[] = array(
            'id'        => $row['id'],
            'tanggal'   => $row['tanggal'],
            'nama_kel'  => $row['nama_kel'],
            'nama_ao'   => $row['nama_ao'],
            'cif'       => $row['cif'],
            'nama'      => $row['nama'],
	    'cicilan'      => $row['cicilan'],
            'nisbah'     => number_format(compat_num($row['nisbah']), 0, ',', '.'),
	    'nisbah_anggota'     => number_format(compat_num($row['nisbah_anggota']), 0, ',', '.')
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
