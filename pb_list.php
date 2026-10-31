<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * PB PerKelompok langkah 2: daftar anggota kelompok (loan os > 0, unit user) dengan saldo simpanan wajib
 * dan isian nominal PB, lalu yang dipilih diproses di realisasi_pb.php. Perilaku sama dengan versi lama.
 * Perbaikan 2026-10-31 (perintah user):
 *   - nominal PB dikirim sebagai bulat[CIF] agar dipasangkan dengan anggota yang benar
 *     (versi lama memasangkan isian ke-i dengan anggota tercentang ke-i)
 *   - kolom Pokok menampilkan saldo simpanan pokok (versi lama selalu 0 karena salah nama variabel)
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
include "indo_tgl.php";

$unit = null;
foreach (db_semua("Select kode_branch from user where userid=?", 's', (string) $userid) as $row) {
    $unit = $row['kode_branch'];
}
$data_anggota = db_semua("Select loan.cif,unit,Plafond,ijaroh,bagi_hasil,bulat,pokok,Cust_Short_name,kelompok.nama_kel,(bulat-angsuran) as sk from loan
inner join kelompok on kelompok.code_kel = loan.code_kel
Where kelompok.code_kel=? and loan.unit=? and os >0", 'ss', $code_kel, (string) $unit);

halaman_awal('PB Kelompok');
?>
<form action="realisasi_pb.php" method="post" id="form-pb">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">Pilih</th>
          <th>Cif</th>
          <th>Nama</th>
          <th>Unit</th>
          <th>Kelompok</th>
          <th class="text-end">Wajib</th>
          <th class="text-end">Pokok</th>
          <th>Nominal PB</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($data_anggota as $data):
          $wajib = db_satu("select SUM(KREDIT-DEBIT) as wajib from simpanan_wajib where cif =?", 's', (string) $data['cif']);
          $pokok = db_satu("select SUM(KREDIT-DEBIT) as pokok from simpanan_pokok where cif =?", 's', (string) $data['cif']);
      ?>
        <tr>
          <td class="text-center"><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo h($data['cif']); ?>" aria-label="Pilih <?php echo h($data['Cust_Short_name']); ?>"></td>
          <td><?php echo h($data['cif']); ?></td>
          <td><?php echo h($data['Cust_Short_name']); ?></td>
          <td><?php echo h($data['unit']); ?></td>
          <td><?php echo h($data['nama_kel']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($wajib ? $wajib['wajib'] : null)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($pokok ? $pokok['pokok'] : null)); ?></td>
          <td><input type="text" class="form-control form-control-sm text-end" name="bulat[<?php echo h($data['cif']); ?>]" value="0" inputmode="numeric" aria-label="Nominal PB <?php echo h($data['Cust_Short_name']); ?>"></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-pilih-semua="1">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" data-pilih-semua="0">Clear All</button>
    <button name="submit" type="submit" value="Realisasi Tabungan" class="btn btn-ni btn-sm ms-auto px-4">Realisasi Tabungan</button>
  </div>
</form>
<script>
document.querySelectorAll('[data-pilih-semua]').forEach(function (b) {
    b.addEventListener('click', function () {
        var nilai = b.getAttribute('data-pilih-semua') === '1';
        document.querySelectorAll('#form-pb input[name="cekbox[]"]').forEach(function (c) {
            c.checked = nilai;
        });
    });
});
</script>
<?php
halaman_akhir();
