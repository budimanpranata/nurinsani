<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Cetak Dokumen > Cetak CS: pilih Kode AO + Tanggal CS, tampilkan kelompok yang ditagih
 * (per hari tagih; bila kosong, per tanggal jadwal JATPO) dengan link Excel / Cetak.
 * Perilaku sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
include __DIR__ . '/function.php';
$nopen = isset($_POST['nopen']) ? (string) $_POST['nopen'] : '';
$tanggl = isset($_POST['tanggal']) ? (string) $_POST['tanggal'] : '';
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

$ao = db_semua('SELECT * FROM ao WHERE kode_unit = ?', 's', $branch);
$cari = !empty($_POST['search']);
$baris = array();

if ($cari) {
    if (empty($nopen)) {
        echo "<script>alert('Maaf,.isi KODE AO !!!');</script>";
        echo "<script language='javascript'>document.location.href='cetak.php'</script>";
        exit;
    }
    // dipakai cetak_cs_excel.php dan cetak_cs_hari.php
    $_SESSION['jatuh'] = $tanggl;
    $_SESSION['code_cao'] = $nopen;
    $day = date('D', strtotime($tanggl));
    $dayList = array('Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu',
        'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu');

    // 1. kelompok dengan hari tagih = hari dari Tanggal CS
    $baris = db_semua("SELECT branch.unit, loan.cao, loan.code_kel AS kel, nama_kel, COUNT(loan.no_anggota) AS noa,
            SUM(loan.angsuran) AS angsur
        FROM loan
        LEFT JOIN branch ON branch.kode_branch = loan.unit
        LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
        WHERE loan.cao = ? AND loan.unit = ? AND loan.hari = ? AND os > 0 GROUP BY loan.code_kel",
        'sss', $nopen, (string) $userid, $dayList[$day]);
    // 2. bila kosong: kelompok dengan jadwal JATPO pada Tanggal CS
    if (count($baris) == 0) {
        $baris = db_semua("SELECT branch.unit, loan.cao, loan.code_kel AS kel, nama_kel, COUNT(loan.no_anggota) AS noa,
                SUM(loan.angsuran) AS angsur, pinjaman_detail_jatpo.tgl_jatuh_tempo AS tgl_murab
            FROM loan
            LEFT JOIN branch ON branch.kode_branch = loan.unit
            LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
            LEFT JOIN pinjaman_detail_jatpo ON pinjaman_detail_jatpo.id_pinjam = loan.no_anggota
            WHERE loan.cao = ? AND loan.unit = ? AND pinjaman_detail_jatpo.tgl_jatuh_tempo = ? AND os > 0
            GROUP BY loan.code_kel", 'sss', $nopen, (string) $userid, $tanggl);
    }
} else {
    // tampilan awal (versi lama): data loan_detail untuk kode kelompok = Kode AO yang dipilih (kosong) pada hari ini
    $baris = db_semua("SELECT branch.unit, loan_detail.cao, loan_detail.code_kel AS kel, nama_kel, hari_tagih,
            COUNT(loan_detail.no_anggota), SUM(loan_detail.angsuran)
        FROM loan_detail
        INNER JOIN branch ON branch.kode_branch = loan_detail.unit
        INNER JOIN loan ON loan.no_anggota = loan_detail.no_anggota
        INNER JOIN kelompok ON kelompok.code_kel = loan_detail.code_kel
        WHERE loan_detail.code_kel = ? AND loan_detail.USERID = ? AND loan.tgl_murab = ? GROUP BY loan_detail.unit",
        'sss', $nopen, (string) $userid, $buss_date);
}

halaman_awal('Cetak Dokumen CS');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="cetak.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="nopen" class="col-sm-4 col-form-label">KODE AO</label>
        <div class="col-sm-8">
          <select name="nopen" id="nopen" class="form-select form-select-sm">
            <option value="">- Select Kode AO -</option>
            <?php foreach ($ao as $a): $a = array_values($a); ?>
              <option value="<?php echo h($a[0]); ?>"><?php echo h($a[1]); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="tanggal" class="col-sm-4 col-form-label">Tanggal CS</label>
        <div class="col-sm-8">
          <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="">
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
        <th>Kode Ao</th>
        <th>Kelompok</th>
        <th>Tanggal Tagih</th>
        <th class="text-end">Noa</th>
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
          <td><?php echo h($data['nama_kel']); ?></td>
          <td><?php echo h(tgl_indo(isset($data['tgl_murab']) ? $data['tgl_murab'] : null)); ?></td>
          <td class="text-end"><?php echo h($data['noa']); ?></td>
          <td class="text-end"><?php echo 'Rp.' . number_format(compat_num($data['angsur'])); ?></td>
          <td class="text-center">
            <a href="cetak_cs_excel.php?kode_kel=<?php echo urlencode($data['kel']); ?>" target="_blank"
               class="btn btn-outline-success btn-sm" data-konfirmasi="Apakah Anda yakin ?">excel</a>
          </td>
          <td class="text-center">
            <a href="cetak_cs_hari.php?kode_kel=<?php echo urlencode($data['kel']); ?>" target="_blank"
               class="btn btn-ni btn-sm" data-konfirmasi="Apakah Anda yakin ?">cetak</a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <?php foreach ($baris as $data): // kolom sama dengan versi lama (noa & angsur tidak diberi alias) ?>
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
