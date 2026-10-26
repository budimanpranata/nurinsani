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
		
		

		
$select_gr_head = mysql_query ("select id_member,angsuran,nama ,setoran,selisih, tanggal  from temp_angsuran where unit ='$userid' and selisih not in('0')");
while($data = mysql_fetch_array($select_gr_head)){

$id_member=$data['id_member'];
$angsuran=$data['angsuran'];
$setoran=$data['setoran'];

$tgl=$data['tanggal'];
$selisih=$data['selisih'];
$kode_trans="BU/$unit-$tgl";
$ket ="Setoran Musawamah $id_member";
$keterangan_transaksi="Setoran Angsuran Musawamah $id_member";


if ($selisih > 0){
					
				$input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl','$id_member','$unit','$id_member','$code_kel',0,'03','$setoran','$unit','$ket','','$cao','1','$tgl','$kode_trans')";
            $inputtrnsk = mysql_query($input);
    
	$input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tgl','$id_member','$unit','$id_member','$code_kel','$angsuran','03',0,'$unit','$ket','','$cao','1','$tgl','$kode_trans')";
            $inputtrnsk = mysql_query($input);
			
			$inputtrnsk= mysql_query("update musawamah set os = os - '$angsuran' where id_member='$id_member'");
			 
			if($inputtrnsk){
				echo "<script>alert('update OS Musawamah Sukses ');</script>";
			}
			
			
			 
			 mysql_query("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tgl','$unit')");
                
				mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl','Bukti SYSTEM','$ket','$selisih','$userid')");
				
				 mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2891000','$tgl','Bukti SYSTEM','$ket','$selisih','$userid')");
				
				
				mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl','Bukti SYSTEM','$ket','$angsuran','$userid')");
				 mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1422000','$tgl','Bukti SYSTEM','$ket',5/100 * '$angsuran','$userid')");
               
                
                mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','1412000','$tgl','Bukti SYSTEM','$ket','$angsuran','$userid')");
                //mysql_query("Update  tunggakan set margin =margin - '$ijaroh', pokok =pokok-'$pokok'where no_anggota  ='$norek'") or die(mysql_error("GAGAL INPUT"));
              mysql_query("insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','41001','$tgl','Bukti SYSTEM','$ket',5/100 * '$angsuran','$userid')");
				
				}else{
	
				$input="insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tgl','Bukti SYSTEM','$ket','$setoran','$userid')";
				 $inputtrnsk = mysql_query($input);	
				$input="insert  into tabel_transaksi_toko(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2891000','$tgl','Bukti SYSTEM','$ket','$setoran','$userid')";
			 $inputtrnsk = mysql_query($input);	
				$input ="Insert into list_toko (BUSS_DATE,NOREK,UNIT,id_member,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
				VALUES('$tgl','$id_member','$unit','$id_member','$code_kel',0,'03','$setoran','$unit','$ket','','$cao','1','$tanggal','$kode_trans')";
            $inputtrnsk = mysql_query($input);					
					
				}
		
		

			
			
			
			}
			

			
			

		
if($update){
echo "<script>alert('update OS Musawamah Sukses ');</script>";
}
		
		if($update){
			echo "<script>alert(' Transaksi Musawamah baru Sukses di import');</script>";
		}else{
			echo mysql_error();
		}
		///////////////////HITUNG LABA-RUGI////////////////////
		//hapus table semporial yang lama
		$hapus_tabel_temporial=mysql_query("delete from temp_angsuran where unit ='$unit'");
		if($hapus_tabel_temporial){
			
		echo "<script>alert('file temporary Sukses di hapus ');</script>";
		
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
			<h2 align="center"><strong>Update Data Angsuran</strong></h2>
			<p align="center">&nbsp;</p>
			<p align="center">
			<p align="center" class="style1"><font color="#666666">
		    Proses ini adalah proses untuk melakuan update data angsuran setelah proses Upload. </font></p>
			<p align="center"><em><font color="#666666">Proses bisa dilakukan setelah semua data di Upload.
		    <?php 
			$cek=mysql_query("select * from temp_angsuran where unit='' and unit ='$unit'");
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

