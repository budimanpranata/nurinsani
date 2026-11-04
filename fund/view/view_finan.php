<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Seting Parameter > Param Financial: daftar param_finan (data per halaman dari ajax-grid-data_finan.php),
 * Tambah Data (../transaksi/input_finan.php) dan Hapus.
 * Hapus (keputusan user 2026-11-04): hanya periode TERBARU (code_param terbesar, yang dipakai Posting Parameter
 * berikutnya - inc/hari_dep.php hari_dep_periode_terbaru); periode lama tetap sebagai riwayat.
 * Dimigrasi 2026-11-04 (perintah user): dulu tanpa cek login; tombol Edit menuju edit deposan (transaksi/edit.php) dan
 * Hapus (link GET tanpa konfirmasi) mengirim kd kosong sehingga selalu "Data tidak ditemukan"; kini Bootstrap 5 +
 * DataTables lokal, prepared statement, kolom Periode S.d dan Jumlah Hari (DATEDIFF(sd, dari), dipakai Posting).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "fund") {
    header("location:../../index.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
    $kode = trim((string) $_POST['hapus']);
    $terbaru = db_satu("SELECT max(code_param) AS kode FROM param_finan");
    if (!db_satu("SELECT code_param FROM param_finan WHERE code_param=?", 's', $kode)) {
        $pesan = array('warning', 'Data ' . $kode . ' tidak ditemukan.');
    } elseif (!$terbaru || (string) $terbaru['kode'] !== $kode) {
        $pesan = array('warning', 'Data ' . $kode . ' bukan periode terbaru, tidak bisa dihapus.');
    } elseif (db_jalankan("DELETE FROM param_finan WHERE code_param=?", 's', $kode)) {
        $pesan = array('success', 'Data ' . $kode . ' berhasil dihapus.');
    } else {
        $pesan = array('danger', 'Data ' . $kode . ' gagal dihapus: ' . db_error_terakhir());
    }
    $_SESSION['pesan_finan'] = $pesan;
    header('Location: view_finan.php');
    exit;
}
$pesan = isset($_SESSION['pesan_finan']) ? $_SESSION['pesan_finan'] : null;
unset($_SESSION['pesan_finan']);

halaman_awal('Parameter Financial Nur Insani', array('assets/app/datatables-ni.css'));
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo h($pesan[0]); ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni mw-100">
  <div class="card-header">Parameter Financial Nur Insani</div>
  <div class="card-body">
    <table id="tabel_finan" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>Kode Bulan</th>
          <th class="text-end">Nominal Murabahah</th>
          <th class="text-end">Nominal Margin</th>
          <th>Bulan</th>
          <th>Periode S.d</th>
          <th class="text-end">Jumlah Hari</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
    </table>
    <div class="d-flex justify-content-between align-items-center mt-2">
      <span class="small text-muted">Hapus hanya untuk periode terbaru (yang dipakai Posting Parameter berikutnya).</span>
      <a href="../transaksi/input_finan.php" class="btn btn-sm btn-ni">Tambah Data</a>
    </div>
  </div>
</div>
<form method="post" action="view_finan.php" id="form_hapus" class="d-none">
  <input type="hidden" name="hapus" id="hapus_kode">
</form>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/fund_finan.js',
));
