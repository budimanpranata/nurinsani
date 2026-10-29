<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Cetak Dokumen > Kartu Angsuran: cari CIF (perorangan) atau kode kelompok, lalu cetak kartu angsuran
 * (cetak_kartu_angsuran.php). Tanpa pencarian: daftar kelompok berstatus CETAK (jenis 03).
 * Perilaku sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
unset($_SESSION['pilih']);
$userid = $_SESSION['userid'];
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}

$kode_rekening = isset($_POST['kode_rekening']) ? (string) $_POST['kode_rekening'] : '';
$pilih = isset($_POST['select']) ? (string) $_POST['select'] : '';
$r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
$branch = $r ? (string) $r['kode_branch'] : '';
$cari = !empty($_POST['search']);
$baris = array();

if ($cari) {
    if (empty($kode_rekening)) {
        echo "<script>alert('Maaf,Kode kelompok atau CIF salah');</script>";
        echo "<script language='javascript'>document.location.href='kartu_angsuran_kel.php'</script>";
        exit;
    }
    if ($pilih == '01') {
        $baris = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(bulat) AS tagihan, unit, kelompok.nama_kel, loan.cao, maturity_date
            FROM loan LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
            WHERE loan.cif = ? AND loan.unit = ? AND os > 0 GROUP BY loan.cif", 'ss', $kode_rekening, $branch);
    } else {
        $baris = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(bulat) AS tagihan, unit, kelompok.nama_kel, loan.cao, maturity_date
            FROM loan LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
            WHERE loan.code_kel = ? AND loan.unit = ? AND os > 0 GROUP BY unit", 'ss', $kode_rekening, $branch);
    }
    if (count($baris) == 0) {
        // versi lama menampilkan variabel $nopen yang tidak pernah diisi, sehingga nama kelompok kosong
        echo "<script>alert ('Maaf...,Kelompok  tidak ditemukan atau sudah lunas!!!'); </script>";
        echo "<script language='javascript'>document.location.href='kartu_angsuran_kel.php' </script>";
        exit;
    }
} else {
    $baris = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(bulat) AS tagihan, unit, kelompok.nama_kel, loan.cao, maturity_date,
            loan.code_kel
        FROM loan LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
        WHERE loan.status = 'CETAK' AND loan.unit = ? AND os > 0 GROUP BY loan.code_kel", 's', $branch);
}

halaman_awal('Cetak Kartu Angsuran');
?>
<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="kartu_angsuran_kel.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="select" class="col-sm-4 col-form-label">Jenis Pencarian</label>
        <div class="col-sm-8">
          <select name="select" id="select" class="form-select form-select-sm">
            <option value="Pilih Salah satu">Pilih Salah satu</option>
            <option value="01">Perorangan</option>
            <option value="02">Kelompok</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="kode" class="col-sm-4 col-form-label">Kode Kelompok/cif</label>
        <div class="col-sm-8">
          <div class="row g-1">
            <div class="col-5 autocomplete">
              <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm"
                     data-autocomplete="ajax/cari_kartu_angsuran.php" data-isi-nilai="CIF" data-isi-label="label"
                     data-isi-target='{"#country":"CUST_SHORT_NAME"}'>
            </div>
            <div class="col-7">
              <input type="text" name="nama_rekening" id="country" class="form-control form-control-sm" disabled aria-label="Nama">
            </div>
          </div>
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="Cetak" class="btn btn-ni btn-sm px-4">Cetak</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Unit</th>
        <th>kelompok</th>
        <th class="text-end">Total NoA</th>
        <th>Nama AO</th>
        <th class="text-center">Cetak</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($baris as $data): ?>
      <tr>
        <td><?php echo h($data['unit']); ?></td>
        <td><?php echo h($data['nama_kel']); ?></td>
        <td class="text-end"><?php echo h($data['noa']); ?></td>
        <td><?php echo h($data['cao']); ?></td>
        <td class="text-center">
          <?php if ($cari): ?>
            <a href="cetak_kartu_angsuran.php?code_kel=<?php echo urlencode($kode_rekening); ?>&amp;jenis=<?php echo urlencode($pilih); ?>"
               class="btn btn-ni btn-sm" data-konfirmasi="Anda akan melakukan Cetak Buku Angsuran ?">Cetak</a>
          <?php else: ?>
            <a href="cetak_kartu_angsuran.php?code_kel=<?php echo urlencode($data['code_kel']); ?>&amp;jenis=03"
               class="btn btn-ni btn-sm" data-konfirmasi="Anda akan melakukan Realsasi Angsuran ?">cetak</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
