<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Tambah Param Financial (dari fund/view/view_finan.php): satu baris param_finan per periode, dipakai Posting Parameter
 * (periode terbaru; jumlah hari = DATEDIFF(sd, dari) disimpan ke hari_dep). Isi yang ditulis sama dengan versi lama:
 * code_param = "NI" + tahun + "-bulan" dari Periode Dari (mis. NI2026-11), nomi_murab, marigin_murab,
 * buss_date = dari = Periode Dari, sd = Periode S.d.
 * Dimigrasi 2026-11-04 (perintah user): dulu cek duplikat membandingkan dengan salinan nomor deposan + karakter tab
 * (tidak pernah cocok) sehingga kode yang sudah ada -> insert PK gagal -> Fatal (or die(mysqli_error())); tanpa cek
 * level, SQL dari input, tanpa validasi (s.d sebelum dari -> jumlah hari Posting negatif; "1.000.000" tersimpan 1).
 * Kini: validasi (nominal > 0 karena Posting membagi dengan nominal, margin >= 0, pemisah ribuan dibuang; s.d sesudah
 * dari dan paling lama 366 hari, batas hari_dep), kode yang sudah ada ditolak, simpan lalu kembali ke daftar.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "admin") {
    header("location:../../index.html");
    exit;
}

/** Tanggal Y-m-d yang sah, atau null. */
function tanggal_finan($v)
{
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    return $d && $d->format('Y-m-d') === $v ? $d : null;
}

/** Simpan; hasil array(kode, null) atau array(null, pesan galat). */
function simpan_finan()
{
    $angka = function ($k) {
        return preg_replace('/[.,\s]/', '', isset($_POST[$k]) ? (string) $_POST[$k] : '');
    };
    $nominal = $angka('nominal');
    $margin = $angka('margin');
    $dari = isset($_POST['bulan']) ? trim((string) $_POST['bulan']) : '';
    $sd = isset($_POST['bulan_dua']) ? trim((string) $_POST['bulan_dua']) : '';
    if (!preg_match('/^\d{1,15}$/', $nominal) || (int) $nominal <= 0) {
        return array(null, 'Murabahah Financing harus angka lebih dari 0.');
    }
    if (!preg_match('/^\d{1,15}$/', $margin)) {
        return array(null, 'Margin Murabahah harus angka.');
    }
    $t_dari = tanggal_finan($dari);
    $t_sd = tanggal_finan($sd);
    if (!$t_dari || !$t_sd) {
        return array(null, 'Isi Periode Dari dan Periode S.d dengan tanggal yang benar.');
    }
    $hari = (int) $t_dari->diff($t_sd)->format('%r%a');
    if ($hari < 1 || $hari > 366) {
        return array(null, 'Periode S.d harus sesudah Periode Dari, paling lama 366 hari (sekarang ' . $hari . ' hari).');
    }
    $kode = 'NI' . substr($dari, 0, 4) . substr($dari, 4, 3);
    if (db_satu("SELECT code_param FROM param_finan WHERE code_param=?", 's', $kode)) {
        return array(null, 'Kode bulan ' . $kode . ' sudah ada.');
    }
    if (!db_jalankan("INSERT INTO param_finan(code_param, nomi_murab, marigin_murab,buss_date,dari,sd) VALUES(?,?,?,?,?,?)",
        'ssssss', $kode, $nominal, $margin, $dari, $dari, $sd)) {
        return array(null, 'Data gagal disimpan: ' . db_error_terakhir());
    }
    return array($kode, null);
}

$pesan = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['input'])) {
    list($kode, $galat) = simpan_finan();
    if ($galat === null) {
        $_SESSION['pesan_finan'] = array('success', 'Data ' . $kode . ' berhasil disimpan.');
        header('Location: ../view/view_finan.php');
        exit;
    }
    $pesan = $galat;
}
$isi = function ($k) {
    return isset($_POST[$k]) ? (string) $_POST[$k] : '';
};

halaman_awal('Parameter Financing Nur Insani');
?>
<?php if ($pesan !== null): ?>
<div class="alert alert-danger"><?php echo h($pesan); ?></div>
<?php endif; ?>
<form method="post" action="input_finan.php" class="card card-ni" id="form_finan">
  <div class="card-header">Input Parameter Financing Nur Insani</div>
  <div class="card-body row g-3">
    <div class="col-12">
      <label class="form-label" for="kode_bulan">Kode Bulan</label>
      <input type="text" id="kode_bulan" class="form-control" placeholder="Terisi otomatis dari Periode Dari" readonly>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="nominal">Murabahah Financing</label>
      <input type="text" name="nominal" id="nominal" inputmode="numeric" value="<?php echo h($isi('nominal')); ?>" placeholder="Nominal" class="form-control" required>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="margin">Margin Murabahah</label>
      <input type="text" name="margin" id="margin" inputmode="numeric" value="<?php echo h($isi('margin')); ?>" placeholder="Margin Murabahah" class="form-control" required>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="bulan">Periode Dari</label>
      <input type="date" name="bulan" id="bulan" value="<?php echo h($isi('bulan')); ?>" class="form-control" required>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="bulan_dua">Periode S.d <span class="small text-muted" id="jumlah_hari"></span></label>
      <input type="date" name="bulan_dua" id="bulan_dua" value="<?php echo h($isi('bulan_dua')); ?>" class="form-control" required>
    </div>
    <div class="col-12 d-flex gap-2">
      <button type="submit" name="input" value="1" class="btn btn-ni">Simpan</button>
      <a href="../view/view_finan.php" class="btn btn-secondary">Kembali</a>
    </div>
  </div>
</form>
<?php
halaman_akhir(array('assets/app/fund_finan_input.js'));
