<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Proses Konsolidasi Harian: langsung berjalan saat menu dibuka (seperti versi lama, tanpa tombol):
 *   1. kosongkan tabel_master_hari
 *   2. salin tabel_master semua unit kecuali 3000-3003 / 4000-4003, unit 1010 menjadi 0000 ("Posting Koperasi")
 *   3. tambah baris dari API http://192.168.88.44/ni/api/categories/list.php ("Posting Toko")
 *   4. tanggal_awal semua baris = param_tgl unit 001
 * Pesan alert sama dengan versi lama, lalu ringkasan hasil.
 * Dimigrasi 2026-11-03: tampilan Bootstrap 5, prepared statement, wajib login (dulu tanpa cek). API tidak terjangkau /
 * bukan JSON dianggap tanpa data (versi lama: warning foreach), batas koneksi 10 detik.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();

function curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

$curl = curl("http://192.168.88.44/ni/api/categories/list.php");
$data = is_string($curl) ? json_decode($curl, true) : null;
$api_ok = is_array($data);
if (!$api_ok) {
    $data = array();
}

$alert = array();
db_jalankan("TRUNCATE tabel_master_hari");
$p = db_satu("SELECT param_tgl from param_tgl where unit ='001'");
$tanggal = (string) ($p ? $p['param_tgl'] : '');
$alert[] = 'Temporary selesai di hapus';
db_jalankan("insert into tabel_master_hari select * from tabel_master where unit not in ('3000','3001','3002','3003','4000','4001','4002','4003')");
db_jalankan("UPDATE tabel_master_hari set unit ='0000' where unit ='1010'");
$alert[] = 'Posting Koperasi Sukses';

$kolom = array('rekening_gl', 'kode_rekening', 'unit', 'nama_rekening', 'tanggal_awal', 'awal_debet', 'awal_kredit', 'mut_debet',
    'mut_kredit', 'sisa_debet', 'sisa_kredit', 'posisi', 'normal', 'gr_head', 'gr_sub', 'gr_neraca', 'saldo_awal', 'saldo_akhir', 'LINE_BALANCE');
$tanda = implode(',', array_fill(0, count($kolom), '?'));
$baris_api = 0;
foreach ($data as $row) {
    $nilai = array();
    foreach ($kolom as $k) {
        $nilai[] = isset($row[$k]) ? (string) $row[$k] : '';
    }
    if (db_jalankan("insert into tabel_master_hari(" . implode(',', $kolom) . ") values($tanda)", str_repeat('s', count($nilai)), ...$nilai)) {
        $baris_api++;
    }
}
db_jalankan("update tabel_master_hari set tanggal_awal=?", 's', $tanggal);
$alert[] = 'Posting Toko selesai';

$ringkas = db_semua("select unit, count(*) as n from tabel_master_hari group by unit order by unit");
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
    <div class="alert alert-success mb-3">Proses Selesai</div>
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <tbody>
        <tr><th>Tanggal (param_tgl unit 001)</th><td><?php echo h($tanggal); ?></td></tr>
        <tr><th>Baris tabel_master_hari</th><td><?php echo number_format($total, 0, ',', '.'); ?> rekening, <?php echo count($ringkas); ?> unit</td></tr>
        <tr><th>Data API 192.168.88.44</th><td><?php echo $api_ok ? number_format($baris_api, 0, ',', '.') . ' baris' : 'tidak terjangkau / tanpa data'; ?></td></tr>
      </tbody>
    </table>
  </div>
</div>
<?php
halaman_akhir();
