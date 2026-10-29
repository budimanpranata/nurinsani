<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Detail Pengajuan (dari menu Pengajuan / simulasi.php):
 * daftar anggota kelompok yang masih berstatus ANGGOTA, tombol Ajukan membuka form
 * pengajuan pembiayaan yang dikirim ke engine_update.php?p=update.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}
if (empty($_SESSION['csrf_pengajuan'])) {
    $_SESSION['csrf_pengajuan'] = bin2hex(random_bytes(16));
}

$db = db_ni();
$code_kel = isset($_GET['code_kel']) ? trim((string) $_GET['code_kel']) : '';
// tanggal terakhir yang dipakai (diisi engine_update.php)
$sakad = isset($_SESSION['sakadd']) ? $_SESSION['sakadd'] : '';
$wakal = isset($_SESSION['swakall']) ? $_SESSION['swakall'] : '';

$st = $db->prepare('SELECT kode_branch FROM user WHERE userid = ?');
$st->bind_param('s', $_SESSION['userid']);
$st->execute();
$user = $st->get_result()->fetch_assoc();
$branch = $user ? $user['kode_branch'] : '';

$st = $db->prepare('SELECT nama_kel FROM kelompok WHERE code_kel = ?');
$st->bind_param('s', $code_kel);
$st->execute();
$kel = $st->get_result()->fetch_assoc();

$st = $db->prepare("SELECT anggota.CIF AS dim, anggota.UNIT, anggota.CUST_SHORT_NAME, anggota.no, anggota.CODE_KEL,
        loan.Plafond, loan.angsuran, loan.bagi_hasil, loan.Tenor, loan.suffix, loan.cao
    FROM anggota
    INNER JOIN loan ON loan.cif = anggota.cif
    WHERE anggota.CODE_KEL = ? AND anggota.UNIT = ? AND anggota.status = 'ANGGOTA'
    ORDER BY anggota.CUST_SHORT_NAME");
$st->bind_param('ss', $code_kel, $branch);
$st->execute();
$anggota = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$st = $db->prepare('SELECT jenis_akad, nama_akad FROM jenis_akad WHERE unit = ? ORDER BY nama_akad');
$st->bind_param('s', $_SESSION['userid']);
$st->execute();
$jenis_akad = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$info = isset($_SESSION['info']) ? $_SESSION['info'] : '';
unset($_SESSION['info']);

halaman_awal('Pengajuan Pembiayaan Kelompok');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
  <div>
    <span class="text-muted">Kelompok:</span>
    <strong><?php echo h($code_kel . ($kel ? ' - ' . $kel['nama_kel'] : '')); ?></strong>
  </div>
  <a href="simulasi.php" class="btn btn-outline-secondary btn-sm">&larr; Kembali ke Pengajuan</a>
</div>

<?php if ($info !== ''): ?>
  <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
    Berhasil: <?php echo h($info); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
<?php endif; ?>

<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
  <input type="search" class="form-control form-control-sm" style="max-width: 22rem" autofocus
         placeholder="Cari nama, CIF, plafond..." aria-label="Cari anggota"
         data-cari-tabel="#tabel-anggota" data-cari-info="#info-cari">
  <span class="text-muted" id="info-cari"></span>
</div>

<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle" id="tabel-anggota">
    <thead>
      <tr>
        <th>Unit</th>
        <th>CIF</th>
        <th>Kode Kelompok</th>
        <th>Nama</th>
        <th class="text-end">Plafond Sebelumnya</th>
        <th class="text-end">Angsuran</th>
        <th class="text-end">Margin</th>
        <th class="text-center">Pinjaman Ke</th>
        <th class="text-center">Detail</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$anggota): ?>
      <tr data-baris-kosong><td colspan="9" class="text-center text-muted py-3">Tidak ada anggota berstatus ANGGOTA di kelompok ini.</td></tr>
    <?php endif; ?>
    <?php foreach ($anggota as $r): ?>
      <tr>
        <td><?php echo h($r['UNIT']); ?></td>
        <td><?php echo h($r['dim']); ?></td>
        <td><?php echo h($r['CODE_KEL']); ?></td>
        <td><?php echo h($r['CUST_SHORT_NAME']); ?></td>
        <td class="text-end"><?php echo number_format((float) $r['Plafond']); ?></td>
        <td class="text-end"><?php echo number_format((float) $r['angsuran']); ?></td>
        <td class="text-end"><?php echo number_format((float) $r['bagi_hasil']); ?></td>
        <td class="text-center"><?php echo h($r['suffix']); ?></td>
        <td class="text-center">
          <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#form-pengajuan"
                  data-id="<?php echo h($r['no']); ?>" data-cif="<?php echo h($r['dim']); ?>"
                  data-nama="<?php echo h($r['CUST_SHORT_NAME']); ?>" data-kds="<?php echo h($r['cao']); ?>"
                  data-pla="<?php echo h($r['Plafond']); ?>">Ajukan</button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="form-pengajuan" tabindex="-1" aria-labelledby="judul-pengajuan" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="form-data" method="post" action="engine_update.php?p=update" autocomplete="off">
      <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_pengajuan']); ?>">
      <input type="hidden" name="id" id="id">
      <div class="modal-header">
        <h5 class="modal-title fs-6" id="judul-pengajuan">Update Data Pengajuan Pembiayaan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger py-2 d-none" id="pesan-error" role="alert"></div>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label for="nama" class="form-label mb-0">Nama</label>
            <input type="text" name="nama" id="nama" class="form-control form-control-sm" readonly>
          </div>
          <div class="col-md-3">
            <label for="cif" class="form-label mb-0">CIF</label>
            <input type="text" name="cif" id="cif" class="form-control form-control-sm" readonly>
          </div>
          <div class="col-md-3">
            <label for="kds" class="form-label mb-0">Kode AO</label>
            <input type="text" name="kds" id="kds" class="form-control form-control-sm" readonly>
          </div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-3">
            <label for="tenor" class="form-label mb-0">Tenor (minggu)</label>
            <input type="number" name="tenor" id="tenor" class="form-control form-control-sm" min="1" step="1" required>
          </div>
          <div class="col-md-3">
            <label for="pla" class="form-label mb-0">Plafond</label>
            <input type="number" name="pla" id="pla" class="form-control form-control-sm" min="1" step="1" required>
          </div>
          <div class="col-md-3">
            <label for="inn" class="form-label mb-0">Tanggal Wakalah</label>
            <input type="date" name="inn" id="inn" class="form-control form-control-sm" value="<?php echo h($wakal); ?>" required>
          </div>
          <div class="col-md-3">
            <label for="akad" class="form-label mb-0">Tanggal Akad</label>
            <input type="date" name="akad" id="akad" class="form-control form-control-sm" value="<?php echo h($sakad); ?>" required>
          </div>
        </div>
        <div class="mb-2">
          <label for="jenisAkad" class="form-label mb-0">Jenis Akad</label>
          <select name="jenisAkad" id="jenisAkad" class="form-select form-select-sm" required>
            <option value="">-- Pilih Akad --</option>
            <?php foreach ($jenis_akad as $a): ?>
              <option value="<?php echo h($a['jenis_akad']); ?>"><?php echo h($a['nama_akad']); ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!$jenis_akad): ?><div class="form-text text-danger">Data jenis akad untuk unit ini belum ada.</div><?php endif; ?>
        </div>

        <div class="border rounded p-2">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <strong>Rincian Barang</strong>
            <span class="text-muted">Total harga otomatis mengisi Plafond</span>
          </div>
          <?php for ($i = 1; $i <= 10; $i++): ?>
          <div class="row g-2 mb-1">
            <div class="col-7">
              <input type="text" name="barang_<?php echo $i; ?>" class="form-control form-control-sm" maxlength="40"
                     placeholder="Nama Barang <?php echo $i; ?>" aria-label="Nama Barang <?php echo $i; ?>">
            </div>
            <div class="col-5">
              <input type="number" name="harga_<?php echo $i; ?>" class="form-control form-control-sm harga-barang" min="0" step="1"
                     placeholder="Harga Barang <?php echo $i; ?>" aria-label="Harga Barang <?php echo $i; ?>">
            </div>
          </div>
          <?php endfor; ?>
          <div class="text-end fw-semibold">Total: <span id="total-barang">0</span></div>
        </div>

        <div class="mt-3 d-none" id="progress-simpan">
          <div class="d-flex justify-content-between small mb-1">
            <span id="progress-teks">Memproses pengajuan...</span>
            <span id="progress-persen">0%</span>
          </div>
          <div class="progress" role="progressbar" aria-label="Proses simpan" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width: 0%"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-ni btn-sm px-4" id="tombol-simpan">
          <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span> Simpan
        </button>
      </div>
    </form>
  </div>
</div>
<?php
halaman_akhir(array('assets/app/pengajuan.js'));
