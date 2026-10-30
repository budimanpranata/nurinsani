<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Omzet dan Nisbah: daftar omzet anggota (tambah / edit / hapus lewat crud_omzet.php)
 * dan tampilan nisbah pengajuan MUSYAROKAH. Data dari ajax/ajax_omzet.php. Perilaku sama dengan versi lama;
 * aset (jQuery, DataTables, Bootstrap) kini dari folder lokal, bukan CDN.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();

halaman_awal('Data Omzet Anggota', array('assets/app/datatables-ni.css'));
?>
<div class="d-flex flex-wrap gap-2 mb-3">
  <button type="button" class="btn btn-ni btn-sm" id="tombol-tambah">+ Tambah Data</button>
  <button type="button" class="btn btn-outline-success btn-sm" id="tombol-nisbah">View Nisbah</button>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table id="omzetTable" class="table table-sm table-bordered table-ni table-hover align-middle w-100">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Nama Kelompok</th>
            <th>Nama AO</th>
            <th>CIF</th>
            <th>Nama Anggota</th>
            <th class="text-end">Omzet</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Modal Form Tambah/Edit -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formData" class="form-ni">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Tambah Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
          <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label for="cif" class="form-label">CIF</label>
            <input type="text" name="cif" id="cif" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label for="nominal" class="form-label">Nominal</label>
            <input type="number" name="nominal" id="nominal" class="form-control form-control-sm" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-ni btn-sm px-4">Simpan</button>
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal View Nisbah -->
<div class="modal fade" id="modalView" tabindex="-1" aria-labelledby="judulView" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulView">Data Pengajuan (MUSYAROKAH)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table id="viewTable" class="table table-sm table-bordered table-ni align-middle w-100">
            <thead>
              <tr>
                <th>Kelompok</th>
                <th>AO</th>
                <th>CIF</th>
                <th>Anggota</th>
                <th class="text-end">Plafond</th>
                <th class="text-end">Nisbah</th>
                <th class="text-end">Omzet</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/omzet.js',
));
