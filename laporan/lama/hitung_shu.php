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
		
		

$update=mysql_query ("UPDATE tabel_master set saldo_awal ='0',saldo_akhir='0',mut_debet='0',mut_kredit='0' where LINE_BALANCE ='HEADING' and unit ='$unit' and kode_rekening not in ('2500000')");
$update=mysql_query ("update tabel_master set saldo_awal = awal_debet - awal_kredit where gr_neraca ='AKTIVA' and unit ='$unit'");
$update=mysql_query ("update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where gr_neraca ='AKTIVA' and unit ='$unit'");

//PASIVA
$update=mysql_query ("update tabel_master set saldo_awal =awal_kredit - awal_debet where gr_neraca ='PASIVA' and unit ='$unit'");
$update=mysql_query ("update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where gr_neraca ='PASIVA'and unit ='$unit'");


//Pedndapatan
$update=mysql_query ("update tabel_master set saldo_awal = awal_kredit - awal_debet where kode_rekening between '40000'and '42399' and unit ='$unit'");
$update=mysql_query ("update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where kode_rekening between '40000'and '42399' and unit ='$unit'");

//Biaya
$update=mysql_query ("update tabel_master set saldo_awal = awal_debet - awal_kredit where kode_rekening between '50000'and '56999' and unit ='$unit'");
$update=mysql_query ("update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where kode_rekening between '50000'and '56999' and unit ='$unit'");

//Pendapatan Lainyya
$update=mysql_query ("update tabel_master set saldo_awal = awal_kredit - awal_debet where kode_rekening between '57000'and '57501' and unit ='$unit'");
$update=mysql_query ("update tabel_master set saldo_akhir = sisa_kredit - sisa_debet where kode_rekening between '57000'and '57501' and unit ='$unit'");

//BEBAN 
$update=mysql_query ("update tabel_master set saldo_awal = awal_debet - awal_kredit where kode_rekening between '58000'and '59999' and unit ='$unit'");
$update=mysql_query ("update tabel_master set saldo_akhir = sisa_debet - sisa_kredit where kode_rekening between '58000'and '59999' and unit ='$unit'");

if($update){
echo "<script>alert('update Saldo awal saldo akhir Sukses ');</script>";
}
		
$select = mysql_query ("Select kode_rekening,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit, 
sum(saldo_awal) as saldo_awal, sum(saldo_akhir) as saldo_akhir from tabel_master where unit ='$unit' and gr_head > 0 group by gr_head ");
while($data = mysql_fetch_array($select)){

$saldo_awal=$data['saldo_awal'];
$saldo_akhir=$data['saldo_akhir'];
$mut_debet=$data['mut_debet'];
$mut_kredit=$data['mut_kredit'];
$kode_rekening=$data['kode_rekening'];

$update=mysql_query ("UPDATE tabel_master set saldo_awal ='$saldo_awal',saldo_akhir='$saldo_akhir',mut_debet='$mut_debet',mut_kredit='$mut_kredit' where kode_rekening ='$kode_rekening' and unit ='$unit'");
			}

$select = mysql_query ("Select kode_rekening,sum(mut_debet) as mut_debet, sum(mut_kredit) as mut_kredit, 
sum(saldo_awal) as saldo_awal, sum(saldo_akhir) as saldo_akhir from tabel_master where unit ='$unit' and gr_sub > 0  group by gr_sub ");
while($data = mysql_fetch_array($select)){

$saldo_awal=$data['saldo_awal'];
$saldo_akhir=$data['saldo_akhir'];
$mut_debet=$data['mut_debet'];
$mut_kredit=$data['mut_kredit'];
$kode_rekening=$data['kode_rekening'];

$update=mysql_query ("UPDATE tabel_master set saldo_awal ='$saldo_awal',saldo_akhir='$saldo_akhir',mut_debet='$mut_debet',mut_kredit='$mut_kredit' where kode_rekening ='$kode_rekening' and unit ='$unit'");
			}	
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
//Pedndapatan
$update=mysql_query("update tabel_master set saldo_jalan = mut_kredit - mut_debet where kode_rekening between '40000'and '42399' and unit ='$unit'");

//Biaya
$update=mysql_query("update tabel_master set saldo_jalan = mut_debet - mut_kredit where kode_rekening between '50000'and '56999' and unit ='$unit'");

//Pendapatan Lainyya
$update=mysql_query("update tabel_master set saldo_jalan = mut_kredit - mut_debet where kode_rekening between '57000'and '57501' and unit ='$unit'");

//BEBAN 
$update=mysql_query("update tabel_master set saldo_jalan = mut_debet - mut_kredit where kode_rekening between '58000'and '58999' and unit ='$unit'");

			
			
			
			}else{
				//update neraca
				$update=mysql_query("update tabel_master set nrc_debet='$sisa_debet', nrc_kredit='$sisa_kredit' where kode_rekening='$kode_rekening'  and unit ='$unit'");
			}
		}
		
		//jika sudah selesai update
		if($update){
			//$biaya=mysql_fetch_array(mysql_query("select sum(rl_debet) as biaya from tabel_master where normal='debet' and posisi='rugi-laba' and gr_head='50000' and unit ='$unit'"));
			//$pendapatan=mysql_fetch_array(mysql_query("select sum(rl_kredit) as pendapatan from tabel_master where normal='kredit' and posisi='rugi-laba' and gr_head='40000' and unit ='$unit'"));
			//hitung SHU
			//$shu=$pendapatan['pendapatan']-$biaya['biaya'];
		}
		//update rugi laba debet dan neraca kredit dengan SHU
		$total_pendapan=mysql_fetch_array(mysql_query("select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit ='$unit' and kode_rekening='40000'"));
$total_biaya=mysql_fetch_array(mysql_query("select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit ='$unit' and kode_rekening='50000'"));


$shu_ops_awal=$total_pendapan['saldo_awal'] - $total_biaya['saldo_awal'];
$shu_ops_jalan=$total_pendapan['saldo_jalan'] - $total_biaya['saldo_jalan'];
$shu_ops_akhir=$total_pendapan['saldo_akhir'] - $total_biaya['saldo_akhir'];

$total_pendapan_n=mysql_fetch_array(mysql_query("select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit ='$unit' and kode_rekening='57000'"));
$total_beban=mysql_fetch_array(mysql_query("select saldo_jalan,saldo_awal,saldo_akhir from tabel_master where unit ='$unit' and kode_rekening='58000'"));


$shu_non_ops_awal=$total_pendapan_n['saldo_awal'] - $total_beban['saldo_awal'];
$shu_non_ops_jalan=$total_pendapan_n['saldo_jalan'] - $total_beban['saldo_jalan'];
$shu_ops_non_akhir=$total_pendapan_n['saldo_akhir'] - $total_beban['saldo_akhir'];
$total_biaya_beba=$total_biaya['saldo_akhir'] + $total_beban['saldo_akhir'];
$total_pendapantan_dan_n=$total_pendapan_n['saldo_akhir']+$total_pendapan['saldo_akhir'];

$shu_sb_pajak_awal=$shu_ops_awal + $shu_non_ops_awal;
$shu_sb_pajak_jalan=$shu_ops_jalan + $shu_non_ops_jalan;
$shu_sb_pajak_akhir=$shu_ops_akhir + $shu_ops_non_akhir;
		
		
$update_shu=mysql_query("update tabel_master set saldo_awal='', mut_debet='$total_biaya_beba',mut_kredit='$total_pendapantan_dan_n',saldo_akhir='$shu_sb_pajak_akhir', saldo_jalan='$shu_sb_pajak_jalan' where kode_rekening='3902000' and unit ='$unit'");
		
if($update_shu){
echo "<script>alert('update Total SHU Sukses ');</script>";
}
		
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
			mysql_query("INSERT INTO tabel_rugi_laba SELECT * FROM tabel_master where gr_neraca='rugi-laba' and unit ='$unit'");
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

