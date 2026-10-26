
<?php require_once __DIR__ . '/php8_compat.php';
include "conn2.php";
$koneksi=open_connection2();
$select = mysql_query ("Select tgl_murab,cif from loan where hari=' ' ");
while($data = mysql_fetch_array($select)){

$murab=$data['tgl_murab'];
$cif=$data['cif'];

$tanggal = $murab;
    $day = date('D', strtotime($tanggal));
    $dayList = array(
    	'Sun' => 'Minggu',
    	'Mon' => 'Senin',
    	'Tue' => 'Selasa',
    	'Wed' => 'Rabu',
    	'Thu' => 'Kamis',
    	'Fri' => 'Jumat',
    	'Sat' => 'Sabtu'
    );
	

$update=mysql_query ("UPDATE loan  set hari ='$dayList[$day]' where cif ='$cif'");
			}	
			
if ($update){
echo "<script>alert('suksess!!!');</script>";

}else {
	echo "<script>alert('gagal!!!');</script>";
}
?>
