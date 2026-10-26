<?php require_once __DIR__ . '/../../koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];

//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
}

//cek level user
if($_SESSION['level']!="unit"){
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
$user=mysql_query("select kode_branch from user where userid='$userid'");
			
			while($id_user=mysql_fetch_array($user)){
			
				$unit=$id_user['kode_branch'];
			}
			
$sql = mysql_query("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,saldo_jalan,LINE_BALANCE
FROM `tabel_rugi_laba` 
where unit ='$unit' and posisi='rugi-laba' and gr_neraca='rugi-laba' and kode_rekening not in ('59999')
GROUP BY gr_neraca,kode_rekening ");

$total_pendapan=mysql_fetch_array(mysql_query("select saldo_jalan,saldo_awal,saldo_akhir from tabel_rugi_laba where unit ='$unit' and kode_rekening='40000'"));
$total_biaya=mysql_fetch_array(mysql_query("select saldo_jalan,saldo_awal,saldo_akhir from tabel_rugi_laba where unit ='$unit' and kode_rekening='50000'"));


$shu_ops_awal=$total_pendapan['saldo_awal'] - $total_biaya['saldo_awal'];
$shu_ops_jalan=$total_pendapan['saldo_jalan'] - $total_biaya['saldo_jalan'];
$shu_ops_akhir=$total_pendapan['saldo_akhir'] - $total_biaya['saldo_akhir'];

$total_pendapan_n=mysql_fetch_array(mysql_query("select saldo_jalan,saldo_awal,saldo_akhir from tabel_rugi_laba where unit ='$unit' and kode_rekening='57000'"));
$total_beban=mysql_fetch_array(mysql_query("select saldo_jalan,saldo_awal,saldo_akhir from tabel_rugi_laba where unit ='$unit' and kode_rekening='58000'"));


$shu_non_ops_awal=$total_pendapan_n['saldo_awal'] - $total_beban['saldo_awal'];
$shu_non_ops_jalan=$total_pendapan_n['saldo_jalan'] - $total_beban['saldo_jalan'];
$shu_ops_non_akhir=$total_pendapan_n['saldo_akhir'] - $total_beban['saldo_akhir'];

$shu_sb_pajak_awal=$shu_ops_awal + abs(compat_num($shu_non_ops_awal));
$shu_sb_pajak_jalan=$shu_ops_jalan + abs(compat_num($shu_non_ops_jalan));
$shu_sb_pajak_akhir=$shu_ops_akhir + abs(compat_num($shu_ops_non_akhir));

//estimasi

$shu_after_pajak_awal=$shu_sb_pajak_awal - $estimasi_awal;
$shu_after_pajak_jalan=$shu_sb_pajak_jalan - $estimasi_jalan;
$shu_after_pajak_akhir=$shu_sb_pajak_akhir - $estimasi_jalan;

echo '<html>
		<head>
			<title>LABA RUGI</title>
			<style>
				body {font-family:tahoma, arial}
				table {border-collapse: collapse}
				th, td {font-size: 13px; border: 1px solid #DEDEDE; padding: 5px 7px; color: #303030}
				th {background: #EAEAEA; font-size: 13px; border-color:#B0B0B0}
				.subtotal td{background: #F5F5F5}
				.total td{background: #ECECEC}
				.right{text-align: right}
			</style>
		</head>
		<body>';

echo '<table class="grey">
<tr>
            <a href="laba_excel.php" /><img src="../excel-icon.jpeg" width="16" height="16" />
    excel
</tr>

<tr>       
    <a href="neraca_pdf.php" /><img src="../pdf-icon.jpg" width="16" height="16" /> pdf
     
</tr> 
<br>   
		<thead>
			<tr>
				<th colspan="">TYPE COA</th>
				<th colspan="">COA</th>
				<th colspan="">Deskripsi</th>
				<th colspan="">Saldo Akhir</th>
			</tr>
			
		</thead>
		<tbody>';
while($row=mysql_fetch_array($sql))
{
	$class = '';
	$akhir =$row['saldo_akhir'];
	$awal =$row['saldo_awal'];
	
	
	
	if ($akhir < 0){
		
		$akhir = "".number_format (compat_num(abs(compat_num($akhir))), '0', ',', '.')."";
		
	}else{
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
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
		
		<td>' . $akhir. '</td>
		</tr>';
		$total += $row['total'];
}
	
	echo '<tr class="grey">
		<td colspan="3"><b>SHU OPERASIONAL</td>
	
		<td><b>' . number_format(compat_num($shu_ops_akhir), 0, ',', '.') . '</td>
	 </tr>';

echo '<tr class="grey">
		<td colspan="3"><b>SHU NON OPERASIONAL</td>
			<td><b>' . number_format(compat_num(abs(compat_num($shu_ops_non_akhir))), 0, ',', '.') . '</td>
	 </tr>';
	 
echo '<tr class="grey">
		<td colspan="3"><b>SHU TAHUN BERJALAN SEBELUM PAJAK</td>
				<td><b>' . number_format(compat_num($shu_sb_pajak_akhir), 0, ',', '.') . '</td>
	 </tr>';	 
	 
	 echo '<tr class="grey">
		<td colspan="3"><b>ESTIMASI TAKSIRAN PAJAK PENGHASILAN</td>
		
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td>
	 </tr>';
echo '<tr class="grey">
		<td colspan="3"><b>SHU TAHUN BERJALAN SETELAH PAJAK</td>
		
		<td><b>' . number_format(compat_num($shu_after_pajak_akhir), 0, ',', '.') . '</td>
	 </tr>	 	 
	 



	</tbody>
</table>
</body>
</html>';