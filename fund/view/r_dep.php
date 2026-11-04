<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data Jatuh Tempo > Realisasi (?kd=norek, dari view_tempo.php): realisasi deposito jatuh tempo.
 *   PK (Aro)  : jurnal_umum + tabel_transaksi 'Bukti Umum' debet sima / kredit 1010-2851000 lalu debet 1010-2851000 /
 *               kredit sima (nominal); jadwal baru (inc/jadwal_deposito.php) dari tanggal realisasi; deposito suffix+1,
 *               rasio = Adjust Baghas, status_print ADVIS
 *   BREAK     : bayar  -> salin deposito ke dep_break dengan maturity_date = tanggal realisasi (lanjut di menu Break)
 *               titip  -> jurnal 'Bukti System' debet sima / kredit 1010-2851000 (nominal), salin ke dep_titip,
 *                         deposito dihapus (dana dipakai lagi saat penempatan baru)
 *   ARO-ARO   : ARO_bulan -> jadwal baru, aro ARO_bulan, status_print ADVIS (tanpa jurnal)
 *               ARO_jatpo -> nominal + saldo escrow_deposito norek itu; jurnal 'Bukti System' 4 baris seperti PK; jadwal
 *                            baru; aro ARO_jatpo, status_print OK, rasio & eq_rate dari form
 *   sima menurut tenor: 1 -> 1010-2201000, 3 -> 2202000, 6 -> 2203000, 12 -> 2204000, lainnya 2206000.
 *   Tanggal jurnal = hari ini (seperti versi lama); star_date/contract_date = tanggal realisasi.
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, prepared statement, skrip assets/app/fund_rdep.js.
 * Perbaikan atas perintah user 2026-11-04:
 *   - wajib login level fund; tanggal, jenis, jenis break, jenis ARO wajib dan divalidasi (dulu Break tanpa pilihan jenis
 *     langsung diproses sebagai Titipan = deposito terhapus); dikunci per norek; sesudah Simpan dialihkan (F5 tidak
 *     mengirim ulang -> dulu suffix dan jadwal dobel); gagal -> dibatalkan dengan pesan (dulu or die(mysqli_error())
 *     = Fatal error PHP 8)
 *   - (L) ARO-ARO: dulu syarat $enis_aro=='ARO-bulan' salah ketik sehingga pilihan Bulan tidak pernah jalan, dan jalur
 *     lain menulis maturity_date dari variabel yang tidak pernah dihitung (0000-00-00) tanpa jadwal baru
 *   - (O) aro ditulis ARO_bulan / ARO_jatpo seperti penempatan (dulu 'ARO-bulan' / 'ARO-jatpo' yang tidak dikenali
 *     Realisasi Bagi Hasil)
 *   - (P) rasio dan eq_rate ARO_jatpo dari form (dulu field 'bisbah' salah nama -> rasio 0, eq_rate form tertimpa)
 *   - (M) star_date/contract_date = tanggal realisasi (dulu tanggal jadwal terakhir pada PK)
 *   - (N) Break titip tidak lagi membuat jadwal dan meng-update deposito yang sudah dihapus
 *   - keterangan jurnal PK diisi (dulu kosong); satu nomor bukti BU/-<unit>-<6 digit> (inc/nomor_bukti.php) dan
 *     jurnal_umum mencatat kode itu (dulu kode_transaksi kosong)
 * Dipertahankan (keputusan user): saldo escrow ARO_jatpo ditambahkan ke nominal tanpa mendebet escrow.
 */
require_once __DIR__ . '/../../inc/tampilan.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "fund") {
    header("location:../../index.html");
    exit;
}
require_once __DIR__ . '/../../inc/jadwal_deposito.php';
require_once __DIR__ . '/../../inc/nomor_bukti.php';
include_once __DIR__ . '/../../function.php';

$userid = (string) $_SESSION['userid'];
$k = db_satu("select kode_branch AS unit FROM user where userid=?", 's', $userid);
$unit = $k ? (string) $k['unit'] : '';

/** Saldo escrow_deposito satu norek (kredit - debet). */
function saldo_escrow($norek)
{
    $es = db_satu("select sum(KREDIT)-sum(DEBIT) as saldo from escrow_deposito where norek =?", 's', $norek);
    return compat_num($es ? $es['saldo'] : null);
}

/** Proses realisasi; hasil array(berhasil, pesan). Dipanggil di dalam kunci per norek. */
function realisasi_tempo($unit, $userid)
{
    $pos = function ($k) {
        return isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
    };
    $norek = $pos('norek');
    $tgl = str_replace('/', '-', substr($pos('bulan'), 0, 10));
    $jenis = $pos('jenis');
    $jenis_break = $pos('jenis_break');
    $jenis_aro = $pos('jenis_aro');
    $add_baghas = $pos('add_baghas');
    $nisbah = $pos('nisbah');
    $eq_rate = $pos('eq_rate');

    $hit = db_satu("select * from deposito where norek =?", 's', $norek);
    if (!$hit) {
        return array(false, 'Deposito tidak ditemukan.');
    }
    $d = DateTime::createFromFormat('!Y-m-d', $tgl);
    if (!$d || $d->format('Y-m-d') !== $tgl) {
        return array(false, 'Tanggal Realisasi tidak valid.');
    }
    if (!in_array($jenis, array('PK', 'BREAK', 'ARO-ARO'), true)) {
        return array(false, 'Pilih Jenis Realisasi.');
    }
    if ($jenis === 'BREAK' && !in_array($jenis_break, array('bayar', 'titip'), true)) {
        return array(false, 'Pilih Jenis Break.');
    }
    if ($jenis === 'ARO-ARO' && !in_array($jenis_aro, array('ARO_bulan', 'ARO_jatpo'), true)) {
        return array(false, 'Pilih Jenis ARO.');
    }
    if ($jenis === 'PK' && !is_numeric($add_baghas)) {
        return array(false, 'Adjust Baghas harus angka.');
    }
    if ($jenis === 'ARO-ARO' && $jenis_aro === 'ARO_jatpo' && (!is_numeric($nisbah) || !is_numeric($eq_rate))) {
        return array(false, 'Nisbah dan Eq Rate harus angka.');
    }

    $buss_date = date("Y-m-d");
    $nominal = (string) $hit['nominal'];
    $cif = (string) $hit['cif'];
    $tenor = (string) $hit['tenor'];
    $suffix = (string) (compat_num($hit['suffix']) + 1);
    if ($tenor == '6') {
        $sima = "1010-2203000";
    } elseif ($tenor == '3') {
        $sima = "1010-2202000";
    } elseif ($tenor == '12') {
        $sima = "1010-2204000";
    } elseif ($tenor == '1') {
        $sima = "1010-2201000";
    } else {
        $sima = "1010-2206000";
    }
    $jadwal = array();
    if ($jenis !== 'BREAK') {
        $jadwal = jadwal_deposito($tgl, $tenor);
        if (!$jadwal) {
            return array(false, 'Jadwal tidak bisa dibuat (tenor deposito tidak valid).');
        }
    }
    $maturity_date = $jadwal ? end($jadwal) : $tgl;

    if ($jenis === 'BREAK' && $jenis_break === 'bayar') {
        if (!db_satu("select 1 as ada from dep_break where norek=?", 's', $norek)
            && !db_jalankan("insert into dep_break SELECT * FROM deposito where norek =?", 's', $norek)) {
            return array(false, 'Ups, Data Gagal Di simpan ! ' . db_error_terakhir());
        }
        if (!db_jalankan("update dep_break set maturity_date=? where norek =?", 'ss', $tgl, $norek)) {
            return array(false, 'Ups, Data Gagal Di simpan ! ' . db_error_terakhir());
        }
        return array(true, 'Data Berhasil Di Simpan. Proses berikutnya lakukan Break di menu Break.');
    }

    // jurnal: baris tabel_transaksi (rekening, kolom, jumlah)
    $baris = array();
    if ($jenis === 'PK') {
        $ket = "Perpanjangan Dana Norek $norek";
        $jenis_trx = 'Bukti Umum';
        $baris = array(array($sima, 'debet', $nominal), array('1010-2851000', 'kredit', $nominal),
            array('1010-2851000', 'debet', $nominal), array($sima, 'kredit', $nominal));
    } elseif ($jenis === 'BREAK') {
        $ket = "BREAK Dana Norek $norek";
        $jenis_trx = 'Bukti System';
        $baris = array(array($sima, 'debet', $nominal), array('1010-2851000', 'kredit', $nominal));
    } elseif ($jenis_aro === 'ARO_jatpo') {
        $new_nominal = (string) (compat_num($nominal) + saldo_escrow($norek));
        $ket = "Perpanjangan Dana Norek $norek";
        $jenis_trx = 'Bukti System';
        $baris = array(array($sima, 'debet', $new_nominal), array('1010-2851000', 'kredit', $new_nominal),
            array('1010-2851000', 'debet', $new_nominal), array($sima, 'kredit', $new_nominal));
    }
    $kode = $baris ? nb_baru('BU', $unit) : '';

    $db = db_ni();
    $gagal_awal = db_jumlah_gagal();
    $db->begin_transaction();
    foreach ($baris as $t) {
        db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,{$t[1]},id_admin)
            values(?,?,?,?,?,?,?,?)", 'ssssssss', $unit, $kode, $t[0], $buss_date, $jenis_trx, $ket, $t[2], $userid);
    }
    foreach ($jadwal as $ke => $tanggal) {
        db_jalankan("INSERT INTO deposito_detail (id_deposito,cif,ke,tgl_jatuh_tempo,tgl_bayar,jumlah_nisbah,rek_tujuan,ket) values(?,?,?,?,?,'0',?,'')",
            'ssssss', $norek, $cif, (string) $ke, $tanggal, $tanggal, $norek);
    }
    if ($jenis === 'BREAK') {
        // dep_titip (MyISAM) di dalam langkah ini: bila gagal, jurnal dibatalkan dan deposito tidak dihapus
        db_jalankan("insert into dep_titip select * from deposito where norek=?", 's', $norek);
    }
    if (db_jumlah_gagal() > $gagal_awal) {
        $galat = db_error_terakhir();
        $db->rollback();
        return array(false, 'Ups, Data Gagal Di simpan ! ' . $galat);
    }
    $db->commit();

    if ($kode !== '') {
        db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', $kode, $buss_date, $unit);
    }
    if ($jenis === 'PK') {
        $ok = db_jalankan("update deposito set star_date=?,contract_date =?,maturity_date=?,suffix=?,rasio=?, status_print='ADVIS' where norek=?",
            'ssssss', $tgl, $tgl, $maturity_date, $suffix, $add_baghas, $norek);
    } elseif ($jenis === 'BREAK') {
        $ok = db_jalankan("delete from deposito where norek=?", 's', $norek);
    } elseif ($jenis_aro === 'ARO_bulan') {
        $ok = db_jalankan("update deposito set star_date=?,contract_date =?,maturity_date=?,suffix=?,aro='ARO_bulan', status_print='ADVIS' where norek=?",
            'sssss', $tgl, $tgl, $maturity_date, $suffix, $norek);
    } else {
        $ok = db_jalankan("update deposito set star_date=?,contract_date =?,maturity_date=?,suffix=?, aro='ARO_jatpo', status_print='OK',eq_rate=?,rasio=?,nominal=? where norek=?",
            'ssssssss', $tgl, $tgl, $maturity_date, $suffix, $eq_rate, $nisbah, $new_nominal, $norek);
    }
    if (!$ok) {
        return array(false, 'Jurnal dan jadwal tersimpan, tetapi data deposito gagal diperbarui: ' . db_error_terakhir());
    }
    return array(true, 'Data Berhasil Di Simpan.');
}

if (isset($_POST['input'])) {
    $norek = isset($_POST['norek']) ? (string) $_POST['norek'] : '';
    $kunci = db_satu("SELECT GET_LOCK(?, 15) as ok", 's', 'nurinsani_rdep_' . $norek);
    if (!$kunci || (int) $kunci['ok'] !== 1) {
        $hasil = array(false, 'Proses lain sedang berjalan, silakan ulangi.');
    } elseif (!nb_kunci('BU', $unit)) {
        $hasil = array(false, 'Proses lain sedang berjalan, silakan ulangi.');
        db_satu("SELECT RELEASE_LOCK(?) as ok", 's', 'nurinsani_rdep_' . $norek);
    } else {
        $hasil = realisasi_tempo($unit, $userid);
        nb_lepas('BU', $unit);
        db_satu("SELECT RELEASE_LOCK(?) as ok", 's', 'nurinsani_rdep_' . $norek);
    }
    $_SESSION['pesan_rdep'] = array($hasil[0] ? 'success' : 'danger', $hasil[1]);
    header('Location: r_dep.php?kd=' . urlencode($norek));
    exit;
}

$norekk = isset($_GET['kd']) ? trim((string) $_GET['kd']) : '';
$hitt = $norekk === '' ? null : db_satu("select deposito.*, CUST_SHORT_NAME from deposito left join deposan on deposan.cif = deposito.cif where norek =?", 's', $norekk);
$pesan = isset($_SESSION['pesan_rdep']) ? $_SESSION['pesan_rdep'] : null;
unset($_SESSION['pesan_rdep']);
$saldo_aro = $hitt ? saldo_escrow($norekk) : 0;

halaman_awal('Realisasi Jatuh Tempo');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<?php if (!$hitt): ?>
<div class="alert alert-warning">Deposito <?php echo h($norekk); ?> tidak ditemukan (mungkin sudah di-Break).</div>
<a href="view_tempo.php" class="btn btn-sm btn-secondary">Kembali</a>
<?php else: ?>
<div class="card card-ni mw-100">
  <div class="card-header">Realisasi Jatuh Tempo <?php echo h($hitt['norek'] . ' - ' . $hitt['CUST_SHORT_NAME']); ?></div>
  <div class="card-body">
    <dl class="row small mb-4">
      <dt class="col-sm-2">Bilyet</dt><dd class="col-sm-4"><?php echo h($hitt['bilyet']); ?></dd>
      <dt class="col-sm-2">Status</dt><dd class="col-sm-4"><?php echo h($hitt['aro']); ?></dd>
      <dt class="col-sm-2">Nominal</dt><dd class="col-sm-4"><?php echo number_format(compat_num($hitt['nominal'])); ?></dd>
      <dt class="col-sm-2">Saldo Escrow</dt><dd class="col-sm-4"><?php echo number_format($saldo_aro); ?></dd>
      <dt class="col-sm-2">Tenor</dt><dd class="col-sm-4"><?php echo h($hitt['tenor']); ?> bulan</dd>
      <dt class="col-sm-2">Jatuh Tempo</dt><dd class="col-sm-4"><?php echo h(tgl_indo($hitt['maturity_date'])); ?></dd>
      <dt class="col-sm-2">Rasio</dt><dd class="col-sm-4"><?php echo h($hitt['rasio']); ?></dd>
      <dt class="col-sm-2">Eq Rate</dt><dd class="col-sm-4"><?php echo h($hitt['eq_rate']); ?></dd>
    </dl>
    <form method="post" action="r_dep.php" class="row g-3" id="form_rdep">
      <input type="hidden" name="norek" value="<?php echo h($hitt['norek']); ?>">
      <div class="col-md-4">
        <label class="form-label" for="bulan">Tanggal Realisasi</label>
        <input type="date" name="bulan" id="bulan" value="<?php echo h(substr((string) $hitt['maturity_date'], 0, 10)); ?>" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="jenis">Jenis Realisasi</label>
        <select name="jenis" id="jenis" class="form-select" required>
          <option value="">Jenis Realisasi</option>
          <option value="PK">Aro</option>
          <option value="BREAK">Break</option>
          <option value="ARO-ARO">ARO-ARO</option>
        </select>
      </div>
      <div class="col-md-4" data-untuk="PK">
        <label class="form-label" for="add_baghas">Adjust Baghas</label>
        <input type="text" name="add_baghas" id="add_baghas" value="<?php echo h($hitt['rasio']); ?>" class="form-control" inputmode="decimal">
      </div>
      <div class="col-md-4" data-untuk="BREAK">
        <label class="form-label" for="jenis_break">Jenis Break</label>
        <select name="jenis_break" id="jenis_break" class="form-select">
          <option value="">Pilih Jenis Break</option>
          <option value="bayar">Bayarkan</option>
          <option value="titip">Titipan</option>
        </select>
      </div>
      <div class="col-md-4" data-untuk="ARO-ARO">
        <label class="form-label" for="jenis_aro">Jenis ARO</label>
        <select name="jenis_aro" id="jenis_aro" class="form-select">
          <option value="">Pilih ARO</option>
          <option value="ARO_bulan">Bulan</option>
          <option value="ARO_jatpo">Jatuh Tempo</option>
        </select>
      </div>
      <div class="col-md-4" data-untuk="ARO_jatpo">
        <label class="form-label" for="new_nomi">Nominal Baru (nominal + saldo escrow)</label>
        <input type="text" id="new_nomi" value="<?php echo number_format(compat_num($hitt['nominal']) + $saldo_aro); ?>" class="form-control" disabled>
      </div>
      <div class="col-md-4" data-untuk="ARO_jatpo">
        <label class="form-label" for="nisbah">Nisbah</label>
        <input type="text" name="nisbah" id="nisbah" value="<?php echo h($hitt['rasio']); ?>" class="form-control" inputmode="decimal">
      </div>
      <div class="col-md-4" data-untuk="ARO_jatpo">
        <label class="form-label" for="eq_rate">Eq Rate</label>
        <input type="text" name="eq_rate" id="eq_rate" value="<?php echo h($hitt['eq_rate']); ?>" class="form-control" inputmode="decimal">
      </div>
      <div class="col-12 d-flex gap-2 justify-content-end">
        <a href="view_tempo.php" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" name="input" value="1" class="btn btn-ni px-4" data-konfirmasi="Simpan realisasi jatuh tempo deposito ini?">Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
<?php
halaman_akhir(array('assets/app/fund_rdep.js'));
