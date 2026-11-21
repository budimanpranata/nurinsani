<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu New Report > Rugi Laba: rekening rugi-laba unit dari tabel_rugi_laba beserta SHU operasional, non operasional,
 * sebelum dan sesudah pajak. Link Excel ke laba_excel.php, link PDF ke laba_pdf.php.
 * Logika dan perhitungan sama dengan versi lama (keputusan user: hanya migrasi kode), termasuk:
 *   - estimasi pajak tidak pernah diisi (0), sehingga SHU setelah pajak = SHU sebelum pajak
 * Perbaikan 2026-11-21 (perintah user, PEKERJAAN_TERTUNDA §32 A2): saldo akhir negatif dan SHU non operasional rugi
 * tampil dengan tanda minus (merah); dulu nilai mutlak sehingga tanda rugi hilang. Tombol PDF ke laba_pdf.php (PDF Rugi
 * Laba, isi sama dengan layar); dulu ke neraca_pdf.php (PDF Neraca).
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "unit") {
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
    where unit =? and posisi='rugi-laba' and kode_rekening not in ('59999')
    GROUP BY gr_neraca,kode_rekening ORDER BY gr_neraca,kode_rekening ", 's', $unit);

/** Saldo satu rekening ringkasan rugi laba unit (null bila tidak ada). */
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
$shu_sb_pajak_akhir = $shu_ops_akhir + $shu_ops_non_akhir;
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
  <?php // 2026-11-21 (perintah user, §32 A2): PDF Rugi Laba (dulu mengarah ke neraca_pdf.php = PDF Neraca) ?>
  <a href="laba_pdf.php" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">PDF</a>
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
        <?php // 2026-11-21 (§32 A2): saldo negatif tampil dengan tanda minus (dulu nilai mutlak, tanda hilang) ?>
        <td class="text-end<?php echo compat_num($row['saldo_akhir']) < 0 ? ' text-danger' : ''; ?>"><?php echo angka_rl($row['saldo_akhir']); ?></td>
      </tr>
    <?php endforeach; ?>
      <tr class="fw-bold">
        <td colspan="3">SHU OPERASIONAL</td>
        <td class="text-end"><?php echo angka_rl($shu_ops_akhir); ?></td>
      </tr>
      <tr class="fw-bold">
        <td colspan="3">SHU NON OPERASIONAL</td>
        <td class="text-end<?php echo $shu_ops_non_akhir < 0 ? ' text-danger' : ''; ?>"><?php echo angka_rl($shu_ops_non_akhir); ?></td>
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
