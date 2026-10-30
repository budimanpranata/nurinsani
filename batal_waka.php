<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Batal Wakalah / Musyarakah: daftar calon_loan unit berstatus APPROVE / MUSYAROKAH
 * untuk dipilih, lalu dibatalkan di realisasi_batal.php. Perilaku sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
$userid = $_SESSION['userid'];
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

$dsmo = db_satu('SELECT nama, kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
$unit = $dsmo ? $dsmo['kode_branch'] : null;
$data_anggota = db_semua("SELECT * FROM calon_loan WHERE unit = ? AND status_app IN ('APPROVE','MUSYAROKAH') ORDER BY code_kel, Cust_Short_name", 's', (string) $unit);

halaman_awal('Batal Wakalah / Musyarakah');
?>
<form action="realisasi_batal.php" method="post" id="form-batal">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">Pilih</th>
          <th>Kode Kelompok</th>
          <th>CIF</th>
          <th>Nama</th>
          <th>Jatuh Tempo</th>
          <th class="text-end">Pembiayaan</th>
          <th class="text-end">Margin</th>
          <th class="text-end">Angsuran</th>
          <th>Tanggal Murabahah</th>
          <th>Unit</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($data_anggota as $data): ?>
        <tr>
          <td class="text-center"><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo h($data['cif']); ?>" aria-label="Pilih <?php echo h($data['Cust_Short_name']); ?>"></td>
          <td><?php echo h($data['code_kel']); ?></td>
          <td><?php echo h($data['cif']); ?></td>
          <td><?php echo h($data['Cust_Short_name']); ?></td>
          <td><?php echo h(tgl_indo($data['maturity_date'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['Plafond'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['bagi_hasil'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['angsuran'])); ?></td>
          <td><?php echo h(tgl_indo($data['tgl_murab'])); ?></td>
          <td><?php echo h($data['unit']); ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$data_anggota): ?>
        <tr><td colspan="10" class="text-center text-muted py-3">Tidak ada data wakalah / musyarakah yang bisa dibatalkan.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-pilih-semua="1">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" data-pilih-semua="0">Clear All</button>
    <input type="hidden" name="ds" value=" ">
    <button name="submit" type="submit" value="Batal" class="btn btn-danger btn-sm ms-auto px-4">Batal</button>
  </div>
</form>
<script>
document.querySelectorAll('[data-pilih-semua]').forEach(function (b) {
    b.addEventListener('click', function () {
        var nilai = b.getAttribute('data-pilih-semua') === '1';
        document.querySelectorAll('#form-batal input[name="cekbox[]"]').forEach(function (c) {
            c.checked = nilai;
        });
    });
});
</script>
<?php
halaman_akhir();
