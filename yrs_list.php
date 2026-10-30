<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Realisasi Tagihan Kelompok langkah 2: daftar anggota kelompok (loan dengan OS > 0) beserta TWM,
 * dipilih lalu dikirim ke realisasi_tag.php. Perilaku sama dengan versi lama.
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

$anggota = db_semua("SELECT loan.no_anggota, loan.cif, loan.unit, Plafond, angsuran, ijaroh, bagi_hasil, bulat, pokok, loan.Cust_Short_name, kelompok.nama_kel, run_tenor
    FROM loan
    LEFT JOIN anggota ON loan.cif = anggota.cif
    LEFT JOIN kelompok ON kelompok.code_kel = loan.code_kel
    WHERE kelompok.code_kel = ? AND loan.unit = ? AND os > 0
    GROUP BY loan.cif", 'ss', $code_kel, (string) $unit);

$total = db_satu("SELECT SUM(Plafond) AS tot_pla, SUM(bagi_hasil) AS margin, SUM(angsuran) AS angsuran, SUM(pokok) AS pokok, SUM(bulat) AS bulat, SUM(ijaroh) AS ijaroh
    FROM loan
    WHERE code_kel = ? AND unit = ? AND status_app IN ('MURAB','REST','COVID') AND os > 0
    GROUP BY code_kel", 'ss', $code_kel, (string) $unit);

halaman_awal('Realisasi Tagihan Kelompok');
?>
<form action="realisasi_tag.php" method="post" id="form-realisasi">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">Pilih</th>
          <th class="text-center">No</th>
          <th>No Anggota</th>
          <th>Nama</th>
          <th>Unit</th>
          <th>Kelompok</th>
          <th class="text-end">Pembiayaan</th>
          <th class="text-end">Margin</th>
          <th class="text-end">Setoran</th>
          <th class="text-end">Angsuran</th>
          <th class="text-end">TWM</th>
          <th class="text-end">Saldo TWM</th>
          <th class="text-end">Sisa TWM</th>
        </tr>
      </thead>
      <tbody>
      <?php $no = 0; foreach ($anggota as $data):
          $cif = $data['cif'];
          $bulat = $data['bulat'];
          $angsuran = $data['angsuran'];
          $minggon = $bulat - $angsuran;
          $run_tenor = $data['run_tenor'];
          $total_twm = $minggon * $run_tenor;
          $row_sal = db_satu("SELECT (SUM(KREDIT) - SUM(DEBIT)) AS twm FROM simpanan WHERE type NOT IN ('05','04') AND cif = ?", 's', (string) $cif) or die(mysql_error());
          $twm = $row_sal['twm'];
          $saldo_twm = $twm - $total_twm;
          $no++; ?>
        <tr>
          <td class="text-center"><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo h($data['no_anggota']); ?>" aria-label="Pilih <?php echo h($data['Cust_Short_name']); ?>"></td>
          <td class="text-center"><?php echo $no; ?></td>
          <td><?php echo h($data['cif']); ?></td>
          <td><?php echo h($data['Cust_Short_name']); ?></td>
          <td><?php echo h($data['unit']); ?></td>
          <td><?php echo h($data['nama_kel']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['Plafond'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['bagi_hasil'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['bulat'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['angsuran'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($total_twm)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($twm)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($saldo_twm)); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <?php
        // baris total versi lama: kolom TWM berisi total pokok, Saldo TWM berisi total margin (ijaroh).
        // Bila query total kosong, versi lama menampilkan $bulat milik anggota terakhir di tabel; dipertahankan.
        if ($total) {
            $bulat = $total['bulat'];
        }
        ?>
        <tr class="fw-bold">
          <td colspan="6" class="text-center">TOTAL TRANSAKSI</td>
          <td class="text-end"><?php echo number_format(compat_num($total ? $total['tot_pla'] : null)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($total ? $total['margin'] : null)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num(isset($bulat) ? $bulat : null)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($total ? $total['angsuran'] : null)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($total ? $total['pokok'] : null)); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($total ? $total['ijaroh'] : null)); ?></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" data-pilih-semua="1">Select All</button>
    <button type="button" class="btn btn-outline-danger btn-sm" data-pilih-semua="0">Clear All</button>
    <button name="submit" type="submit" value="Realisasi Tagihan" class="btn btn-ni btn-sm ms-auto px-4">Realisasi Tagihan</button>
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
