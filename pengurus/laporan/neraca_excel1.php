<?php require_once __DIR__ . '/../../koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_cari=$_SESSION['tgl_caris'];
$unit=$_SESSION['units'];

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
}

//cek level user
if($_SESSION['level']!="kp"){
   header("location:../index.html");
}
function open_connection2(){

	//konfigurasi
	$host="localhost";
	$user="root";
	$pass="anaskorti";
	$db="nur_insani";
	
	
	//koneksi database
	$koneksi2=db_ni();
	mysql_select_db($db,$koneksi2);
	
	return $koneksi2;
}


$koneksi=open_connection2();
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=neraca.xls");

 $select = mysql_query("Select unit,sum(sisa_debet) as debet,sum(sisa_kredit) as kredit,posisi from tabel_master_konsol where  tanggal_awal = '$tgl_cari' and unit ='$unit' group By posisi");
		$tabel='tabel_master_konsol';
		

	if(mysql_num_rows($select)==0){
			 
		$tabel='tabel_master';
		}
		
			
$sql = mysql_query("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,mut_debet,mut_kredit,LINE_BALANCE
FROM ".$tabel." 
where unit ='$unit' and posisi='Neraca' and gr_neraca='AKTIVA' and tanggal_awal = '$tgl_cari'
GROUP BY gr_neraca,kode_rekening ");

$total_ak=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$tabel." where unit ='$unit' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and tanggal_awal = '$tgl_cari'  "));
$total_pa=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$tabel." where unit ='$unit' and gr_neraca='PASIVA' and kode_rekening in ('3901000','3902000'.'2500000') and tanggal_awal = '$tgl_cari' "));
$total_shu=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$tabel." where unit ='$unit' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING' and tanggal_awal = '$tgl_cari' "));


$total_la=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from tabel_rugi_laba where unit ='$unit' and gr_neraca='rugi-laba' and LINE_BALANCE='HEADING'  and tanggal_awal = '$tgl_cari'"));
$sql2 = mysql_query("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,mut_debet,mut_kredit,LINE_BALANCE
FROM ".$tabel."
where unit ='$unit' and gr_neraca='PASIVA' and tanggal_awal = '$tgl_cari'
GROUP BY gr_neraca,kode_rekening ");

								
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
	<B>LAPORAN MUTASI HARIAN</b><br/>
	<B>UNIT - ".$unit."</B>  <br/>
	<B>PERIODE - ".$tgl_cari."</B>  <br/>
	
	<strong></strong><br/>";


echo '<table class="grey">

<br>
		<thead>
			<tr>
				<th rowspan="2">TYPE COA</th>
				<th rowspan="2">COA</th>
				<th rowspan="2">Deskripsi</th>
				<th rowspan="2">Saldo Awal</th>
				<th colspan="2">Mutasi</th>
				<th rowspan="2">Saldo Akhir</th>
			</tr>
			<tr>
			<th>Debet</th>
				<th>Kredit</th>
				</tr>
		</thead>
		<tbody>';
while($row=mysql_fetch_array($sql))
{
	$class = '';
	$akhir =$row['saldo_akhir'];
	$awal =$row['saldo_awal'];
	
	
	if ($akhir < 0){
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
		//$akhir = "(".number_format (abs($akhir), '0', ',', '.').")";
		
	}else{
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
	}

if ($awal < 0){
		
		//$awal = "(".number_format (abs($awal), '0', ',', '.').")";
		$awal =number_format (compat_num($row['saldo_awal']), '0', ',', '.');
		
	}else{
		$awal =number_format (compat_num($row['saldo_awal']), '0', ',', '.');
	}

	// jika ada total atau sub total
		if ($row['LINE_BALANCE'] == 'HEADING') {
		$class = ' class="subtotal"';
	} else if ($row['kode_rekening'] == 'SUB TOTAL' || $row['nama_rekening'] == 'SUB TOTAL') {
		//$class = ' class="subtotal"';
	}
	
	echo 
	'<tr'.$class.'>
		<td>' . $row['LINE_BALANCE'] . '</td>
		<td>' . $row['kode_rekening'] . '</td>
		<td>' . $row['nama_rekening'] . '</td>
		<td>' . $awal. '</td>
		<td>' . number_format(compat_num($row['mut_debet']), '0', ',', '.') . '</td>
		<td>' . number_format(compat_num($row['mut_kredit']), '0', ',', '.') . '</td>
		<td>' . $akhir. '</td>
		</tr>';
		$total += $row['total'];
}
	
	
	
	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL AKTIVA</td>
		<td><b>' . number_format(compat_num($total_ak['tot_saldo_awal']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_ak['tot_mut_debet']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_ak['tot_mut_kredit']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_ak['tot_saldo_akhir']), 0, ',', '.') . '</td>
	 </tr>';
echo '<tr class="grey">
		<td colspan="3"><b></td>
		<td><b></td>
		<td><b></td>
		<td><b></td>
		<td><b></td>
	 </tr>';

while($row=mysql_fetch_array($sql2))
{
	$class = '';
	$akhir =$row['saldo_akhir'];
	$awal =$row['saldo_awal'];
	
	
	if ($akhir < 0){
		
		//$akhir = "(".number_format (abs($akhir), '0', ',', '.').")";
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
	}else{
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
	}

if ($awal < 0){
		
		//$awal = "(".number_format (abs($awal), '0', ',', '.').")";//
		$awal =number_format (compat_num($row['saldo_awal']), '0', ',', '.');
	}else{
		$awal =number_format (compat_num($row['saldo_awal']), '0', ',', '.');
	}

	
	// jika ada total atau sub total
	if ($row['LINE_BALANCE'] == 'HEADING') {
		$class = ' class="subtotal"';
	} else if ($row['kode_rekening'] == 'SUB TOTAL' || $row['nama_rekening'] == 'SUB TOTAL') {
	$class = ' class="subtotal"';
	}
	
	echo 
	'<tr'.$class.'>
			<td>' . $row['LINE_BALANCE'] . '</td>
		<td>' . $row['kode_rekening'] . '</td>
		<td>' . $row['nama_rekening'] . '</td>
		<td>' . $awal. '</td>
		<td>' . number_format(compat_num($row['mut_debet']), '0', ',', '.') . '</td>
		<td>' . number_format(compat_num($row['mut_kredit']), '0', ',', '.') . '</td>
		<td>' . $akhir. '</td>
		</tr>';
		$total += $row['total'];
}
	
	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL PASIVA</td>
		<td><b>' . number_format(compat_num($total_pa['tot_saldo_awal'] + $total_shu['tot_saldo_awal']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_pa['tot_mut_debet'] + $total_shu['tot_mut_debet']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_pa['tot_mut_kredit'] + $total_shu['tot_mut_kredit']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_pa['tot_saldo_akhir'] + $total_shu['tot_saldo_akhir']), 0, ',', '.') . '</td>
	 </tr>';
	 echo '<tr class="grey">
		<td colspan="3"><b></td>
		<td><b></td>
		<td><b></td>
		<td><b></td>
		<td><b></td>
	 </tr>';
 $sql3 = mysql_query("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,mut_debet,mut_kredit,LINE_BALANCE
FROM `tabel_rugi_laba` 
where unit ='$unit' and posisi='rugi-laba' and gr_neraca='rugi-laba'
GROUP BY gr_neraca,kode_rekening ");
while($row=mysql_fetch_array($sql3))
{
	$class = '';
	$akhir =$row['saldo_akhir'];
	$awal =$row['saldo_awal'];
	
	
	if ($akhir < 0){
		
		//$akhir = "(".number_format (abs($akhir), '0', ',', '.').")";
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
	}else{
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
	}

if ($awal < 0){
		
		//$awal = "(".number_format (abs($awal), '0', ',', '.').")";
				$awal =number_format (compat_num($row['saldo_awal']), '0', ',', '.');
	}else{
		$awal =number_format (compat_num($row['saldo_awal']), '0', ',', '.');
	}

	
	// jika ada total atau sub total
	if ($row['LINE_BALANCE'] == 'HEADING') {
		$class = ' class="subtotal"';
	} else if ($row['kode_rekening'] == 'SUB TOTAL' || $row['nama_rekening'] == 'SUB TOTAL') {
	$class = ' class="subtotal"';
	}
	
	echo 
	'<tr'.$class.'>
			<td>' . $row['LINE_BALANCE'] . '</td>
		<td>' . $row['kode_rekening'] . '</td>
		<td>' . $row['nama_rekening'] . '</td>
		<td>' . $awal. '</td>
		<td>' . number_format(compat_num($row['mut_debet']), '0', ',', '.') . '</td>
		<td>' . number_format(compat_num($row['mut_kredit']), '0', ',', '.') . '</td>
		<td>' . $akhir. '</td>
		</tr>';
		$total += $row['total'];
}
	
	
	 $sql = mysql_query("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,saldo_jalan,LINE_BALANCE
FROM ".$tabel."
where unit ='$unit' and posisi='rugi-laba' and gr_neraca='rugi-laba' and kode_rekening not in ('59999')  and tanggal_awal = '$tgl_cari'
GROUP BY gr_neraca,kode_rekening ");

$total_pendapan=mysql_fetch_array(mysql_query("select mut_debet,mut_kredit,saldo_awal,saldo_akhir from ".$tabel." where unit ='$unit' and kode_rekening='40000' and tanggal_awal = '$tgl_cari'"));
$total_biaya=mysql_fetch_array(mysql_query("select mut_debet,mut_kredit,saldo_awal,saldo_akhir from ".$tabel." where unit ='$unit' and kode_rekening='50000' and tanggal_awal = '$tgl_cari'"));


$shu_ops_awal=$total_pendapan['saldo_awal'] - $total_biaya['saldo_awal'];
$shu_ops_mut_debet=$total_pendapan['mut_debet'] - $total_biaya['mut_debet'];
$shu_ops_mut_kredit=$total_pendapan['mut_kredit'] - $total_biaya['mut_kredit'];
$shu_ops_akhir=$total_pendapan['saldo_akhir'] - $total_biaya['saldo_akhir'];

$total_pendapan_n=mysql_fetch_array(mysql_query("select mut_debet,mut_kredit,saldo_awal,saldo_akhir from ".$tabel." where unit ='$unit' and kode_rekening='57000'  and tanggal_awal = '$tgl_cari'"));
$total_beban=mysql_fetch_array(mysql_query("select mut_debet,mut_kredit,saldo_awal,saldo_akhir from ".$tabel." where unit ='$unit' and kode_rekening='58000' and tanggal_awal = '$tgl_cari'"));


$shu_non_ops_awal=$total_pendapan_n['saldo_awal'] - $total_beban['saldo_awal'];
$shu_non_ops_mut_debet=$total_pendapan_n['mut_debet'] - $total_beban['mut_debet'];
$shu_non_ops_mut_kredit=$total_pendapan_n['mut_kredit'] - $total_beban['mut_kredit'];
$shu_ops_non_akhir=$total_pendapan_n['saldo_akhir'] - $total_beban['saldo_akhir'];

$shu_sb_pajak_awal=$shu_ops_awal + $shu_non_ops_awal;
$shu_sb_pajak_mut_debet=$shu_ops_mut_debet + $shu_non_ops_mut_debet;
$shu_sb_pajak_mut_kredit=$shu_ops_mut_kredit + $shu_non_ops_mut_kredit;
$shu_sb_pajak_akhir=$shu_ops_akhir + $shu_ops_non_akhir;

//estimasi

$shu_after_pajak_awal=$shu_sb_pajak_awal - $estimasi_awal;
$shu_after_pajak_jalan=$shu_sb_pajak_jalan - $estimasi_jalan;
$shu_after_pajak_akhir=$shu_sb_pajak_akhir - $estimasi_jalan;

echo '<tr class="grey">
		<td colspan="3"><b>SHU OPERASIONAL</td>
		<td><b>' . number_format(compat_num($shu_ops_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_mut_debet), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_mut_kredit), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_akhir), 0, ',', '.') . '</td>
	 </tr>';

echo '<tr class="grey">
		<td colspan="3"><b>SHU NON OPERASIONAL</td>
		<td><b>' . number_format(compat_num($shu_non_ops_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_non_ops_mut_debet), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_non_ops_mut_kredit), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_non_akhir), 0, ',', '.') . '</td>
	 </tr>';
	 
echo '<tr class="grey">
		<td colspan="3"><b>SHU TAHUN BERJALAN SEBELUM PAJAK</td>
		<td><b>' . number_format(compat_num($shu_sb_pajak_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_sb_pajak_mut_debet), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_sb_pajak_mut_kredit), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_sb_pajak_akhir), 0, ',', '.') . '</td>
	 </tr>';	 
	 
	 echo '<tr class="grey">
		<td colspan="3"><b>ESTIMASI TAKSIRAN PAJAK PENGHASILAN</td>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td>
	 </tr>';
echo '<tr class="grey">
		<td colspan="3"><b>SHU TAHUN BERJALAN SETELAH PAJAK</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_mut_debet), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_mut_kredit), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_akhir), 0, ',', '.') . '</td>
	 </tr>	 	 



	</tbody>
</table>
</body>
</html>';
