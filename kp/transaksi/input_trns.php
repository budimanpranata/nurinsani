<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Transaksi > Simpanan > Edit (level kp), dibuka dari daftar simpanan dengan ?cif=...&type=...: catat transaksi
 * simpanan KP ke tabel_transaksi (2 baris), simpanan_kp, dan jurnal_umum.
 *   Jenis Transaksi GL (1)         : PB debet  -> debet rekening simpanan, kredit rekening GL; simpanan_kp DEBIT
 *                                    PB kredit -> debet rekening GL, kredit rekening simpanan; simpanan_kp KREDIT
 *   Jenis Transaksi Antar Rek. (2) : debet rekening simpanan jenis asal (tipe baris yang dibuka), kredit rekening
 *                                    simpanan jenis tujuan; simpanan_kp DEBIT di rekening asal, KREDIT di tujuan
 * Rumus dan arah jurnal sama dengan versi lama. Perbaikan 2026-11-03 atas perintah user (PEKERJAAN_TERTUNDA.md
 * bagian 44):
 *   - validasi: tanggal valid, nominal angka bulat > 0, jenis/jenis transaksi/PB dikenal, CIF punya rekening
 *     simpanan_kp, No GL wajib untuk GL dan harus rekening DETAIL unit 1010, rekening asal dan tujuan tidak sama;
 *   - saldo rekening yang didebet (GL PB debet dan rekening asal antar rekening) harus cukup;
 *   - simpanan_kp + tabel_transaksi dalam satu transaksi DB (semua tercatat atau tidak sama sekali); proses
 *     dikunci per aplikasi supaya simpan bersamaan tidak melewati cek saldo atau memakai kode BU/ yang sama;
 *   - jurnal_umum kini menyimpan kode_transaksi BU/... (dulu kosong); nomor kode BU/ untuk PB kredit diambil dari
 *     unit 1010 seperti jalur lain (dulu dari unit kosong); keterangan PB simpanan pokok terisi (dulu kosong).
 * Saran rekening GL dari ajax/cari_gl_kp.php (query sama dengan kp/autosuggest_kp_kp.php).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!isset($_SESSION['userid'])) {
    header("location:../index.html");
    exit;
}

$cif = isset($_GET['cif']) ? (string) $_GET['cif'] : '';
$type = isset($_GET['type']) ? (string) $_GET['type'] : '';

$cod = db_satu("SELECT kode_branch FROM user where userid=?", 's', (string) $userid);
$unit = $cod ? (string) $cod['kode_branch'] : '';

// rekening simpanan dan keterangan per jenis
$rekening_jenis = array(
    '04' => array('1010-3102000', 'Simpanan Pokok Anggota An ', 'PB Simpanan Pokok Anggota An '),
    '05' => array('1010-3202000', 'Simpanan Wajib Anggota An ', 'PB Simpanan Wajib Anggota An '),
    '06' => array('1010-3302000', 'Simpanan Khusus Anggota An ', 'PB SImpanan Khusus Anggota An '),
    '07' => array('1010-2102000', 'SImpanan UMUM Anggota An ', 'SImpanan UMUM Anggota An '),
);

/** Saldo satu rekening simpanan_kp (KREDIT - DEBIT). $kunci = true mengunci baris rekening sampai commit. */
function saldo_simpanan_kp($norek, $kunci = false)
{
    $s = db_satu("SELECT COALESCE(SUM(KREDIT - DEBIT), 0) as saldo FROM simpanan_kp WHERE SNOREK=?" . ($kunci ? " FOR UPDATE" : ""), 's', $norek);
    return $s ? (float) $s['saldo'] : 0.0;
}

$pesan = null;
$isi = array();
if (isset($_POST['input'])) {
    $pos = function ($k) {
        return isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
    };
    $isi = $_POST;
    $jenis = $pos('jenis');
    $tgl = $pos('tgl');
    $pb = $pos('pb');
    $coa = $pos('code');
    $scif = $pos('cif');
    $pob = $pos('pob');
    $tipe = $pos('tipe');
    $nominal_as = str_replace('.', '', $pos('nominal'));

    // ---- validasi ----
    $salah = array();
    $tanggal = DateTime::createFromFormat('!Y-m-d', $tgl);
    if (!$tanggal || $tanggal->format('Y-m-d') !== $tgl) {
        $salah[] = 'Tanggal transaksi tidak valid.';
    }
    if (!preg_match('/^[0-9]+$/', $nominal_as) || (int) $nominal_as <= 0) {
        $salah[] = 'Nominal harus angka bulat lebih dari 0 (tanpa koma, tanda minus, atau huruf).';
    }
    if (!isset($rekening_jenis[$jenis])) {
        $salah[] = 'Jenis simpanan belum dipilih.';
    }
    if ($pob !== '1' && $pob !== '2') {
        $salah[] = 'Jenis transaksi belum dipilih.';
    }
    if ($scif === '' || !db_satu("SELECT 1 as ada FROM simpanan_kp WHERE SCIF=? LIMIT 1", 's', $scif)) {
        $salah[] = 'CIF ' . $scif . ' tidak punya rekening simpanan KP. Buka transaksi dari daftar Simpanan.';
    }
    if ($pob === '1') {
        if ($pb !== 'debet' && $pb !== 'kredit') {
            $salah[] = 'Jenis PB belum dipilih.';
        }
        if ($coa === '') {
            $salah[] = 'No GL wajib diisi untuk transaksi GL.';
        } elseif (!db_satu("SELECT 1 as ada FROM tabel_master WHERE unit='1010' AND rekening_gl=? AND LINE_BALANCE='DETAIL'", 's', $coa)) {
            $salah[] = 'No GL ' . $coa . ' tidak terdaftar sebagai rekening DETAIL unit 1010.';
        }
    } elseif ($pob === '2') {
        if (!isset($rekening_jenis[$tipe])) {
            $salah[] = 'Jenis rekening asal tidak dikenal. Buka transaksi dari daftar Simpanan.';
        } elseif ($tipe === $jenis) {
            $salah[] = 'Rekening asal dan tujuan sama (jenis ' . $jenis . ').';
        }
    }

    if (!$salah) {
        list($sima, $ket, $ket2) = $rekening_jenis[$jenis];
        $norek = $scif . $jenis;
        $db = db_ni();
        $kunci = db_satu("SELECT GET_LOCK('nurinsani_kp_simpanan', 15) as ok");
        if (!$kunci || (int) $kunci['ok'] !== 1) {
            $salah[] = 'Sistem sedang memproses transaksi lain, coba lagi.';
        } else {
            $gagal_awal = db_jumlah_gagal();
            $db->begin_transaction();

            // rekening yang didebet harus bersaldo cukup
            $didebet = null;
            if ($pob === '1' && $pb === 'debet') {
                $didebet = $norek;
            } elseif ($pob === '2') {
                $didebet = $scif . $tipe;
            }
            if ($didebet !== null) {
                $saldo = saldo_simpanan_kp($didebet, true);
                if ($saldo < (int) $nominal_as) {
                    $salah[] = 'Saldo rekening ' . $didebet . ' tidak cukup: saldo ' . number_format($saldo, 0, ',', '.')
                        . ', nominal ' . number_format((int) $nominal_as, 0, ',', '.') . '.';
                }
            }

            if (!$salah) {
                // kode BU/<unit><nomor jurnal_umum terakhir unit 1010 + 1>
                $jurnal = db_satu("SELECT max(nomor_jurnal) as nomor FROM jurnal_umum where unit='1010'");
                $trns = "BU/" . $unit . (compat_num($jurnal ? $jurnal['nomor'] : null) + 1);

                $catat_transaksi = function ($rekening, $kolom, $keterangan) use ($unit, $trns, $tgl, $nominal_as, $userid) {
                    db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,$kolom,id_admin)
                        values(?,?,?,?,'Bukti System',?,?,?)", 'sssssss', $unit, $trns, $rekening, $tgl, $keterangan, $nominal_as, (string) $userid);
                };
                // kode kelompok dan CAO kosong seperti versi lama
                $catat_simpanan = function ($norek_isi, $tipe_isi, $debit, $kredit, $keterangan) use ($tgl, $unit, $scif, $userid, $trns) {
                    db_jalankan("Insert into simpanan_kp(BUSS_DATE,SNOREK,SUNIT,SCIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)
                        VALUES(?,?,?,?,'',?,?,?,?,?,NULL,'','1',?,?)", 'sssssssssss',
                        $tgl, $norek_isi, $unit, $scif, $debit, $tipe_isi, $kredit, (string) $userid, $keterangan, $tgl, $trns);
                };

                if ($pob === '1' && $pb === 'debet') {
                    $keterangan_transaksi = "$ket2 $norek";
                    $catat_transaksi($sima, 'debet', $keterangan_transaksi);
                    $catat_transaksi($coa, 'kredit', $keterangan_transaksi);
                    $catat_simpanan($norek, $jenis, $nominal_as, '0', $keterangan_transaksi);
                } elseif ($pob === '1') {
                    $keterangan_transaksi = "$ket $norek";
                    $catat_transaksi($coa, 'debet', $keterangan_transaksi);
                    $catat_transaksi($sima, 'kredit', $keterangan_transaksi);
                    $catat_simpanan($norek, $jenis, '0', $nominal_as, $keterangan_transaksi);
                } else {
                    $keterangan_transaksi = "$ket2 $norek";
                    $catat_transaksi($rekening_jenis[$tipe][0], 'debet', $keterangan_transaksi);
                    $catat_transaksi($sima, 'kredit', $keterangan_transaksi);
                    $catat_simpanan($scif . $tipe, $tipe, $nominal_as, '0', $keterangan_transaksi);
                    $catat_simpanan($norek, $jenis, '0', $nominal_as, $keterangan_transaksi);
                }

                if (db_jumlah_gagal() === $gagal_awal) {
                    $db->commit();
                    // jurnal_umum (MyISAM, tidak ikut transaksi) dicatat sesudah data utama tersimpan
                    if (!db_jalankan("insert into jurnal_umum(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values(NULL,?,?,?)", 'sss', $trns, $tgl, $unit)) {
                        $peringatan = 'Transaksi ' . $trns . ' tersimpan, tetapi pencatatan jurnal_umum gagal. Hubungi admin.';
                    }
                } else {
                    $db->rollback();
                    $salah[] = 'Penyimpanan gagal, tidak ada data yang dicatat.';
                }
            } else {
                $db->rollback();
            }
            db_satu("SELECT RELEASE_LOCK('nurinsani_kp_simpanan') as ok");
        }
    }

    if ($salah) {
        $pesan = array('danger', "Transaksi tidak disimpan:\n- " . implode("\n- ", $salah));
    } elseif (isset($peringatan)) {
        // data utama tersimpan, hanya jurnal_umum yang gagal
        $pesan = array('warning', $peringatan);
        $isi = array();
    } else {
        $pesan = array('success', 'Data Berhasil Di Simpan. Kode transaksi ' . $trns . '.');
        $isi = array();
    }
}

// saldo rekening simpanan CIF ini untuk acuan
$cif_tampil = $cif !== '' ? $cif : (isset($isi['cif']) ? (string) $isi['cif'] : '');
$saldo_cif = array();
foreach ($rekening_jenis as $kode => $r) {
    $saldo_cif[$kode] = $cif_tampil !== '' ? saldo_simpanan_kp($cif_tampil . $kode) : null;
}
$nama_jenis = array('04' => 'Pokok', '05' => 'Wajib', '06' => 'Khusus', '07' => 'Umum');
$nilai = function ($k) use ($isi) {
    return isset($isi[$k]) ? (string) $isi[$k] : '';
};
$terpilih = function ($k, $v) use ($nilai) {
    return $nilai($k) === $v ? ' selected' : '';
};

halaman_awal('Data Deposan Nur Insani');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert" style="white-space:pre-line">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<div class="card card-ni">
  <div class="card-header">Input Data Simpanan Nur Insani</div>
  <div class="card-body">
    <?php if ($cif_tampil !== ''): ?>
    <div class="table-responsive mb-3">
      <table class="table table-sm table-bordered table-ni mb-0">
        <thead><tr><th>Rekening</th><th>Jenis</th><th class="text-end">Saldo</th></tr></thead>
        <tbody>
        <?php foreach ($saldo_cif as $kode => $saldo): ?>
          <tr<?php echo $kode === $type ? ' class="table-active"' : ''; ?>>
            <td><?php echo h($cif_tampil . $kode); ?></td>
            <td><?php echo h($nama_jenis[$kode]); ?></td>
            <td class="text-end<?php echo $saldo < 0 ? ' text-danger' : ''; ?>"><?php echo number_format($saldo, 0, ',', '.'); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
    <form name="form1" id="form1" action="input_trns.php?cif=<?php echo h(rawurlencode($cif)); ?>&amp;type=<?php echo h(rawurlencode($type)); ?>" method="POST" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="tipe">Norek</label>
        <div class="input-group">
          <input type="text" name="tipe" id="tipe" placeholder="Tidak perlu di isi" value="<?php echo h($type); ?>" class="form-control" style="max-width:5rem" readonly>
          <input type="text" name="cif" id="cif" placeholder="Tidak perlu di isi" value="<?php echo h($cif); ?>" class="form-control" readonly>
        </div>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="tgl">Tanggal Transaksi</label>
        <input type="date" name="tgl" id="tgl" class="form-control" value="<?php echo h($nilai('tgl')); ?>" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="pb">Jenis PB</label>
        <select name="pb" id="pb" class="form-select" required>
          <option value="">Pilih PB</option>
          <option value="debet"<?php echo $terpilih('pb', 'debet'); ?>>DEBET</option>
          <option value="kredit"<?php echo $terpilih('pb', 'kredit'); ?>>KREDIT</option>
        </select>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="pob">Jenis Transaksi</label>
        <select name="pob" id="pob" class="form-select" required>
          <option value="">Jenis Transaksi</option>
          <option value="1"<?php echo $terpilih('pob', '1'); ?>>GL</option>
          <option value="2"<?php echo $terpilih('pob', '2'); ?>>Antar Rekening</option>
        </select>
      </div>
      <div class="col-sm-6<?php echo $nilai('pob') === '1' ? '' : ' d-none'; ?>" id="grup-gl">
        <label class="form-label" for="kode">No GL</label>
        <div class="autocomplete position-relative">
          <input type="text" name="code" id="kode" placeholder="Rekning GL" class="form-control" value="<?php echo h($nilai('code')); ?>"
                 data-autocomplete="../../ajax/cari_gl_kp.php" data-isi-nilai="rekening_gl" data-isi-label="label">
        </div>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nominal">Nominal Simpanan</label>
        <input type="text" name="nominal" id="nominal" placeholder="Nominal Simpanan" class="form-control" inputmode="numeric" value="<?php echo h($nilai('nominal')); ?>" required>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="jenis">Jenis Simpanan</label>
        <select name="jenis" id="jenis" class="form-select" required>
          <option value="">Pilih Simpanan</option>
          <?php foreach ($nama_jenis as $kode => $nama): ?>
          <option value="<?php echo $kode; ?>"<?php echo $terpilih('jenis', $kode); ?>><?php echo h($nama); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 d-flex gap-2">
        <button type="submit" name="input" id="input" class="btn btn-ni">Simpan</button>
        <a href="../view/master_trans.php" class="btn btn-outline-secondary">Kembali</a>
      </div>
    </form>
  </div>
</div>
<script>
(function () {
    // pemisah ribuan titik saat mengetik; titik dibuang lagi saat disimpan
    var nominal = document.getElementById('nominal');
    nominal.addEventListener('keyup', function () {
        var angka = nominal.value.replace(/[^\d]/g, '');
        nominal.value = angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
    // isian No GL tampil saat GL (1), sembunyi saat Antar Rekening (2)
    var pob = document.getElementById('pob');
    var gl = document.getElementById('grup-gl');
    pob.addEventListener('change', function () {
        if (pob.value === '1') {
            gl.classList.remove('d-none');
        } else if (pob.value === '2') {
            gl.classList.add('d-none');
        }
    });
    // cegah klik ganda (tombol tidak di-disable supaya nilai "input" tetap terkirim)
    var terkirim = false;
    document.getElementById('form1').addEventListener('submit', function (e) {
        if (terkirim) {
            e.preventDefault();
        }
        terkirim = true;
    });
})();
</script>
<?php
halaman_akhir();
