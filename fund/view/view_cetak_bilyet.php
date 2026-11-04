<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Cetak > Cetak Bilyet: deposito dengan status_print 'OK' dan tombol Print (cetak_bilyet.php, PDF di tab
 * baru; daftar dimuat ulang saat kembali ke tab ini karena deposito yang dicetak berstatus 'Print'), serta Export .xlsx
 * bertahap (tempo_export.php?laporan=bilyet). Filter di sesi bilyet_filter.
 * Dimigrasi 2026-11-04 (perintah user): tampilan Bootstrap 5, DataTables lokal, prepared statement, wajib login level
 * fund (dulu tanpa cek login); pencarian CIF/nama diperbaiki (dulu salah tanda kutip, selalu gagal); export tidak lagi
 * lewat export.php.
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
require_once __DIR__ . '/../../inc/cetak_deposito.php';

$cari = isset($_GET['cari']) ? trim((string) $_GET['cari']) : '';
$filter = array('cari' => $cari);
$_SESSION['bilyet_filter'] = $filter;
$data = bilyet_daftar($filter);

halaman_awal('Cetak Bilyet Deposito', array('assets/app/datatables-ni.css'));
?>
<div class="card card-ni mw-100">
  <div class="card-header">Cetak Bilyet Deposito</div>
  <div class="card-body">
    <form method="get" action="view_cetak_bilyet.php" class="row g-2 align-items-end mb-3">
      <div class="col-sm-4">
        <label class="form-label" for="cari">Cari CIF / Nama Deposan</label>
        <input type="text" name="cari" id="cari" value="<?php echo h($cari); ?>" class="form-control">
      </div>
      <div class="col-sm-8 d-flex gap-2">
        <button type="submit" class="btn btn-ni">Tampilkan</button>
        <?php if ($cari !== ''): ?>
        <a class="btn btn-outline-secondary" href="view_cetak_bilyet.php">Reset Pencarian</a>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-success ms-auto" data-export-xlsx data-export-url="tempo_export.php?laporan=bilyet" data-export-satuan="deposito">Export Excel (.xlsx)</button>
      </div>
    </form>
    <table id="tabel_cetak" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>#</th>
          <th>No Transaksi</th>
          <th>Cif</th>
          <th>Nama</th>
          <th class="text-end">Nominal</th>
          <th>Tenor</th>
          <th>Jatuh Tempo</th>
          <th>Status Aro</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $i => $row): ?>
        <tr>
          <td><?php echo $i + 1; ?></td>
          <td><?php echo h($row['norek']); ?></td>
          <td><?php echo h($row['cif']); ?></td>
          <td><?php echo h($row['CUST_SHORT_NAME']); ?></td>
          <td class="text-end" data-order="<?php echo h($row['nominal']); ?>"><?php echo number_format(compat_num($row['nominal'])); ?></td>
          <td><?php echo h($row['tenor']); ?></td>
          <td data-order="<?php echo h($row['maturity_date']); ?>"><?php echo h(tgl_indo($row['maturity_date'])); ?></td>
          <td><?php echo h($row['aro']); ?></td>
          <td class="text-center"><a href="cetak_bilyet.php?kd=<?php echo h(urlencode($row['norek'])); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success" data-cetak>Print</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modal-export" tabindex="-1" aria-labelledby="judul-export" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="judul-export">Export Data Cetak Bilyet</h2>
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
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/export_xlsx.js',
    'assets/app/fund_cetak.js',
));
