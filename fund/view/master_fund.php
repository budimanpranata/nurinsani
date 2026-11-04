<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Input Data Master > Data Master Deposan: daftar deposan (DataTables server-side dari ajax-grid-data.php)
 * dengan tombol Edit, serta + Data Perorangan (../transaksi/input.php) dan + Data Perusahaan (../transaksi/input_per.php).
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, DataTables lokal (assets/media), skrip assets/app/fund_deposan.js.
 * Perintah user 2026-11-04: tombol Hapus dibuang (versi lama menjalankan DELETE FROM anggota WHERE no = CIF deposan,
 * tabel dan kolom salah, sehingga tidak pernah menghapus apa pun).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}

halaman_awal('Data Deposan Nur Insani', array('assets/app/datatables-ni.css'));
?>
<div class="card card-ni mw-100">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Data Deposan Nur Insani</span>
    <span>
      <a href="../transaksi/input.php" class="btn btn-sm btn-ni">+ Data Perorangan</a>
      <a href="../transaksi/input_per.php" class="btn btn-sm btn-ni">+ Data Perusahaan</a>
    </span>
  </div>
  <div class="card-body">
    <table id="lookup" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>Unit</th>
          <th>CIF</th>
          <th>Nama</th>
          <th>Tanggal Lahir</th>
          <th>Alamat</th>
          <th>No Telepon</th>
          <th class="text-center">Action</th>
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
    'assets/app/fund_deposan.js',
));
