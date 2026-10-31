<?php require_once __DIR__ . '/php8_compat.php'; 
session_start();
ob_start();
        $userid=$_SESSION['userid'];
        if(!isset($_SESSION['userid'])){
   header("location:../index.html");
 exit; }

// Tampilan Bootstrap 5 (2026-10-31, permintaan user): hanya HTML/CSS yang diganti.
// Data tetap dari mob-ajax-grid-data_bank.php (kode versi lama).
require_once __DIR__ . '/inc/tampilan.php';
halaman_awal('Data Setoran Bank Mobile Collection', array('assets/app/datatables-ni.css'));
?>
<div class="card card-ni mw-100">
  <div class="card-header">Data Setoran Bank Mobile Collection</div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="lookup" class="table table-sm table-bordered table-hover table-ni align-middle w-100">
        <thead>
          <tr>
            <th>Tgl Setor</th>
            <th>Unit</th>
            <th>AO</th>
            <th>Bank</th>
            <th>Nominal</th>
            <th>Bukti Setor</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/mob_setoran_bank.js',
));
