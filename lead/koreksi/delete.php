<?php require_once __DIR__ . '/../../php8_compat.php';
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
if(isset($_GET['d'])){
     $id = $_GET['d'];
	 

 
$result = mysql_query("select cif,type,buss_date FROM simpanan WHERE kode_transaksi='$id'");
while($row = mysql_fetch_array($result)){
$cif=$row['cif'];
$type=$row['type'];
$buss_date=$row['buss_date'];
}

mysql_query("DELETE FROM tabel_transaksi WHERE kode_transaksi='$id'");
	
	 
if ($type =='03'){
$q=mysql_query("update loan set os = os + angsuran, saldo_margin=saldo_margin + ijaroh, ke = ke - 1, run_tenor = run_tenor - 1  WHERE cif='$cif'");
$q=mysql_query("insert into simpanan_delete select * FROM simpanan WHERE kode_transaksi='$id'");
$q=mysql_query("DELETE FROM simpanan WHERE kode_transaksi='$id'");
$q=mysql_query("DELETE FROM rek_loan WHERE cif='$cif' and tgl_realisasi='$buss_date'");

}else{	
$q=mysql_query("DELETE FROM simpanan WHERE kode_transaksi='$id'");
$q=mysql_query("DELETE FROM simpanan_pokok WHERE kode_transaksi='$id'");
$q=mysql_query("DELETE FROM simpanan_wajib WHERE kode_transaksi='$id'");
     }
 
     if($q){
	 
	echo "<script>alert('Sukses')</script>";          
	echo "<script>location.href='koreksi.php'</script>";
     }else{
          echo "<script>alert('GAGAL')</script>";
     }
}
?>
