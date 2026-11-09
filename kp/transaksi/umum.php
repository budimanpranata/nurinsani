<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu Transaksi > jurnal Umum (level kp).
 *   Simpan  : catat satu baris debet atau kredit (rekening dari tabel_master unit 1010).
 *   Selesai : catat nomor jurnal_umum untuk nomor bukti ini.
 *   Cancel  : hapus satu baris transaksi (link terenkripsi).
 *   cetak   : PDF bukti jurnal (cetak.php).
 * Kode bukti BU/-<unit><nomor> (sama dengan Jurnal Umum unit; dulu BU/-<userid><unit><nomor>). Versi lama tidak punya cek login; sekarang wajib login. Saran rekening dari
 * ajax/cari_gl_kp.php?kolom=rekening_gl (query sama dengan kp/autosuggesst.php).
 * Perbaikan 2026-11-03 (perintah user, PEKERJAAN_TERTUNDA.md bagian 47; model sama dengan Jurnal Masuk/Keluar kp):
 *   - nomor bukti berjalan milik user dihitung server: yang belum diselesaikan dilanjutkan, kalau tidak ada nomor baru
 *     (jurnal_umum terakhir unit + 1); Simpan/Selesai/Cancel selalu memakai nomor ini, bukan isian form
 *   - Selesai hanya bila jurnal balance (total debet = total kredit > 0) dan belum diselesaikan; dikunci dari kirim ganda
 *   - Cancel hanya baris milik user pada nomor bukti berjalan yang belum selesai
 *   - Simpan memeriksa rekening (DETAIL unit 1010), posisi, tanggal, dan jumlah
 *   - tautan cetak selalu memakai nomor bukti berjalan (total dihitung cetak.php dari DB)
 * Perbaikan 2026-11-03 (perintah user, bagian 61): disamakan dengan Jurnal Umum unit
 *   - kode BU/-<unit><nomor jurnal_umum terakhir + 1>; nomor yang sedang dipakai user lain (belum selesai) dilewati
 *     supaya dua user tidak mendapat nomor yang sama; nomor lama BU/-<userid>... yang belum selesai tetap dilanjutkan
 *   - Simpan berhasil: notif + form kosong (nomor bukti tetap sampai Selesai, tanggal terakhir dipertahankan)
 *   - Selesai berhasil: notif + nomor bukti baru
 * Perbaikan 2026-11-03 (perintah user, PEKERJAAN_TERTUNDA.md bagian 65): nomor bukti urut per unit BU/-<unit>-<6 digit>
 * (inc/nomor_bukti.php, dulu BU/-<unit><nomor jurnal_umum terakhir + 1>); nomor baru dipastikan di dalam kunci saat Simpan supaya dua user
 * tidak mendapat nomor yang sama. Selesai = ada di jurnal_umum dengan tanggal_selesai terisi.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/nomor_bukti.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
$userid = (string) $_SESSION['userid'];
include "../encryption/function.php";
//untuk waktu (versi lama date("y-m-d"), tanggal yang sama)
$tanggal = date("Y-m-d");

$unit = '';
foreach (db_semua("select kode_branch from user where userid=?", 's', $userid) as $id_user) {
    $unit = (string) $id_user['kode_branch'];
}

/** true bila nomor bukti ini sudah SELESAI (ada di jurnal_umum dengan tanggal_selesai terisi). */
function jurnal_umum_kp_selesai($kode)
{
    return nb_selesai('BU', $kode);
}
/** Tanggal Y-m-d yang benar-benar ada. */
function tanggal_valid_ju($t)
{
    $d = DateTime::createFromFormat('!Y-m-d', (string) $t);
    return $d && $d->format('Y-m-d') === (string) $t;
}
/** Nomor bukti berjalan user, atau nomor urut unit berikutnya BU/-<unit>-<6 digit> (bagian 65). */
function nomor_bukti_ju($unit, $userid)
{
    return nb_tampil('BU', (string) $unit, (string) $userid, 'Bukti Umum');
}
/** Simpan/Selesai berhasil: simpan notif, lalu buka ulang form kosong (pola POST-redirect-GET, sama dengan unit). */
function kembali_ke_form_kp($pesan)
{
    $_SESSION['jukp_pesan'] = $pesan;
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Location: umum.php?' . paramEncrypt('page=../transaksi/umum'));
    exit;
}
/** Total debet dan kredit baris user pada nomor bukti ini. */
function total_ju($kode, $userid, $unit)
{
    $r = db_satu("select coalesce(sum(debet),0) as d, coalesce(sum(kredit),0) as k from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=?",
        'sss', $kode, $userid, $unit);
    return array($r ? (float) $r['d'] : 0.0, $r ? (float) $r['k'] : 0.0);
}

$kode_transaksi = nomor_bukti_ju($unit, $userid);
// tanggal terakhir yang dipakai Simpan tetap dipakai sampai Selesai (form dikosongkan setelah Simpan)
$tanggal_terakhir = !empty($_SESSION['jukp_tanggal']) ? (string) $_SESSION['jukp_tanggal'] : $tanggal;
$tanggal_form = empty($_POST['tanggal_transaksi']) ? $tanggal_terakhir : $_POST['tanggal_transaksi'];
$pesan_sesi = isset($_SESSION['jukp_pesan']) ? (string) $_SESSION['jukp_pesan'] : '';
unset($_SESSION['jukp_pesan']);
$keterangan_form = isset($_POST['keterangan_transaksi']) ? $_POST['keterangan_transaksi'] : '';

// pesan/script dari proses ditampilkan di bawah form, seperti versi lama
ob_start();

//untuk menyimpan transaksi (selalu ke nomor bukti berjalan milik user)
if (isset($_POST['simpan'])) {
    $tanggal_transaksi = isset($_POST['tanggal_transaksi']) ? trim((string) $_POST['tanggal_transaksi']) : '';
    $keterangan_transaksi = isset($_POST['keterangan_transaksi']) ? (string) $_POST['keterangan_transaksi'] : '';
    $kode_rekening = isset($_POST['kode_rekening']) ? trim((string) $_POST['kode_rekening']) : '';
    $posisi = isset($_POST['posisi']) ? (string) $_POST['posisi'] : '';
    $jumlah_dk = trim(ucwords(isset($_POST['jumlah_dk']) ? (string) $_POST['jumlah_dk'] : ''));
    $salah = array();
    if ($kode_rekening === '' || !db_satu("select 1 as ada from tabel_master where unit='1010' and rekening_gl=? and LINE_BALANCE='DETAIL'", 's', $kode_rekening)) {
        $salah[] = 'Rekening ' . $kode_rekening . ' tidak terdaftar sebagai rekening DETAIL unit 1010';
    }
    if ($posisi !== 'debet' && $posisi !== 'kredit') {
        $salah[] = 'Posisi harus Debet atau Kredit';
    }
    if (!tanggal_valid_ju($tanggal_transaksi)) {
        $salah[] = 'Tanggal tidak valid';
    }
    if (!is_numeric($jumlah_dk) || $jumlah_dk <= 0) {
        $salah[] = 'Jumlah harus angka lebih dari 0';
    }
    if (jurnal_umum_kp_selesai($kode_transaksi)) {
        $salah[] = 'Nomor bukti ' . $kode_transaksi . ' sudah diselesaikan';
    }
    if ($salah) {
        echo "<script>alert(" . json_encode(implode("\n", $salah) . "\nTransaksi tidak disimpan.") . ");</script>";
    } elseif (!nb_kunci('BU', $unit)) {
        echo "<script>alert('Sistem sedang memproses transaksi lain, coba lagi.');</script>";
    } else {
        // nomor dipastikan ulang di dalam kunci (nomor baru bisa sudah diambil user lain sejak halaman dibuka)
        $kode_transaksi = nomor_bukti_ju($unit, $userid);
        $tersimpan = db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$posisi,id_admin)
            values(?,?,?,?,'Bukti Umum',?,?,?)", 'sssssss', $unit, $kode_transaksi, $kode_rekening, $tanggal_transaksi,
            $keterangan_transaksi, $jumlah_dk, $userid);
        nb_lepas('BU', $unit);
        if ($tersimpan) {
            $_SESSION['jukp_tanggal'] = $tanggal_transaksi;
            kembali_ke_form_kp("Data tersimpan pada nomor bukti $kode_transaksi (" . ucfirst($posisi) . " Rp. "
                . number_format(compat_num($jumlah_dk), 0, ',', '.') . ").");
        } else {
            echo "<script>alert('Maaf, data gagal disimpan.');</script>";
        }
    }
}

//untuk menyelesaikan transaksi (nomor bukti berjalan milik user)
if (isset($_POST['selesai'])) {
    $tanggal_selesai = isset($_POST['tanggal_selesai']) ? trim((string) $_POST['tanggal_selesai']) : '';
    $kunci = db_satu("SELECT GET_LOCK('nurinsani_kp_jurnal_umum', 15) as ok");
    if (!$kunci || (int) $kunci['ok'] !== 1) {
        echo "<script>alert('Sistem sedang memproses transaksi lain, coba lagi.');</script>";
    } else {
        list($d, $k) = total_ju($kode_transaksi, $userid, $unit);
        if (jurnal_umum_kp_selesai($kode_transaksi)) {
            echo "<script>alert(" . json_encode('Nomor bukti ' . $kode_transaksi . ' sudah diselesaikan.') . ");</script>";
        } elseif ($d <= 0 && $k <= 0) {
            echo "<script>alert('Belum ada transaksi yang perlu diselesaikan.');</script>";
        } elseif (round($d, 2) != round($k, 2)) {
            echo "<script>alert(" . json_encode('Jurnal belum balance (debet ' . number_format($d, 0, ',', '.') . ', kredit '
                . number_format($k, 0, ',', '.') . '). Selesai ditolak.') . ");</script>";
        } elseif (!tanggal_valid_ju($tanggal_selesai)) {
            echo "<script>alert('Tanggal tidak valid.');</script>";
        } elseif (db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(NULL,?,?,?)", 'sss',
            $kode_transaksi, $tanggal_selesai, $unit)) {
            db_satu("SELECT RELEASE_LOCK('nurinsani_kp_jurnal_umum') as ok");
            unset($_SESSION['jukp_tanggal']);
            kembali_ke_form_kp("Jurnal nomor bukti $kode_transaksi selesai.");
        } else {
            echo "<script>alert('Pencatatan jurnal umum gagal, transaksi belum diselesaikan.');</script>";
        }
        db_satu("SELECT RELEASE_LOCK('nurinsani_kp_jurnal_umum') as ok");
    }
}

//untuk mendecode url yang di enrypsi
$var = strpos($_SERVER['REQUEST_URI'], '?') !== false ? decode($_SERVER['REQUEST_URI']) : array();
$mode = isset($var['mode']) ? $var['mode'] : null;
$id_transaksi = isset($var['id_transaksi']) ? $var['id_transaksi'] : null;
if ($mode == 'delete' && !jurnal_umum_kp_selesai($kode_transaksi)) {
    // hanya baris milik user ini pada nomor bukti berjalan yang belum diselesaikan (versi lama: id apa pun)
    db_jalankan("delete from tabel_transaksi where id_transaksi=? and id_admin=? and kode_transaksi=?", 'sss',
        (string) $id_transaksi, $userid, $kode_transaksi);
}

$pesan_proses = ob_get_clean();

// sesudah Selesai/Cancel nomor bukti berjalan bisa berganti
if (isset($_POST['selesai'])) {
    nb_lupa(); // status selesai bisa berubah oleh proses di atas
}
$kode_transaksi = nomor_bukti_ju($unit, $userid);

//untuk menampilkan data
$baris_transaksi = array();
$tot_debet = 0;
$tot_kredit = 0;
// hanya baris jurnal yang belum selesai (bagian 64)
foreach (jurnal_umum_kp_selesai($kode_transaksi) ? array() : db_semua("select * from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=? order by id_transaksi", 'sss', $kode_transaksi, $userid, $unit) as $row) {
    $debet = $row['debet'] === null ? null : (string) $row['debet'];
    $kredit = $row['kredit'] === null ? null : (string) $row['kredit'];
    $tot_debet += compat_num($debet);
    $tot_kredit += compat_num($kredit);
    $baris_transaksi[] = array(
        'kode_rekening' => $row['kode_rekening'],
        'keterangan_transaksi' => $row['keterangan_transaksi'],
        'debet' => $debet,
        'kredit' => $kredit,
        'id_transaksi' => $row['id_transaksi'],
    );
}

$aksi_form = '?' . paramEncrypt('page=../transaksi/umum');
halaman_awal('Jurnal Umum');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Input</div>
  <div class="card-body">
    <form action="<?php echo h($aksi_form); ?>" method="post" name="postform" class="form-ni" autocomplete="off">
      <input type="hidden" name="kode_bukti" value="<?php echo h($kode_transaksi); ?>">
      <div class="row g-2 mb-2">
        <label class="col-sm-4 col-form-label">Nomor Bukti</label>
        <div class="col-sm-8"><input type="text" class="form-control form-control-sm" value="<?php echo h($kode_transaksi); ?>" readonly></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="tanggal_transaksi" class="col-sm-4 col-form-label">Tanggal</label>
        <div class="col-sm-8"><input type="date" name="tanggal_transaksi" id="tanggal_transaksi" class="form-control form-control-sm" value="<?php echo h($tanggal_form); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="keterangan_transaksi" class="col-sm-4 col-form-label">Keterangan</label>
        <div class="col-sm-8"><input type="text" name="keterangan_transaksi" id="keterangan_transaksi" class="form-control form-control-sm" value="<?php echo h($keterangan_form); ?>" autofocus></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="jumlah_dk" class="col-sm-4 col-form-label">Jumlah (Rp)</label>
        <div class="col-sm-4"><input type="text" name="jumlah_dk" id="jumlah_dk" class="form-control form-control-sm text-end" inputmode="numeric" autocomplete="off"></div>
        <div class="col-sm-4 col-form-label text-muted" id="format"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Nomor Rekening</label>
        <div class="col-sm-4">
          <div class="autocomplete position-relative">
            <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm"
                   data-autocomplete="../../ajax/cari_gl_kp.php?kolom=rekening_gl" data-isi-nilai="rekening_gl" data-isi-label="label"
                   data-isi-target='{"#country":"nama_rekening"}'>
          </div>
        </div>
        <div class="col-sm-4"><input type="text" id="country" class="form-control form-control-sm" disabled></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="posisi" class="col-sm-4 col-form-label">Posisi</label>
        <div class="col-sm-4">
          <select name="posisi" id="posisi" class="form-select form-select-sm">
            <option value="debet">Debet</option>
            <option value="kredit">Kredit</option>
          </select>
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="simpan" value="Simpan" class="btn btn-ni btn-sm px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php echo $pesan_proses; ?>
<?php if ($pesan_sesi !== ''): ?>
<script>alert(<?php echo json_encode($pesan_sesi); ?>);</script>
<?php endif; ?>

<div class="table-responsive mt-3">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr><th>Kode Rekening</th><th>Keterangan</th><th class="text-end">Debet</th><th class="text-end">Kredit</th><th class="text-center">Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($baris_transaksi as $b): ?>
      <tr>
        <td><?php echo h($b['kode_rekening']); ?></td>
        <td><?php echo h($b['keterangan_transaksi']); ?></td>
        <td class="text-end"><?php if ($b['debet'] !== "0") { echo number_format(compat_num($b['debet']), 2, '.', ','); } ?></td>
        <td class="text-end"><?php if ($b['kredit'] !== "0") { echo number_format(compat_num($b['kredit']), 2, '.', ','); } ?></td>
        <td class="text-center"><a href="?<?php echo h(paramEncrypt('page=../transaksi/umum&mode=delete&id_transaksi=' . $b['id_transaksi'])); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin?">Cancel</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="fw-bold">
        <td colspan="2" class="text-center">TOTAL</td>
        <td class="text-end"><?php echo number_format($tot_debet, 2, '.', ','); ?></td>
        <td class="text-end"><?php echo number_format($tot_kredit, 2, '.', ','); ?></td>
        <td class="text-center">
          <?php //untuk menghitung balance
          if (!empty($tot_debet) || !empty($tot_kredit)) {
              if ($tot_debet == $tot_kredit) {
                  echo '<span class="text-primary">Balance</span>';
              } else {
                  echo '<span class="text-danger">Not Balance : ' . h(abs($tot_debet - $tot_kredit)) . '</span>';
              }
          } ?>
          <?php if ($baris_transaksi): ?>
          <div class="mt-1">
            <a href="cetak.php?file=<?php echo h(rawurlencode($kode_transaksi)); ?>" target="_blank" class="btn btn-outline-primary btn-sm">cetak</a>
          </div>
          <?php endif; ?>
        </td>
      </tr>
    </tfoot>
  </table>
</div>

<form action="<?php echo h($aksi_form); ?>" method="post" name="form" class="text-end">
  <input type="hidden" name="tanggal_selesai" value="<?php if (empty($_POST['tanggal_transaksi'])) { echo h($tanggal_terakhir); } else { echo h($_POST['tanggal_transaksi']); } ?>">
  <input type="hidden" name="kode_bukti" value="<?php echo h($kode_transaksi); ?>">
  <button type="submit" name="selesai" value="Selesai" class="btn btn-ni btn-sm px-4" data-konfirmasi="Apakah Anda Yakin?">Selesai</button>
</form>
<script>
// tampilan Rp. 1.234 di samping isian jumlah (seperti formatCurrency versi lama)
document.getElementById('jumlah_dk').addEventListener('keyup', function () {
    var angka = this.value.replace(/[^0-9]/g, '') || '0';
    document.getElementById('format').textContent = 'Rp. ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
});
</script>
<?php
halaman_akhir();
