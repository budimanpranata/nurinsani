<?php require_once __DIR__ . '/../php8_compat.php'; 
session_start();

$userid=$_SESSION['userid'];
$tgl_system=$_SESSION['stgl_system'];
	
	//untuk waktu
	$tanggal=date("y-m-d");
	
	//untuk koneksi database
	include "conn2.php";
	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}
	
?>
	<?php
	if(isset($_POST['eod'])){
	
		///////////////////////// CEK COA DAN NOMINATIVE /////////////////////
			$simpanan=mysql_fetch_array(mysql_query("select sum(Kredit) as saldo_wadiah from simpanan where unit ='$unit' and buss_date='$tgl_system' and type not in ('04','05') "));
			$simpanan=$simpanan['saldo_wadiah'];
			$wadiah=mysql_fetch_array(mysql_query("select sum(kredit) as wadiah from tabel_transaksi where unit ='$unit' and kode_rekening='3102000' and tanggal_transaksi='$tgl_system'"));
			$wadiah=$wadiah['wadiah'];
			$os=mysql_fetch_array(mysql_query("select sum(os) as os from loan where unit ='$unit'"));
			$os=$os['os'];
			$os=mysql_fetch_array(mysql_query("select sisa as os from loan where unit ='$unit'"));
			$os_margin=mysql_fetch_array(mysql_query("select sum(debet) as os from rek_loan where unit ='$unit' and tgl_realisasi='$tgl_system'"));
			$os_margin=$os_margin['os'];
			$piutang_murab=mysql_fetch_array(mysql_query("select sum(kredit) as piutang_murab from tabel_transaksi where unit ='$unit' and kode_rekening='1413000' and tanggal_transaksi='$tgl_system'"));
			$piutang_murab=$piutang_murab['piutang_murab'];
			
			if($simpanan<>$wadiah){
				echo "<script>alert('Maaf Salso Simpanan tidak sama dengan jurnal wadiah');</script>";
				echo "<script language='javascript'>document.location.href='eod.php'</script>";
			}elseif($os_margin<>$piutang_murab){
					echo "<script>alert('Maaf Debet OS  tidak sama dengan jurnal Piutang Murabahah');</script>";
				echo "<script language='javascript'>document.location.href='eod.php'</script>";
				}else{
				$update=mysql_query("update param_tgl set param_tgl='$tgl_system' and unit ='$unit'");
				if($update){
				$berhasil="AKhir Hari Sukses...";		
				}else{
					echo mysql_error();
			}
				}
			
				
							
	}else{
		unset($_POST['eod']);
	}
	?>
	
	<style type="text/css">
	<!--
	.style1 {font-style: italic}
	-->
	</style>

	  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

 <script type="text/javascript" src="js/jquery.min.js"></script>
 <script type="text/javascript" src="js/bootstrap.min.js"></script>

	
	<div class="post">
	  <div class="entry">
			<h2 align="center"><strong>Akhir Hari</strong></h2>
			<p align="center">&nbsp;</p>
			<p align="center">
						<p align="center"><em><font color="#666666">Proses bisa dilakukan setelah semua data diposting.
		    <?php 
			$cek=mysql_query("select * from tabel_transaksi where keterangan_posting='' and unit ='$unit' and tanggal_transaksi ='$tgl_system'");
			$cek_posting=mysql_num_rows($cek);
			if($cek_posting!==0){
				echo "masih ada yang belum di posting";
			}else{
				?>
				</font></em></p>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="postform" id ="myform">
				<div align="center">
				<p>&nbsp;</p><p>
				<font color="#666666">
				<input type="submit" onclick="return confirm('Apakah Anda Yakin?')" name="eod" value="Proses" class="btn btn-primary" />
				
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
			echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Sukses </strong> EOD Sukses </div>';

			}
			?>		  
	        </font>
	        </p>
            </div>
	  </div>
	</div>
<?php 


?>

