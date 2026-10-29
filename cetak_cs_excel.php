<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
ob_start();
$userid=$_SESSION['userid'];
$tgl_jatuh_tempo=$_SESSION['jatuh'];
$cao=$_SESSION['code_cao'];
//cek apakah user sudah login
if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

//cek level user
if($_SESSION['level']!="unit"){
   header("location:../index.html");
 exit; }
function open_connection2(){

	//konfigurasi
	$host="localhost";
	$user="root";
	$pass="anaskorti";
	$db="nur_insani";
	
	
	//koneksi database
	$koneksi2=db_ni();
	mysqli_select_db($koneksi2, $db);
	
	return $koneksi2;
}
 include "indo_tgl.php";
$koneksi=open_connection2();
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=neraca.xls");

$code_kel=$_GET['kode_kel'];

$ca = db_satu("Select nama_ao FROM ao WHERE cao=?", 's', (string) $cao);
$nama_sales=$ca['nama_ao'];


$dsmo = db_satu("Select nama,kode_branch FROM user WHERE userid=?", 's', (string) $userid);
$kode_branch=$dsmo['kode_branch'];

$br = db_satu("Select * FROM branch WHERE kode_branch=?", 's', (string) $kode_branch);
$nama_unit=$br['unit'];
$alamat=$br['alamat'];

$cd = db_satu("Select nama_kel,loan.code_kel,next_schedule  FROM loan 
inner join kelompok on kelompok.code_kel = loan.code_kel WHERE loan.code_kel=?", 's', (string) $code_kel);
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
			
			$sql = db_semua("select loan.Plafond as tot_pla,sum(bulat)as tot_bulat,loan.unit,ao.nama_ao, loan.cif,ke,run_tenor,sum(KREDIT)-sum(DEBIT) as saldo,nama_kel,loan.cao, loan.cust_Short_name,loan.Plafond,loan.bagi_hasil,sum(loan.bagi_hasil) as baghas,bulat,os,loan.angsuran,sum(loan.angsuran)tot_angsur,loan.bulat
 from loan
LEFT join anggota on anggota.CIF = loan.cif
inner join kelompok on kelompok.code_kel=loan.code_kel
INNER join simpanan on simpanan.cif=loan.cif
LEFT join ao on ao.cao=loan.cao where loan.code_kel=? AND loan.hari=? and loan.cao=? and os > 0 group by loan.cif", 'sss', (string) $code_kel, (string) $dayList[$day], (string) $cao);

if(count($sql)==0){
$sql = db_semua("select loan.Plafond as tot_pla,sum(bulat)as tot_bulat,loan.unit,ao.nama_ao, loan.cif,ke,run_tenor,sum(KREDIT)-sum(DEBIT) as saldo,nama_kel,loan.cao, loan.cust_Short_name,loan.Plafond,loan.bagi_hasil,sum(loan.bagi_hasil) as baghas,bulat,os,loan.angsuran,sum(loan.angsuran)tot_angsur,loan.bulat
 from loan
LEFT join anggota on anggota.CIF = loan.cif
inner join kelompok on kelompok.code_kel=loan.code_kel
INNER join simpanan on simpanan.cif=loan.cif
LEFT join pinjaman_detail_jatpo on loan.no_anggota=pinjaman_detail_jatpo.id_pinjam
LEFT join ao on ao.cao=loan.cao where loan.code_kel=? AND pinjaman_detail_jatpo.tgl_jatuh_tempo=? and loan.cao=? and os > 0 group by loan.cif", 'sss', (string) $code_kel, (string) $tgl_jatuh_tempo, (string) $cao);
		
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
	<B>UNIT - ".$nama_unit."</B>  <br/>
	<B>ALAMAT ".$alamat."</B>  <br/><br/>
	
	<B>Nama Kelompok : ".$nama_kel."</B>  <br/>
	<B>Hari Tanggal  : ".$dayList[$day]."</B>  <br/>
	<B>Nama Petugas : ".$nama_sales."</B>  <br/>
	<B>Kode Kelompok : ".$code_kel."</B>  <br/>
	<strong></strong><br/>";
		

echo '<table class="grey">

<br>
		<thead>
			<tr>
				<th>No </th>
				<th>Norek</th>
				<th>Nama Nasabah</th>
				<th>Palfond</th>
				<th>Margin</th>
				<th>Ke</th>
				<th>PB</th>
				<th>Pembiayaan</th>
				<th>Tabungan</th>
				<th>Pokok dan Wajib</th>
				<th>FT</th>
				<th>Tunggakan</th>
				<th>Angsuran</th>
				<th>Setoran</th>
				
				</tr>
		</thead>
		<tbody>';
foreach ($sql as $result)
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
$bulat =  ($result['bulat']);
$saldo =  ($result['saldo']);
$run_tenor = $result['run_tenor'];
$os = ($result['os']);
$norek="$cao-$cif";
$bagi_hasil = ($result['bagi_hasil']);
$cek_pokok= db_satu("select sum(KREDIT)- sum(DEBIT) as pokok from tunggakan WHERE cif=? and type='01'", 's', (string) $cif);
$tunggak_pokok =$cek_pokok['pokok'];
$cek_margin= db_satu("select sum(KREDIT)- sum(DEBIT) as margin from tunggakan WHERE cif=? and type='02'", 's', (string) $cif);
$tunggak_margin =$cek_margin['margin'];
$cek_saldo= db_satu("select sum(KREDIT - DEBIT) as saldo from simpanan WHERE cif=? and type not in ('04','05')", 's', (string) $cif);
$saldo =$cek_saldo['saldo'];

$cek_saldo_p= db_satu("select sum(KREDIT - DEBIT) as saldo from simpanan_pokok WHERE cif=?", 's', (string) $cif);
$saldo_p =$cek_saldo_p['saldo'];
$cek_saldo_w= db_satu("select sum(KREDIT - DEBIT) as saldo from simpanan_wajib WHERE cif=?", 's', (string) $cif);
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
		<td>' . $norek . '</td>
		<td>' . $cust_Short_name . '</td>
		<td>' . number_format(compat_num($Plafond ), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($result['bagi_hasil']), 0, ',', '.'). '</td>
		<td>' . $run_tenor. '</td>
		<td>' . $ke. '</td>
		<td>' . number_format(compat_num($result['os']), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($saldo), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($wp), 0, ',', '.'). '</td>
		<td>' . $ft. '</td>
		<td>' . number_format(compat_num($tg), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($angsuran), 0, ',', '.'). '</td>
		<td>' . number_format(compat_num($bulat), 0, ',', '.'). '</td>
		
		</tr>';
}
echo '<tr class="grey">
		
		
	 </tr>	 	 
</tbody>
</table>
</body>
</html>';