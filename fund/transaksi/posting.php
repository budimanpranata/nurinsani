<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu admin > Seting Parameter > Posting Parameter (dipindah dari level fund 2026-11-07): hitung parameter bagi hasil periode berjalan dari Param Financial
 * terbaru (param_finan) dan Param Margin yang belum diposting (param_deposito / param_rasio ket kosong), lalu isi ulang
 * param_dep (dibaca Realisasi Bagi Hasil dan laporan Baghas & Eq Rate) dan tandai semuanya 'Post'.
 *
 * Rumus sama dengan versi lama:
 *   - tot_fin, margin, hari = nomi_murab, marigin_murab, DATEDIFF(sd, dari) Param Financial terbaru (inc/hari_dep.php)
 *   - wadiah1 = nominal baris WD pertama, mudarobah = jumlah nominal DP; nominal WD diganti (tot_fin - mudarobah) kecuali
 *     2 x wadiah1 <= tot_fin - mudarobah (rumus lama; hasilnya bisa negatif bila mudarobah > tot_fin)
 *   - per kode bulan: margin_murab = nominal / tot_fin x margin, margin_bagi = nominal / tot x margin (tot = tot_fin)
 *   - per param_rasio: rasio_cor = 100 - rasio_invest, nomi_invest = rasio_invest% x margin_bagi,
 *     eq = nomi_invest / nominal x 365 / hari, nomi_cor = rasio_cor% x margin_bagi,
 *     ekspek = rate% x nominal x hari / 365, rasio_eks = round(ekspek / margin_bagi x 100)
 *   - param_dep dikosongkan lalu diisi salinan param_rasio periode ini; ket 'Post'; code_bulan param_dep DP = '1', WD = '2'
 * Perbaikan atas perintah user 2026-11-04:
 *   - (A) param_rasio.margin_murab tidak lagi ditimpa 0 (dulu diisi $marginnn dari loop param_dep yang tidak pernah
 *     berisi), jadi param_dep.margin_murab = margin hasil hitungan
 *   - (B) ekspek memakai jumlah hari periode (dulu ditulis mati 31), sama dengan eq
 *   - Posting ganda / tanpa data belum-posting dulu tetap mengosongkan param_dep (eq hilang); kini ditolak, dengan kunci
 *     GET_LOCK; Param Financial wajib ada dengan nominal > 0 dan hari 1-366
 *   - pembagian dengan nol mengikuti PHP 5 (hasil 0, compat_bagi), dulu crash DivisionByZeroError di PHP 8 di tengah
 *     proses; semua angka dihitung dulu baru ditulis (tabel MyISAM, tanpa transaksi)
 *   - hari_dep (Jumlah Hari Bagi Hasil) disimpan hanya bila posting berhasil
 *   - wajib login level fund (dulu tanpa cek), prepared statement, tanpa enkripsi URL
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
$userid = (string) $_SESSION['userid'];

/** Nilai bigint seperti yang disimpan MySQL dari angka pecahan (dibulatkan). */
function bulat_db($x)
{
    return (int) round(compat_num($x));
}

/** Rencana posting (semua angka) atau array('galat' => pesan). */
function rencana_posting()
{
    $pf = hari_dep_periode_terbaru();
    if (!$pf) {
        return array('galat' => 'Param Financial belum diisi.');
    }
    $tot_fin = compat_num($pf['nomi_murab']);
    $margin = compat_num($pf['marigin_murab']);
    $hari = (int) $pf['hari'];
    if ($tot_fin <= 0) {
        return array('galat' => 'Nominal murabahah Param Financial ' . $pf['code_param'] . ' harus lebih dari 0.');
    }
    if ($hari < 1 || $hari > 366) {
        return array('galat' => 'Jumlah hari Param Financial ' . $pf['code_param'] . ' harus 1-366 (sekarang ' . $hari . ').');
    }
    $deposito = db_semua("select code_bulan, code_jenis, nominal from param_deposito where ket=''");
    if (!$deposito) {
        return array('galat' => 'Tidak ada Param Margin yang belum diposting.');
    }
    $wd = db_satu("select nominal from param_deposito where code_jenis='WD' and ket='' limit 1");
    $dp = db_satu("select sum(nominal) as n from param_deposito where code_jenis='DP' and ket=''");
    $wadiah1 = $wd ? compat_num($wd['nominal']) : null;
    $mudarobah = compat_num($dp ? $dp['n'] : 0);
    $kurang = $tot_fin - $mudarobah;
    // rumus lama: wadiah tetap bila wadiah1 <= kurang - wadiah1, selain itu wadiah = kurang; tot selalu = tot_fin
    $wadiah = ($wadiah1 === null || $wadiah1 <= $kurang - $wadiah1) ? $wadiah1 : $kurang;
    $tot = $tot_fin;

    $margin_bagi = array();
    $rencana = array('pf' => $pf, 'hari' => $hari, 'wadiah' => $wadiah, 'deposito' => array(), 'rasio' => array());
    foreach ($deposito as $r) {
        $kode = (string) $r['code_bulan'];
        $n = $r['code_jenis'] === 'WD' && $wadiah !== null ? bulat_db($wadiah) : compat_num($r['nominal']);
        $mrg = $n / $tot_fin * $margin;
        $mb = $n / $tot * $margin;
        $margin_bagi[$kode] = bulat_db($mb);
        $rencana['deposito'][] = array('kode' => $kode, 'margin_murab' => (string) $mrg, 'margin_bagi' => (string) $mb);
    }
    foreach (db_semua("select code_bulan, code_jenis, nominal, margin_bagi, rasio_invest, rate from param_rasio where ket=''") as $r) {
        $kode = (string) $r['code_bulan'];
        // nilai param_rasio sesudah update di atas (versi lama membacanya ulang dari tabel): nominal WD = wadiah,
        // margin_bagi dari kode bulan yang sama di param_deposito (bila tidak ada, nilai tersimpan)
        $n = $r['code_jenis'] === 'WD' && $wadiah !== null ? bulat_db($wadiah) : compat_num($r['nominal']);
        $mb = isset($margin_bagi[$kode]) ? $margin_bagi[$kode] : compat_num($r['margin_bagi']);
        $rasio_invest = compat_num($r['rasio_invest']);
        $rasio_cor = 100 - $rasio_invest;
        $nomi_invest = ($rasio_invest / 100) * $mb;
        $eq = compat_bagi($nomi_invest, $n) * (365 / $hari);
        $nomi_cor = ($rasio_cor / 100) * $mb;
        $ekspek = (compat_num($r['rate']) / 100) * $n * $hari / 365;
        $rasio_eks = round(compat_num(compat_bagi($ekspek, $mb) * 100));
        $rencana['rasio'][] = array('kode' => $kode, 'nomi_invest' => (string) $nomi_invest, 'rasio_cor' => (string) $rasio_cor,
            'eq' => (string) $eq, 'nomi_cor' => (string) $nomi_cor, 'ekspek' => (string) $ekspek, 'rasio_eks' => (string) $rasio_eks);
    }
    return $rencana;
}

/** Tulis rencana; hasil null bila berhasil atau pesan galat. */
function jalankan_posting($p, $userid)
{
    $gagal = db_jumlah_gagal();
    if ($p['wadiah'] !== null) {
        db_jalankan("update param_deposito set nominal =? where code_jenis='WD' and ket=''", 's', (string) bulat_db($p['wadiah']));
        db_jalankan("update param_rasio set nominal =? where code_jenis='WD' and ket=''", 's', (string) bulat_db($p['wadiah']));
    }
    foreach ($p['deposito'] as $d) {
        db_jalankan("update param_deposito set margin_murab=?, margin_bagi=? where code_bulan=? and ket=''", 'sss', $d['margin_murab'], $d['margin_bagi'], $d['kode']);
        db_jalankan("update param_rasio set margin_murab=?, margin_bagi=? where code_bulan=? and ket=''", 'sss', $d['margin_murab'], $d['margin_bagi'], $d['kode']);
    }
    foreach ($p['rasio'] as $r) {
        db_jalankan("update param_rasio set nomi_invest=?, rasio_cor=?, eq=?, nomi_cor=?, ekspek=?, rasio_eks=? where code_bulan=? and ket=''",
            'sssssss', $r['nomi_invest'], $r['rasio_cor'], $r['eq'], $r['nomi_cor'], $r['ekspek'], $r['rasio_eks'], $r['kode']);
    }
    if (db_jumlah_gagal() !== $gagal) {
        return 'Posting gagal saat menghitung parameter: ' . db_error_terakhir() . '. Data belum ditandai Post; periksa lalu ulangi.';
    }
    db_jalankan("delete from param_dep");
    db_jalankan("insert into param_dep select * from param_rasio where ket =''");
    db_jalankan("update param_deposito set ket='Post' where ket=''");
    db_jalankan("update param_dep set ket='Post' where ket=''");
    db_jalankan("update param_rasio set ket='Post' where ket=''");
    db_jalankan("update param_dep set code_bulan='1' where code_jenis='DP'");
    db_jalankan("update param_dep set code_bulan='2' where code_jenis='WD'");
    if (db_jumlah_gagal() !== $gagal) {
        return 'Posting gagal saat mengisi param_dep: ' . db_error_terakhir() . '.';
    }
    hari_dep_simpan($p['hari'], 'posting', $userid);
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['posting'])) {
    $kunci = db_satu("SELECT GET_LOCK('fund_posting_parameter', 10) AS ok");
    if (!$kunci || (int) $kunci['ok'] !== 1) {
        $pesan = array('warning', 'Posting sedang diproses user lain, coba lagi sebentar.');
    } else {
        $p = rencana_posting();
        if (isset($p['galat'])) {
            $pesan = array('warning', $p['galat']);
        } else {
            $galat = jalankan_posting($p, $userid);
            $pesan = $galat === null
                ? array('success', 'Proses Posting Selesai: ' . count($p['deposito']) . ' kode bulan, Param Financial ' . $p['pf']['code_param'] . ', ' . $p['hari'] . ' hari.')
                : array('danger', $galat);
        }
        db_satu("SELECT RELEASE_LOCK('fund_posting_parameter') AS ok");
    }
    $_SESSION['pesan_posting'] = $pesan;
    header('Location: posting.php');
    exit;
}
$pesan = isset($_SESSION['pesan_posting']) ? $_SESSION['pesan_posting'] : null;
unset($_SESSION['pesan_posting']);
$pf = hari_dep_periode_terbaru();
$daftar = db_semua("select buss_date, code_bulan, code_jenis, nominal, ket from param_deposito where ket='' order by buss_date asc");

halaman_awal('Proses Posting Parameter');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo h($pesan[0]); ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni mw-100">
  <div class="card-header">Proses Posting Parameter</div>
  <div class="card-body">
    <p class="mb-2">Param Financial yang dipakai:
      <?php if ($pf): ?>
      <strong><?php echo h($pf['code_param']); ?></strong> &mdash; murabahah <?php echo number_format(compat_num($pf['nomi_murab'])); ?>,
      margin <?php echo number_format(compat_num($pf['marigin_murab'])); ?>, <?php echo (int) $pf['hari']; ?> hari
      <?php else: ?>
      <span class="text-danger">belum diisi (Seting Parameter &gt; Param Financial)</span>
      <?php endif; ?>
    </p>
    <table class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr><th>Tanggal</th><th>Kode Proses</th><th>Jenis Dana</th><th class="text-end">Nominal</th><th>Keterangan</th></tr>
      </thead>
      <tbody>
        <?php if (!$daftar): ?>
        <tr><td colspan="5" class="text-center text-muted">Tidak ada Param Margin yang belum diposting</td></tr>
        <?php endif; ?>
        <?php foreach ($daftar as $r): ?>
        <tr>
          <td class="text-center"><?php echo h($r['buss_date']); ?></td>
          <td class="text-center"><?php echo h($r['code_bulan']); ?></td>
          <td><?php echo h($r['code_jenis']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($r['nominal']), 2, '.', ','); ?></td>
          <td class="text-center"><?php echo h($r['ket']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if ($daftar && $pf): ?>
    <form method="post" action="posting.php" class="text-center" onsubmit="if (!confirm('Apakah Anda Yakin?')) return false; this.querySelector('button').disabled = true;">
      <input type="hidden" name="posting" value="1">
      <button type="submit" class="btn btn-ni">POSTING PARAMETER</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
