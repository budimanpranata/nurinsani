<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Report > View Realisasi Baghas: jadwal bagi hasil deposito (deposito_detail) dengan nama deposan dan
 * status realisasinya; data per halaman dari serverSide.php, saringan Status (bawaan Semua = isi sama dengan versi lama).
 * Dimigrasi 2026-11-04 (perintah user): Bootstrap 5, DataTables lokal, wajib login level fund (dulu tanpa cek login);
 * tombol Edit/delete dan modal dibuang (memanggil crud.php yang tidak pernah ada); pencarian per kolom di kaki tabel
 * (petanya salah) diganti satu kotak Cari; bagi hasil dengan pemisah ribuan, tanggal tgl_indo.
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

halaman_awal('View Realisasi Bagi Hasil', array('assets/app/datatables-ni.css'));
?>
<div class="card card-ni mw-100">
  <div class="card-header">View Realisasi Bagi Hasil Deposito</div>
  <div class="card-body">
    <div class="row g-2 align-items-end mb-3">
      <div class="col-sm-3">
        <label class="form-label" for="status">Status</label>
        <select id="status" class="form-select">
          <option value="">Semua</option>
          <option value="belum">Belum</option>
          <option value="REALISASI">REALISASI (Realisasi Bagi Hasil)</option>
          <option value="BAYAR">BAYAR (Disburse)</option>
        </select>
      </div>
      <div class="col-sm-9 small text-muted">Tanggal dapat dicari dengan format dd-mm-yyyy atau yyyy-mm-dd.</div>
    </div>
    <table id="tabel_baghas" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>Id Deposito</th>
          <th>Nama</th>
          <th class="text-end">Pembayaran Ke</th>
          <th class="text-end">Bagi Hasil</th>
          <th>Tanggal Tempo</th>
          <th>Tanggal Bayar</th>
          <th>Status</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/fund_baghas.js',
));
