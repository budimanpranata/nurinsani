<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu fund > EOD (EOD deposito), tanggal = tanggal sistem (sesi stgl_system, keputusan user 2026-11-04). Hanya bisa
 * bila tidak ada tabel_transaksi unit user yang belum diposting (sama dengan versi lama). Proses:
 *   1. semua deposito code_bulan = '1' (dicocokkan dengan param_dep DP '1' hasil Posting Parameter; penempatan baru
 *      baru masuk daftar Realisasi Bagi Hasil sesudah EOD) - sama dengan versi lama
 *   2. tanggal sistem hari Jumat: jadwal bagi hasil (deposito_detail tgl_jatuh_tempo & tgl_bayar) Sabtu/Minggu berikutnya
 *      dipindah ke Senin, kecuali bila Senin itu sudah bulan berikutnya -> dipindah ke Jumat (tetap di bulannya)
 *   3. tanggal terakhir bulan (dari tanggal sistem) Sabtu/Minggu: jadwal di tanggal itu dipindah ke Jumat sebelumnya
 * Perbaikan atas perintah user 2026-11-04: dulu syarat "bukan akhir bulan" membandingkan jumlah hari bulan dengan nomor
 * bulan (selalu beda) sehingga EOD Jumat memindah jadwal akhir bulan Sabtu/Minggu ke Senin bulan berikutnya sebelum
 * aturan akhir bulan sempat berlaku; aturan akhir bulan memakai bulan jam server, bukan tanggal EOD; tanggal diketik
 * bebas tanpa validasi (teks rusak dibaca tanggal 1970). Kini juga: login + level fund, prepared statement, GET_LOCK,
 * ringkasan jumlah baris yang diubah.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
ob_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "fund") {
    header("location:../index.html");
    exit;
}
include_once __DIR__ . '/../function.php';
$userid = (string) $_SESSION['userid'];
$tgl = substr(isset($_SESSION['stgl_system']) ? (string) $_SESSION['stgl_system'] : '', 0, 10);
$d_tgl = DateTime::createFromFormat('!Y-m-d', $tgl);
if (!$d_tgl || $d_tgl->format('Y-m-d') !== $tgl) {
    $d_tgl = null;
}
$u = db_satu("select kode_branch from user where userid=?", 's', $userid);
$unit = $u ? (string) $u['kode_branch'] : '';

/** Jalankan UPDATE prepared; hasil jumlah baris berubah, atau false bila gagal. */
function ubah_eod($sql, $tipe = '', ...$param)
{
    $st = db_ni()->prepare($sql);
    if (!$st || ($tipe !== '' && !$st->bind_param($tipe, ...$param)) || !$st->execute()) {
        $GLOBALS['__db_error_terakhir'] = $st ? $st->error : db_ni()->error;
        return false;
    }
    return $st->affected_rows;
}

/** Pindah jadwal bagi hasil tanggal $dari ke $ke; hasil teks ringkasan atau false. */
function pindah_jadwal($dari, $ke)
{
    $n = ubah_eod("UPDATE deposito_detail set tgl_jatuh_tempo=?, tgl_bayar=? where tgl_jatuh_tempo=?", 'sss', $ke, $ke, $dari);
    return $n === false ? false : "jadwal $dari -> $ke: $n baris";
}

/** Proses EOD tanggal $d; hasil array(berhasil, daftar ringkasan). */
function proses_eod(DateTime $d)
{
    $catat = array();
    $n = ubah_eod("UPDATE deposito set code_bulan ='1'");
    if ($n === false) {
        return array(false, array('Gagal: ' . db_error_terakhir()));
    }
    $catat[] = "deposito code_bulan '1': $n baris diubah";
    $pindah = array();
    if ($d->format('N') === '5') {
        // Jumat: Sabtu/Minggu -> Senin, kecuali Senin sudah bulan berikutnya -> Jumat
        $senin = (clone $d)->modify('+3 days');
        foreach (array(1, 2) as $tambah) {
            $libur = (clone $d)->modify("+$tambah days");
            $ke = $senin->format('Y-m') === $libur->format('Y-m') ? $senin : $d;
            $pindah[$libur->format('Y-m-d')] = $ke->format('Y-m-d');
        }
    }
    $akhir = (clone $d)->modify('last day of this month');
    $hari_akhir = $akhir->format('N');
    if (($hari_akhir === '6' || $hari_akhir === '7') && !isset($pindah[$akhir->format('Y-m-d')])) {
        // akhir bulan Sabtu/Minggu -> Jumat sebelumnya
        $pindah[$akhir->format('Y-m-d')] = (clone $akhir)->modify($hari_akhir === '6' ? '-1 day' : '-2 days')->format('Y-m-d');
    }
    foreach ($pindah as $dari => $ke) {
        $hasil = pindah_jadwal($dari, $ke);
        if ($hasil === false) {
            $catat[] = 'Gagal memindah jadwal ' . $dari . ': ' . db_error_terakhir();
            return array(false, $catat);
        }
        $catat[] = $hasil;
    }
    return array(true, $catat);
}

$belum_posting = db_satu("select count(*) as n from tabel_transaksi where keterangan_posting='' and unit =?", 's', $unit);
$belum_posting = $belum_posting ? (int) $belum_posting['n'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses'])) {
    if (!$d_tgl) {
        $pesan = array('danger', 'Tanggal sistem tidak terbaca, silakan login ulang.', array());
    } elseif ($belum_posting > 0) {
        $pesan = array('warning', 'Masih ada ' . $belum_posting . ' transaksi unit yang belum diposting. Proses bisa dilakukan setelah semua data diposting.', array());
    } else {
        $kunci = db_satu("SELECT GET_LOCK('fund_eod', 10) AS ok");
        if (!$kunci || (int) $kunci['ok'] !== 1) {
            $pesan = array('warning', 'EOD sedang diproses user lain, coba lagi sebentar.', array());
        } else {
            list($ok, $catat) = proses_eod($d_tgl);
            db_satu("SELECT RELEASE_LOCK('fund_eod') AS ok");
            $pesan = $ok ? array('success', 'Proses EOD Deposito tanggal ' . $tgl . ' selesai.', $catat) : array('danger', 'Proses EOD Deposito gagal.', $catat);
        }
    }
    $_SESSION['pesan_eod'] = $pesan;
    header('Location: eod.php');
    exit;
}
$pesan = isset($_SESSION['pesan_eod']) ? $_SESSION['pesan_eod'] : null;
unset($_SESSION['pesan_eod']);
$nama_hari = array('1' => 'Senin', '2' => 'Selasa', '3' => 'Rabu', '4' => 'Kamis', '5' => 'Jumat', '6' => 'Sabtu', '7' => 'Minggu');

halaman_awal('Proses EOD');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo h($pesan[0]); ?>" role="alert">
  <?php echo h($pesan[1]); ?>
  <?php if ($pesan[2]): ?>
  <ul class="mb-0 mt-2 small"><?php foreach ($pesan[2] as $c): ?><li><?php echo h($c); ?></li><?php endforeach; ?></ul>
  <?php endif; ?>
</div>
<?php endif; ?>
<div class="card card-ni">
  <div class="card-header">Proses EOD Deposito</div>
  <div class="card-body">
    <p class="text-muted">Proses bisa dilakukan setelah semua data diposting.</p>
    <?php if (!$d_tgl): ?>
    <div class="alert alert-danger mb-0">Tanggal sistem tidak terbaca, silakan login ulang.</div>
    <?php elseif ($belum_posting > 0): ?>
    <div class="alert alert-warning mb-0">Masih ada <?php echo $belum_posting; ?> transaksi unit yang belum diposting.</div>
    <?php else: ?>
    <form method="post" action="eod.php" onsubmit="if (!confirm('Apakah Anda Yakin?')) return false; this.querySelector('button').disabled = true;">
      <p>Tanggal Transaksi (tanggal sistem): <strong><?php echo h($nama_hari[$d_tgl->format('N')] . ', ' . tgl_indo($tgl)); ?></strong></p>
      <input type="hidden" name="proses" value="1">
      <button type="submit" class="btn btn-ni">Proses</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
