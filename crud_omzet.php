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

    $query = "INSERT INTO omzet (tanggal, cif, nominal) VALUES ('$tanggal', '$cif', '$nominal')";
    db_query($conn, $query);
    echo "ok";

} elseif ($aksi == 'edit') {
    $id      = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $cif     = $_POST['cif'];
    $nominal = $_POST['nominal'];

    $query = "UPDATE omzet SET tanggal='$tanggal', cif='$cif', nominal='$nominal' WHERE id='$id'";
    db_query($conn, $query);
    $cek_nisbah= db_fetch_array(db_query($conn,"SELECT nisbah from nisbah WHERE cif ='$cif'"));
    $nisbah=$cek_nisbah['nisbah']; 
   $porsi= $nominal * $nisbah;
    $query = "UPDATE pinjaman_detail_mus SET nisbah_anggota='$porsi' WHERE cif='$cif' and tgl_bayar ='$tanggal'";
    db_query($conn, $query);


    echo "ok";

} elseif ($aksi == 'hapus') {
    $id = $_POST['id'];
    db_query($conn, "DELETE FROM omzet WHERE id='$id'");
    echo "ok";

} elseif ($aksi == 'get') {
    $id = $_POST['id'];
    $q = db_query($conn, "SELECT * FROM omzet WHERE id='$id'");
    $data = db_fetch_assoc($q);
    echo json_encode($data);
}
?>
