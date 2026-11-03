<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu kp > Koreksi: daftar jurnal tabel_transaksi 2 hari terakhir (semua unit) dengan Edit (tanggal, kode rekening,
 * debet, kredit) dan Hapus. Data dari ajax_koreksi.php?type=koreksi, simpan/hapus lewat crud_koreksi.php.
 * Dimigrasi 2026-11-03: dulu HTML statis tanpa cek login dengan Bootstrap/DataTables/jQuery dari CDN; kini kerangka
 * bersama (Bootstrap 5 lokal), wajib login, DataTables lokal (assets/media), skrip di assets/app/kp_koreksi.js.
 * Perilaku sama dengan versi lama; judul kolom Jenis/Keterangan disesuaikan dengan isinya (versi lama tertukar) dan
 * Kode Transaksi di form Edit hanya-baca (versi lama bisa diketik tetapi tidak pernah disimpan).
 * Perintah user 2026-11-03: hanya level kp; tabel DataTables server-side (satu halaman per permintaan, cari/urut di
 * database) karena versi lama memuat seluruh jurnal 3 hari semua unit sekaligus.
 */
require_once __DIR__ . '/../inc/tampilan.php';
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
// hanya level kp (perintah user 2026-11-03; dulu halaman statis tanpa cek)
if ($_SESSION['level'] != 'kp') {
    header("location:../index.html");
    exit;
}

halaman_awal('Data Jurnal Kantor Pusat', array('assets/app/datatables-ni.css'));
?>
<div class="card card-ni mw-100">
  <div class="card-header">Jurnal 2 hari terakhir</div>
  <div class="card-body">
    <table id="omzetTable" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal</th>
          <th>Unit</th>
          <th>Kode Transaksi</th>
          <th>Kode Rekening</th>
          <th>Keterangan Transaksi</th>
          <th>Jenis Transaksi</th>
          <th class="text-end">Debet</th>
          <th class="text-end">Kredit</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Form Edit -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formData" class="form-ni" autocomplete="off">
        <div class="modal-header">
          <h2 class="modal-title fs-6" id="modalTitle">Edit Data</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
          <div class="mb-2">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
            <label for="kode_transaksi" class="form-label">Kode Transaksi</label>
            <input type="text" name="kode_transaksi" id="kode_transaksi" class="form-control form-control-sm" readonly>
          </div>
          <div class="mb-2">
            <label for="kode_rekening" class="form-label">Kode Rekening</label>
            <input type="text" name="kode_rekening" id="kode_rekening" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
            <label for="debet" class="form-label">Debet</label>
            <input type="number" name="debet" id="debet" class="form-control form-control-sm" required>
          </div>
          <div class="mb-2">
            <label for="kredit" class="form-label">Kredit</label>
            <input type="number" name="kredit" id="kredit" class="form-control form-control-sm" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-ni btn-sm">Simpan</button>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/kp_koreksi.js',
));
