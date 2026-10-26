<?php require_once __DIR__ . '/../../php8_compat.php'; 
ini_set('display_errors',1);
session_start();
$userid=$_SESSION['userid'];
$norek=$_SESSION['noreks'];
$bulan=$_SESSION['bulans'];
$tahun=$_SESSION['tahuns'];
$checkbox=$_SESSION['checkboxs'];
if(!isset($_SESSION['stgl_system'])){
echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";

   header("location:param_tgl.php");
}
if(!isset($_SESSION['userid'])){
   header("location:../../index.html");
}
if($_SESSION['level']!="audit"){
   header("location:location:../../index.html");
}

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



			function curl($url){
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				$output = curl_exec($ch);  
				curl_close($ch);      
				return $output;
			}
			
			$curl =curl("http://192.168.88.44/ni/api/categories/transaksi.php?name=$norek&bln=$bulan");
			$data = json_decode($curl, TRUE);
			
			//kosongkan temporari
			@mysql_query("TRUNCATE tabel_transaksi_api");
			echo "<script>alert('Temporary selesai di hapus');</script>";
			
	$sql="insert into tabel_transaksi_api(unit,kode_transaksi,kode_rekening,tanggal_transaksi,keterangan_transaksi,debet,kredit) 
			values ";
							
			foreach($data as $row){
				
				$unit=$row["unit"];
				$kode_rekening=$row["kode_rekening"];
				$kode_transaksi=$row["kode_transaksi"];
				$tanggal_transaksi=$row["tanggal_transaksi"];
				$keterangan_transaksi=$row["keterangan_transaksi"];
				$debet=$row["debet"];
				$kredit=$row["kredit"];



				$sql .= '("'.$unit.'", "'.$kode_transaksi.'","'.$kode_rekening.'","'.$tanggal_transaksi.'","'.$keterangan_transaksi.'","'.$debet.'","'.$kredit.'"),';

				//@mysql_query("insert into tabel_transaksi_api_api(unit,kode_transaksi,kode_rekening,tanggal_transaksi,keterangan_transaksi,debet,kredit) 
			//values('$unit','$kode_transaksi','$kode_rekening','$tanggal_transaksi','$keterangan_transaksi','$debet','$kredit')");
			}
			$sql = rtrim($sql, ', ');
			mysql_query($sql);
			echo "<script>alert('Api Toko Sukses di akses');</script>";

			
//Data Nurinsani
$curl =curl("http://192.168.88.4/nurinsani/api/categories/transaksi_2020.php?name=$norek&bln=$bulan");
			$data = json_decode($curl, TRUE);
			
						
	$sql="insert into tabel_transaksi_api(unit,kode_transaksi,kode_rekening,tanggal_transaksi,keterangan_transaksi,debet,kredit) 
			values ";
							
			foreach($data as $row){
				
				$unit=$row["unit"];
				$kode_rekening=$row["kode_rekening"];
				$kode_transaksi=$row["kode_transaksi"];
				$tanggal_transaksi=$row["tanggal_transaksi"];
				$keterangan_transaksi=$row["keterangan_transaksi"];
				$debet=$row["debet"];
				$kredit=$row["kredit"];



				$sql .= '("'.$unit.'", "'.$kode_transaksi.'","'.$kode_rekening.'","'.$tanggal_transaksi.'","'.$keterangan_transaksi.'","'.$debet.'","'.$kredit.'"),';

				//@mysql_query("insert into tabel_transaksi_api_api(unit,kode_transaksi,kode_rekening,tanggal_transaksi,keterangan_transaksi,debet,kredit) 
			//values('$unit','$kode_transaksi','$kode_rekening','$tanggal_transaksi','$keterangan_transaksi','$debet','$kredit')");
			}
			$sql = rtrim($sql, ', ');
			mysql_query($sql);
			echo "<script>alert('Api Toko Sukses di akses');</script>";


$curl =curl("http://192.168.88.4/nurinsani/api/categories/transaksi_kp_2020.php?name=$norek&bln=$bulan");
			$data = json_decode($curl, TRUE);
			
						
	$sql="insert into tabel_transaksi_api(unit,kode_transaksi,kode_rekening,tanggal_transaksi,keterangan_transaksi,debet,kredit) 
			values ";
							
			foreach($data as $row){
				
				$unit=$row["unit"];
				$kode_rekening=$row["kode_rekening"];
				$kode_transaksi=$row["kode_transaksi"];
				$tanggal_transaksi=$row["tanggal_transaksi"];
				$keterangan_transaksi=$row["keterangan_transaksi"];
				$debet=$row["debet"];
				$kredit=$row["kredit"];



				$sql .= '("'.$unit.'", "'.$kode_transaksi.'","'.$kode_rekening.'","'.$tanggal_transaksi.'","'.$keterangan_transaksi.'","'.$debet.'","'.$kredit.'"),';

				//@mysql_query("insert into tabel_transaksi_api_api(unit,kode_transaksi,kode_rekening,tanggal_transaksi,keterangan_transaksi,debet,kredit) 
			//values('$unit','$kode_transaksi','$kode_rekening','$tanggal_transaksi','$keterangan_transaksi','$debet','$kredit')");
			}
			$sql = rtrim($sql, ', ');
			mysql_query($sql);
			echo "<script>alert('Api NI Sukses di akses');</script>";

	
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=buku_besar.xls");

echo "<html>
		<head>
			<title>Buku Besar</title>
			
		</head>
		<body>
		<b align='center'>KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH</b><br/>
	<B>LAPORAN BUKU BESAR KONSOLIDASI</b><br/>
	
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


			$select = mysql_query("select sum(saldo_awal) as saldo_awal ,sum(saldo_akhir) as saldo_akhir,kode_rekening,nama_rekening from tabel_master_konsol WHERE kode_rekening = '$norek' and month(tanggal_awal)='$bulan' and Year(tanggal_awal)='$tahun' and unit ='0000'");
            while($row = mysql_fetch_array($select)){
                //$next_schedule=$row['next_schedule'];
                $saldo_awal=$row['saldo_awal'];
				$kode_rekening=$row['kode_rekening'];
				$nama_rekening=$row['nama_rekening'];
				$saldo_akhir=$row['saldo_akhir'];
				
				
                 }

$s_awal = mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_awal from tabel_master_konsol WHERE kode_rekening ='$norek' and month(tanggal_awal)='12' and Year(tanggal_awal)='2020' and unit ='0000'"));
                      $saldo_awal_min=$s_awal['saldo_awal'];
				
				
 		
				//tanggal periode laporan
				$select = mysql_query("select normal,posisi from coa WHERE kode_rek ='$kode_rekening'");
				while($row = mysql_fetch_array($select)){
                //$next_schedule=$row['next_schedule'];
                $normal=$row['normal'];
				$pilih=$row['posisi'];
				
                 }	  
				   
				   if($checkbox=="checkbox"){
					   $saldo_awal=$saldo_awal_min;
					   switch($pilih)
						{
						case "Neraca":
					   	   			   
					   if($normal=="debet"){
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_api.tanggal_transaksi as tanggal,
						tabel_transaksi_api.kode_transaksi as no_bukti,
						kode_rekening,
						branch.unit,
						tabel_transaksi_api.keterangan_transaksi,
						tabel_transaksi_api.debet,
						tabel_transaksi_api.kredit,
						@bal:=(@bal+tabel_transaksi_api.debet)-tabel_transaksi_api.kredit as balance 
						FROM tabel_transaksi_api
						inner join branch on branch.kode_branch = tabel_transaksi_api.unit
						
						order by branch.kode_branch asc
						");
				   
						}else{
					   
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_api.tanggal_transaksi as tanggal,
						tabel_transaksi_api.kode_transaksi as no_bukti,
						kode_rekening,
						branch.unit,
						tabel_transaksi_api.keterangan_transaksi,
						tabel_transaksi_api.debet,
						tabel_transaksi_api.kredit,
						@bal:=(@bal-tabel_transaksi_api.debet)+tabel_transaksi_api.kredit as balance 
						FROM tabel_transaksi_api
						inner join branch on branch.kode_branch = tabel_transaksi_api.unit
						
						 order by branch.kode_branch asc
								");
							}
					break;
					 case "rugi-laba":
					 if($normal=="debet"){
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_api.tanggal_transaksi as tanggal,
						tabel_transaksi_api.kode_transaksi as no_bukti,
						kode_rekening,
						branch.unit,
						tabel_transaksi_api.keterangan_transaksi,
						tabel_transaksi_api.debet,
						tabel_transaksi_api.kredit,
						@bal:=(@bal+tabel_transaksi_api.debet)-tabel_transaksi_api.kredit as balance 
						FROM tabel_transaksi_api
						inner join branch on branch.kode_branch = tabel_transaksi_api.unit
						
						order by branch.kode_branch asc
						");
				   
						}else{
					   
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_api.tanggal_transaksi as tanggal,
						tabel_transaksi_api.kode_transaksi as no_bukti,
						kode_rekening,
						branch.unit,
						tabel_transaksi_api.keterangan_transaksi,
						tabel_transaksi_api.debet,
						tabel_transaksi_api.kredit,
						@bal:=(@bal-tabel_transaksi_api.debet)+tabel_transaksi_api.kredit as balance 
						FROM tabel_transaksi_api
						inner join branch on branch.kode_branch = tabel_transaksi_api.unit
						
						order by kode_branch desc 
								");
							}
					break;
				default:
				}
					
					
					
					}
				   
				else{
				   $saldo_awal=$saldo_awal_min;
					   
				   		switch($pilih)
						{
						case "Neraca":
					   	   			   
					   if($normal=="debet"){
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_api.tanggal_transaksi as tanggal,
						tabel_transaksi_api.kode_transaksi as no_bukti,
						kode_rekening,
						branch.unit,
						tabel_transaksi_api.keterangan_transaksi,
						tabel_transaksi_api.debet,
						tabel_transaksi_api.kredit,
						@bal:=(@bal+tabel_transaksi_api.debet)-tabel_transaksi_api.kredit as balance 
						FROM tabel_transaksi_api
						inner join branch on branch.kode_branch = tabel_transaksi_api.unit
						
						order by branch.kode_branch asc
						");
				   
						}else{
					   
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_api.tanggal_transaksi as tanggal,
						tabel_transaksi_api.kode_transaksi as no_bukti,
						kode_rekening,
						branch.unit,
						tabel_transaksi_api.keterangan_transaksi,
						tabel_transaksi_api.debet,
						tabel_transaksi_api.kredit,
						@bal:=(@bal-tabel_transaksi_api.debet)+tabel_transaksi_api.kredit as balance 
						FROM tabel_transaksi_api
						inner join branch on branch.kode_branch = tabel_transaksi_api.unit
						'
						 order by branch.kode_branch asc
								");
							}
					break;
					 case "rugi-laba":
					 if($normal=="debet"){
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_api.tanggal_transaksi as tanggal,
						tabel_transaksi_api.kode_transaksi as no_bukti,
						kode_rekening,
						branch.unit,
						tabel_transaksi_api.keterangan_transaksi,
						tabel_transaksi_api.debet,
						tabel_transaksi_api.kredit,
						@bal:=(@bal+tabel_transaksi_api.debet)-tabel_transaksi_api.kredit as balance 
						FROM tabel_transaksi_api
						inner join branch on branch.kode_branch = tabel_transaksi_api.unit
						  
						order by branch.kode_branch asc
						");
				   
						}else{
					   
						$select1 = mysql_query("SET @bal = '$saldo_awal';");
						$query_transaksi=mysql_query("SELECT 
						tabel_transaksi_api.tanggal_transaksi as tanggal,
						tabel_transaksi_api.kode_transaksi as no_bukti,
						kode_rekening,
						branch.unit,
						tabel_transaksi_api.keterangan_transaksi,
						tabel_transaksi_api.debet,
						tabel_transaksi_api.kredit,
						@bal:=(@bal-tabel_transaksi_api.debet)+tabel_transaksi_api.kredit as balance 
						FROM tabel_transaksi_api
						inner join branch on branch.kode_branch = tabel_transaksi_api.unit
						  
						order by branch.kode_branch asc
						 
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
    <td><?php echo number_format(compat_num($saldo_awal_min),0,',', '.'); ?></td>
  </tr>
  <tr>
    <td><span class="style12">Saldo Akhir </span></td>
    <td>:</td>
    <td><?php echo number_format(compat_num($saldo_akhir),0,',', '.'); ?></td>
  </tr>
</table>
			
			<div class="container">
			
			
			
				<table width="100%" border="1" bordercolor="669900" class="table table-striped table-bordered" width="100%" 
			<tr>
				<th>Tanggal</th><th>Unit</th><th>Nomor Bukti</th><th>Kode Rekening</th><th>Keterangan<th>debet</th></th><th>kredit</th><th>saldo</th>
			</tr>
			<?php
			
			
			
			while($row_tran=mysql_fetch_array($query_transaksi)){
				$debet=$row_tran['debet'];
				$kredit=$row_tran['kredit'];
				
				?>
				<tr>
					<td><div align="center"><?php echo $row_tran['tanggal'];?></div></td>
					<td><div align="center"><?php echo $row_tran['unit'];?></div></td>
					<td><div align="center"><?php echo $row_tran['no_bukti'];?></div></td>
					<td><div align="center"><?php echo $row_tran['kode_rekening'];?></div></td>
					<td><div align="center"><?php echo $row_tran['keterangan_transaksi'];?></div></td>
					<td align="right"><?php echo number_format(compat_num($row_tran['debet']),0,',', '.'); ?></td>
					<td align="right"><?php echo number_format(compat_num($row_tran['kredit']),0,',', '.'); ?></td>
					<td align="right"><?php echo number_format(compat_num($row_tran['balance']),0,',', '.'); ?></td>
					
					
					
				</tr>
				<?php
			}
			?>
			
			</table>
				</div>
			
			</p>
		</div>
	</div>
	
	


<?php 

?>
