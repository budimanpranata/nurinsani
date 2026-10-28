<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];

if(!isset($_SESSION['userid'])){
   header("location:index.html");
 exit; }
if($_SESSION['level']!="unit"){
   header("location:index.html");
 exit; }
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=NERACA.xls");
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
<table width="100%">
  <tr bgcolor="#669900">
  <td height="30" colspan="8" nowrap><div align="left" class="style9 style3 style1 style1"><strong> Neraca </strong></div></td>
  </tr>
</table>
<p>
	<div class="post">
	<div class="entry">
			<?php
					?>
					
			<div class="container">
				<table id="neraca" class="table table-striped table-bordered" width="100%" 
			<thead>
			<tr> 
				<div class="row" align="center">	
				<th align="center" rowspan="2">Account</th>
			 </div>
				<div class="row" align="center">
					<th align="center" rowspan="2">Nama Account</th>
					<th colspan="2">Awal</th>
					<th colspan="2">Mutasi</th>
					<th colspan="2">Sisa</th>
			  </tr>
			</div>
			
			
			<tr>
			
				<th>Debet</th>
				<th>Kredit</th><th>Debet</th><th> Kredit</th><th>Debet</th><th>Kredit</th>
			</tr>	
			
			<?php
			$query_mutasi=mysql_query("select * from tabel_master where unit ='$unit'  and  nama_rekening not like '%total%' order by kode_rekening asc");
			$total=mysql_fetch_array(mysql_query("select sum(awal_debet) as tot_awal_debet, sum(awal_kredit) as tot_awal_kredit, sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(sisa_debet) as tot_sisa_debet, sum(sisa_kredit) as tot_sisa_kredit from tabel_master where unit ='$unit' order by kode_rekening asc"));
			
			while($row_mut=mysql_fetch_array($query_mutasi)){
			
				$awal_debet=$row_mut['awal_debet'];
				$awal_kredit=$row_mut['awal_kredit'];
				$mutasi_debet=$row_mut['mut_debet'];
				$mutasi_kredit=$row_mut['mut_kredit'];
				$sisa_debet=$row_mut['sisa_debet'];
				$sisa_kredit=$row_mut['sisa_kredit'];
			
				?>
				<tr>
					<td><div align="center"><?php echo $row_mut['kode_rekening'];?></div></td>
					<td><?php echo $row_mut['nama_rekening'];?></td>
					
					<td align="right"><?php echo number_format(compat_num($awal_debet),2,",","."); ?></td>
					<td align="right"><?php echo number_format(compat_num($awal_kredit),2,",","."); ?></td>	
					
					<td align="right"><?php echo number_format(compat_num($mutasi_debet),2,",","."); ?></td>
					<td align="right"><?php echo number_format(compat_num($mutasi_kredit),2,",","."); ?></td>	
					
					<td align="right"><?php echo number_format(compat_num($sisa_debet),2,",","."); ?></td>
					<td align="right"><?php echo number_format(compat_num($sisa_kredit),2,",","."); ?></td>					
				</tr>
				<?php
			}
			?>
			</thead>
			<tfoot align="right">
			<tr>
				<td colspan="2"><div align="center"><strong>TOTAL TRANSAKSI</strong></div></td>
				<td><div align="right"><strong><?php echo number_format(compat_num($total['tot_awal_debet']),2,",","."); ?></strong></div></td><td><div align="right"><strong><?php echo number_format(compat_num($total['tot_awal_kredit'])); ?></strong></div></td>
				<td><div align="right"><strong><?php echo number_format(compat_num($total['tot_mut_debet']),2,",","."); ?></strong></div></td><td><div align="right"><strong><?php echo number_format(compat_num($total['tot_mut_kredit'])); ?></strong></div></td>
				<td><div align="right"><strong><?php echo number_format(compat_num($total['tot_sisa_debet']),2,",","."); ?></strong></div></td>
				<td><div align="right"><strong><?php echo number_format(compat_num($total['tot_sisa_kredit']),2,",","."); ?></strong></div></td>
			</tr>
			<tr><th></th><th></th><th></th><th></th><th></th><th></th></tr>
			</tfoot>
			</table>
			</div>
			</p>
	</div>
	</div>
