<?php require_once __DIR__ . '/koneksi_db.php';

$host_dua   ="76.13.20.176";
$user_dua ="admin";
$password_dua ="";
$db_dua ="mobcol";

$config_dua =  db_mobcol();
if(mysqli_connect_errno())
{
echo'Gagal :'.mysqli_connect_error();
}


$cif = isset($_GET['cif']) ? (string) $_GET['cif'] : '';
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';

// tabel tagihan per jenis; cif dikirim sebagai parameter (prepared statement)
$tabel_tagihan = array(
    'lima' => 'tagihan_lima_persen',
    'lebaran' => 'tagihan_lebaran',
    'pelunasan' => 'tagihan_pelunasan',
    'penarikan' => 'tagihan_penarikan',
    'pelunasan19' => 'tagihan_pelunasan',
    'pelunasanDO' => 'tagihan_pelunasan',
);
if (isset($tabel_tagihan[$jenis])) {
    $t = $tabel_tagihan[$jenis];
    @db_hasil_di($config_dua, "delete pull_data, $t from pull_data inner join $t on $t.cif = pull_data.cif  where pull_data.cif = ? ", 's', $cif);
}


echo "<script>alert(" . json_encode("hapus pull data $cif sukses ", JSON_HEX_TAG) . ");</script>";
echo "<script language='javascript'>document.location.href='mob_pull_data.php'</script>";

?>
