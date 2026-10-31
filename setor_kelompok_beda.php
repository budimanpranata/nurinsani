<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Transaksi > Setoran Beda hari langkah 1: cari kelompok (loan os > 0) di unit user yang tidak sedang PSBB,
 * lalu proses setoran per anggota di setoran_list_beda.php. Perilaku dan pesan sama dengan versi lama
 * Perbaikan 2026-10-31 (perintah user): pesan gagal kembali ke halaman ini (versi lama ke setor_kelompok.php).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
include "indo_tgl.php";
$tgl_system = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['stgl_system'])) {
    echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
    header("location:param_tgl.php");
    exit;
}
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

$kelompok = isset($_POST['kelompok']) ? (string) $_POST['kelompok'] : '';
$r = db_satu("select kode_branch from user where userid =?", 's', (string) $userid);
$branch = $r ? $r['kode_branch'] : null;

$pesan = array();
$hasil = array();
if (!empty($_POST['search'])) {
    if (empty($kelompok)) {
        $pesan[] = "<script>alert('Maaf,isi Kode Kelompok Yang Benar!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='setor_kelompok_beda.php'</script>";
    } else {
        $hasil = db_semua("select count(no_anggota)as noa,sum(bulat) as tagihan,unit,kelompok.nama_kel,loan.cao,next_schedule from loan
            inner join kelompok on kelompok.code_kel = loan.code_kel
            where loan.code_kel =? AND loan.unit=? and os > 0 and unit not in (select unit from psbb)
            group by loan.code_kel order by loan.code_kel", 'ss', $kelompok, (string) $branch);
        if (!$hasil) {
            // variabel $nopen di pesan versi lama tidak pernah diisi
            $pesan[] = "<script>alert ('Maaf,Kelompok  tidak ditemukan atau dalam masa PSBB'); </script>";
            $pesan[] = "<script language='javascript'>document.location.href='setor_kelompok_beda.php' </script>";
        }
    }
}

halaman_awal('Setoran Kelompok Beda Hari');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="setor_kelompok_beda.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-3">
        <label for="kode" class="col-sm-4 col-form-label">Kelompok</label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="kelompok" id="kode" class="form-control form-control-sm" placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel" data-isi-label="code_kel,nama_kel">
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="Search" class="btn btn-ni btn-sm px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<?php echo implode("\n", $pesan); ?>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Unit</th>
        <th>Kelompok</th>
        <th class="text-end">NOA</th>
        <th>Nama AO</th>
        <th class="text-end">Setoran</th>
        <th>Tgl</th>
        <th class="text-center">Detail</th>
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
          <a href="setoran_list_beda.php?code_kel=<?php echo urlencode($kelompok); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Anda akan melakukan Setoran Kelompok ?">Proses</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
