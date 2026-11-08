<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Seting Parameter > Jumlah Hari Bagi Hasil: lihat dan ubah hari_dep.jumlah_hari (inc/hari_dep.php), yaitu
 * jumlah hari yang dipakai Posting Parameter (eq) dan Realisasi Bagi Hasil. Posting Parameter mengisinya otomatis dari
 * DATEDIFF(sd, dari) periode Param Financial terbaru; di sini bisa diubah manual atau diambil lagi dari periode itu.
 * Dibuat 2026-11-04 atas perintah user.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
// menu ada di fund; juga ditautkan dari halaman Posting level admin
if (!in_array(isset($_SESSION['level']) ? $_SESSION['level'] : '', array('fund', 'admin'), true)) {
    header("location:../../index.html");
    exit;
}
require_once __DIR__ . '/../../inc/hari_dep.php';
include_once __DIR__ . '/../../function.php';

$userid = (string) $_SESSION['userid'];
$periode = hari_dep_periode_terbaru();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pakai_periode'])) {
        $n = $periode ? (int) $periode['hari'] : 0;
        $ok = $n > 0 && hari_dep_simpan($n, 'periode', $userid);
        $_SESSION['pesan_hari_dep'] = $ok ? array('success', "Jumlah hari diubah menjadi $n (periode " . $periode['code_param'] . ').')
            : array('danger', 'Periode Param Financial terbaru tidak punya jumlah hari yang valid.');
    } else {
        $n = isset($_POST['jumlah_hari']) ? trim((string) $_POST['jumlah_hari']) : '';
        $ok = ctype_digit($n) && hari_dep_simpan((int) $n, 'manual', $userid);
        $_SESSION['pesan_hari_dep'] = $ok ? array('success', "Jumlah hari diubah menjadi $n.")
            : array('danger', 'Jumlah hari harus angka 1 sampai 366.');
    }
    header('Location: view_hari_dep.php');
    exit;
}
$pesan = isset($_SESSION['pesan_hari_dep']) ? $_SESSION['pesan_hari_dep'] : null;
unset($_SESSION['pesan_hari_dep']);
$baris = hari_dep_baris();

halaman_awal('Jumlah Hari Bagi Hasil');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni mw-100">
  <div class="card-header">Jumlah Hari Bagi Hasil Deposito</div>
  <div class="card-body">
    <p class="small text-muted">
      Dipakai Posting Parameter untuk menghitung eq (eq = porsi margin &times; 365 / hari) dan Realisasi Bagi Hasil
      (bagi hasil = nominal &times; eq &times; hari / 365). Posting Parameter mengisinya otomatis dari periode Param Financial terbaru.
    </p>
    <dl class="row mb-4">
      <dt class="col-sm-3">Jumlah hari berlaku</dt>
      <dd class="col-sm-9">
        <?php if ($baris): ?>
        <span class="fs-5 fw-semibold"><?php echo (int) $baris['jumlah_hari']; ?></span> hari
        <span class="small text-muted">(<?php echo h($baris['sumber']); ?>, <?php echo h($baris['tgl_update']); ?>, <?php echo h($baris['userid']); ?>)</span>
        <?php else: ?>
        <span class="text-danger">Belum diisi</span> <span class="small text-muted">- Realisasi Bagi Hasil sementara memakai <?php echo HARI_DEP_CADANGAN; ?> hari.</span>
        <?php endif; ?>
      </dd>
      <dt class="col-sm-3">Param Financial terbaru</dt>
      <dd class="col-sm-9">
        <?php if ($periode): ?>
        <?php echo h($periode['code_param']); ?>: <?php echo h(tgl_indo($periode['dari'])); ?> s/d <?php echo h(tgl_indo($periode['sd'])); ?>
        = <strong><?php echo (int) $periode['hari']; ?></strong> hari
        <?php else: ?>
        <span class="text-muted">Belum ada data Param Financial.</span>
        <?php endif; ?>
      </dd>
    </dl>
    <form method="post" action="view_hari_dep.php" class="row g-2 align-items-end">
      <div class="col-sm-3">
        <label class="form-label" for="jumlah_hari">Ubah jumlah hari</label>
        <input type="number" name="jumlah_hari" id="jumlah_hari" min="1" max="366" required class="form-control"
               value="<?php echo h($baris ? $baris['jumlah_hari'] : ($periode ? $periode['hari'] : HARI_DEP_CADANGAN)); ?>">
      </div>
      <div class="col-sm-9 d-flex gap-2">
        <button type="submit" class="btn btn-ni" data-konfirmasi="Simpan jumlah hari bagi hasil?">Simpan</button>
      </div>
    </form>
    <?php if ($periode && (int) $periode['hari'] > 0): ?>
    <form method="post" action="view_hari_dep.php" class="mt-2">
      <button type="submit" name="pakai_periode" value="1" class="btn btn-outline-secondary btn-sm"
              data-konfirmasi="Pakai <?php echo (int) $periode['hari']; ?> hari dari periode <?php echo h($periode['code_param']); ?>?">
        Pakai dari Param Financial terbaru (<?php echo (int) $periode['hari']; ?> hari)
      </button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
