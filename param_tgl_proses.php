<?php require_once __DIR__ . '/koneksi_db.php'; 
require_once __DIR__ . '/inc/notif.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
$tgl_input=date("Y-m-d");
//cek apakah user sudah login
if(!isset($_SESSION['stgl_system'])){
notif_pergi('Maaf, tanggal system belum di setup', 'param_tgl.php', 'warning');

   /* redirect ditangani notif_pergi */
 exit; }
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }

//cek level user
if($_SESSION['level']!="lead"){
   header("location:index.html");
 exit; }
db_ni();
mysql_select_db('nur_insani');
include "function.php";

$tanggal=$tgl_system;
			
			
$cekbox = $_POST['cekbox'];
if ($cekbox) {
       foreach ($cekbox as $value) {
		   
		$select = mysql_query("  select * from param_tgl  WHERE unit ='" . $value . "'");
			while($row = mysql_fetch_array($select)){
				
				$unit=$row['unit'];
$param_tgl=$row['param_tgl'];
				@mysql_query("delete from data_os where buss_date ='$param_tgl' and unit='$unit'") or die(mysql_error("GAGAL INPUT"));
	@mysql_query("Insert into data_os 
SELECT 
'$param_tgl',
unit,
sum(os) as os,
sum(saldo_margin) as saldo_margin,
sum(os-saldo_margin) as saldo_os
FROM `loan`  
where unit='$unit' ") or die(mysql_error("GAGAL INPUT"));
							


				@mysql_query("Update  param_tgl set  param_tgl ='$tanggal' where unit='$unit'") or die(mysql_error("GAGAL INPUT"));

@mysql_query("Update  tabel_master set  tanggal_awal ='$tanggal' where unit='$unit'") or die(mysql_error("GAGAL INPUT"));
				}
			}
@mysql_query("Update  param_tgl set  param_tgl ='$tanggal' where unit='$userid'") or die(mysql_error("GAGAL INPUT"));
			notif_pergi('Proses Ganti Parameter Tanggal Selesai.', 'param_tgl.php');
		
		
	}else {
			notif_pergi('anda belum memilih data.', 'param_tgl.php');
	}

?>
