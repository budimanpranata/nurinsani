
<?php require_once __DIR__ . '/php8_compat.php';
include "conn2.php";
$koneksi=open_connection2();
$select = mysql_query ("Select tgl_murab,cif,pokok,no_anggota,ijaroh,unit,cao,tenor,hari,
DATE_ADD(tgl_murab,INTERVAL tenor WEEK) as maturity_datee,maturity_date from loan where unit ='005' and os > 0");
while($data = mysql_fetch_array($select)){


$mat=$data['maturity_date'];
$cif=$data['cif'];
$hari=$data['hari'];


$tgl_wakal    = strtotime($mat);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_ress =  date("Y-m-d", strtotime("+2 WEEK", $tgl_m));

$cek_tanggal= mysql_fetch_array(mysql_query("SELECT COUNT(tgl_libur) as libur from param_libur where tgl_libur between '$mat' and '$maturity_date_ress' and Hari='$hari'"));
$libur =$cek_tanggal['libur'];

if ($libur>0){

$ft =2+$libur;
$tgl_wakal    = strtotime($mat);
        $murab1     = date("Y-m-d", strtotime("+7 day", $tgl_wakal));
        $tgl_m    = strtotime($murab1);
        $maturity_date_res =  date("Y-m-d", strtotime("+$ft WEEK", $tgl_m));

$maturity_date_res=$$maturity_date_res;

}else{
$maturity_date_res=$maturity_date_ress;
}




@mysql_query("update loan set maturity_date ='$maturity_date_res' where cif ='$cif'");



}			

echo "<script>alert('suksess!!!');</script>";
echo "Jumlah Libur : $libur";
echo "<br>";
echo "Jatuh tempo: .$maturity_date_res";
var_dump ($maturity_date_ress);


?>
