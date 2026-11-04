<?php require_once __DIR__ . '/koneksi_db.php';
$conn = db_ni();
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

if ($aksi == 'tambah') {
    $tanggal = $_POST['tanggal'];
    $cif     = $_POST['cif'];
    $nominal = $_POST['nominal'];

    db_jalankan("INSERT INTO omzet (tanggal, cif, nominal) VALUES (?, ?, ?)", 'sss', $tanggal, $cif, $nominal);
    echo "ok";

} elseif ($aksi == 'edit') {
    $id      = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $cif     = $_POST['cif'];
    $nominal = $_POST['nominal'];

    db_jalankan("UPDATE omzet SET tanggal=?, cif=?, nominal=? WHERE id=?", 'ssss', $tanggal, $cif, $nominal, $id);
    echo "ok";

} elseif ($aksi == 'hapus') {
    $id = $_POST['id'];
    db_jalankan("DELETE FROM omzet WHERE id=?", 's', $id);
    echo "ok";

} elseif ($aksi == 'get') {
    $id = $_POST['id'];
    $data = db_satu("SELECT * FROM omzet WHERE id=?", 's', $id);
    // nilai sebagai string seperti hasil mysqli_query versi lama (JSON tetap "id":"1")
    if ($data) {
        $data = array_map(function ($v) { return $v === null ? null : (string) $v; }, $data);
    }
    echo json_encode($data);
}
?>
