<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Proses Konsolidasi: salin neraca kantor pusat (tabel_master unit 1010) ke tabel_master_konsol sebagai unit
 * '0000' untuk tanggal sistem. Tombol hanya muncul bila tidak ada jurnal kp (unit 1010) tanggal sistem yang belum
 * diposting (keterangan_posting kosong).
 * Urutan proses sama dengan versi lama: hapus tabel_master_konsol unit 0000 tanggal sistem, kosongkan tabel_master_kp,
 * isi tabel_master_kp dari tabel_master unit 1010, ubah unit -> 0000 dan tanggal_awal -> tanggal sistem, lalu salin ke
 * tabel_master_konsol (group by rekening_gl; rekening_gl tetap 1010-...). tabel_master/tabel_master_kp MyISAM, jadi
 * tanpa transaksi DB (seperti versi lama).
 * Dimigrasi 2026-11-03: tampilan Bootstrap 5, prepared statement, proses lalu kembali ke halaman ini dengan pesan
 * (POST lalu redirect). var_dump tanggal sistem (sisa debug) dibuang. Pesan sukses kini hanya bila semua langkah
 * berhasil (versi lama selalu "Sukses" setelah tabel_master_kp dikosongkan).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
ob_start();
if (!isset($_SESSION['stgl_system'])) {
    echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
    header("location:../../param_tgl.php");
    exit;
}
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:../../index.html");
    exit;
}
$tgl_system = (string) $_SESSION['stgl_system'];

if (isset($_POST['hitung_shu'])) {
    $ok = db_jalankan("delete from tabel_master_konsol where unit ='0000' and tanggal_awal =?", 's', $tgl_system)
        && db_jalankan("delete from tabel_master_kp");
    if ($ok) {
        //1. Tahap Penginputan
        $ok = db_jalankan("INSERT INTO tabel_master_kp SELECT * FROM tabel_master where unit ='1010'")
            && db_jalankan("UPDATE tabel_master_kp set unit ='0000',tanggal_awal=?", 's', $tgl_system)
            && db_jalankan("insert into tabel_master_konsol select * from tabel_master_kp group by rekening_gl order by rekening_gl");
    }
    $_SESSION['pesan_konsol'] = $ok ? array('success', 'Proses Konsolidasi Sukses')
        : array('danger', 'Proses Konsolidasi gagal: ' . db_error_terakhir());
    header('Location: proses_konsol.php');
    exit;
}

$pesan = isset($_SESSION['pesan_konsol']) ? $_SESSION['pesan_konsol'] : null;
unset($_SESSION['pesan_konsol']);
$n = db_satu("select count(*) as n from tabel_transaksi where keterangan_posting='' and tanggal_transaksi=? and unit ='1010'", 's', $tgl_system);
$belum_posting = $n ? (int) $n['n'] : 0;

halaman_awal('Proses Konsolidasi');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?>"><?php echo h($pesan[1]); ?></div>
<script>alert(<?php echo json_encode($pesan[1]); ?>);</script>
<?php endif; ?>
<div class="card card-ni">
  <div class="card-header">Proses Konsolidasi</div>
  <div class="card-body text-center">
    <p class="mb-1">Tanggal Periode Konsol <strong><?php echo h($tgl_system); ?></strong></p>
    <p class="text-muted fst-italic">Proses bisa Setelah Neraca Balance.</p>
    <?php if ($belum_posting !== 0): ?>
    <div class="alert alert-warning mb-0">masih ada yang belum di posting (<?php echo $belum_posting; ?> jurnal kantor pusat)</div>
    <?php else: ?>
    <form action="proses_konsol.php" method="post" name="postform" id="myform">
      <button type="submit" name="hitung_shu" value="Proses Konsolidasi" class="btn btn-ni px-4" data-konfirmasi="Apakah Anda Yakin?">Proses Konsolidasi</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php
halaman_akhir();
