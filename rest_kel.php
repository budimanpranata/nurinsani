<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Restrukturisasi Data > Restrukturisasi Jatpo.
 * Cari kelompok (loan dengan OS > 0) di unit user; Cetak (rest_cetak.php) atau proses per anggota di rest_list.php.
 * Perilaku sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
include "indo_tgl.php";
$tgl_system = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
//cek tanggal sistem
if (!isset($_SESSION['stgl_system'])) {
    echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
    header("location:param_tgl.php");
    exit;
}
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

$kelompok = isset($_POST['kelompok']) ? (string) $_POST['kelompok'] : '';
$hasil = array();

/** Tampilkan alert lalu pindah ke $tujuan (sama seperti versi lama). */
function alert_pindah($pesan, $tujuan)
{
    echo '<script>alert(' . json_encode($pesan) . ');</script>';
    echo "<script language='javascript'>document.location.href='$tujuan'</script>";
    exit;
}

if (!empty($_POST['search'])) {
    if ($kelompok === '') {
        alert_pindah('Maaf,isi Kode Kelompok Yang Benar!', 'rest_kel.php');
    }
    $r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
    $branch = $r ? (string) $r['kode_branch'] : '';
    $hasil = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(bulat) AS tagihan, unit, kelompok.nama_kel, loan.cao, maturity_date AS next_schedule
        FROM loan
        INNER JOIN kelompok ON kelompok.code_kel = loan.code_kel
        WHERE loan.code_kel = ? AND loan.unit = ? AND os > 0
        GROUP BY unit ORDER BY loan.cao", 'ss', $kelompok, $branch);
    if (!$hasil) {
        // versi lama menampilkan variabel $nopen yang tidak pernah diisi
        alert_pindah('Maaf,Kelompok  tidak ditemukan', 'rest_kel.php');
    }
}

halaman_awal('Transaksi Restrukturisasi Jatpo');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="rest_kel.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-3">
        <label for="kode" class="col-sm-4 col-form-label">Kelompok</label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="kelompok" id="kode" class="form-control form-control-sm" value="<?php echo h($kelompok); ?>"
                 placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel" data-isi-label="code_kel,nama_kel">
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="Search" class="btn btn-ni btn-sm px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Unit</th>
        <th>Kelompok</th>
        <th class="text-end">Noa</th>
        <th>Nama AO</th>
        <th class="text-end">Setoran</th>
        <th>Jatpo</th>
        <th class="text-center">Cetak</th>
        <th class="text-center">Disburse</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td><?php echo h($data['unit']); ?></td>
        <td><?php echo h($data['nama_kel']); ?></td>
        <td class="text-end"><?php echo h($data['noa']); ?></td>
        <td><?php echo h($data['cao']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['tagihan'])); ?></td>
        <td><?php echo h(tgl_indo($data['next_schedule'])); ?></td>
        <td class="text-center">
          <a href="rest_cetak.php?code_kel=<?php echo urlencode($kelompok); ?>" target="_blank" class="btn btn-outline-secondary btn-sm"
             data-konfirmasi="Anda akan melakukan Cetak Restrukturisasi Kelompok ?">Cetak</a>
        </td>
        <td class="text-center">
          <a href="rest_list.php?code_kel=<?php echo urlencode($kelompok); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Anda akan melakukan Restrukturisasi Kelompok ?">Proses</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
