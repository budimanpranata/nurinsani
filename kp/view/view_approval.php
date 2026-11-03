<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu Monitoring > Approval (level kp): daftar pengajuan pembiayaan calon_loan status PENDING per unit dan tanggal
 * murabahah, dengan tombol Disburse yang membuka modal "Posting Ke Jurnal" dan mengirim ke
 * ../transaksi/engine_update_upproval.php?p=update (AJAX).
 * Isi tabel, nilai awal modal, dan alur kirim sama dengan versi lama; tampilan dan query (prepared statement)
 * diperbarui. Saran rekening dari ajax/cari_gl_kp.php?kolom=bank (query sama dengan kp/autosuggest_kp.php).
 * Perbaikan 2026-11-03 (perintah user, bagian 57): satu baris per unit + tgl wakalah + tgl murabahah (dulu dikelompokkan
 * per tgl murabahah + unit, sedangkan proses mengubah status per tgl wakalah); tgl murabahah ikut dikirim; kode
 * transaksi tidak lagi dihitung di halaman, tetapi dibuat unik oleh proses (KK/1010 + kode acak).
 * Dibuang: modal "Tambah Data" dan "Konfirmasi hapus" yang tidak punya tombol pembuka (tidak pernah bisa dipakai).
 * Temuan versi lama: PEKERJAAN_TERTUNDA.md bagian 57.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!isset($_SESSION['userid'])) {
    header("location:../../index.html");//jika belum login jangan lanjut..
    exit;
}
include "../../indo_tgl.php";

// versi lama: keterangan jurnal tetap (dengan spasi di belakang)
$ket = "DROPING WAKALAH UNIT ";

$baris = db_semua("select count(no_anggota) as noa, sum(plafond) as pla,branch.unit as nama_unit,calon_loan.unit as unit,kelompok.nama_kel,calon_loan.code_kel,tgl_murab,tgl_wakalah,status_app from calon_loan
 inner join kelompok on kelompok.code_kel = calon_loan.code_kel
 inner join branch on branch.kode_branch= calon_loan.unit
 WHERE status_app='PENDING' group by calon_loan.unit,calon_loan.tgl_wakalah,calon_loan.tgl_murab order by calon_loan.unit, tgl_wakalah, tgl_murab");
$info = isset($_SESSION['info']) ? $_SESSION['info'] : null;
unset($_SESSION['info']);

halaman_awal('Monitoring Approval');
?>
<div class="card card-ni mw-100">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <thead>
        <tr>
          <th>UNIT</th><th>NAMA UNIT</th><th class="text-end">PEMBIAYAAN</th><th>TGL WAKALAH</th><th>TGL MURABAHAH</th>
          <th class="text-end">NOA</th><th>STATUS</th><th class="text-center">DETAIL</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($baris as $r): ?>
        <tr>
          <td><?php echo h($r['unit']); ?></td>
          <td><?php echo h($r['nama_unit']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['pla'])); ?></td>
          <td><?php echo h(tgl_indo($r['tgl_wakalah'])); ?></td>
          <td><?php echo h(tgl_indo($r['tgl_murab'])); ?></td>
          <td class="text-end"><?php echo h($r['noa']); ?></td>
          <td><?php echo h($r['status_app']); ?></td>
          <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-warning tombol-disburse"
                    data-unit="<?php echo h($r['unit']); ?>"
                    data-jatuh="<?php echo h($r['tgl_wakalah']); ?>"
                    data-murab="<?php echo h($r['tgl_murab']); ?>"
                    data-nomi="<?php echo h($r['pla']); ?>">Disburse</button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$baris): ?>
        <tr><td colspan="8" class="text-center text-muted">Tidak ada pengajuan berstatus PENDING</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($info !== null): ?>
<div class="alert alert-info mt-3" style="max-width:20rem">Berhasil <?php echo h($info); ?> Data</div>
<?php endif; ?>

<div class="modal fade" id="edit-data" tabindex="-1" aria-labelledby="judul-edit" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="form-data" method="post" action="../transaksi/engine_update_upproval.php?p=update" autocomplete="off">
        <input type="hidden" name="murab" id="murab">
        <div class="modal-header">
          <h2 class="modal-title fs-6" id="judul-edit">Posting Ke Jurnal</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label small" for="kode_trn">ID TRANSAKSI</label>
            <input type="text" id="kode_trn" value="KK/1010 + kode unik (otomatis saat simpan)" class="form-control form-control-sm" readonly>
          </div>
          <div class="mb-2">
            <label class="form-label small" for="jatuh">TANGGAL</label>
            <input type="text" name="jatuh" id="jatuh" class="form-control form-control-sm" placeholder="0000-00-00">
          </div>
          <div class="mb-2">
            <label class="form-label small" for="unit">UNIT</label>
            <input type="text" name="unit" id="unit" class="form-control form-control-sm">
          </div>
          <div class="mb-2">
            <label class="form-label small" for="ket">KETERANGAN JURNAL</label>
            <input type="text" name="ket" id="ket" class="form-control form-control-sm" value="<?php echo h($ket); ?>" maxlength="50">
          </div>
          <div class="mb-2">
            <label class="form-label small" for="kode">REKENING</label>
            <div class="autocomplete position-relative">
              <input type="text" name="code" id="kode" class="form-control form-control-sm"
                     data-autocomplete="../../ajax/cari_gl_kp.php?kolom=bank" data-isi-nilai="rekening_gl" data-isi-label="label">
            </div>
          </div>
          <div class="form-check mb-2">
            <?php // dicentang: pengajuan tetap PENDING (masih tampil di daftar); tidak dicentang: menjadi APPROVE ?>
            <input class="form-check-input" type="checkbox" name="checkbox" id="checkbox">
            <label class="form-check-label small" for="checkbox">Split</label>
          </div>
          <div class="mb-2">
            <label class="form-label small" for="nomi">JUMLAH</label>
            <input type="text" name="nomi" id="nomi" class="form-control form-control-sm">
          </div>
          <div class="progress d-none mt-3" id="progres" style="height:.75rem">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-ni btn-sm btn-submit">Simpan</button>
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-peringatan" tabindex="-1" aria-labelledby="judul-peringatan" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="judul-peringatan">Peringatan</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-bg-danger" id="pesan-required-field"></div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
<?php
halaman_akhir(array('assets/app/kp_approval.js'));
