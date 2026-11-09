<?php require_once __DIR__ . '/../../php8_compat.php';
require_once __DIR__ . '/../../inc/csrf.php';
session_start();  
ob_start();
$iduser=$_SESSION['userid'];
if(!isset($_SESSION['userid'])){
   header("location:../../index.html");
 exit; }

//cek level user
if($_SESSION['level']!="lead"){
   header("location:../../index.html");
 exit; }

include "../../conn2.php";
$koneksi=open_connection2(); 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['d'])) {
     csrf_wajib();
     $id = (string) $_POST['d'];
	 

 
$result = db_hasil("select cif,type,buss_date FROM simpanan WHERE kode_transaksi=?", 's', $id);
while($row = mysql_fetch_array($result)){
$cif=$row['cif'];
$type=$row['type'];
$buss_date=$row['buss_date'];
}

db_jalankan("DELETE FROM tabel_transaksi WHERE kode_transaksi=?", 's', $id);
	
	 
if ($type =='03'){
$q=db_jalankan("update loan set os = os + angsuran, saldo_margin=saldo_margin + ijaroh, ke = ke - 1, run_tenor = run_tenor - 1  WHERE cif=?", 's', $cif);
$q=db_jalankan("insert into simpanan_delete select * FROM simpanan WHERE kode_transaksi=?", 's', $id);
$q=db_jalankan("DELETE FROM simpanan WHERE kode_transaksi=?", 's', $id);
$q=db_jalankan("DELETE FROM rek_loan WHERE cif=? and tgl_realisasi=?", 'ss', $cif, $buss_date);

}else{	
$q=db_jalankan("DELETE FROM simpanan WHERE kode_transaksi=?", 's', $id);
$q=db_jalankan("DELETE FROM simpanan_pokok WHERE kode_transaksi=?", 's', $id);
$q=db_jalankan("DELETE FROM simpanan_wajib WHERE kode_transaksi=?", 's', $id);
     }
 
     if($q){
	 
	echo "<script>alert('Sukses')</script>";          
	echo "<script>location.href='koreksi.php'</script>";
     }else{
          echo "<script>alert('GAGAL')</script>";
     }
}
?>
