<?php require_once __DIR__ . '/php8_compat.php';
$server="loclhost,1433";
$username="sa";
$password="Btpns";

$koneksi=mssql_connect($server,$username,$password);
$sqldb=mssql_select_db("Dbkanwil",$koneksi);

if($koneksi)
{
echo "koneksi sukses..!!";
}
else 
	{
echo "koneksi gagal";
}
?>