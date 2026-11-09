<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data Transaksi > + Data transaksi (level fund): penempatan deposito baru.
 *   - norek = unit + yymmdd + 4 digit urut harian
 *   - dana titipan deposan (dep_titip) ditambahkan ke nominal lalu dihapus
 *   - jadwal bagi hasil deposito_detail (ke 1..tenor) dan jatuh tempo dari jadwal_deposito() (inc/jadwal_deposito.php)
 *   - deposito, jurnal Penempatan Dana (tabel_transaksi debet 1010-2851000 / kredit 1010-220x000 menurut tenor,
 *     jurnal_umum), dep_realisasi PENEMPATAN (hanya bila tanpa dana titipan, seperti versi lama)
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, prepared statement, saran deposan lewat ../ajax_cari_deposan.php.
 * Perbaikan atas perintah user 2026-11-04:
 *   - (C) nomor urut diambil dari 4 digit terakhir norek (dulu substr(norek,12,4) = 2 digit terakhir, deposito ke-100
 *     di hari yang sama bentrok "Rekening Sudah Ada")
 *   - (D) urutan simpan aman: jadwal + tabel_transaksi (InnoDB) dalam transaksi, deposito (MyISAM) baru ditulis bila
 *     semuanya berhasil, lalu jurnal_umum/dep_realisasi/dep_titip; gagal -> dibatalkan dengan pesan (dulu
 *     or die(mysqli_error()) = Fatal error PHP 8 dan data sebagian tertulis)
 *   - (I, J) jadwal & jatuh tempo: tanggal penempatan yang sama tiap bulan, dipotong ke akhir bulan bila bulannya lebih
 *     pendek (inc/jadwal_deposito.php). Cara lama (satu baris per hari di deposito_detail_hitung lalu 42 aturan hapus,
 *     jatuh tempo strtotime('+n month')) kehilangan tanggal 29 di tahun bukan kabisat, kehilangan Februari bila
 *     melewati tahun berikutnya, dan meluapkan jatuh tempo tanggal 29-31 ke bulan berikutnya. Tanggal 1-28 identik
 *     (uji 1.260 kasus); deposito_detail_hitung tidak dipakai lagi.
 * Dipertahankan sampai ada keputusan user: (B) nomor jurnal dari max(nomor_jurnal) jurnal_umum unit '' ($branch tidak
 * pernah diisi) dengan kode BU/<unit><n>, jurnal_umum diisi kode_transaksi kosong; (G) jalur dana titipan tanpa
 * dep_realisasi dan norek_tujuan dep_realisasi kosong.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('fund', 'admin');
ob_start();
$userid = isset($_SESSION['userid']) ? (string) $_SESSION['userid'] : null;
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}
require_once __DIR__ . '/../../inc/jadwal_deposito.php';

$cod = db_satu("SELECT kode_branch FROM user where userid=?", 's', $userid);
$unit = $cod ? (string) $cod['kode_branch'] : '';
$today = date("ymd");
$t = $unit . $today;
$hit = db_satu("select max(norek) AS last FROM deposito where norek LIKE ?", 's', '%' . $t . '%');
$lastNoTransaksi = $hit ? (string) $hit['last'] : '';
// (C) 4 digit terakhir; versi lama substr($last, 12, 4) hanya mendapat 2 digit terakhir dari norek 14 karakter
$nextNoUrut = (int) substr($lastNoTransaksi, -4) + 1;
$norek = $unit . $today . sprintf('%04s', $nextNoUrut);

/** Simpan penempatan deposito; hasil array(berhasil, pesan). */
function simpan_penempatan($norek, $unit, $userid)
{
    $pos = function ($k) {
        return isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
    };
    $cif = $pos('code');
    $titip = db_satu("select sum(nominal) AS titip FROM dep_titip where cif = ?", 's', $cif);
    $tot_titip = compat_num($titip ? $titip['titip'] : null);
    $bilyet = $pos('bilyet');
    $tenor = $pos('tenor');
    $tgl = $pos('tgl');
    $eq_rate = $pos('eq_rate');
    $nama_bank = $pos('nama_bank');
    $norek_bank = $pos('norek_bank');
    $cara_hitung = $pos('cara_hitung');
    $sima_per_tenor = array('6' => '1010-2203000', '3' => '1010-2202000', '12' => '1010-2204000', '1' => '1010-2201000');
    $sima = isset($sima_per_tenor[$tenor]) ? $sima_per_tenor[$tenor] : '1010-2206000';
    $nominal_as = compat_num(str_replace('.', '', $pos('nominal')));
    $nominal = $nominal_as + $tot_titip;
    $rasio = $pos('nisbah');
    $aro = $pos('aro') === 'ARO-ARO' ? $pos('jenis_aro') : $pos('aro');
    $bayar = $pos('bayar');
    $jenis_simpanan = "SIM-$tenor";
    $code_bulan = '';
    $awalan_jenis = array('WD' => 'WD', 'DP' => 'DP', 'MC' => 'MC20', 'EMC' => 'EMC20');
    if (isset($awalan_jenis[$pos('jenis')])) {
        // sama dengan versi lama: potongan teks tanggal yyyy-mm-dd apa adanya
        $code_bulan = $awalan_jenis[$pos('jenis')] . substr($tgl, 0, 2) . substr($tgl, 2, 5);
    }
    $d_tgl = DateTime::createFromFormat('!Y-m-d', $tgl);
    if (!$d_tgl || $d_tgl->format('Y-m-d') !== $tgl || $cif === '' || $tenor === '' || !ctype_digit($tenor)) {
        return array(false, 'Isi Tanggal Transaksi, No Anggota, dan Tenor dengan benar.');
    }
    // (I, J) jadwal & jatuh tempo: tanggal yang sama tiap bulan, dipotong ke akhir bulan bila bulannya lebih pendek
    $jadwal = jadwal_deposito($tgl, $tenor);
    if (!$jadwal) {
        return array(false, 'Isi Tanggal Transaksi dan Tenor dengan benar.');
    }
    $maturity_date = end($jadwal);
    if (db_satu("SELECT 1 as ada FROM deposito WHERE norek=?", 's', $norek)) {
        return array(false, 'Rekening Sudah Ada..!');
    }

    $db = db_ni();
    $gagal_awal = db_jumlah_gagal();
    $db->begin_transaction();
    // jadwal bagi hasil: satu baris per bulan (ke = 1..tenor); id = norek + ke, kolom lain sama dengan versi lama
    foreach ($jadwal as $ke => $tanggal) {
        db_jalankan("INSERT INTO deposito_detail (id,id_deposito,cif,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values(?,?,?,?,?,?,'0',?,'')",
            'sssssss', $norek . $ke, $norek, $cif, (string) $ke, $tanggal, $tanggal, $norek);
    }

    // (B) dipertahankan: nomor dari jurnal unit '' (versi lama memakai $branch yang tidak pernah diisi)
    $jurnal = db_satu("SELECT max(nomor_jurnal) as n FROM jurnal_umum where unit=''");
    $trns = "BU/" . $unit . (compat_num($jurnal ? $jurnal['n'] : null) + 1);
    $ket = "Penempatan Dana Norek $norek";
    $jumlah = $tot_titip > 0 ? $nominal : $nominal_as;
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values(?,?,'1010-2851000',?,'Bukti System',?,?,?)", 'ssssss', $unit, $trns, $tgl, $ket, (string) $jumlah, $userid);
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values(?,?,?,?,'Bukti System',?,?,?)", 'sssssss', $unit, $trns, $sima, $tgl, $ket, (string) $jumlah, $userid);
    if (db_jumlah_gagal() > $gagal_awal) {
        $db->rollback();
        return array(false, 'Ups, Data Gagal Di simpan ! ' . db_error_terakhir());
    }

    // deposito (MyISAM) baru ditulis setelah jadwal dan jurnal berhasil; bila gagal, semuanya dibatalkan
    // (K) perbaikan 2026-11-04 (perintah user): cara_hitung kini disimpan di kedua jalur (dulu jalur tanpa dana titipan
    // tidak menyimpannya sehingga kolom Cara Hitung kosong); nominal = nominal + titipan bila ada titipan
    $ok = db_jalankan("INSERT INTO deposito(norek,cif,bilyet, nominal,tenor,star_date,maturity_date,jenis_simpanan,aro,rasio,code_bulan,unit,suffix,contract_date,userid,jenis_bayar,eq_rate,nama_bank,norek_bank,cara_hitung,last_payment)
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,'1',?,?,?,?,?,?,?,'0000-00-00')", 'sssssssssssssssssss',
        $norek, $cif, $bilyet, (string) ($tot_titip > 0 ? $nominal : $nominal_as), $tenor, $tgl, $maturity_date, $jenis_simpanan, $aro, $rasio,
        $code_bulan, $unit, $tgl, $userid, $bayar, $eq_rate, $nama_bank, $norek_bank, $cara_hitung);
    if (!$ok) {
        $db->rollback();
        return array(false, 'Ups, Data Gagal Di simpan ! ' . db_error_terakhir());
    }
    // (B) dipertahankan: nomor_jurnal diisi teks (MySQL memberi nomor otomatis), kode_transaksi kosong
    db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(?,'',?,?)", 'sss', $trns, $tgl, $unit);
    if ($tot_titip > 0) {
        db_jalankan("delete from dep_titip where cif=?", 's', $cif);
    } else {
        // (G) dipertahankan: hanya jalur tanpa titipan, norek_tujuan kosong
        db_jalankan("insert into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)
            VALUES(?,?,?,?,?,?,'','EVVI','PENEMPATAN')", 'ssssss', $norek, $norek, $cif, (string) $nominal_as, $tgl, $maturity_date);
    }
    $db->commit();
    return array(true, 'Data Berhasil Di Simpan.');
}

if (isset($_POST['input'])) {
    list($ok, $pesan) = simpan_penempatan($norek, $unit, $userid);
    if ($ok) {
        header("location:../view/master_trans.php");
        exit;
    }
    $_SESSION['pesan_input_trns'] = $pesan;
    header("location:input_trns.php");
    exit;
}
$pesan = isset($_SESSION['pesan_input_trns']) ? $_SESSION['pesan_input_trns'] : null;
unset($_SESSION['pesan_input_trns']);

halaman_awal('Data Deposan Nur Insani');
?>
<?php if ($pesan): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <?php echo h($pesan); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni">
  <div class="card-header">Input Data Deposan Nur Insani</div>
  <div class="card-body">
    <form name="form1" id="form1" action="input_trns.php" method="POST" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="norek">Norek</label>
        <input type="text" name="norek" id="norek" placeholder="Tidak perlu di isi" value="<?php echo h($norek); ?>" class="form-control" readonly>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="tgl">Tanggal Transaksi</label>
        <input type="date" name="tgl" id="tgl" class="form-control" required>
      </div>
      <div class="col-sm-6 autocomplete">
        <label class="form-label" for="kode">No Anggota</label>
        <input type="text" name="code" id="kode" placeholder="cif deposan" class="form-control" required
               data-autocomplete="../ajax_cari_deposan.php" data-isi-nilai="cif" data-isi-label="label" data-isi-target='{"#nama_deposan":"nama"}'>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nama_deposan">Nama Deposan</label>
        <input type="text" id="nama_deposan" class="form-control" disabled>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="bilyet">Bilyet</label>
        <input type="text" name="bilyet" id="bilyet" placeholder="Bilyet" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nominal">Nominal Simpanan</label>
        <input type="text" name="nominal" id="nominal" placeholder="Nominal Simpanan" class="form-control" inputmode="numeric" required>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="tenor">Tenor</label>
        <select name="tenor" id="tenor" class="form-select" required>
          <option value="">Pilih Tenor</option>
          <option value="1">1 Bulan</option>
          <option value="3">3 Bulan</option>
          <option value="6">6 Bulan</option>
          <option value="12">12 Bulan</option>
          <option value="18">18 Bulan</option>
        </select>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="nisbah">Nisbah</label>
        <input type="text" name="nisbah" id="nisbah" placeholder="Nisbah" class="form-control" required>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="eq_rate">Eq Rate</label>
        <input type="text" name="eq_rate" id="eq_rate" placeholder="Eq Rate" class="form-control" required>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="cara_hitung">Cara Hitung</label>
        <select name="cara_hitung" id="cara_hitung" class="form-select" required>
          <option value="">Cara Hitung</option>
          <option value="1">Nisbah</option>
          <option value="2">Rate</option>
        </select>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="aro">Perpanjangan</label>
        <select name="aro" id="aro" class="form-select" required>
          <option value="">Pilih Perpanjangan</option>
          <option value="ARO">ARO</option>
          <option value="ARO-ARO">ARO ARO</option>
          <option value="BREAK">Tidak di Perpanjang</option>
        </select>
      </div>
      <div class="col-sm-4">
        <label class="form-label" for="jenis_aro">Jenis ARO</label>
        <select name="jenis_aro" id="jenis_aro" class="form-select">
          <option value="">Pilih ARO</option>
          <option value="ARO_bulan">Bulan</option>
          <option value="ARO_jatpo">Jatuh Tempo</option>
        </select>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="jenis">Jenis Dana</label>
        <select name="jenis" id="jenis" class="form-select" required>
          <option value="">Pilih Dana</option>
          <option value="DP">DEPOSITO MUDHARABAH</option>
          <option value="MC">MICRO CAPITAL</option>
          <option value="EMC">ESCROW MICRO CAPITAL</option>
        </select>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="bayar">Pembayaran Bagi Hasil</label>
        <select name="bayar" id="bayar" class="form-select" required>
          <option value="">Pilih Jenis</option>
          <option value="lain">Rekening Bank Lain</option>
          <option value="ni">Rekening Nurinsani</option>
        </select>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nama_bank">Nama Bank</label>
        <input type="text" name="nama_bank" id="nama_bank" placeholder="Nama Bank" class="form-control" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="norek_bank">Norek Bank</label>
        <input type="text" name="norek_bank" id="norek_bank" placeholder="Norek Bank" class="form-control" required>
      </div>
      <div class="col-12 text-end">
        <a href="../view/master_trans.php" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" name="input" id="input" value="Simpan" class="btn btn-ni px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>
<script>
// pemisah ribuan pada Nominal Simpanan (seperti versi lama; titik dibuang saat disimpan)
document.getElementById('nominal').addEventListener('keyup', function () {
    var angka = this.value.replace(/[^\d]/g, '');
    this.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
});
</script>
<?php
halaman_akhir();
