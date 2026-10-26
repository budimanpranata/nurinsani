<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
ob_start();
error_reporting(0);
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
//cek apakah user sudah login

if(!isset($_SESSION['userid'])){
   header("location:index.html");
}
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
            omzet.id,
            omzet.tanggal,
            kelompok.nama_kel,
            ao.nama_ao,
            omzet.cif,
            anggota.CUST_SHORT_NAME AS nama,
            omzet.nominal AS omzet
        FROM omzet
        LEFT JOIN anggota ON anggota.cif = omzet.cif
        LEFT JOIN kelompok ON kelompok.code_kel = anggota.CODE_KEL
        LEFT JOIN ao ON ao.cao = anggota.CAO  
	where anggota.UNIT ='$userid'     
        ORDER BY omzet.tanggal DESC
    ";

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
            'omzet'     => number_format(compat_num($row['omzet']), 0, ',', '.')
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
            calon_loan.cif,
            calon_loan.Cust_Short_name,
            calon_loan.Plafond,
            nisbah.nisbah,
            nisbah.omzet
        FROM calon_loan
        LEFT JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
        LEFT JOIN ao ON ao.cao = calon_loan.cao
        LEFT JOIN nisbah ON nisbah.cif = calon_loan.cif
        WHERE calon_loan.status_app = 'MUSYAROKAH' and calon_loan.unit ='$userid'
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

/* ============================================================
   3️⃣ JIKA PARAMETER TYPE SALAH
   ============================================================ */
else {
    echo json_encode(array('data' => array(), 'error' => 'Invalid type parameter'));
    exit;
}
?>
