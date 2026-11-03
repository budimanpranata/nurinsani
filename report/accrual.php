<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Proses Accrual dari kp > Report > Nominative Pembiayaan (Kr_kp.php): posting jurnal "Accrual PM Murabahah"
 * sebesar sum(loan_accrual.accrual) gol 1 ke tabel_transaksi (debet 1010-1913000, kredit 1010-41002), kode
 * BU/<unit><7 karakter acak>, tanggal buss_dates sesi; lalu mengosongkan tabel accrual dan menampilkan Excel ringkas.
 * loan_accrual diisi oleh Rekap (export_nomi_kp.php).
 * Dimigrasi 2026-11-03 (prepared statement, pengaman login); proses sama dengan versi lama. Query nomor_jurnal versi
 * lama yang hasilnya tidak dipakai dibuang.
 */
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
include "../function.php";
$userid = $_SESSION['userid'];
$buss_date = isset($_SESSION['buss_dates']) ? (string) $_SESSION['buss_dates'] : '';

$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit = $id_user['kode_branch'];
}

$accrual = null;
foreach (db_semua("select sum(accrual) as accrual from loan_accrual where gol='1'") as $data_acc) {
    $accrual = $data_acc['accrual'];
}
$kodet = acakangkahuruf(7);
$kode_tran = "BU/" . $unit . $kodet;
$ket = "Accrual PM Murabahah";

db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
    values(?,?,'1010-1913000',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, $kode_tran, $buss_date, $ket, (string) $accrual, (string) $userid);
db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
    values(?,?,'1010-41002',?,'Bukti SYSTEM',?,?,?)", 'ssssss', (string) $unit, $kode_tran, $buss_date, $ket, (string) $accrual, (string) $userid);
db_jalankan("TRUNCATE TABLE accrual");

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=accrual.xls");

$e = function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};
?>
<html>
<head>
<title>NeracaPercobaan</title>
<style>
  body {font-family:tahoma, arial}
  table {border-collapse: collapse}
  th, td {font-size: 17px; border: 1px solid #DEDEDE; padding: 5px 7px; color: #303030}
  th {background: #EAEAEA; font-size: 17px; border-color:#B0B0B0}
</style>
</head>
<body>
<b>KOPERASI SIMPAN PINJAM PEMBIAYAAN SYARIAH</b><br/>
<b>LAPORAN JURNAL ACRUAL</b><br/>
<b>UNIT KANTOR PUSAT</b><br/>
<b>Periode - <?php echo $e($buss_date); ?></b><br/>
<br/>
<table width="100%" border="1">
<tr>
<th>buss_date</th>
<th>Debet</th>
<th>kredit</th>
</tr>
<tr>
<td align="center"><?php echo $e($buss_date); ?></td>
<td align="center"><?php echo $e($accrual); ?></td>
<td align="center"><?php echo $e($accrual); ?></td>
</tr>
</table>
</body>
</html>
