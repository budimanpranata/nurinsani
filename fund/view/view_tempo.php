<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Data Jatuh Tempo (Posting & Report): deposito yang jatuh tempo pada bulan terpilih (bawaan bulan ini),
 * atau hasil pencarian tanggal jatuh tempo (kalender) dan/atau nama deposan (semua bulan), dengan tombol
 * Realisasi (r_dep.php) dan Export .xlsx bertahap (tempo_export.php, assets/app/export_xlsx.js).
 * Filter disimpan di sesi (tempo_filter) untuk export.
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, DataTables lokal, prepared statement.
 * Perbaikan atas perintah user 2026-11-04:
 *   - wajib login level fund (dulu tanpa cek login)
 *   - daftar bawaan: dulu bulan = substr('Y-m-d', 2, 2) = 2 digit TAHUN ('26') sehingga MONTH(maturity_date)='26' tidak
 *     pernah cocok dan daftar selalu kosong; kini bulan + tahun terpilih
 *   - export tidak lagi mengirim teks SQL ke export.php (siapa pun bisa menjalankan SQL apa pun)
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

require_once __DIR__ . '/../../inc/jatuh_tempo_deposito.php';

$tanggal = isset($_GET['tanggal']) ? substr(trim((string) $_GET['tanggal']), 0, 10) : '';
$dt = DateTime::createFromFormat('!Y-m-d', $tanggal);
if (!$dt || $dt->format('Y-m-d') !== $tanggal) {
    $tanggal = '';
}
$nama = isset($_GET['nama']) ? trim((string) $_GET['nama']) : '';
$bulan = isset($_GET['bulan']) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $_GET['bulan']) ? (string) $_GET['bulan'] : date('Y-m');
$filter = array('tanggal' => $tanggal, 'nama' => $nama, 'bulan' => $bulan);
$cari = tempo_keterangan_cari($filter);
$_SESSION['tempo_filter'] = $filter;
$data = tempo_daftar($filter);

halaman_awal('Data Jatuh Tempo Nur Insani', array('assets/app/datatables-ni.css'));
?>
<div class="card card-ni mw-100">
  <div class="card-header">Jatuh Tempo Nur Insani</div>
  <div class="card-body">
    <form method="get" action="view_tempo.php" class="row g-2 align-items-end mb-3">
      <div class="col-sm-3">
        <label class="form-label" for="bulan">Bulan Jatuh Tempo</label>
        <input type="month" name="bulan" id="bulan" value="<?php echo h($bulan); ?>" class="form-control">
      </div>
      <div class="col-sm-2">
        <label class="form-label" for="tanggal">Cari Tanggal Jatuh Tempo</label>
        <input type="date" name="tanggal" id="tanggal" value="<?php echo h($tanggal); ?>" class="form-control">
      </div>
      <div class="col-sm-3">
        <label class="form-label" for="nama">Cari Nama Deposan</label>
        <input type="text" name="nama" id="nama" value="<?php echo h($nama); ?>" class="form-control" placeholder="kosongkan untuk per bulan">
      </div>
      <div class="col-sm-4 d-flex gap-2">
        <button type="submit" class="btn btn-ni">Tampilkan</button>
        <?php if ($cari !== ''): ?>
        <a class="btn btn-outline-secondary" href="view_tempo.php?bulan=<?php echo h(urlencode($bulan)); ?>">Reset Pencarian</a>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-success ms-auto" data-export-xlsx data-export-url="tempo_export.php" data-export-satuan="deposito">Export Excel (.xlsx)</button>
      </div>
    </form>
    <div class="small text-muted mb-2">
      <?php echo $cari !== '' ? 'Hasil pencarian ' . h($cari) . ' (semua bulan)' : 'Jatuh tempo bulan ' . h(getBulan(substr($bulan, 5, 2)) . ' ' . substr($bulan, 0, 4)); ?>
      (<?php echo count($data); ?> deposito)
    </div>
    <table id="tabel_tempo" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>#</th>
          <th>No Rekening</th>
          <th>Bilyet</th>
          <th>Cif</th>
          <th>Nama</th>
          <th class="text-end">Nominal</th>
          <th>Tenor</th>
          <th>Jatuh Tempo</th>
          <th>Status</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $i => $row): ?>
        <tr>
          <td><?php echo $i + 1; ?></td>
          <td><?php echo h($row['norek']); ?></td>
          <td><?php echo h($row['bilyet']); ?></td>
          <td><?php echo h($row['cif']); ?></td>
          <td><?php echo h($row['CUST_SHORT_NAME']); ?></td>
          <td class="text-end" data-order="<?php echo h($row['nominal']); ?>"><?php echo number_format(compat_num($row['nominal'])); ?></td>
          <td><?php echo h($row['tenor']); ?></td>
          <td data-order="<?php echo h($row['maturity_date']); ?>"><?php echo h(tgl_indo($row['maturity_date'])); ?></td>
          <td><?php echo h($row['aro']); ?></td>
          <td class="text-center"><a href="r_dep.php?kd=<?php echo h(urlencode($row['norek'])); ?>" class="btn btn-sm btn-warning">Realisasi</a></td>
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
        <h2 class="modal-title fs-6" id="judul-export">Export Data Jatuh Tempo</h2>
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
    'assets/app/fund_tempo.js',
));
