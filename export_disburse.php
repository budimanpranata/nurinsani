<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Excel daftar pencairan per rekening, dibuka dari Report Disbursement (disburse.php).
 * Dimigrasi 2026-11-03 (prepared statement). Perubahan atas perintah user:
 *   - boleh untuk level kp/ho/lead (menu Disbursement ada di sana) selain unit
 *   - rentang tanggal dari link (?buss_date1=&buss_date2=) dan disaring buss_date & unit = userid, sama dengan layar
 *     (versi lama membaca session yang tanggal awalnya tidak pernah terisi dan menyaring Contract_Date)
 */
require_once __DIR__ . '/inc/tampilan.php';
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
$userid = $_SESSION['userid'];
if (!in_array($_SESSION['level'], array('unit', 'kp', 'ho', 'lead'))) {
    header("location:index.html");
    exit;
}
include "indo_tgl.php";
$buss_date1 = isset($_GET['buss_date1']) ? (string) $_GET['buss_date1'] : '';
$buss_date2 = isset($_GET['buss_date2']) ? (string) $_GET['buss_date2'] : '';

$baris = db_semua("select Contract_Date,Plafond, tenor,loan_detail.cif,Cust_Short_name,nama_ao,no_anggota,loan_detail.cao,angsuran,suffic,loan_detail.unit,nama_kel from loan_detail
inner join kelompok on kelompok.code_kel = loan_detail.code_kel
inner join ao on ao.cao = loan_detail.cao
where buss_date BETWEEN ? AND ? and unit=?", 'sss', $buss_date1, $buss_date2, (string) $userid);

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=NERACA.xls");
?>
<table width="100%">
  <tr bgcolor="#2F6B4F">
  <td height="30" colspan="12" nowrap><div align="left"><strong>Rekap Approval </strong></div></td>
  </tr>
</table>
<table>
  <tr>
    <th>No</th>
    <th>Unit</th>
    <th>Tanggal Murabahah</th>
    <th>Norek</th>
    <th>Nama Anggota</th>
    <th>Kelompok</th>
    <th>CIF</th>
    <th>Flafond</th>
    <th>Tenor</th>
    <th>Nama AO</th>
  </tr>
<?php $no = 0; foreach ($baris as $r): $no++; ?>
  <tr>
    <td><?php echo $no; ?></td>
    <td><?php echo h($r['unit']); ?></td>
    <td><?php echo h(tgl_indo($r['Contract_Date'])); ?></td>
    <td><?php echo h($r['no_anggota']); ?></td>
    <td><?php echo h($r['Cust_Short_name']); ?></td>
    <td><?php echo h($r['nama_kel']); ?></td>
    <td><?php echo h($r['cif']); ?></td>
    <td><?php echo number_format(compat_num($r['Plafond']), 0, ",", "."); ?></td>
    <td><?php echo h($r['tenor']); ?></td>
    <td><?php echo h($r['nama_ao']); ?></td>
  </tr>
<?php endforeach; ?>
</table>
