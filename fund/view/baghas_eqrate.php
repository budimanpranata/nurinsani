
<?php require_once __DIR__ . '/../../koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$buss_date1=$_SESSION['tgl_awal_pos'];
$buss_date2=$_SESSION['tgl_akhir_pos'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
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

$perode_bulan=date("Ymd");
		 $tahun = substr($perode_bulan,0,4);
					$bulan=substr($perode_bulan,4,2);
					$code_bln_f="NI$tahun-$bulan";
	$query_jml_aset=mysql_fetch_array(mysql_query("select nomi_murab,marigin_murab,DATEDIFF(sd,dari) as hari from param_finan where code_param='NI2025-07'"));
	$tot_fin=$query_jml_aset['nomi_murab'];
	$margin=$query_jml_aset['marigin_murab'];
	$hari=$query_jml_aset['hari'];
$hari=30;

			$sql = mysql_query("SELECT norek,deposito.cif,CUST_SHORT_NAME,bilyet,nominal,tenor,deposito_detail.tgl_jatuh_tempo as tgl_bayar,star_date, maturity_date,aro,rasio,eq_rate,last_payment,
DATE_ADD(tgl_bayar, INTERVAL -1 MONTH) as tgl_sb,
DATEDIFF(tgl_bayar,DATE_ADD(tgl_bayar, INTERVAL -1 MONTH)) as hari_rate 
			from deposito 
			left join deposito_detail on deposito.norek=deposito_detail.id_deposito 
			left join deposan on deposito.cif=deposan.cif 
			where deposito_detail.tgl_jatuh_tempo between '$buss_date1' and '$buss_date2'
			group by norek  order by CUST_SHORT_NAME
			");


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
	<B>LAPORAN BAGIHASIL DAN EQ RATE</b><br/>
	<B>PERIODE  ".$buss_date1." s.d ".$buss_date2." </B>  <br/>
	
	<strong></strong><br/>";
		

echo '<table class="grey">

<br>
		<thead>
			<tr>
				<th rowspan="2">Norek</th>
				<th rowspan="2">Cif</th>
				<th rowspan="2">Nama</th>
				<th rowspan="2">No. Bilyet</th>
				<th rowspan="2">Nominal</th>
				<th rowspan="2">Tenor</th>
				<th rowspan="2">Tanggal Kontrak</th>
				<th rowspan="2">Jatuh tempo</th>
				<th rowspan="2">Tgl Bayar</th>
				<th rowspan="2">Status</th>
				<th colspan="2">Nisbah</th>
				
				<th colspan="4">Bagi Hasil</th>
				<th rowspan="2">Eq Rate</th>
				<th rowspan="2">Nisbah</th>
				<th colspan="4">Rate</th>
				
				
			</tr>
			<tr>
			<th>Nasabah</th>
				<th>Koperasi</th>
				<th>Nisbah</th>
				<th>Gross Nisbah</th>
				<th>PPh4 Ayat 2</th>
				<th>Jml. Bayar</th>
				<th>Rate</th>
				<th>Gross Rate</th>
				<th>PPh4 Ayat 2</th>
				<th>Jml. Bayar Rate</th>
				</tr>
		</thead>
		<tbody>';
while($row=mysql_fetch_array($sql))
{
	$class = '';
	$last_payment=$row['last_payment'];
		$dt1 = new DateTime($last_payment);
		$dt2 = new DateTime($tanggal);
		$hari_stay =$dt2->diff($dt1)->days + 0;

	if($hari_stay>31){
		$hari=0;
	}else{
		$hari=$hari_stay;
	}
$hari =31;

	$norek=$row['norek'];
	$D="'";
	$no="$D$norek";
	$rasio=$row['rasio'];
	$kop=100-$rasio;
	$nomi=$row['nominal'];
	$hari_rate=$row['hari_rate'];
	$eq_rate=$row['eq_rate'];
	$kali_eq=$eq_rate/100;
	
		
	$param_rasio = mysql_query ("select rasio_invest,eq,rasio_eks from param_dep where rasio_invest = '$rasio'");
		$param = mysql_fetch_array($param_rasio);
		$eq=$param['eq'];
		$rasio_invest=$param['rasio_invest'];
		$rasio_eks=$param['rasio_eks'];
		//Perhitungan Nisbah
		$bayar=($nomi*$eq)*$hari/365;
		$gross=$bayar/0.9;
		$pph4=$gross-$bayar;
		$jlbayar=$gross-$pph4;
		//perhitungan Rate 
		$bayar_rate=($nomi*$kali_eq)*$hari/365;
		$gross_rate=$bayar_rate/0.9;
		$pph4_rate=$gross_rate-$bayar_rate;
		$jlbayar_rate=$gross_rate-$pph4_rate;
		
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
		//$class = ' class="subtotal"';
	}
	
	echo 
	'<tr'.$class.'>
		<td>' . $no . '</td>
		<td>' . $row['cif'] . '</td>
		<td>' . $row['CUST_SHORT_NAME'] . '</td>
		<td>' . $row['bilyet'] . '</td>
		<td>' . $row['nominal'] . '</td>
		<td>' . $row['tenor'] . '</td>
		<td>' . $row['star_date'] . '</td>
		<td>' . $row['maturity_date'] . '</td>
		<td>' . $row['tgl_bayar'] . '</td>
		<td>' . $row['aro'] . '</td>
		<td>' . $row['rasio'] . '</td>
		<td>' . $kop . '</td>
		
		<td>' . number_format (compat_num($bayar), '0', ',', '.') . '</td>
		<td>' . number_format (compat_num($gross), '0', ',', '.') . '</td>
		<td>' . number_format (compat_num($pph4), '0', ',', '.') . '</td>
		<td>' . number_format (compat_num($jlbayar), '0', ',', '.') . '</td>
		<td>' . $row['eq_rate'] . '</td>
		<td>' . $rasio_eks . '</td>
		<td>' . number_format (compat_num($bayar_rate), '0', ',', '.') . '</td>
		<td>' . number_format (compat_num($gross_rate), '0', ',', '.') . '</td>
		<td>' . number_format (compat_num($pph4_rate), '0', ',', '.') . '</td>
		<td>' . number_format (compat_num($jlbayar_rate), '0', ',', '.') . '</td>
		</tr>';
		$total += $row['total'];
}
	
	
	
	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL </td>
		<td><b>' . number_format(compat_num($total_ak['tot_saldo_awal']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_ak['tot_mut_debet']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_ak['tot_mut_kredit']), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($total_ak['tot_saldo_akhir']), 0, ',', '.') . '</td>
	 </tr>


	</tbody>
</table>
</body>
</html>';
