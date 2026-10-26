<?php require_once __DIR__ . '/../../koneksi_db.php';
session_start();
ob_start();

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=neraca_konsolidasi.xls");

include "../../indo_tgl.php";
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

$koneksi = open_connection2();



/* ==========================
   PARAMETER
========================== */
$tgl_cari = isset($_SESSION['tgl_cari_konsols']) ? $_SESSION['tgl_cari_konsols'] : date('Y-m-d');
$jenis    = isset($_SESSION['jenis']) ? $_SESSION['jenis'] : 'eom';
$tgl      = tgl_indo($tgl_cari);

/* ==========================
   SUMBER DATA
========================== */
if ($jenis == 'eom') {
    $sumber = "tabel_master_konsol";
} elseif ($jenis == 'current') {
    $sumber = "tabel_master_hari_ah";
} else {
    $sumber = "tabel_master_eoy";
}

/* ==========================
   UNIT DINAMIS (KODE => NAMA)
========================== */
$units = array();

$qUnit = mysql_query("
    SELECT kode_branch, unit
    FROM branch
    WHERE code_area = '1101'
    ORDER BY kode_branch
") or die(mysql_error());

while ($u = mysql_fetch_assoc($qUnit)) {
    $units[$u['kode_branch']] = $u['unit'];
}

/* ==========================
   SELECT DINAMIS PER UNIT
========================== */
$selectUnit = '';
foreach ($units as $kode => $nama) {
    $selectUnit .= "SUM(IF(unit='$kode', saldo_akhir, 0)) AS `$kode`,";
}


/* ==========================
   FUNCTION QUERY NERACA
========================== */
function queryNeraca($gr, $sumber, $tgl_cari, $selectUnit) {
    return "
        SELECT
            kode_rekening,
            nama_rekening,
            LINE_BALANCE,
            $selectUnit
            SUM(saldo_akhir) AS total_trx
        FROM $sumber
        WHERE gr_neraca='$gr'
        AND tanggal_awal='$tgl_cari'
        GROUP BY kode_rekening
        ORDER BY kode_rekening
    ";
}

/* ==========================
   QUERY DATA
========================== */
$qAktiva = mysql_query(queryNeraca('AKTIVA', $sumber, $tgl_cari, $selectUnit)) or die(mysql_error());
$qPasiva = mysql_query(queryNeraca('PASIVA', $sumber, $tgl_cari, $selectUnit)) or die(mysql_error());
$qLaba   = mysql_query(queryNeraca('rugi-laba', $sumber, $tgl_cari, $selectUnit)) or die(mysql_error());
$qAdmin  = mysql_query(queryNeraca('admin', $sumber, $tgl_cari, $selectUnit)) or die(mysql_error());

/* ==========================
   TOTAL PER UNIT
========================== */
function totalPerUnit($gr, $sumber, $tgl_cari) {
    $data = array();
    $q = mysql_query("
        SELECT unit, SUM(saldo_akhir) total
        FROM $sumber
        WHERE gr_neraca='$gr'
        AND tanggal_awal='$tgl_cari'
        GROUP BY unit
    ");
    while ($r = mysql_fetch_assoc($q)) {
        $data[$r['unit']] = $r['total'];
    }
    return $data;
}

/* ==========================
   PASIVA KHUSUS (EKUITAS)
========================== */
$pasiva_khusus = array();

$qPaKhusus = mysql_query("
    SELECT unit, SUM(saldo_akhir) total
    FROM $sumber
    WHERE kode_rekening IN ('3901000','3902000','2500000')
    AND tanggal_awal='$tgl_cari'
    GROUP BY unit
") or die(mysql_error());

while ($r = mysql_fetch_assoc($qPaKhusus)) {
    $pasiva_khusus[$r['unit']] = $r['total'];
}


$totalAktiva = totalPerUnit('AKTIVA', $sumber, $tgl_cari);
$totalPasiva = totalPerUnit('PASIVA', $sumber, $tgl_cari);

/* ==========================
   SHU SEBELUM PAJAK PER UNIT
========================== */
$shu_per_unit = array();
$shutot_sb = 0;

$qShu = mysql_query("
    SELECT unit, SUM(saldo_akhir) total
    FROM $sumber
    WHERE gr_neraca='rugi-laba'
    AND tanggal_awal='$tgl_cari'
    GROUP BY unit
") or die(mysql_error());

while ($r = mysql_fetch_assoc($qShu)) {
    $shu_per_unit[$r['unit']] = $r['total'];
    $shutot_sb += $r['total'];
}

/* ==========================
   PAJAK
========================== */
$sbg_jamu = mysql_fetch_assoc(mysql_query("
    SELECT SUM(saldo_akhir) saldo_akhir
    FROM $sumber
    WHERE kode_rekening IN ('56211','56212','56311','56312')
    AND tanggal_awal='$tgl_cari'
"));

$pendapatan = mysql_fetch_assoc(mysql_query("
    SELECT SUM(saldo_akhir) saldo_akhir
    FROM $sumber
    WHERE kode_rekening='40000'
    AND tanggal_awal='$tgl_cari'
"));

$smb_jamu = $sbg_jamu['saldo_akhir'];
$totalp   = $pendapatan['saldo_akhir'];

$shu_sbp = $shutot_sb + $smb_jamu;

$param_pajak = 4800000000;
$persen = ($totalp > 0) ? ($param_pajak / $totalp) : 0;

$taksiran_dapat = $shu_sbp * $persen;
$taksiran_tidak = $shu_sbp - $taksiran_dapat;

$tks_pajak = 0.125 * $taksiran_dapat;
$tsk_b = 0.25 * $taksiran_tidak;

$estimasi_taksiran = $tks_pajak + $tsk_b;
if ($estimasi_taksiran < 1) $estimasi_taksiran = 0;

$shu_bersih = $shutot_sb - $estimasi_taksiran;

/* ==========================
   HTML OUTPUT
========================== */
echo "<html><head>
<style>
body{
    font-family: Tahoma, Arial;
    font-size: 20px;
}

table{
    border-collapse: collapse;
}

th, td{
    border: 1px solid #000;
    padding: 8px;
    font-size: 18px;
}

th{
    background: #eee;
    font-weight: bold;
    text-align: center;
    font-size: 20px;
}

.grey{
    background: #dcdcdc;
    font-weight: bold;
    font-size: 18px;
}

.right{
    text-align: right;
}

.heading{
    background: #e0e0e0;
     font-weight: normal;
    text-shadow: 0.4px 0 0 #000;
   
}

.detail{
    background: #ffffff;
}

</style>

</head><body>
<B>KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH</b><br/>
<b>LAPORAN NERACA KONSOLIDASI</b><br>
<b>$tgl</b><br><br>

<table>
<tr>
<th>TYPE</th>
<th>COA</th>
<th>DESKRIPSI</th>";

foreach ($units as $kode => $nama) {
    echo "<th>$nama</th>";
}
echo "<th>KONSOLIDASI</th></tr>";
function renderRows($query, $units) {
    while ($r = mysql_fetch_assoc($query)) {

        // tentukan class berdasarkan jenis account
        $rowClass = ($r['LINE_BALANCE'] == 'HEADING') ? 'heading' : 'detail';

        echo "<tr class='$rowClass'>";
        echo "<td>{$r['LINE_BALANCE']}</td>";
        echo "<td>{$r['kode_rekening']}</td>";
        echo "<td>{$r['nama_rekening']}</td>";

        foreach ($units as $kode => $nama) {
            echo "<td class='right'>" . number_format(compat_num($r[$kode]),0,',','.') . "</td>";
        }

        echo "<td class='right'>" . number_format(compat_num($r['total_trx']),0,',','.') . "</td>";
        echo "</tr>";
    }
}


/* AKTIVA */
renderRows($qAktiva, $units);

/* TOTAL AKTIVA */
echo "<tr class='grey'><td colspan='3'>TOTAL AKTIVA</td>";
$g=0;
foreach ($units as $k=>$n){$v=isset($totalAktiva[$k])?$totalAktiva[$k]:0;echo "<td class='right'>".number_format(compat_num($v),0,',','.')."</td>";$g+=$v;}
echo "<td class='right'>".number_format(compat_num($g),0,',','.')."</td></tr>";

/* PASIVA */
renderRows($qPasiva, $units);

/* TOTAL PASIVA */
echo "<tr class='grey'><td colspan='3'>TOTAL PASIVA</td>";
$g=0;
foreach ($units as $k=>$n){
    $pasiva_normal  = isset($totalPasiva[$k]) ? $totalPasiva[$k] : 0;
    $pasiva_kh      = isset($pasiva_khusus[$k]) ? $pasiva_khusus[$k] : 0;

    $total_unit = $pasiva_normal + $pasiva_kh;

    echo "<td class='right'>".number_format(compat_num($total_unit),0,',','.')."</td>";
    $g += $total_unit;
}

echo "<td class='right'>".number_format(compat_num($g),0,',','.')."</td></tr>";

/* LABA + ADMIN */
renderRows($qLaba, $units);
renderRows($qAdmin, $units);

/* SHU SEBELUM PAJAK */
echo "<tr class='grey'><td colspan='3'>SHU TAHUN BERJALAN SEBELUM PAJAK</td>";
foreach ($units as $k=>$n){$v=isset($shu_per_unit[$k])?$shu_per_unit[$k]:0;echo "<td class='right'>".number_format(compat_num($v),0,',','.')."</td>";}
echo "<td class='right'>".number_format(compat_num($shutot_sb),0,',','.')."</td></tr>";

/* ESTIMASI PAJAK */
echo "<tr class='grey'><td colspan='3'>ESTIMASI TAKSIRAN PAJAK</td>";
foreach ($units as $k=>$n){
    $shu=$shu_per_unit[$k];
    $pjk=($shutot_sb>0)?($shu/$shutot_sb)*$estimasi_taksiran:0;
    echo "<td class='right'>".number_format(compat_num($pjk),0,',','.')."</td>";
}
echo "<td class='right'>".number_format(compat_num($estimasi_taksiran),0,',','.')."</td></tr>";

/* SHU SETELAH PAJAK */
echo "<tr class='grey'><td colspan='3'>SHU TAHUN BERJALAN SETELAH PAJAK</td>";
foreach ($units as $k=>$n){
    $shu=$shu_per_unit[$k];
    $pjk=($shutot_sb>0)?($shu/$shutot_sb)*$estimasi_taksiran:0;
    echo "<td class='right'>".number_format(compat_num($shu-$pjk),0,',','.')."</td>";
}
echo "<td class='right'>".number_format(compat_num($shu_bersih),0,',','.')."</td></tr>";

echo "</table></body></html>";
?>

