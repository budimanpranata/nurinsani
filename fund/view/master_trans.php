<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Input Data Master > Data Transaksi: daftar deposito (DataTables server-side dari ajax-grid-data_trns.php)
 * dengan Edit nisbah (../transaksi/edit_nisbah.php) dan Break, serta + Data transaksi (../transaksi/input_trns.php).
 * Break: salin baris deposito ke dep_break (proses lanjutan di menu Break), pesan sama dengan versi lama.
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, DataTables lokal, skrip assets/app/fund_deposito.js, prepared statement.
 * Perbaikan atas perintah user 2026-11-04 (E): Break lewat tombol POST dengan konfirmasi (dulu langsung lewat link
 * ?hal=hapus tanpa konfirmasi; syaratnya isset($_GET['hal']) == 'hapus' juga benar untuk nilai hal apa pun).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}

if (isset($_POST['break'])) {
    $kd = (string) $_POST['break'];
    if (!db_satu("SELECT 1 as ada FROM deposito WHERE norek=?", 's', $kd)) {
        $_SESSION['pesan_trans'] = array('warning', 'Data tidak ditemukan.');
    } elseif (db_jalankan("insert into dep_break select * FROM deposito WHERE norek=?", 's', $kd)) {
        $_SESSION['pesan_trans'] = array('success', 'Data berhasil Proses, Proses berikutnya lakukan Break di menu Break.');
    } else {
        $_SESSION['pesan_trans'] = array('danger', 'Proses Break Gagal.');
    }
    header('Location: master_trans.php');
    exit;
}
$pesan = isset($_SESSION['pesan_trans']) ? $_SESSION['pesan_trans'] : null;
unset($_SESSION['pesan_trans']);

halaman_awal('Data Transaksi Simpanan Nur Insani', array('assets/app/datatables-ni.css'));
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni mw-100">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Data Transaksi Simpanan Nur Insani</span>
    <a href="../transaksi/input_trns.php" class="btn btn-sm btn-ni">+ Data transaksi</a>
  </div>
  <div class="card-body">
    <table id="lookup" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>Unit</th>
          <th>Nomor Rekening</th>
          <th>No. Bilyet</th>
          <th>Nama</th>
          <th class="text-end">Simpanan</th>
          <th>Tenor</th>
          <th>Nisbah</th>
          <th>Rate</th>
          <th>Cara Hitung</th>
          <th>Start Date</th>
          <th>Maturity Date</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/fund_deposito.js',
));
