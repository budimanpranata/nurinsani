<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu admin > Pembukaan Unit > Tambah Unit: kode unit, nama unit, alamat ->
 *   1. branch (kode_branch, unit, alamat, tgl_open = hari ini; kolom lain kosong seperti versi lama)
 *   2. tabel_master: satu rekening per baris coa, rekening_gl = <kode unit>-<kode_rek>, posisi/normal/gr_neraca/gr_head/gr_sub/LINE_BALANCE
 *      dari coa, saldo 0
 * lalu menampilkan rekening tabel_master unit itu (kolom sama dengan versi lama, termasuk SALDO = awal_kredit).
 * Kode unit usulan tetap seperti versi lama: jumlah baris branch + 1, 3 digit (boleh diubah).
 * Dimigrasi 2026-11-05 (perintah user): tampilan Bootstrap 5, prepared statement, proses lalu kembali ke halaman ini
 * dengan pesan (dulu alert). Perbaikan yang disetujui user ("ok lanjut"):
 *   - kode unit yang sudah ada di branch atau tabel_master ditolak (dulu tetap diproses dan dilaporkan berhasil)
 *   - INSERT branch sekali (dulu diulang untuk setiap baris coa; hanya yang pertama berhasil)
 *   - nama rekening coa yang mengandung tanda petik kini ikut tersalin (dulu SQL-nya rusak dan baris itu hilang)
 * Rekening coa dengan kode_rek dobel tetap hanya tersalin sekali (kunci rekening_gl), sama dengan versi lama.
 * Hanya level admin (cek_admin.php).
 */
require_once __DIR__ . '/cek_admin.php';
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
require_once __DIR__ . '/../inc/tampilan.php';

if (empty($_SESSION['token_add_unit'])) {
    $_SESSION['token_add_unit'] = bin2hex(random_bytes(16));
}

$hit = db_satu("select count(kode_branch) AS last FROM branch");
$kode_unit = sprintf('%03s', (int) $hit['last'] + 1);
$isi = array('unit' => $kode_unit, 'nama' => '', 'alamat' => '');
$pesan = isset($_SESSION['pesan_add_unit']) ? $_SESSION['pesan_add_unit'] : null;
unset($_SESSION['pesan_add_unit']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($isi as $k => $v) {
        $isi[$k] = isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
    }
    $galat = '';
    if (!hash_equals($_SESSION['token_add_unit'], isset($_POST['token']) ? (string) $_POST['token'] : '')) {
        $galat = 'Formulir kedaluwarsa, silakan ulangi.';
    } elseif ($isi['unit'] === '' || $isi['nama'] === '' || $isi['alamat'] === '') {
        $galat = 'Maaf, isi form yang benar (kode unit, nama unit, dan alamat wajib diisi).';
    } elseif (!preg_match('/^[A-Za-z0-9]{1,4}$/', $isi['unit'])) {
        $galat = 'Kode unit maksimal 4 karakter, huruf/angka.';
    } elseif (db_satu("select kode_branch from branch where kode_branch = ?", 's', $isi['unit'])) {
        $galat = 'Kode unit ' . $isi['unit'] . ' sudah ada di tabel branch.';
    } elseif (db_satu("select rekening_gl from tabel_master where unit = ? limit 1", 's', $isi['unit'])) {
        $galat = 'Kode unit ' . $isi['unit'] . ' sudah punya rekening di tabel_master.';
    }
    if ($galat === '') {
        $unit = $isi['unit'];
        $ok_branch = db_jalankan("insert into branch(kode_branch,unit,alamat,tgl_open) values(?,?,?,?)", 'ssss',
            $unit, $isi['nama'], $isi['alamat'], date('Ymd'));
        // setiap rekening sendiri-sendiri seperti versi lama: kode_rek dobel di coa gagal (kunci) tanpa menghentikan yang lain
        $st = db_ni()->prepare("insert into tabel_master(rekening_gl,kode_rekening,nama_rekening,unit,posisi,normal,gr_neraca,gr_head,gr_sub,LINE_BALANCE)
            values(?,?,?,?,?,?,?,?,?,?)");
        $masuk = 0;
        $lewat = 0;
        foreach (db_semua("select * FROM coa") as $gl) {
            $v = array("$unit-" . $gl['kode_rek'], (string) $gl['kode_rek'], (string) $gl['nama_rek'], $unit, (string) $gl['posisi'],
                (string) $gl['normal'], (string) $gl['gr_neraca'], (string) $gl['gr_head'], (string) $gl['gr_sub'], (string) $gl['LINE_BALANCE']);
            $st->bind_param('ssssssssss', ...$v);
            try {
                $st->execute() ? $masuk++ : $lewat++;
            } catch (mysqli_sql_exception $e) {
                $lewat++;
            }
        }
        if ($ok_branch || $masuk > 0) {
            $_SESSION['pesan_add_unit'] = array('success', "Unit {$isi['nama']} Sudah di Tambahkan Kedalam System. "
                . "$masuk rekening dibuat di tabel_master" . ($lewat ? " ($lewat baris coa dilewati karena kode rekening dobel)" : '') . '.');
        } else {
            $_SESSION['pesan_add_unit'] = array('danger', 'Gagal input: ' . db_error_terakhir());
        }
        $_SESSION['token_add_unit'] = bin2hex(random_bytes(16));
        header('Location: add_unit.php?unit=' . urlencode($unit));
        exit;
    }
    $pesan = array('danger', $galat);
}

// rekening unit yang baru dibuat (versi lama: tampil setelah Input)
$lihat = isset($_GET['unit']) ? trim((string) $_GET['unit']) : '';
$rekening = $lihat !== '' ? db_semua("select * FROM tabel_master where unit = ? order by rekening_gl", 's', $lihat) : array();

halaman_awal('Pembukaan Unit Baru');
?>
<div class="card card-ni mb-3">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <?php if ($pesan): ?>
      <div class="alert alert-<?php echo h($pesan[0]); ?> py-2"><?php echo h($pesan[1]); ?></div>
    <?php endif; ?>
    <form method="post" action="add_unit.php" name="postform" class="form-ni" autocomplete="off">
      <input type="hidden" name="token" value="<?php echo h($_SESSION['token_add_unit']); ?>">
      <div class="row g-2 mb-2">
        <label for="unit" class="col-sm-4 col-form-label">Kode Unit <span class="text-danger">*</span></label>
        <div class="col-sm-3"><input name="unit" type="text" id="unit" class="form-control form-control-sm" maxlength="4" value="<?php echo h($isi['unit']); ?>" required></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="nama" class="col-sm-4 col-form-label">Nama Unit <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="nama" type="text" id="nama" class="form-control form-control-sm" maxlength="50" value="<?php echo h($isi['nama']); ?>" required></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="alamat" class="col-sm-4 col-form-label">Alamat <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="alamat" type="text" id="alamat" class="form-control form-control-sm" maxlength="200" value="<?php echo h($isi['alamat']); ?>" required></div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Input" class="btn btn-ni btn-sm px-4" data-konfirmasi="Tambah unit baru beserta rekening COA-nya?">Input</button>
      </div>
    </form>
  </div>
</div>
<?php if ($lihat !== ''): ?>
<div class="card card-ni mw-100">
  <div class="card-header">Rekening Unit <?php echo h($lihat); ?> <span class="ms-2 small fw-normal"><?php echo count($rekening); ?> rekening</span></div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-bordered table-hover table-ni align-middle mb-0">
        <thead>
          <tr><th>NOREK</th><th>NAMA</th><th>UNIT</th><th class="text-end">DEBIT</th><th class="text-end">CREDIT</th><th class="text-end">SALDO</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rekening as $data): ?>
          <tr>
            <td><?php echo h($data['rekening_gl']); ?></td>
            <td><?php echo h($data['nama_rekening']); ?></td>
            <td><?php echo h($data['unit']); ?></td>
            <td class="text-end"><?php echo number_format(compat_num($data['awal_debet'])); ?></td>
            <td class="text-end"><?php echo number_format(compat_num($data['awal_kredit'])); ?></td>
            <td class="text-end"><?php echo number_format(compat_num($data['awal_kredit'])); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rekening): ?>
          <tr><td colspan="6" class="text-center text-muted">Belum ada rekening untuk unit ini.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
<?php
halaman_akhir();
