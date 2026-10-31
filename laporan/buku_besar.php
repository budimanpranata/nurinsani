<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Report > Buku Besar.
 *   Cari     : simpan pilihan (rekening, bulan, tahun, jenis report, All data) di sesi, tampilkan saldo awal/akhir
 *              rekening dari tabel_master_konsol untuk bulan/tahun itu.
 *   Download : export .xlsx bertahap dengan indikator progres (laporan/buku_besar_export.php, assets/app/export_xlsx.js),
 *              pengganti excel_buku_besar.php (tabel HTML yang dinamai .xls).
 * Isian sesi dan tampilan saldo sama dengan versi lama; hanya tampilan, query (prepared statement), dan cara export yang berubah.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
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
if ($_SESSION['level'] != "unit") {
    header("location:index.html");
    exit;
}

$kkel = db_satu("select kode_branch from user where userid=?", 's', (string) $userid);
$unit = $kkel ? $kkel['kode_branch'] : null;

$norek = isset($_POST['kode_rekening']) ? trim((string) $_POST['kode_rekening']) : '';
$bulan = isset($_POST['bulan']) ? (string) $_POST['bulan'] : '';
$tahun = isset($_POST['tahun']) ? (string) $_POST['tahun'] : '';
$checkbox = isset($_POST['all']) ? (string) $_POST['all'] : null;
$report = isset($_POST['j']) ? (string) $_POST['j'] : '';

$pesan = array();
$hasil = array();
$cari = isset($_POST['search']);
if ($cari) {
    // (versi lama juga membaca coa.normal di sini dan mencetaknya dengan var_dump; tidak dipakai di halaman ini)
    if (empty($norek)) {
        $pesan[] = "<script>alert('Maaf, Bulan,tahun atau Nomor Perkiraan belum di isi');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='buku_besar.php'</script>";
    } else {
        $_SESSION['noreks'] = $norek;
        $_SESSION['bulans'] = $bulan;
        $_SESSION['tahuns'] = $tahun;
        $_SESSION['js'] = $report;
        $_SESSION['checkboxs'] = $checkbox;
        // versi lama: query sama untuk dicentang maupun tidak
        $hasil = db_semua("select saldo_awal, saldo_akhir ,kode_rekening,nama_rekening from tabel_master_konsol WHERE kode_rekening =? and unit =? and month(tanggal_awal) = ? and Year(tanggal_awal)=?",
            'ssss', $norek, (string) $unit, $bulan, $tahun);
    }
}

$nama_bulan = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Des');
halaman_awal('Buku Besar');
?>
<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="buku_besar.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="bulan" class="col-sm-4 col-form-label">Bulan dan Tahun</label>
        <div class="col-sm-4">
          <select name="bulan" id="bulan" class="form-select form-select-sm">
            <option value="">- Select Month -</option>
            <?php foreach ($nama_bulan as $no => $nm): ?>
            <option value="<?php echo $no; ?>"<?php echo $bulan === (string) $no ? ' selected' : ''; ?>><?php echo $nm; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4">
          <select name="tahun" id="tahun" class="form-select form-select-sm">
            <option>Tahun</option>
            <?php for ($th = (int) date('Y'); $th >= 2020; $th--): ?>
            <option value="<?php echo $th; ?>"<?php echo $tahun === (string) $th ? ' selected' : ''; ?>><?php echo $th; ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="j" class="col-sm-4 col-form-label">Report</label>
        <div class="col-sm-8">
          <select name="j" id="j" class="form-select form-select-sm">
            <option value="">- Select Report -</option>
            <option value="eom"<?php echo $report === 'eom' ? ' selected' : ''; ?>>eom</option>
            <option value="current"<?php echo $report === 'current' ? ' selected' : ''; ?>>current</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="kode" class="col-sm-4 col-form-label">No Perkiraan</label>
        <div class="col-sm-3 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" placeholder="kode / nama rekening" value="<?php echo h($norek); ?>"
                 data-autocomplete="../ajax/cari_coa.php" data-isi-nilai="kode_rek" data-isi-label="kode_rek,nama_rek"
                 data-isi-target='{"#country":"nama_rek"}'>
        </div>
        <div class="col-sm-5"><input type="text" id="country" class="form-control form-control-sm" disabled></div>
        <div class="col-sm-8 offset-sm-4">
          <div class="form-check">
            <input type="checkbox" name="all" value="checkbox" id="all" class="form-check-input"<?php echo $checkbox === 'checkbox' ? ' checked' : ''; ?>>
            <label for="all" class="form-check-label">All data</label>
          </div>
        </div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Cari" class="btn btn-ni btn-sm px-4">Cari</button>
      </div>
    </form>
  </div>
</div>

<?php echo implode("\n", $pesan); ?>

<div class="table-responsive mt-3">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Nomor Perkiraan</th>
        <th>Nama Perkiraan</th>
        <th class="text-end">Saldo Awal</th>
        <th class="text-end">Saldo Akhir</th>
        <th class="text-center">Download</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td><?php echo h($data['kode_rekening']); ?></td>
        <td><?php echo h($data['nama_rekening']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['saldo_awal'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['saldo_akhir'])); ?></td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-success btn-sm" data-export-xlsx data-export-url="buku_besar_export.php" data-export-satuan="transaksi">Download (.xlsx)</button>
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
        <h2 class="modal-title fs-6" id="judul-export">Export Buku Besar</h2>
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
<?php
halaman_akhir(array('assets/app/export_xlsx.js'));
