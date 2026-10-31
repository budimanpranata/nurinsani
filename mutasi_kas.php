<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Report > Mutasi Kas: transaksi rekening kas 1120000 unit user dengan saldo berjalan.
 *   Tanpa pencarian : transaksi pada tanggal sistem (sudah maupun belum diposting).
 *   Search          : transaksi yang sudah diposting di antara Tanggal Awal dan Tanggal Akhir.
 * Saldo berjalan dimulai dari 0 dan dihitung menurut urutan hasil query (versi lama: @bal:=(@bal+debet)-kredit tanpa ORDER BY).
 * Logika sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 * Keputusan user 2026-10-31: logika dan perlakuan menu ini dipertahankan seperti versi lama (hanya migrasi kode).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

$buss_date1 = isset($_POST['buss_date1']) ? (string) $_POST['buss_date1'] : '';
$buss_date2 = isset($_POST['buss_date2']) ? (string) $_POST['buss_date2'] : '';
$kkel = db_satu("select kode_branch FROM user where  userid =?", 's', (string) $userid);
$unit = $kkel ? (string) $kkel['kode_branch'] : '';

$pesan = array();
$baris = array();
if (isset($_POST['search'])) {
    if (empty($buss_date1) || empty($buss_date2)) {
        $pesan[] = "<script>alert('Maaf,.isi Tanggal Pencarian  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='mutasi_kas.php'</script>";
    } else {
        $baris = db_semua("Select kode_rekening,DATE_FORMAT(tanggal_transaksi, '%d %M %Y') as tanggal_transaksi,keterangan_transaksi,debet,kredit from tabel_transaksi where kode_rekening='1120000' and tanggal_transaksi between ? and ? and unit=? and keterangan_posting='Post'",
            'sss', $buss_date1, $buss_date2, $unit);
    }
} else {
    $baris = db_semua("Select kode_rekening,DATE_FORMAT(tanggal_transaksi, '%d %M %Y') as tanggal_transaksi,keterangan_transaksi,debet,kredit from tabel_transaksi where kode_rekening='1120000' and tanggal_transaksi=? and unit=?",
        'ss', (string) $tgl_system, $unit);
}
// saldo berjalan dari 0, urut hasil query (sama dengan @bal versi lama)
$saldo = 0;
foreach ($baris as $i => $b) {
    $saldo = ($saldo + compat_num($b['debet'])) - compat_num($b['kredit']);
    $baris[$i]['saldo'] = $saldo;
}

halaman_awal('Mutasi Kas');
?>
<div class="card card-ni">
  <div class="card-header">Pencarian Berdasarkan Tanggal</div>
  <div class="card-body">
    <form method="post" action="mutasi_kas.php" name="postform" class="form-ni" autocomplete="off">
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
  <table class="table table-sm table-bordered table-ni table-hover align-middle">
    <thead>
      <tr>
        <th>Norek</th>
        <th>Keterangan</th>
        <th>Tgl Transaksi</th>
        <th class="text-end">Debet</th>
        <th class="text-end">Kredit</th>
        <th class="text-end">Saldo</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($baris as $data): ?>
      <tr>
        <td><?php echo h($data['kode_rekening']); ?></td>
        <td><?php echo h($data['keterangan_transaksi']); ?></td>
        <td><?php echo h($data['tanggal_transaksi']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['debet'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['kredit'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['saldo'])); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
