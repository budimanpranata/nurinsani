<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu Transaksi > Simpanan (level kp): daftar saldo simpanan_kp per norek dan jenis (DataTables server-side dari
 * ajax-grid-data_trns.php), tombol + Anggota (input.php) dan export Excel per jenis (export_*.php).
 * Logika sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah. Termasuk perilaku lama
 * ?hal=hapus&kd=<norek> yang menghapus data deposito (tidak ada tautan ke sana; PEKERJAAN_TERTUNDA.md bagian 43).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}

$pesan = null;
// versi lama: isset($_GET['hal']) == 'hapus' -> benar untuk nilai hal apa pun
if (isset($_GET['hal'])) {
    $kd_dept = isset($_GET['kd']) ? (string) $_GET['kd'] : '';
    if (!db_satu("SELECT 1 as ada FROM deposito WHERE norek=?", 's', $kd_dept)) {
        $pesan = array('success', 'Data tidak ditemukan.');
    } elseif (db_jalankan("DELETE FROM deposito WHERE norek=?", 's', $kd_dept)) {
        $pesan = array('primary', 'Data berhasil dihapus.');
    } else {
        $pesan = array('danger', 'Data gagal dihapus.');
    }
}

halaman_awal('Data Transaksi Simpanan NurInsani', array('assets/app/datatables-ni.css'));
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card shadow-sm border-0">
  <div class="card-body position-relative">
    <div class="table-responsive">
      <table id="lookup" class="table table-sm table-bordered table-ni table-hover align-middle w-100">
        <thead>
          <tr>
            <th>Unit</th>
            <th>Nomor Rekening</th>
            <th>CIF</th>
            <th>Nama</th>
            <th>Simpanan</th>
            <th class="text-end">Saldo</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
      <a href="input.php" class="btn btn-sm btn-ni">+ Anggota</a>
      <a href="export_pokok.php" class="btn btn-sm btn-outline-success">Excel Pokok</a>
      <a href="export_wajib.php" class="btn btn-sm btn-outline-success">Excel Wajib</a>
      <a href="export_khusus.php" class="btn btn-sm btn-outline-success">Excel Khusus</a>
      <a href="export_umum.php" class="btn btn-sm btn-outline-success">Excel Umum</a>
    </div>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/kp_simpanan.js',
));
