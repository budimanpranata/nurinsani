<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];
 $tanggal1=$_SESSION['tgl_awal_pos'];
 $tanggal2=$_SESSION['tgl_akhir_pos'];
	include "../encryption/function.php";
	
	//untuk waktu
	$tanggal=date("Y-m-d");
	
	//untuk koneksi database
	include "../conn2.php";
	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}  
			
	
	header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=list_jurnal.xls");

	
?>

<table width="100%">
  
</table>
<div class="post">
	<div class="entry">
		<form action="?<?php echo paramEncrypt('page=../laporan/buku_jurnal')?>" method="post" name="postform">
		 
		</form>
	</div>
</div>


	<div class="post">
		<div class="entry">
			<p>
			<?php
			//untuk menyelesaikan transaksi
		
				//tanggal periode laporan
				
				
				$query_transaksi=mysql_query("select * from tabel_transaksi where tanggal_transaksi between '$tanggal1' and '$tanggal2' and unit ='$unit' order by tanggal_transaksi,kode_transaksi");
				$total=mysql_fetch_array(mysql_query("select sum(debet) as tot_debet, sum(kredit) as tot_kredit from tabel_transaksi where tanggal_transaksi between '$tanggal1' and '$tanggal2' and unit ='$unit' order by kode_rekening asc"));
	
			
			?>
			
			<?php
		
			?>
			

			<p align="center"><font color="#333333"><?php if(!empty($tanggal2)){ echo "Periode ".$tanggal2;} ?></font></p>
			<p align="center">&nbsp;</p>
			<div class="container">
				<table width="100%" border="1" bordercolor="669900" class="table table-striped table-bordered" width="100%" 
			<tr>
				<th>Tanggal</th><th>Nomor Bukti</th><th>Kode Rekening</th><th>Keterangan<th>Jenis Transaksi</th></th><th>Debet</th><th>Kredit</th>
			</tr>
			<?php
			
			while($row_tran=mysql_fetch_array($query_transaksi)){
				$debet=$row_tran['debet'];
				$kredit=$row_tran['kredit'];
				
				?>
				<tr>
					<td><div align="center"><?php echo $row_tran['tanggal_transaksi'];?></div></td>
					<td><div align="center"><?php echo $row_tran['kode_transaksi'];?></div></td>
					<td><div align="center"><?php echo $row_tran['kode_rekening'];?></div></td>
					<td><?php echo $row_tran['keterangan_transaksi'];?></td>
					<td><?php echo $row_tran['jenis_transaksi'];?></td>
					<td align="right"><?php echo number_format(compat_num($debet),0,',', '.'); ?></td>
					<td align="right"><?php echo number_format(compat_num($kredit),0,',', '.'); ?></td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="5"><div align="center"><strong>TOTAL TRANSAKSI</strong></div></td>
				<td align="right"><strong><?php echo number_format(compat_num($total['tot_debet']),0,',', '.'); ?></strong></td>
				<td align="right"><strong><?php echo number_format(compat_num($total['tot_kredit']),0,',', '.'); ?></strong></td>
			</tr>
			</table>
				</div>
			
			</p>
		</div>
	</div>
	
	
	<iframe width=174 height=189 name="gToday:normal:../calender/agenda.js" id="gToday:normal:../calender/agenda.js" src="../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;"></iframe>


<?php 

?>
