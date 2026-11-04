<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Seting Parameter > Param Margin: daftar input dana per bulan (param_deposito + jenis_dana; data per halaman
 * dari ajax-grid-data_margin.php), Tambah Data (../transaksi/input_margin.php) dan Hapus.
 * Hapus (keputusan user 2026-11-04): hanya kode bulan yang BELUM diposting (param_deposito.ket kosong); menghapus baris
 * kode bulan itu di param_deposito, param_rasio dan param_dep (yang ditulis input_margin.php) selama belum diposting.
 * Dimigrasi 2026-11-04 (perintah user): dulu tanpa cek login; tombol Edit/Hapus di daftar menuju halaman deposan
 * (transaksi/edit.php, master_fund.php?hal=hapus) dan kode hapus di halaman ini menghapus "WHERE no=" (kolom tidak ada)
 * sehingga tidak pernah berfungsi; kini Bootstrap 5 + DataTables lokal, prepared statement, kolom Status Posting.
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
    $ada = db_satu("SELECT code_bulan, ket FROM param_deposito WHERE code_bulan=?", 's', $kode);
    if (!$ada) {
        $pesan = array('warning', 'Data ' . $kode . ' tidak ditemukan.');
    } elseif ((string) $ada['ket'] !== '') {
        $pesan = array('warning', 'Data ' . $kode . ' sudah diposting (' . $ada['ket'] . '), tidak bisa dihapus.');
    } else {
        // tabel MyISAM (tanpa transaksi); param_deposito terakhir supaya bila gagal di tengah, data masih tampil dan bisa diulang
        $ok = db_jalankan("DELETE FROM param_rasio WHERE code_bulan=? AND ket=''", 's', $kode)
            && db_jalankan("DELETE FROM param_dep WHERE code_bulan=? AND IFNULL(ket, '')=''", 's', $kode)
            && db_jalankan("DELETE FROM param_deposito WHERE code_bulan=? AND ket=''", 's', $kode);
        $pesan = $ok ? array('success', 'Data ' . $kode . ' berhasil dihapus.') : array('danger', 'Data ' . $kode . ' gagal dihapus: ' . db_error_terakhir());
    }
    $_SESSION['pesan_margin'] = $pesan;
    header('Location: view_margin.php');
    exit;
}
$pesan = isset($_SESSION['pesan_margin']) ? $_SESSION['pesan_margin'] : null;
unset($_SESSION['pesan_margin']);

halaman_awal('Parameter Margin Nur Insani', array('assets/app/datatables-ni.css'));
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo h($pesan[0]); ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni mw-100">
  <div class="card-header">Parameter Margin Nur Insani</div>
  <div class="card-body">
    <table id="tabel_margin" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>Kode Bulan</th>
          <th>Jenis Dana</th>
          <th class="text-end">Nominal</th>
          <th class="text-end">Margin</th>
          <th>Bulan</th>
          <th>Status Posting</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
    </table>
    <div class="text-end mt-2">
      <a href="../transaksi/input_margin.php" class="btn btn-sm btn-ni">Tambah Data</a>
    </div>
  </div>
</div>
<form method="post" action="view_margin.php" id="form_hapus" class="d-none">
  <input type="hidden" name="hapus" id="hapus_kode">
</form>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/fund_margin.js',
));
