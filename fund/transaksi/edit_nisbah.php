<?php require_once __DIR__ . '/../../koneksi_db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('fund', 'admin');
/**
 * Data Transaksi > Edit (level fund): ubah nisbah, rate, cara hitung, bank & rekening tujuan bagi hasil satu deposito
 * (?kd = norek); disimpan oleh update_nisbah.php.
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, prepared statement, wajib login. Perbaikan atas perintah user 2026-11-04:
 *   - (A) isian No Rekening bank tujuan dulu terisi norek DEPOSITO sendiri, sehingga Update menimpa deposito.norek_bank
 *     dengan nomor deposito; kini berisi norek_bank
 *   - (H) Nama Deposan hanya tampilan (dulu bisa diubah tetapi tidak pernah disimpan); cara hitung tersimpan langsung
 *     terpilih; norek tidak ditemukan kembali ke daftar (dulu ke master_fund.php di folder transaksi, tidak ada)
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();

$kd = isset($_GET['kd']) ? (string) $_GET['kd'] : '';
$row = db_satu("SELECT norek,rasio,eq_rate,deposito.nama_bank,norek_bank,cara_hitung,CUST_SHORT_NAME
    FROM deposito left join deposan on deposan.CIF = deposito.cif WHERE norek=?", 's', $kd);
if (!$row) {
    header("Location: ../view/master_trans.php");
    exit;
}

halaman_awal('Update Data Nisbah Nur Insani');
?>
<div class="card card-ni">
  <div class="card-header">Update Data Nisbah Nur Insani</div>
  <div class="card-body">
    <form name="form1" id="form1" action="update_nisbah.php" method="POST" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="norek">Norek</label>
        <input type="text" name="norek" id="norek" value="<?php echo h($row['norek']); ?>" class="form-control" readonly>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nama">Nama Deposan</label>
        <input type="text" id="nama" value="<?php echo h($row['CUST_SHORT_NAME']); ?>" class="form-control" disabled>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="rasio">Nisbah</label>
        <input type="text" name="rasio" id="rasio" value="<?php echo h($row['rasio']); ?>" placeholder="Rasio Nisbah" class="form-control" required>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="eq_rate">Rate</label>
        <input type="text" name="eq_rate" id="eq_rate" value="<?php echo h($row['eq_rate']); ?>" placeholder="Eq Rate" class="form-control" required>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="cara_hitung">Jenis Hitung</label>
        <select name="cara_hitung" id="cara_hitung" class="form-select" required>
          <option value="">Pilih Hitung</option>
          <?php foreach (array('1' => 'Nisbah', '2' => 'Rate') as $v => $t): ?>
          <option value="<?php echo $v; ?>"<?php echo (string) $row['cara_hitung'] === (string) $v ? ' selected' : ''; ?>><?php echo $t; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nama_bank">Nama Bank Tujuan</label>
        <input type="text" name="nama_bank" id="nama_bank" value="<?php echo h($row['nama_bank']); ?>" placeholder="Nama Bank" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="norek_bank">No Rekening Bank Tujuan</label>
        <input type="text" name="norek_bank" id="norek_bank" value="<?php echo h($row['norek_bank']); ?>" placeholder="No Rekening" class="form-control" required>
      </div>
      <div class="col-12 text-end">
        <a href="../view/master_trans.php" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" name="update" id="update" value="Update" class="btn btn-ni px-4">Update</button>
      </div>
    </form>
  </div>
</div>
<?php
halaman_akhir();
