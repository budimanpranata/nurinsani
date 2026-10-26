<?php require_once __DIR__ . '/../koneksi_db.php';
$conn = db_ni();
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

if ($aksi == 'tambah') {
    $tanggal = $_POST['tanggal'];
    $cif     = $_POST['cif'];
    $nominal = $_POST['nominal'];

    $query = "INSERT INTO omzet (tanggal, cif, nominal) VALUES ('$tanggal', '$cif', '$nominal')";
    db_query($conn, $query);
    echo "ok";

} elseif ($aksi == 'edit') {
    $id      = $_POST['id'];
    $debet = $_POST['debet'];
$tanggal = $_POST['tanggal'];
    $kredit     = $_POST['kredit'];
    $kode_rekening = $_POST['kode_rekening'];

    $query = "UPDATE tabel_transaksi SET debet='$debet', kredit='$kredit', kode_rekening='$kode_rekening',tanggal_transaksi='$tanggal' WHERE id_transaksi='$id'";
    db_query($conn, $query);
    echo "ok";

} elseif ($aksi == 'hapus') {
    $id = $_POST['id'];
    db_query($conn, "DELETE FROM tabel_transaksi WHERE id_transaksi='$id'");
    echo "ok";

} elseif ($aksi == 'get') {
    $id = $_POST['id'];
    $q = db_query($conn, "SELECT id_transaksi as id, DATE(tanggal_transaksi) as tanggal,unit, kode_transaksi, kode_rekening, jenis_transaksi, keterangan_transaksi, debet,kredit from tabel_transaksi WHERE id_transaksi='$id'");
    $data = db_fetch_assoc($q);
    echo json_encode($data);
}
?>
