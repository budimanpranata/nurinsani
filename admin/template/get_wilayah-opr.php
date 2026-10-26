<?php require_once __DIR__ . '/../../php8_compat.php';
include "conn.php";
$koneksi=open_connection();

echo "<script language=\"JavaScript\" src=\"js/form_validation.js\"></script>";

echo "<select name='wilayah'  class='cmb' onChange='DinamisWilayah(this);'>";
echo "<option value=\"\">- Select Branch -</option>";
$query = "select Branch, keterangan from branch where GroupBranch='$_GET[kode]'";
$result = mssql_query($query);
while ($wilayah=mssql_fetch_array($result))
    {
        echo "<option value=$wilayah[0]>$wilayah[1]</option>";
    }
echo "</select>";
?>
