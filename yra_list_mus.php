<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Realisasi Musyarakah langkah 2: daftar anggota kelompok berstatus MUSYAROKAH untuk dipilih,
 * lalu dikirim ke realisasi_musyarakah.php. Perilaku sama dengan versi lama.
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

$code_kel = isset($_GET['code_kel']) ? (string) $_GET['code_kel'] : '';
$data_anggota = array();
if ($code_kel !== '') {
    $data_anggota = db_semua("SELECT * FROM calon_loan WHERE code_kel = ? AND status_app = 'MUSYAROKAH'", 's', $code_kel);
}
$tot_pla = 0;
$margin = 0;
$angsuran = 0;

halaman_awal('Realisasi Musyarakah');
?>
<form action="realisasi_musyarakah.php" method="post" id="form-realisasi">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">No</th>
          <th class="text-center">Pilih</th>
          <th>Kode Kelompok</th>
          <th>Nama</th>
          <th>Jatuh Tempo</th>
          <th class="text-end">Pembiayaan</th>
          <th class="text-end">Margin</th>
          <th class="text-end">Angsuran</th>
          <th>Tanggal Akad</th>
          <th>Unit</th>
        </tr>
      </thead>
      <tbody>
      <?php $no = 0; foreach ($data_anggota as $data):
          $tot_pla += compat_num($data['Plafond']);
          $margin += compat_num($data['bagi_hasil']);
          $angsuran += compat_num($data['angsuran']);
          $no++; ?>
        <tr>
          <td class="text-center"><?php echo $no; ?></td>
          <td class="text-center"><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo h($data['cif']); ?>" aria-label="Pilih <?php echo h($data['Cust_Short_name']); ?>"></td>
          <td><?php echo h($data['code_kel']); ?></td>
          <td><?php echo h($data['Cust_Short_name']); ?></td>
          <td><?php echo h(tgl_indo($data['maturity_date'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['Plafond'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['bagi_hasil'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['angsuran'])); ?></td>
          <td><?php echo h(tgl_indo($data['tgl_akad'])); ?></td>
          <td><?php echo h($data['unit']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="fw-bold">
          <td colspan="5" class="text-center">TOTAL TRANSAKSI</td>
          <td class="text-end"><?php echo number_format(compat_num($tot_pla)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($margin)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($angsuran)); ?></td>
          <td></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-pilih-semua="1">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" data-pilih-semua="0">Clear All</button>
    <input type="hidden" name="ds" value=" ">
    <button name="submit" type="submit" value="Realisasi Pembiayaan" class="btn btn-ni btn-sm ms-auto px-4">Realisasi Pembiayaan</button>
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
