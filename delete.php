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


$cif = $_GET['cif'];
$jenis = $_GET['jenis'];


if ($jenis =="lima"){
    @db_query($config_dua,"delete pull_data, tagihan_lima_persen from pull_data inner join tagihan_lima_persen on tagihan_lima_persen.cif = pull_data.cif  where pull_data.cif = '$cif' ");
}elseif($jenis =="lebaran"){
    @db_query($config_dua,"delete pull_data, tagihan_lebaran from pull_data inner join tagihan_lebaran on tagihan_lebaran.cif = pull_data.cif  where pull_data.cif = '$cif' ");
}elseif($jenis =="pelunasan"){
    @db_query($config_dua,"delete pull_data, tagihan_pelunasan from pull_data inner join tagihan_pelunasan on tagihan_pelunasan.cif = pull_data.cif  where pull_data.cif = '$cif' ");
}elseif($jenis =="penarikan"){
    @db_query($config_dua,"delete pull_data, tagihan_penarikan from pull_data inner join tagihan_penarikan on tagihan_penarikan.cif = pull_data.cif  where pull_data.cif = '$cif' ");
}elseif($jenis =="pelunasan19"){
    @db_query($config_dua,"delete pull_data, tagihan_pelunasan from pull_data inner join tagihan_pelunasan on tagihan_pelunasan.cif = pull_data.cif  where pull_data.cif = '$cif' ");
}elseif($jenis =="pelunasanDO"){
    @db_query($config_dua,"delete pull_data, tagihan_pelunasan from pull_data inner join tagihan_pelunasan on tagihan_pelunasan.cif = pull_data.cif  where pull_data.cif = '$cif' ");
}


echo "<script>alert('hapus pull data $cif sukses ');</script>";
echo "<script language='javascript'>document.location.href='mob_pull_data.php'</script>";

?>
