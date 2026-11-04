<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Report > View Baghas & Eq Rate: pilih periode tanggal jatuh tempo jadwal bagi hasil, tampil ringkasan per
 * unit (NOA deposito, total pokok) dan Export .xlsx bertahap laporan Bagi Hasil dan Eq Rate (eqrate_export.php,
 * periode di sesi eqrate_filter). Hitungan di inc/eqrate_deposito.php.
 * Dimigrasi 2026-11-04 (perintah user): dulu cek level tidak ada, mysql_* dengan tanggal langsung di SQL, ringkasan
 * tanpa group by (satu baris, unit acak) dan join jadwal per CIF (NOA = jumlah jadwal x deposito, nominal terhitung
 * berulang, kolom Unit berisi NOA), tampilan awal menjalankan query mutasi kas sisa salinan; Detail (baghas_eqrate.php,
 * tabel HTML bernama neraca.xls, hari ditulis mati 31, baris TOTAL kosong) diganti eqrate_export.php.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "fund") {
    header("location:../../index.html");
    exit;
}
include_once __DIR__ . '/../../function.php';
require_once __DIR__ . '/../../inc/eqrate_deposito.php';

/** Tanggal Y-m-d yang sah, atau ''. */
function tanggal_sah($v)
{
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d && $d->format('Y-m-d') === $v ? $v : '';
}

$awal = isset($_GET['awal']) ? tanggal_sah(trim((string) $_GET['awal'])) : '';
$akhir = isset($_GET['akhir']) ? tanggal_sah(trim((string) $_GET['akhir'])) : '';
$dicari = isset($_GET['cari']);
$pesan = '';
$data = null;
if ($dicari) {
    if ($awal === '' || $akhir === '') {
        $pesan = 'Maaf, isi Tanggal Awal dan Tanggal Akhir pencarian.';
    } elseif ($awal > $akhir) {
        $pesan = 'Tanggal Awal tidak boleh sesudah Tanggal Akhir.';
    } else {
        $_SESSION['eqrate_filter'] = array('awal' => $awal, 'akhir' => $akhir);
        $data = eqrate_ringkasan($awal, $akhir);
    }
}
list($hari, $dari_hari_dep) = bhd_hari();

halaman_awal('Report Baghas dan Eq Rate');
?>
<div class="card card-ni mw-100">
  <div class="card-header">Report Baghas dan Eq Rate</div>
  <div class="card-body">
    <form method="get" action="eq_rate.php" class="row g-2 align-items-end mb-3">
      <div class="col-sm-3">
        <label class="form-label" for="awal">Tanggal Awal</label>
        <input type="date" name="awal" id="awal" value="<?php echo h($awal); ?>" class="form-control" required>
      </div>
      <div class="col-sm-3">
        <label class="form-label" for="akhir">Tanggal Akhir</label>
        <input type="date" name="akhir" id="akhir" value="<?php echo h($akhir); ?>" class="form-control" required>
      </div>
      <div class="col-sm-6 d-flex gap-2">
        <button type="submit" name="cari" value="1" class="btn btn-ni">Search</button>
        <?php if ($data): ?>
        <button type="button" class="btn btn-outline-success ms-auto" data-export-xlsx data-export-url="eqrate_export.php" data-export-satuan="deposito">Export Excel (.xlsx)</button>
        <?php endif; ?>
      </div>
    </form>
    <?php if ($dari_hari_dep): ?>
    <p class="small text-muted">Jumlah hari bagi hasil: <?php echo (int) $hari; ?> hari (Seting Parameter &gt; Jumlah Hari Bagi Hasil).</p>
    <?php else: ?>
    <div class="alert alert-warning py-2">Jumlah hari bagi hasil belum diisi; dipakai <?php echo (int) $hari; ?> hari. Isi di Seting Parameter &gt; Jumlah Hari Bagi Hasil.</div>
    <?php endif; ?>
    <?php if ($pesan !== ''): ?>
    <div class="alert alert-warning"><?php echo h($pesan); ?></div>
    <?php endif; ?>
    <?php if ($data !== null): ?>
    <table class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>Unit</th>
          <th>Tgl Awal Transaksi</th>
          <th>Tgl Akhir Transaksi</th>
          <th class="text-end">Total Noa</th>
          <th class="text-end">Total Pokok</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$data): ?>
        <tr><td colspan="5" class="text-center text-muted">Tidak ada jadwal bagi hasil dalam periode ini</td></tr>
        <?php endif; ?>
        <?php foreach ($data as $r): ?>
        <tr>
          <td><?php echo h($r['unit']); ?></td>
          <td><?php echo h(tgl_indo($awal)); ?></td>
          <td><?php echo h(tgl_indo($akhir)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['noa'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['nominal'])); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="modal-export" tabindex="-1" aria-labelledby="judul-export" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="judul-export">Export Laporan Bagi Hasil dan Eq Rate</h2>
      </div>
      <div class="modal-body">
        <div class="progress" role="progressbar" aria-label="Progres export" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="height: 1.25rem;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
        </div>
        <div class="small text-muted mt-2" data-export-teks aria-live="polite">Menyiapkan data…</div>
        <div data-export-ringkasan></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm d-none" data-bs-dismiss="modal" data-export-tutup>Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php
halaman_akhir(array('assets/app/export_xlsx.js'));
