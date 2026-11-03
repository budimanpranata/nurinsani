<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Form approve pengajuan (level lead), dibuka dari Approval Pengajuan (data_approval.php) dengan ?cif=...
 * Menampilkan data anggota dan calon_loan; tombol Approve mengirim ke update_cim_lead.php.
 * Nama isian dan nilai awal sama dengan versi lama, termasuk keanehannya (PEKERJAAN_TERTUNDA.md bagian 38):
 *   - NPWP selalu terisi "0" (atribut value ganda di versi lama, yang pertama "0"), sehingga approve menimpa NPWP jadi 0
 *   - pilihan (jenis kelamin, pendidikan, agama, dll.) tidak mengikuti data tersimpan
 * Perbaikan 2026-11-03 (laporan user): Tempat Lahir dari TEMPAT_LAHIR (dulu CITIZENSHIP = kode negara), Pekerjaan
 * Pasangan dari pekerjaan_pasangan (dulu kode unit), Bidang Usaha memilih kode tersimpan di calon_loan.usaha.
 * Handler JS yang tidak ada (suggest, fill2, startCalc, stopCalc) dibuang; validasi isian wajib sama dengan versi lama.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
ob_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$cif = isset($_GET['cif']) ? (string) $_GET['cif'] : '';

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

$a = db_satu("select * from anggota where cif =?", 's', $cif);
$r = db_satu("select * from calon_loan where cif =?", 's', $cif);
$ag = function ($k) use ($a) {
    return $a && isset($a[$k]) ? (string) $a[$k] : '';
};
$cl = function ($k) use ($r) {
    return $r && isset($r[$k]) ? (string) $r[$k] : '';
};
$bidang_usaha = db_semua("select * from bidang_usaha ");

/** Satu isian teks dalam grid form. */
function isian_lead($label, $nama, $nilai, $atribut = '', $wajib = true)
{
    ?>
    <div class="col-md-6 col-lg-3">
      <label class="form-label small mb-1" for="<?php echo h($nama); ?>"><?php echo h($label); ?><?php echo $wajib ? ' <span class="text-danger">*</span>' : ''; ?></label>
      <input type="text" class="form-control form-control-sm" name="<?php echo h($nama); ?>" id="<?php echo h($nama); ?>" value="<?php echo h($nilai); ?>" <?php echo $atribut; ?>>
    </div>
    <?php
}

/** Satu pilihan dalam grid form; $opsi = array(array(value|null, teks, terpilih)). value null = tanpa atribut value. */
function pilihan_lead($label, $nama, $opsi)
{
    ?>
    <div class="col-md-6 col-lg-3">
      <label class="form-label small mb-1" for="<?php echo h($nama); ?>"><?php echo h($label); ?> <span class="text-danger">*</span></label>
      <select class="form-select form-select-sm" name="<?php echo h($nama); ?>" id="<?php echo h($nama); ?>">
        <?php foreach ($opsi as $o): ?>
        <option<?php echo $o[0] === null ? '' : ' value="' . h($o[0]) . '"'; ?><?php echo !empty($o[2]) ? ' selected' : ''; ?>><?php echo h($o[1]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php
}

$besar = 'data-huruf-besar';
$angka = 'data-wajib-angka';
$urutan = array('satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh');
// bidang usaha tersimpan di pengajuan terpilih; kode yang belum ada di tabel bidang_usaha tetap ditampilkan
$usaha_tersimpan = $cl('usaha');
$opsi_usaha = array(array(null, 'Pilih Salah satu', $usaha_tersimpan === ''));
$ada_usaha = false;
foreach ($bidang_usaha as $u) {
    $nilai_u = array_values($u);
    $pilih_ini = $usaha_tersimpan !== '' && (string) $nilai_u[0] === $usaha_tersimpan;
    $ada_usaha = $ada_usaha || $pilih_ini;
    $opsi_usaha[] = array((string) $nilai_u[0], (string) $nilai_u[1], $pilih_ini);
}
if ($usaha_tersimpan !== '' && !$ada_usaha) {
    $opsi_usaha[] = array($usaha_tersimpan, $usaha_tersimpan . ' (kode belum terdaftar)', true);
}

halaman_awal('Form Approve Calon Anggota');
?>
<form action="update_cim_lead.php" method="post" name="postform" id="form-approve" autocomplete="off">
  <div class="card card-ni mw-100 mb-3">
    <div class="card-header">Informasi Anggota</div>
    <div class="card-body row g-2">
      <?php isian_lead('Kode Unit', 'branch', $ag('UNIT'), 'maxlength="6"'); ?>
      <?php isian_lead('Kode Kelompok', 'code_kel', $ag('CODE_KEL'), $besar . ' maxlength="20"'); ?>
      <?php isian_lead('Cif', 'cif', $cif); ?>
      <?php isian_lead('Cif Simpanan', 'ciff', $cif, '', false); ?>
      <?php isian_lead('Kode AO', 'code_ao', $ag('CAO')); ?>
      <?php isian_lead('Nomor KTP', 'id', $ag('ID_NUMBER'), $angka . ' maxlength="16"'); ?>
      <?php pilihan_lead('Kewarganegaraan', 'negara', array(array('', 'INDONESIA'))); ?>
      <?php isian_lead('Nama', 'nama', $ag('CUST_SHORT_NAME'), $besar . ' maxlength="45"'); ?>
      <?php pilihan_lead('Pendidikan', 'pendidikan', array(array(null, 'Pilih Salah satu'), array('SMA', 'SMA', true), array('SMP', 'SMP'),
          array('SD', 'SD'), array('D1', 'D1'), array('D3', 'D3'), array('S1', 'S1'))); ?>
      <?php isian_lead('Tempat Lahir', 'tempat_lahir', $ag('TEMPAT_LAHIR'), $besar . ' maxlength="14"'); ?>
      <?php isian_lead('Nama Pasangan/Penjamin', 'wali', $ag('WARIS'), $besar); ?>
      <?php isian_lead('Tgl Lahir Sesuai KTP', 'tgl_lahir', $ag('BIRTH_DATE'), 'placeholder="YYYY-MM-DD"'); ?>
      <?php isian_lead('No Tlp', 'no_tlp', $ag('MOBILE_PHONE')); ?>
      <?php pilihan_lead('Jenis Kelamin', 'jenis_kel', array(array('Perempuan', 'Perempuan'), array('Laki-laki', 'Laki-laki'))); ?>
      <?php isian_lead('No Tlp Penjamin', 'tlp_penjamin', $ag('HOME_PHONE')); ?>
      <?php isian_lead('Alamat Sesuai KTP', 'alamat', $ag('HADDRESS1'), $besar . ' maxlength="50"'); ?>
      <?php // versi lama: value="0" muncul lebih dulu dari value NPWP, sehingga isian selalu "0" ?>
      <?php isian_lead('NPWP', 'npwp', '0', 'maxlength="20"'); ?>
      <?php isian_lead('RT/RW', 'rt', $ag('HADDRESS5')); ?>
      <?php isian_lead('Nama Ibu Kandung', 'ibu', $ag('MOTHER_NAME'), $besar); ?>
      <?php isian_lead('Kode Pos', 'kd_pos', $ag('HPOST_CODE')); ?>
      <?php isian_lead('Tanggal Wakalah', 'tgl_skep', $cl('tgl_wakalah'), 'placeholder="YYYY-MM-DD"'); ?>
      <?php isian_lead('Kelurahan', 'lurah', $ag('HADDRESS2'), $besar); ?>
      <?php isian_lead('Tgl Murabahah', 'tgl_akad', $cl('tgl_akad'), 'placeholder="YYYY-MM-DD"'); ?>
      <?php isian_lead('Kecamatan', 'kec', $ag('HADDRESS3'), $besar); ?>
      <?php pilihan_lead('Jenis Produk Pembiayaan', 'deal_type', array(array('1', 'Minggu'), array('2', 'Hari'), array('3', 'Bulan'))); ?>
      <?php isian_lead('Kabupaten/Kota', 'kota', $ag('HADDRESS4'), $besar); ?>
      <?php pilihan_lead('Bidang Usaha', 'usaha', $opsi_usaha); ?>
      <?php isian_lead('Nama Usaha', 'nama_usaha', $cl('nama_usaha'), '', false); ?>
      <?php pilihan_lead('Status Perkawinan', 'status_kawin', array(array('kawin', 'KAWIN'), array('belum_kawin', 'BELUM KAWIN'), array('janda', 'JANDA'))); ?>
      <?php pilihan_lead('Sumber Dana', 'sumber', array(array('1', 'NI'), array('2', 'Pihak Ketiga'), array('3', 'Bank Victoria'), array('4', 'BSM'),
          array('5', 'BAV'), array('6', 'BJB'), array('7', 'BRI'), array('8', 'LPDB'))); ?>
      <?php isian_lead('Pekerjaan Pasangan', 'pekerjaan', $ag('pekerjaan_pasangan'), $besar . ' maxlength="75"'); ?>
      <?php isian_lead('Golongan Pemilik', 'gol_pe', '875', 'maxlength="20"'); ?>
      <?php pilihan_lead('Agama', 'agama', array(array('ISLAM', 'ISLAM'), array('BUDHA', 'BUDHA'))); ?>
      <?php isian_lead('Golongan Debitur', 'gol_deb', '874'); ?>
      <?php isian_lead('ID Expire', 'id_expire', $ag('ID_EXPIRED')); ?>
      <?php isian_lead('Keterkaitan', 'ket', '2'); ?>
    </div>
  </div>

  <div class="card card-ni mw-100 mb-3">
    <div class="card-header">Informasi Pengajuan Pembiayaan</div>
    <div class="card-body row g-2">
      <?php foreach ($urutan as $i => $u): ?>
      <div class="col-md-6">
        <div class="row g-2">
          <div class="col-7">
            <label class="form-label small mb-1" for="barang_<?php echo $u; ?>">Barang <?php echo $i + 1; ?></label>
            <input type="text" class="form-control form-control-sm" name="barang_<?php echo $u; ?>" id="barang_<?php echo $u; ?>" <?php echo $besar; ?>>
          </div>
          <div class="col-5">
            <label class="form-label small mb-1" for="harga_<?php echo $u; ?>">Harga</label>
            <input type="text" class="form-control form-control-sm" name="harga_<?php echo $u; ?>" id="harga_<?php echo $u; ?>" value="0" <?php echo $angka; ?>>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card card-ni mw-100 mb-3">
    <div class="card-header">Informasi Pembiayaan dan Simpanan Anggota</div>
    <div class="card-body row g-2">
      <?php isian_lead('No Rekening', 'no_anggota', $cl('no_anggota'), '', false); ?>
      <?php isian_lead('PB', 'ke', $cl('ke'), 'maxlength="2"', false); ?>
      <?php isian_lead('Sisa Pembiayaan', 'simpanan', $cl('os'), '', false); ?>
      <?php isian_lead('Pengajuan', 'num', $cl('Plafond'), $angka . ' data-format-rupiah maxlength="40"', false); ?>
      <?php isian_lead('Sisa Margin', 'angsuran', $cl('saldo_margin'), '', false); ?>
      <?php isian_lead('Tenor', 'tenor', $cl('Tenor'), '', false); ?>
      <?php isian_lead('Angsuran', 'tot_margin', $cl('angsuran'), '', false); ?>
      <div class="col-md-6 col-lg-3">
        <label class="form-label small mb-1" for="pla">Disetujui UM/GFH</label>
        <input type="text" class="form-control form-control-sm fw-bold" name="pla" id="pla" value="<?php echo h($cl('Plafond')); ?>" maxlength="8" <?php echo $angka; ?> data-format-rupiah>
        <div class="form-text" id="format"></div>
      </div>
      <?php isian_lead('Harga Jual', 'harga_jual', $cl('Plafond'), '', false); ?>
      <?php isian_lead('Margin', 'rate', $cl('bagi_hasil'), '', false); ?>
      <?php isian_lead('Setoran', 'setoran', $cl('bulat'), '', false); ?>
    </div>
    <div class="card-footer text-end">
      <button type="button" class="btn btn-ni" id="tombol-approve" name="simpan" value="Approve">Approve</button>
    </div>
  </div>
</form>
<?php
halaman_akhir(array('assets/app/approval_form.js'));
