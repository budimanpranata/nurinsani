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
				
			$sql = mysql_query("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,mut_debet,mut_kredit,LINE_BALANCE
FROM `tabel_master` 
where unit ='$unit' and posisi='Neraca' and gr_neraca='AKTIVA'
GROUP BY gr_neraca,kode_rekening ");

$total_ak=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from tabel_master where unit ='$unit' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING'  "));
$total_pa=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from tabel_master where unit ='$unit' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'  "));

echo '<html>
		<head>
			<title>NeracaPercobaan</title>
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
            <a href="neraca_excel.php" /><img src="../excel-icon.jpeg" width="16" height="16" />
    excel
</tr>

<tr>       
    <a href="neraca_pdf.php" /><img src="../pdf-icon.jpg" width="16" height="16" /> pdf
     
</tr> 
<br>
		<thead>
			<tr>
				<th>TYPE COA</th>
				<th>COA</th>
				<th>Deskripsi</th>
				<th>Saldo Akhir</th>
			</tr>
			
		</thead>
		<tbody>';
while($row=mysql_fetch_array($sql))
{
	$class = '';
	$akhir =$row['saldo_akhir'];
	$awal =$row['saldo_awal'];
	
	
	if ($akhir < 0){
		
		$akhir = "(".number_format (compat_num(abs(compat_num($akhir))), '0', ',', '.').")";
		
	}else{
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
	}

if ($awal < 0){
		
		$awal = "(".number_format (compat_num(abs(compat_num($awal))), '0', ',', '.').")";
		
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
		
		<td>' . $akhir. '</td>
		</tr>';
		$total += $row['total'];
}
	
	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL AKTIVA</td>
		
		<td><b>' . number_format(compat_num($total_ak['tot_saldo_akhir']), 0, ',', '.') . '</td>
	 </tr>';

$sql2 = mysql_query("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,mut_debet,mut_kredit,LINE_BALANCE
FROM `tabel_master` 
where unit ='001' and posisi='Neraca' and gr_neraca='PASIVA'
GROUP BY gr_neraca,kode_rekening ");
while($row=mysql_fetch_array($sql2))
{
	$class = '';
	$akhir =$row['saldo_akhir'];
	$awal =$row['saldo_awal'];
	
	
	if ($akhir < 0){
		
		$akhir = "(".number_format (compat_num(abs(compat_num($akhir))), '0', ',', '.').")";
		
	}else{
		$akhir =number_format (compat_num($row['saldo_akhir']), '0', ',', '.');
	}

if ($awal < 0){
		
		$awal = "(".number_format (compat_num(abs(compat_num($awal))), '0', ',', '.').")";
		
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
		
		<td>' . $akhir. '</td>
		</tr>';
		$total += $row['total'];
}
	
	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL PASIVA</td>
		
		<td><b>' . number_format(compat_num($total_pa['tot_saldo_akhir']), 0, ',', '.') . '</td>
	 </tr>


	</tbody>
</table>
</body>
</html>';