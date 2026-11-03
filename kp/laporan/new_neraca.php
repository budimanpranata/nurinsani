<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Report > Akuntasi > Neraca > TB (neraca percobaan): rekening AKTIVA, PASIVA, dan rugi-laba dari tabel_master
 * (saldo awal, mutasi debet/kredit, saldo akhir) beserta total dan SHU.
 * Link Excel ke neraca_excel_kp.php, link PDF ke neraca_pdf.php (sama dengan versi lama; neraca_pdf.php tidak ada di kp/laporan).
 * Dimigrasi 2026-11-03: hanya tampilan (Bootstrap 5) dan query (prepared statement); logika sama dengan versi lama, termasuk:
 *   - daftar PASIVA tanpa saring posisi; TOTAL PASIVA = rekening 3901000+3902000 ditambah jumlah HEADING PASIVA
 *   - saldo awal/akhir negatif dalam kurung, mutasi dan total dengan tanda minus; SHU NON OPERASIONAL nilai mutlak
 *   - estimasi pajak tidak pernah diisi (0); SHU SETELAH PAJAK kolom mutasi debet/kredit selalu 0 (variabelnya tidak pernah
 *     diisi di versi lama)
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

$kolom_rek = "SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,mut_debet,mut_kredit,LINE_BALANCE FROM `tabel_master` ";
$aktiva = db_semua($kolom_rek . "where unit =? and posisi='Neraca' and gr_neraca='AKTIVA' GROUP BY gr_neraca,kode_rekening", 's', $unit);
$pasiva = db_semua($kolom_rek . "where unit =? and gr_neraca='PASIVA' GROUP BY gr_neraca,kode_rekening", 's', $unit);
$rugi_laba = db_semua($kolom_rek . "where unit =? and posisi='rugi-laba' and gr_neraca='rugi-laba' GROUP BY gr_neraca,kode_rekening", 's', $unit);

$kolom_total = "select sum(mut_debet) as tot_mut_debet, sum(mut_kredit) as tot_mut_kredit,
    sum(saldo_awal) as tot_saldo_awal, sum(saldo_akhir) as tot_saldo_akhir from tabel_master ";
$total_ak = db_satu($kolom_total . "where unit =? and gr_neraca='AKTIVA' and LINE_BALANCE='HEADING'", 's', $unit);
$total_pa = db_satu($kolom_total . "where unit =? and gr_neraca='PASIVA' and kode_rekening in ('3901000','3902000')", 's', $unit);
$total_shu = db_satu($kolom_total . "where unit =? and gr_neraca='PASIVA' and LINE_BALANCE='HEADING'", 's', $unit);

/** Nilai kolom dari satu baris hasil query (0 bila baris tidak ada). */
function nilai_tb($baris, $kolom)
{
    return compat_num($baris ? $baris[$kolom] : null);
}

/** Saldo satu rekening ringkasan rugi laba (null bila tidak ada). */
function rek_tb($unit, $kode)
{
    return db_satu("select mut_debet,mut_kredit,saldo_awal,saldo_akhir from tabel_master where unit =? and kode_rekening=?", 'ss', $unit, $kode);
}

$kol = array('saldo_awal', 'mut_debet', 'mut_kredit', 'saldo_akhir');
$tot_aktiva = array();
$tot_pasiva = array();
foreach ($kol as $k) {
    $tot_aktiva[] = nilai_tb($total_ak, 'tot_' . $k);
    $tot_pasiva[] = nilai_tb($total_pa, 'tot_' . $k) + nilai_tb($total_shu, 'tot_' . $k);
}

$pendapatan = rek_tb($unit, '40000');
$biaya = rek_tb($unit, '50000');
$pendapatan_n = rek_tb($unit, '57000');
$beban = rek_tb($unit, '58000');
$shu_ops = array();
$shu_non_ops = array();
$shu_sb_pajak = array();
foreach ($kol as $i => $k) {
    $shu_ops[$i] = nilai_tb($pendapatan, $k) - nilai_tb($biaya, $k);
    $shu_non_ops[$i] = nilai_tb($pendapatan_n, $k) - nilai_tb($beban, $k);
    $shu_sb_pajak[$i] = $shu_ops[$i] + $shu_non_ops[$i];
}
// versi lama: estimasi pajak tidak pernah diisi (0); kolom mutasi SHU setelah pajak memakai variabel yang tidak pernah diisi (0)
$estimasi_pajak = array(0, 0, 0, 0);
$shu_after_pajak = array($shu_sb_pajak[0], 0, 0, $shu_sb_pajak[3]);

/** Angka laporan: titik ribuan tanpa desimal (negatif dengan tanda minus). */
function angka_tb($v)
{
    return number_format(compat_num($v), 0, ',', '.');
}

/** Saldo awal/akhir rekening: negatif dalam kurung (versi lama). */
function saldo_tb($v)
{
    $v = compat_num($v);
    return $v < 0 ? '(' . angka_tb(abs($v)) . ')' : angka_tb($v);
}

$bagian = array(
    array('rek' => $aktiva, 'sub_total' => false, 'judul' => 'TOTAL AKTIVA', 'total' => $tot_aktiva),
    array('rek' => $pasiva, 'sub_total' => true, 'judul' => 'TOTAL PASIVA', 'total' => $tot_pasiva),
    array('rek' => $rugi_laba, 'sub_total' => true, 'judul' => null, 'total' => null),
);
$ringkasan = array(
    'SHU OPERASIONAL' => $shu_ops,
    'SHU NON OPERASIONAL' => array_map('abs', $shu_non_ops),
    'SHU TAHUN BERJALAN SEBELUM PAJAK' => $shu_sb_pajak,
    'ESTIMASI TAKSIRAN PAJAK PENGHASILAN' => $estimasi_pajak,
    'SHU TAHUN BERJALAN SETELAH PAJAK' => $shu_after_pajak,
);

halaman_awal('Neraca Percobaan');
?>
<div class="d-flex gap-2 mb-3">
  <a href="neraca_excel_kp.php" class="btn btn-outline-success btn-sm">Excel</a>
  <a href="neraca_pdf.php" class="btn btn-outline-secondary btn-sm">PDF</a>
</div>
<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th rowspan="2">TYPE COA</th>
        <th rowspan="2">COA</th>
        <th rowspan="2">Deskripsi</th>
        <th rowspan="2" class="text-end">Saldo Awal</th>
        <th colspan="2" class="text-center">Mutasi</th>
        <th rowspan="2" class="text-end">Saldo Akhir</th>
      </tr>
      <tr>
        <th class="text-end">Debet</th>
        <th class="text-end">Kredit</th>
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
        <td class="text-end"><?php echo saldo_tb($row['saldo_awal']); ?></td>
        <td class="text-end"><?php echo angka_tb($row['mut_debet']); ?></td>
        <td class="text-end"><?php echo angka_tb($row['mut_kredit']); ?></td>
        <td class="text-end"><?php echo saldo_tb($row['saldo_akhir']); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if ($b['judul'] !== null): ?>
      <tr class="fw-bold">
        <td colspan="3"><?php echo $b['judul']; ?></td>
        <?php foreach ($b['total'] as $v): ?>
        <td class="text-end"><?php echo angka_tb($v); ?></td>
        <?php endforeach; ?>
      </tr>
      <tr><td colspan="7">&nbsp;</td></tr>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php foreach ($ringkasan as $judul => $nilai): ?>
      <tr class="fw-bold">
        <td colspan="3"><?php echo $judul; ?></td>
        <?php foreach ($nilai as $v): ?>
        <td class="text-end"><?php echo angka_tb($v); ?></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
