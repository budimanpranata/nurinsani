<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Home (level unit): sambutan dan cocokkan saldo nominatif dengan neraca (tabel_master) unit user.
 * Perhitungan dan perbandingan sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 * Versi lama memeriksa login di akhir halaman (sesudah query dan tabel tampil); kini di awal.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
//cek level user
if ($_SESSION['level'] != "unit") {
    die("Anda bukan user"); //jika bukan user jangan lanjut
}
$userid = $_SESSION['userid'];

/** Satu nilai dari query (sebagai teks, seperti mysql_fetch_array versi lama), atau null. */
function nilai_home($sql, $kolom)
{
    $baris = db_satu($sql, 's', (string) $GLOBALS['userid']);
    return $baris && $baris[$kolom] !== null ? (string) $baris[$kolom] : null;
}

/** saldo_akhir rekening neraca unit user di tabel_master. */
function neraca_home($kode_rekening)
{
    $baris = db_satu("SELECT saldo_akhir FROM tabel_master WHERE unit =? and kode_rekening =?", 'ss', (string) $GLOBALS['userid'], $kode_rekening);
    return $baris ? (string) $baris['saldo_akhir'] : null;
}

$user = db_satu("SELECT * FROM user WHERE userid=?", 's', (string) $userid);
$baris_cek = array();
if ($user) {
    $cek_os = db_satu("SELECT sum(os) as os ,sum(saldo_margin) as saldo_margin FROM loan  WHERE unit  =? group by unit", 's', (string) $userid);
    $os = $cek_os && $cek_os['os'] !== null ? (string) $cek_os['os'] : null;
    $margin = $cek_os && $cek_os['saldo_margin'] !== null ? (string) $cek_os['saldo_margin'] : null;
    $coa_os = compat_num(neraca_home('1411000')) + compat_num(neraca_home('1413000'));
    $coa_margin = neraca_home('1423000');

    // versi lama membaca kolom 'simpanan' padahal alias query-nya simpanan_pokok / simpanan_wajib,
    // sehingga nominatif simpanan pokok dan wajib selalu kosong (0); dipertahankan
    $pokok = null;
    $wajib = null;
    $coa_pokok = neraca_home('3102000');
    $coa_wajib = neraca_home('3202000');

    $simpanan = nilai_home("SELECT sum(KREDIT-DEBIT) as simpanan  FROM simpanan  WHERE unit  =? group by unit", 'simpanan');
    $coa_wadiah = neraca_home('2101000');

    // urutan baris tabel sama dengan versi lama
    $baris_cek = array(
        array('Outstanding', $os, $coa_os),
        array('Saldo Margin', $margin, $coa_margin),
        array('Simpanan Wadiah', $simpanan, $coa_wadiah),
        array('Simpanan Pokok', $pokok, $coa_pokok),
        array('Simpanan Wajib', $wajib, $coa_wajib),
    );
}

halaman_awal('Home');
?>
<?php if ($user): ?>
<div class="card card-ni mb-3">
  <div class="card-body">
    <h1 class="h5 mb-1">Selamat Datang <?php echo h($user['nama']); ?></h1>
    <div class="text-muted small">Di Sistem Informasi KSPPS NurInsani</div>
  </div>
</div>

<div class="card card-ni">
  <div class="card-header">Kecocokan Nominatif dan Neraca</div>
  <div class="table-responsive">
    <table class="table table-sm table-ni align-middle mb-0">
      <thead>
        <tr>
          <th>Jenis Transaksi</th>
          <th class="text-end">Nominatif</th>
          <th class="text-end">Neraca</th>
          <th class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($baris_cek as $b):
          // perbandingan sama dengan versi lama ($nominatif == $neraca)
          $cocok = $b[1] == $b[2];
      ?>
        <tr>
          <td><?php echo h($b[0]); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($b[1])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($b[2])); ?></td>
          <td class="text-center">
            <?php if ($cocok): ?>
              <span class="badge text-bg-success">Done</span>
            <?php else: ?>
              <span class="badge text-bg-danger">Tidak Sesuai</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php
halaman_akhir();
