<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu Transaksi > Jurnal Masuk (level kp, kas masuk kantor pusat).
 *   Simpan  : catat sisi kredit rekening yang dipilih (saran dari tabel_master unit 1010).
 *   Selesai : catat sisi debet kas <unit>-1120000 sebesar total kredit dan nomor jurnal_masuk.
 *   Batal   : hapus satu baris transaksi (link terenkripsi).
 * Alur sama dengan versi lama; tampilan dan query (prepared statement) diperbarui. Versi lama tidak punya cek login;
 * sekarang wajib login. Saran rekening dari ajax/cari_gl_kp.php?kolom=rekening_gl (query sama dengan kp/autosuggesst.php).
 * Perbaikan 2026-11-03 (perintah user, PEKERJAAN_TERTUNDA.md bagian 46):
 *   - kode bukti KM/<unit><userid><nomor> dan daftar per user (dulu KM/<unit><nomor> dipakai bersama semua user kp);
 *     nomor bukti milik user yang belum diselesaikan dilanjutkan, bukan diganti nomor baru
 *   - debet kas saat Selesai = total kredit - debet baris user pada nomor bukti ini di DB (dulu kredit baris terakhir
 *     saja dari isian tersembunyi, sehingga jurnal tidak seimbang); TOTAL di layar = total kredit (dulu selalu 0)
 *   - Selesai ditolak bila kosong atau sudah selesai, dikunci dari kirim ganda; jurnal_masuk ditulis dulu sebagai
 *     penanda (nomor otomatis) dan dihapus lagi bila debet kas gagal
 *   - Batal hanya baris milik user pada nomor bukti yang sedang dibuka dan belum selesai
 *   - Simpan memeriksa rekening (DETAIL unit 1010), tanggal, dan jumlah; Simpan/Selesai selalu memakai nomor bukti
 *     berjalan milik user yang dihitung server (bukan isian form), sehingga tidak bisa menyentuh nomor user lain
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
$userid = (string) $_SESSION['userid'];
include "../encryption/function.php";
//untuk waktu (versi lama date("y-m-d"), tanggal yang sama)
$tanggal = date("Y-m-d");

$unitt = '';
foreach (db_semua("select kode_branch from user where userid=?", 's', $userid) as $id_user) {
    $unitt = (string) $id_user['kode_branch'];
}
$awalan_kode = 'KM/' . $unitt . $userid;

/** true bila nomor bukti ini sudah diselesaikan (ada di jurnal_masuk). */
function kas_masuk_kp_selesai($kode)
{
    $r = db_satu("select count(*) as n from jurnal_masuk where kode_transaksi=?", 's', (string) $kode);
    return $r && (int) $r['n'] > 0;
}
/** Tanggal Y-m-d yang benar-benar ada. */
function tanggal_valid_km($t)
{
    $d = DateTime::createFromFormat('!Y-m-d', (string) $t);
    return $d && $d->format('Y-m-d') === (string) $t;
}
/** Nomor bukti berjalan user: lanjutkan yang belum selesai, kalau tidak ada nomor baru (jurnal_masuk terakhir unit + 1). */
function nomor_bukti_km($unit, $userid, $awalan)
{
    $belum = db_satu("select t.kode_transaksi from tabel_transaksi t
        where t.unit=? and t.id_admin=? and t.jenis_transaksi='Kas Masuk' and t.kode_transaksi like ?
          and not exists (select 1 from jurnal_masuk j where j.kode_transaksi=t.kode_transaksi)
        order by t.id_transaksi desc limit 1", 'sss', $unit, $userid, $awalan . '%');
    if ($belum) {
        return (string) $belum['kode_transaksi'];
    }
    $j = db_satu("SELECT max(nomor_jurnal) AS n FROM jurnal_masuk where unit=?", 's', $unit);
    return $awalan . (compat_num($j ? $j['n'] : null) + 1);
}

/** Insert satu baris tabel_transaksi (sisi debet atau kredit). */
function catat_kas_masuk_kp($unit, $kode_transaksi, $kode_rekening, $tanggal, $keterangan, $sisi, $jumlah, $userid)
{
    $kolom = $sisi === 'debet' ? 'debet' : 'kredit';
    return db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
                        values(?,?,?,?,'Kas Masuk',?,?,?)", 'sssssss', (string) $unit, (string) $kode_transaksi, (string) $kode_rekening,
        (string) $tanggal, (string) $keterangan, (string) $jumlah, (string) $userid);
}

$kode_transaksi = nomor_bukti_km($unitt, $userid, $awalan_kode);
$tanggal_form = empty($_POST['tanggal_transaksi']) ? $tanggal : $_POST['tanggal_transaksi'];
$keterangan_form = isset($_POST['keterangan_jurnal']) ? $_POST['keterangan_jurnal'] : '';
$keterangan_jurnal = '';

// pesan/script dari proses ditampilkan di bawah form, seperti versi lama
ob_start();

//untuk menyimpan transaksi
if (isset($_POST['simpan'])) {
    // nomor bukti selalu nomor berjalan milik user ini (dihitung server), bukan nilai kiriman form
    $kode_kirim = $kode_transaksi;
    $tanggal_transaksi = isset($_POST['tanggal_transaksi']) ? trim((string) $_POST['tanggal_transaksi']) : '';
    $keterangan_transaksi = ucwords(isset($_POST['keterangan_transaksi']) ? (string) $_POST['keterangan_transaksi'] : '');
    $kode_rekening = isset($_POST['kode_rekening']) ? trim((string) $_POST['kode_rekening']) : '';
    $keterangan_jurnal = ucwords(isset($_POST['keterangan_jurnal']) ? (string) $_POST['keterangan_jurnal'] : '');
    //untuk jurnal kas masuk, semua transaksi ada sisi KREDIT
    $jumlah_dk = trim(ucwords(isset($_POST['jumlah_dk']) ? (string) $_POST['jumlah_dk'] : ''));
    $salah = array();
    if ($kode_rekening === '' || !db_satu("select 1 as ada from tabel_master where unit='1010' and rekening_gl=? and LINE_BALANCE='DETAIL'", 's', $kode_rekening)) {
        $salah[] = 'Rekening ' . $kode_rekening . ' tidak terdaftar sebagai rekening DETAIL unit 1010';
    }
    if (!tanggal_valid_km($tanggal_transaksi)) {
        $salah[] = 'Tanggal tidak valid';
    }
    if (!is_numeric($jumlah_dk) || $jumlah_dk <= 0) {
        $salah[] = 'Jumlah harus angka lebih dari 0';
    }
    if (kas_masuk_kp_selesai($kode_kirim)) {
        $salah[] = 'Nomor bukti ' . $kode_kirim . ' sudah diselesaikan';
    }
    if ($salah) {
        echo "<script>alert(" . json_encode(implode("\n", $salah) . "\nTransaksi tidak disimpan.") . ");</script>";
    } else {
        catat_kas_masuk_kp($unitt, $kode_kirim, $kode_rekening, $tanggal_transaksi, $keterangan_transaksi, 'kredit', $jumlah_dk, $userid);
        $kode_transaksi = $kode_kirim;
    }
}

//untuk menyelesaikan transaksi
if (isset($_POST['selesai'])) {
    // yang diselesaikan selalu nomor berjalan milik user ini (dihitung server)
    $kode_kirim = $kode_transaksi;
    $tanggal_selesai = isset($_POST['tanggal_selesai']) ? trim((string) $_POST['tanggal_selesai']) : '';
    $keterangan_jurnal = isset($_POST['keterangan_jurnal']) ? (string) $_POST['keterangan_jurnal'] : '';
    $kunci = db_satu("SELECT GET_LOCK('nurinsani_kp_kas_masuk', 15) as ok");
    if (!$kunci || (int) $kunci['ok'] !== 1) {
        echo "<script>alert('Sistem sedang memproses transaksi lain, coba lagi.');</script>";
    } else {
        $saldo = db_satu("select sum(kredit)-sum(debet) as saldo from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=?", 'sss',
            $kode_kirim, $userid, $unitt);
        $debet_kas = compat_num($saldo ? $saldo['saldo'] : null);
        if (kas_masuk_kp_selesai($kode_kirim)) {
            echo "<script>alert(" . json_encode('Nomor bukti ' . $kode_kirim . ' sudah diselesaikan.') . ");</script>";
        } elseif ($debet_kas <= 0) {
            echo "<script>alert('Belum ada transaksi yang perlu diselesaikan.');</script>";
        } elseif (!tanggal_valid_km($tanggal_selesai)) {
            echo "<script>alert('Tanggal tidak valid.');</script>";
        } else {
            if (trim($keterangan_jurnal) === '') {
                // keterangan diambil dari baris terakhir bila isian tersembunyi kosong (mis. sesudah Batal)
                $akhir = db_satu("select keterangan_transaksi from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=? order by id_transaksi desc limit 1", 'sss',
                    $kode_kirim, $userid, $unitt);
                $keterangan_jurnal = $akhir ? (string) $akhir['keterangan_transaksi'] : $keterangan_jurnal;
            }
            // jurnal_masuk (MyISAM) ditulis dulu sebagai penanda selesai, nomor otomatis; dihapus lagi bila debet kas gagal
            if (!db_jalankan("insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(NULL,?,?,?)", 'sss',
                $kode_kirim, $tanggal_selesai, $unitt)) {
                echo "<script>alert('Pencatatan jurnal masuk gagal, transaksi belum diselesaikan.');</script>";
            } elseif (!catat_kas_masuk_kp($unitt, $kode_kirim, $unitt . '-1120000', $tanggal_selesai, $keterangan_jurnal, 'debet', $debet_kas, $userid)) {
                //karena kas masuk lawan transaksinya adalah kas, maka kas <unit>-1120000 ada di sisi DEBET.
                db_jalankan("delete from jurnal_masuk where kode_transaksi=?", 's', $kode_kirim);
                echo "<script>alert('Penyimpanan debet kas gagal, transaksi belum diselesaikan.');</script>";
            } else {
                ?><script language="javascript">document.location.href="?<?php echo paramEncrypt('page=../transaksi/kas_masuk')?>"</script><?php
            }
        }
        db_satu("SELECT RELEASE_LOCK('nurinsani_kp_kas_masuk') as ok");
    }
}

//untuk mendecode url yang di enrypsi
$var = strpos($_SERVER['REQUEST_URI'], '?') !== false ? decode($_SERVER['REQUEST_URI']) : array();
$mode = isset($var['mode']) ? $var['mode'] : null;
$id_transaksi = isset($var['id_transaksi']) ? $var['id_transaksi'] : null;
if ($mode == 'delete' && !kas_masuk_kp_selesai($kode_transaksi)) {
    // hanya baris milik user ini pada nomor bukti yang sedang dibuka dan belum diselesaikan (versi lama: id apa pun)
    db_jalankan("delete from tabel_transaksi where id_transaksi=? and id_admin=? and kode_transaksi=?", 'sss',
        (string) $id_transaksi, $userid, $kode_transaksi);
}

$pesan_proses = ob_get_clean();

// sesudah Selesai/Batal nomor bukti berjalan bisa berganti
$kode_transaksi = nomor_bukti_km($unitt, $userid, $awalan_kode);

//untuk menampilkan transaksi milik user ini pada nomor bukti berjalan
$baris_transaksi = array();
$tot_kredit = 0;
foreach (db_semua("select * from tabel_transaksi where kode_transaksi=? and id_admin=? and unit=? order by id_transaksi", 'sss', $kode_transaksi, $userid, $unitt) as $row) {
    $kredit = $row['kredit'] === null ? null : (string) $row['kredit'];
    $tot_kredit += compat_num($kredit) - compat_num($row['debet']);
    $baris_transaksi[] = array(
        'kode_rekening' => $row['kode_rekening'],
        'keterangan_transaksi' => $row['keterangan_transaksi'],
        'kredit' => $kredit,
        'id_transaksi' => $row['id_transaksi'],
    );
}

$aksi_form = '?' . paramEncrypt('page=../transaksi/kas_masuk');
halaman_awal('Jurnal Masuk');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Input</div>
  <div class="card-body">
    <form action="<?php echo h($aksi_form); ?>" method="post" name="postform" class="form-ni" autocomplete="off">
      <input type="hidden" name="kode_bukti" value="<?php echo h($kode_transaksi); ?>">
      <div class="row g-2 mb-2">
        <label class="col-sm-4 col-form-label">Nomor Transaksi</label>
        <div class="col-sm-8"><input type="text" class="form-control form-control-sm" disabled value="<?php echo h($kode_transaksi); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="tanggal_transaksi" class="col-sm-4 col-form-label">Tanggal</label>
        <div class="col-sm-8"><input type="date" name="tanggal_transaksi" id="tanggal_transaksi" class="form-control form-control-sm" value="<?php echo h($tanggal_form); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="keterangan_jurnal" class="col-sm-4 col-form-label">Keterangan Jurnal</label>
        <div class="col-sm-8"><input type="text" name="keterangan_jurnal" id="keterangan_jurnal" class="form-control form-control-sm" value="<?php echo h($keterangan_form); ?>" autofocus></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Rekening</label>
        <div class="col-sm-4">
          <div class="autocomplete position-relative">
            <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm"
                   data-autocomplete="../../ajax/cari_gl_kp.php?kolom=rekening_gl" data-isi-nilai="rekening_gl" data-isi-label="label"
                   data-isi-target='{"#country":"nama_rekening"}'>
          </div>
        </div>
        <div class="col-sm-4"><input type="text" id="country" class="form-control form-control-sm" disabled></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="keterangan_transaksi" class="col-sm-4 col-form-label">Keterangan Transaksi</label>
        <div class="col-sm-8"><input type="text" name="keterangan_transaksi" id="keterangan_transaksi" class="form-control form-control-sm" value="<?php echo h($keterangan_form); ?>"></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="jumlah_dk" class="col-sm-4 col-form-label">Jumlah (Rp)</label>
        <div class="col-sm-4"><input type="text" name="jumlah_dk" id="jumlah_dk" class="form-control form-control-sm text-end" inputmode="numeric" autocomplete="off"></div>
        <div class="col-sm-4 col-form-label text-muted" id="format"></div>
      </div>
      <div class="text-end">
        <button type="submit" name="simpan" value="Simpan" class="btn btn-ni btn-sm px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php echo $pesan_proses; ?>

<div class="table-responsive mt-3">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr><th>Kode Rekening</th><th>Keterangan</th><th class="text-end">Kredit</th><th class="text-center">Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($baris_transaksi as $b): ?>
      <tr>
        <td><?php echo h($b['kode_rekening']); ?></td>
        <td><?php echo h($b['keterangan_transaksi']); ?></td>
        <td class="text-end"><?php if ($b['kredit'] !== "0") { echo number_format(compat_num($b['kredit']), 2, '.', ','); } ?></td>
        <td class="text-center"><a href="?<?php echo h(paramEncrypt('page=../transaksi/kas_masuk&mode=delete&id_transaksi=' . $b['id_transaksi'])); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin?">Batal</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr class="fw-bold">
        <td colspan="2" class="text-center">TOTAL</td>
        <td class="text-end"><?php echo number_format($tot_kredit, 2, '.', ','); ?></td>
        <td></td>
      </tr>
    </tfoot>
  </table>
</div>

<form action="<?php echo h($aksi_form); ?>" method="post" name="form" class="text-end">
  <input type="hidden" name="tanggal_selesai" value="<?php if (empty($_POST['tanggal_transaksi'])) { echo h($tanggal); } else { echo h($_POST['tanggal_transaksi']); } ?>">
  <input type="hidden" name="kode_bukti" value="<?php echo h($kode_transaksi); ?>">
  <input type="hidden" name="keterangan_jurnal" value="<?php echo h($keterangan_jurnal); ?>">
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
