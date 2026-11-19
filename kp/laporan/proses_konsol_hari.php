<?php require_once __DIR__ . '/../../koneksi_db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
/**
 * Menu kp > Proses Konsolidasi Harian: langsung berjalan saat menu dibuka (seperti versi lama, tanpa tombol):
 *   1. kosongkan tabel_master_hari
 *   2. salin tabel_master semua unit kecuali 3000-3003 / 4000-4003, unit 1010 menjadi 0000 ("Posting Koperasi")
 *   3. (dihapus 2026-11-19, perintah user: toko tidak dipakai) dulu tambah baris dari API toko ("Posting Toko")
 *   4. tanggal_awal semua baris = param_tgl unit 001
 * Pesan alert sama dengan versi lama, lalu ringkasan hasil.
 * Dimigrasi 2026-11-03: tampilan Bootstrap 5, prepared statement, wajib login (dulu tanpa cek). API tidak terjangkau /
 * bukan JSON dianggap tanpa data (versi lama: warning foreach), batas koneksi 10 detik.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();

// 2026-11-19 (perintah user): langkah "Posting Toko" (API 192.168.88.44) dihapus, toko tidak dipakai lagi.
$alert = array();
$tanggal = '';
$ringkas = array();
// 2026-11-05: tabel_master_hari pernah belum ada di prod -> semua langkah gagal diam-diam tapi tertulis "Proses Selesai".
// Kini dicek dulu, dan kegagalan perintah database ditampilkan.
$ada_tabel = (bool) db_satu("select 1 from information_schema.tables where table_schema = database() and table_name = 'tabel_master_hari'");
$gagal_awal = db_jumlah_gagal();
if ($ada_tabel) {
db_jalankan("TRUNCATE tabel_master_hari");
$p = db_satu("SELECT param_tgl from param_tgl where unit ='001'");
$tanggal = (string) ($p ? $p['param_tgl'] : '');
$alert[] = 'Temporary selesai di hapus';
db_jalankan("insert into tabel_master_hari select * from tabel_master where unit not in ('3000','3001','3002','3003','4000','4001','4002','4003')");
db_jalankan("UPDATE tabel_master_hari set unit ='0000' where unit ='1010'");
$alert[] = 'Posting Koperasi Sukses';

db_jalankan("update tabel_master_hari set tanggal_awal=?", 's', $tanggal);

$ringkas = db_semua("select unit, count(*) as n from tabel_master_hari group by unit order by unit");
}
$gagal = db_jumlah_gagal() - $gagal_awal;
$total = 0;
foreach ($ringkas as $r) {
    $total += (int) $r['n'];
}

halaman_awal('Proses Konsolidasi Harian');
foreach ($alert as $a) {
    echo "<script>alert(" . json_encode($a) . ");</script>";
}
?>
<div class="card card-ni">
  <div class="card-header">Proses Konsolidasi Harian</div>
  <div class="card-body">
    <?php if (!$ada_tabel): ?>
    <div class="alert alert-danger mb-3">Proses tidak dijalankan: tabel <strong>tabel_master_hari</strong> belum ada di database.
      Minta admin menjalankan <code>db/parameter/2026-11-05_tabel_master_hari.sql</code>, lalu buka menu ini lagi.</div>
    <?php elseif ($gagal > 0): ?>
    <div class="alert alert-danger mb-3">Proses selesai dengan <?php echo (int) $gagal; ?> perintah database gagal; data current bisa tidak lengkap.
      Error terakhir: <?php echo h(isset($GLOBALS['__db_error_terakhir']) ? $GLOBALS['__db_error_terakhir'] : ''); ?></div>
    <?php else: ?>
    <div class="alert alert-success mb-3">Proses Selesai</div>
    <?php endif; ?>
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <tbody>
        <tr><th>Tanggal (param_tgl unit 001)</th><td><?php echo h($tanggal); ?></td></tr>
        <tr><th>Baris tabel_master_hari</th><td><?php echo number_format($total, 0, ',', '.'); ?> rekening, <?php echo count($ringkas); ?> unit</td></tr>
      </tbody>
    </table>
  </div>
</div>
<?php
halaman_akhir();
