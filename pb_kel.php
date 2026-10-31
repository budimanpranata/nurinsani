<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Transaksi > PB PerKelompok langkah 1: pilih kelompok (loan os > 0) di unit user, jenis pemindahbukuan
 * (Debet/Kredit) dan jenis simpanan (pokok/wajib), lalu proses per anggota di pb_list.php.
 * Jenis PB dan simpanan disimpan di sesi (simpans/bakis) untuk realisasi_pb.php. Perilaku dan pesan sama dengan versi lama.
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
        $pesan[] = "<script>alert('Maaf,.Mohon Nomor Kelompok Yang Benar !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='pb_kel.php'</script>";
    } else {
        $hasil = db_semua("select count(no_anggota)as noa,sum(bulat) as tagihan,unit,kelompok.nama_kel,loan.cao,next_schedule from loan
            inner join kelompok on kelompok.code_kel = loan.code_kel
            where loan.code_kel =? AND loan.unit=? and os >0
            group by unit order by loan.cao", 'ss', $kelompok, (string) $branch);
        $_SESSION['simpans'] = isset($_POST['simpan']) ? $_POST['simpan'] : null;
        $_SESSION['bakis'] = isset($_POST['baki']) ? $_POST['baki'] : null;
        // versi lama mencetak var_dump() kedua nilai sesi ini (sisa debug); tidak ditampilkan lagi
        if (!$hasil) {
            // variabel $nopen di pesan versi lama tidak pernah diisi
            $pesan[] = "<script>alert ('Maaf,Kelompok  tidak ditemukan'); </script>";
            $pesan[] = "<script language='javascript'>document.location.href='pb_kel.php' </script>";
        }
    }
}

halaman_awal('PB Kelompok');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="pb_kel.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Kelompok</label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="kelompok" id="kode" class="form-control form-control-sm" placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel" data-isi-label="code_kel,nama_kel">
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="baki" class="col-sm-4 col-form-label">Jenis Pemindah Bukuan</label>
        <div class="col-sm-8">
          <select name="baki" id="baki" class="form-select form-select-sm">
            <option value="Debet">Debet</option>
            <option value="Kredit">Kredit</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="simpan" class="col-sm-4 col-form-label">Jenis Simpanan</label>
        <div class="col-sm-8">
          <select name="simpan" id="simpan" class="form-select form-select-sm">
            <option value="pokok">Pokok</option>
            <option value="wajib">Wajib</option>
          </select>
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
          <a href="pb_list.php?code_kel=<?php echo urlencode($kelompok); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Anda akan melakukan Pemindah bukuan Kelompok ?">Proses</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
