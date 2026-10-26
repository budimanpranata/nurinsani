<?php require_once __DIR__ . '/../../php8_compat.php'; 
function open_connection(){

	//konfigurasi
	$host="localhost,1433";
	$user="sa";
	$pass="Btpns";
	$db="DBKanwil";
	
	//koneksi database
	$koneksi=mssql_connect($host,$user,$pass);
	mssql_select_db($db,$koneksi);
	
	return $koneksi;
}
?>
