<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];

include "../encryption/function.php";
	
	//untuk waktu
	$tanggal=date("y-m-d");
	
	//untuk koneksi database
	include "../conn2.php";
	$koneksi=open_connection2();
	
	?>
	<?php
	if(isset($_POST['hitung_shu'])){
		
		

$hapus_tabel_temporial=mysql_query("DELETE tabel_master_hari_ah 
FROM tabel_master_hari_ah 
INNER JOIN branch ON branch.kode_branch = tabel_master_hari_ah.unit
WHERE branch.code_area = '$userid'");
		if($hapus_tabel_temporial){
			
			echo "<script>alert('Temporary sukses di buat ');</script>";
			
					}else{
			echo mysql_error();
		}

$update=mysql_query("insert into tabel_master_hari_ah(rekening_gl,kode_rekening,unit,nama_rekening,tanggal_awal,awal_debet,
awal_kredit,mut_debet,mut_kredit,sisa_debet,sisa_kredit,posisi,normal,gr_head,gr_sub,gr_neraca,saldo_awal,saldo_akhir,LINE_BALANCE) select rekening_gl,kode_rekening,tabel_master.unit as unit,nama_rekening,tanggal_awal,awal_debet,
awal_kredit,mut_debet,mut_kredit,sisa_debet,sisa_kredit,posisi,normal,gr_head,gr_sub,gr_neraca,saldo_awal,saldo_akhir,LINE_BALANCE from tabel_master LEFT JOIN branch on branch.kode_branch = tabel_master.unit
WHERE branch.code_area ='$userid'");
				
		//jika sudah selesai update
		if($update){
			
		
		$update_shu=mysql_query("UPDATE tabel_master_hari_ah
INNER JOIN branch ON branch.kode_branch = tabel_master_hari_ah.unit
SET tabel_master_hari_ah.tanggal_awal = '$tgl_system'
WHERE branch.code_area = '$userid'");
}	

	if($update_shu){
			$berhasil="Neraca Konsol Sukses di buat";		
		}else{
			echo mysql_error();
		}
		///////////////////HITUNG LABA-RUGI////////////////////
		//hapus table semporial yang lama
			
							
	}else{
		unset($_POST['hitung_shu']);
	}
	?>
	
	<style type="text/css">
	<!--
	.style1 {font-style: italic}
	-->
	</style>

	  <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">

 <script type="text/javascript" src="../js/jquery.min.js"></script>
 <script type="text/javascript" src="../js/bootstrap.min.js"></script>

	
	<div class="post">
	  <div class="entry">
			<h2 align="center"><strong>Hitung Konsolidasi</strong></h2>
			<p align="center">&nbsp;</p>
			<p align="center">
			<p align="center" class="style1"><font color="#666666">
			<p align="center"><em><font color="#666666">Proses bisa dilakukan setelah semua data diposting.
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
				<input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="hitung_shu" value="Proses" class="btn btn-primary" />
				
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

