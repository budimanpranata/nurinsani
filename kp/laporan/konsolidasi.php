<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Report > Akuntasi > Konsolidasi > Konsolidasi: total saldo awal/akhir per posisi pada tanggal awal periode,
 * dari tabel_master_konsol (bila kosong: tabel_master_eoy, lalu tabel_master_hari). Link per posisi ke ../konsolidasi.php
 * (meneruskan ke laporan/neraca_konsol.php atau laporan/laba_konsol.php); tanggal dan jenis report disimpan di sesi
 * tgl_cari_konsols / jenis (dipakai halaman konsolidasi lanjutan).
 * Dimigrasi 2026-11-03: hanya tampilan (Bootstrap 5) dan query (prepared statement); logika sama dengan versi lama, termasuk:
 *   - sesi unit_konsols diisi variabel $unit yang tidak pernah diisi (selalu null)
 * Dibuang: skrip autosuggest/formatCurrency dan skrip #select yang tidak dipakai.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

$buss_date1 = isset($_POST['buss_date1']) ? trim((string) $_POST['buss_date1']) : '';
$jenis = isset($_POST['jenis']) ? (string) $_POST['jenis'] : '';
$pesan = array();
$hasil = array();
if (isset($_POST['search'])) {
    if (empty($buss_date1)) {
        $pesan[] = "<script>alert('Maaf,.isi Tanggal Pencarian  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='konsolidasi.php'</script>";
    } else {
        $_SESSION['tgl_cari_konsols'] = $buss_date1;
        $_SESSION['unit_konsols'] = null;
        $_SESSION['jenis'] = $jenis;
        foreach (array('tabel_master_konsol', 'tabel_master_eoy', 'tabel_master_hari') as $tabel) {
            $hasil = db_semua("Select unit,sum(saldo_awal) as debet,sum(saldo_akhir) as kredit,posisi from $tabel where tanggal_awal = ? group by posisi", 's', $buss_date1);
            if ($hasil) {
                break;
            }
        }
        if (!$hasil) {
            $pesan[] = "<script>alert('Maaf,.data tidak ditemukan');</script>";
            $pesan[] = "<script language='javascript'>document.location.href='konsolidasi.php'</script>";
        }
    }
}

halaman_awal('Neraca');
?>
<div class="card card-ni">
  <div class="card-header">Pencarian Berdasarkan Tanggal</div>
  <div class="card-body">
    <form method="post" action="konsolidasi.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="buss_date1" class="col-sm-4 col-form-label">Tanggal Awal</label>
        <div class="col-sm-8">
          <input type="date" name="buss_date1" id="buss_date1" class="form-control form-control-sm" value="<?php echo h($buss_date1); ?>">
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="jenis" class="col-sm-4 col-form-label">Jenis Report</label>
        <div class="col-sm-8">
          <select name="jenis" id="jenis" class="form-select form-select-sm">
            <option> pilih Report </option>
            <?php foreach (array('eom', 'eoy', 'current') as $j): ?>
            <option value="<?php echo $j; ?>"<?php echo $jenis === $j ? ' selected' : ''; ?>><?php echo $j; ?></option>
            <?php endforeach; ?>
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

<div class="table-responsive mt-3">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Unit</th>
        <th>Tgl Awal Periode</th>
        <th class="text-end">Total Saldo Awal</th>
        <th class="text-end">Total Saldo Akhir</th>
        <th class="text-center">Konsolidasi</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td>Konsolidasi</td>
        <td><?php echo h($buss_date1); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['debet'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['kredit'])); ?></td>
        <td class="text-center">
          <a href="../konsolidasi.php?consol=<?php echo h(urlencode((string) $data['posisi'])); ?>" class="btn btn-outline-primary btn-sm" data-konfirmasi="Apakah adan Yakin akan melakukan Posting Neraca ?"><?php echo h($data['posisi']); ?></a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
