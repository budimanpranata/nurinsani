<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Cetak Dokumen > Wakalah: rekap calon pinjaman per unit dengan link cetak Approval, Wakalah dan Rekap.
 * Tanpa pencarian: semua data user (cetak_approval.php / cetak_wakalah.php).
 * Dengan Tanggal Murabahah: data tanggal tsb (cetak_approval1.php / cetak_wakalah1.php / export_wakalah.php).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();

if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}
if ($_SESSION['level'] != 'unit') {
    header('location:index.html');
    exit;
}

$userid = $_SESSION['userid'];
$tanggal = isset($_POST['tanggal']) ? trim((string) $_POST['tanggal']) : '';
$dicari = isset($_POST['search']);
$pesan = '';

if ($dicari && $tanggal === '') {
    $pesan = 'Maaf, isi Tanggal Murabahah yang benar.';
}

if ($dicari && $tanggal !== '') {
    // tanggal dipakai oleh cetak_approval1.php, cetak_wakalah1.php dan export_wakalah.php
    $_SESSION['smrb'] = $tanggal;
    $data = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(plafond) AS pla, MAX(branch.unit) AS nama_unit, calon_loan.unit,
            MAX(calon_loan.code_kel) AS code_kel
        FROM calon_loan
        LEFT JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
        LEFT JOIN branch ON branch.kode_branch = calon_loan.unit
        WHERE calon_loan.USERID = ? AND status_usia <> 'YA' AND calon_loan.tgl_murab = ?
        GROUP BY calon_loan.unit", 'ss', $userid, $tanggal);
    if (!$data) {
        $pesan = 'Maaf, data tidak ditemukan.';
    }
} else {
    $data = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(plafond) AS pla, MAX(branch.unit) AS nama_unit, calon_loan.unit
        FROM calon_loan
        INNER JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
        INNER JOIN branch ON branch.kode_branch = calon_loan.unit
        INNER JOIN ao ON ao.cao = calon_loan.cao
        WHERE calon_loan.USERID = ? AND status_usia <> 'YA'
        GROUP BY calon_loan.unit", 's', $userid);
}
$per_tanggal = $dicari && $tanggal !== '';

halaman_awal('Cari Data Wakalah');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <?php if ($pesan !== ''): ?>
      <div class="alert alert-warning py-2"><?php echo h($pesan); ?></div>
    <?php endif; ?>
    <form method="post" action="wakalah.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-3">
        <label for="tanggal" class="col-sm-4 col-form-label">Tanggal Murabahah</label>
        <div class="col-sm-8">
          <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" required value="<?php echo h($tanggal); ?>">
        </div>
      </div>
      <div class="text-end">
        <?php if ($dicari): ?><a href="wakalah.php" class="btn btn-outline-secondary btn-sm">Tampilkan semua</a><?php endif; ?>
        <button type="submit" name="search" value="1" class="btn btn-ni btn-sm px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<div class="mb-1 text-muted">
  <?php echo $per_tanggal ? 'Data tanggal murabahah ' . h(date('d-m-Y', strtotime($tanggal))) : 'Semua data wakalah'; ?>
</div>
<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Kode Unit</th>
        <th>Nama Unit</th>
        <th class="text-end">NOA</th>
        <th class="text-end">Total Plafond</th>
        <th class="text-center">Cetak Approval</th>
        <th class="text-center">Cetak</th>
        <th class="text-center">Rekap</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$data): ?>
      <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $r):
        // versi lama: tampilan semua data mengirim kode unit, pencarian per tanggal mengirim kode kelompok
        $kode = $per_tanggal ? $r['code_kel'] : $r['unit'];
        $akhiran = $per_tanggal ? '1' : '';
    ?>
      <tr>
        <td><?php echo h($r['unit']); ?></td>
        <td><?php echo h($r['nama_unit']); ?></td>
        <td class="text-end"><?php echo h($r['noa']); ?></td>
        <td class="text-end"><?php echo number_format((float) $r['pla']); ?></td>
        <td class="text-center">
          <a href="cetak_approval<?php echo $akhiran; ?>.php?code_kel=<?php echo urlencode($kode); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Apakah Anda yakin akan melakukan Cetak Approval?">Cetak</a>
        </td>
        <td class="text-center">
          <a href="cetak_wakalah<?php echo $akhiran; ?>.php?code_kel=<?php echo urlencode($kode); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Apakah Anda yakin akan melakukan Cetak Wakalah?">Cetak</a>
        </td>
        <td class="text-center">
          <?php if ($per_tanggal): ?>
            <a href="export_wakalah.php" class="btn btn-outline-success btn-sm" title="Rekap Excel">Excel</a>
          <?php else: ?>
            <span class="text-muted" title="Cari per Tanggal Murabahah untuk rekap Excel">&ndash;</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
