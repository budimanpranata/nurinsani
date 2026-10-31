<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Monitoring Nisbah: jadwal nisbah musyarakah per anggota (pinjaman_detail_mus)
 * dan ringkasan nisbah pembiayaan MUSYAROKAH. Data dari ajax/ajax_nisbah.php. Perilaku sama dengan versi lama;
 * aset (jQuery, DataTables, Bootstrap) kini dari folder lokal, bukan CDN.
 * Catatan: tombol Edit/Hapus dan form modal versi lama dihapus karena tidak pernah bisa dipakai
 * (memanggil crud_nisbah.php yang tidak ada).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();

halaman_awal('Data Nisbah Anggota', array('assets/app/datatables-ni.css'));
?>
<div class="d-flex flex-wrap gap-2 mb-3">
  <button type="button" class="btn btn-outline-success btn-sm" id="tombol-nisbah">View Nisbah</button>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body">
    <div class="table-responsive">
      <table id="nisbahTable" class="table table-sm table-bordered table-ni table-hover align-middle w-100">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Nama Kelompok</th>
            <th>Nama AO</th>
            <th>CIF</th>
            <th>Nama Anggota</th>
            <th class="text-end">Ke</th>
            <th class="text-end">Nisbah</th>
            <th class="text-end">Nisbah Anggota</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!-- Modal View Nisbah -->
<div class="modal fade" id="modalView" tabindex="-1" aria-labelledby="judulView" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulView">Data Pembiayaan (MUSYAROKAH)</h5>
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
    'assets/app/nisbah.js',
));
