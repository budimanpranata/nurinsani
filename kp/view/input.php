<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Transaksi > Simpanan > + Anggota (level kp): tambah anggota simpanan KP beserta 4 rekening simpanan_kp
 * (CIF+04 pokok, +05 wajib, +06 khusus, +07 umum) bersaldo 0.
 * Logika sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah. var_dump($unit) sisa
 * debug dihapus. Perilaku lama yang dipertahankan dicatat di PEKERJAAN_TERTUNDA.md bagian 44 (nomor urut dari unit
 * '1010', tempat lahir tidak disimpan, rekening simpanan tetap dibuat walau anggota gagal disimpan, dll.).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}

$today = date("Ymd");
$kbranch = db_satu("select kode_branch AS unit FROM user where userid=?", 's', (string) $userid);
$unit = $kbranch ? (string) $kbranch['unit'] : '';
// nomor urut harian; versi lama mengambil nomor terakhir dari unit '1010' (bukan unit user)
$hit = db_satu("select max(no) AS last FROM anggota where  no LIKE ? and unit ='1010'", 's', '%' . $today . '%');
$lastNoTransaksi = $hit ? (string) $hit['last'] : '';
$nextNoUrut = (int) substr($lastNoTransaksi, 12, 4) + 1;
$nextNoTransaksi = $today . sprintf('%04s', $nextNoUrut);
$nim = "$unit$nextNoTransaksi";
$cif = "$nextNoTransaksi";

$pesan = null;
if (isset($_POST['input'])) {
    $pos = function ($k) {
        return isset($_POST[$k]) ? (string) $_POST[$k] : '';
    };
    $ktp = $pos('ktp');
    $cif_baru = $pos('cif');
    // versi lama: $cif ditimpa nilai kiriman, sehingga isian CIF sesudah simpan menampilkan CIF yang dikirim
    $cif = $cif_baru;
    // enkripsi (PEKERJAAN_TERTUNDA.md bagian 87): NIK anggota pasangan deposan tersimpan terenkripsi -> cocokkan juga indeksnya
    require_once __DIR__ . '/../../inc/enkripsi.php';
    if (!db_satu("SELECT 1 as ada FROM anggota WHERE ID_NUMBER=? OR ID_NUMBER_IDX=?", 'ss', $ktp, indeks_nik($ktp))) {
        // 4 rekening simpanan; kode_kel, keterangan, cao, kode transaksi kosong seperti versi lama
        foreach (array('04', '05', '06', '07') as $jenis) {
            db_jalankan("Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
                VALUES(?,?,?,?,'',0,?,0,?,'','','','1',?,'')", 'sssssss',
                $today, $cif_baru . $jenis, $unit, $cif_baru, $jenis, (string) $userid, $today);
        }
        $insert = db_jalankan("INSERT INTO anggota(no,CIF,unit,CUST_SHORT_NAME,BIRTH_DATE, HADDRESS1, MOBILE_PHONE,ID_NUMBER,NPWP)
            VALUES(?,?,?,?,?,?,?,?,?)", 'sssssssss',
            $nim, $cif_baru, $unit, $pos('nama'), $pos('tanggal_lahir'), $pos('alamat'), $pos('notelp'), $ktp, $pos('npwp'));
        $pesan = $insert ? array('success', 'Data Berhasil Di Simpan.') : array('danger', 'Ups, Data Gagal Di simpan !');
    } else {
        $pesan = array('danger', 'Maaf, GAgal..!');
    }
}

halaman_awal('Data Anggota Nur Insani');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni">
  <div class="card-header">Input Data Anggota Nur Insani</div>
  <div class="card-body">
    <form name="form1" id="form1" action="input.php" method="POST" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="nim">No</label>
        <input type="text" name="nim" id="nim" placeholder="Tidak perlu di isi" value="<?php echo h($nim); ?>" class="form-control" readonly>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="cif">CIF</label>
        <input type="text" name="cif" id="cif" value="<?php echo h($cif); ?>" placeholder="Nomor CIF" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="ktp">Nomor KTP</label>
        <input type="text" name="ktp" id="ktp" placeholder="Nomor KTP" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nama">Nama</label>
        <input type="text" name="nama" id="nama" placeholder="Nama Deposan" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="tempat_lahir">Tempat Lahir</label>
        <input type="text" name="tempat_lahir" id="tempat_lahir" placeholder="Tempat Lahir" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="tanggal_lahir">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" required>
      </div>
      <div class="col-12">
        <label class="form-label" for="alamat">Alamat</label>
        <input type="text" name="alamat" id="alamat" placeholder="Alamat" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="npwp">NPWP</label>
        <input type="text" name="npwp" id="npwp" placeholder="NPWP" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="notelp">No Telepon</label>
        <input type="text" name="notelp" id="notelp" placeholder="No Telepon" class="form-control" required>
      </div>
      <div class="col-12 d-flex gap-2">
        <button type="submit" name="input" id="input" class="btn btn-ni">Simpan</button>
        <?php // versi lama: tombol Kembali mengarah ke master_fund.php (tidak ada di folder ini) - dipertahankan ?>
        <a href="../view/master_fund.php" class="btn btn-outline-secondary">Kembali</a>
      </div>
    </form>
  </div>
</div>
<?php
halaman_akhir();
