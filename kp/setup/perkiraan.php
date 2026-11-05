<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu kp > Report > Akuntasi > Setup Parameter > Tambah Coa: tambah rekening COA baru (tabel coa) sekaligus baris
 * tabel_master untuk setiap unit (rekening_gl = <unit>-<kode>), dan daftar seluruh COA.
 * Dimigrasi 2026-11-03: tampilan Bootstrap 5, query prepared statement, simpan lalu kembali ke halaman ini (POST lalu
 * redirect, pesan sama dengan versi lama). Logika simpan sama dengan versi lama, termasuk:
 *   - posisi: PASIVA/AKTIVA -> 'Neraca'; Rugi Laba -> 'rugi-laba'; lainnya 'admin'
 *   - tanggal_awal, awal_debet, awal_kredit diisi kosong (variabelnya tidak pernah diisi di versi lama); isian Tanggal
 *     Buka tidak dipakai
 *   - tabel_master diisi per unit tanpa transaksi; pesan sukses/gagal menurut insert coa
 * Perbaikan atas perintah user 2026-11-03: Rugi Laba dulu tersimpan 'admin' (dibandingkan dengan 'Rugi-laba'); kode/nama
 * wajib diisi, Group Header wajib dipilih, dan kode yang sudah ada di coa atau tabel_master ditolak (dulu coa dobel).
 * Daftar COA: DataTables lokal (assets/media) untuk cari/urut/halaman; tombol copy/csv/excel/pdf/print versi lama (CDN)
 * diganti Export .xlsx dengan indikator progres (kp/setup/coa_export.php; permintaan user: export kp seperti menu unit).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
ob_start();

if (isset($_POST['kode_rekening'])) {
    $kode_rekening = trim((string) $_POST['kode_rekening']);
    $nama_rekening = ucwords(trim(isset($_POST['nama_rekening']) ? (string) $_POST['nama_rekening'] : ''));
    $normal = isset($_POST['normal']) ? (string) $_POST['normal'] : '';
    $gr_neraca = isset($_POST['posisi']) ? (string) $_POST['posisi'] : '';
    $gr_sub = isset($_POST['gr_sub']) ? (string) $_POST['gr_sub'] : '';
    if ($gr_neraca == 'PASIVA') {
        $posisi = 'Neraca';
    } elseif ($gr_neraca == 'AKTIVA') {
        $posisi = 'Neraca';
    } elseif ($gr_neraca == 'rugi-laba') {
        // versi lama membandingkan dengan 'Rugi-laba' sehingga jatuh ke 'admin' (diperbaiki atas perintah user 2026-11-03)
        $posisi = 'rugi-laba';
    } else {
        $posisi = 'admin';
    }
    // pemeriksaan isian (perintah user 2026-11-03): versi lama menyimpan kode/nama kosong, teks "Pilih Salah satu"
    // sebagai gr_sub, dan kode yang sudah ada (coa dobel, tabel_master gagal diam-diam)
    $salah = null;
    if ($kode_rekening === '' || $nama_rekening === '') {
        $salah = 'Kode COA dan Nama Rekening wajib diisi';
    } elseif (!db_satu("select gr_sub from coa where gr_sub > 1 and gr_sub = ? limit 1", 's', $gr_sub)) {
        $salah = 'Pilih Group Header';
    } elseif (db_satu("select kode_rek from coa where kode_rek = ? limit 1", 's', $kode_rekening)
        || db_satu("select rekening_gl from tabel_master where kode_rekening = ? limit 1", 's', $kode_rekening)) {
        $salah = 'Gagal di simpan No Gl sudah tersedia';
    }
    if ($salah !== null) {
        $_SESSION['pesan_coa'] = array('danger', $salah);
        header('Location: perkiraan.php');
        exit;
    }
    foreach (db_semua("select unit from tabel_master group by unit order by unit") as $data) {
        $unit = (string) $data['unit'];
        db_jalankan("insert into tabel_master(rekening_gl,kode_rekening,nama_rekening,tanggal_awal,awal_debet,awal_kredit,posisi,normal,unit,gr_sub,gr_neraca,LINE_BALANCE)
            values(?,?,?,'','','',?,?,?,?,?,'DETAIL')", 'ssssssss',
            $unit . '-' . $kode_rekening, $kode_rekening, $nama_rekening, $posisi, $normal, $unit, $gr_sub, $gr_neraca);
    }
    $ok = db_jalankan("insert into coa(kode_rek,nama_rek,posisi,normal,LINE_BALANCE) values(?,?,?,?,'DETAIL')", 'ssss',
        $kode_rekening, $nama_rekening, $posisi, $normal);
    $_SESSION['pesan_coa'] = $ok ? array('success', 'Sukses di simpan') : array('danger', 'Gagal di simpan No Gl sudah tersedia. ' . db_error_terakhir());
    header('Location: perkiraan.php');
    exit;
}

$pesan = isset($_SESSION['pesan_coa']) ? $_SESSION['pesan_coa'] : null;
unset($_SESSION['pesan_coa']);
$grup = db_semua("select gr_sub,nama_rek from coa where gr_sub > 1 group by gr_sub order by gr_sub");
$coa = db_semua("select kode_rek,nama_rek,posisi,normal from coa order by kode_rek asc");

halaman_awal('Setup Coa', array('assets/app/datatables-ni.css'));
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?>"><?php echo h($pesan[1]); ?></div>
<script>alert(<?php echo json_encode($pesan[0] === 'success' ? 'Sukses di simpan' : (string) strtok($pesan[1], '.')); ?>);</script>
<?php endif; ?>

<div class="card card-ni">
  <div class="card-header">System Parameter</div>
  <div class="card-body">
    <form action="perkiraan.php" method="post" name="form" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode_rekening" class="col-sm-3 col-form-label">Kode COA</label>
        <div class="col-sm-9"><input name="kode_rekening" id="kode_rekening" type="text" class="form-control form-control-sm" placeholder="COA"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="nama_rekening" class="col-sm-3 col-form-label">Nama Rekening</label>
        <div class="col-sm-9"><input name="nama_rekening" id="nama_rekening" type="text" class="form-control form-control-sm" placeholder="Nama Rekening"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="normal" class="col-sm-3 col-form-label">Normal balance</label>
        <div class="col-sm-9">
          <select class="form-select form-select-sm" name="normal" id="normal">
            <option value="debet">Debet</option>
            <option value="kredit">Kredit</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="gr_sub" class="col-sm-3 col-form-label">Group Header</label>
        <div class="col-sm-9">
          <select class="form-select form-select-sm" name="gr_sub" id="gr_sub">
            <option>Pilih Salah satu</option>
            <?php foreach ($grup as $g): ?>
            <option value="<?php echo h($g['gr_sub']); ?>"><?php echo h($g['gr_sub'] . '-' . $g['nama_rek']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="posisi" class="col-sm-3 col-form-label">Posisi</label>
        <div class="col-sm-9">
          <select name="posisi" id="posisi" class="form-select form-select-sm">
            <option value="PASIVA">Pasiva</option>
            <option value="AKTIVA">Aktiva</option>
            <option value="rugi-laba">Rugi Laba</option>
            <option value="admin">Administratif</option>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="tgl_akhir" class="col-sm-3 col-form-label">Tanggal Buka</label>
        <div class="col-sm-9"><input name="tgl_akhir" id="tgl_akhir" type="date" class="form-control form-control-sm"></div>
      </div>
      <div class="text-end">
        <button type="submit" class="btn btn-ni btn-sm px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="card card-ni mw-100 mt-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Daftar COA (<?php echo count($coa); ?> rekening)</span>
    <button type="button" class="btn btn-outline-success btn-sm" data-export-xlsx data-export-url="coa_export.php" data-export-satuan="rekening">Export (.xlsx)</button>
  </div>
  <div class="card-body">
    <table id="ghatable" class="table table-sm table-bordered table-ni align-middle w-100">
      <thead>
        <tr><th>Kode Rekening</th><th>Nama Rekening</th><th>Posisi</th><th>Normal</th></tr>
      </thead>
      <tbody>
      <?php foreach ($coa as $row): ?>
        <tr>
          <td class="text-center"><?php echo h($row['kode_rek']); ?></td>
          <td><?php echo h($row['nama_rek']); ?></td>
          <td><?php echo h($row['posisi']); ?></td>
          <td><?php echo h($row['normal']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modal-export" tabindex="-1" aria-labelledby="judul-export" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-6" id="judul-export">Export Daftar COA</h2>
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
<?php
halaman_akhir(array(
    'assets/media/js/jquery.js',
    'assets/media/js/jquery.dataTables.js',
    'assets/media/js/dataTables.bootstrap.js',
    'assets/app/export_xlsx.js',
    'assets/app/coa_tabel.js',
));
