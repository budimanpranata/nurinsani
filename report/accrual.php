<?php require_once __DIR__ . '/../php8_compat.php'; 
session_start();
$userid=$_SESSION['userid'];
$userid=$_SESSION['userid'];
$bulan=$_SESSION['bulan'];
$tahun=$_SESSION['tahun'];
$status=$_SESSION['status'];

$today=date("Y-m-d");
$buss_date=$_SESSION['buss_dates']; 
 $day = date('D', strtotime($buss_date));
    $dayList = array(
    	'Sun' => 'Minggu',
    	'Mon' => 'Senin',
    	'Tue' => 'Selasa',
    	'Wed' => 'Rabu',
    	'Thu' => 'Kamis',
    	'Fri' => 'Jumat',
    	'Sat' => 'Sabtu'
    );

	include "../encryption/function.php";
	
	//untuk waktu
	$tanggal=date("Y-m-d");
	
	//untuk koneksi database
	include "../conn2.php";
	include "../function.php";

	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}  
		
	header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=accrual.xls");

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
	<B>LAPORAN JURNAL ACRUAL</b><br/>
	<B>UNIT KANTOR PUSAT</B>  <br/>
	<B>Periode - ".$buss_date."</B>  <br/>
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

				$acc=mysql_query("select sum(accrual) as accrual from loan_accrual where gol='1'");
			
			while($data_acc=mysql_fetch_array($acc)){
			
				$accrual=$data_acc['accrual'];
			}  	   
				   $kodet=7;
$kodet=acakangkahuruf($kodet);
$kode_tran="BU/".$unit .$kodet;
$ket="Accrual PM Murabahah";
		
			$jurnal_umum=mysql_fetch_array(mysql_query("SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='1010' ORDER BY tanggal_selesai DESC"));
				$nomor_jurnal=$jurnal_umum[0]+1;
				
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
				values('$unit','$kode_tran','1010-1913000','$buss_date','Bukti SYSTEM','$ket','$accrual','$userid')");
				
				mysql_query("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
							values('$unit','$kode_tran','1010-41002','$buss_date','Bukti SYSTEM','$ket','$accrual','$userid')");
				
			 $truncate ="TRUNCATE TABLE accrual";
             mysql_query($truncate);
			 
			?>
			
			<?php
		
			?>
			

			<p align="center"><font color="#333333"><?php if(!empty($tanggal2)){ echo "Periode ".$tanggal2;} ?></font></p>
			<p align="center">&nbsp;</p>
			<div class="container">
			
			
			
				<table width="100%" border="1" bordercolor="669900" class="table table-striped table-bordered" width="100%" 
			<tr>
				<th>buss_date</th>
<th>Debet</th>
<th>kredit</th>

			</tr>

				<tr>
				<td><div align="center"><?php echo$buss_date;?></div></td>

					<td><div align="center"><?php echo$accrual;?></div></td>
<td><div align="center"><?php echo$accrual;?></div></td>
					
				</tr>
			
			</table>
				</div>
			
			</p>
		</div>
	</div>
	
	
	<iframe width=174 height=189 name="gToday:normal:../calender/agenda.js" id="gToday:normal:../calender/agenda.js" src="../calender/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;"></iframe>


<?php 

?>
