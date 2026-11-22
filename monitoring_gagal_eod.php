<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Realisasi -> Monitoring Gagal EOD (2026-11-22, perintah user, P2-10).
 * Daftar anggota yang TIDAK direalisasi oleh EOD Realisasi Murabahah / Musyarakah (tabel gagal_loan, diisi EOD bila
 * OS lama masih ada atau proses anggota itu gagal & dibatalkan). Status dihitung langsung saat halaman dibuka:
 *   - masih ada di calon_loan + OS lama > 0  -> belum direalisasi, OS lama harus diselesaikan dulu
 *   - masih ada di calon_loan, OS lama 0     -> belum direalisasi (proses EOD gagal), bisa direalisasi dari sini
 *   - sudah ada di loan dengan tgl akad >= tgl gagal -> sudah direalisasi
 * Tombol Realisasi mengirim anggota terpilih ke proses manual yang sudah ada (realisasi_pinjaman.php /
 * realisasi_musyarakah.php); halaman ini sendiri tidak menulis data.
 */
require_once __DIR__ . '/inc/tampilan.php';
require_once __DIR__ . '/inc/csrf.php';
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login();
wajib_level('unit');
include 'indo_tgl.php';

$userid = (string) $_SESSION['userid'];
$tgl_system = isset($_SESSION['stgl_system']) ? substr((string) $_SESSION['stgl_system'], 0, 10) : date('Y-m-d');
$r_unit = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', $userid);
$unit = $r_unit ? (string) $r_unit['kode_branch'] : '';

$tgl_valid = function ($t) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $t) ? $t : ''; };
$dari = $tgl_valid(isset($_GET['dari']) ? (string) $_GET['dari'] : '');
$sampai = $tgl_valid(isset($_GET['sampai']) ? (string) $_GET['sampai'] : '');
if ($dari === '') $dari = date('Y-m-d', strtotime('-60 days', strtotime($tgl_system)));
if ($sampai === '') $sampai = $tgl_system;

$baris = db_semua("SELECT g.tgl_akad, g.cif, g.no_anggota, g.Cust_Short_name, g.code_kel, g.status_app, g.Plafond,
        c.cif AS c_cif, c.status_app AS c_status, l.os AS l_os, l.tgl_akad AS l_akad
    FROM gagal_loan g
    LEFT JOIN calon_loan c ON c.cif = g.cif
    LEFT JOIN loan l ON l.cif = g.cif
    WHERE g.unit = ? AND g.tgl_akad BETWEEN ? AND ?
    ORDER BY g.tgl_akad DESC, g.code_kel, g.Cust_Short_name", 'sss', $unit, $dari . ' 00:00:00', $sampai . ' 23:59:59');

$grup = array('MURAB' => array(), 'MUSYAROKAH' => array());
foreach ($baris as $b) {
    $os = compat_num($b['l_os']);
    if ($b['c_cif'] !== null && $b['c_cif'] !== '') {
        if ($os > 0) {
            $b['_status'] = 'Belum direalisasi: OS lama masih ' . number_format($os);
            $b['_warna'] = 'text-danger';
            $b['_pilih'] = false;
        } else {
            $b['_status'] = 'Belum direalisasi: proses EOD gagal';
            $b['_warna'] = 'text-danger';
            $b['_pilih'] = in_array($b['c_status'], array('MURAB', 'MUSYAROKAH'), true);
        }
    } elseif ($b['l_akad'] !== null && substr((string) $b['l_akad'], 0, 10) >= substr((string) $b['tgl_akad'], 0, 10)) {
        $b['_status'] = 'Sudah direalisasi';
        $b['_warna'] = 'text-success';
        $b['_pilih'] = false;
    } else {
        $b['_status'] = 'Tidak ada di calon pembiayaan (dibatalkan/dihapus)';
        $b['_warna'] = 'text-muted';
        $b['_pilih'] = false;
    }
    $akad = $b['status_app'] === 'MUSYAROKAH' ? 'MUSYAROKAH' : 'MURAB';
    $grup[$akad][] = $b;
}

halaman_awal('Monitoring Gagal EOD');
?>
<form method="get" class="row g-2 align-items-end mb-3">
  <div class="col-auto">
    <label class="form-label mb-0 small" for="dari">Tanggal akad dari</label>
    <input type="date" class="form-control form-control-sm" id="dari" name="dari" value="<?php echo h($dari); ?>">
  </div>
  <div class="col-auto">
    <label class="form-label mb-0 small" for="sampai">sampai</label>
    <input type="date" class="form-control form-control-sm" id="sampai" name="sampai" value="<?php echo h($sampai); ?>">
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-ni btn-sm px-4">Tampilkan</button>
  </div>
</form>
<p class="small text-muted mb-3">Anggota yang tidak direalisasi oleh proses EOD Realisasi Murabahah / Musyarakah (OS lama masih ada,
atau proses anggota itu gagal dan dibatalkan). Anggota berstatus merah tanpa OS lama dapat dipilih lalu direalisasi.</p>
<?php
$tujuan = array('MURAB' => array('realisasi_pinjaman.php', 'no_anggota', 'Murabahah'), 'MUSYAROKAH' => array('realisasi_musyarakah.php', 'cif', 'Musyarakah'));
foreach ($grup as $akad => $isi):
    $t = $tujuan[$akad];
    $ada_pilih = false;
    foreach ($isi as $b) if ($b['_pilih']) $ada_pilih = true;
?>
<h6 class="mt-3"><?php echo h($t[2]); ?> (<?php echo count($isi); ?>)</h6>
<form action="<?php echo h($t[0]); ?>" method="post" id="form-<?php echo h($akad); ?>">
  <?php echo csrf_field(); ?>
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni table-hover align-middle">
      <thead>
        <tr>
          <th class="text-center">Pilih</th>
          <th class="text-center">No</th>
          <th>Tgl Akad</th>
          <th>CIF</th>
          <th>No Anggota</th>
          <th>Nama</th>
          <th>Kelompok</th>
          <th class="text-end">Plafond</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$isi): ?>
        <tr><td colspan="9" class="text-center text-muted">Tidak ada data</td></tr>
      <?php endif; ?>
      <?php $no = 0; foreach ($isi as $b): $no++; ?>
        <tr>
          <td class="text-center"><?php if ($b['_pilih']): ?><input type="checkbox" class="form-check-input" name="cekbox[]" value="<?php echo h($b[$t[1]]); ?>" aria-label="Pilih <?php echo h($b['Cust_Short_name']); ?>"><?php endif; ?></td>
          <td class="text-center"><?php echo $no; ?></td>
          <td><?php echo h(tgl_indo(substr((string) $b['tgl_akad'], 0, 10))); ?></td>
          <td><?php echo h($b['cif']); ?></td>
          <td><?php echo h($b['no_anggota']); ?></td>
          <td><?php echo h($b['Cust_Short_name']); ?></td>
          <td><?php echo h($b['code_kel']); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($b['Plafond'])); ?></td>
          <td class="<?php echo h($b['_warna']); ?>"><?php echo h($b['_status']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($ada_pilih): ?>
  <div class="d-flex flex-wrap gap-2">
    <input type="hidden" name="ds" value=" ">
    <button name="submit" type="submit" value="Realisasi Pembiayaan" class="btn btn-ni btn-sm ms-auto px-4"
            data-konfirmasi="Realisasi <?php echo h($t[2]); ?> untuk anggota terpilih?">Realisasi <?php echo h($t[2]); ?></button>
  </div>
  <?php endif; ?>
</form>
<?php endforeach;
halaman_akhir();
