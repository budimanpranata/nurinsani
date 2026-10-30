<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Realisasi > Murabahah: cari kelompok berstatus MURAB di unit user pada tanggal akad tertentu,
 * lalu pilih anggota (yra_list.php). Perilaku sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
$userid = $_SESSION['userid'];

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}

$kelompok = isset($_POST['kelompok']) ? (string) $_POST['kelompok'] : '';
$nama = isset($_POST['nama']) ? (string) $_POST['nama'] : '';
$tanggal = isset($_POST['tanggal']) ? (string) $_POST['tanggal'] : '';
$hasil = array();
$pesan = array(); // script alert/pindah, dicetak di halaman seperti versi lama

if (!empty($_POST['search'])) {
    if ($kelompok === '') {
        $pesan[] = '<script>alert(' . json_encode('Maaf,.Mohon Nomor Kelompok Yang Benar !!!') . ');</script>';
        $pesan[] = "<script language='javascript'>document.location.href='simulasi.php'</script>";
    } else {
        $r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
        $branch = $r ? (string) $r['kode_branch'] : '';
        $hasil = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(Plafond) AS pla, unit, kelompok.nama_kel, calon_loan.cao, tgl_wakalah
            FROM calon_loan
            INNER JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
            WHERE calon_loan.code_kel = ? AND calon_loan.unit = ? AND status_app = 'MURAB' AND tgl_akad = ?
            GROUP BY unit", 'sss', $kelompok, $branch, $tanggal);
    }
    if (!$hasil) {
        // versi lama tidak pindah halaman di sini (redirect dikomentari), halaman tetap tampil
        $pesan[] = '<script>alert(' . json_encode('Maaf...,Kelompok ' . $kelompok . ' tidak dalam rencana Murabahah!!!') . ');</script>';
    }
}

halaman_awal('Realisasi Murabahah');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="realisasi.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Nomor Kelompok</label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="kelompok" id="kode" class="form-control form-control-sm" value="<?php echo h($kelompok); ?>"
                 placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel"
                 data-isi-label="code_kel,nama_kel" data-isi-target='{"#nama":"nama_kel"}'>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="nama" class="col-sm-4 col-form-label">Nama Kelompok</label>
        <div class="col-sm-8">
          <input type="text" name="nama" id="nama" class="form-control form-control-sm" value="<?php echo h($nama); ?>">
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="tanggal" class="col-sm-4 col-form-label">Tanggal Murabahah</label>
        <div class="col-sm-8">
          <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="<?php echo h($tanggal); ?>">
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
        <th class="text-end">Pembiayaan</th>
        <th>Nama AO</th>
        <th>Jatuh Tempo</th>
        <th class="text-center">Detail</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td><?php echo h($data['unit']); ?></td>
        <td><?php echo h($data['nama_kel']); ?></td>
        <td class="text-end"><?php echo h($data['noa']); ?></td>
        <td class="text-end"><?php echo h($data['pla']); ?></td>
        <td><?php echo h($data['cao']); ?></td>
        <td><?php echo h($data['tgl_wakalah']); ?></td>
        <td class="text-center">
          <a href="yra_list.php?code_kel=<?php echo urlencode($kelompok); ?>&amp;tanggal=<?php echo urlencode($tanggal); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Apakah anda Yakin ?">Detail</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
echo implode("\n", $pesan);
halaman_akhir();
