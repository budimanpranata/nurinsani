<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Report > Nomonative > Daily Disbursement (menu kp, ho, lead): daftar pinjaman (loan) yang cair pada satu tanggal
 * (BUSS_DATE) di semua unit. Tanpa pencarian: tanggal hari ini (versi lama).
 * Dimigrasi 2026-11-03 (Bootstrap 5 + prepared statement). Perubahan atas perintah user:
 *   - pencarian Tanggal kini berfungsi (versi lama memeriksa variabel yang tidak pernah diisi sehingga selalu muncul
 *     "isi Tanggal"); hasilnya pinjaman cair pada tanggal itu, semua unit, kolom sama dengan daftar hari ini
 *   - tanggal dicocokkan satu hari penuh (BUSS_DATE berisi jam; versi lama membandingkan dengan tanggal tanpa jam
 *     sehingga daftar hampir selalu kosong); ditambah baris Total plafond
 *   - join ke tabel prod (nama produk) dibuang karena tabel itu tidak ada; kolom Deal Type menampilkan kode Deal_type
 *   - kolom Detail dibuang: link ke ctk_dok.php (cetak dokumen pensiunan, membaca ?nopen) dengan nilai kosong, tidak
 *     pernah menghasilkan dokumen
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
//cek level user
if (!in_array($_SESSION['level'], array('unit', 'lead', 'kp', 'ho'))) {
    header("location:index.html");
    exit;
}
include "indo_tgl.php";

$pesan = null;
$tanggal = date('Y-m-d');
$tgl_cari = isset($_POST['tgl_cari']) ? trim((string) $_POST['tgl_cari']) : '';
if (isset($_POST['search'])) {
    $d = DateTime::createFromFormat('!Y-m-d', $tgl_cari);
    if ($tgl_cari === '' || !$d || $d->format('Y-m-d') !== $tgl_cari) {
        $pesan = 'Maaf, isi Tanggal Transaksi yang benar!';
    } else {
        $tanggal = $tgl_cari;
    }
}

$baris = db_semua("select unit,no_anggota,Deal_type,nama_kel,Cust_Short_name,loan.cif,Plafond,Tenor from loan
 inner join kelompok on kelompok.code_kel = loan.code_kel
 where BUSS_DATE >= ? and BUSS_DATE < ? + interval 1 day", 'ss', $tanggal, $tanggal);
if ($pesan === null && isset($_POST['search']) && !$baris) {
    $pesan = 'Maaf, data tidak ditemukan!';
}

halaman_awal('Daily Disbursement');
?>
<div class="card card-ni">
  <div class="card-header">Cari Data Disbursement</div>
  <div class="card-body">
    <form method="post" action="view_disburse.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-3">
        <label for="tgl_cari" class="col-sm-4 col-form-label">Tanggal Pencarian</label>
        <div class="col-sm-8">
          <input type="date" name="tgl_cari" id="tgl_cari" class="form-control form-control-sm" value="<?php echo h($tgl_cari); ?>">
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="Search" class="btn btn-ni btn-sm px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<?php if ($pesan !== null): ?>
<div class="alert alert-warning mt-3"><?php echo h($pesan); ?></div>
<?php endif; ?>

<div class="card card-ni mw-100 mt-3">
  <div class="card-header">Disbursement <?php echo h(tgl_indo($tanggal)); ?> (<?php echo count($baris); ?> rekening)</div>
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <thead>
        <tr>
          <th>Unit</th><th>NOREK</th><th>Deal Type</th><th>Nama</th><th>Nama Kelompok</th><th>CIF</th>
          <th class="text-end">Plafond</th><th class="text-end">Tenor</th>
        </tr>
      </thead>
      <tbody>
      <?php $total = 0; foreach ($baris as $r): $total += compat_num($r['Plafond']); ?>
        <tr>
          <td><?php echo h($r['unit']); ?></td>
          <td><?php echo h($r['no_anggota']); ?></td>
          <td><?php echo h($r['Deal_type']); ?></td>
          <td><?php echo h($r['Cust_Short_name']); ?></td>
          <td><?php echo h($r['nama_kel']); ?></td>
          <td><?php echo h($r['cif']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['Plafond'])); ?></td>
          <td class="text-end"><?php echo h($r['Tenor']); ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$baris): ?>
        <tr><td colspan="8" class="text-center text-muted">Tidak ada pencairan pada tanggal ini</td></tr>
      <?php else: ?>
        <tr class="fw-bold">
          <td colspan="6" class="text-end">Total</td>
          <td class="text-end"><?php echo number_format($total); ?></td>
          <td></td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
halaman_akhir();
