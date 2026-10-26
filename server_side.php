<?php require_once __DIR__ . '/koneksi_db.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
		
		$unit=$_SESSION['userid'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nur_insani";
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$conn = db_ni();
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$search = $_POST['search']['value'];
$start = $_POST['start'];
$length = $_POST['length'];

$sql = " SELECT anggota.unit,anggota.code_kel,norek,ID_NUMBER,anggota.Cust_Short_name,saldo_margin,os,Plafond,no_anggota,loan.cif from anggota left join loan on loan.cif = anggota.cif where (plafond > 0 or plafond IS Null)";

if (!empty($search)) {
    $sql .= " AND (ID_NUMBER LIKE '%$search%' OR anggota.Cust_Short_name LIKE '%$search%' OR anggota.CIF LIKE '%$search%')";
}

$totalDataQuery = "SELECT COUNT(*) as total FROM anggota";
$totalDataResult = $conn->query($totalDataQuery);
$totalDataRow = $totalDataResult->fetch_assoc();
$totalData = $totalDataRow['total'];

$result = $conn->query($sql . " LIMIT $start, $length");

$data = array();
while ($row = $result->fetch_assoc()) {
    $row['action'] = '<a href="new3.php?cif='.$row['cif'].'" class="btn btn-sm btn-warning">Edit</a>';
    $data[] = $row;
}

$totalFilteredQuery = "SELECT COUNT(*) as total FROM anggota";
$totalFilteredResult = $conn->query($totalFilteredQuery);
$totalFilteredRow = $totalFilteredResult->fetch_assoc();
$totalFiltered = $totalFilteredRow['total'];

$json_data = array(
    "draw" => intval($_POST['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
);

echo json_encode($json_data);
$conn->close();
?>
