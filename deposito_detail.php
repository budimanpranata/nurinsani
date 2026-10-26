
<?php require_once __DIR__ . '/php8_compat.php';
include "conn2.php";
$koneksi=open_connection2();
$select = mysql_query ("Select * from deposito where norek ='10102002180003'");
while($data = mysql_fetch_array($select)){

$tgl=$data['star_date'];
$maturity_date=$data['maturity_date'];
$norek=$data['norek'];
$cif=$data['cif'];

$awal_hari = $tgl;
$akhir_hari = $maturity_date;
$x=0;
while (strtotime($awal_hari) < strtotime($akhir_hari)) {
$x ++;
$awal_hari= date ("Y-m-d", strtotime("+1 month", strtotime($awal_hari)));
$detail= mysql_query("INSERT INTO deposito_detail (id,id_deposito,cif,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values('','$norek','$cif','$x','$awal_hari','$awal_hari','0','$norek','')") or die(mysql_error());

}
}
if ($detail){
echo "<script>alert('suksess!!!');</script>";
}else {
echo "<script>alert('gagal!!!');</script>";
}
?>
