<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu kp > Report > Nominative Pembiayaan (juga ada di menu ho, tetapi level ho ditolak seperti versi lama).
 *   Cari    : total NOA dan outstanding seluruh unit (CURRENT dari loan, EOM dari data_loan per bulan/tahun).
 *   Rekap   : export .xlsx bertahap dengan indikator progres (report/nominatif_kp_export.php, assets/app/export_xlsx.js),
 *             mengisi ulang loan_accrual seperti export_nomi_kp.php versi lama (permintaan user 2026-11-03, sama dengan menu unit).
 *   Accrual : accrual.php (posting jurnal accrual dari loan_accrual).
 * Pencarian dan isian sesi sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 * Dimigrasi 2026-11-03. Satu-satunya perbedaan: Status kosong kini kembali ke halaman ini (versi lama ke Kr.php,
 * halaman level unit, sehingga user kp mendapat "Anda bukan user").
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
$buss_date = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    die("Maaf Sesion anda habis,  Anda harus login lembali"); //jika belum login jangan lanjut..
}
//cek level user
if ($_SESSION['level'] != "kp") {
    die("Anda bukan user"); //jika bukan user jangan lanjut
}

$userid = $_SESSION['userid'];
$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit = $id_user['kode_branch'];
}

$bulan1 = isset($_POST['bulan']) ? (string) $_POST['bulan'] : '';
$tahun1 = isset($_POST['tahun']) ? (string) $_POST['tahun'] : '';
$status = isset($_POST['status']) ? (string) $_POST['status'] : '';

$pesan = array();
$hasil = array();
if (isset($_POST['search'])) {
    if (empty($status)) {
        $pesan[] = "<script>alert('Maaf,Isi Status Terlebih Dahulu  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='Kr_kp.php'</script>";
    } else {
        $_SESSION['bulan'] = $bulan1;
        $_SESSION['tahun'] = $tahun1;
        $_SESSION['status'] = $status;
        $_SESSION['unt'] = $unit;
    }
    if ($status == 'CURRENT') {
        $hasil = db_semua("Select COUNT(loan.cif) AS NOA,SUM(os) AS os, unit From loan");
    } else {
        $hasil = db_semua("Select buss_date,COUNT(data_loan.cif) AS NOA,SUM(os) AS os, unit From data_loan
            Where Month(data_loan.buss_date)=? AND Year(data_loan.buss_date)=?", 'ss', $bulan1, $tahun1);
    }
    if (!$hasil) {
        $pesan[] = "<script>alert('Maaf...data tidak ditemukan!!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='../report/Kr_kp.php' </script>";
    }
    foreach ($hasil as $i => $data) {
        $hasil[$i]['tgl'] = $status == 'CURRENT' ? $buss_date : $data['buss_date'];
        $_SESSION['buss_dates'] = $hasil[$i]['tgl'];
    }
}

$nama_bulan = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Des');
halaman_awal('Nominative Acrual');
?>
<div class="card card-ni">
  <div class="card-header">Criteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="Kr_kp.php" name="form" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="status" class="col-sm-4 col-form-label">Status Nominative</label>
        <div class="col-sm-8">
          <select name="status" id="status" class="form-select form-select-sm">
            <option value="">- Select Status -</option>
            <option value="CURRENT"<?php echo $status === 'CURRENT' ? ' selected' : ''; ?>>CURRENT</option>
            <option value="EOM"<?php echo $status === 'EOM' ? ' selected' : ''; ?>>EOM</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3" id="baris-periode">
        <label for="bulan" class="col-sm-4 col-form-label">Bulan dan Tahun</label>
        <div class="col-sm-4">
          <select name="bulan" id="bulan" class="form-select form-select-sm">
            <option value="">- Select Month -</option>
            <?php foreach ($nama_bulan as $no => $nm): ?>
            <option value="<?php echo $no; ?>"<?php echo $bulan1 === (string) $no ? ' selected' : ''; ?>><?php echo $nm; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4">
          <select name="tahun" id="tahun" class="form-select form-select-sm">
            <option>Tahun</option>
            <?php for ($th = (int) date('Y'); $th >= 2020; $th--): ?>
            <option value="<?php echo $th; ?>"<?php echo $tahun1 === (string) $th ? ' selected' : ''; ?>><?php echo $th; ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Cari Report" class="btn btn-ni btn-sm px-4">Cari Report</button>
      </div>
    </form>
  </div>
</div>

<?php echo implode("\n", $pesan); ?>

<div class="table-responsive mt-3">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th class="text-center">Bussdate</th>
        <th class="text-end">Total NOA</th>
        <th class="text-end">Total OS</th>
        <th class="text-center">Cetak</th>
        <th class="text-center">Export</th>
        <th class="text-center">Proses</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td class="text-center"><?php echo h($data['tgl']); ?></td>
        <td class="text-end"><?php echo h($data['NOA']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['os'])); ?></td>
        <?php // versi lama: link ke /cetak_tag.php dengan $tipe yang tidak pernah diisi (file tidak ada) ?>
        <td class="text-center"><a href="/cetak_tag.php?type=" class="btn btn-outline-secondary btn-sm" data-konfirmasi="Apakah Anda yakin?">Cetak</a></td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-success btn-sm" data-export-xlsx data-export-url="nominatif_kp_export.php" data-export-satuan="rekening">Rekap (.xlsx)</button>
        </td>
        <td class="text-center">
          <a href="accrual.php?type=<?php echo h(urlencode((string) $data['tgl'])); ?>" class="btn btn-outline-primary btn-sm" data-konfirmasi="Apakah Anda yakin?">Accrual</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modal-export" tabindex="-1" aria-labelledby="judul-export" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="judul-export">Export Rekap Nominative Acrual</h2>
      </div>
      <div class="modal-body">
        <div class="progress" role="progressbar" aria-label="Progres export" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="height: 1.25rem;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
        </div>
        <div class="small text-muted mt-2" data-export-teks aria-live="polite">Menyiapkan data…</div>
        <div data-export-ringkasan></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm d-none" data-bs-dismiss="modal" data-export-tutup>Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
// Bulan dan Tahun hanya dipakai untuk status EOM (maksud skrip versi lama, yang tidak jalan karena jQuery tidak dimuat)
(function () {
    var status = document.getElementById('status');
    var periode = document.getElementById('baris-periode');
    function atur() { periode.classList.toggle('d-none', status.value !== 'EOM'); }
    status.addEventListener('change', atur);
    atur();
})();
</script>
<?php
halaman_akhir(array('assets/app/export_xlsx.js'));
