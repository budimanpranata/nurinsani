<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Report > Akuntasi > Neraca > Buku Besar Unit: buku besar satu rekening (kode_rekening COA) untuk unit terpilih.
 *   Cari     : simpan pilihan (rekening, bulan, tahun, All data, unit) di sesi; tampilkan saldo rekening unit dari
 *              tabel_master_konsol bulan/tahun itu, atau dengan All data dari tabel_master.
 *   Download : export .xlsx bertahap dengan indikator progres (kp/laporan/buku_besar_unit_export.php, assets/app/export_xlsx.js),
 *              pengganti excel_buku_besar_unit.php (permintaan user 2026-11-03, sama dengan menu unit).
 * Dimigrasi 2026-11-03: tampilan (Bootstrap 5) dan query (prepared statement); isian sesi dan saldo sama dengan versi lama.
 * Saran rekening dari ajax/cari_coa.php (query sama dengan autosuggesst.php di root). Pilihan Tahun 2020 s.d. tahun berjalan
 * (versi lama hanya 2022). Rekening kosong kembali ke halaman ini (versi lama ke buku_besar.php, Buku Besar KP).
 * Dibuang: var_dump($unit) dan skrip autosuggest/formatCurrency lama.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
if ($_SESSION['level'] != "kp") {
    header("location:index.html");
    exit;
}

$norek = isset($_POST['kode_rekening']) ? trim((string) $_POST['kode_rekening']) : '';
$bulan = isset($_POST['bulan']) ? (string) $_POST['bulan'] : '';
$tahun = isset($_POST['tahun']) ? (string) $_POST['tahun'] : '';
$checkbox = isset($_POST['all']) ? (string) $_POST['all'] : null;
$unit = isset($_POST['unit']) ? (string) $_POST['unit'] : '';

$pesan = array();
$hasil = array();
if (isset($_POST['search'])) {
    // (versi lama juga membaca coa.normal di sini; tidak dipakai di halaman ini)
    if (empty($norek)) {
        $pesan[] = "<script>alert('Maaf, Bulan,tahun atau Nomor Perkiraan belum di isi');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='buku_besar_unit.php'</script>";
    } else {
        $_SESSION['noreks'] = $norek;
        $_SESSION['bulans'] = $bulan;
        $_SESSION['tahuns'] = $tahun;
        $_SESSION['checkboxs'] = $checkbox;
        $_SESSION['units'] = $unit;
        if ($checkbox == 'checkbox') {
            $hasil = db_semua("select saldo_awal, saldo_akhir ,kode_rekening,nama_rekening from tabel_master WHERE kode_rekening =? and unit =?", 'ss', $norek, $unit);
        } else {
            $hasil = db_semua("select saldo_awal as saldo_awal,saldo_akhir ,kode_rekening,nama_rekening from tabel_master_konsol
                WHERE kode_rekening =? and unit =? and month(tanggal_awal)=? and Year(tanggal_awal)=?", 'ssss', $norek, $unit, $bulan, $tahun);
        }
    }
}
$cabang = db_semua("select kode_branch, unit from branch");

$nama_bulan = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Des');
halaman_awal('Buku Besar Unit');
?>
<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="buku_besar_unit.php" name="postform" class="form-ni" autocomplete="off">
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
            <option value="">Tahun</option>
            <?php for ($th = (int) date('Y'); $th >= 2020; $th--): ?>
            <option value="<?php echo $th; ?>"<?php echo $tahun === (string) $th ? ' selected' : ''; ?>><?php echo $th; ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">No Perkiraan</label>
        <div class="col-sm-3 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" placeholder="kode / nama rekening" value="<?php echo h($norek); ?>"
                 data-autocomplete="../../ajax/cari_coa.php" data-isi-nilai="kode_rek" data-isi-label="kode_rek,nama_rek"
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
          <button type="button" class="btn btn-outline-success btn-sm" data-export-xlsx data-export-url="buku_besar_unit_export.php" data-export-satuan="transaksi">Download (.xlsx)</button>
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
        <h2 class="modal-title fs-6" id="judul-export">Export Buku Besar Unit</h2>
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
