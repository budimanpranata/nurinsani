<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Report > Akuntasi > Neraca > Neraca: rekening neraca AKTIVA dan PASIVA dari tabel_master beserta total.
 * Link Excel ke neraca_excel.php, link PDF ke neraca_pdf.php (sama dengan versi lama; neraca_pdf.php tidak ada di kp/laporan).
 * Dimigrasi 2026-11-03: hanya tampilan (Bootstrap 5) dan query (prepared statement); logika sama dengan versi lama, termasuk:
 *   - AKTIVA, PASIVA, dan kedua total memakai unit user (kode_branch); perintah user 2026-11-03: daftar PASIVA versi
 *     lama memakai unit '001' sehingga tidak cocok dengan TOTAL PASIVA
 *   - saldo akhir negatif ditampilkan dalam kurung; baris HEADING (dan di PASIVA juga SUB TOTAL) diberi latar
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:../../index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "kp") {
    header("location:../../index.html");
    exit;
}

$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit = $id_user['kode_branch'];
}
$unit = (string) $unit;

$sql_rek = "SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,mut_debet,mut_kredit,LINE_BALANCE
    FROM `tabel_master`
    where unit =? and posisi='Neraca' and gr_neraca=?
    GROUP BY gr_neraca,kode_rekening ";
$sql_total = "select sum(mut_debet) as tot_mut_debet, sum(mut_kredit) as tot_mut_kredit,
    sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from tabel_master where unit =? and gr_neraca=? and LINE_BALANCE='HEADING'";
$aktiva = db_semua($sql_rek, 'ss', $unit, 'AKTIVA');
// versi lama memakai unit '001' untuk daftar rekening PASIVA; kini unit user seperti AKTIVA dan total (perintah user 2026-11-03)
$pasiva = db_semua($sql_rek, 'ss', $unit, 'PASIVA');
$total_ak = db_satu($sql_total, 'ss', $unit, 'AKTIVA');
$total_pa = db_satu($sql_total, 'ss', $unit, 'PASIVA');

/** Saldo laporan: titik ribuan tanpa desimal, negatif dalam kurung (versi lama). */
function saldo_neraca($v)
{
    $v = compat_num($v);
    return $v < 0 ? '(' . number_format(abs($v), 0, ',', '.') . ')' : number_format($v, 0, ',', '.');
}

$bagian = array(
    array('rek' => $aktiva, 'total' => $total_ak, 'judul' => 'TOTAL AKTIVA', 'sub_total' => false),
    array('rek' => $pasiva, 'total' => $total_pa, 'judul' => 'TOTAL PASIVA', 'sub_total' => true),
);

halaman_awal('Neraca');
?>
<div class="d-flex gap-2 mb-3">
  <a href="neraca_excel.php" class="btn btn-outline-success btn-sm">Excel</a>
  <a href="neraca_pdf.php" class="btn btn-outline-secondary btn-sm">PDF</a>
</div>
<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>TYPE COA</th>
        <th>COA</th>
        <th>Deskripsi</th>
        <th class="text-end">Saldo Akhir</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($bagian as $b): ?>
      <?php foreach ($b['rek'] as $row):
          $sub = $row['LINE_BALANCE'] == 'HEADING'
              || ($b['sub_total'] && ($row['kode_rekening'] == 'SUB TOTAL' || $row['nama_rekening'] == 'SUB TOTAL')); ?>
      <tr<?php echo $sub ? ' class="table-secondary fw-semibold"' : ''; ?>>
        <td><?php echo h($row['LINE_BALANCE']); ?></td>
        <td><?php echo h($row['kode_rekening']); ?></td>
        <td><?php echo h($row['nama_rekening']); ?></td>
        <td class="text-end"><?php echo saldo_neraca($row['saldo_akhir']); ?></td>
      </tr>
      <?php endforeach; ?>
      <tr class="fw-bold">
        <td colspan="3"><?php echo $b['judul']; ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b['total'] ? $b['total']['tot_saldo_akhir'] : null), 0, ',', '.'); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
