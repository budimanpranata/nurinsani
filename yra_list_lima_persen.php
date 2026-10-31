<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Setoran 5 Persen langkah 2: daftar anggota kelompok (calon_loan MURAB, os > 0) beserta simpanan 5% dari plafond,
 * lalu yang dipilih diproses di realisasi_simpanan_lima_persen.php. Perilaku sama dengan versi lama.
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
include "indo_tgl.php";

$code_kel = isset($_GET['code_kel']) ? (string) $_GET['code_kel'] : '';
$data_anggota = array();
if ($code_kel) {
    $data_anggota = db_semua("Select * from calon_loan where code_kel=? and os > 0 and status_app ='MURAB'", 's', $code_kel);
}
$tot_pla = 0;
$tot_angsuran = 0;
$persen = 0;

halaman_awal('Realisasi Simpanan 5%');
?>
<form action="realisasi_simpanan_lima_persen.php" method="post" id="form-realisasi">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">No</th>
          <th class="text-center">Pilih</th>
          <th>Kode Kelompok</th>
          <th>Nama</th>
          <th>Tgl Akad</th>
          <th class="text-end">Pembiayaan</th>
          <th class="text-end">Angsuran</th>
          <th class="text-end">5 %</th>
        </tr>
      </thead>
      <tbody>
      <?php $no = 0; foreach ($data_anggota as $data):
          $tot_pla += compat_num($data['Plafond']);
          $tot_angsuran += compat_num($data['angsuran']);
          $persen += compat_num($data['Plafond']) * 5 / 100;
          $no++; ?>
        <tr>
          <td class="text-center"><?php echo $no; ?></td>
          <td class="text-center"><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo h($data['cif']); ?>" aria-label="Pilih <?php echo h($data['Cust_Short_name']); ?>"></td>
          <td><?php echo h($data['code_kel']); ?></td>
          <td><?php echo h($data['Cust_Short_name']); ?></td>
          <td><?php echo h(tgl_indo($data['tgl_murab'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['Plafond'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['angsuran'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['Plafond']) * 5 / 100); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="fw-bold">
          <td colspan="5" class="text-center">TOTAL TRANSAKSI</td>
          <td class="text-end"><?php echo number_format(compat_num($tot_pla)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($tot_angsuran)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($persen)); ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-pilih-semua="1">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" data-pilih-semua="0">Clear All</button>
    <input type="hidden" name="ds" value=" ">
    <button name="submit" type="submit" value="Realisasi Simpanan" class="btn btn-ni btn-sm ms-auto px-4">Realisasi Simpanan</button>
  </div>
</form>
<script>
// Perbaikan 2026-10-31 (perintah user): cegah klik ganda. Setelah form terkirim, kiriman berikutnya diabaikan
// dan tombol dinonaktifkan (setelah data form terbentuk, agar isi kiriman sama dengan versi lama).
(function () {
    var form = document.getElementById('form-realisasi');
    var terkirim = false;
    form.addEventListener('submit', function (e) {
        if (terkirim) {
            e.preventDefault();
            return;
        }
        terkirim = true;
        var tombol = form.querySelector('button[type="submit"]');
        setTimeout(function () {
            tombol.disabled = true;
            tombol.textContent = 'Memproses…';
        }, 0);
    });
})();
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
