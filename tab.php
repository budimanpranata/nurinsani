<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Realisasi > Tagihan Kelompok.
 *   - Tanpa pencarian: daftar tagihan_cs berstatus PENDING untuk tanggal sistem di unit user.
 *   - Dengan pencarian: total tagihan kelompok (loan dengan OS > 0).
 * Lanjut ke yrs_list.php untuk memilih anggota. Perilaku sama dengan versi lama.
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
$nama = isset($_POST['nama']) ? (string) $_POST['nama'] : '';

/** Tampilkan alert lalu pindah ke $tujuan (sama seperti versi lama). */
function alert_pindah($pesan, $tujuan)
{
    echo '<script>alert(' . json_encode($pesan) . ');</script>';
    echo "<script language='javascript'>document.location.href='$tujuan'</script>";
    exit;
}

$r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
$branch = $r ? (string) $r['kode_branch'] : '';

$baris = array(); // unit, nama_kel, noa, ao, tagihan, tanggal, code_kel, label
if (!empty($_POST['search'])) {
    if ($kelompok === '') {
        alert_pindah('Maaf,.Mohon Nomor Kelompok Yang Benar !!!', 'tab.php');
    }
    $hasil = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(bulat) AS tagihan, unit, kelompok.nama_kel, loan.cao, maturity_date
        FROM loan
        INNER JOIN kelompok ON kelompok.code_kel = loan.code_kel
        WHERE loan.code_kel = ? AND loan.unit = ? AND os > 0
        GROUP BY unit", 'ss', $kelompok, $branch);
    if (!$hasil) {
        // versi lama menampilkan variabel $nopen yang tidak pernah diisi
        alert_pindah('Maaf...,Kelompok  tidak ditemukan atau sudah lunas!!!', 'tab.php');
    }
    foreach ($hasil as $d) {
        $baris[] = array($d['unit'], $d['nama_kel'], $d['noa'], $d['cao'], $d['tagihan'], $d['maturity_date'], $kelompok, 'Realisasi');
    }
} else {
    $hasil = db_semua("SELECT COUNT(norek) AS noa, SUM(angsuran) AS tagihan, tagihan_cs.code_kel, tagihan_cs.unit, kelompok.nama_kel, nama_ao, tgl_tagih, status_realisasi
        FROM tagihan_cs
        LEFT JOIN kelompok ON kelompok.code_kel = tagihan_cs.code_kel
        LEFT JOIN ao ON ao.cao = tagihan_cs.cao
        WHERE tagihan_cs.unit = ? AND tgl_tagih = ? AND status_realisasi = 'PENDING'
        GROUP BY tagihan_cs.code_kel", 'ss', $branch, (string) $tgl_system);
    foreach ($hasil as $d) {
        $status = $d['status_realisasi'] == 'PENDING' ? 'Pending' : 'Done';
        $baris[] = array($d['unit'], $d['nama_kel'], $d['noa'], $d['nama_ao'], $d['tagihan'], $d['tgl_tagih'], $d['code_kel'], $status);
    }
}

halaman_awal('Realisasi Tagihan Kelompok');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="tab.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Nomor Kelompok</label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="kelompok" id="kode" class="form-control form-control-sm" value="<?php echo h($kelompok); ?>"
                 placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel"
                 data-isi-label="code_kel,nama_kel" data-isi-target='{"#nama":"nama_kel"}'>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="nama" class="col-sm-4 col-form-label">Nama Kelompok</label>
        <div class="col-sm-8">
          <input type="text" name="nama" id="nama" class="form-control form-control-sm" value="<?php echo h($nama); ?>">
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
        <th class="text-end">Total NoA</th>
        <th>Nama AO</th>
        <th class="text-end">Total Setor</th>
        <th>Jatpo</th>
        <th class="text-center">Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($baris as $b): ?>
      <tr>
        <td><?php echo h($b[0]); ?></td>
        <td><?php echo h($b[1]); ?></td>
        <td class="text-end"><?php echo h($b[2]); ?></td>
        <td><?php echo h($b[3]); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b[4])); ?></td>
        <td><?php echo h(tgl_indo($b[5])); ?></td>
        <td class="text-center">
          <a href="yrs_list.php?code_kel=<?php echo urlencode($b[6]); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Anda akan melakukan Realsasi Angsuran ?"><?php echo h($b[7]); ?></a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
