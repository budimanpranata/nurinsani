<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
$useridd=$_SESSION['userid'];

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
		
		

		
$select_gr_head = mysql_query ("select id_cust,sum(total) as oss,Tenor from temp_toko left join musawamah on musawamah.id_member = temp_toko.id_cust where cash_crdt ='Credit' 
and temp_toko.unit ='$useridd' group by id_cust ");
while($data = mysql_fetch_array($select_gr_head)){

$id_cust=$data['id_cust'];
$os=$data['oss'];
$tenor=$data['Tenor'];

if($tenor > 0){
$tenor=$tenor;
}else{
$tenor =4;
}

$update=mysql_query ("UPDATE musawamah set os ='$os' + os where id_member ='$id_cust'");
$update=mysql_query ("UPDATE musawamah set angsuran = os / '$tenor' where id_member ='$id_cust'");
$update=mysql_query ("UPDATE musawamah set saldo_margin = os * 5/100, ijaroh =angsuran * 5/100 where id_member ='$id_cust'");

			}
			
			$select_gr_head = mysql_query ("select tgl,id_cust,id_outlet,nm_cust,sum(total) as os from  temp_toko 
where cash_crdt ='Credit' and id_cust not in (select id_member from musawamah where musawamah.id_member = temp_toko.id_cust) and unit ='$useridd' group by id_cust ");
while($data = mysql_fetch_array($select_gr_head)){

$id_member=$data['id_cust'];
$tgl=$data['tgl'];
$os=$data['os'];
$angsuran=$os/4;
$saldo_margin=$os*5/100;
$ijaroh=$saldo_margin/4;
$Cust_Short_name=mysql_real_escape_string($data['nm_cust']);
$id_outlet=$data['id_outlet'];
$code_musa="$id_outlet$id_member";
if ($id_outlet=='WH03'){
			$userid='3001';
			
			}elseif($id_outlet=='SRI002'){
			$userid='3002';
			}
elseif($id_outlet=='SRI003'){
			$userid='3003';
			}
//$row_sal= mysql_fetch_array(mysql_query("select klmpk,hari_tagih code_ao from member  WHERE id_cust ='$id_member'")) or die(mysql_error());
				//$code_kel=$row_sal['klmpk'];
				//$cao=$row_sal['code_ao'];
				//$hari=$row_sal['hari_tagih'];

$update=mysql_query ("insert into musawamah values('$tgl','$code_kel','$id_member','$id_member','$Cust_Short_name','1','$suffix','$bagi_hasil','4','$os','$os','$saldo_margin','$angsuran','$pokok','$ijaroh','$bulat','$run_tenor','$ke','','','$unit',
'$tgl','$tgl','$tgl','$tgl','$tgl','$tgl','$hari','$cao','$userid','Active','','','1','$id_outlet','$code_musa')");

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
		$hapus_tabel_temporial=mysql_query("delete from temp_toko where unit ='$useridd'");
		if($hapus_tabel_temporial){
			//1. Tahap Penginputan
			$update=mysql_query ("UPDATE musawamah,member 
			set musawamah.code_kel = member.klmpk,
			musawamah.cao = member.kode_ao, 
			musawamah.hari = member.hari_tagih,
			musawamah.unit = member.unit

			where
			musawamah.id_member = member.id_cust and musawamah.code_kel=' ' ");
		
			

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
			<h2 align="center"><strong>Update Data Musawamah</strong></h2>
			<p align="center">&nbsp;</p>
			<p align="center">
			<p align="center" class="style1"><font color="#666666">
		    Proses ini adalah proses untuk melakuan update data musawaha setelah proses Upload. </font></p>
			<p align="center"><em><font color="#666666">Proses bisa dilakukan setelah semua data di Upload.
		    <?php 
			$cek=mysql_query("select * from tabel_transaksi where keterangan_posting='' and unit ='$useridd'");
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

