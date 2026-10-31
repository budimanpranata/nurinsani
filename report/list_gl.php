<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu New Report > List Jurnal: total debet/kredit transaksi unit di antara dua tanggal, lalu Download daftar jurnal.
 *   Search   : simpan tanggal di sesi (tgl_awal_pos / tgl_akhir_pos), tampilkan total unit.
 *   Download : export .xlsx bertahap dengan indikator progres (report/list_gl_export.php, assets/app/export_xlsx.js),
 *              pengganti excel_gl.php (tabel HTML yang dinamai .xls). Isi dan urutan sama dengan versi lama.
 * Logika sama dengan versi lama; hanya tampilan, query (prepared statement), dan format file export yang berubah.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
$r = db_satu("select kode_branch from user where userid =?", 's', (string) $userid);
$unit = $r ? (string) $r['kode_branch'] : '';
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

$buss_date1 = isset($_POST['buss_date1']) ? (string) $_POST['buss_date1'] : '';
$buss_date2 = isset($_POST['buss_date2']) ? (string) $_POST['buss_date2'] : '';
$pesan = array();
$hasil = array();
if (isset($_POST['search'])) {
    if (empty($buss_date1) || empty($buss_date2)) {
        $pesan[] = "<script>alert('Maaf,.isi Tanggal Pencarian  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='list_gl.php'</script>";
    } else {
        $_SESSION['tgl_awal_pos'] = $buss_date1;
        $_SESSION['tgl_akhir_pos'] = $buss_date2;
        // (versi lama mencetak var_dump tanggal awal di sini, sisa debug)
        $hasil = db_semua("Select sum(debet) as debet,sum(kredit) as kredit from tabel_transaksi where  tanggal_transaksi between ? and ? and unit=? group by unit",
            'sss', $buss_date1, $buss_date2, $unit);
    }
}

halaman_awal('List Jurnal');
?>
<div class="card card-ni">
  <div class="card-header">Pencarian Berdasarkan Tanggal</div>
  <div class="card-body">
    <form method="post" action="list_gl.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="tgl_lahir" class="col-sm-4 col-form-label">Tanggal Awal</label>
        <div class="col-sm-8"><input name="buss_date1" type="date" id="tgl_lahir" value="<?php echo h($buss_date1); ?>" class="form-control form-control-sm"></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="tgl_trn" class="col-sm-4 col-form-label">Tanggal Akhir</label>
        <div class="col-sm-8"><input name="buss_date2" type="date" id="tgl_trn" value="<?php echo h($buss_date2); ?>" class="form-control form-control-sm"></div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Search" class="btn btn-ni btn-sm px-4">Search</button>
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
          <button type="button" class="btn btn-outline-success btn-sm" data-export-xlsx data-export-url="list_gl_export.php" data-export-satuan="transaksi">Download (.xlsx)</button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modal-export" tabindex="-1" aria-labelledby="judul-export" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="judul-export">Export List Jurnal</h2>
      </div>
      <div class="modal-body">
        <div class="progress" role="progressbar" aria-label="Progres export" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="height: 1.25rem;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
        </div>
        <div class="small text-muted mt-2" data-export-teks aria-live="polite">Menyiapkan data…</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm d-none" data-bs-dismiss="modal" data-export-tutup>Tutup</button>
      </div>
    </div>
  </div>
</div>
<?php
halaman_akhir(array('assets/app/export_xlsx.js'));
