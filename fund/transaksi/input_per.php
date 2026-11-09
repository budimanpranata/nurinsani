<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data Master Deposan > + Data Perusahaan (level fund): tambah deposan perusahaan ke tabel deposan (tanpa anggota dan
 * tanpa rekening simpanan_kp), JENIS 'PERUSAHAAN'. Nomor urut sama dengan deposan perorangan (no = unit + YYYYMMDD +
 * 4 digit, CIF = YYYYMMDD + 4 digit). NIK pengurus 1 yang sudah ada di deposan (ID_NUMBER) ditolak "NOMOR KTP Sudah Ada..!".
 * Dimigrasi 2026-11-04 (tampilan Bootstrap 5, prepared statement, simpan lalu kembali dengan pesan). Perbaikan atas
 * perintah user 2026-11-04 (versi lama):
 *   - form mengirim ke input.php (perorangan) sehingga tersimpan sebagai perorangan + anggota + 4 simpanan_kp
 *   - isi kolom: cek KTP memakai variabel kosong; norek_tujuan diisi variabel kosong, nama_bank dan ID_NUMBER tidak
 *     terisi; alamat perusahaan dibaca dari isian yang tidak ada; JENIS 'PERUSHAAN'
 *   Kini: CUST_SHORT_NAME nama perusahaan, HADDRESS1 alamat perusahaan (isian baru), NPWP, norek_tujuan, nama_bank;
 *   pengurus 1: KTP_SATU + ID_NUMBER, NAMA_SATU, HADDRESS2; pengurus 2: KTP_DUA, NAMA_DUA, HADDRESS3.
 * Isian "Bertindak Sebagai" (jabatan pengurus) belum disimpan: tabel deposan tidak punya kolomnya.
 * Enkripsi (PEKERJAAN_TERTUNDA.md bagian 87): NIK (ID_NUMBER, KTP_SATU, KTP_DUA) dan alamat (HADDRESS1-3) disimpan
 * terenkripsi, plus ID_NUMBER_IDX (indeks buta NIK pengurus 1) untuk cek dobel (juga mencocokkan data lama).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/enkripsi.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('fund', 'admin');
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
        return isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
    };
    $ktp = $pos('ktp');
    $ktp_idx = indeks_nik($ktp);
    $ktp_enc = enkripsi_data($ktp);
    if (!nik_angka_sah($ktp) || !nik_angka_sah($pos('ktp_dua'))) {
        // perintah user 2026-11-04: NIK pengurus 1 dan 2 hanya angka, maksimal 16 digit
        $_SESSION['pesan_deposan_per'] = array('danger', 'NIK pengurus harus angka, maksimal 16 digit.');
    } elseif (!db_satu("SELECT 1 as ada FROM deposan WHERE ID_NUMBER_IDX=? OR ID_NUMBER=?", 'ss', $ktp_idx, $ktp)) {
        $ok = db_jalankan("INSERT INTO deposan(no,CIF,unit,CUST_SHORT_NAME,NAMA_SATU,NAMA_DUA,KTP_SATU,KTP_DUA,HADDRESS1,HADDRESS2,HADDRESS3,norek_tujuan,nama_bank,ID_NUMBER,ID_NUMBER_IDX,JENIS,NPWP)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'PERUSAHAAN',?)", 'ssssssssssssssss',
            $nim, $cif, $unit, $pos('nama'), $pos('nama_satu'), $pos('nama_dua'), $ktp_enc, enkripsi_data($pos('ktp_dua')),
            enkripsi_data(potong_data($pos('alamat'), 100)), enkripsi_data(potong_data($pos('alamat_satu'), 100)),
            enkripsi_data(potong_data($pos('alamat_dua'), 100)),
            $pos('norek'), $pos('bank'), $ktp_enc, $ktp_idx, $pos('npwp'));
        $_SESSION['pesan_deposan_per'] = $ok ? array('success', 'Data Berhasil Di Simpan.')
            : array('danger', 'Ups, Data Gagal Di simpan ! ' . db_error_terakhir());
    } else {
        $_SESSION['pesan_deposan_per'] = array('danger', 'NOMOR KTP Sudah Ada..!');
    }
    header('Location: input_per.php');
    exit;
}

$pesan = isset($_SESSION['pesan_deposan_per']) ? $_SESSION['pesan_deposan_per'] : null;
unset($_SESSION['pesan_deposan_per']);

halaman_awal('Data Deposan Nur Insani');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<form name="form1" id="form1" action="input_per.php" method="POST" autocomplete="off">
  <div class="card card-ni">
    <div class="card-header">Input Data Perusahaan</div>
    <div class="card-body row g-3">
      <div class="col-sm-6">
        <label class="form-label" for="nim">No</label>
        <input type="text" name="nim" id="nim" placeholder="Tidak perlu di isi" value="<?php echo h($nim); ?>" class="form-control" readonly>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nama">Nama Perusahaan</label>
        <input type="text" name="nama" id="nama" placeholder="Nama Perusahaan" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="alamat">Alamat Perusahaan</label>
        <input type="text" name="alamat" id="alamat" placeholder="Alamat Perusahaan" class="form-control" maxlength="100" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="npwp">NPWP</label>
        <input type="text" name="npwp" id="npwp" placeholder="NPWP Perusahaan" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="norek">No Rekening Bank Lain</label>
        <input type="text" name="norek" id="norek" placeholder="No Rekening Pencairan Baghas" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="bank">Nama Bank</label>
        <input type="text" name="bank" id="bank" placeholder="Nama Bank" class="form-control" required>
      </div>
    </div>
  </div>
  <div class="row g-3 mt-0">
    <?php foreach (array(array('', 'satu', '1'), array('_dua', 'dua', '2')) as $p): ?>
    <div class="col-lg-6">
      <div class="card card-ni h-100">
        <div class="card-header">Pengurus <?php echo $p[2]; ?></div>
        <div class="card-body row g-3">
          <div class="col-12">
            <label class="form-label" for="ktp<?php echo $p[0]; ?>">NIK</label>
            <input type="text" name="ktp<?php echo $p[0]; ?>" id="ktp<?php echo $p[0]; ?>" placeholder="Nomor KTP" class="form-control" maxlength="16" inputmode="numeric" pattern="\d{1,16}" title="Hanya angka, maksimal 16 digit" oninput="this.value = this.value.replace(/\D/g, '')" required>
          </div>
          <div class="col-12">
            <label class="form-label" for="nama_<?php echo $p[1]; ?>">Nama</label>
            <input type="text" name="nama_<?php echo $p[1]; ?>" id="nama_<?php echo $p[1]; ?>" placeholder="Nama" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label" for="alamat_<?php echo $p[1]; ?>">Alamat</label>
            <input type="text" name="alamat_<?php echo $p[1]; ?>" id="alamat_<?php echo $p[1]; ?>" placeholder="Alamat" class="form-control" maxlength="100" required>
          </div>
          <div class="col-12">
            <label class="form-label" for="jabatan_<?php echo $p[1]; ?>">Bertindak Sebagai</label>
            <input type="text" name="jabatan_<?php echo $p[1]; ?>" id="jabatan_<?php echo $p[1]; ?>" placeholder="Jabatan" class="form-control">
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="text-end mt-3">
    <a href="../view/master_fund.php" class="btn btn-outline-secondary">Kembali</a>
    <button type="submit" name="input" id="input" value="Simpan" class="btn btn-ni px-4">Simpan</button>
  </div>
</form>
<?php
halaman_akhir();
