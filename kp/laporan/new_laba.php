<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Report > Akuntasi > Neraca > Rugi-Laba: rekening rugi-laba kp dari tabel_rugi_laba beserta SHU operasional,
 * non operasional, sebelum dan sesudah pajak. Link Excel ke laba_excel.php, link PDF ke neraca_pdf.php (sama dengan
 * versi lama; neraca_pdf.php tidak ada di kp/laporan).
 * Dimigrasi 2026-11-03: hanya tampilan (Bootstrap 5) dan query (prepared statement); logika sama dengan versi lama, termasuk:
 *   - saldo akhir negatif ditampilkan sebagai nilai mutlak; SHU non operasional ditampilkan sebagai nilai mutlak
 *   - SHU sebelum pajak = SHU operasional + nilai mutlak SHU non operasional (berbeda dengan versi unit)
 *   - estimasi pajak tidak pernah diisi (0), sehingga SHU setelah pajak = SHU sebelum pajak
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "kp") {
    header("location:../index.html");
    exit;
}

$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit = $id_user['kode_branch'];
}
$unit = (string) $unit;

$rekening = db_semua("SELECT kode_rekening,nama_rekening,saldo_awal,saldo_akhir,saldo_jalan,LINE_BALANCE
    FROM `tabel_rugi_laba`
    where unit =? and posisi='rugi-laba' and gr_neraca='rugi-laba' and kode_rekening not in ('59999')
    GROUP BY gr_neraca,kode_rekening ORDER BY gr_neraca,kode_rekening ", 's', $unit);

/** Saldo satu rekening ringkasan rugi laba (null bila tidak ada). */
function saldo_rl($unit, $kode)
{
    return db_satu("select saldo_jalan,saldo_awal,saldo_akhir from tabel_rugi_laba where unit =? and kode_rekening=?", 'ss', $unit, $kode);
}
$total_pendapan = saldo_rl($unit, '40000');
$total_biaya = saldo_rl($unit, '50000');
$total_pendapan_n = saldo_rl($unit, '57000');
$total_beban = saldo_rl($unit, '58000');
$nilai = function ($baris, $kolom) {
    return compat_num($baris ? $baris[$kolom] : null);
};
$shu_ops_akhir = $nilai($total_pendapan, 'saldo_akhir') - $nilai($total_biaya, 'saldo_akhir');
$shu_ops_non_akhir = $nilai($total_pendapan_n, 'saldo_akhir') - $nilai($total_beban, 'saldo_akhir');
$shu_sb_pajak_akhir = $shu_ops_akhir + abs($shu_ops_non_akhir);
// versi lama: estimasi pajak ($estimasi_jalan, $shu_ops_akhir1) tidak pernah diisi -> 0
$estimasi_pajak = 0;
$shu_after_pajak_akhir = $shu_sb_pajak_akhir - $estimasi_pajak;

/** Format angka laporan (titik ribuan, tanpa desimal) seperti versi lama. */
function angka_rl($v)
{
    return number_format(compat_num($v), 0, ',', '.');
}

halaman_awal('Laba Rugi');
?>
<div class="d-flex gap-2 mb-3">
  <a href="laba_excel.php" class="btn btn-outline-success btn-sm">Excel</a>
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
    <?php foreach ($rekening as $row): ?>
      <tr<?php echo $row['LINE_BALANCE'] == 'HEADING' ? ' class="table-secondary fw-semibold"' : ''; ?>>
        <td><?php echo h($row['LINE_BALANCE']); ?></td>
        <td><?php echo h($row['kode_rekening']); ?></td>
        <td><?php echo h($row['nama_rekening']); ?></td>
        <?php // saldo negatif ditampilkan sebagai nilai mutlak (versi lama) ?>
        <td class="text-end"><?php echo angka_rl(abs(compat_num($row['saldo_akhir']))); ?></td>
      </tr>
    <?php endforeach; ?>
      <tr class="fw-bold">
        <td colspan="3">SHU OPERASIONAL</td>
        <td class="text-end"><?php echo angka_rl($shu_ops_akhir); ?></td>
      </tr>
      <tr class="fw-bold">
        <td colspan="3">SHU NON OPERASIONAL</td>
        <td class="text-end"><?php echo angka_rl(abs($shu_ops_non_akhir)); ?></td>
      </tr>
      <tr class="fw-bold">
        <td colspan="3">SHU TAHUN BERJALAN SEBELUM PAJAK</td>
        <td class="text-end"><?php echo angka_rl($shu_sb_pajak_akhir); ?></td>
      </tr>
      <tr class="fw-bold">
        <td colspan="3">ESTIMASI TAKSIRAN PAJAK PENGHASILAN</td>
        <td class="text-end"><?php echo angka_rl($estimasi_pajak); ?></td>
      </tr>
      <tr class="fw-bold">
        <td colspan="3">SHU TAHUN BERJALAN SETELAH PAJAK</td>
        <td class="text-end"><?php echo angka_rl($shu_after_pajak_akhir); ?></td>
      </tr>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
