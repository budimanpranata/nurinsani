<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Approve > Approval Pengajuan (level lead): daftar calon_loan gol='0' (DataTables server-side dari
 * server_side_approval.php, protokol dan pencarian per kolom sama dengan versi lama). Tombol View Data membuka
 * new4.php?cif=... untuk di-approve.
 * Perubahan: wajib login (versi lama tanpa cek login); modal "Pemeliharaan Tagihan" dan tautan delete versi lama
 * dihapus karena tidak pernah berfungsi di halaman ini (lihat PEKERJAAN_TERTUNDA.md bagian 38).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();

$kolom = array('Kode Kel', 'Norek', 'cif', 'nama', 'Pembiayaan', 'Saldo Pembiayaan', 'Saldo Margin', 'Action');
halaman_awal('Approval Pengajuan', array('assets/app/datatables-ni.css'));
?>
<div class="card shadow-sm border-0">
  <div class="card-body position-relative">
    <div class="table-responsive">
      <table id="example" class="table table-sm table-bordered table-ni table-hover align-middle w-100">
        <thead>
          <tr>
            <?php foreach ($kolom as $i => $judul): ?>
            <th<?php echo $i >= 4 && $i <= 6 ? ' class="text-end"' : ''; ?>><?php echo h($judul); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <?php foreach ($kolom as $judul): ?>
            <th><?php if ($judul !== 'Action'): ?><input type="text" class="form-control form-control-sm" placeholder="Search <?php echo h($judul); ?>"><?php endif; ?></th>
            <?php endforeach; ?>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/approval.js',
));
