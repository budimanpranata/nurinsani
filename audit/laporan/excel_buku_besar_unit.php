<?php require_once __DIR__ . '/../../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];
$norek=$_SESSION['noreks'];
$bulan=$_SESSION['bulans'];
$tahun=$_SESSION['tahuns'];
$checkbox=$_SESSION['checkboxs'];
$unit=$_SESSION['units'];

	include "../encryption/function.php";
	
	//untuk waktu
	$tanggal=date("Y-m-d");
	
	//untuk koneksi database
	include "../conn2.php";
	$koneksi=open_connection2();

			
	
	header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=buku_besar.xls");

echo "<html>
		<head>
			<title>NeracaPercobaan</title>
			<style>
				body {font-family:tahoma, arial}
				table {border-collapse: collapse}
				th, td {font-size: 17px; border: 1px solid #DEDEDE; padding: 5px 7px; color: #303030}
				th {background: #EAEAEA; font-size: 17px; border-color:#B0B0B0}
				.subtotal td{background: #F5F5F5}
				.total td{background: #ECECEC}
				.right{text-align: right}
			</style>
		</head>
		<body>
		<b align='center'>KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH</b><br/>
	<B>LAPORAN BUKU BESAR</b><br/>
	<B>UNIT - ".$unit."</B>  <br/>
	
	<strong></strong><br/>";

	
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
			$select = mysql_query("select saldo_awal,saldo_akhir,kode_rekening,nama_rekening from tabel_master WHERE kode_rekening ='$norek' and unit ='$unit' ");
            while($row = mysql_fetch_array($select)){
                

				$kode_rekening=$row['kode_rekening'];
				$nama_rekening=$row['nama_rekening'];
				$saldo_akhir=$row['saldo_akhir'];
				
                 }



$s_awal = mysql_fetch_array(mysql_query("select saldo_awal,saldo_akhir,kode_rekening,nama_rekening from tabel_master_konsol_eoy WHERE kode_rekening ='$norek' and unit ='$unit' and month(tanggal_awal)='12' and Year(tanggal_awal)='2022'"));
                      $saldo_awal=$s_awal['saldo_akhir'];
				
				
 		
				//tanggal periode laporan
				$select = mysql_query("select normal,posisi from coa WHERE kode_rek ='$norek'");
				while($row = mysql_fetch_array($select)){
                //$next_schedule=$row['next_schedule'];
                $normal=$row['normal'];
				$pilih=$row['posisi'];
				
                 }	  
			

	 var_dump($pilih);  
				   if($checkbox=="checkbox"){
					   $saldo_awal=$saldo_awal;
					   switch($pilih)
						{
						case "Neraca":
					   	   			   
					   if($normal=="debet"){
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_2023.tanggal_transaksi as tanggal,
						tabel_transaksi_2023.kode_transaksi as no_bukti,
						kode_rekening,
						tabel_transaksi_2023.keterangan_transaksi,
						tabel_transaksi_2023.debet,
						tabel_transaksi_2023.kredit,
						@bal:=(@bal+tabel_transaksi_2023.debet)-tabel_transaksi_2023.kredit as balance 
						FROM tabel_transaksi_2023
						where tabel_transaksi_2023.kode_rekening='$norek' and tabel_transaksi_2023.unit ='$unit' and month(tabel_transaksi_2023.tanggal_transaksi) between '1' and '$bulan' and year(tabel_transaksi_2023.tanggal_transaksi)='$tahun'
						");
				   
						}else{
					   
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_2023.tanggal_transaksi as tanggal,
						tabel_transaksi_2023.kode_transaksi as no_bukti,
						kode_rekening,
						tabel_transaksi_2023.keterangan_transaksi,
						tabel_transaksi_2023.debet,
						tabel_transaksi_2023.kredit,
						@bal:=(@bal-tabel_transaksi_2023.debet)+tabel_transaksi_2023.kredit as balance 
						FROM tabel_transaksi_2023
						where tabel_transaksi_2023.kode_rekening='$norek' and tabel_transaksi_2023.unit ='$unit' and month(tabel_transaksi_2023.tanggal_transaksi)between '1' and '$bulan'  and year(tabel_transaksi_2023.tanggal_transaksi)='$tahun'
						 
								");
							}
					break;
					 case "rugi-laba":
					 if($normal=="debet"){
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_2023.tanggal_transaksi as tanggal,
						tabel_transaksi_2023.kode_transaksi as no_bukti,
						kode_rekening,
						tabel_transaksi_2023.keterangan_transaksi,
						tabel_transaksi_2023.debet,
						tabel_transaksi_2023.kredit,
						@bal:=(@bal+tabel_transaksi_2023.debet)-tabel_transaksi_2023.kredit as balance 
						FROM tabel_transaksi_2023
						where tabel_transaksi_2023.kode_rekening='$norek' and tabel_transaksi_2023.unit ='$unit' and month(tabel_transaksi_2023.tanggal_transaksi) between '1' and '$bulan' and year(tabel_transaksi_2023.tanggal_transaksi)='$tahun'
						");
				   
						}else{
					   
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_2023.tanggal_transaksi as tanggal,
						tabel_transaksi_2023.kode_transaksi as no_bukti,
						kode_rekening,
						tabel_transaksi_2023.keterangan_transaksi,
						tabel_transaksi_2023.debet,
						tabel_transaksi_2023.kredit,
						@bal:=(@bal-tabel_transaksi_2023.debet)+tabel_transaksi_2023.kredit as balance 
						FROM tabel_transaksi_2023
						where tabel_transaksi_2023.kode_rekening='$norek' and tabel_transaksi_2023.unit ='$unit' and month(tabel_transaksi_2023.tanggal_transaksi)between '1' and '$bulan'  and year(tabel_transaksi_2023.tanggal_transaksi)='$tahun'
						 
								");
							}
					break;
				default:
				}
					
					
					
					}
				   
				else{
				   $saldo_awal=$saldo_awal;
					   
				   		switch($pilih)
						{
						case "Neraca":
					   	   			   
					   if($normal=="debet"){
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_2023.tanggal_transaksi as tanggal,
						tabel_transaksi_2023.kode_transaksi as no_bukti,
						kode_rekening,
						tabel_transaksi_2023.keterangan_transaksi,
						tabel_transaksi.debet,
						tabel_transaksi_2023.kredit,
						@bal:=(@bal+tabel_transaksi_2023.debet)-tabel_transaksi_2023.kredit as balance 
						FROM tabel_transaksi_2023
						where tabel_transaksi_2023.kode_rekening='$norek' and tabel_transaksi_2023.unit ='$unit' and month(tabel_transaksi_2023.tanggal_transaksi) between '1' and '$bulan' and year(tabel_transaksi_2023.tanggal_transaksi)='$tahun'
						");
				   
						}else{
					   
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_2023.tanggal_transaksi as tanggal,
						tabel_transaksi_2023.kode_transaksi as no_bukti,
						kode_rekening,
						tabel_transaksi_2023.keterangan_transaksi,
						tabel_transaksi_2023.debet,
						tabel_transaksi_2023.kredit,
						@bal:=(@bal-tabel_transaksi_2023.debet)+tabel_transaksi_2023.kredit as balance 
						FROM tabel_transaksi_2023
						where tabel_transaksi_2023.kode_rekening='$norek' and tabel_transaksi_2023.unit ='$unit' and month(tabel_transaksi_2023.tanggal_transaksi)between '1' and '$bulan'  and year(tabel_transaksi_2023.tanggal_transaksi)='$tahun'
						 
								");
							}
					break;
					 case "rugi-laba":
					 if($normal=="debet"){
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_2023.tanggal_transaksi as tanggal,
						tabel_transaksi_2023.kode_transaksi as no_bukti,
						kode_rekening,
						tabel_transaksi_2023.keterangan_transaksi,
						tabel_transaksi_2023.debet,
						tabel_transaksi_2023.kredit,
						@bal:=(@bal+tabel_transaksi_2023.debet)-tabel_transaksi_2023.kredit as balance 
						FROM tabel_transaksi_2023
					where tabel_transaksi_2023.kode_rekening='$norek' and tabel_transaksi_2023.unit ='$unit' and month(tabel_transaksi_2023.tanggal_transaksi)='$bulan' and year(tabel_transaksi_2023.tanggal_transaksi)='$tahun'   
				
						");
				   
						}else{
					   
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_2023.tanggal_transaksi as tanggal,
						tabel_transaksi_2023.kode_transaksi as no_bukti,
						kode_rekening,
						tabel_transaksi_2023.keterangan_transaksi,
						tabel_transaksi_2023.debet,
						tabel_transaksi_2023.kredit,
						@bal:=(@bal-tabel_transaksi_2023.debet)+tabel_transaksi_2023.kredit as balance 
						FROM tabel_transaksi_2023
						where tabel_transaksi_2023.kode_rekening='$norek' and tabel_transaksi_2023.unit ='$unit' and month(tabel_transaksi_2023.tanggal_transaksi)='$bulan' and year(tabel_transaksi_2023.tanggal_transaksi)='$tahun'   
				
						 
								");
							}
					break;
				default:
				}
					   
				}
				   
				   
				   
				   
				   
			
			?>
			
			<?php
		
			?>
			
<table >
  <tr>
    <td width="143"><span class="style12">No Perkiraan </span></td>
    <td width="24">:</td>
    <td width="151"><?php echo $kode_rekening; ?></td>
  </tr>
  <tr>
    <td><span class="style12">Nama Perkiraan </span></td>
    <td>:</td>
    <td><?php echo $nama_rekening; ?></td>
  </tr>
  <tr>
    <td><span class="style12">Saldo Awal </span></td>
    <td>:</td>
    <td><?php echo number_format(compat_num($saldo_awal),0,',', '.'); ?></td>
  </tr>
  <tr>
    <td><span class="style12">Saldo Akhir </span></td>
    <td>:</td>
    <td><?php echo number_format(compat_num($saldo_akhir),0,',', '.'); ?></td>
  </tr>
</table>
			<p align="center"><font color="#333333"><?php if(!empty($tanggal2)){ echo "Periode ".$tanggal2;} ?></font></p>
			<p align="center">&nbsp;</p>
			<div class="container">
			
			
			
				<table width="100%" border="1" bordercolor="669900" class="table table-striped table-bordered" width="100%" 
			<tr>
				<th>Tanggal</th><th>Nomor Bukti</th><th>Kode Rekening</th><th>Keterangan<th>debet</th></th><th>kredit</th><th>saldo</th>
			</tr>
			<?php
			
			
			
			while($row_tran=mysql_fetch_array($query_transaksi)){
				$debet=$row_tran['debet'];
				$kredit=$row_tran['kredit'];
				
				?>
				<tr>
					<td><div align="center"><?php echo $row_tran['tanggal'];?></div></td>
					<td><div align="center"><?php echo $row_tran['no_bukti'];?></div></td>
					<td><div align="center"><?php echo $row_tran['kode_rekening'];?></div></td>
					<td><div align="center"><?php echo $row_tran['keterangan_transaksi'];?></div></td>
					<td align="right"><?php echo $row_tran['debet']; ?></td>
					<td align="right"><?php echo $row_tran['kredit']; ?></td>
					<td align="right"><?php echo $row_tran['balance']; ?></td>
					
					
					
				</tr>
				<?php
			}
			?>
			
			</table>
				</div>
			
			</p>
		</div>
	</div>
	
	
	<iframe width=174 height=189 name="gToday:normal:../calender/agenda.js" id="gToday:normal:../calender/agenda.js" src="../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;"></iframe>


<?php 

?>
