<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu Monitoring > Daily Deposito (level kp): daftar dep_realisasi (penempatan, break, bagi hasil) dengan tombol
 * Disburse yang membuka modal "Posting Ke Jurnal" dan mengirim ke ../transaksi/engine_update_dep.php?p=update (AJAX).
 * Isi tabel dan alur kirim sama dengan versi lama; tampilan dan query (prepared statement) diperbarui. Saran rekening
 * dari ajax/cari_gl_kp.php?kolom=bank (query sama dengan kp/autosuggest_kp.php).
 * Perbaikan 2026-11-03 (perintah user, bagian 51): keterangan jurnal di modal diisi keterangan baris yang diklik
 * (dulu keterangan baris terakhir tabel untuk semua baris).
 * Dibuang: modal "Tambah Data" dan "Konfirmasi hapus" yang tidak punya tombol pembuka (tidak pernah bisa dipakai).
 * Temuan versi lama: PEKERJAAN_TERTUNDA.md bagian 51.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!isset($_SESSION['userid'])) {
    header("location:../../index.html");//jika belum login jangan lanjut..
    exit;
}
include "../../indo_tgl.php";

$r = db_satu("select kode_branch from user where userid =?", 's', (string) $userid);
$unit = $r ? (string) $r['kode_branch'] : '';
$jurnal_keluar = db_satu("SELECT max(nomor_jurnal) as n FROM jurnal_umum where unit =? ORDER BY tanggal_selesai DESC", 's', $unit);
$nomor_jurnal = compat_num($jurnal_keluar ? $jurnal_keluar['n'] : null) + 1;
$kode_transaksi = "KK/" . $unit . $nomor_jurnal;

$baris = array();
$bayar = null;
$ket = null;
$tanggal = null;
foreach (db_semua("select dep_realisasi.norek,CUST_SHORT_NAME,jumlah_bayar as nominal,tgl_bayar,tgl_jatuh_tempo,tenor,jumlah_bayar,status from dep_realisasi
    left join deposan on  deposan.cif = dep_realisasi.cif
    left join deposito on deposito.norek=dep_realisasi.norek") as $row) {
    // versi lama: kolom bagi hasil untuk BREAK memakai nilai baris sebelumnya ($bayar tidak diisi ulang)
    if ($row['status'] == 'PENEMPATAN') {
        $bayar = 0;
        $ket = 'PENEMPATAN';
    } elseif ($row['status'] == 'BREAK') {
        // perbaikan 2026-11-03: nomor rekening ikut (versi lama memakai $norek yang tidak pernah diisi)
        $ket = "PEMBAYARAN BREAK PENEMPATAN " . $row['norek'];
    } else {
        $bayar = $row['nominal'];
        $ket = 'BAGI HASIL';
    }
    $tanggal = $row['tgl_jatuh_tempo'];
    $row['bayar'] = $bayar;
    $row['ket_jurnal'] = $ket;
    $baris[] = $row;
}
$info = isset($_SESSION['info']) ? $_SESSION['info'] : null;
unset($_SESSION['info']);

halaman_awal('Monitoring Daily Disbursement');
?>
<div class="card card-ni mw-100">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <thead>
        <tr>
          <th>NOREK</th><th>NAMA</th><th class="text-end">NOMINAL</th><th>TGL KONTRAK</th><th>TGL JATUH TEMPO</th>
          <th>TENOR</th><th class="text-end">BAGI HASIL</th><th>STATUS</th><th class="text-center">DETAIL</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($baris as $r): ?>
        <tr>
          <td><?php echo h($r['norek']); ?></td>
          <td><?php echo h($r['CUST_SHORT_NAME']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['nominal'])); ?></td>
          <?php // versi lama: kolom TGL KONTRAK juga menampilkan tanggal jatuh tempo ?>
          <td><?php echo h(tgl_indo($r['tgl_jatuh_tempo'])); ?></td>
          <td><?php echo h(tgl_indo($r['tgl_jatuh_tempo'])); ?></td>
          <td><?php echo h($r['tenor']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['bayar'])); ?></td>
          <td><?php echo h($r['status']); ?></td>
          <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-warning tombol-disburse"
                    data-id="<?php echo h($r['norek']); ?>"
                    data-jatuh="<?php echo h($r['tgl_bayar']); ?>"
                    data-nomi="<?php echo h($r['nominal']); ?>"
                    data-ket="<?php echo h($r['ket_jurnal']); ?>">Disburse</button>
          </td>
        </tr>
      <?php endforeach; ?>
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
      <form id="form-data" method="post" action="../transaksi/engine_update_dep.php?p=update" autocomplete="off">
        <input type="hidden" name="id" id="id">
        <div class="modal-header">
          <h2 class="modal-title fs-6" id="judul-edit">Posting Ke Jurnal</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label small" for="norek">ID TRANSAKSI</label>
            <input type="text" name="norek" id="norek" value="<?php echo h($kode_transaksi); ?>" class="form-control form-control-sm">
          </div>
          <div class="mb-2">
            <label class="form-label small" for="jatuh">TANGGAL</label>
            <input type="text" name="jatuh" id="jatuh" value="<?php echo h($tanggal); ?>" class="form-control form-control-sm" placeholder="0000-00-00">
          </div>
          <div class="mb-2">
            <label class="form-label small" for="ket">KETERANGAN JURNAL</label>
            <?php // perbaikan 2026-11-03: diisi keterangan baris yang diklik (versi lama: keterangan baris terakhir + spasi) ?>
            <input type="text" name="ket" id="ket" class="form-control form-control-sm" value="" maxlength="50">
          </div>
          <div class="mb-2">
            <label class="form-label small" for="kode">REKENING</label>
            <div class="autocomplete position-relative">
              <input type="text" name="code" id="kode" class="form-control form-control-sm"
                     data-autocomplete="../../ajax/cari_gl_kp.php?kolom=bank" data-isi-nilai="rekening_gl" data-isi-label="label">
            </div>
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
halaman_akhir(array('assets/app/kp_daily_deposito.js'));
