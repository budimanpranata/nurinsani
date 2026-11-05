<?php require_once __DIR__ . '/../../php8_compat.php';
// Mode export bertahap (KONSOL_EXPORT_JOB, dari neraca_konsol_export.php, perintah user 2026-11-04): sesi sudah dibaca dan
// ditutup oleh pemanggil (agar permintaan status progres tidak terkunci) dan header unduhan dikirim pemanggil.
if (!defined('KONSOL_EXPORT_JOB')) {
session_start();
// 2026-11-05: wajib login level kp (mode export sudah dicek neraca_konsol_export.php)
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ($_SESSION['level'] != "kp") {
    header("location:../../index.html");
    exit;
}
}
ob_start();
$_SESSION['units']=$unit;
$tgl_cari=$_SESSION['tgl_cari_konsols'];

if (!defined('KONSOL_EXPORT_JOB')) {
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=consol.xls");
}
 include"../../indo_tgl.php";

$jenis=$_SESSION['jenis'];
$tgl=tgl_indo($tgl_cari);
if($jenis=='eom'){
$sumber="tabel_master_konsol";
}elseif($jenis=='current'){
$sumber = "tabel_master_hari";
}
else{
$sumber = "tabel_master_eoy";
}
// var_dump($jenis) dibuang 2026-11-04: teks string(3) "eom" tertulis di baris pertama file Excel
//membuat header HTML
echo "<html>
		<head>
			<title>NeracaKonsolidasi</title>
		
			<style>
				body {font-family:tahoma, arial}
				table {border-collapse: collapse}
				th, td {font-size: 17px; border: 1px solid #DEDEDE; padding: 5px 7px; color: #303030}
				th {background: #EAEAEA; font-size: 17px; border-color:#B0B0B0}
				.subtotal td{background: #F5F5F5}
				.total td{background: #ECECEC}
				.right{text-align: rijprght}
			</style>
		</head>
		<body>
	<B>KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH</b><br/>
	<B>LAPORAN PERHITUNGAN HASIL USAHA</b><br/>
	<B>KONSOLIDASI</b><br/>
	<B> ".$tgl."</B>  <br/>
	
	<strong></strong><br/>";


//melakukan koneksi ke database, parameter disesuaikan dengan lingkungan anda
include "../conn2.php";
	$koneksi=open_connection2();

/*
 * 2026-11-05 (perintah user): kolom unit DINAMIS dari data. Dulu 93 unit ditulis tangan (SUM(IF(unit='001'...)) di
 * 8 query, 279 query total per unit, variabel $unitXp/$shuX_sb per unit) sehingga unit baru harus ditambah manual di
 * banyak tempat, dan judul kolom (dari data) bisa bergeser terhadap isi kolom (ditulis tangan).
 * Sekarang unit = semua unit yang ada di tabel sumber pada tanggal laporan (urut kode, 0000 Kantor Pusat paling kiri);
 * nama kolom dari tabel branch (kode unit bila tidak ada di branch). Rumus, urutan baris, dan format sel SAMA dengan
 * versi lama:
 *   - baris rekening AKTIVA / PASIVA / rugi-laba (tanpa 59999) / admin: saldo_akhir per unit + KONSOLIDASI semua unit
 *   - TOTAL AKTIVA unit = HEADING AKTIVA kecuali 1310000, 1330000, 1340000, 1350000
 *   - TOTAL PASIVA unit = rekening 3901000, 3902000, 2500000 (+ rekening tambahan khusus unit, lihat
 *     $pasiva_tambahan_unit) + HEADING PASIVA; kolom KONSOLIDASI memakai semua rekening tambahan untuk semua unit
 *   - SHU OPERASIONAL = 40000 - 50000, SHU NON OPERASIONAL = 57000 - 58000 per unit (baris terakhir per gr_head)
 *   - estimasi pajak hanya di kolom Kantor Pusat (0000); unit lain 0
 */
$tgl_cari = mysql_real_escape_string((string) $tgl_cari);

// rekening pasiva tambahan khusus unit tertentu (sama dengan query per unit versi lama)
$pasiva_dasar = array('3901000', '3902000', '2500000');
$pasiva_tambahan_unit = array('2008' => array('2611000'));
foreach (array('3000', '3001', '3002', '3003', '3004', '3005', '4000', '4001', '4002', '4003', '5000', '5001') as $u) {
    $pasiva_tambahan_unit[$u] = array('2761000', '2762000', '2763000');
}
$pasiva_semua = "'3901000','3902000','2500000','2761000','2762000','2763000','2611000'";

// daftar unit (kode => nama kolom)
$daftar_unit = array();
$q_unit = mysql_query("SELECT s.unit as kode, max(branch.unit) as nama from ".$sumber." s
left join branch on branch.kode_branch = s.unit
where s.tanggal_awal='$tgl_cari' group by s.unit order by s.unit");
while ($q_unit && $r = mysql_fetch_array($q_unit)) {
    $daftar_unit[(string) $r['kode']] = ($r['nama'] !== null && $r['nama'] !== '') ? $r['nama'] : $r['kode'];
}

// kolom saldo_akhir per unit, alias u_<kode> (kode unit bisa diawali angka 0)
$kolom_unit = '';
foreach ($daftar_unit as $kode => $nama) {
    $kolom_unit .= "SUM( IF( unit = '" . mysql_real_escape_string($kode) . "', saldo_akhir, 0) ) AS `u_" . $kode . "`,\n";
}

function konsol_sql_rekening($sumber, $kolom_unit, $saring)
{
    return "SELECT
kode_rekening,
nama_rekening,
LINE_BALANCE,
" . $kolom_unit . "SUM(saldo_akhir) AS total_trx
FROM
" . $sumber . " where " . $saring . "
group by kode_rekening order by kode_rekening";
}

/** Total per unit: array kode => jumlah saldo_akhir. */
function konsol_total_unit($sql)
{
    $hasil = array();
    $q = mysql_query($sql);
    while ($q && $r = mysql_fetch_array($q)) {
        $hasil[(string) $r['unit']] = $r['tot_saldo_akhir'];
    }
    return $hasil;
}

/** Nilai satu unit dari baris SUM(IF) (null bila tidak ada, seperti versi lama). */
function konsol_nilai($row, $kode)
{
    return is_array($row) && isset($row['u_' . $kode]) ? $row['u_' . $kode] : null;
}

$total_ak = konsol_total_unit("select unit, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where tanggal_awal='$tgl_cari' and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000') group by unit order by unit");
$total_ak_tot=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where gr_neraca='AKTIVA' and tanggal_awal='$tgl_cari' and LINE_BALANCE='HEADING' and kode_rekening not in('1310000','1330000','1340000','1350000')  "));

// pasiva khusus per unit: rekening dasar untuk semua unit + rekening tambahan hanya untuk unit yang ditentukan
$syarat_pa = "kode_rekening in ('" . implode("','", $pasiva_dasar) . "')";
foreach ($pasiva_tambahan_unit as $u => $rek) {
    $syarat_pa .= " or (unit ='" . $u . "' and kode_rekening in ('" . implode("','", $rek) . "'))";
}
$total_pa = konsol_total_unit("select unit, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where (" . $syarat_pa . ") and tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' group by unit order by unit");
$total_pa_tot=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where gr_neraca='PASIVA' and tanggal_awal='$tgl_cari' and kode_rekening in (" . $pasiva_semua . ")"));
$total_pax = konsol_total_unit("select unit, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where tanggal_awal='$tgl_cari' and gr_neraca='PASIVA' and LINE_BALANCE='HEADING' group by unit order by unit");
$total_pax_tot=mysql_fetch_array(mysql_query("select sum(mut_debet) as tot_mut_debet,  sum(mut_kredit) as tot_mut_kredit,
								sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from ".$sumber." where gr_neraca='PASIVA' and tanggal_awal='$tgl_cari' and LINE_BALANCE='HEADING'  "));

$query = mysql_query(konsol_sql_rekening($sumber, $kolom_unit, "gr_neraca='AKTIVA' and tanggal_awal='$tgl_cari' "));
$query_pa = mysql_query(konsol_sql_rekening($sumber, $kolom_unit, "gr_neraca='PASIVA' and tanggal_awal='$tgl_cari' "));
$query_laba = mysql_query(konsol_sql_rekening($sumber, $kolom_unit, "gr_neraca='rugi-laba' and tanggal_awal='$tgl_cari' and kode_rekening not in ('59999')"));
$query_admin = mysql_query(konsol_sql_rekening($sumber, $kolom_unit, "gr_neraca='admin' and tanggal_awal='$tgl_cari' "));

/** Baris terakhir (per gr_head) rekening total pendapatan/biaya, seperti while(...) versi lama. */
function konsol_baris_akhir($sumber, $kolom_unit, $tgl_cari, $kode_rekening)
{
    $akhir = null;
    $q = mysql_query("select unit,
" . $kolom_unit . "SUM(saldo_akhir) AS total_trx
from " . $sumber . "
where kode_rekening='" . $kode_rekening . "' and tanggal_awal='$tgl_cari' group by gr_head order by gr_head");
    while ($q && $r = mysql_fetch_array($q)) {
        $akhir = $r;
    }
    return $akhir;
}
$total_pendapan = konsol_baris_akhir($sumber, $kolom_unit, $tgl_cari, '40000');
$total_biaya = konsol_baris_akhir($sumber, $kolom_unit, $tgl_cari, '50000');
$total_pendapan_n = konsol_baris_akhir($sumber, $kolom_unit, $tgl_cari, '57000');
$total_beban = konsol_baris_akhir($sumber, $kolom_unit, $tgl_cari, '58000');

/** Satu baris rekening (format sel sama dengan versi lama; $akhir_sel = penutup sel). */
function konsol_tampil_rekening($query, $daftar_unit, $akhir_sel)
{
    while ($row = mysql_fetch_array($query)) {
        if ($row['LINE_BALANCE'] == 'HEADING') {
            $class = ' class="subtotal"';
        } else {
            $class = ' class="subtotall"';
        }
        echo '<tr '.$class.' >';
        echo '<td>'.$row['LINE_BALANCE'].'</td>';
        echo '<td>'.$row['kode_rekening'].'</td>';
        echo '<td align="Left">'.$row['nama_rekening'].'</td>';
        foreach ($daftar_unit as $kode => $nama) {
            echo '<td align="right">'.number_format(compat_num(konsol_nilai($row, $kode)),0, ',', '.').$akhir_sel;
        }
        echo '<td align="right">'.number_format(compat_num($row['total_trx']),0, ',', '.').$akhir_sel;
        echo '</tr>';
    }
}

/** Baris pemisah kosong di bawah total (versi lama: sel berisi variabel $t yang tidak pernah diisi). */
function konsol_tampil_pemisah($daftar_unit)
{
    echo '<tr class="grey">
		<td colspan="3"><b></td>';
    foreach ($daftar_unit as $kode => $nama) {
        echo '<td align="right"></td>';
    }
    echo '<td align="right"></td>';
    echo '</tr>';
}

 //check apakah object query tercipta
if($query)
{
    //buat table
    echo '<table class="grey">';

    echo '<thead>';
    echo '<tr>';
	echo '<th>TYPE COA</th>';
    echo '<th>COA</th>';
    echo '<th>DESKRIPSI</th>';
    foreach ($daftar_unit as $kode => $nama) {
    echo '<th>'.$nama.'</th>';
    }
    echo '<th>KONSOLIDASI</th>';
    echo '</tr>';
    echo '</thead>';

    echo '<tbody>';

	konsol_tampil_rekening($query, $daftar_unit, '</td>');

	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL AKTIVA</td>';
	foreach ($daftar_unit as $kode => $nama) {
		echo '<td align="right"><B>'.number_format(compat_num(isset($total_ak[$kode]) ? $total_ak[$kode] : null),0, ',', '.').'</td></B>';
	}
	echo '<td align="right"><B>'.number_format(compat_num($total_ak_tot['tot_saldo_akhir']),0, ',', '.').'</td></B>';
	echo '</tr>';
	konsol_tampil_pemisah($daftar_unit);

	konsol_tampil_rekening($query_pa, $daftar_unit, '</td></B>');

	echo '<tr class="grey">
		<td colspan="3"><b>TOTAL PASIVA</td>';
	foreach ($daftar_unit as $kode => $nama) {
		$pa = isset($total_pa[$kode]) ? $total_pa[$kode] : null;
		$pax = isset($total_pax[$kode]) ? $total_pax[$kode] : null;
		echo '<td align="right"><B>'.number_format(compat_num($pa + $pax),0, ',', '.').'</td></B>';
	}
	echo '<td align="right"><B>'.number_format(compat_num($total_pa_tot['tot_saldo_akhir'] + $total_pax_tot['tot_saldo_akhir']),0, ',', '.').'</td></B>';
	echo '</tr>';
	konsol_tampil_pemisah($daftar_unit);
	echo '</tr>'; // sama dengan versi lama (</tr> ganda)

	konsol_tampil_rekening($query_laba, $daftar_unit, '</td>');
	konsol_tampil_rekening($query_admin, $daftar_unit, '</td>');
	konsol_tampil_pemisah($daftar_unit);

	// SHU per unit: operasional (40000 - 50000), non operasional (57000 - 58000), sebelum pajak
	$shu_p = array();
	$shu_n = array();
	$shu_sb = array();
	// versi lama menghitung SHU unit di dalam while baris 40000 / 57000: bila baris itu tidak ada, nilainya kosong (0)
	$totalp = $total_pendapan !== null ? 0 : null;
	$totaln = $total_pendapan_n !== null ? 0 : null;
	$shutot_sb = 0;
	foreach ($daftar_unit as $kode => $nama) {
		$shu_p[$kode] = $total_pendapan !== null ? konsol_nilai($total_pendapan, $kode) - konsol_nilai($total_biaya, $kode) : null;
		$shu_n[$kode] = $total_pendapan_n !== null ? konsol_nilai($total_pendapan_n, $kode) - konsol_nilai($total_beban, $kode) : null;
		$shu_sb[$kode] = $shu_n[$kode] + $shu_p[$kode];
		if ($totalp !== null) {
			$totalp += $shu_p[$kode];
		}
		if ($totaln !== null) {
			$totaln += $shu_n[$kode];
		}
		$shutot_sb += $shu_sb[$kode];
	}

	echo '<tr class="grey">
		<td colspan="3"><b>SHU OPERASIONAL</td>';
	foreach ($daftar_unit as $kode => $nama) {
		echo '<td align="right"><B>'.number_format(compat_num($shu_p[$kode]),0, ',', '.').'</td></B>';
	}
	echo '<td align="right"><B>'.number_format(compat_num($totalp),0, ',', '.').'</td>';
	echo '</tr>';

	echo '<tr class="grey">

		<td colspan="3"><b>SHU NON OPERASIONAL</td>';
	foreach ($daftar_unit as $kode => $nama) {
		echo '<td align="right"><B>'.number_format(compat_num($shu_n[$kode]),0, ',', '.').'</td></B>';
	}
	echo '<td align="right"><B>'.number_format(compat_num($totaln),0, ',', '.').'</td></B>';
	echo '</tr>';

echo '<tr class="grey">

		<td colspan="3"><b>SHU TAHUN BERJALAN SEBELUM PAJAK</td>';
	foreach ($daftar_unit as $kode => $nama) {
		echo '<td align="right"><B>'.number_format(compat_num($shu_sb[$kode]),0, ',', '.').'</td></B>';
	}
	echo '<td align="right"><B>'.number_format(compat_num($shutot_sb),0, ',', '.').'</td></B>';
	echo '</tr>';
//Estimasi Perhitungan Pajak
//sumbangan dan Jamuan
//SHU_SEBELUMPAJAK
$sbg_jamu=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from ".$sumber." where kode_rekening in('56211','56212','56311','56312') and tanggal_awal='$tgl_cari'"));

$smb_jamu=$sbg_jamu['saldo_akhir'];

$pendapatan=mysql_fetch_array(mysql_query("select sum(saldo_akhir) as saldo_akhir from ".$sumber." where kode_rekening in('40000') and tanggal_awal='$tgl_cari'"));
$pendapatan_p=$pendapatan['saldo_akhir'];

//SHU sebelum pajak tahun berjalan fiskal
$shu_sbp=$shutot_sb+$smb_jamu;
//Pendapatan
$totalp=$pendapatan_p;

//parameter pajak
$param_pajak=4800000000;
//persentase
// compat_bagi 2026-11-04: pendapatan 0 (tidak ada data periode/jenis itu) dulu DivisionByZeroError di PHP 8; kini 0 seperti PHP 5
$persen =compat_bagi($param_pajak,$totalp);

//taksiran pajak mendapat psilitas
$taksiran_dapat=($shu_sbp * $persen);

//Taksiran tidak Mendapatan Pasilitas
$taksiran_tidak=$shu_sbp - $taksiran_dapat;
//Taksitan Pajaka
//25% X 50% X shu sebelum pajak
$tks_pajak=0.125* $taksiran_dapat;
//25 * b`
$tsk_b=(25/100) * $taksiran_tidak;

$estimasi_taksiran=$tks_pajak + $tsk_b;
if($estimasi_taksiran < 1){
$estimasi_taksiran=0;
}else{
$estimasi_taksiran=$estimasi_taksiran;
}


$shu_bersih=$shutot_sb-$estimasi_taksiran;

	// estimasi pajak hanya di kolom Kantor Pusat (0000), unit lain 0 (versi lama: variabel kosong)
	 echo '<tr class="grey">
		<td colspan="3"><b>ESTIMASI TAKSIRAN PAJAK PENGHASILAN</td>';
	foreach ($daftar_unit as $kode => $nama) {
		echo '<td><b>' . number_format(compat_num($kode === '0000' ? $estimasi_taksiran : null), 0, ',', '.') . '</td></B>';
	}
	echo '<td><b>' . number_format(compat_num($estimasi_taksiran), 0, ',', '.') . '</td></B>

	 </tr>';
echo '<tr class="grey">
		<td colspan="3"><b>SHU TAHUN BERJALAN SETELAH PAJAK</td>';
	foreach ($daftar_unit as $kode => $nama) {
		echo '<td><b>' . number_format(compat_num($kode === '0000' ? $shu_sb[$kode] - $estimasi_taksiran : $shu_sb[$kode]), 0, ',', '.') . '</td></B>';
	}
	// "<<td" sama dengan versi lama (tampilan tidak diubah)
	echo '<<td align="right"><b>' . number_format(compat_num($shu_bersih), 0, ',', '.') . '</td></B>
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
