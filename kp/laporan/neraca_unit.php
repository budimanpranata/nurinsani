<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Report > Akuntasi > Neraca > Neraca Unit: total sisa debet/kredit per posisi untuk satu unit dan tanggal awal
 * periode, dari tabel_master_konsol (bila kosong: tabel_master). Detail per posisi di rekap_neraca_unit.php; tanggal dan
 * unit disimpan di sesi tgl_caris/units (dipakai rekap_neraca_unit.php dan neraca_excel.php).
 * Dimigrasi 2026-11-03: hanya tampilan (Bootstrap 5) dan query (prepared statement); logika sama dengan versi lama, kecuali
 * (perintah user 2026-11-03): kolom Total Debet kini sum(sisa_debet) (versi lama membaca kunci 'debets' sehingga selalu 0).
 * Dibuang: var_dump($unit) (keluaran debug di setiap baris) dan skrip autosuggest/formatCurrency yang tidak dipakai.
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
$unit = isset($_POST['unit']) ? (string) $_POST['unit'] : '';
$pesan = array();
$hasil = array();
if (isset($_POST['search'])) {
    if (empty($buss_date1)) {
        $pesan[] = "<script>alert('Maaf,.isi Tanggal Pencarian');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='neraca_unit.php'</script>";
    } else {
        $_SESSION['tgl_caris'] = $buss_date1;
        $_SESSION['units'] = $unit;
        $hasil = db_semua("Select unit,sum(sisa_debet) as debet,sum(sisa_kredit) as kredit,posisi from tabel_master_konsol
            where tanggal_awal = ? and unit =? group By posisi", 'ss', $buss_date1, $unit);
        if (!$hasil) {
            $hasil = db_semua("Select unit,sum(sisa_debet) as debet,sum(sisa_kredit) as kredit,posisi from tabel_master
                where tanggal_awal = ? and unit =? group By posisi", 'ss', $buss_date1, $unit);
        }
        if (!$hasil) {
            $pesan[] = "<script>alert('Maaf,.data tidak ditemukan');</script>";
            $pesan[] = "<script language='javascript'>document.location.href='neraca_unit.php'</script>";
        }
    }
}
$cabang = db_semua("select kode_branch, unit from branch");

halaman_awal('Neraca Unit');
?>
<div class="card card-ni">
  <div class="card-header">Pencarian Berdasarkan Tanggal</div>
  <div class="card-body">
    <form method="post" action="neraca_unit.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="buss_date1" class="col-sm-4 col-form-label">Tanggal Awal</label>
        <div class="col-sm-8">
          <input type="date" name="buss_date1" id="buss_date1" class="form-control form-control-sm" value="<?php echo h($buss_date1); ?>">
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="unit" class="col-sm-4 col-form-label">Unit</label>
        <div class="col-sm-8">
          <select name="unit" id="unit" class="form-select form-select-sm">
            <option>Pilih Unit</option>
            <?php foreach ($cabang as $c): ?>
            <option value="<?php echo h($c['kode_branch']); ?>"<?php echo $unit === (string) $c['kode_branch'] ? ' selected' : ''; ?>><?php echo h($c['unit'] . ' - ' . $c['kode_branch']); ?></option>
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
        <th class="text-end">Total Debet</th>
        <th class="text-end">Total Kredit</th>
        <th class="text-center">Detail</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td><?php echo h($unit); ?></td>
        <td><?php echo h($buss_date1); ?></td>
        <?php // versi lama membaca $data['debets'] (tidak ada) sehingga selalu 0; diperbaiki atas perintah user 2026-11-03 ?>
        <td class="text-end"><?php echo number_format(compat_num($data['debet'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['kredit'])); ?></td>
        <td class="text-center">
          <a href="rekap_neraca_unit.php?posisi=<?php echo h(urlencode((string) $data['posisi'])); ?>" class="btn btn-outline-primary btn-sm" data-konfirmasi="Apakah adan Yakin?"><?php echo h($data['posisi']); ?></a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
