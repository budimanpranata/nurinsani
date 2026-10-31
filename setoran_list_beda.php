<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Setoran Beda hari langkah 2: daftar anggota kelompok (loan os > 0, unit user) dengan isian nyata setoran,
 * lalu yang dipilih diproses di realisasi_setoran_kel_beda.php. Perilaku sama dengan versi lama.
 * Perbaikan 2026-10-31 (perintah user): isian nyata setoran dikirim sebagai bulat[CIF] agar proses memasangkannya dengan
 * anggota yang benar (versi lama memasangkan isian ke-i dengan anggota tercentang ke-i).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$code_kel = isset($_GET['code_kel']) ? (string) $_GET['code_kel'] : '';
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

$unit = null;
foreach (db_semua("Select kode_branch from user where userid=?", 's', (string) $userid) as $row) {
    $unit = $row['kode_branch'];
}
// (versi lama juga menghitung saldo simpanan tiap anggota, tetapi tidak pernah ditampilkan)
$data_anggota = db_semua("Select loan.cif,unit,Plafond,ijaroh,angsuran,bulat, bagi_hasil,bulat,pokok,Cust_Short_name,kelompok.nama_kel,(os-saldo_margin) as pokok, saldo_margin, os from loan
inner join kelompok on kelompok.code_kel = loan.code_kel
Where kelompok.code_kel=? and loan.unit=? and os > 0", 'ss', $code_kel, (string) $unit);

halaman_awal('Setoran Kelompok Beda Hari');
?>
<form action="realisasi_setoran_kel_beda.php" method="post" name="myform" id="form-setoran">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">Pilih</th>
          <th>Cif</th>
          <th>Nama</th>
          <th>Kelompok</th>
          <th class="text-end">Pembiayaan</th>
          <th class="text-end">Angsuran</th>
          <th class="text-end">Setoran</th>
          <th>Nyata Setoran</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($data_anggota as $data): ?>
        <tr>
          <td class="text-center"><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo h($data['cif']); ?>" aria-label="Pilih <?php echo h($data['Cust_Short_name']); ?>"></td>
          <td><?php echo h($data['cif']); ?></td>
          <td><?php echo h($data['Cust_Short_name']); ?></td>
          <td><?php echo h($data['nama_kel']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['os'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['angsuran'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['bulat'])); ?></td>
          <td><input type="text" class="form-control form-control-sm text-end nyata-setoran" name="bulat[<?php echo h($data['cif']); ?>]" value="<?php echo h($data['bulat']); ?>" inputmode="numeric" aria-label="Nyata setoran <?php echo h($data['Cust_Short_name']); ?>"></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="fw-bold">
          <td colspan="7" class="text-end">Total</td>
          <td><input name="total" type="text" id="total" class="form-control form-control-sm text-end" value="0" readonly></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-pilih-semua="1">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" data-pilih-semua="0">Clear All</button>
    <button name="submit" type="submit" value="Setorkan" class="btn btn-ni btn-sm ms-auto px-4">Setorkan</button>
  </div>
</form>
<script>
// total semua isian nyata setoran (seperti getTotal() versi lama)
function getTotal() {
    var total = 0;
    document.querySelectorAll('.nyata-setoran').forEach(function (el) {
        total += parseFloat(el.value);
    });
    document.getElementById('total').value = total;
}
document.querySelectorAll('.nyata-setoran').forEach(function (el) {
    el.addEventListener('keyup', getTotal);
});
getTotal();
document.querySelectorAll('[data-pilih-semua]').forEach(function (b) {
    b.addEventListener('click', function () {
        var nilai = b.getAttribute('data-pilih-semua') === '1';
        document.querySelectorAll('#form-setoran input[name="cekbox[]"]').forEach(function (c) {
            c.checked = nilai;
        });
    });
});
</script>
<?php
halaman_akhir();
