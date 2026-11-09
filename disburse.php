<?php require_once __DIR__ . '/koneksi_db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit');
/**
 * Report > Nomonative > Disbursement (menu kp, ho, lead): rekap pencairan (loan_detail) per unit pada rentang
 * buss_date, unit = userid (seperti versi lama).
 * Dimigrasi 2026-11-03 (Bootstrap 5 + prepared statement). Perubahan atas perintah user:
 *   - link Download (export_disburse.php) kini terbuka untuk kp/ho/lead dan memakai rentang tanggal & unit yang sama
 *     dengan layar (versi lama menolak selain level unit dan tanggal awalnya selalu kosong)
 */
require_once __DIR__ . '/inc/tampilan.php';
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
$userid = $_SESSION['userid'];

$pesan = null;
$baris = array();
$tgl1 = isset($_POST['buss_date1']) ? trim((string) $_POST['buss_date1']) : '';
$tgl2 = isset($_POST['buss_date2']) ? trim((string) $_POST['buss_date2']) : '';
if (isset($_POST['search'])) {
    if ($tgl1 === '') {
        $pesan = 'Maaf, isi tanggal pencarian yang benar!';
    } else {
        $baris = db_semua("select unit, SUM(Plafond) AS PLA, Count(cif) AS NOA
 FROM loan_detail where buss_date BETWEEN ? AND ? and unit=? Group By unit Order By unit", 'sss', $tgl1, $tgl2, (string) $userid);
    }
}

halaman_awal('Report Disbursement');
?>
<div class="card card-ni">
  <div class="card-header">Pencarian Berdasarkan Tanggal</div>
  <div class="card-body">
    <form method="post" action="disburse.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="buss_date1" class="col-sm-4 col-form-label">Tanggal Awal</label>
        <div class="col-sm-8">
          <input type="date" name="buss_date1" id="buss_date1" class="form-control form-control-sm" value="<?php echo h($tgl1); ?>">
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="buss_date2" class="col-sm-4 col-form-label">Tanggal Akhir</label>
        <div class="col-sm-8">
          <input type="date" name="buss_date2" id="buss_date2" class="form-control form-control-sm" value="<?php echo h($tgl2); ?>">
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="Search" class="btn btn-ni btn-sm px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<?php if ($pesan !== null): ?>
<div class="alert alert-warning mt-3"><?php echo h($pesan); ?></div>
<?php endif; ?>

<div class="card card-ni mw-100 mt-3">
  <div class="card-header">Disbursement</div>
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <thead>
        <tr><th>Unit</th><th class="text-end">Pembiayaan</th><th class="text-end">NoA</th><th class="text-center">Download</th></tr>
      </thead>
      <tbody>
      <?php foreach ($baris as $r): ?>
        <tr>
          <td><?php echo h($r['unit']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['PLA'])); ?></td>
          <td class="text-end"><?php echo h($r['NOA']); ?></td>
          <td class="text-center">
            <a href="export_disburse.php?<?php echo h(http_build_query(array('buss_date1' => $tgl1, 'buss_date2' => $tgl2))); ?>" class="btn btn-outline-success btn-sm">Excel Disburse</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (isset($_POST['search']) && $pesan === null && !$baris): ?>
        <tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
halaman_akhir();
