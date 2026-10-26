<html>
<head>
<meta http-equiv=”Content-Type” content=”text/html; charset=UTF-8?>
<title>aaa</title>
</head>
<body>
<?php require_once __DIR__ . '/../php8_compat.php';
 include "conn.php";
  $koneksi=open_connection();
?>
<form name=”frmPegawai” method=”post” action=””>
Pilih Area :
<select name=”pilihPegawai” onclick=”javascript:
var val = frmPegawai.pilihPegawai.value;
if(val!=”){
var data = val.split(‘/’, 2);
frmPegawai.txtTglLahir.value=data[0];
frmPegawai.txtTemLahir.value=data[1];
}else{
frmPegawai.txtTglLahir.value=”;
frmPegawai.txtTemLahir.value=”;
}
“>
<option value=””>-Pilih-</option>
<?php
$query= mssql_query ("select branch, GroupBranch from Branch Group by GroupBranch");
$data   = mssql_fetch_array($query); 
$area=$row['GroupBranch'];
?>
<option  value="<?php echo ($area); ?></option>
<?php

?>
</select><br/>
Tanggal Lahir : <input name="txtTglLahir" type="text"/><br/>
Tempat Lahir : <input name="txtTemLahir" type="text"/>
</form>
</body>
</html>