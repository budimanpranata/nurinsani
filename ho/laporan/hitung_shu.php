<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();

$userid=$_SESSION['userid'];

	include "../encryption/function.php";
	
	//untuk waktu
	$tanggal=date("y-m-d");
	
	//untuk koneksi database
	include "../conn2.php";
	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}
	
?>
	<?php
	if(isset($_POST['hitung_shu'])){
	
		///////////////////////// HITUNG SHU /////////////////////
		$master=mysql_query("select * from tabel_master where unit ='$unit'");
		while($row=mysql_fetch_array($master)){
			$posisi=$row['posisi'];
			$sisa_debet=$row['sisa_debet'];
			$sisa_kredit=$row['sisa_kredit'];
			$kode_rekening=$row['kode_rekening'];
			
			if($posisi=='rugi-laba'){
				//update rugi laba
				$update=mysql_query("update tabel_master set rl_debet='$sisa_debet', rl_kredit='$sisa_kredit' where kode_rekening='$kode_rekening' and unit ='$unit'");
			}else{
				//update neraca
				$update=mysql_query("update tabel_master set nrc_debet='$sisa_debet', nrc_kredit='$sisa_kredit' where kode_rekening='$kode_rekening'  and unit ='$unit'");
			}
		}
		
		
		//jika sudah selesai update
		if($update){
			$biaya=mysql_fetch_array(mysql_query("select sum(rl_debet) as biaya from tabel_master where normal='debet' and posisi='rugi-laba' and kode_rekening<>'314.01' and unit ='$unit'"));
			$pendapatan=mysql_fetch_array(mysql_query("select sum(rl_kredit) as pendapatan from tabel_master where normal='kredit' and posisi='rugi-laba' and unit ='$unit'"));
			
			//hitung SHU
			$shu=$pendapatan['pendapatan']-$biaya['biaya'];
		}
		

		//update rugi laba debet dan neraca kredit dengan SHU
		$update_shu=mysql_query("update tabel_master set rl_debet='$shu', nrc_kredit='$shu' where kode_rekening='314.01' and unit ='$unit'");
		
		if($update_shu){
			$berhasil="Neraca dan Rugi Laba Berhasil dihitung";		
		}else{
			echo mysql_error();
		}
		
		
		///////////////////HITUNG LABA-RUGI////////////////////
		//hapus table semporial yang lama
		$hapus_tabel_temporial=mysql_query("delete from tabel_rugi_laba where unit ='$unit'");
		if($hapus_tabel_temporial){
			//1. Tahap Penginputan
			mysql_query("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where tabel_master.kode_rekening between '41000' and '41281' and unit ='$unit'");
			
						mysql_query("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where tabel_master.kode_rekening between '42100' and '42399' and unit ='$unit'");

		
			mysql_query("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where tabel_master.kode_rekening between '50000' and '51309'  and unit ='$unit'");
			
		
			mysql_query("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where tabel_master.kode_rekening between '52100' and '56999'  and unit ='$unit'");
			
		
			mysql_query("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where tabel_master.kode_rekening between '57000' and '57501' and unit ='$unit'");
			

			mysql_query("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where tabel_master.kode_rekening between '58000' and '58999' and unit ='$unit'");

	
			mysql_query("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where tabel_master.kode_rekening = '58999' and unit ='$unit'");


			

		}else{
			echo mysql_error();
		}
							
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
			<h2 align="center"><strong>Hitung Rugi-Laba</strong></h2>
			<p align="center">&nbsp;</p>
			<p align="center">
			<p align="center" class="style1"><font color="#666666">
		    Proses ini adalah proses untuk menghasilkan laporan keuangan yaitu menghitung Rugi Laba. </font></p>
			<p align="center"><em><font color="#666666">Proses bisa dilakukan setelah semua data diposting.
		    <?php 
			$cek=mysql_query("select * from tabel_transaksi where keterangan_posting='' and unit ='$unit'");
			$cek_posting=mysql_num_rows($cek);
			if($cek_posting!==0){
				//echo "masih ada yang belum di posting";
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

