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
	$koneksi=open_connection2();
	$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}  
		 $truncate ="TRUNCATE TABLE accrual";
             mysql_query($truncate);
	
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
	<B>LAPORAN ACRUAL</b><br/>
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

					   
				   		switch($status)
						{
						case "CURRENT":
						$query = "select buss_date,
code_kel,
no_anggota,
cif,
Cust_Short_name,
Deal_type,
suffix,
bagi_hasil,
Tenor,
Plafond,
os,
saldo_margin,
angsuran,
pokok,
ijaroh,
bulat,
run_tenor,
ke,
usaha,
nama_usaha,
unit,
tgl_wakalah,
tgl_akad,
tgl_murab,
next_schedule,
maturity_date,
last_payment,
hari,
cao,
USERID,
status,
status_usia,
status_app,
gol
from loan

where  os > 0 ";



					   
					break;
					 case "EOM":
					 $query = "select buss_date,
code_kel,
no_anggota,
cif,
Cust_Short_name,
Deal_type,
suffix,
bagi_hasil,
Tenor,
Plafond,
os,
saldo_margin,
angsuran,
pokok,
ijaroh,
bulat,
run_tenor,
ke,
usaha,
nama_usaha,
unit,
tgl_wakalah,
tgl_akad,
tgl_murab,
next_schedule,
maturity_date,
last_payment,
hari,
cao,
USERID,
status,
status_usia,
status_app,
gol
from data_loan

where  os > 0 and buss_date ='$buss_date' ";

					break;
				default:
				}
					   
				
				   
				   
				   
				   
				   
			
			?>
			
			<?php
		
			?>
			

			<p align="center"><font color="#333333"><?php if(!empty($tanggal2)){ echo "Periode ".$tanggal2;} ?></font></p>
			<p align="center">&nbsp;</p>
			<div class="container">
			
			
			
				<table width="100%" border="1" bordercolor="669900" class="table table-striped table-bordered" width="100%" 
			<tr>
				<th>buss_date</th>
<th>code_kel</th>
<th>no_anggota</th>
<th>cif</th>
<th>Cust_Short_name</th>
<th>Deal_type</th>
<th>suffix</th>
<th>bagi_hasil</th>
<th>Tenor</th>
<th>Plafond</th>
<th>Pembiayaan</th>
<th>os</th>
<th>saldo_margin</th>
<th>angsuran</th>
<th>pokok</th>
<th>ijaroh</th>
<th>bulat</th>
<th>run_tenor</th>
<th>ke</th>
<th>usaha</th>
<th>nama_usaha</th>
<th>unit</th>
<th>tgl_wakalah</th>
<th>tgl_akad</th>
<th>tgl_murab</th>
<th>next_schedule</th>
<th>maturity_date</th>
<th>last_payment</th>
<th>hari</th>
<th>cao</th>
<th>USERID</th>
<th>status</th>
<th>status_usia</th>
<th>status_app</th>
<th>gol</th>

				<th>Day</th>
				<th>Acrual</th>
			</tr>
			<?php
			
			
			
			$hasil = mysql_query($query);
$baris = 1;
while ($data = mysql_fetch_array($hasil))
{
$cif=$data['cif'];
$minggon=$data['hari'];
$tenor=$data['Tenor'];
$os=$data['os'];
$Plafond=$data['Plafond'];
$gol=$data['gol'];
$saldo_margin=$data['saldo_margin'];
//$cek_tg= mysql_fetch_array(mysql_query("select sum(KREDIT-DEBIT) as pokok from tunggakan where cif ='$cif' and type ='01'"));
//$tunggak_pokok=$cek_tg['pokok'];
//$cek_tg_wj= mysql_fetch_array(mysql_query("select sum(KREDIT-DEBIT) as margin from tunggakan where cif ='$cif' and type ='02'"));
//$tunggak_margin=$cek_tg_wj['margin'];
$cek_hari= mysql_fetch_array(mysql_query("select hari_eom,hari_tagih,total_hari from param_hari where hari_eom ='$dayList[$day]' and hari_tagih='$minggon'"));
$tot_hari=$cek_hari['total_hari'];

$saldo_os=$os-$saldo_margin;
if($saldo_os=0){
	$acrual=0;
}else{
$acrual=$Plafond*18/100/$tenor/7*$tot_hari;	
mysql_query("insert accrual(buss_date,cif,accrual,gol)VALUES('$buss_date','$cif','$acrual','$gol')") or die(mysql_error("GAGAL INPUT"));
	
}
				
				?>
				<tr>
				<td><div align="center"><?php echo$buss_date;?></div></td>
<td><div align="center"><?php echo$data['code_kel'];?></div></td>
<td><div align="center"><?php echo$data['no_anggota'];?></div></td>
<td><div align="center"><?php echo$data['cif'];?></div></td>
<td><div align="center"><?php echo$data['Cust_Short_name'];?></div></td>
<td><div align="center"><?php echo$data['Deal_type'];?></div></td>
<td><div align="center"><?php echo$data['suffix'];?></div></td>
<td><div align="center"><?php echo$data['bagi_hasil'];?></div></td>
<td><div align="center"><?php echo$data['Tenor'];?></div></td>
<td><div align="center"><?php echo$data['Plafond'];?></div></td>
<td><div align="center"><?php echo$data['os'];?></div></td>
<td><div align="center"><?php echo$data['os']-$data['saldo_margin'];?></div></td>
<td><div align="center"><?php echo$data['saldo_margin'];?></div></td>
<td><div align="center"><?php echo$data['angsuran'];?></div></td>
<td><div align="center"><?php echo$data['pokok'];?></div></td>
<td><div align="center"><?php echo$data['ijaroh'];?></div></td>
<td><div align="center"><?php echo$data['bulat'];?></div></td>
<td><div align="center"><?php echo$data['run_tenor'];?></div></td>
<td><div align="center"><?php echo$data['ke'];?></div></td>
<td><div align="center"><?php echo$data['usaha'];?></div></td>
<td><div align="center"><?php echo$data['nama_usaha'];?></div></td>
<td><div align="center"><?php echo$data['unit'];?></div></td>
<td><div align="center"><?php echo$data['tgl_wakalah'];?></div></td>
<td><div align="center"><?php echo$data['tgl_akad'];?></div></td>
<td><div align="center"><?php echo$data['tgl_murab'];?></div></td>
<td><div align="center"><?php echo$data['next_schedule'];?></div></td>
<td><div align="center"><?php echo$data['maturity_date'];?></div></td>
<td><div align="center"><?php echo$data['last_payment'];?></div></td>
<td><div align="center"><?php echo$data['hari'];?></div></td>
<td><div align="center"><?php echo$data['cao'];?></div></td>
<td><div align="center"><?php echo$data['USERID'];?></div></td>
<td><div align="center"><?php echo$data['status'];?></div></td>
<td><div align="center"><?php echo$data['status_usia'];?></div></td>
<td><div align="center"><?php echo$data['status_app'];?></div></td>
<td><div align="center"><?php echo$data['gol'];?></div></td>
<td align="left"><?php echo $tot_hari; ?></td>
<td align="right"><?php echo number_format(compat_num($acrual),0,',', '.'); ?></td>
					
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
