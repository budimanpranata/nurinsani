<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu unit "Akhir Hari" (tree_items.js). Sejak 2026-11-21 (perintah user, PEKERJAAN_TERTUNDA §103 P1-2, pilihan b)
 * halaman ini HANYA INFORMASI dan tidak mengubah data apa pun.
 * Versi lama: tombol Proses menjalankan "update param_tgl set param_tgl='<tgl>' and unit='<unit>'" TANPA WHERE, sehingga
 * semua baris param_tgl (tanggal sistem semua unit, EOD_MOB, EOM, MOB_AWAL, dll) berubah jadi 0/1 (tanggal tidak sah);
 * lolos cek terutama di hari tanpa transaksi (kedua jumlah NULL). Kalaupun benar, isinya hanya menulis tanggal yang sama.
 * Tanggal sistem dimajukan otomatis oleh cron proses_eod_posting.php (07:30 Senin-Jumat).
 * Info "masih ada yang belum di posting" tetap ditampilkan seperti versi lama (hanya membaca).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit');

$userid = (string) $_SESSION['userid'];
$tgl_system = isset($_SESSION['stgl_system']) ? (string) $_SESSION['stgl_system'] : '';
$u = db_satu('select kode_branch from user where userid=?', 's', $userid);
$unit = $u ? (string) $u['kode_branch'] : '';
$belum = db_satu("select count(*) as jml from tabel_transaksi where keterangan_posting='' and unit=? and tanggal_transaksi=?", 'ss', $unit, $tgl_system);
$jml_belum = $belum ? (int) $belum['jml'] : 0;

halaman_awal('Akhir Hari');
?>
<div class="alert alert-info">
  Akhir hari dilakukan <strong>otomatis oleh sistem</strong> setiap pagi pukul 07:30 (Senin-Jumat).
  Tidak perlu menekan tombol apa pun di halaman ini.
</div>
<p>Tanggal sistem: <strong><?php echo h($tgl_system); ?></strong></p>
<?php if ($jml_belum !== 0): ?>
<p class="text-muted fst-italic">Masih ada yang belum di posting (<?php echo $jml_belum; ?> baris jurnal).</p>
<?php else: ?>
<p class="text-muted fst-italic">Semua jurnal tanggal sistem sudah diposting.</p>
<?php endif; ?>
<?php
halaman_akhir();
