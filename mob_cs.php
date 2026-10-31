<?php require_once __DIR__ . '/php8_compat.php'; 
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

include "conn.php"; 

// Tampilan Bootstrap 5 (2026-10-31, permintaan user): hanya HTML/CSS yang diganti.
// Data tetap dari mob-ajax-grid-data_trns.php (kode versi lama); tombol export tetap ke file lama.
require_once __DIR__ . '/inc/tampilan.php';
halaman_awal('Data Transaksi CS Mobile Collection', array('assets/app/datatables-ni.css'));
?>
<div class="card card-ni mw-100">
  <div class="card-header">Data Transaksi CS Mobile Collection</div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="lookup" class="table table-sm table-bordered table-hover table-ni align-middle w-100">
        <thead>
          <tr>
            <th>Unit</th>
            <th>Tgl Tagih</th>
            <th>Kelompok</th>
            <th>CIF</th>
            <th>Nama</th>
            <th>Tagihan</th>
            <th>Nyata</th>
            <th>Status Transaksi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
      <a href="mob_export_cs.php" class="btn btn-outline-success btn-sm">Excel Cs</a>
      <a href="mob_export_penarikan.php" class="btn btn-outline-success btn-sm">Excel Penarikan</a>
      <a href="mob_export_lebaran.php" class="btn btn-outline-success btn-sm">Excel Lebaran</a>
      <a href="mob_export_lima.php" class="btn btn-outline-success btn-sm">Excel Lima%</a>
      <a href="mob_export_pelunasan.php" class="btn btn-outline-success btn-sm">Excel Pelunasan</a>
      <a href="mob_export_tunggakan.php" class="btn btn-outline-success btn-sm">Excel Tunggakan</a>
      <a href="mob_export_wo.php" class="btn btn-outline-success btn-sm">Excel WO</a>
      <a href="mob_export_omzet.php" class="btn btn-outline-success btn-sm">Excel Omzet</a>
    </div>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/mob_cs.js',
));
