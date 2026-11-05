<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Data AO (tabel `ao`): daftar, tambah, ubah, hapus.
 * cao dipakai sebagai kunci di loan, calon_loan, simpanan, dll., jadi tidak bisa diubah
 * dan AO yang masih dipakai tidak bisa dihapus.
 * Tampilan dimigrasi ke Bootstrap 5 (inc/tampilan.php) 2026-11-05, perintah user; proses tidak berubah.
 * Hanya level admin (cek_admin.php).
 */
require_once __DIR__ . '/cek_admin.php';
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
require_once __DIR__ . '/../inc/tampilan.php';

$db = db_ni();
$per_halaman = 20;
// tabel yang menyimpan cao; AO yang masih tercatat di sini tidak boleh dihapus
$tabel_pemakai = array('loan', 'calon_loan', 'simpanan', 'anggota', 'kelompok', 'tagihan_cs', 'tunggakan');

if (empty($_SESSION['csrf_ao'])) {
    $_SESSION['csrf_ao'] = bin2hex(random_bytes(16));
}

function ambil_ao($db, $cao)
{
    $st = $db->prepare('SELECT cao, nama_ao, no_tlp, kode_unit, atasan, nik_ao FROM ao WHERE cao = ?');
    $st->bind_param('s', $cao);
    $st->execute();
    return $st->get_result()->fetch_assoc();
}

function ke_daftar($pesan, $jenis = 'success')
{
    $_SESSION['pesan_ao'] = array($jenis, $pesan);
    header('location:ao.php');
    exit;
}

// daftar unit untuk pilihan
$unit = array();
$r = $db->query('SELECT kode_branch, unit FROM branch ORDER BY kode_branch');
while ($r && ($row = $r->fetch_assoc())) {
    $unit[$row['kode_branch']] = $row['unit'];
}

$form = array('cao' => '', 'nama_ao' => '', 'no_tlp' => '', 'kode_unit' => '', 'atasan' => '', 'nik_ao' => '');
$mode = 'tambah';
$error = array();

/* ---------------- aksi (POST) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_ao'], $_POST['csrf'])) {
        ke_daftar('Sesi formulir sudah tidak berlaku, silakan ulangi.', 'danger');
    }
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'hapus') {
        $cao = trim((string) $_POST['cao']);
        if (!ambil_ao($db, $cao)) {
            ke_daftar('AO tidak ditemukan.', 'danger');
        }
        $dipakai = array();
        foreach ($tabel_pemakai as $t) {
            $st = $db->prepare("SELECT COUNT(*) FROM `$t` WHERE cao = ?");
            if (!$st) continue;
            $st->bind_param('s', $cao);
            $st->execute();
            $n = (int) $st->get_result()->fetch_row()[0];
            if ($n > 0) $dipakai[] = "$t ($n)";
        }
        if ($dipakai) {
            ke_daftar("AO $cao tidak bisa dihapus karena masih dipakai di: " . implode(', ', $dipakai) . '.', 'danger');
        }
        $st = $db->prepare('DELETE FROM ao WHERE cao = ?');
        $ok = $st && $st->bind_param('s', $cao) && $st->execute();
        $ok ? ke_daftar("AO $cao sudah dihapus.") : ke_daftar('Gagal menghapus: ' . $db->error, 'danger');
    }

    if ($aksi === 'tambah' || $aksi === 'ubah') {
        foreach ($form as $k => $v) {
            $form[$k] = isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
        }
        $mode = $aksi;

        if ($form['cao'] === '' || !preg_match('/^[A-Za-z0-9]{1,8}$/', $form['cao'])) {
            $error[] = 'Kode AO wajib diisi, huruf/angka, maksimal 8 karakter.';
        }
        if ($form['nama_ao'] === '' || strlen($form['nama_ao']) > 50) {
            $error[] = 'Nama AO wajib diisi, maksimal 50 karakter.';
        }
        if ($form['no_tlp'] !== '' && !preg_match('/^[0-9+\- ]{1,14}$/', $form['no_tlp'])) {
            $error[] = 'No. telepon hanya angka, +, - atau spasi, maksimal 14 karakter.';
        }
        if ($form['kode_unit'] === '' || strlen($form['kode_unit']) > 4 || ($unit && !isset($unit[$form['kode_unit']]))) {
            $error[] = 'Unit wajib dipilih.';
        }
        if (strlen($form['atasan']) > 16) {
            $error[] = 'NIK atasan maksimal 16 karakter.';
        }
        if (strlen($form['nik_ao']) > 30) {
            $error[] = 'NIK AO maksimal 30 karakter.';
        }
        if ($aksi === 'tambah' && !$error && ambil_ao($db, $form['cao'])) {
            $error[] = 'Kode AO ' . $form['cao'] . ' sudah dipakai.';
        }
        if ($aksi === 'ubah' && !ambil_ao($db, $form['cao'])) {
            ke_daftar('AO tidak ditemukan.', 'danger');
        }

        if (!$error) {
            if ($aksi === 'tambah') {
                $st = $db->prepare('INSERT INTO ao (cao, nama_ao, no_tlp, kode_unit, atasan, nik_ao) VALUES (?, ?, ?, ?, ?, ?)');
                $ok = $st && $st->bind_param('ssssss', $form['cao'], $form['nama_ao'], $form['no_tlp'], $form['kode_unit'], $form['atasan'], $form['nik_ao']) && $st->execute();
            } else {
                $st = $db->prepare('UPDATE ao SET nama_ao = ?, no_tlp = ?, kode_unit = ?, atasan = ?, nik_ao = ? WHERE cao = ?');
                $ok = $st && $st->bind_param('ssssss', $form['nama_ao'], $form['no_tlp'], $form['kode_unit'], $form['atasan'], $form['nik_ao'], $form['cao']) && $st->execute();
            }
            if ($ok) {
                ke_daftar('AO ' . $form['cao'] . ($aksi === 'tambah' ? ' sudah ditambahkan.' : ' sudah diperbarui.'));
            }
            $error[] = 'Gagal menyimpan: ' . $db->error;
        }
    }
}

/* ---------------- form ubah (GET ?ubah=cao) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ubah'])) {
    $data = ambil_ao($db, (string) $_GET['ubah']);
    if (!$data) {
        ke_daftar('AO tidak ditemukan.', 'danger');
    }
    $form = $data;
    $mode = 'ubah';
}

/* ---------------- daftar ---------------- */
$cari = isset($_GET['cari']) ? trim((string) $_GET['cari']) : '';
$hal = max(1, (int) (isset($_GET['hal']) ? $_GET['hal'] : 1));
$where = '';
$like = '%' . $cari . '%';
if ($cari !== '') {
    $where = 'WHERE ao.cao LIKE ? OR ao.nama_ao LIKE ? OR ao.nik_ao LIKE ? OR ao.kode_unit LIKE ? OR branch.unit LIKE ?';
}
$st = $db->prepare("SELECT COUNT(*) FROM ao LEFT JOIN branch ON branch.kode_branch = ao.kode_unit $where");
if ($cari !== '') $st->bind_param('sssss', $like, $like, $like, $like, $like);
$st->execute();
$total = (int) $st->get_result()->fetch_row()[0];
$jml_hal = max(1, (int) ceil($total / $per_halaman));
$hal = min($hal, $jml_hal);
$mulai = ($hal - 1) * $per_halaman;

$st = $db->prepare("SELECT ao.*, branch.unit AS nama_unit FROM ao LEFT JOIN branch ON branch.kode_branch = ao.kode_unit
    $where ORDER BY ao.kode_unit, ao.cao LIMIT $mulai, $per_halaman");
if ($cari !== '') $st->bind_param('sssss', $like, $like, $like, $like, $like);
$st->execute();
$daftar = $st->get_result()->fetch_all(MYSQLI_ASSOC);

// NIK atasan yang tersedia (tabel person) sebagai saran isian
$atasan = array();
$r = $db->query('SELECT NIK, Nama FROM person ORDER BY Nama');
while ($r && ($row = $r->fetch_assoc())) {
    $atasan[$row['NIK']] = $row['Nama'];
}

$pesan = isset($_SESSION['pesan_ao']) ? $_SESSION['pesan_ao'] : null;
unset($_SESSION['pesan_ao']);
$url_cari = 'ao.php?cari=' . urlencode($cari);
halaman_awal('Data AO');
?>
<?php if ($pesan): ?>
  <div class="alert alert-<?php echo h($pesan[0]); ?> py-2"><?php echo h($pesan[1]); ?></div>
<?php endif; ?>

<div class="card card-ni mb-3">
  <div class="card-header"><?php echo $mode === 'ubah' ? 'Ubah AO ' . h($form['cao']) : 'Tambah AO'; ?></div>
  <div class="card-body">
    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?php echo implode('<br>', array_map('h', $error)); ?></div>
    <?php endif; ?>
    <form method="post" action="ao.php" class="form-ni" autocomplete="off">
      <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_ao']); ?>">
      <input type="hidden" name="aksi" value="<?php echo h($mode); ?>">
      <div class="row g-2 mb-2">
        <label class="col-sm-3 col-form-label" for="cao">Kode AO <span class="text-danger">*</span></label>
        <div class="col-sm-4">
          <input type="text" name="cao" id="cao" class="form-control form-control-sm" maxlength="8" required pattern="[A-Za-z0-9]{1,8}"
                 value="<?php echo h($form['cao']); ?>" <?php echo $mode === 'ubah' ? 'readonly' : ''; ?>>
          <?php if ($mode === 'ubah'): ?><div class="form-text">Kode AO tidak bisa diubah.</div><?php endif; ?>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label class="col-sm-3 col-form-label" for="nama_ao">Nama AO <span class="text-danger">*</span></label>
        <div class="col-sm-7"><input type="text" name="nama_ao" id="nama_ao" class="form-control form-control-sm" maxlength="50" required value="<?php echo h($form['nama_ao']); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label class="col-sm-3 col-form-label" for="nik_ao">NIK AO</label>
        <div class="col-sm-5"><input type="text" name="nik_ao" id="nik_ao" class="form-control form-control-sm" maxlength="30" value="<?php echo h($form['nik_ao']); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label class="col-sm-3 col-form-label" for="no_tlp">No. Telepon</label>
        <div class="col-sm-4"><input type="text" name="no_tlp" id="no_tlp" class="form-control form-control-sm" maxlength="14" value="<?php echo h($form['no_tlp']); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label class="col-sm-3 col-form-label" for="kode_unit">Unit <span class="text-danger">*</span></label>
        <div class="col-sm-7">
          <?php if ($unit): ?>
            <select name="kode_unit" id="kode_unit" class="form-select form-select-sm" required>
              <option value="">- pilih unit -</option>
              <?php foreach ($unit as $kode => $nama): ?>
                <option value="<?php echo h($kode); ?>" <?php echo $form['kode_unit'] === (string) $kode ? 'selected' : ''; ?>><?php echo h($kode . ' - ' . $nama); ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="text" name="kode_unit" id="kode_unit" class="form-control form-control-sm" maxlength="4" required value="<?php echo h($form['kode_unit']); ?>">
            <div class="form-text">Tabel branch masih kosong; isi kode unit (4 karakter).</div>
          <?php endif; ?>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label class="col-sm-3 col-form-label" for="atasan">NIK Atasan</label>
        <div class="col-sm-5">
          <input type="text" name="atasan" id="atasan" class="form-control form-control-sm" maxlength="16" list="daftar_atasan" value="<?php echo h($form['atasan']); ?>">
          <datalist id="daftar_atasan">
            <?php foreach ($atasan as $nik => $nama): ?><option value="<?php echo h($nik); ?>"><?php echo h($nama); ?></option><?php endforeach; ?>
          </datalist>
        </div>
      </div>
      <div class="text-end">
        <?php if ($mode === 'ubah' || $error): ?><a href="ao.php" class="btn btn-outline-secondary btn-sm">Batal</a><?php endif; ?>
        <button type="submit" class="btn btn-ni btn-sm px-4"><?php echo $mode === 'ubah' ? 'Simpan Perubahan' : 'Tambah'; ?></button>
      </div>
    </form>
  </div>
</div>

<div class="card card-ni mw-100">
  <div class="card-header d-flex flex-wrap align-items-center gap-2">
    <span>Daftar AO</span>
    <span class="ms-auto small"><?php echo $total; ?> AO</span>
  </div>
  <div class="card-body">
    <form method="get" action="ao.php" class="d-flex flex-wrap gap-2 align-items-center mb-2">
      <input type="search" name="cari" class="form-control form-control-sm" style="max-width:20rem" placeholder="kode / nama / NIK / unit" value="<?php echo h($cari); ?>">
      <button type="submit" class="btn btn-outline-secondary btn-sm">Cari</button>
      <?php if ($cari !== ''): ?><a href="ao.php" class="btn btn-link btn-sm">Tampilkan semua</a><?php endif; ?>
    </form>
    <div class="table-responsive">
      <table class="table table-sm table-bordered table-hover table-ni align-middle mb-0">
        <thead>
          <tr><th>Kode AO</th><th>Nama AO</th><th>NIK AO</th><th>No. Telepon</th><th>Unit</th><th>NIK Atasan</th><th style="width:120px">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (!$daftar): ?>
          <tr><td colspan="7" class="text-center text-muted"><?php echo $cari !== '' ? 'Tidak ada AO yang cocok.' : 'Belum ada data AO.'; ?></td></tr>
        <?php endif; ?>
        <?php foreach ($daftar as $row): ?>
          <tr>
            <td><?php echo h($row['cao']); ?></td>
            <td><?php echo h($row['nama_ao']); ?></td>
            <td><?php echo h($row['nik_ao']); ?></td>
            <td><?php echo h($row['no_tlp']); ?></td>
            <td><?php echo h($row['kode_unit'] . ($row['nama_unit'] !== null ? ' - ' . $row['nama_unit'] : '')); ?></td>
            <td><?php echo h($row['atasan'] . (isset($atasan[$row['atasan']]) ? ' - ' . $atasan[$row['atasan']] : '')); ?></td>
            <td class="text-nowrap">
              <a href="ao.php?ubah=<?php echo urlencode($row['cao']); ?>" class="btn btn-outline-primary btn-sm py-0">Ubah</a>
              <form method="post" action="ao.php" class="d-inline" onsubmit="return confirm(<?php echo h(json_encode('Hapus AO ' . $row['cao'] . ' - ' . $row['nama_ao'] . '?')); ?>);">
                <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_ao']); ?>">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="cao" value="<?php echo h($row['cao']); ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm py-0">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($jml_hal > 1): ?>
    <nav class="mt-2">
      <ul class="pagination pagination-sm flex-wrap mb-0">
        <?php for ($i = 1; $i <= $jml_hal; $i++): ?>
          <li class="page-item<?php echo $i === $hal ? ' active' : ''; ?>"><a class="page-link" href="<?php echo h($url_cari . '&hal=' . $i); ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
