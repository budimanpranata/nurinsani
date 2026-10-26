<?php require_once __DIR__ . '/../../php8_compat.php';
 
//membuat header HTML
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
 
//melakukan koneksi ke database, parameter disesuaikan dengan lingkungan anda
include "../conn2.php";
	$koneksi=open_connection2();
 
//query yang telah dibuat
$sql = "SELECT
kode_rekening,
nama_rekening,
LINE_BALANCE,
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '1010', saldo_akhir, 0) ) AS '1010',
SUM(saldo_akhir) AS total_trx 
FROM
tabel_master_konsol where gr_neraca='rugi-laba'
group by kode_rekening";
 
//jalankan query dan akan menghasilkan mysqli result object
$query = mysql_query($sql);
$sql1 = "SELECT
branch.unit as unit from tabel_master_konsol
inner join branch 
on branch.kode_branch = tabel_master_konsol.unit
group by tabel_master_konsol.unit";
$query1 = mysql_query($sql1);

$total_pendapan=mysql_query("select unit,
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '1010', saldo_akhir, 0) ) AS '1010',
SUM(saldo_akhir) AS total_trx
from tabel_master_konsol where gr_neraca='rugi-laba'
and kode_rekening='40000' group by gr_head");

$total_biaya=mysql_query("select 
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '1010', saldo_akhir, 0) ) AS '1010',
SUM(saldo_akhir) AS total_trx
from tabel_master_konsol where gr_neraca='rugi-laba'
and kode_rekening='50000' group by gr_head");

$total_pendapan_n=mysql_query("select unit,
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '1010', saldo_akhir, 0) ) AS '1010',
SUM(saldo_akhir) AS total_trx
from tabel_master_konsol where gr_neraca='rugi-laba'
and kode_rekening='57000' group by gr_head");

$total_beban=mysql_query("select 
SUM( IF( unit = '001', saldo_akhir, 0) ) AS '001',
SUM( IF( unit = '002', saldo_akhir, 0) ) AS '002',
SUM( IF( unit = '003', saldo_akhir, 0) ) AS '003',
SUM( IF( unit = '004', saldo_akhir, 0) ) AS '004',
SUM( IF( unit = '005', saldo_akhir, 0) ) AS '005',
SUM( IF( unit = '006', saldo_akhir, 0) ) AS '006',
SUM( IF( unit = '007', saldo_akhir, 0) ) AS '007',
SUM( IF( unit = '008', saldo_akhir, 0) ) AS '008',
SUM( IF( unit = '009', saldo_akhir, 0) ) AS '009',
SUM( IF( unit = '010', saldo_akhir, 0) ) AS '010',
SUM( IF( unit = '1010', saldo_akhir, 0) ) AS '1010',
SUM(saldo_akhir) AS total_trx
from tabel_master_konsol where gr_neraca='rugi-laba'
and kode_rekening='58000' group by gr_head");





 //check apakah object query tercipta
if($query)
{
    //buat table
    echo '<table class="grey">
	<tr>
            <a href="excel_konsol.php" /><img src="../excel-icon.jpeg" width="16" height="16"/>
    Pl Konsol
</tr>';
    echo '<thead>';
    echo '<tr>';
	echo '<th>TYPE COA</th>';
    echo '<th>COA</th>';
    echo '<th>DESKRIPSI</th>';
	 while($row2=mysql_fetch_array($query1)){
    echo '<th>'.$row2['unit'].'</th>';
	 }
    echo '<th>KONSOLIDASI</th>';
    echo '</tr>';
    echo '</thead>';
 
    echo '<tbody>';
    //ambil data per row
		// jika ada total atau sub total

   while($row=mysql_fetch_array($query))
    
	{
			if ($row['LINE_BALANCE'] == 'HEADING') {
		$class = ' class="subtotal"';
	} else if ($row['kode_rekening'] == 'SUB TOTAL' || $row['nama_rekening'] == 'SUB TOTAL') {
		//$class = ' class="subtotal"';
	}	
        echo '<tr>';
		echo '<td>'.$row['LINE_BALANCE'].'</td>';
        echo '<td>'.$row['kode_rekening'].'</td>';
        echo '<td align="Left">'.$row['nama_rekening'].'</td>';
        echo '<td align="right">'.number_format(compat_num($row['001']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['002']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['003']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['004']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['005']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['006']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['007']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['008']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['009']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['010']),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($row['1010']),0, ',', '.').'</td>';
        echo '<td align="right">'.number_format(compat_num($row['total_trx']),0, ',', '.').'</td>';
        echo '</tr>';
    }
	
	while($row2=mysql_fetch_array($total_biaya)){
	$unit1b=$row2['001'];
	$unit2b=$row2['002'];
	$unit3b=$row2['003'];
	$unit4b=$row2['004'];
	$unit5b=$row2['005'];
	$unit6b=$row2['006'];
	$unit7b=$row2['007'];
	$unit8b=$row2['008'];
	$unit9b=$row2['009'];
	$unit10b=$row2['001'];
	$unit1010b=$row2['1010'];
	$total_b=$row2['total_trx'];
		}
	echo '<tr class="grey">
		<td colspan="3"><b>SHU OPERASIONAL</td>';
		
		while($row=mysql_fetch_array($total_pendapan))
    {

	$unit1p=$row['001']-$unit1b;
	$unit2p=$row['002']-$unit2b;
	$unit3p=$row['003']-$unit3b;
	$unit4p=$row['004']-$unit4b;
	$unit5p=$row['005']-$unit5b;
	$unit6p=$row['006']-$unit6b;
	$unit7p=$row['007']-$unit7b;
	$unit8p=$row['008']-$unit8b;
	$unit9p=$row['009']-$unit9b;
	$unit10p=$row['010']-$unit10b;
	$unit1010p=$row['1010']-$unit1010b;
	$totalp=$unit1p+$unit2p+$unit3p+$unit4p+$unit5p+$unit6p+$unit7p+$unit8p+$unit9p+$unit1010p;
	}
        echo '<td align="right">'.number_format(compat_num($unit1p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit2p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit3p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit4p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit5p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit6p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit7p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit8p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit9p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit10p),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit1010p),0, ',', '.').'</td>';
        echo '<td align="right">'.number_format(compat_num($totalp),0, ',', '.').'</td>';
        echo '</tr>';
	

	while($row2=mysql_fetch_array($total_beban)){
	$unit1=$row2['001'];
	$unit2=$row2['002'];
	$unit3=$row2['003'];
	$unit4=$row2['004'];
	$unit5=$row2['005'];
	$unit6=$row2['006'];
	$unit7=$row2['007'];
	$unit8=$row2['008'];
	$unit9=$row2['009'];
	$unit10=$row2['001'];
	$unit1010=$row2['1010'];
	$total_b=$row2['total_trx'];
		}
	echo '<tr class="grey">

		<td colspan="3"><b>SHU NON OPERASIONAL</td>';
			while($row=mysql_fetch_array($total_pendapan_n))
    {

	$unit1n=$row['001']-$unit1;
	$unit2n=$row['002']-$unit2;
	$unit3n=$row['003']-$unit3;
	$unit4n=$row['004']-$unit4;
	$unit5n=$row['005']-$unit5;
	$unit6n=$row['006']-$unit6;
	$unit7n=$row['007']-$unit7;
	$unit8n=$row['008']-$unit8;
	$unit9n=$row['009']-$unit9;
	$unit10n=$row['010']-$unit10;
	$unit1010n=$row['1010']-$unit1010;
	$totaln=$unit1n+$unit2n+$unit3n+$unit4n+$unit5n+$unit6n+$unit7n+$unit8n+$unit9n+$unit10n+$unit1010n;
	}
echo '<td align="right">'.number_format(compat_num($unit1n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit2n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit3n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit4n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit5n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit6n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit7n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit8n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit9n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit10n),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($unit1010n),0, ',', '.').'</td>';
        echo '<td align="right">'.number_format(compat_num($totaln),0, ',', '.').'</td>';
        echo '</tr>';
	$shu1_sb=$unit1n + $unit1p;
	$shu2_sb=$unit2n + $unit2p;
	$shu3_sb=$unit3n + $unit3p;
	$shu4_sb=$unit4n + $unit4p;
	$shu5_sb=$unit5n + $unit5p;
	$shu6_sb=$unit6n + $unit6p;
	$shu7_sb=$unit7n + $unit7p;
	$shu8_sb=$unit8n + $unit8p;
	$shu9_sb=$unit9n + $unit9p;
	$shu10_sb=$unit10n+ $unit10p;
	$shu1010_sb=$unit1010n + $unit1010p;
	$shutot_sb=$shu1_sb+$shu2_sb+$shu3_sb+$shu4_sb+$shu5_sb+$shu6_sb+$shu7_sb+$shu8_sb+$shu9_sb+$shu10_sb+$shu1010_sb;
echo '<tr class="grey">

		<td colspan="3"><b>SHU TAHUN BERJALAN SEBELUM PAJAK</td>';
	echo '<td align="right">'.number_format(compat_num($shu1_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu2_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu3_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu4_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu5_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu6_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu7_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu8_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu9_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu10_sb),0, ',', '.').'</td>';
		echo '<td align="right">'.number_format(compat_num($shu1010_sb),0, ',', '.').'</td>';
        echo '<td align="right">'.number_format(compat_num($shutot_sb),0, ',', '.').'</td>';
        echo '</tr>';
//Estimasi Perhitungan Pajak
//sumbangan dan Jamuan 
//SHU_SEBELUMPAJAK
$sbg_jamu=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where kode_rekening in('56211','56212','56311','56312')"));

$smb_jamu=$sbg_jamu['saldo_akhir'];


$pendapatan=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from tabel_master_konsol where kode_rekening in('40000')"));
$pendapatan_p=$pendapatan['saldo_akhir'];
//var_dump($shutot_sb);

//SHU sebelum pajak tahun berjalan fiskal
$shu_sbp=$shutot_sb+$smb_jamu;

//Pendapatan
$totalp=$pendapatan_p;
//var_dump($totalp);


//parameter pajak 
$param_pajak='4800000000';
//persentase
$persen =($param_pajak/$totalp);



//taksiran pajak mendapat psilitas 
$taksiran_dapat=($shu_sbp * $persen);
//var_dump($taksiran_dapat);

//Taksiran tidak Mendapatan Pasilitas
$taksiran_tidak=$shu_sbp - $taksiran_dapat;
//var_dump($taksiran_tidak);
//Taksitan Pajaka
//25% X 50% X shu sebelum pajak
$tks_pajak=0.125* $taksiran_dapat;
//25 * b`
$tsk_b=(25/100) * $taksiran_tidak;

$estimasi_taksiran=$tks_pajak + $tsk_b;


	 echo '<tr class="grey">
		<td colspan="3"><b>TAKSIRAN PAJAK PENGHASILAN</td>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_akhir1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_awal1), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_ops_jalan1), 0, ',', '.') . '</td>

	 </tr>';
echo '<tr class="grey">
		<td colspan="3"><b>SHU TAHUN BERJALAN SETELAH PAJAK</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_jalan), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_jalan), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_jalan), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_jalan), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_awal), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_jalan), 0, ',', '.') . '</td>
		<td><b>' . number_format(compat_num($shu_after_pajak_jalan), 0, ',', '.') . '</td>
		<<td align="right"><b>' . number_format(compat_num($estimasi_taksiran), 0, ',', '.') . '</td>
	 </tr>';	 	 
    echo '</tbody>';
    echo '</table>';
	

}
else
{
    echo '<p>Gagal menjalankan query. Aplikasi dihentikan</p>';
}
 
//tutup koneksi
 
echo '</body>';
echo '</html>';
 
?>