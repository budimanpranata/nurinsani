<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Realisasi Tagihan Kelompok langkah 2: daftar anggota kelompok (loan dengan OS > 0) beserta TWM,
 * dipilih lalu dikirim ke realisasi_tag.php. Perilaku sama dengan versi lama.
 * §2b (2026-11-19, perintah user): judul kolom "No Anggota" -> "CIF" (isinya memang CIF); baris TOTAL dari baris yang tampil.
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
    GROUP BY loan.cif ORDER BY loan.cif", 'ss', $code_kel, (string) $unit);

// §2b (2026-11-19, perintah user): baris TOTAL dijumlah dari baris yang tampil (semua status, kolom sesuai judul).
// Versi lama memakai query terpisah khusus MURAB/REST/COVID, kolom TWM berisi total pokok, Saldo TWM total ijaroh,
// Sisa TWM kosong, dan kelompok tanpa MURAB/REST/COVID menampilkan Setoran anggota terakhir.
$total = array('Plafond' => 0, 'bagi_hasil' => 0, 'bulat' => 0, 'angsuran' => 0, 'total_twm' => 0, 'twm' => 0, 'saldo_twm' => 0);

halaman_awal('Realisasi Tagihan Kelompok');
?>
<form action="realisasi_tag.php" method="post" id="form-realisasi">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">Pilih</th>
          <th class="text-center">No</th>
          <th>CIF</th>
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
          foreach (array('Plafond', 'bagi_hasil', 'bulat', 'angsuran') as $k) {
              $total[$k] += compat_num($data[$k]);
          }
          $total['total_twm'] += compat_num($total_twm);
          $total['twm'] += compat_num($twm);
          $total['saldo_twm'] += compat_num($saldo_twm);
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
        <tr class="fw-bold">
          <td colspan="6" class="text-center">TOTAL TRANSAKSI</td>
          <td class="text-end"><?php echo number_format($total['Plafond']); ?></td>
          <td class="text-end"><?php echo number_format($total['bagi_hasil']); ?></td>
          <td class="text-end"><?php echo number_format($total['bulat']); ?></td>
          <td class="text-end"><?php echo number_format($total['angsuran']); ?></td>
          <td class="text-end"><?php echo number_format($total['total_twm']); ?></td>
          <td class="text-end"><?php echo number_format($total['twm']); ?></td>
          <td class="text-end"><?php echo number_format($total['saldo_twm']); ?></td>
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
