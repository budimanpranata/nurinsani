<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > View Data: daftar anggota & pembiayaan (DataTables server-side, data dari server_side.php).
 * Tombol Edit membuka new3.php?cif=... seperti versi lama.
 * Catatan: modal "Edit Data" versi lama dihapus karena tidak pernah bisa dipakai
 * (tidak ada tombol .editBtn dan update_data.php tidak ada).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();

// Cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

halaman_awal('Data Pembiayaan', array('assets/app/datatables-ni.css'));
?>
<div class="card shadow-sm border-0">
  <div class="card-body position-relative">
    <div class="table-responsive">
      <table id="example" class="table table-sm table-bordered table-ni table-hover align-middle w-100">
        <thead>
          <tr>
            <th>KTP</th>
            <th>Unit</th>
            <th>Kode Kel</th>
            <th>Norek</th>
            <th>CIF</th>
            <th>Nama</th>
            <th class="text-end">Pembiayaan</th>
            <th class="text-end">Plafond</th>
            <th class="text-end">Saldo Margin</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/view_data.js',
));
