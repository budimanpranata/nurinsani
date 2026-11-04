<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data Master Deposan > + Data Perorangan (level fund): tambah deposan perorangan. Seperti versi lama menulis:
 *   - 4 rekening simpanan_kp bersaldo 0 (CIF+04, +05, +06, +07)
 *   - anggota (no = unit + CIF) dan deposan (no sama, JENIS kosong)
 * Nomor urut harian: no terakhir deposan hari itu + 1 (no = unit + YYYYMMDD + 4 digit, CIF = YYYYMMDD + 4 digit).
 * KTP yang sudah ada di deposan ditolak "Maaf, GAgal..!".
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, prepared statement, simpan lalu kembali ke halaman ini dengan pesan sama
 * (nomor berikutnya langsung tampil); var_dump($unit) dibuang. Perintah user 2026-11-04: simpan dalam satu transaksi
 * (simpanan_kp + anggota InnoDB dibatalkan bila salah satu insert gagal; deposan MyISAM ditulis terakhir).
 * Enkripsi (PEKERJAAN_TERTUNDA.md bagian 87): NIK (ID_NUMBER) dan alamat (HADDRESS1) disimpan terenkripsi di deposan
 * dan anggota, plus ID_NUMBER_IDX (indeks buta) untuk cek KTP dobel (juga mencocokkan data lama yang belum terenkripsi).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/enkripsi.php';
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
$hit = db_satu("select max(no) AS last FROM deposan where no LIKE ?", 's', '%' . $today . '%');
$lastNoTransaksi = $hit ? (string) $hit['last'] : '';
$nextNoUrut = (int) substr($lastNoTransaksi, 12, 4) + 1;
$nextNoTransaksi = $today . sprintf('%04s', $nextNoUrut);
$nim = "$unit$nextNoTransaksi";
$cif = "$nextNoTransaksi";

if (isset($_POST['input'])) {
    $pos = function ($k) {
        return isset($_POST[$k]) ? (string) $_POST[$k] : '';
    };
    $ktp = trim($pos('ktp'));
    $ktp_idx = indeks_nik($ktp);
    $ktp_enc = enkripsi_data($ktp);
    $alamat_enc = enkripsi_data(potong_data($pos('alamat'), 100));
    if (!nik_angka_sah($ktp)) {
        // perintah user 2026-11-04: Nomor KTP hanya angka, maksimal 16 digit
        $_SESSION['pesan_deposan'] = array('danger', 'Nomor KTP harus angka, maksimal 16 digit.');
    } elseif (!db_satu("SELECT 1 as ada FROM deposan WHERE ID_NUMBER_IDX=? OR ID_NUMBER=?", 'ss', $ktp_idx, $ktp)) {
        $db = db_ni();
        $gagal_awal = db_jumlah_gagal();
        $db->begin_transaction();
        // 4 rekening simpanan; kode_kel, keterangan, cao, kode transaksi kosong seperti versi lama
        foreach (array('04', '05', '06', '07') as $jenis) {
            db_jalankan("Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
                VALUES(?,?,?,?,'',0,?,0,?,'','','','1',?,'')", 'sssssss',
                $today, $cif . $jenis, $unit, $cif, $jenis, (string) $userid, $today);
        }
        db_jalankan("INSERT INTO anggota(no,CIF,unit,CUST_SHORT_NAME,BIRTH_DATE, HADDRESS1, MOBILE_PHONE,ID_NUMBER,ID_NUMBER_IDX,NPWP)
            VALUES(?,?,?,?,?,?,?,?,?,?)", 'ssssssssss',
            $nim, $cif, $unit, $pos('nama'), $pos('tanggal_lahir'), $alamat_enc, $pos('notelp'), $ktp_enc, $ktp_idx, $pos('npwp'));
        $ok = db_jumlah_gagal() === $gagal_awal;
        if ($ok) {
            // deposan (MyISAM, di luar transaksi) ditulis terakhir; bila gagal, rekening dan anggota dibatalkan
            $ok = db_jalankan("INSERT INTO deposan(no,CIF,unit,CUST_SHORT_NAME, TEMPAT_LAHIR, BIRTH_DATE, HADDRESS1, MOBILE_PHONE,norek_tujuan,nama_bank,ID_NUMBER,ID_NUMBER_IDX,NPWP)
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)", 'sssssssssssss',
                $nim, $cif, $unit, $pos('nama'), $pos('tempat_lahir'), $pos('tanggal_lahir'), $alamat_enc, $pos('notelp'),
                $pos('norek'), $pos('bank'), $ktp_enc, $ktp_idx, $pos('npwp'));
        }
        if ($ok) {
            $db->commit();
            $_SESSION['pesan_deposan'] = array('success', 'Data Berhasil Di Simpan.');
        } else {
            $db->rollback();
            $_SESSION['pesan_deposan'] = array('danger', 'Ups, Data Gagal Di simpan ! ' . db_error_terakhir());
        }
    } else {
        $_SESSION['pesan_deposan'] = array('danger', 'Maaf, GAgal..!');
    }
    header('Location: input.php');
    exit;
}

$pesan = isset($_SESSION['pesan_deposan']) ? $_SESSION['pesan_deposan'] : null;
unset($_SESSION['pesan_deposan']);

halaman_awal('Data Deposan Nur Insani');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni">
  <div class="card-header">Input Data Deposan Nur Insani</div>
  <div class="card-body">
    <form name="form1" id="form1" action="input.php" method="POST" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="nim">No</label>
        <input type="text" name="nim" id="nim" placeholder="Tidak perlu di isi" value="<?php echo h($nim); ?>" class="form-control" readonly>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="ktp">Nomor KTP</label>
        <input type="text" name="ktp" id="ktp" placeholder="Nomor KTP" class="form-control" maxlength="16" inputmode="numeric" pattern="\d{1,16}" title="Hanya angka, maksimal 16 digit" oninput="this.value = this.value.replace(/\D/g, '')" required>
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
      <div class="col-sm-6">
        <label class="form-label" for="alamat">Alamat</label>
        <input type="text" name="alamat" id="alamat" placeholder="Alamat" class="form-control" maxlength="100" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="npwp">NPWP</label>
        <input type="text" name="npwp" id="npwp" placeholder="NPWP" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="notelp">No Telepon</label>
        <input type="text" name="notelp" id="notelp" placeholder="No Telepon" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="norek">No Rekening Bank Lain</label>
        <input type="text" name="norek" id="norek" placeholder="No Rekening Pencairan Baghas" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="bank">Nama Bank</label>
        <input type="text" name="bank" id="bank" placeholder="Nama Bank" class="form-control" required>
      </div>
      <div class="col-12 text-end">
        <a href="../view/master_fund.php" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" name="input" id="input" value="Simpan" class="btn btn-ni px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php
halaman_akhir();
