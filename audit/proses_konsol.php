<?php require_once __DIR__ . '/../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
include "../encryption/function.php";
	
	//untuk waktu
	$tanggal=date("y-m-d");
	
	//untuk koneksi database
	include "../conn2.php";
	$koneksi=open_connection2();
	
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:../../param_tgl.php");
}
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../../index.html");
}
	
	
	?>
	<?php

	if(isset($_POST['hitung_shu'])){
		
		

$hapus_tabel_temporial=mysql_query("delete from tabel_master_konsol where unit ='0000' and tanggal_awal ='$tgl_system'");
$hapus_tabel_temporial=mysql_query("delete from tabel_master_kp");
		if($hapus_tabel_temporial){
			//1. Tahap Penginputan
			mysql_query("INSERT INTO tabel_master_kp SELECT * FROM tabel_master where unit ='1010' ");
			$update=mysql_query ("UPDATE tabel_master_kp set unit ='0000',tanggal_awal='$tgl_system'");
			$update=mysql_query ("insert into tabel_master_konsol select * from tabel_master_kp");

			echo "<script>alert('Proses Konsolidasi Sukses ');</script>";
			
					}else{
			echo mysql_error();
		}
			
							
	}else{
		unset($_POST['hitung_shu']);
	}
	?>
	
	<style type="text/css">
	<!--
	.style1 {font-style: italic,
			font-size :20 px
	}
	-->
	</style>

	  <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">

 <script type="text/javascript" src="../js/jquery.min.js"></script>
 <script type="text/javascript" src="../js/bootstrap.min.js"></script>

	
	<div class="post">
	  <div class="entry">
			<h2 align="center"><strong>Proses Konsolidasi</strong></h2>
			<p align="center">&nbsp;</p>
			<p align="center">
			<p align="center" class="style1"><font color="#666666">
			<p align="center"><em><font color="#666666">Tanggal Periode Konsol <?php echo $tgl_system?>.
		    <p align="center"><em><font color="#666666">Proses bisa Setelah Neraca Balance.
			<?php 
			
			$cek=mysql_query("select * from tabel_transaksi where keterangan_posting=''");
			$cek_posting=mysql_num_rows($cek);
			if($cek_posting!==0){
				echo "masih ada yang belum di posting";
			}else{
				?>
				</font></em></p>
				<form action="?<?php echo paramEncrypt('page=../laporan/hitung_shu')?>" method="post" name="postform" id ="myform">
				<div align="center">
				<p>&nbsp;</p><p>
				<font color="#666666">
				<input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="hitung_shu" value="Proses Konsolidasi" class="btn btn-primary" />
				
				</font></p>
				</div>
				</form>
				
				
				
					</div>
				<?php
							
			}

			?>
		    </div>
			</font>
		    <div align="center"><font color="#0066FF">
			
            <?php
			
			if($berhasil){
			echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Sukses </strong> SHU berhasil di Hitung </div>';

			}
			?>		  
	        </font>
	        </p>
            </div>
	  </div>
	</div>
<?php 


?>

