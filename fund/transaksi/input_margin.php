<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Tambah Param Margin (dari fund/view/view_margin.php): input dana per periode ke param_deposito, param_rasio dan
 * param_dep (dipakai Posting Parameter fund/transaksi/posting.php). Isi yang ditulis sama dengan versi lama:
 *   - kode bulan = awalan + tahun + "-bulan" dari periode (yyyy-mm-dd): WD / MC / EMC, DP memakai kode nisbah (DP12-DP20),
 *     mis. WD2026-11, DP202026-11
 *   - DP: rate DP20=15, DP19=14, DP18=13, DP17..DP12=11; param_rasio.rasio_invest = angka nisbah (DP20 -> 20)
 *   - jenis lain: rate dan rasio_invest 0 (dulu nilai kosong yang disimpan MySQL sebagai 0)
 *   - param_deposito (code_bulan, code_jenis, nominal, buss_date, rate), param_rasio (+ rasio_invest), param_dep
 *     (code_bulan, code_jenis, nominal, buss_date)
 * Dimigrasi 2026-11-04 (perintah user): dulu Simpan selalu crash di PHP 8 (100 - "DP20" TypeError) sebelum menulis apa
 * pun; isian Kode Bulan berisi salinan nomor deposan dan cek duplikat memakai nilai itu (kode yang sudah ada -> insert
 * gagal -> Fatal, tabel lain bisa tertulis sebagian); tanpa cek login/level, SQL dari input, tanpa validasi, Kembali ke
 * master_fund.php. Kini: validasi, kode yang sudah ada ditolak, bila tabel ke-2/ke-3 gagal yang sudah tertulis dihapus
 * lagi (MyISAM, tanpa transaksi), simpan lalu kembali ke daftar.
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

const MARGIN_JENIS = array('WD' => 'TABUNGAN WADIAH', 'DP' => 'DEPOSITO MUDHARABAH', 'MC' => 'MICRO CAPITAL', 'EMC' => 'ESCROW MICRO CAPITAL');
/** Nisbah mudarobah => rate (versi lama). */
const MARGIN_NISBAH = array('DP20' => 15, 'DP19' => 14, 'DP18' => 13, 'DP17' => 11, 'DP16' => 11, 'DP15' => 11, 'DP14' => 11, 'DP13' => 11, 'DP12' => 11);

/** Simpan; hasil array(kode bulan, null) atau array(null, pesan galat). */
function simpan_margin()
{
    $jenis = isset($_POST['jenis']) ? (string) $_POST['jenis'] : '';
    $nisbah = isset($_POST['nisbah']) ? (string) $_POST['nisbah'] : '';
    $nominal = preg_replace('/[.,\s]/', '', isset($_POST['nominal']) ? (string) $_POST['nominal'] : '');
    $periode = isset($_POST['bulan']) ? trim((string) $_POST['bulan']) : '';
    if (!isset(MARGIN_JENIS[$jenis])) {
        return array(null, 'Pilih Jenis Dana.');
    }
    if ($jenis === 'DP' && !isset(MARGIN_NISBAH[$nisbah])) {
        return array(null, 'Pilih Nisbah Mudarobah untuk Deposito Mudharabah.');
    }
    if (!preg_match('/^\d{1,15}$/', $nominal) || (int) $nominal <= 0) {
        return array(null, 'Nominal harus angka lebih dari 0.');
    }
    $tgl = DateTime::createFromFormat('!Y-m-d', $periode);
    if (!$tgl || $tgl->format('Y-m-d') !== $periode) {
        return array(null, 'Isi Periode Bulan dengan tanggal yang benar.');
    }
    $awalan = $jenis === 'DP' ? $nisbah : $jenis;
    $kode = $awalan . substr($periode, 0, 4) . substr($periode, 4, 3);
    $rate = $jenis === 'DP' ? MARGIN_NISBAH[$nisbah] : 0;
    $rasio_invest = $jenis === 'DP' ? (int) substr($nisbah, 2, 2) : 0;

    if (db_satu("SELECT code_bulan FROM param_deposito WHERE code_bulan=?", 's', $kode)
        || db_satu("SELECT code_bulan FROM param_rasio WHERE code_bulan=?", 's', $kode)) {
        return array(null, 'Kode bulan ' . $kode . ' sudah ada.');
    }
    if (!db_jalankan("INSERT INTO param_deposito(code_bulan,code_jenis, nominal, buss_date, rate) VALUES(?,?,?,?,?)",
        'ssssi', $kode, $jenis, $nominal, $periode, $rate)) {
        return array(null, 'Data gagal disimpan: ' . db_error_terakhir());
    }
    if (!db_jalankan("INSERT INTO param_rasio(code_bulan,code_jenis, nominal, buss_date,rasio_invest,rate) VALUES(?,?,?,?,?,?)",
            'ssssii', $kode, $jenis, $nominal, $periode, $rasio_invest, $rate)
        || !db_jalankan("INSERT INTO param_dep(code_bulan,code_jenis, nominal, buss_date) VALUES(?,?,?,?)", 'ssss', $kode, $jenis, $nominal, $periode)) {
        $galat = db_error_terakhir();
        // MyISAM tanpa transaksi: batalkan yang sudah tertulis
        db_jalankan("DELETE FROM param_rasio WHERE code_bulan=? AND ket=''", 's', $kode);
        db_jalankan("DELETE FROM param_deposito WHERE code_bulan=? AND ket=''", 's', $kode);
        return array(null, 'Data gagal disimpan: ' . $galat);
    }
    return array($kode, null);
}

$pesan = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['input'])) {
    list($kode, $galat) = simpan_margin();
    if ($galat === null) {
        $_SESSION['pesan_margin'] = array('success', 'Data ' . $kode . ' berhasil disimpan.');
        header('Location: ../view/view_margin.php');
        exit;
    }
    $pesan = $galat;
}
$isi = function ($k) {
    return isset($_POST[$k]) ? (string) $_POST[$k] : '';
};

halaman_awal('Parameter Margin Nur Insani');
?>
<?php if ($pesan !== null): ?>
<div class="alert alert-danger"><?php echo h($pesan); ?></div>
<?php endif; ?>
<form method="post" action="input_margin.php" class="card card-ni" id="form_margin">
  <div class="card-header">Input Parameter Margin Nur Insani</div>
  <div class="card-body row g-3">
    <div class="col-12">
      <label class="form-label" for="kode_bulan">Kode Bulan</label>
      <input type="text" id="kode_bulan" class="form-control" placeholder="Terisi otomatis dari Jenis Dana, Nisbah dan Periode" readonly>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="jenis">Jenis Dana</label>
      <select name="jenis" id="jenis" class="form-select" required>
        <option value="">Pilih Dana</option>
        <?php foreach (MARGIN_JENIS as $k => $v): ?>
        <option value="<?php echo $k; ?>"<?php echo $isi('jenis') === $k ? ' selected' : ''; ?>><?php echo $v; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-6" id="kotak_nisbah">
      <label class="form-label" for="nisbah">Nisbah Mudarobah</label>
      <select name="nisbah" id="nisbah" class="form-select">
        <option value="">Pilih Nisbah Mudarobah</option>
        <?php foreach (array_keys(MARGIN_NISBAH) as $k): ?>
        <option value="<?php echo $k; ?>"<?php echo $isi('nisbah') === $k ? ' selected' : ''; ?>>Nisbah <?php echo substr($k, 2); ?>%</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="nominal">Nominal</label>
      <input type="text" name="nominal" id="nominal" inputmode="numeric" value="<?php echo h($isi('nominal')); ?>" placeholder="isi Nominal" class="form-control" required>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="bulan">Periode Bulan</label>
      <input type="date" name="bulan" id="bulan" value="<?php echo h($isi('bulan')); ?>" class="form-control" required>
    </div>
    <div class="col-12 d-flex gap-2">
      <button type="submit" name="input" value="1" class="btn btn-ni">Simpan</button>
      <a href="../view/view_margin.php" class="btn btn-secondary">Kembali</a>
    </div>
  </div>
</form>
<?php
halaman_akhir(array('assets/app/fund_margin_input.js'));
