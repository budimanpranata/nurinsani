<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Restrukturisasi Jatpo langkah 2: daftar anggota kelompok (OS > 0) untuk dipilih,
 * lalu jatuh temponya diperpanjang di realisasi_rest_kel.php. Perilaku sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
$userid = $_SESSION['userid'];
$code_kel = isset($_GET['code_kel']) ? (string) $_GET['code_kel'] : '';
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}
//cek level user
if ($_SESSION['level'] != 'unit') {
    header('location:index.html');
    exit;
}

include 'indo_tgl.php';

$r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
$unit = $r ? $r['kode_branch'] : null;

$anggota = db_semua("SELECT loan.cif, unit, Plafond, maturity_date, ijaroh, bagi_hasil, bulat, pokok, Cust_Short_name, kelompok.nama_kel, (os-saldo_margin) AS pokok, saldo_margin, os
    FROM loan
    INNER JOIN kelompok ON kelompok.code_kel = loan.code_kel
    WHERE kelompok.code_kel = ? AND loan.unit = ? AND os > 0", 'ss', $code_kel, (string) $unit);

halaman_awal('Restrukturisasi Jatpo');
?>
<form action="realisasi_rest_kel.php" method="post" id="form-realisasi">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">Pilih</th>
          <th>Cif</th>
          <th>Nama</th>
          <th>Kelompok</th>
          <th class="text-end">Pembiayaan</th>
          <th class="text-end">Saldo Margin</th>
          <th class="text-end">Pokok</th>
          <th>Jatpo</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($anggota as $data): ?>
        <tr>
          <td class="text-center"><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo h($data['cif']); ?>" aria-label="Pilih <?php echo h($data['Cust_Short_name']); ?>"></td>
          <td><?php echo h($data['cif']); ?></td>
          <td><?php echo h($data['Cust_Short_name']); ?></td>
          <td><?php echo h($data['nama_kel']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['os'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['saldo_margin'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['os'] - $data['saldo_margin'])); ?></td>
          <td><?php echo h(tgl_indo($data['maturity_date'])); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-pilih-semua="1">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" data-pilih-semua="0">Clear All</button>
    <button name="realisasi" type="submit" value="realisasi" class="btn btn-ni btn-sm ms-auto px-4">Realisasi</button>
  </div>
</form>
<script>
document.querySelectorAll('[data-pilih-semua]').forEach(function (b) {
    b.addEventListener('click', function () {
        var nilai = b.getAttribute('data-pilih-semua') === '1';
        document.querySelectorAll('#form-realisasi input[name="cekbox[]"]').forEach(function (c) {
            c.checked = nilai;
        });
    });
});
</script>
<?php
halaman_akhir();
