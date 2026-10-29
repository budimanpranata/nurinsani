<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Cetak Dokumen > Cetak CS WO: pilih Nama AO + Hari Tagih, tampilkan tagihan pembiayaan write-off
 * (loan_wo) per hari dengan link Excel / Cetak (cetak_cs_wo.php).
 * Perilaku sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
include __DIR__ . '/function.php';
$ao = isset($_POST['ao']) ? (string) $_POST['ao'] : '';
$hari = isset($_POST['hari']) ? (string) $_POST['hari'] : '';
$buss_date = date('y-m-d');
$userid = $_SESSION['userid'];
$r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
$branch = $r ? $r['kode_branch'] : '';

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}
//cek level user
if ($_SESSION['level'] != 'unit') {
    header('location:index.html');
    exit;
}

$daftar_ao = db_semua('SELECT * FROM ao WHERE kode_unit = ?', 's', $branch);
$cari = !empty($_POST['search']);
$baris = array();

if ($cari) {
    if (empty($ao)) {
        // versi lama kembali ke cetak.php (menu Cetak CS)
        echo "<script>alert('Maaf,.isi KODE AO!');</script>";
        echo "<script language='javascript'>document.location.href='cetak.php'</script>";
        exit;
    }
    // dipakai cetak_cs_wo.php
    $_SESSION['hari'] = $hari;
    $_SESSION['code_cao'] = $ao;
    $sql = "SELECT hari, branch.unit, loan_wo.cao, loan_wo.code_kel AS kel, nama_kel, COUNT(loan_wo.no_anggota) AS noa,
            SUM(loan_wo.angsuran) AS angsur
        FROM loan_wo
        LEFT JOIN branch ON branch.kode_branch = loan_wo.unit
        LEFT JOIN kelompok ON kelompok.code_kel = loan_wo.code_kel
        WHERE loan_wo.cao = ? AND loan_wo.USERID = ? AND loan_wo.hari = ? AND os > 0 GROUP BY loan_wo.hari";
    $baris = db_semua($sql, 'sss', $ao, (string) $userid, $hari);
    if (count($baris) == 0) {
        // versi lama menjalankan query yang sama sekali lagi
        $baris = db_semua($sql, 'sss', $ao, (string) $userid, $hari);
    }
} else {
    // tampilan awal (versi lama): data loan_detail untuk kode kelompok kosong pada hari ini
    $baris = db_semua("SELECT branch.unit, loan_detail.cao, loan_detail.code_kel AS kel, nama_kel, hari_tagih,
            COUNT(loan_detail.no_anggota), SUM(loan_detail.angsuran)
        FROM loan_detail
        INNER JOIN branch ON branch.kode_branch = loan_detail.unit
        INNER JOIN loan_wo ON loan_wo.no_anggota = loan_detail.no_anggota
        INNER JOIN kelompok ON kelompok.code_kel = loan_detail.code_kel
        WHERE loan_detail.code_kel = ? AND loan_detail.USERID = ? AND loan_wo.tgl_murab = ? GROUP BY loan_detail.unit",
        'sss', '', (string) $userid, $buss_date);
}

halaman_awal('Cetak Dokumen CS WO');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="cetak_wo.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="ao" class="col-sm-4 col-form-label">Nama Ao</label>
        <div class="col-sm-8">
          <select name="ao" id="ao" class="form-select form-select-sm">
            <option value="">- Select Nama AO -</option>
            <?php foreach ($daftar_ao as $a): $a = array_values($a); ?>
              <option value="<?php echo h($a[0]); ?>"><?php echo h($a[1]); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="hari" class="col-sm-4 col-form-label">Hari Tagih</label>
        <div class="col-sm-8">
          <select name="hari" id="hari" class="form-select form-select-sm">
            <option value="">- Select Hari --</option>
            <option value="Senin">Senin</option>
            <option value="Selasa">Selasa</option>
            <option value="Rabu">Rabu</option>
            <option value="Kamis">Kamis</option>
          </select>
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
        <th>Kode AO</th>
        <th>Hari</th>
        <th class="text-end">NOA</th>
        <th class="text-end">Total Tagihan</th>
        <th class="text-center">Excel</th>
        <th class="text-center">Cetak</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($cari): ?>
      <?php foreach ($baris as $data): ?>
        <tr>
          <td><?php echo h($data['unit']); ?></td>
          <td><?php echo h($data['cao']); ?></td>
          <td><?php echo h($data['hari']); ?></td>
          <td class="text-end"><?php echo h($data['noa']); ?></td>
          <td class="text-end"><?php echo 'Rp.' . number_format(compat_num($data['angsur'])); ?></td>
          <td class="text-center">
            <a href="cetak_cs_excel.php?hari=<?php echo urlencode($data['hari']); ?>" target="_blank"
               class="btn btn-outline-success btn-sm" data-konfirmasi="Apakah Anda yakin ?">excel</a>
          </td>
          <td class="text-center">
            <a href="cetak_cs_wo.php?hari=<?php echo urlencode($data['hari']); ?>" target="_blank"
               class="btn btn-ni btn-sm" data-konfirmasi="Apakah Anda yakin ?">Cetak</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <?php foreach ($baris as $data): // kolom sama dengan versi lama ?>
        <tr>
          <td><?php echo h($data['unit']); ?></td>
          <td><?php echo h($data['cao']); ?></td>
          <td><?php echo h($data['nama_kel']); ?></td>
          <td><?php echo h($data['hari_tagih']); ?></td>
          <td><?php echo h(isset($data['tgl_murab']) ? $data['tgl_murab'] : ''); ?></td>
          <td><?php echo h(isset($data['noa']) ? $data['noa'] : ''); ?></td>
          <td class="text-end"><?php echo 'Rp.' . number_format(compat_num(isset($data['angsur']) ? $data['angsur'] : null)); ?></td>
          <td class="text-center">
            <a href="cetak_cs_hari.php?code_kel=<?php echo urlencode($data['kel']); ?>" class="btn btn-ni btn-sm"
               data-konfirmasi="Apakah Anda yakin?">Cetak</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
