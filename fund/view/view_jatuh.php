<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Realisasi Bagi Hasil dan Posting: jadwal bagi hasil deposito yang jatuh tempo pada tanggal terpilih
 * (bawaan hari ini) dan belum direalisasi, dengan pilihan deposito dan tombol Realisasi (../realisasi_dep.php).
 * Hitungan dari inc/bagi_hasil_deposito.php, sama dengan yang dibukukan.
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, prepared statement; daftar pilihan (dulu ../depo_list.php, dibuka lewat
 * link Realisasi tiap baris namun menampilkan semua deposito tanggal itu) digabung ke halaman ini. Pencarian tetap
 * mengisi $_SESSION['jatuh'] (dibaca cetak bilyet/advis) seperti versi lama.
 * Perbaikan atas perintah user 2026-11-04: daftar memakai syarat yang sama dengan pembukuan (dulu depo_list tanpa
 * syarat ket kosong); kolom Dibukukan dan Pajak menampilkan jumlah menurut cara hitung (dulu depo_list selalu
 * menampilkan hitungan nisbah).
 * Jumlah hari dari hari_dep (Seting Parameter > Jumlah Hari Bagi Hasil); peringatan bila belum diisi (cadangan 30).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "fund") {
    header("location:../../index.html");
    exit;
}
require_once __DIR__ . '/../../inc/bagi_hasil_deposito.php';
include_once __DIR__ . '/../../function.php';

$tanggal = date('Y-m-d');
$pesan = isset($_SESSION['pesan_jatuh']) ? $_SESSION['pesan_jatuh'] : array();
unset($_SESSION['pesan_jatuh']);
if (isset($_GET['tanggal']) && $_GET['tanggal'] !== '') {
    $t = substr(trim((string) $_GET['tanggal']), 0, 10);
    $d = DateTime::createFromFormat('!Y-m-d', $t);
    if ($d && $d->format('Y-m-d') === $t) {
        $tanggal = $t;
        $_SESSION['jatuh'] = $tanggal;
    } else {
        $pesan[] = array('warning', 'Maaf, isi Tanggal Pencarian yang Benar !');
    }
}
$data = bhd_daftar($tanggal);
list($jumlah_hari, $hari_dari_tabel) = bhd_hari();

halaman_awal('Realisasi Bagi Hasil Deposito');
?>
<?php foreach ($pesan as $p): ?>
<div class="alert alert-<?php echo $p[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($p[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endforeach; ?>
<?php if (!$hari_dari_tabel): ?>
<div class="alert alert-warning">
  Jumlah hari bagi hasil belum diisi, sementara dipakai <?php echo $jumlah_hari; ?> hari. Jalankan Posting Parameter atau isi di
  <a href="view_hari_dep.php" class="alert-link">Seting Parameter &rsaquo; Jumlah Hari Bagi Hasil</a>.
</div>
<?php endif; ?>
<div class="card card-ni mw-100">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="get" action="view_jatuh.php" class="row g-2 align-items-end">
      <div class="col-sm-4">
        <label class="form-label" for="tanggal">Tanggal Jatuh Tempo</label>
        <input type="date" name="tanggal" id="tanggal" value="<?php echo h($tanggal); ?>" class="form-control" required>
      </div>
      <div class="col-sm-2">
        <button type="submit" class="btn btn-ni w-100">Search</button>
      </div>
    </form>
  </div>
</div>

<form method="post" action="../realisasi_dep.php" class="mt-3">
  <input type="hidden" name="jatuh_tempo" value="<?php echo h($tanggal); ?>">
  <div class="card card-ni mw-100">
    <div class="card-header">Jatuh Tempo <?php echo h(tgl_indo($tanggal)); ?></div>
    <div class="card-body">
      <?php if (!$data): ?>
      <div class="text-muted">Data tidak ditemukan.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped table-hover align-middle" id="tabel_jatuh">
          <thead>
            <tr>
              <th><input type="checkbox" class="form-check-input" id="pilih_semua" title="Pilih semua"></th>
              <th>Norek</th>
              <th>Nama</th>
              <th>Bilyet</th>
              <th>Status</th>
              <th>Cara Hitung</th>
              <th class="text-end">Rasio %</th>
              <th class="text-end">Eqv %</th>
              <th class="text-end">Nominal</th>
              <th>Jatuh Tempo</th>
              <th class="text-end">Baghas</th>
              <th class="text-end">Rate</th>
              <th class="text-end">Dibukukan</th>
              <th class="text-end">Pajak</th>
              <th class="text-end">Hari</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($data as $row): $x = bhd_hitung($row); ?>
            <tr>
              <td><input type="checkbox" class="form-check-input pilih-deposito" name="cekbox[]" value="<?php echo h($row['norek']); ?>"></td>
              <td><?php echo h($row['norek']); ?></td>
              <td><?php echo h($row['CUST_SHORT_NAME']); ?></td>
              <td><?php echo h($row['bilyet']); ?></td>
              <td><?php echo h($row['aro']); ?></td>
              <td><?php echo h($row['cara_hitung']); ?></td>
              <td class="text-end"><?php echo h($row['rasio']); ?></td>
              <td class="text-end"><?php echo number_format($x['eq'] * 100, 2); ?></td>
              <td class="text-end"><?php echo number_format(compat_num($row['nomi'])); ?></td>
              <td><?php echo h(tgl_indo($row['tgl_jatuh_tempo'])); ?></td>
              <td class="text-end"><?php echo number_format($x['bayar_nisbah']); ?></td>
              <td class="text-end"><?php echo number_format($x['bayar_rate']); ?></td>
              <td class="text-end fw-semibold"><?php echo number_format($x['bayar']); ?></td>
              <td class="text-end"><?php echo number_format($x['pajak']); ?></td>
              <td class="text-end"><?php echo $x['hari']; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex gap-2 justify-content-end align-items-center">
        <span class="text-muted small me-auto" id="jumlah_pilih">0 dipilih</span>
        <button type="submit" class="btn btn-ni px-4" id="tombol_realisasi" disabled
                data-konfirmasi="Realisasi bagi hasil deposito yang dipilih? Jurnal akan langsung dibukukan.">Realisasi Deposito</button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</form>
<?php
halaman_akhir(array('assets/app/fund_realisasi.js'));
