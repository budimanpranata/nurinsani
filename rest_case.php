<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Restrukturisasi Data > Rest Special Case.
 * Hitung pratinjau angsuran baru dari Plafond Baru ÷ tenor, simpan pilihan ke session,
 * lalu Cetak (cetak_add_case.php) atau Disburse (realisasi_res_case.php).
 * Perilaku sama dengan versi lama, termasuk keluaran debug var_dump($ciff), cek simpanan 'Ya' (huruf besar)
 * yang tidak pernah cocok, dan $param_margin/$param_tabung yang tidak pernah diisi.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$tgl_system = isset($_SESSION['stgl_system']) ? $_SESSION['stgl_system'] : null;
//cek tanggal sistem
if (!isset($_SESSION['stgl_system'])) {
    echo "<script>alert('Maaf,.tanggal system belum di setup');</script>";
    header("location:param_tgl.php");
    exit;
}
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "unit") {
    header("location:index.html");
    exit;
}
include "function.php";

$norek = isset($_POST['kode_rekening']) ? $_POST['kode_rekening'] : null;
$wadiah = isset($_POST['wadiah']) ? $_POST['wadiah'] : null;
$tenor = isset($_POST['tenor']) ? $_POST['tenor'] : null;
$pilih = isset($_POST['pilih']) ? $_POST['pilih'] : null;
$nominal = isset($_POST['nominal']) ? $_POST['nominal'] : null;

$kel = db_semua("select CODE_KEL,CIF,UNIT,CUST_SHORT_NAME,CAO FROM loan where no_anggota =?", 's', (string) $norek);
$kkel = $kel ? $kel[0] : null;
$ciff = $kkel ? $kkel['CIF'] : null;

$pilihan_jenis = array('Pilih Salah satu' => 'Pilih Salah satu', '01' => 'Pokok', '02' => 'Pokok Margin', '04' => 'Muqosah', '05' => 'Pokok dan Angsuran');
$pilihan_wadiah = array('Pilih Salah satu' => 'Pilih Salah satu', 'ya' => 'Ya', 'tidak' => 'Tidak');

halaman_awal('Transaksi Restrukturisasi Special Case');
?>
<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="rest_case.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">No Rekening</label>
        <div class="col-sm-4 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" value="<?php echo h($norek); ?>"
                 placeholder="CIF / nama"
                 data-autocomplete="ajax/cari_rekening.php" data-isi-nilai="norek" data-isi-label="norek,nama,unit,nama_kel"
                 data-isi-target='{"#country":"nama"}'>
        </div>
        <div class="col-sm-4"><input type="text" id="country" class="form-control form-control-sm" disabled value="<?php echo h($kkel ? $kkel['CUST_SHORT_NAME'] : ''); ?>"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="pilih" class="col-sm-4 col-form-label">Jenis Restrukturisasi</label>
        <div class="col-sm-8">
          <select name="pilih" id="pilih" class="form-select form-select-sm">
            <?php foreach ($pilihan_jenis as $nilai => $label): ?>
            <option value="<?php echo h($nilai); ?>"<?php echo (string) $pilih === (string) $nilai ? ' selected' : ''; ?>><?php echo h($label); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="wadiah" class="col-sm-4 col-form-label">Dari Simpanan</label>
        <div class="col-sm-8">
          <select name="wadiah" id="wadiah" class="form-select form-select-sm">
            <?php foreach ($pilihan_wadiah as $nilai => $label): ?>
            <option value="<?php echo h($nilai); ?>"<?php echo (string) $wadiah === (string) $nilai ? ' selected' : ''; ?>><?php echo h($label); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="tenor" class="col-sm-4 col-form-label">Tenor</label>
        <div class="col-sm-8"><input type="text" name="tenor" id="tenor" class="form-control form-control-sm" inputmode="numeric" data-hanya-angka value="<?php echo h($tenor); ?>"></div>
      </div>
      <div class="row g-2 mb-3" id="baris-nominal">
        <label for="nominal" class="col-sm-4 col-form-label">Plafond Baru</label>
        <div class="col-sm-8">
          <div class="input-group input-group-sm">
            <input type="text" name="nominal" id="nominal" class="form-control" inputmode="numeric" data-hanya-angka value="<?php echo h($nominal); ?>">
            <span class="input-group-text" id="format"></span>
          </div>
        </div>
      </div>
      <div class="text-end">
        <button type="submit" name="search" value="Cari" class="btn btn-ni btn-sm px-4">Cari</button>
      </div>
    </form>
  </div>
</div>
<?php
// keluaran debug versi lama (selalu tampil), dipertahankan
echo '<pre class="small text-body-secondary mb-2">';
var_dump($ciff);
echo '</pre>';

$baris = array();
if (isset($_POST['search'])) {
    if (count($kel)) {
        if (empty($norek) || empty($pilih) || empty($wadiah)) {
            echo "<script>alert('Maaf,isi Nomor REKENING dan Jenis Transaksi yang benar');</script>";
            echo "<script language='javascript'>document.location.href='rest_case.php'</script>";
        } else {
            $_SESSION['spilih'] = $pilih;
            $_SESSION['swadiah'] = $wadiah;
            $_SESSION['snominal'] = $nominal;
            $_SESSION['stenor'] = $tenor;
            $select = db_semua("select unit,CUST_SHORT_NAME,saldo_margin,code_kel,no_anggota,cif,ijaroh,angsuran,pokok,os,angsuran,bulat,tgl_akad,(os-saldo_margin) as saldo_os  from loan  WHERE cif =?", 's', (string) $ciff);
            $row_saldo = db_satu("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif =? and type not in('04','05') ", 's', (string) $ciff) or die(mysql_error());
            $saldo_simpanan = $row_saldo['saldo_simpanan'];

            foreach ($select as $data) {
                // versi lama membandingkan dengan 'Ya' (huruf besar) sehingga cabang ini tidak pernah terpakai; kedua cabang menghitung sama
                $saldo_simpanan = $wadiah == 'Ya' ? $saldo_simpanan : 0;
                $param_margin = isset($param_margin) ? $param_margin : null; // tidak pernah diisi di versi lama
                $param_tabung = isset($param_tabung) ? $param_tabung : null;
                $pokok = compat_bagi($nominal, $tenor);
                $margin = compat_num($nominal) * (compat_num($param_margin) / 100);
                $ijaroh = compat_bagi($margin, $tenor);
                $angsuran = $ijaroh + $pokok;
                $baris[] = array(
                    'nama' => $data['CUST_SHORT_NAME'],
                    'tgl_akad' => $data['tgl_akad'],
                    'saldo_os' => $data['saldo_os'],
                    'saldo_margin' => $data['saldo_margin'],
                    'simpanan' => $saldo_simpanan,
                    'angsuran' => $data['angsuran'],
                    'angsuran_baru' => $angsuran,
                    'cif' => $data['cif'],
                );
            }
        }
    }
}
?>
<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Norek</th>
        <th>Nama</th>
        <th>Tgl Akad</th>
        <th class="text-end">Saldo Pokok</th>
        <th class="text-end">Saldo Margin</th>
        <th class="text-end">Simpanan</th>
        <th class="text-end">Angsuran</th>
        <th class="text-end">Angsuran Baru</th>
        <th class="text-center">Cetak</th>
        <th class="text-center">Disburse</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($baris as $b): ?>
      <tr>
        <td><?php echo h($norek); ?></td>
        <td><?php echo h($b['nama']); ?></td>
        <td><?php echo h(tgl_indo($b['tgl_akad'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b['saldo_os'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b['saldo_margin'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b['simpanan'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b['angsuran'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b['angsuran_baru'])); ?></td>
        <td class="text-center"><a href="cetak_add_case.php?cif=<?php echo urlencode($b['cif']); ?>" class="btn btn-ni btn-sm" data-konfirmasi="Apakah Anda yakin akan melakukan Cetak dokumen ?">Cetak</a></td>
        <td class="text-center"><a href="realisasi_res_case.php?cif=<?php echo urlencode($b['cif']); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin?">Disburse</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script>
(function () {
    // Plafond Baru: disembunyikan untuk jenis 01-03, tampil untuk 04 (seperti versi lama)
    var pilih = document.getElementById('pilih');
    var baris = document.getElementById('baris-nominal');
    pilih.addEventListener('change', function () {
        if (pilih.value === '04') {
            baris.hidden = false;
        } else if (['01', '02', '03'].indexOf(pilih.value) > -1) {
            baris.hidden = true;
        }
    });
    var nominal = document.getElementById('nominal');
    var format = document.getElementById('format');
    nominal.addEventListener('keyup', function () {
        var angka = nominal.value.replace(/[^0-9]/g, '');
        format.textContent = 'Rp. ' + (angka === '' ? '0' : Number(angka).toLocaleString('de-DE'));
    });
})();
</script>
<?php
halaman_akhir();
