<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Report > Nominative Simpanan.
 *   Cari  : simpan pilihan periode di sesi, tampilkan baris unit dengan tombol Rekap.
 *   Rekap : export .xlsx bertahap (3 sheet) dengan indikator progres (report/simpanan_export.php, assets/app/export_xlsx.js);
 *           total nominatif per sheet (dari data per CIF) tampil di jendela progres dan sebagai baris TOTAL di file.
 * Perbaikan 2026-10-31 (perintah user), lihat PEKERJAAN_TERTUNDA.md bagian 26:
 *   - total tidak lagi dihitung di halaman ini (versi lama: NOA dari tabel loan dan Total Saldo selalu 0; untuk EOM memindai
 *     seluruh simpanan per bulan). Menghitung total per CIF sama beratnya dengan export, jadi dihitung sekali saat Rekap.
 *   - status EOM wajib memilih bulan dan tahun (export EOM kini benar-benar memakai periode itu)
 *   - pesan tidak ditemukan mengarah ke krt.php (versi lama Krt.php, gagal di server Linux)
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    die("Maaf Sesion anda habis,  Anda harus login lembali"); //jika belum login jangan lanjut..
}
//cek level user
if ($_SESSION['level'] != "unit") {
    die("Anda bukan user"); //jika bukan user jangan lanjut
}

$userid = $_SESSION['userid'];
$unit = null;
foreach (db_semua("select kode_branch from user where userid=?", 's', (string) $userid) as $id_user) {
    $unit = $id_user['kode_branch'];
}

$bulan1 = isset($_POST['bulan']) ? (string) $_POST['bulan'] : '';
$tahun1 = isset($_POST['tahun']) ? (string) $_POST['tahun'] : '';
$status = isset($_POST['status']) ? (string) $_POST['status'] : '';

$nama_bulan = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Des');
$pesan = array();
$hasil = array();
if (isset($_POST['search'])) {
    if (empty($status)) {
        $pesan[] = "<script>alert('Maaf,Isi Status Terlebih Dahulu  !!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='krt.php'</script>";
    } elseif ($status === 'EOM' && (!ctype_digit($bulan1) || (int) $bulan1 < 1 || (int) $bulan1 > 12 || !ctype_digit($tahun1))) {
        $pesan[] = "<script>alert('Untuk status EOM, pilih Bulan dan Tahun terlebih dahulu.');</script>";
    } elseif ($unit === null) {
        $pesan[] = "<script>alert('Maaf...data tidak ditemukan!!!');</script>";
        $pesan[] = "<script language='javascript'>document.location.href='krt.php' </script>";
    } else {
        $_SESSION['bulan'] = $bulan1;
        $_SESSION['tahun'] = $tahun1;
        $_SESSION['status'] = $status;
        $_SESSION['unt'] = $unit;

        // neraca sebagai pembanding (permintaan user): posisi saat ini di tabel_master, jadi hanya untuk CURRENT
        $neraca = array();
        if ($status === 'CURRENT') {
            foreach (db_semua("select kode_rekening, saldo_akhir from tabel_master where unit=? and kode_rekening in ('2101000','3102000','3202000')", 's', (string) $unit) as $n) {
                $neraca[(string) $n['kode_rekening']] = $n['saldo_akhir'];
            }
        }
        $hasil[] = array(
            'unit' => $unit,
            'periode' => $status === 'EOM' ? 'EOM ' . $nama_bulan[(int) $bulan1] . ' ' . $tahun1 : 'CURRENT',
            'neraca' => $neraca,
        );
    }
}

halaman_awal('Nominative Simpanan');
?>
<div class="card card-ni">
  <div class="card-header">Kriteria Pencarian</div>
  <div class="card-body">
    <form method="post" action="krt.php" name="form" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="status" class="col-sm-4 col-form-label">Status Nominative</label>
        <div class="col-sm-8">
          <select name="status" id="status" class="form-select form-select-sm">
            <option value="">- Select Status -</option>
            <option value="CURRENT"<?php echo $status === 'CURRENT' ? ' selected' : ''; ?>>CURRENT</option>
            <option value="EOM"<?php echo $status === 'EOM' ? ' selected' : ''; ?>>EOM</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3" id="baris-periode">
        <label for="bulan" class="col-sm-4 col-form-label">Bulan dan Tahun</label>
        <div class="col-sm-4">
          <select name="bulan" id="bulan" class="form-select form-select-sm">
            <option value="">- Select Month -</option>
            <?php foreach ($nama_bulan as $no => $nm): ?>
            <option value="<?php echo $no; ?>"<?php echo $bulan1 === (string) $no ? ' selected' : ''; ?>><?php echo $nm; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4">
          <select name="tahun" id="tahun" class="form-select form-select-sm">
            <option>Tahun</option>
            <?php for ($th = (int) date('Y'); $th >= 2020; $th--): ?>
            <option value="<?php echo $th; ?>"<?php echo $tahun1 === (string) $th ? ' selected' : ''; ?>><?php echo $th; ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="text-end">
        <button name="search" type="submit" id="submit2" value="Cari Report" class="btn btn-ni btn-sm px-4">Cari Report</button>
      </div>
    </form>
  </div>
</div>

<?php echo implode("\n", $pesan); ?>

<div class="table-responsive mt-3">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th class="text-center">Branch</th>
        <th>Periode</th>
        <th>Total Nominatif</th>
        <th class="text-end">Neraca Wadiah<div class="small fw-normal">2101000 (pembanding)</div></th>
        <th class="text-end">Neraca Pokok<div class="small fw-normal">3102000 (pembanding)</div></th>
        <th class="text-end">Neraca Wajib<div class="small fw-normal">3202000 (pembanding)</div></th>
        <th class="text-center">Cetak Nominative</th>
        <th class="text-center">Export</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($hasil as $data): ?>
      <tr>
        <td class="text-center"><?php echo h($data['unit']); ?></td>
        <td><?php echo h($data['periode']); ?></td>
        <td class="small text-muted">Dihitung dari data per CIF saat Rekap (tampil di jendela progres dan baris TOTAL di file)</td>
        <?php foreach (array('2101000', '3102000', '3202000') as $kode): ?>
        <td class="text-end"><?php echo isset($data['neraca'][$kode]) ? number_format(compat_num($data['neraca'][$kode])) : '-'; ?></td>
        <?php endforeach; ?>
        <?php // versi lama: link ke /cetak_tag.php dengan $tipe yang tidak pernah diisi (file tidak ada) ?>
        <td class="text-center"><a href="/cetak_tag.php?type=" class="btn btn-outline-secondary btn-sm" data-konfirmasi="Apakah Anda yakin?">Cetak</a></td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-success btn-sm" data-export-xlsx data-export-url="simpanan_export.php" data-export-satuan="rekening">Rekap (.xlsx)</button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="modal-export" tabindex="-1" aria-labelledby="judul-export" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="judul-export">Export Rekap Nominative Simpanan</h2>
      </div>
      <div class="modal-body">
        <div class="progress" role="progressbar" aria-label="Progres export" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="height: 1.25rem;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
        </div>
        <div class="small text-muted mt-2" data-export-teks aria-live="polite">Menyiapkan data…</div>
        <div data-export-ringkasan></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm d-none" data-bs-dismiss="modal" data-export-tutup>Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
// Bulan dan Tahun hanya dipakai untuk status EOM (maksud skrip versi lama, yang tidak jalan karena jQuery tidak dimuat)
(function () {
    var status = document.getElementById('status');
    var periode = document.getElementById('baris-periode');
    function atur() { periode.classList.toggle('d-none', status.value !== 'EOM'); }
    status.addEventListener('change', atur);
    atur();
})();
</script>
<?php
halaman_akhir(array('assets/app/export_xlsx.js'));
