<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Data Break > Break (?kd=norek dari ../view/view_break.php): bayar break deposito yang ada di dep_break.
 *   Nilai bawaan (sama dengan versi lama): Margin = floor(gross) dengan gross = (nominal x eq_rate% x hari / 365) / 0.9,
 *   hari = jumlah hari bulan sebelum tanggal break (DATEDIFF(maturity_date, maturity_date - 1 bulan)); Simpanan Umum
 *   (hanya tampil) = saldo escrow_deposito norek, ditambah Margin bila saldo > 0; SIMKA = nominal dep_break.
 *   Bayar (jurnal SAMA dengan yang berjalan di versi lama, perintah user 2026-11-04): jumlah = Margin + SIMKA;
 *   tabel_transaksi 'Bukti System' tanggal break: debet sima (menurut tenor) / kredit 1010-2852000 sebesar jumlah;
 *   jurnal_umum (tanggal hari ini); dep_realisasi BREAK (id = norek) dibayar lewat Disburse kp; baris dep_break dihapus.
 *   sima: 1 -> 1010-2201000, 3 -> 2202000, 6 -> 2203000, 12 -> 2204000, lainnya 2206000.
 * Dimigrasi 2026-11-04: tampilan Bootstrap 5, prepared statement, skrip assets/app/fund_break_input.js.
 * Perbaikan atas perintah user 2026-11-04:
 *   - wajib login level fund; var_dump() dibuang; tanggal & angka divalidasi; dikunci per norek; sesudah Bayar dialihkan
 *   - ditolak bila norek tidak ada lagi di dep_break (dulu klik ulang/F5 mencatat jurnal lagi dengan tenor & CIF kosong)
 *     atau sudah punya dep_realisasi (dulu insert BREAK gagal diam-diam bila PENEMPATAN belum di-Disburse, jurnal tetap
 *     masuk dan dep_break terhapus -> break hilang)
 *   - tabel_transaksi dalam transaksi; satu nomor bukti BU/-<unit>-<6 digit> (inc/nomor_bukti.php) dan jurnal_umum
 *     mencatat kode itu (dulu kode kosong); dep_realisasi tgl_jatuh_tempo & norek_tujuan kini terisi (dulu kosong)
 *   - salinan ke audit_dep_realisasi saat Break dihapus (Disburse kp sudah menyalin ke audit; dulu tercatat dua kali)
 *   - jalur "margin > 0" versi lama tidak pernah jalan (if ($bijak = $jlbayar_rate) adalah penugasan, nilainya 0 saat
 *     POST) dan rusak (jurnal tidak seimbang, salah ketik alues, $konesi) -> dibuang; yang dipertahankan = jalur yang
 *     selama ini berjalan
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
require_once __DIR__ . '/../../inc/nomor_bukti.php';
include_once __DIR__ . '/../../function.php';

$userid = (string) $_SESSION['userid'];
$k = db_satu("select kode_branch AS unit FROM user where userid=?", 's', $userid);
$unit = $k ? (string) $k['unit'] : '';

/** Data dep_break + nilai bawaan form (versi lama), atau null. */
function data_break($norek)
{
    $hit = db_satu("select dep_break.*, CUST_SHORT_NAME, norek_tujuan,
            datediff(maturity_date,DATE_ADD(maturity_date, INTERVAL -1 Month)) as hari
        from dep_break left join deposan on deposan.cif = dep_break.cif where norek =?", 's', $norek);
    if (!$hit) {
        return null;
    }
    $bayar_rate = (compat_num($hit['nominal']) * (compat_num($hit['eq_rate']) / 100)) * compat_num($hit['hari']) / 365;
    $gross_rate = $bayar_rate / 0.9;
    // versi lama: floor($gross_rate - $pph4_rate) dengan $pph4_rate tidak pernah diisi = floor(gross)
    $hit['margin'] = floor($gross_rate);
    $es = db_satu("SELECT sum(KREDIT)-sum(DEBIT) as saldo FROM escrow_deposito where NOREK=? group by NOREK order by NOREK", 's', $norek);
    $saldo_tab = compat_num($es ? $es['saldo'] : null);
    $hit['simpanan'] = $saldo_tab > 0 ? $saldo_tab + $hit['margin'] : $saldo_tab;
    return $hit;
}

/** Angka rupiah dari input (titik ribuan dibuang); null bila bukan angka bulat >= 0. */
function angka_rupiah($s)
{
    $s = str_replace('.', '', trim((string) $s));
    return ctype_digit($s) ? $s : null;
}

/** Proses Bayar; hasil array(berhasil, pesan). Dipanggil di dalam kunci. */
function bayar_break($unit, $userid)
{
    $norek = isset($_POST['norek']) ? trim((string) $_POST['norek']) : '';
    $tgl = isset($_POST['bulan']) ? str_replace('/', '-', substr(trim((string) $_POST['bulan']), 0, 10)) : '';
    $bijak = angka_rupiah(isset($_POST['nominal']) ? $_POST['nominal'] : '');
    $simka = angka_rupiah(isset($_POST['simka']) ? $_POST['simka'] : '');

    $hit = data_break($norek);
    if (!$hit) {
        return array(false, "Deposito $norek tidak ada di data Break (mungkin sudah dibayar).");
    }
    if (db_satu("select 1 as ada from dep_realisasi where id_realisasi=?", 's', $norek)) {
        return array(false, "Deposito $norek masih punya data realisasi yang belum di-Disburse kp. Selesaikan dulu sebelum Break.");
    }
    $d = DateTime::createFromFormat('!Y-m-d', $tgl);
    if (!$d || $d->format('Y-m-d') !== $tgl) {
        return array(false, 'Tanggal Break tidak valid.');
    }
    if ($bijak === null || $simka === null) {
        return array(false, 'Margin dan SIMKA harus angka.');
    }
    $nominal_break = (string) ((int) $bijak + (int) $simka);
    $tenor = (string) $hit['tenor'];
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
    $ket = "Break Penempatan Dana Norek $norek";
    $kode = nb_baru('BU', $unit);

    // dep_realisasi (MyISAM) lebih dulu: kunci utama id_realisasi = norek mencegah break dicatat dua kali
    if (!db_jalankan("insert into dep_realisasi(id_realisasi,norek,cif,jumlah_bayar,tgl_bayar,tgl_jatuh_tempo,norek_tujuan,ket,status)
            VALUES(?,?,?,?,?,?,?,'EVVI','BREAK')", 'sssssss',
            $norek, $norek, (string) $hit['cif'], $nominal_break, $tgl, (string) $hit['maturity_date'], (string) $hit['norek_tujuan'])) {
        return array(false, 'Ups, Data Gagal Di simpan ! ' . db_error_terakhir());
    }
    $db = db_ni();
    $gagal_awal = db_jumlah_gagal();
    $db->begin_transaction();
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
        values(?,?,?,?,'Bukti System',?,?,?)", 'sssssss', $unit, $kode, $sima, $tgl, $ket, $nominal_break, $userid);
    db_jalankan("insert into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
        values(?,?,'1010-2852000',?,'Bukti System',?,?,?)", 'ssssss', $unit, $kode, $tgl, $ket, $nominal_break, $userid);
    if (db_jumlah_gagal() > $gagal_awal) {
        $galat = db_error_terakhir();
        $db->rollback();
        db_jalankan("delete from dep_realisasi where id_realisasi=? and status='BREAK'", 's', $norek);
        return array(false, 'Ups, Data Gagal Di simpan ! ' . $galat);
    }
    $db->commit();
    db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('',?,?,?)", 'sss', $kode, date('Y-m-d'), $unit);
    db_jalankan("delete FROM dep_break WHERE norek=?", 's', $norek);
    return array(true, "Data Berhasil Di Simpan. Break $norek sebesar " . number_format((float) $nominal_break, 0, ',', '.')
        . " menunggu Disburse kp ($kode).");
}

if (isset($_POST['input'])) {
    $norek = isset($_POST['norek']) ? (string) $_POST['norek'] : '';
    $kunci = db_satu("SELECT GET_LOCK(?, 15) as ok", 's', 'nurinsani_break_' . $norek);
    if (!$kunci || (int) $kunci['ok'] !== 1 || !nb_kunci('BU', $unit)) {
        $hasil = array(false, 'Proses lain sedang berjalan, silakan ulangi.');
    } else {
        $hasil = bayar_break($unit, $userid);
        nb_lepas('BU', $unit);
    }
    db_satu("SELECT RELEASE_LOCK(?) as ok", 's', 'nurinsani_break_' . $norek);
    if ($hasil[0]) {
        $_SESSION['pesan_break'] = array('success', $hasil[1]);
        header('Location: ../view/view_break.php');
    } else {
        $_SESSION['pesan_input_break'] = array('danger', $hasil[1]);
        header('Location: input_break.php?kd=' . urlencode($norek));
    }
    exit;
}

$code = isset($_GET['kd']) ? trim((string) $_GET['kd']) : '';
$hit = $code === '' ? null : data_break($code);
$pesan = isset($_SESSION['pesan_input_break']) ? $_SESSION['pesan_input_break'] : null;
unset($_SESSION['pesan_input_break']);

halaman_awal('Input Data Break Nur Insani');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<?php if (!$hit): ?>
<div class="alert alert-warning">Deposito <?php echo h($code); ?> tidak ada di data Break (mungkin sudah dibayar).</div>
<a href="../view/view_break.php" class="btn btn-sm btn-secondary">Kembali</a>
<?php else: ?>
<div class="card card-ni mw-100">
  <div class="card-header">Break <?php echo h($hit['norek'] . ' - ' . $hit['CUST_SHORT_NAME']); ?></div>
  <div class="card-body">
    <dl class="row small mb-4">
      <dt class="col-sm-2">Nominal</dt><dd class="col-sm-4"><?php echo number_format(compat_num($hit['nominal']), 0, ',', '.'); ?></dd>
      <dt class="col-sm-2">Tenor</dt><dd class="col-sm-4"><?php echo h($hit['tenor']); ?> bulan</dd>
      <dt class="col-sm-2">Eq Rate</dt><dd class="col-sm-4"><?php echo h($hit['eq_rate']); ?> %</dd>
      <dt class="col-sm-2">Hari Margin</dt><dd class="col-sm-4"><?php echo h($hit['hari']); ?> hari</dd>
    </dl>
    <form method="post" action="input_break.php" class="row g-3">
      <input type="hidden" name="norek" value="<?php echo h($hit['norek']); ?>">
      <div class="col-md-3">
        <label class="form-label" for="bulan">Tanggal Break</label>
        <input type="date" name="bulan" id="bulan" value="<?php echo h(substr((string) $hit['maturity_date'], 0, 10)); ?>" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="nominal">Margin</label>
        <input type="text" name="nominal" id="nominal" value="<?php echo number_format($hit['margin'], 0, ',', '.'); ?>" class="form-control text-end" inputmode="numeric" data-rupiah required>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="simpanan">Simpanan Umum</label>
        <input type="text" id="simpanan" value="<?php echo number_format($hit['simpanan'], 0, ',', '.'); ?>" class="form-control text-end" disabled>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="simka">SIMKA</label>
        <input type="text" name="simka" id="simka" value="<?php echo number_format(compat_num($hit['nominal']), 0, ',', '.'); ?>" class="form-control text-end" inputmode="numeric" data-rupiah required>
      </div>
      <div class="col-12 d-flex gap-2 justify-content-end align-items-center">
        <span class="small text-muted me-auto">Dibayarkan (Margin + SIMKA): <strong id="total_break"></strong></span>
        <a href="../view/view_break.php" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" name="input" value="1" class="btn btn-danger px-4" data-konfirmasi="Bayar break deposito ini? Jurnal akan langsung dibukukan.">Bayar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
<?php
halaman_akhir(array('assets/app/fund_break_input.js'));
