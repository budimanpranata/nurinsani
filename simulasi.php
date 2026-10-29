<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pengajuan: cari kelompok di unit user, lalu lanjut ke input pinjaman (pengajuan_ex.php).
 * Kelompok yang belum punya pinjaman diarahkan ke pendaftaran kelompok (new.php).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}

$db = db_ni();
$kelompok = isset($_POST['kelompok']) ? trim((string) $_POST['kelompok']) : '';
$nama = isset($_POST['nama']) ? trim((string) $_POST['nama']) : '';
$jenis = isset($_POST['jenis']) ? trim((string) $_POST['jenis']) : '';
$dicari = isset($_POST['search']);
$pesan = '';
$hasil = array();

if ($dicari) {
    if ($kelompok === '') {
        $pesan = 'Maaf, isi Kode Kelompok yang benar.';
    } else {
        $st = $db->prepare('SELECT kode_branch FROM user WHERE userid = ?');
        $st->bind_param('s', $_SESSION['userid']);
        $st->execute();
        $user = $st->get_result()->fetch_assoc();
        $branch = $user ? $user['kode_branch'] : '';

        $st = $db->prepare('SELECT COUNT(loan.no_anggota) AS noa, loan.unit, MAX(kelompok.nama_kel) AS nama_kel,
                MAX(loan.cao) AS cao, MAX(ao.nama_ao) AS nama_ao, MAX(loan.tgl_wakalah) AS tgl_wakalah
            FROM loan
            INNER JOIN kelompok ON kelompok.code_kel = loan.code_kel
            LEFT JOIN ao ON ao.cao = loan.cao
            WHERE loan.code_kel = ? AND loan.unit = ?
            GROUP BY loan.unit');
        $st->bind_param('ss', $kelompok, $branch);
        $st->execute();
        $hasil = $st->get_result()->fetch_all(MYSQLI_ASSOC);

        if (!$hasil) {
            $teks = json_encode('Maaf, kelompok ' . $kelompok . ' tidak ditemukan. Sistem akan membuat pendaftaran kelompok.');
            echo "<script>alert($teks); document.location.href = 'new.php';</script>";
            exit;
        }
    }
}

halaman_awal('Pengajuan');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <?php if ($pesan !== ''): ?>
      <div class="alert alert-warning py-2"><?php echo h($pesan); ?></div>
    <?php endif; ?>
    <form method="post" action="simulasi.php" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Kode Kelompok</label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="kelompok" id="kode" class="form-control form-control-sm" required
                 value="<?php echo h($kelompok); ?>" placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel"
                 data-isi-label="code_kel,nama_kel" data-isi-target='{"#nama":"nama_kel"}'>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="nama" class="col-sm-4 col-form-label">Nama Kelompok</label>
        <div class="col-sm-8">
          <input type="text" name="nama" id="nama" class="form-control form-control-sm" value="<?php echo h($nama); ?>">
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="jenis" class="col-sm-4 col-form-label">Jenis Pinjaman</label>
        <div class="col-sm-8">
          <input type="text" name="jenis" id="jenis" class="form-control form-control-sm" value="<?php echo h($jenis); ?>">
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="1" class="btn btn-ni btn-sm px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Unit</th>
        <th>Nama Kelompok</th>
        <th class="text-end">Total NOA</th>
        <th>Nama AO</th>
        <th>Tanggal Jatuh Tempo</th>
        <th class="text-center">Detail</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$hasil): ?>
      <tr><td colspan="6" class="text-center text-muted py-3">Cari kelompok untuk menampilkan data.</td></tr>
    <?php endif; ?>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td><?php echo h($data['unit']); ?></td>
        <td><?php echo h($data['nama_kel']); ?></td>
        <td class="text-end"><?php echo h($data['noa']); ?></td>
        <td><?php echo h($data['cao'] . ($data['nama_ao'] !== null ? ' - ' . $data['nama_ao'] : '')); ?></td>
        <td><?php echo h($data['tgl_wakalah'] ? date('d-m-Y', strtotime($data['tgl_wakalah'])) : ''); ?></td>
        <td class="text-center">
          <a href="pengajuan_ex.php?code_kel=<?php echo urlencode($kelompok); ?>" class="btn btn-ni btn-sm"
             data-konfirmasi="Anda yakin akan melakukan Input Pinjaman?">Detail</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
halaman_akhir();
