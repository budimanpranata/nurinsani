<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu Transaksi > Setor/Penarikan (level kp): setor / tarik tunai escrow deposito.
 *   01 Setor Tunai : escrow_deposito KREDIT, jurnal debet 1120000 / kredit 2102000 (unit deposito)
 *   02 Tarik Tunai : bila saldo escrow rekening cukup, escrow_deposito DEBIT, jurnal debet 2102000 / kredit 1120000
 *   03 Setoran Angsuran, 04 Pemindah Bukuan : belum tersedia (pesan)
 * Lalu tampil mutasi escrow CIF dengan saldo berjalan. Kode transaksi BU/<unit><7 karakter acak>.
 * Rekening jurnal (1120000/2102000) dan pesan sama dengan versi lama; tampilan dan query (prepared statement) diperbarui.
 * Saran rekening dari ajax/cari_deposito_kp.php (query sama dengan kp/view/autosuggesst_rek.php).
 * Perbaikan 2026-11-03 (perintah user, PEKERJAAN_TERTUNDA.md bagian 49):
 *   - Tarik Tunai memeriksa saldo rekening (norek) yang ditarik, bukan gabungan semua rekening CIF
 *   - nominal harus angka bulat > 0 (diperiksa di server); rekening harus deposito unit user (sama dengan saran)
 *   - proses dikunci (GET_LOCK) supaya dua penarikan bersamaan tidak sama-sama lolos cek saldo
 *   - jurnal (tabel_transaksi) dalam transaksi DB; bila jurnal gagal, baris escrow dihapus lagi
 *   - pesan sukses hanya sesudah data tersimpan; redirect kembali ke view_stor.php (dulu stor.php / view_stor..php
 *     yang tidak ada)
 */
require_once __DIR__ . '/../../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['stgl_system'])) {
    echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
    header("location:param_tgl.php");
    exit;
}
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
include "../../function.php";

$pos = function ($k) {
    return isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
};
$norek = $pos('kode_rekening');
$nominal = $pos('nama');
$pilih = $pos('select');
$keterangan = $pos('ket');

$ku = db_satu("select kode_branch from user where userid=?", 's', (string) $userid);
$unit_user = $ku ? (string) $ku['kode_branch'] : '';
// hanya deposito unit user (sama dengan saringan saran rekening)
$kkel = db_satu("select deposito.CIF,deposito.UNIT,CUST_SHORT_NAME FROM deposito left join deposan on deposan.cif = deposito.cif where  norek =? and deposito.UNIT=?", 'ss', $norek, $unit_user);
$ciff = $kkel ? (string) $kkel['CIF'] : '';
$unit = $kkel ? (string) $kkel['UNIT'] : '';
$nama = $kkel ? (string) $kkel['CUST_SHORT_NAME'] : '';
$kode_trans = "BU/" . $unit . acakangkahuruf(7);

$skrip = array();
$select = array();
/** Mutasi escrow CIF dengan saldo berjalan (@bal dihitung MySQL seperti versi lama). */
$mutasi = function ($cif) {
    db_jalankan("SET @bal = 0;");
    return db_semua("SELECT escrow_deposito.NOREK,escrow_deposito.BUSS_DATE,deposan.CUST_SHORT_NAME,DEBIT,KREDIT,@bal:=(@bal+KREDIT)-DEBIT as balance FROM escrow_deposito
        left join deposan on deposan.cif = escrow_deposito.cif
        where escrow_deposito.cif=? order by escrow_deposito.REFF", 's', $cif);
};
/**
 * Catat escrow (MyISAM) lalu 2 baris jurnal dalam transaksi DB; bila jurnal gagal, escrow dihapus lagi.
 * $sisi_escrow 'DEBIT' (tarik) atau 'KREDIT' (setor). Hasil true bila semuanya tersimpan.
 */
$catat = function ($sisi_escrow, $rek_debet, $rek_kredit, $ket1) use ($tgl_system, $norek, &$unit, $ciff, $nominal, $userid, $kode_trans) {
    $debit = $sisi_escrow === 'DEBIT' ? $nominal : '0';
    $kredit = $sisi_escrow === 'KREDIT' ? $nominal : '0';
    $db = db_ni();
    if (!db_jalankan("Insert into escrow_deposito (BUSS_DATE,NOREK,UNIT,CIF,DEBIT,TYPE,KREDIT,USERID,KET,REFF,kode_transaksi)VALUES(?,?,?,?,?,'02',?,?,?,NULL,?)",
        'sssssssss', (string) $tgl_system, $norek, $unit, $ciff, $debit, $kredit, (string) $userid, $ket1, $kode_trans)) {
        return false;
    }
    $reff = $db->insert_id;
    $gagal_awal = db_jumlah_gagal();
    $db->begin_transaction();
    foreach (array(array($rek_debet, 'debet'), array($rek_kredit, 'kredit')) as $j) {
        db_jalankan("insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,{$j[1]},id_admin)
            values(?,?,?,?,'Bukti SYSTEM',?,?,?)", 'sssssss', $unit, $kode_trans, $j[0], (string) $tgl_system, $ket1, $nominal, (string) $userid);
    }
    if (db_jumlah_gagal() !== $gagal_awal) {
        $db->rollback();
        db_jalankan("delete from escrow_deposito where REFF=?", 's', (string) $reff);
        return false;
    }
    $db->commit();
    return true;
};

if (isset($_POST['search'])) {
    if ($kkel) {
        if (empty($norek) || empty($pilih) || empty($nominal)) {
            $skrip[] = "alert('Maaf,.isi Nomor REKENING dan Jenis Transaksi yang benar');";
            $skrip[] = "document.location.href='view_stor.php';";
        } elseif (($pilih === '01' || $pilih === '02') && (!preg_match('/^[0-9]+$/', $nominal) || (int) $nominal <= 0)) {
            $skrip[] = "alert('Maaf,.Nominal harus angka lebih dari 0');";
            $skrip[] = "document.location.href='view_stor.php';";
        } else {
            switch ($pilih) {
                case "01":
                    $ket1 = "Setor Simpanan  an $nama";
                    if ($catat('KREDIT', '1120000', '2102000', $ket1)) {
                        $skrip[] = "alert(" . json_encode("Nominal sudah dikreditkan pada rekening $norek !!!") . ");";
                        $select = $mutasi($ciff);
                    } else {
                        $skrip[] = "alert('Maaf,.Gagal Input');";
                        $skrip[] = "document.location.href='view_stor.php';";
                    }
                    break;
                case "02":
                    $kunci = db_satu("SELECT GET_LOCK('nurinsani_kp_escrow', 15) as ok");
                    if (!$kunci || (int) $kunci['ok'] !== 1) {
                        $skrip[] = "alert('Sistem sedang memproses transaksi lain, coba lagi.');";
                        break;
                    }
                    // saldo rekening yang ditarik (dulu: gabungan semua rekening CIF)
                    $SAL = db_satu("select coalesce(sum(KREDIT)- sum(DEBIT),0) as SALDO  FROM escrow_deposito where  norek =?", 's', $norek);
                    $SALDO = $SAL ? (float) $SAL['SALDO'] : 0.0;
                    if ($SALDO < (int) $nominal) {
                        $skrip[] = "alert(" . json_encode("Maaf,.Saldo Tidak Mencukupi saldo adalah " . number_format($SALDO, 0, '', '')) . ");";
                        $skrip[] = "document.location.href='view_stor.php';";
                    } else {
                        $ket1 = "Penarikan Simpanan  an $nama";
                        if ($catat('DEBIT', '2102000', '1120000', $ket1)) {
                            $skrip[] = "alert(" . json_encode("Nominal sudah didebet pada rekening $norek !!!") . ");";
                            $select = $mutasi($ciff);
                        } else {
                            $skrip[] = "alert('Maaf,.Gagal Input');";
                            $skrip[] = "document.location.href='view_stor.php';";
                        }
                    }
                    db_satu("SELECT RELEASE_LOCK('nurinsani_kp_escrow') as ok");
                    break;
                case "03":
                case "04":
                    $skrip[] = "alert('Maaf,Menu ini belum tersedia');";
                    $skrip[] = "document.location.href='view_stor.php';";
                    break;
                default:
            }
        }
    } else {
        $skrip[] = "alert('Maaf,Nomor Rekening tidak ditemukan');";
        $skrip[] = "document.location.href='view_stor.php';";
    }
}

halaman_awal('Transaksi Setoran dan Penarikan Simpanan Umum');
foreach ($skrip as $s) {
    echo "<script>$s</script>";
}
?>
<div class="card card-ni mb-3">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="view_stor.php" name="postform" class="row g-3" autocomplete="off">
      <div class="col-sm-6">
        <label class="form-label" for="kode">No Rekening</label>
        <div class="autocomplete position-relative">
          <input type="text" name="kode_rekening" id="kode" class="form-control"
                 data-autocomplete="../../ajax/cari_deposito_kp.php" data-isi-nilai="norek" data-isi-label="label"
                 data-isi-target='{"#country":"nama"}'>
        </div>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="country">Nama</label>
        <input type="text" disabled="disabled" name="nama_rekening" id="country" class="form-control">
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="nominal">Nominal</label>
        <input name="nama" type="text" id="nominal" class="form-control" inputmode="numeric" autocomplete="off">
        <div class="form-text" id="format"></div>
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="ket">Keterangan</label>
        <input name="ket" type="text" id="ket" class="form-control">
      </div>
      <div class="col-sm-6">
        <label class="form-label" for="select">Jenis Transaksi</label>
        <select name="select" id="select" class="form-select">
          <option>Pilih Salah satu</option>
          <option value="01">Setor Tunai </option>
          <option value="02">Tarik Tunai </option>
          <option value="03">Setoran Angsuran</option>
          <option value="04">Pemindah Bukuan</option>
        </select>
      </div>
      <?php // versi lama: dua pilihan berikut hanya tampil untuk Pemindah Bukuan (04) ?>
      <div class="col-sm-3 d-none grup-pb">
        <label class="form-label" for="baki">Jenis Pemindah Bukuan</label>
        <select name="baki" id="baki" class="form-select">
          <option value="Debet">Debet</option>
          <option value="Kredit">Kredit </option>
        </select>
      </div>
      <div class="col-sm-3 d-none grup-pb">
        <label class="form-label" for="simpan">Jenis Transaksi PB</label>
        <select name="simpan" id="simpan" class="form-select">
          <option value="tunai">Tunai</option>
          <option value="gl">GL </option>
        </select>
      </div>
      <div class="col-12">
        <button name="search" type="submit" id="submit2" value="Input" class="btn btn-ni">Input</button>
      </div>
    </form>
  </div>
</div>

<div class="card card-ni mw-100">
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-ni align-middle mb-0">
      <thead>
        <tr><th>NOREK</th><th>NAMA</th><th>TGL TRANSAKSI</th><th class="text-end">DEBIT</th><th class="text-end">KREDIT</th><th class="text-end">SALDO</th></tr>
      </thead>
      <tbody>
      <?php foreach ($select as $data): ?>
        <tr>
          <td><?php echo h($data['NOREK']); ?></td>
          <td><?php echo h($data['CUST_SHORT_NAME']); ?></td>
          <td><?php echo h(tgl_indo($data['BUSS_DATE'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['DEBIT'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['KREDIT'])); ?></td>
          <td class="text-end"><?php echo number_format(compat_num($data['balance'])); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
(function () {
    var pilih = document.getElementById('select');
    pilih.addEventListener('change', function () {
        // versi lama: hanya bereaksi pada 01-04 (pilihan kosong tidak mengubah tampilan)
        if (['01', '02', '03', '04'].indexOf(pilih.value) < 0) { return; }
        document.querySelectorAll('.grup-pb').forEach(function (el) {
            el.classList.toggle('d-none', pilih.value !== '04');
        });
    });
    // hanya angka, dan tampilan rupiah di bawah isian (versi lama: wajibAngka + formatCurrency)
    var nominal = document.getElementById('nominal');
    nominal.addEventListener('keypress', function (evt) {
        var kode = evt.which || evt.keyCode;
        if (kode > 31 && (kode < 48 || kode > 57)) {
            evt.preventDefault();
        }
    });
    var terkirim = false;
    document.forms.postform.addEventListener('submit', function (e) {
        if (terkirim) { e.preventDefault(); }
        terkirim = true;
    });
    nominal.addEventListener('keyup', function () {
        var angka = nominal.value.replace(/[^0-9]/g, '') || '0';
        document.getElementById('format').textContent = 'Rp. ' + angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
})();
</script>
<?php
halaman_akhir();
