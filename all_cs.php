<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
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
 include "indo_tgl.php";
$koneksi=open_connection2();
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=neraca.xls");

$code_kel=$_GET['kode_kel'];

$sql111=mysql_query("Select nama_ao FROM ao WHERE cao='$cao'");
$ca= mysql_fetch_array($sql111);
$nama_sales=$ca['nama_ao'];


$sql1=mysql_query("Select nama,kode_branch FROM user WHERE userid='$userid'");
$dsmo= mysql_fetch_array($sql1);
$kode_branch=$dsmo['kode_branch'];

$sql2=mysql_query("Select * FROM branch WHERE kode_branch='$kode_branch'");
$br= mysql_fetch_array($sql2);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];

$sql4=mysql_query("Select nama_kel,loan.code_kel,next_schedule  FROM loan 
inner join kelompok on kelompok.code_kel = loan.code_kel WHERE loan.code_kel='$code_kel'");
$cd= mysql_fetch_array($sql4);
$nama_kel=$cd['nama_kel'];
$hari=$cd['next_schedule'];
$hari2=tgl_indo($tgl_jatuh_tempo);
$day = date('D', strtotime($tgl_jatuh_tempo));
$dayList = array(
	'Sun' => 'Minggu',
	'Mon' => 'Senin',
	'Tue' => 'Selasa',
	'Wed' => 'Rabu',
	'Thu' => 'Kamis',
	'Fri' => 'Jumat',
	'Sat' => 'Sabtu'
);
			
			$sql=mysql_query("select loan.Plafond as tot_pla,loan.unit,ao.nama_ao,tenor,loan.cif,ke,run_tenor,nama_kel,loan.cao, loan.cust_Short_name,loan.Plafond,loan.bagi_hasil,bulat,os,loan.angsuran,loan.bulat,hari
 from loan
LEFT join anggota on anggota.CIF = loan.cif
inner join kelompok on kelompok.code_kel=loan.code_kel
LEFT join ao on ao.cao=loan.cao where group by loan.cif");

if(mysql_num_rows($sql)==0){
$sql=mysql_query("select loan.Plafond as tot_pla,sum(bulat)as tot_bulat,loan.unit,ao.nama_ao, loan.cif,ke,run_tenor,sum(KREDIT)-sum(DEBIT) as saldo,nama_kel,loan.cao, loan.cust_Short_name,loan.Plafond,loan.bagi_hasil,sum(loan.bagi_hasil) as baghas,bulat,os,loan.angsuran,sum(loan.angsuran)tot_angsur,loan.bulat,hari
 from loan
LEFT join anggota on anggota.CIF = loan.cif
inner join kelompok on kelompok.code_kel=loan.code_kel
INNER join simpanan on simpanan.cif=loan.cif
LEFT join pinjaman_detail_jatpo on loan.no_anggota=pinjaman_detail_jatpo.id_pinjam
LEFT join ao on ao.cao=loan.cao group by loan.cif");
		
		 }


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
	<B>DAFTAR TAGIHAN PEMBIAYAAN</b><br/>
	
	<strong></strong><br/>";
		

echo '<table class="grey">

<br>
		<thead>
			<tr>
				<th>No </th>
				<th>Unit</th>
				<th>Norek</th>
				<th>Nama Nasabah</th>
				<th>Palfond</th>
				<th>Margin</th>
				<th>Tenor</th>
				<th>Ke</th>
				<th>PB</th>
				<th>Pembiayaan</th>
				<th>Tabungan</th>
				<th>Pokok dan Wajib</th>
				<th>FT</th>
				<th>Tunggakan</th>
				<th>Angsuran</th>
				<th>Setoran</th>
				<th>Hari</th>

				
				</tr>
		</thead>
		<tbody>';
while($result=mysql_fetch_array($sql))
{
	$no ++;
 $TGL_KUNJUNGAN = $result['nama_ao'];
$cif = $result['cif'];
$cao = $result['cao'];
$t=$result['angsuran'];
$cust_Short_name= $result['cust_Short_name'];
$Plafond=  ($result['Plafond']);  
$angsuran = ($result['angsuran']);
$bulat =  ($result['bulat']);
$unit = $result['unit'];
$ke = $result['ke'];
$tenor = $result['tenor'];
$hari = $result['hari'];
$bulat =  ($result['bulat']);
$saldo =  ($result['saldo']);
$run_tenor = $result['run_tenor'];

$os = ($result['os']);
$norek="$cao-$cif";
$bagi_hasil = ($result['bagi_hasil']);
$cek_pokok= mysql_fetch_array(mysql_query("select sum(KREDIT)- sum(DEBIT) as pokok from tunggakan WHERE cif='$cif' and type='01'"));
$tunggak_pokok =$cek_pokok['pokok'];
$cek_margin= mysql_fetch_array(mysql_query("select sum(KREDIT)- sum(DEBIT) as margin from tunggakan WHERE cif='$cif' and type='02'"));
$tunggak_margin =$cek_margin['margin'];
$cek_saldo= mysql_fetch_array(mysql_query("select sum(KREDIT - DEBIT) as saldo from simpanan WHERE cif='$cif' and type not in ('04','05')"));
$saldo =$cek_saldo['saldo'];

$cek_saldo_p= mysql_fetch_array(mysql_query("select sum(KREDIT - DEBIT) as saldo from simpanan_pokok WHERE cif='$cif'"));
$saldo_p =$cek_saldo_p['saldo'];
$cek_saldo_w= mysql_fetch_array(mysql_query("select sum(KREDIT - DEBIT) as saldo from simpanan_wajib WHERE cif='$cif'"));
$saldo_w =$cek_saldo_w['saldo'];
$wp=($saldo_p + $saldo_w);


$tunggak=$tunggak_pokok + $tunggak_margin;
$tg=($tunggak);
//$ft=$tunggak/$t;

if ($tunggak!=0) {
$ft=$tunggak/$t;
} else {
 $ft=0;
}

$ft=($ft);

	$class = '';
	
	// jika ada total atau sub total
		if ($row['LINE_BALANCE'] == 'HEADING') {
		$class = ' class="subtotal"';
	} else if ($row['kode_rekening'] == 'SUB TOTAL' || $row['nama_rekening'] == 'SUB TOTAL') {
		//$class = ' class="subtotal"';
	}
	
	echo 
	'<tr'.$class.'>
		<td>' . $no . '</td>
		<td>' . $unit . '</td>
		<td>' . $norek . '</td>
		<td>' . $cust_Short_name . '</td>
		<td>' . number_format(compat_num($Plafond ), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($result['bagi_hasil']), 0, ',', '.'). '</td>
		<td>' . $run_tenor. '</td>
		<td>' . $tenor. '</td>
		<td>' . $ke. '</td>
		<td>' . number_format(compat_num($result['os']), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($saldo), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($wp), 0, ',', '.'). '</td>
		<td>' . $ft. '</td>
		<td>' . number_format(compat_num($tg), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($angsuran), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($bulat), 0, ',', '.'). '</td>
		<td>' . $hari. '</td>
		</tr>';
}
echo '<tr class="grey">
		
		
	 </tr>	 	 
</tbody>
</table>
</body>
</html>';
