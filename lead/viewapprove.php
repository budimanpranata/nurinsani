<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Approve > View Data Deviasi (level lead): form cari data approve deviasi per tanggal atau norek/CIF,
 * dikirim ke listapprove.php (sama dengan versi lama). Hanya tampilan yang berubah; versi lama tanpa cek login.
 * Tanggal tetap dikirim dalam format YYYY-MM-DD seperti kalender popup versi lama.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();

halaman_awal('Cari Data Approve');
?>
<div class="card card-ni">
  <div class="card-body">
    <form action="listapprove.php" method="post" name="postform" class="row g-3 align-items-end" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="tgl_trn">Masukan Tanggal Pencarian</label>
        <input name="tgl_trn" type="date" id="tgl_trn" value="" class="form-control">
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nopen">Norek</label>
        <input name="nopen" type="text" id="nopen" value="" class="form-control">
      </div>
      <div class="col-12">
        <button type="submit" value="Cari Data verifikasi" name="cari" class="btn btn-ni">Cari Data verifikasi</button>
      </div>
    </form>
  </div>
</div>
<?php
halaman_akhir();
