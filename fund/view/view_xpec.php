<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu admin > Seting Parameter > expectation (Perhitungan Nisbah): simulasi, hanya membaca.
 *   - pilih Tanggal Periode (tanggal Param Margin yang ada di param_rasio); tabel 1 = hasil Posting Parameter per dana
 *     (porsi investor: rasio, nominal, eq %; porsi koperasi: rasio, nominal)
 *   - isi ekspektasi rate per nisbah (20% .. 12%); tabel 2 (khusus DP) = harapan = ekspektasi% x nominal x hari / 365
 *     dan selisih = nominal porsi investor - harapan
 *   - jumlah hari (keputusan user 2026-11-04): Param Financial yang periodenya mencakup tanggal itu (dari <= tanggal <= sd),
 *     bila tidak ada Jumlah Hari Bagi Hasil (hari_dep, cadangan 30)
 * Dimigrasi 2026-11-04 (perintah user): dulu param_finan 'NI2018-07' ditulis mati (hasilnya ditimpa, tidak terpakai);
 * hari dari param_finan yang tanggal Dari-nya sama persis (hampir selalu tidak ada -> harapan 0); elseif
 * ($rasio_invest=-"16") penugasan -> nisbah 16/15/14 memakai isian yang sama, isian 15% bernama expt_16, nisbah 13/12 tanpa
 * isian; $new_rasio (tidak dipakai) bisa DivisionByZeroError; saran tanggal autosuggest_tgl.php rusak (dihapus); tidak
 * ditemukan -> view_expec.php (tidak ada). Kolom "Margin Murabahah" yang isinya nominal dana kini berjudul "Nominal Dana"
 * dan tabel 1 menampilkan Margin Bagi (usulan disetujui user).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/hari_dep.php';
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "admin") {
    header("location:../../index.html");
    exit;
}
include_once __DIR__ . '/../../function.php';

/** Nisbah => ekspektasi bawaan (sama dengan isian versi lama; 13% dan 12% mengikuti rate Param Margin). */
const XPEC_BAWAAN = array(20 => 15, 19 => 14, 18 => 13, 17 => 12, 16 => 11, 15 => 11, 14 => 11, 13 => 11, 12 => 11);

$periode = array_column(db_semua("SELECT DISTINCT DATE(buss_date) AS tgl FROM param_rasio ORDER BY tgl DESC"), 'tgl');
$tanggal = isset($_GET['tanggal']) ? (string) $_GET['tanggal'] : '';
if (!in_array($tanggal, $periode, true)) {
    $tanggal = '';
}
$expt = array();
foreach (XPEC_BAWAAN as $n => $bawaan) {
    $v = isset($_GET['expt_' . $n]) ? str_replace(',', '.', trim((string) $_GET['expt_' . $n])) : '';
    $expt[$n] = is_numeric($v) ? (float) $v : $bawaan;
}

$data = array();
$hari = null;
$sumber_hari = '';
if ($tanggal !== '') {
    $data = db_semua("SELECT code_bulan, nama_jenis, param_rasio.code_jenis, nominal, margin_bagi, rasio_invest, nomi_invest, eq, rasio_cor, nomi_cor, buss_date
        FROM param_rasio inner join jenis_dana on jenis_dana.code_jenis = param_rasio.code_jenis
        WHERE buss_date = ? order by rasio_invest Desc", 's', $tanggal);
    $pf = db_satu("select code_param, DATEDIFF(sd, dari) as hari from param_finan where dari <= ? and sd >= ? order by code_param desc limit 1", 'ss', $tanggal, $tanggal);
    if ($pf) {
        $hari = (int) $pf['hari'];
        $sumber_hari = 'Param Financial ' . $pf['code_param'];
    } else {
        $n = hari_dep_ambil();
        $hari = $n !== null ? $n : HARI_DEP_CADANGAN;
        $sumber_hari = $n !== null ? 'Jumlah Hari Bagi Hasil (tidak ada Param Financial yang mencakup tanggal ini)' : 'cadangan 30 hari (Param Financial dan Jumlah Hari Bagi Hasil belum ada)';
    }
}
/** Angka tampilan ekspektasi: bulat tanpa desimal, pecahan dengan 2 desimal. */
function angka_xpec($x)
{
    return floor($x) == $x ? number_format($x) : number_format($x, 2);
}

halaman_awal('Perhitungan Nisbah');
?>
<div class="card card-ni mw-100">
  <div class="card-header">Perhitungan Nisbah</div>
  <div class="card-body">
    <form method="get" action="view_xpec.php" class="row g-2 align-items-end mb-3">
      <div class="col-sm-3">
        <label class="form-label" for="tanggal">Tanggal Periode</label>
        <select name="tanggal" id="tanggal" class="form-select" required>
          <option value="">Pilih tanggal</option>
          <?php foreach ($periode as $p): ?>
          <option value="<?php echo h($p); ?>"<?php echo $p === $tanggal ? ' selected' : ''; ?>><?php echo h(tgl_indo($p)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php foreach (XPEC_BAWAAN as $n => $bawaan): ?>
      <div class="col-4 col-sm-1">
        <label class="form-label small" for="expt_<?php echo $n; ?>">Nisbah <?php echo $n; ?>%</label>
        <input type="text" name="expt_<?php echo $n; ?>" id="expt_<?php echo $n; ?>" value="<?php echo h(angka_xpec($expt[$n])); ?>" inputmode="decimal" class="form-control form-control-sm">
      </div>
      <?php endforeach; ?>
      <div class="col-sm-auto">
        <button type="submit" class="btn btn-ni">Search</button>
      </div>
      <div class="col-12 small text-muted">Isian Nisbah = ekspektasi rate (%) per nisbah mudarobah.</div>
    </form>

    <?php if ($tanggal !== '' && !$data): ?>
    <div class="alert alert-warning">Data tidak ditemukan.</div>
    <?php elseif ($data): ?>
    <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th rowspan="2">Periode</th><th rowspan="2">Dana</th><th rowspan="2" class="text-end">Nominal Dana</th><th rowspan="2" class="text-end">Margin Bagi</th>
          <th colspan="3" class="text-center">INVESTOR PORTION</th><th colspan="2" class="text-center">COOPERATIVE PORTION</th>
        </tr>
        <tr><th class="text-center">Ratio</th><th class="text-end">Nominal</th><th class="text-center">Eq in %</th><th class="text-center">Ratio</th><th class="text-end">Nominal</th></tr>
      </thead>
      <tbody>
        <?php foreach ($data as $r): ?>
        <tr>
          <td><?php echo h(tgl_indo((string) $r['buss_date'])); ?></td>
          <td><?php echo h($r['nama_jenis']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['nominal'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['margin_bagi'])); ?></td>
          <td class="text-center"><?php echo h($r['rasio_invest']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['nomi_invest'])); ?></td>
          <td class="text-center"><?php echo number_format(compat_num($r['eq']) * 100, 2); ?></td>
          <td class="text-center"><?php echo h($r['rasio_cor']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['nomi_cor'])); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>

    <h2 class="fs-6 mt-4">Ekspektasi Nisbah (Deposito Mudharabah)</h2>
    <p class="small text-muted mb-2">Jumlah hari: <?php echo (int) $hari; ?> hari (<?php echo h($sumber_hari); ?>).</p>
    <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr>
          <th rowspan="2">Periode</th><th rowspan="2">Dana</th><th rowspan="2" class="text-end">Nominal Dana</th>
          <th colspan="3" class="text-center">INVESTOR PORTION</th><th colspan="3" class="text-center">EKSPEKTASI</th>
        </tr>
        <tr><th class="text-center">Ratio</th><th class="text-end">Nominal</th><th class="text-center">Eq in %</th><th class="text-center">Eq in %</th><th class="text-end">Nominal</th><th class="text-end">Selisih</th></tr>
      </thead>
      <tbody>
        <?php foreach ($data as $r): if ((string) $r['code_jenis'] !== 'DP') { continue; }
            $rasio = (int) $r['rasio_invest'];
            $e = isset($expt[$rasio]) ? $expt[$rasio] : null;
            $nominal = compat_num($r['nominal']);
            $harapan = $e === null ? 0 : $e / 100 * $nominal * $hari / 365;
            $selisih = compat_num($r['nomi_invest']) - $harapan; ?>
        <tr>
          <td><?php echo h(tgl_indo((string) $r['buss_date'])); ?></td>
          <td><?php echo h($r['nama_jenis']); ?></td>
          <td class="text-end"><?php echo number_format($nominal); ?></td>
          <td class="text-center"><?php echo h($r['rasio_invest']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['nomi_invest'])); ?></td>
          <td class="text-center"><?php echo number_format(compat_num($r['eq']) * 100, 2); ?></td>
          <td class="text-center"><?php echo $e === null ? '-' : h(angka_xpec($e)); ?></td>
          <td class="text-end"><?php echo number_format($harapan); ?></td>
          <td class="text-end"><?php echo number_format($selisih); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
