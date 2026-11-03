<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Report Tunggakan (menu unit): anggota unit user dengan saldo tunggakan > 0, per CIF: unit, kelompok, AO, CIF,
 * nama, FT (saldo tunggakan / angsuran, dibulatkan) dan nominal tunggakan.
 * Perbaikan tampilan 2026-11-03 (perintah user): versi lama memuat Bootstrap 3 + DataTables dari CDN dan mengambil
 * data lewat response.php (AJAX); kini tabel Bootstrap 5 langsung dari server dengan query yang sama (prepared
 * statement), kotak cari, baris total, Export Excel (?export=xlsx), dan Cetak.
 * Dibuang dari versi lama: footer tabel yang kolomnya tidak cocok (tanpa nama AO), filter min/max untuk tabel #example
 * yang tidak ada. response.php tidak diubah (masih dipakai jurnal_kas.php).
 * Ditambah cek login dan level unit (versi lama hanya di response.php).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "unit") {
    header("location:index.html");
    exit;
}

$r = db_satu("select kode_branch from user where userid =?", 's', (string) $userid);
$unit = $r ? (string) $r['kode_branch'] : '';

$baris = db_semua("select
tunggakan.unit,
nama_kel,
nama_ao,
tunggakan.cif,
Cust_Short_name,
ROUND(sum(KREDIT-DEBIT)/angsuran) as ft,
(sum(KREDIT-DEBIT)) as nominal
from tunggakan
inner join loan on loan.cif=tunggakan.cif
inner join ao on ao.cao=tunggakan.CAO
inner join kelompok on kelompok.code_kel = tunggakan.code_kel
where tunggakan.unit =?
group by tunggakan.cif
HAVING sum(KREDIT-DEBIT) > 0
ORDER BY cif", 's', $unit);
$total = 0;
foreach ($baris as $b) {
    $total += compat_num($b['nominal']);
}

if (isset($_GET['export']) && $_GET['export'] === 'xlsx') {
    require_once __DIR__ . '/inc/xlsx_writer.php';
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nurinsani_export' . DIRECTORY_SEPARATOR . bin2hex(random_bytes(12));
    if (!@mkdir($dir, 0700, true)) {
        die('Folder sementara export tidak bisa dibuat.');
    }
    $xml = '';
    $n = 1;
    foreach ($baris as $b) {
        $n++;
        $xml .= xlsx_baris_xml($n, array(
            array('s', $b['unit']), array('s', $b['nama_kel']), array('s', $b['nama_ao']), array('s', $b['cif']),
            array('s', $b['Cust_Short_name']), $b['ft'] === null ? array('s', '') : array('n', $b['ft']), array('n', $b['nominal']),
        ));
    }
    $n++;
    $xml .= xlsx_baris_xml($n, array(array('s', 'TOTAL'), array('s', ''), array('s', ''), array('s', ''),
        array('s', count($baris) . ' anggota'), array('s', ''), array('n', $total)), 2);
    xlsx_tulis_baris($dir . '/data.part', $xml);
    $nama_berkas = 'tunggakan-' . $unit . '-' . date('Ymd') . '.xlsx';
    $berkas = $dir . '/' . $nama_berkas;
    $ok = xlsx_rakit($berkas, 'Tunggakan', array('Unit', 'Kelompok', 'Nama AO', 'CIF', 'Nama', 'FT', 'Nominal'),
        array(8, 30, 22, 12, 32, 8, 16), $dir . '/data.part');
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!$ok) {
        die('Gagal membuat file xlsx.');
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nama_berkas . '"');
    header('Content-Length: ' . filesize($berkas));
    header('Cache-Control: no-store');
    readfile($berkas);
    foreach (glob($dir . '/*') ?: array() as $f) {
        @unlink($f);
    }
    @rmdir($dir);
    exit;
}

halaman_awal('Report Tunggakan');
?>
<style>
@media print {
  .no-print { display: none !important; }
  .table-responsive { overflow: visible !important; }
}
</style>
<div class="card card-ni mw-100">
  <div class="card-header d-flex flex-wrap align-items-center gap-2">
    <span>Report Tunggakan Unit <?php echo h($unit); ?></span>
    <span class="ms-auto small"><?php echo count($baris); ?> anggota &middot; Total Rp <?php echo number_format($total); ?></span>
  </div>
  <div class="card-body">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-2 no-print">
      <input type="search" class="form-control form-control-sm" style="max-width:20rem" placeholder="Cari kelompok, AO, CIF, nama..."
             data-cari-tabel="#tabel-tunggakan" data-cari-info="#info-cari" aria-label="Cari">
      <span id="info-cari" class="small text-muted"></span>
      <div class="ms-auto d-flex gap-2">
        <a href="tunggak.php?export=xlsx" class="btn btn-outline-success btn-sm">Export Excel</a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">Cetak</button>
      </div>
    </div>
    <div class="table-responsive">
      <table id="tabel-tunggakan" class="table table-sm table-bordered table-hover table-ni align-middle mb-0">
        <thead>
          <tr>
            <th class="text-center">No</th><th>Unit</th><th>Kelompok</th><th>Nama AO</th><th>CIF</th><th>Nama</th>
            <th class="text-end">FT</th><th class="text-end">Nominal</th>
          </tr>
        </thead>
        <tbody>
        <?php $no = 0; foreach ($baris as $b): $no++; ?>
          <tr>
            <td class="text-center"><?php echo $no; ?></td>
            <td><?php echo h($b['unit']); ?></td>
            <td><?php echo h($b['nama_kel']); ?></td>
            <td><?php echo h($b['nama_ao']); ?></td>
            <td><?php echo h($b['cif']); ?></td>
            <td><?php echo h($b['Cust_Short_name']); ?></td>
            <td class="text-end"><?php echo h($b['ft']); ?></td>
            <td class="text-end"><?php echo number_format(compat_num($b['nominal'])); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$baris): ?>
          <tr data-baris-kosong><td colspan="8" class="text-center text-muted">Tidak ada tunggakan</td></tr>
        <?php endif; ?>
        </tbody>
        <?php if ($baris): ?>
        <tfoot>
          <tr class="fw-bold">
            <td colspan="7" class="text-end">Total</td>
            <td class="text-end"><?php echo number_format($total); ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>
<?php
halaman_akhir();
