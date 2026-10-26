<?php require_once __DIR__ . '/../../php8_compat.php';
include "conn.php";
$koneksi=open_connection();

echo "<script language=\"JavaScript\" src=\"js/form_validation.js\"></script>";

echo "<select name='kecamatan' class='cmb'>";
echo "<option value=\"\">- Select salah-</option>";
$query = "select branch, keterangan from branch where region='$_GET[kode]'";
$result = mssql_query($query);
while ($kec=mssql_fetch_array($result))
	{
		echo "<option value=$kec[0]>$kec[1]</option>";
	}
echo "</select>";
?>
