<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data Master Deposan > Edit (level fund): ubah data deposan (?kd = CIF); disimpan oleh update-edit.php.
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, prepared statement, wajib login (dulu tanpa cek sendiri). CIF tidak
 * ditemukan kembali ke daftar ../view/master_fund.php (versi lama ke master_fund.php di folder transaksi, tidak ada).
 * Enkripsi (PEKERJAAN_TERTUNDA.md bagian 87): NIK dan alamat didekripsi untuk diisikan ke form (NIK lengkap, karena
 * perlu diubah); data lama yang belum terenkripsi tampil apa adanya.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/enkripsi.php';
wajib_login();

$kd = isset($_GET['kd']) ? (string) $_GET['kd'] : '';
$row = db_satu("SELECT * FROM deposan WHERE CIF=?", 's', $kd);
if (!$row) {
    header("Location: ../view/master_fund.php");
    exit;
}
$row['ID_NUMBER'] = dekripsi_data($row['ID_NUMBER']);
$row['HADDRESS1'] = dekripsi_data($row['HADDRESS1']);
$tgl_lahir = substr((string) $row['BIRTH_DATE'], 0, 10);

halaman_awal('Data Deposan Nur Insani');
?>
<div class="card card-ni">
  <div class="card-header">Update Data Deposan Nur Insani<?php echo $row['JENIS'] !== '' && $row['JENIS'] !== null ? ' (' . h($row['JENIS']) . ')' : ''; ?></div>
  <div class="card-body">
    <form name="form1" id="form1" action="update-edit.php" method="POST" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="nim">No</label>
        <input type="text" name="nim" id="nim" value="<?php echo h($row['no']); ?>" class="form-control" readonly>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="ktp">KTP/SIM</label>
        <input type="text" name="ktp" id="ktp" value="<?php echo h($row['ID_NUMBER']); ?>" placeholder="KTP" class="form-control" maxlength="40" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nama">Nama Deposan</label>
        <input type="text" name="nama" id="nama" value="<?php echo h($row['CUST_SHORT_NAME']); ?>" placeholder="Nama Deposan" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="tanggal_lahir">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?php echo h($tgl_lahir); ?>" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="alamat">Alamat</label>
        <input type="text" name="alamat" id="alamat" value="<?php echo h($row['HADDRESS1']); ?>" placeholder="Alamat" class="form-control" maxlength="100" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="notelp">No Telepon</label>
        <input type="text" name="notelp" id="notelp" value="<?php echo h($row['MOBILE_PHONE']); ?>" placeholder="No Telepon" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="norek_tujuan">Norek Tujuan</label>
        <input type="text" name="norek_tujuan" id="norek_tujuan" value="<?php echo h($row['norek_tujuan']); ?>" placeholder="No Rekening Pencairan Baghas" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="bank">Nama Bank</label>
        <input type="text" name="bank" id="bank" value="<?php echo h($row['nama_bank']); ?>" placeholder="Nama Bank" class="form-control" required>
      </div>
      <div class="col-12 text-end">
        <a href="../view/master_fund.php" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" name="update" id="update" value="Update" class="btn btn-ni px-4">Update</button>
      </div>
    </form>
  </div>
</div>
<?php
halaman_akhir();
