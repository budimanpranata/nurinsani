<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu kp > Posting Ulang: cari total debet/kredit tabel_transaksi unit terpilih (pilihan hanya kantor pusat 1010) pada
 * rentang tanggal, lalu Detail ke posting_neraca_bulan.php (proses posting ulang mutasi neraca 1010). Rentang dan unit
 * disimpan di sesi tgl_awal_pos / tgl_akhir_pos / unit_lead (dipakai posting_neraca_bulan.php).
 * Dimigrasi 2026-11-03: hanya tampilan (Bootstrap 5) dan query (prepared statement); logika sama dengan versi lama.
 * Tanggal kosong kini kembali ke halaman ini (versi lama ke mutasi_kas.php yang tidak ada di folder kp). Dibuang: daftar
 * kas 1120000 tanggal sistem saat belum mencari (unit kosong, selalu tanpa baris), skrip autosuggest/formatCurrency/#select
 * yang tidak dipakai.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_level('kp');
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

$buss_date1 = isset($_POST['buss_date1']) ? trim((string) $_POST['buss_date1']) : '';
$buss_date2 = isset($_POST['buss_date2']) ? trim((string) $_POST['buss_date2']) : '';
$unit = isset($_POST['unit']) ? (string) $_POST['unit'] : '';
$pesan = array();
$hasil = array();
if (isset($_POST['search'])) {
    if (empty($buss_date1) || empty($buss_date2)) {
        $pesan[] = "<script>alert('Maaf,.isi Tanggal Pencarian  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='posting_neraca.php'</script>";
    } else {
        $_SESSION['tgl_awal_pos'] = $buss_date1;
        $_SESSION['tgl_akhir_pos'] = $buss_date2;
        $_SESSION['unit_lead'] = $unit;
        $hasil = db_semua("Select sum(debet) as debet,sum(kredit) as kredit from tabel_transaksi where tanggal_transaksi between ? and ? and unit=?",
            'sss', $buss_date1, $buss_date2, $unit);
    }
}
$cabang = db_semua("select kode_branch, unit from branch where kode_branch='1010'");

halaman_awal('Posting Neraca');
?>
<div class="card card-ni">
  <div class="card-header">Pencarian Berdasarkan Tanggal</div>
  <div class="card-body">
    <form method="post" action="posting_neraca.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="buss_date1" class="col-sm-4 col-form-label">Tanggal Awal</label>
        <div class="col-sm-8"><input type="date" name="buss_date1" id="buss_date1" class="form-control form-control-sm" value="<?php echo h($buss_date1); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="buss_date2" class="col-sm-4 col-form-label">Tanggal Akhir</label>
        <div class="col-sm-8"><input type="date" name="buss_date2" id="buss_date2" class="form-control form-control-sm" value="<?php echo h($buss_date2); ?>"></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="unit" class="col-sm-4 col-form-label">Unit</label>
        <div class="col-sm-8">
          <select name="unit" id="unit" class="form-select form-select-sm">
            <option value="">- Select Kode unit -</option>
            <?php foreach ($cabang as $c): ?>
            <option value="<?php echo h($c['kode_branch']); ?>"<?php echo $unit === (string) $c['kode_branch'] ? ' selected' : ''; ?>><?php echo h($c['unit']); ?></option>
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
        <th>Tgl Awal Transaksi</th>
        <th>Tgl Akhir Transaksi</th>
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
        <td><?php echo h($buss_date2); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['debet'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['kredit'])); ?></td>
        <td class="text-center">
          <a href="posting_neraca_bulan.php?unit=<?php echo h(urlencode($unit)); ?>" class="btn btn-outline-primary btn-sm" data-konfirmasi="Apakah adan Yakin akan melakukan Posting Neraca ?">Detail</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
