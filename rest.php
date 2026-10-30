<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Restrukturisasi Data > Restrukturisasi.
 * Hitung pratinjau angsuran baru per jenis restrukturisasi (Pokok / Pokok Margin / Muqosah / Covid19),
 * simpan pilihan ke session, lalu Cetak (cetak_add.php), Cetak Covid (cetak_covid.php), atau Disburse (realisasi_res.php).
 * Perilaku sama dengan versi lama (termasuk keluaran debug var_dump dan berhenti bila data_loan tidak ada).
 * Pembagian memakai compat_bagi(): tenor kosong/0 tidak menghentikan halaman, sama seperti PHP 5.
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
$tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : null;

$kkel = db_satu("select CODE_KEL,CIF,UNIT,CUST_SHORT_NAME,CAO FROM anggota where  norek =?", 's', (string) $norek);
$ciff = $kkel ? $kkel['CIF'] : null;

/** Bulatkan angsuran ke ribuan seperti versi lama (sisa >= 500 dibulatkan biasa, selain itu naik 1000). */
function bulat_ribuan($angsuran)
{
    $totalharga = ceil(compat_num($angsuran));
    if (substr($totalharga, -3) > 499) {
        return round(compat_num($totalharga), -3);
    }
    return round(compat_num($totalharga), -3) + 1000;
}

$pilihan_jenis = array('Pilih Salah satu' => 'Pilih Salah satu', '01' => 'Pokok', '02' => 'Pokok Margin', '03' => 'Muqosah', '04' => 'Covid19');
$pilihan_wadiah = array('Pilih Salah satu' => 'Pilih Salah satu', 'ya' => 'Ya', 'tidak' => 'Tidak');

halaman_awal('Transaksi Restrukturisasi');
?>
<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="rest.php" name="postform" class="form-ni" autocomplete="off">
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
      <div class="row g-2 mb-2">
        <label for="tanggal" class="col-sm-4 col-form-label">Tanggal Murabahah</label>
        <div class="col-sm-8"><input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="<?php echo h($tanggal); ?>"></div>
      </div>
      <div class="row g-2 mb-3" id="baris-nominal">
        <label for="nominal" class="col-sm-4 col-form-label">Nominal Muqosah</label>
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
$baris = array();
if (isset($_POST['search'])) {
    if ($kkel) {
        if (empty($norek) || empty($pilih) || empty($wadiah)) {
            echo "<script>alert('Maaf,isi Nomor REKENING dan Jenis Transaksi yang benar');</script>";
            echo "<script language='javascript'>document.location.href='rest.php'</script>";
        } else {
            $_SESSION['spilih'] = $pilih;
            $_SESSION['swadiah'] = $wadiah;
            $_SESSION['snominal'] = isset($muqosah) ? $muqosah : null; // versi lama: variabel tidak pernah diisi
            $_SESSION['stenor'] = $tenor;
            $_SESSION['stanggal'] = $tanggal;
            // keluaran debug versi lama, dipertahankan
            echo '<pre class="small text-body-secondary mb-2">';
            var_dump($_SESSION['stanggal']);
            var_dump($pilih);
            echo '</pre>';
            $select = db_semua("select unit,CUST_SHORT_NAME,saldo_margin,code_kel,no_anggota,cif,ijaroh,angsuran,pokok,os,angsuran,bulat,tgl_akad,(os-saldo_margin) as saldo_os  from loan  WHERE cif =?", 's', (string) $ciff);
            $row_saldo = db_satu("select sum(kredit-DEBIT) as saldo_simpanan from simpanan where cif =? and type not in('04','05') ", 's', (string) $ciff) or die(mysql_error());
            $saldo_simpanan = $row_saldo['saldo_simpanan'];
            $saldo_simpan = $saldo_simpanan;
            $row_covid_mar = db_satu("select gol from data_loan where cif =? and buss_date ='2020-03-31'", 's', (string) $ciff) or die(mysql_error());
            $covid_mar = $row_covid_mar['gol'];
            $row_covid_feb = db_satu("select gol from data_loan where cif =? and buss_date ='2020-02-28'", 's', (string) $ciff) or die(mysql_error());
            $covid_feb = $row_covid_feb['gol'];

            foreach ($select as $data) {
                $saldo_margin = $data['saldo_margin'];
                $os = $data['os'];
                $pokok_os = $data['saldo_os'];
                $angsuran = $data['angsuran'];
                switch ($pilih) {
                    case "01":
                        $saldo_simpan = $wadiah == 'ya' ? $saldo_simpanan : 0;
                        $os_res = $pokok_os - $saldo_simpan;
                        $pokok = compat_bagi($os_res, $tenor);
                        $angsuran = $pokok;
                        $bulat = bulat_ribuan($angsuran);
                        break;
                    case "02":
                        $saldo_simpan = $wadiah == 'ya' ? $saldo_simpanan : 0;
                        $os_res = $os - $saldo_simpan;
                        $pokok = compat_bagi($os_res, $tenor);
                        $angsuran = $pokok;
                        $bulat = bulat_ribuan($angsuran);
                        break;
                    case "03":
                        break;
                    case "04":
                        if ($wadiah == 'ya' || $wadiah == 'tidak') {
                            $saldo_simpan = $wadiah == 'ya' ? $saldo_simpanan : 0;
                            if ($covid_feb > 1) {
                                $os_res = $wadiah == 'ya' ? $pokok_os - $saldo_simpan : $pokok_os;
                                $pokok = compat_bagi($os_res, $tenor);
                                $saldo_margin = compat_bagi($saldo_margin, $tenor);
                                $angsuran = $pokok + $saldo_margin;
                                $bulat = bulat_ribuan($angsuran);
                            } else {
                                $os_res = $os;
                                $bulat = $angsuran;
                            }
                        }
                        break;
                    default:
                }
                $baris[] = array(
                    'nama' => $data['CUST_SHORT_NAME'],
                    'tgl_akad' => $data['tgl_akad'],
                    'saldo_os' => $data['saldo_os'],
                    'saldo_margin' => $data['saldo_margin'],
                    'angsuran' => $data['angsuran'],
                    'bulat' => isset($bulat) ? $bulat : null,
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
        <th class="text-center">Cetak Covid</th>
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
        <td class="text-end"><?php echo number_format(compat_num($saldo_simpanan)); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b['angsuran'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($b['bulat'])); ?></td>
        <td class="text-center"><a href="cetak_add.php?cif=<?php echo urlencode($b['cif']); ?>" class="btn btn-ni btn-sm" data-konfirmasi="Apakah Anda yakin akan melakukan Cetak dokumen ?">Cetak</a></td>
        <td class="text-center"><a href="cetak_covid.php?cif=<?php echo urlencode($b['cif']); ?>" class="btn btn-outline-secondary btn-sm" data-konfirmasi="Apakah Anda yakin akan melakukan Cetak dokumen ?">Covid</a></td>
        <td class="text-center"><a href="realisasi_res.php?cif=<?php echo urlencode($b['cif']); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin?">Disburse</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script>
(function () {
    // Nominal Muqosah: disembunyikan untuk jenis 01-03, tampil untuk 04 (seperti versi lama)
    var pilih = document.getElementById('pilih');
    var baris = document.getElementById('baris-nominal');
    pilih.addEventListener('change', function () {
        if (pilih.value === '04') {
            baris.hidden = false;
        } else if (['01', '02', '03'].indexOf(pilih.value) > -1) {
            baris.hidden = true;
        }
    });
    // tampilan rupiah di samping isian nominal (formatCurrency versi lama)
    var nominal = document.getElementById('nominal');
    var format = document.getElementById('format');
    function tampilRupiah() {
        var angka = nominal.value.replace(/[^0-9]/g, '');
        format.textContent = 'Rp. ' + (angka === '' ? '0' : Number(angka).toLocaleString('de-DE'));
    }
    nominal.addEventListener('keyup', tampilRupiah);
})();
</script>
<?php
halaman_akhir();
