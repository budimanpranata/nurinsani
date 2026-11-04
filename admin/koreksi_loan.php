<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Admin > Koreksi Pembiayaan (perintah user 2026-11-04): koreksi data tabel loan satu pembiayaan (OS, saldo margin,
 * ijaroh, bulat, angsuran ke, run tenor, status, gol, semua tanggal). Koreksi jurnal dilakukan level Lead (menu koreksi);
 * menu ini untuk data pembiayaannya.
 *   - cari CIF / nomor anggota / nama (semua unit), pilih satu pembiayaan
 *   - form berisi nilai sekarang; hanya kolom yang diubah yang disimpan; alasan wajib
 *   - simpan dalam transaksi: baris loan dikunci (FOR UPDATE), ditolak bila data berubah sejak form dibuka (mis. EOD
 *     atau angsuran masuk), lalu UPDATE loan + satu baris riwayat koreksi_loan per kolom
 *     (db/parameter/2026-11-04_koreksi_loan.sql)
 *   - kartu angsuran (rek_loan) TIDAK ikut dicatat (keputusan user); saldonya hanya ditampilkan sebagai pembanding
 * Hanya level admin (cek_admin.php).
 */
require_once __DIR__ . '/cek_admin.php';
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
require_once __DIR__ . '/../inc/tampilan.php';
include_once __DIR__ . '/../function.php';

/** Kolom loan yang boleh dikoreksi => array(label, jenis, batas). jenis: angka (bigint), bulat (int kecil), tanggal, teks. */
const KOREKSI_KOLOM = array(
    'os' => array('OS (sisa pokok)', 'angka', 0),
    'saldo_margin' => array('Saldo Margin', 'angka', 0),
    'ijaroh' => array('Margin per Angsuran (ijaroh)', 'angka', 0),
    'bulat' => array('Pembulatan Angsuran (bulat)', 'angka', 0),
    'ke' => array('Angsuran Ke', 'bulat', 99),
    'run_tenor' => array('Run Tenor', 'bulat', 999),
    'status' => array('Status', 'teks', 20),
    'gol' => array('Kolektibilitas (gol)', 'teks', 10),
    'buss_date' => array('Tanggal Input (buss_date)', 'tanggal', 0),
    'tgl_wakalah' => array('Tanggal Wakalah', 'tanggal', 0),
    'tgl_akad' => array('Tanggal Akad', 'tanggal', 0),
    'tgl_murab' => array('Tanggal Murabahah', 'tanggal', 0),
    'next_schedule' => array('Jadwal Berikutnya', 'tanggal', 0),
    'maturity_date' => array('Jatuh Tempo', 'tanggal', 0),
    'last_payment' => array('Pembayaran Terakhir', 'tanggal', 0),
);

/** Baris loan (kolom yang dikoreksi + info), atau null. $kunci: SELECT ... FOR UPDATE (di dalam transaksi). */
function ambil_loan($cif, $kunci = false)
{
    $r = db_satu("select cif, no_anggota, Cust_Short_name, unit, Plafond, angsuran, pokok, Tenor, status_app, cao, "
        . implode(', ', array_keys(KOREKSI_KOLOM)) . " from loan where cif = ?" . ($kunci ? ' for update' : ''), 's', $cif);
    if (!$r) {
        return null;
    }
    foreach ($r as $k => $v) {
        $r[$k] = $v === null ? null : (string) $v;
    }
    return $r;
}

/** Sidik nilai kolom yang dikoreksi, untuk menolak simpan bila data berubah sejak form dibuka. */
function sidik_loan($r)
{
    $isi = array();
    foreach (array_keys(KOREKSI_KOLOM) as $k) {
        $isi[] = $k . '=' . (string) $r[$k];
    }
    return hash('sha256', implode("\n", $isi));
}

/** Nilai untuk isian form: tanggal -> Y-m-d (kosong bila 0000-00-00). */
function nilai_form($kolom, $nilai)
{
    if (KOREKSI_KOLOM[$kolom][1] === 'tanggal') {
        $t = substr((string) $nilai, 0, 10);
        return ($t === '' || $t === '0000-00-00') ? '' : $t;
    }
    return (string) $nilai;
}

/**
 * Nilai baru dari POST untuk satu kolom: array(nilai untuk disimpan atau null bila tidak berubah, pesan galat).
 * Kolom tanggal hanya dianggap berubah bila tanggalnya berbeda (jam yang tersimpan tidak dipakai di form).
 */
function nilai_baru($kolom, $lama, $input)
{
    list($label, $jenis, $batas) = KOREKSI_KOLOM[$kolom];
    $input = trim((string) $input);
    if ($jenis === 'angka') {
        $angka = preg_replace('/[.,\s]/', '', $input);
        if (!preg_match('/^-?\d{1,15}$/', $angka)) {
            return array(null, "$label harus angka.");
        }
        if ((int) $angka < 0) {
            return array(null, "$label tidak boleh negatif.");
        }
        return array((string) (int) $angka === (string) (int) $lama ? null : (string) (int) $angka, null);
    }
    if ($jenis === 'bulat') {
        if (!preg_match('/^\d{1,3}$/', $input) || (int) $input > $batas) {
            return array(null, "$label harus angka 0-$batas.");
        }
        return array((int) $input === (int) $lama && $lama !== '' ? null : (string) (int) $input, null);
    }
    if ($jenis === 'tanggal') {
        if ($input === nilai_form($kolom, $lama)) {
            return array(null, null);
        }
        $d = DateTime::createFromFormat('!Y-m-d', $input);
        if (!$d || $d->format('Y-m-d') !== $input) {
            return array(null, "$label harus tanggal yang benar.");
        }
        return array($input, null);
    }
    if (mb_strlen($input) > $batas) {
        return array(null, "$label maksimal $batas karakter.");
    }
    return array($input === (string) $lama ? null : $input, null);
}

$userid = (string) $_SESSION['userid'];
$cif = isset($_REQUEST['cif']) ? trim((string) $_REQUEST['cif']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $alasan = trim(isset($_POST['alasan']) ? (string) $_POST['alasan'] : '');
    $galat = array();
    $ubah = array();
    $db = db_ni();
    $db->begin_transaction();
    $loan = ambil_loan($cif, true);
    if (!$loan) {
        $galat[] = 'Pembiayaan dengan CIF ' . $cif . ' tidak ditemukan.';
    } elseif (!hash_equals(sidik_loan($loan), isset($_POST['sidik']) ? (string) $_POST['sidik'] : '')) {
        $galat[] = 'Data pembiayaan ini sudah berubah sejak form dibuka (mis. EOD atau angsuran masuk). Periksa nilai terbaru lalu koreksi lagi.';
    } else {
        foreach (array_keys(KOREKSI_KOLOM) as $k) {
            list($baru, $g) = nilai_baru($k, $loan[$k], isset($_POST['nilai'][$k]) ? $_POST['nilai'][$k] : nilai_form($k, $loan[$k]));
            if ($g !== null) {
                $galat[] = $g;
            } elseif ($baru !== null) {
                $ubah[$k] = $baru;
            }
        }
        if (!$galat && !$ubah) {
            $galat[] = 'Tidak ada nilai yang diubah.';
        }
        if (!$galat && ($alasan === '' || mb_strlen($alasan) > 255)) {
            $galat[] = 'Alasan koreksi wajib diisi (maksimal 255 karakter).';
        }
    }
    if (!$galat) {
        $gagal_awal = db_jumlah_gagal();
        $set = implode(', ', array_map(function ($k) { return "$k = ?"; }, array_keys($ubah)));
        db_jalankan("update loan set $set where cif = ?", str_repeat('s', count($ubah) + 1), ...array_merge(array_values($ubah), array($cif)));
        foreach ($ubah as $k => $v) {
            db_jalankan("insert into koreksi_loan (waktu, userid, cif, no_anggota, kolom, nilai_lama, nilai_baru, alasan) values (now(), ?, ?, ?, ?, ?, ?, ?)",
                'sssssss', $userid, $cif, (string) $loan['no_anggota'], $k, $loan[$k], $v, $alasan);
        }
        if (db_jumlah_gagal() === $gagal_awal) {
            $db->commit();
            $_SESSION['pesan_koreksi_loan'] = array('success', 'Koreksi tersimpan: ' . implode(', ', array_map(function ($k) { return KOREKSI_KOLOM[$k][0]; }, array_keys($ubah))) . '.');
        } else {
            $db->rollback();
            $_SESSION['pesan_koreksi_loan'] = array('danger', 'Koreksi gagal disimpan, tidak ada yang berubah: ' . db_error_terakhir());
        }
    } else {
        $db->rollback();
        $_SESSION['pesan_koreksi_loan'] = array('danger', implode(' ', $galat));
        // isian yang sudah diketik dipertahankan saat kembali ke form
        $_SESSION['isian_koreksi_loan'] = array('cif' => $cif, 'nilai' => isset($_POST['nilai']) ? (array) $_POST['nilai'] : array(), 'alasan' => $alasan);
    }
    header('Location: koreksi_loan.php?cif=' . urlencode($cif));
    exit;
}

$pesan = isset($_SESSION['pesan_koreksi_loan']) ? $_SESSION['pesan_koreksi_loan'] : null;
$isian = isset($_SESSION['isian_koreksi_loan']) && $_SESSION['isian_koreksi_loan']['cif'] === $cif ? $_SESSION['isian_koreksi_loan'] : null;
unset($_SESSION['pesan_koreksi_loan'], $_SESSION['isian_koreksi_loan']);

$cari = isset($_GET['cari']) ? trim((string) $_GET['cari']) : '';
$daftar = array();
if ($cari !== '' && $cif === '') {
    $daftar = db_semua("select cif, no_anggota, Cust_Short_name, unit, os, saldo_margin, status from loan
        where cif = ? or no_anggota = ? or Cust_Short_name like ? order by Cust_Short_name limit 50", 'sss', $cari, $cari, '%' . $cari . '%');
}
$loan = $cif !== '' ? ambil_loan($cif) : null;
$kartu = $loan ? db_satu("select sum(saldo_kredit) - sum(debet) as saldo, count(*) as n from rek_loan where cif = ?", 's', $cif) : null;
$riwayat = db_semua("select waktu, userid, cif, no_anggota, kolom, nilai_lama, nilai_baru, alasan from koreksi_loan"
    . ($loan ? ' where cif = ?' : '') . " order by id desc limit 50", $loan ? 's' : '', ...($loan ? array($cif) : array()));
$saran = array(
    'status' => array_column(db_semua("select distinct status from loan where status <> '' order by status"), 'status'),
    'gol' => array_column(db_semua("select distinct gol from loan where gol is not null and gol <> '' order by gol"), 'gol'),
);

halaman_awal('Koreksi Pembiayaan');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo h($pesan[0]); ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni mw-100">
  <div class="card-header">Cari Pembiayaan</div>
  <div class="card-body">
    <form method="get" action="koreksi_loan.php" class="row g-2 align-items-end">
      <div class="col-sm-6">
        <label class="form-label" for="cari">CIF / Nomor Anggota / Nama</label>
        <input type="text" name="cari" id="cari" value="<?php echo h($cari); ?>" class="form-control" required>
      </div>
      <div class="col-sm-3"><button type="submit" class="btn btn-ni">Cari</button></div>
    </form>
    <?php if ($cari !== '' && $cif === ''): ?>
    <div class="table-responsive mt-3">
      <table class="table table-sm table-bordered table-ni align-middle">
        <thead><tr><th>CIF</th><th>No Anggota</th><th>Nama</th><th>Unit</th><th class="text-end">OS</th><th class="text-end">Saldo Margin</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php if (!$daftar): ?><tr><td colspan="8" class="text-center text-muted">Tidak ditemukan</td></tr><?php endif; ?>
          <?php foreach ($daftar as $d): ?>
          <tr>
            <td><?php echo h($d['cif']); ?></td><td><?php echo h($d['no_anggota']); ?></td><td><?php echo h($d['Cust_Short_name']); ?></td>
            <td><?php echo h($d['unit']); ?></td><td class="text-end"><?php echo number_format(compat_num($d['os'])); ?></td>
            <td class="text-end"><?php echo number_format(compat_num($d['saldo_margin'])); ?></td><td><?php echo h($d['status']); ?></td>
            <td class="text-center"><a class="btn btn-sm btn-outline-primary" href="koreksi_loan.php?cif=<?php echo h(urlencode($d['cif'])); ?>">Pilih</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($cif !== '' && !$loan): ?>
<div class="alert alert-warning mt-3">Pembiayaan dengan CIF <?php echo h($cif); ?> tidak ditemukan.</div>
<?php elseif ($loan): ?>
<form method="post" action="koreksi_loan.php" class="card card-ni mw-100 mt-3"
      onsubmit="if (!confirm('Simpan koreksi pembiayaan CIF <?php echo h($cif); ?>? Perubahan langsung berlaku dan tercatat di riwayat.')) return false; this.querySelector('button[type=submit]').disabled = true;">
  <div class="card-header">Koreksi <?php echo h($loan['cif'] . ' - ' . $loan['Cust_Short_name']); ?></div>
  <div class="card-body">
    <p class="small mb-3">
      No Anggota <strong><?php echo h($loan['no_anggota']); ?></strong> · Unit <?php echo h($loan['unit']); ?> ·
      <?php echo h($loan['status_app']); ?> · Plafond <?php echo number_format(compat_num($loan['Plafond'])); ?> ·
      Angsuran <?php echo number_format(compat_num($loan['angsuran'])); ?> (pokok <?php echo number_format(compat_num($loan['pokok'])); ?>) ·
      Tenor <?php echo h($loan['Tenor']); ?> · AO <?php echo h($loan['cao']); ?><br>
      Pembanding kartu angsuran (rek_loan, tidak diubah menu ini): saldo
      <strong><?php echo number_format(compat_num($kartu ? $kartu['saldo'] : 0)); ?></strong> dari <?php echo (int) ($kartu ? $kartu['n'] : 0); ?> baris.
    </p>
    <input type="hidden" name="cif" value="<?php echo h($cif); ?>">
    <input type="hidden" name="sidik" value="<?php echo h(sidik_loan($loan)); ?>">
    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle">
        <thead><tr><th>Kolom</th><th>Nilai Sekarang</th><th style="min-width: 14rem;">Nilai Baru</th></tr></thead>
        <tbody>
          <?php foreach (KOREKSI_KOLOM as $k => $info):
              $sekarang = nilai_form($k, $loan[$k]);
              $isi = $isian && isset($isian['nilai'][$k]) ? (string) $isian['nilai'][$k] : $sekarang; ?>
          <tr>
            <td><?php echo h($info[0]); ?> <span class="small text-muted">(<?php echo $k; ?>)</span></td>
            <td><?php echo h($info[1] === 'angka' ? number_format(compat_num($loan[$k])) : ($loan[$k] === null ? '' : $loan[$k])); ?></td>
            <td>
              <?php if ($info[1] === 'tanggal'): ?>
              <input type="date" name="nilai[<?php echo $k; ?>]" value="<?php echo h($isi); ?>" class="form-control form-control-sm">
              <?php elseif ($info[1] === 'teks'): ?>
              <input type="text" name="nilai[<?php echo $k; ?>]" value="<?php echo h($isi); ?>" maxlength="<?php echo (int) $info[2]; ?>" list="saran_<?php echo $k; ?>" class="form-control form-control-sm">
              <datalist id="saran_<?php echo $k; ?>"><?php foreach ($saran[$k] as $s): ?><option value="<?php echo h($s); ?>"><?php endforeach; ?></datalist>
              <?php else: ?>
              <input type="text" name="nilai[<?php echo $k; ?>]" value="<?php echo h($isi); ?>" inputmode="numeric" class="form-control form-control-sm text-end">
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <label class="form-label" for="alasan">Alasan Koreksi <span class="text-danger">*</span></label>
    <textarea name="alasan" id="alasan" rows="2" maxlength="255" class="form-control" required><?php echo h($isian ? $isian['alasan'] : ''); ?></textarea>
    <div class="form-text">Hanya kolom yang nilainya diubah yang disimpan. Kartu angsuran (rek_loan) dan jurnal tidak ikut berubah.</div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" name="simpan" value="1" class="btn btn-ni">Simpan Koreksi</button>
      <a href="koreksi_loan.php?cif=<?php echo h(urlencode($cif)); ?>" class="btn btn-outline-secondary">Batal</a>
    </div>
  </div>
</form>
<?php endif; ?>

<div class="card card-ni mw-100 mt-3">
  <div class="card-header">Riwayat Koreksi <?php echo $loan ? 'CIF ' . h($cif) : '(50 terakhir)'; ?></div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-bordered table-ni align-middle mb-0">
        <thead><tr><th>Waktu</th><th>User</th><th>CIF</th><th>Kolom</th><th>Nilai Lama</th><th>Nilai Baru</th><th>Alasan</th></tr></thead>
        <tbody>
          <?php if (!$riwayat): ?><tr><td colspan="7" class="text-center text-muted">Belum ada koreksi</td></tr><?php endif; ?>
          <?php foreach ($riwayat as $r): ?>
          <tr>
            <td class="text-nowrap"><?php echo h($r['waktu']); ?></td><td><?php echo h($r['userid']); ?></td>
            <td><a href="koreksi_loan.php?cif=<?php echo h(urlencode($r['cif'])); ?>"><?php echo h($r['cif']); ?></a></td>
            <td><?php echo h(isset(KOREKSI_KOLOM[$r['kolom']]) ? KOREKSI_KOLOM[$r['kolom']][0] : $r['kolom']); ?></td>
            <td><?php echo h($r['nilai_lama']); ?></td><td><?php echo h($r['nilai_baru']); ?></td><td><?php echo h($r['alasan']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
halaman_akhir();
