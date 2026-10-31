<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Transaksi > Setoran 5 Persen langkah 1: cari kelompok (calon_loan berstatus MURAB, os > 0) di unit user,
 * lalu pilih anggota di yra_list_lima_persen.php. Perilaku dan pesan sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
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
        $pesan[] = "<script>alert('Maaf,.Mohon Nomor Kelompok Yang Benar !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='realisasi_wakalah.php'</script>";
    } else {
        $hasil = db_semua("select count(no_anggota)as noa,unit,kelompok.nama_kel,calon_loan.cao,tgl_wakalah from calon_loan
            left join kelompok on kelompok.code_kel = calon_loan.code_kel
            where calon_loan.code_kel =? AND calon_loan.unit=? AND os > 0 and status_app ='MURAB'
            group by unit", 'ss', $kelompok, (string) $branch);
    }
    // versi lama juga memeriksa hasil saat kelompok kosong, sehingga pesan kedua ini ikut tampil
    if (!$hasil) {
        // variabel $nopen di pesan versi lama tidak pernah diisi
        $pesan[] = "<script>alert ('Maaf...,Kelompok  sudah dilakukan realisasi!!!'); </script>";
        $pesan[] = "<script language='javascript'>document.location.href='simpanan_lima_persen.php' </script>";
    }
}

halaman_awal('Realisasi Simpanan 5%');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="simpanan_lima_persen.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Nomor Kelompok</label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="kelompok" id="kode" class="form-control form-control-sm" placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel" data-isi-label="code_kel,nama_kel"
                 data-isi-target='{"#country":"nama_kel"}'>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="country" class="col-sm-4 col-form-label">Nama Kelompok</label>
        <div class="col-sm-8"><input type="text" name="nama" id="country" class="form-control form-control-sm"></div>
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
        <th>Nama Kelompok</th>
        <th class="text-end">Total NOA</th>
        <th>Nama AO</th>
        <th>Tanggal Jatuh Tempo</th>
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
        <td><?php echo h($data['tgl_wakalah']); ?></td>
        <td class="text-center">
          <a href="yra_list_lima_persen.php?code_kel=<?php echo urlencode($kelompok); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Apakah adan Yakin akan melakukan Realisasis Simpanan ?">Detail</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
