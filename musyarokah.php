<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Cetak Dokumen > Cetak Musyarakah: rekap calon pinjaman MUSYAROKAH per unit + link cetak.
 * Tanpa pencarian: semua data user. Dengan Tanggal Musyarakah (tgl_akad): data tanggal tsb.
 * Perilaku sama dengan versi lama (pesan lewat alert lalu kembali ke halaman ini).
 */
require_once __DIR__ . '/inc/tampilan.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
$userid = $_SESSION['userid'];

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

$tanggal = isset($_POST['tanggal']) ? (string) $_POST['tanggal'] : '';
$per_tanggal = !empty($_POST['search']);

/** Tampilkan alert lalu kembali ke musyarokah.php (sama seperti versi lama). */
function alert_kembali($pesan)
{
    echo '<script>alert(' . json_encode($pesan) . ');</script>';
    echo "<script language='javascript'>document.location.href='musyarokah.php'</script>";
    exit;
}

if ($per_tanggal) {
    if (empty($tanggal)) {
        alert_kembali('Maaf,isi Kode Kelompok yang benar!!!');
    }
    // tanggal dipakai oleh cetak_approval2.php, cetak_musyarakah1.php dan export_mus.php
    $_SESSION['smrb'] = $tanggal;
    $data = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(plafond) AS pla, branch.unit AS nama_unit, calon_loan.unit,
            kelompok.nama_kel, calon_loan.code_kel
        FROM calon_loan
        LEFT JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
        LEFT JOIN branch ON branch.kode_branch = calon_loan.unit
        WHERE calon_loan.USERID = ? AND status_usia <> 'YA' AND calon_loan.tgl_akad = ? AND status_app = 'MUSYAROKAH'
        GROUP BY calon_loan.unit, nama_unit", 'ss', $userid, $tanggal);
    if (!$data) {
        alert_kembali('Maaf,Data tidak ditemukan!!!');
    }
} else {
    $data = db_semua("SELECT COUNT(no_anggota) AS noa, SUM(plafond) AS pla, branch.unit AS nama_unit, calon_loan.unit,
            kelompok.nama_kel, calon_loan.code_kel, nama_ao
        FROM calon_loan
        INNER JOIN kelompok ON kelompok.code_kel = calon_loan.code_kel
        INNER JOIN branch ON branch.kode_branch = calon_loan.unit
        INNER JOIN ao ON ao.cao = calon_loan.cao
        WHERE calon_loan.USERID = ? AND status_usia <> 'YA' AND status_app = 'MUSYAROKAH'
        GROUP BY calon_loan.unit, nama_unit", 's', $userid);
}

halaman_awal('Cari Data Musyarakah');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="musyarokah.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-3">
        <label for="tanggal" class="col-sm-4 col-form-label">Tanggal Musyarakah</label>
        <div class="col-sm-8">
          <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="<?php echo h($tanggal); ?>">
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
    <?php foreach ($data as $r):
        // versi lama: pencarian per tanggal mengirim kode kelompok, tampilan semua data mengirim kode unit
        $kode = $per_tanggal ? $r['code_kel'] : $r['unit'];
        $approval = $per_tanggal ? 'cetak_approval2.php' : 'cetak_approval.php';
        $rekap = $per_tanggal ? 'export_mus.php' : 'export_simpana.php';
    ?>
      <tr>
        <td><?php echo h($r['unit']); ?></td>
        <td><?php echo h($r['nama_unit']); ?></td>
        <td class="text-end"><?php echo h($r['noa']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($r['pla'])); ?></td>
        <td class="text-center">
          <a href="<?php echo $approval; ?>?code_kel=<?php echo urlencode($kode); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Apakah Anda yakin akan melakukan Cetak Approval ?">Cetak</a>
        </td>
        <td class="text-center">
          <a href="cetak_musyarakah1.php?code_kel=<?php echo urlencode($kode); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Apakah Anda yakin akan melakukan Cetak Musyarakah ?">Cetak</a>
        </td>
        <td class="text-center">
          <a href="javascript:;" class="btn btn-outline-success btn-sm" title="Rekap Excel"
             onclick="window.open('<?php echo $rekap; ?>','scrollwindow','top=200,left=300,width=800,height=500');">Excel</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
