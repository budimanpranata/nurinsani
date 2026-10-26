<?php require_once __DIR__ . '/php8_compat.php';
error_reporting(E_ALL);
ini_set('display_errors',1);
header('Content-Type: application/json');

// Koneksi ke database (prosedural)
$conn = mysqli_connect("192.168.88.144", "adminni", "147Teriyak!", "system");
if (!$conn) {
    echo json_encode(["valid" => false, "url" => null, "error" => "DB gagal"]);
    exit;
}


$response = array("valid" => false, "url" => null, "msg" => "");

$nik  = isset($_GET['nik']) ? $_GET['nik'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($nik != '' && $type != '') {
    $nik  = mysqli_real_escape_string($conn, $nik);
    $type = mysqli_real_escape_string($conn, $type);

    $row = null;

    // --- 1. Cek di tabel pembiayaan ---
    $sql   = "SELECT * FROM pembiayaan WHERE nik = '$nik' LIMIT 1";
    $query = db_query($conn, $sql);

    if ($query && db_num_rows($query) > 0) {
        $row = db_fetch_assoc($query);
    } else {
        // --- 2. Jika tidak ada, cek di tabel berkas ---
        $sql   = "SELECT * FROM berkas_pembiayaan WHERE nik = '$nik' LIMIT 1";
        $query = db_query($conn, $sql);

        if ($query && db_num_rows($query) > 0) {
            $row = db_fetch_assoc($query);
        }
    }

    // --- 3. Jika ketemu di salah satu tabel ---
    if ($row) {
        $response["valid"] = true;
        if (!empty($row[$type])) {
            // hapus "/" depan jika ada
            $path = ltrim($row[$type], '/');
            $response["url"] = "http://192.168.88.144/berkas/" . $path;
        } else {
            $response["msg"] = "Kolom '$type' kosong";
        }
    } else {
        // --- 4. Tidak ketemu di dua tabel ---
        $response["msg"] = "Data tidak ditemukan";
    }
} else {
    $response["msg"] = "Parameter nik/type kosong";
}

echo json_encode($response);
?>

