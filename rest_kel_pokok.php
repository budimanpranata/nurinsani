<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Restrukturisasi Data > Restrukturisasi By Kel.
 * Pilih kelompok, jenis restrukturisasi, simpanan dan tenor baru; pilihan disimpan ke session,
 * lalu Disburse per anggota di rest_pokok_list.php. Perilaku sama dengan versi lama.
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

$norek = isset($_POST['kode_rekening']) ? $_POST['kode_rekening'] : null;
$wadiah = isset($_POST['wadiah']) ? $_POST['wadiah'] : null;
$tenor = isset($_POST['tenor']) ? $_POST['tenor'] : null;
$pilih = isset($_POST['pilih']) ? $_POST['pilih'] : null;
$nominal = isset($_POST['nominal']) ? $_POST['nominal'] : null;

$pilihan_jenis = array('Pilih Salah satu' => 'Pilih Salah satu', '01' => 'Pokok', '02' => 'Pokok Margin', '04' => 'Muqosah');
$pilihan_wadiah = array('Pilih Salah satu' => 'Pilih Salah satu', 'ya' => 'Ya', 'tidak' => 'Tidak');

halaman_awal('Transaksi Restrukturisasi Kelompok');
?>
<div class="card card-ni">
  <div class="card-header">Input Data</div>
  <div class="card-body">
    <form method="post" action="rest_kel_pokok.php" name="postform" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="kode" class="col-sm-4 col-form-label">Kode Kelompok</label>
        <div class="col-sm-4 autocomplete">
          <input type="text" name="kode_rekening" id="kode" class="form-control form-control-sm" value="<?php echo h($norek); ?>"
                 placeholder="kode / nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel" data-isi-label="code_kel,nama_kel"
                 data-isi-target='{"#country":"nama_kel"}'>
        </div>
        <div class="col-sm-4"><input type="text" id="country" class="form-control form-control-sm" disabled></div>
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
// versi lama: count() tanpa group by selalu menghasilkan satu baris, jadi pemeriksaan ini selalu lolos
$kel = db_semua("select count(loan.cif) as noa, loan.CODE_KEL, nama_kel, loan.CAO  FROM loan
  left join kelompok on kelompok.code_kel = loan.code_kel
  where  loan.code_kel=? ", 's', (string) $norek);
if (isset($_POST['search'])) {
    if ($tenor > 50) {
        // versi lama tidak berhenti di sini
        echo "<script>alert('Maaf, tenor > 50');</script>";
        echo "<script language='javascript'>document.location.href='rest_kel_pokok.php'</script>";
    }
    if (count($kel)) {
        if (empty($norek) || empty($pilih) || empty($wadiah) || empty($tenor)) {
            echo "<script>alert('Maaf,Kode kelompok , jenis restruturisasi dan tenor baru tidak boleh kosong');</script>";
            echo "<script language='javascript'>document.location.href='rest_kel_pokok.php'</script>";
        } else {
            $_SESSION['spilih'] = $pilih;
            $_SESSION['swadiah'] = $wadiah;
            $_SESSION['snominal'] = isset($muqosah) ? $muqosah : null; // versi lama: variabel tidak pernah diisi
            $_SESSION['stenor'] = $tenor;
            $baris = db_semua("select count(loan.cif) as noa, tenor, loan.CODE_KEL, nama_kel, loan.CAO as ao, sum(os) as pembiayaan FROM loan
 left join kelompok on kelompok.code_kel = loan.code_kel
 where  loan.code_kel=? and os > 0 group by loan.code_kel", 's', (string) $norek);
        }
    }
}
?>
<div class="table-responsive">
  <table class="table table-sm table-bordered table-ni align-middle">
    <thead>
      <tr>
        <th>Kode Kelompok</th>
        <th>Kode AO</th>
        <th>Nama</th>
        <th class="text-end">Noa</th>
        <th class="text-end">Pembiayaan</th>
        <th class="text-end">Tenor Lama</th>
        <th class="text-end">Tenor Baru</th>
        <th class="text-center">Disburse</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($baris as $data): ?>
      <tr>
        <td><?php echo h($norek); ?></td>
        <td><?php echo h($data['ao']); ?></td>
        <td><?php echo h($data['nama_kel']); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['noa'])); ?></td>
        <td class="text-end"><?php echo number_format(compat_num($data['pembiayaan'])); ?></td>
        <td class="text-end"><?php echo h($data['tenor']); ?></td>
        <td class="text-end"><?php echo h($tenor); ?></td>
        <td class="text-center"><a href="rest_pokok_list.php?kode_kel=<?php echo urlencode($data['CODE_KEL']); ?>" class="btn btn-outline-danger btn-sm" data-konfirmasi="Apakah Anda yakin?">Disburse</a></td>
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
