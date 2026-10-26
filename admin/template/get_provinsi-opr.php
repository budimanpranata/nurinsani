<?php require_once __DIR__ . '/../../php8_compat.php';
include "conn.php";
$koneksi=open_connection();

echo "<script language=\"JavaScript\" src=\"js/form_validation.js\"></script>";

echo "<select name='provinsi' onChange='DinamisProvinsi(this);' class='cmb'>";
echo "<option value=\"\">- Select Area -</option>";
$query = "select GroupBranch, area from area where region='$_GET[kode]'";
$result = mssql_query($query);
while ($provinsi=mssql_fetch_array($result))
	{
		echo "<option value=$provinsi[0]>$provinsi[1]</option>";
	}
echo "</select>";
?>
