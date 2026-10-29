<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Cetak Dokumen > Cetak Lariswah: cari kelompok di unit user, pilih jenis akad, lalu cetak (cetak_lariswah1.php).
 * Perilaku sama dengan versi lama: pesan lewat alert lalu pindah halaman.
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
$jenis = isset($_POST['jenis']) ? (string) $_POST['jenis'] : '';
$hasil = array();

$daftar_jenis = array(
    'Pilih Salah satu' => 'Pilih Salah satu',
    'MUSYAROKAH' => 'Musyarakah',
    'MURAB' => 'Murabahah',
);

/** Tampilkan alert lalu pindah ke $tujuan (sama seperti versi lama). */
function alert_pindah($pesan, $tujuan)
{
    echo '<script>alert(' . json_encode($pesan) . ');</script>';
    echo "<script language='javascript'>document.location.href='$tujuan'</script>";
    exit;
}

if (!empty($_POST['search'])) {
    if ($kelompok === '') {
        // versi lama kembali ke murabahah.php
        // versi lama tidak berhenti di sini: lanjut ke pengecekan data sehingga alert kedua juga muncul
        echo '<script>alert(' . json_encode('Maaf,.Mohon Nomor Kelompok Yang Benar !!!') . ');</script>';
        echo "<script language='javascript'>document.location.href='murabahah.php'</script>";
        alert_pindah('Maaf...,Kelompok  tidak ditemukan!!!', 'lariswah_satu.php');
    }
    $r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
    $branch = $r ? (string) $r['kode_branch'] : '';
    $hasil = db_semua("SELECT COUNT(no_anggota) AS noa, unit, kelompok.nama_kel, calon_loan.cao, tgl_wakalah
        FROM calon_loan
        INNER JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
        WHERE calon_loan.code_kel = ? AND calon_loan.unit = ? AND status_usia <> 'YA'
        GROUP BY unit", 'ss', $kelompok, $branch);
    if (!$hasil) {
        // versi lama menampilkan variabel $nopen yang tidak pernah diisi, sehingga nama kelompok kosong
        alert_pindah('Maaf...,Kelompok  tidak ditemukan!!!', 'lariswah_satu.php');
    }
}

halaman_awal('Cetak Dokumen Lariswah');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="lariswah_satu.php" class="form-ni" autocomplete="off">
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
      <div class="row g-2 mb-2">
        <label for="jenis" class="col-sm-4 col-form-label">Jenis Akad</label>
        <div class="col-sm-8">
          <select name="jenis" id="jenis" class="form-select form-select-sm">
            <?php foreach ($daftar_jenis as $nilai => $label): ?>
            <option value="<?php echo h($nilai); ?>"<?php echo $jenis === $nilai ? ' selected' : ''; ?>><?php echo h($label); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="tanggal" class="col-sm-4 col-form-label">Tanggal Akad</label>
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
          <a href="cetak_lariswah1.php?code_kel=<?php echo urlencode($kelompok); ?>&amp;jenis=<?php echo urlencode($jenis); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="anda yakin akan Cetak Murabahah ?">Detail</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
