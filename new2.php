<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Entry Data: form calon anggota + pengajuan pembiayaan, disimpan oleh inputlog.php.
 */
require_once __DIR__ . '/inc/tampilan.php';
include __DIR__ . '/function.php';
session_start();

if (!isset($_SESSION['userid'])) {
    header('location:index.html');
    exit;
}
if (empty($_SESSION['csrf_entry'])) {
    $_SESSION['csrf_entry'] = bin2hex(random_bytes(16));
}

$db = db_ni();
$st = $db->prepare('SELECT kode_branch FROM user WHERE userid = ?');
$st->bind_param('s', $_SESSION['userid']);
$st->execute();
$user = $st->get_result()->fetch_assoc();
$branch = $user ? $user['kode_branch'] : '';

// CIF acak 6 karakter dan no rekening = unit + CIF + 01 (sama seperti versi lama)
$cif = acakangkahuruf(6);
$anggota = $branch . $cif . '01';

$bidang_usaha = $db->query('SELECT code_bidang, nama_usaha FROM bidang_usaha')->fetch_all(MYSQLI_NUM);

// pesan dari inputlog.php dan isian sebelumnya bila penyimpanan ditolak
$pesan = isset($_SESSION['pesan_entry']) ? $_SESSION['pesan_entry'] : null;
$lama = isset($_SESSION['form_entry']) ? $_SESSION['form_entry'] : array();
unset($_SESSION['pesan_entry'], $_SESSION['form_entry']);

$awal = array(
    'branch' => $branch, 'code_kel' => isset($_SESSION['code_kel']) ? $_SESSION['code_kel'] : '',
    'cif' => $cif, 'code_ao' => isset($_SESSION['cao']) ? $_SESSION['cao'] : '',
    'tgl_skep' => isset($_SESSION['tgl_wakalah']) ? $_SESSION['tgl_wakalah'] : '',
    'tgl_akad' => isset($_SESSION['stgl_akad']) ? $_SESSION['stgl_akad'] : '',
    'no_tlp' => '0', 'tlp_penjamin' => '0', 'npwp' => '0', 'pekerjaan' => '0', 'gol_pe' => '875', 'gol_deb' => '874',
    'id_expire' => '1900:00:00', 'ket' => '2', 'pendidikan' => 'SMA', 'no_anggota' => $anggota,
);

/** Nilai isian: isian sebelumnya (bila ditolak) atau nilai awal. */
function nilai($nama)
{
    global $lama, $awal;
    if (isset($lama[$nama])) return $lama[$nama];
    return isset($awal[$nama]) ? $awal[$nama] : '';
}

/** Satu baris label + input. $attr: atribut tambahan HTML, $tipe: type input, $kelas: kelas tambahan. */
function isian($label, $nama, $attr = '', $wajib = true, $tipe = 'text', $kelas = '')
{
    ?>
    <div class="row g-2 mb-1">
      <label for="<?php echo h($nama); ?>" class="col-sm-5 col-form-label col-form-label-sm"><?php echo h($label); ?><?php echo $wajib ? ' <span class="text-danger">*</span>' : ''; ?></label>
      <div class="col-sm-7">
        <input type="<?php echo h($tipe); ?>" name="<?php echo h($nama); ?>" id="<?php echo h($nama); ?>"
               class="form-control form-control-sm <?php echo h($kelas); ?>" value="<?php echo h(nilai($nama)); ?>" <?php echo $attr; ?>>
      </div>
    </div>
    <?php
}

/** Satu baris label + select. $pilihan: array value => teks. */
function pilihan($label, $nama, $pilihan, $attr = '')
{
    $terpilih = nilai($nama);
    ?>
    <div class="row g-2 mb-1">
      <label for="<?php echo h($nama); ?>" class="col-sm-5 col-form-label col-form-label-sm"><?php echo h($label); ?> <span class="text-danger">*</span></label>
      <div class="col-sm-7">
        <select name="<?php echo h($nama); ?>" id="<?php echo h($nama); ?>" class="form-select form-select-sm" <?php echo $attr; ?>>
          <?php foreach ($pilihan as $v => $t): ?>
            <option value="<?php echo h($v); ?>" <?php echo (string) $terpilih === (string) $v ? 'selected' : ''; ?>><?php echo h($t); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <?php
}

// isian yang diisi otomatis saat memilih saran CIF (id isian => kunci data di ajax/cari_anggota.php)
$isi_cif = array();
$urutan = array('satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh');
foreach (array('nama', 'no_anggota', 'branch', 'ibu', 'alamat', 'lurah', 'kec', 'kota', 'rt', 'code_kel', 'wali', 'no_tlp',
    'tgl_lahir', 'tempat_lahir', 'pendidikan', 'npwp', 'kd_pos', 'status_kawin', 'id_expire', 'id', 'sumber', 'jenis_kel',
    'code_ao', 'tgl_skep', 'pla', 'usaha') as $k) {
    $isi_cif['#' . $k] = $k;
}
foreach ($urutan as $u) {
    $isi_cif['#barang_' . $u] = 'barang_' . $u;
    $isi_cif['#harga_' . $u] = 'harga_' . $u;
}

halaman_awal('Entry Calon Anggota');
?>
<?php if ($pesan): ?>
  <div class="alert alert-<?php echo h($pesan[0]); ?> alert-dismissible fade show py-2" role="alert">
    <?php echo nl2br(h($pesan[1])); ?>
    <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
<?php endif; ?>
<div class="alert alert-danger py-2 d-none" id="pesan-validasi" role="alert"></div>

<form action="inputlog.php" method="post" name="postform" id="form-entry" class="form-ni" novalidate autocomplete="off">
  <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_entry']); ?>">

  <div class="card mb-3">
    <div class="card-header judul-bagian">Data Calon Anggota</div>
    <div class="card-body py-2">
      <div class="row gx-4">
        <div class="col-lg-6">
          <?php isian('Kode Unit', 'branch', 'maxlength="6"', true, 'text', 'fw-bold'); ?>
          <div class="row g-2 mb-1">
            <label for="cif" class="col-sm-5 col-form-label col-form-label-sm">CIF <span class="text-danger">*</span></label>
            <div class="col-sm-7 autocomplete">
              <input type="text" name="cif" id="cif" class="form-control form-control-sm" value="<?php echo h(nilai('cif')); ?>"
                     placeholder="ketik CIF / nama anggota lama"
                     data-autocomplete="ajax/cari_anggota.php" data-isi-nilai="cif" data-isi-label="label"
                     data-isi-target='<?php echo h(json_encode($isi_cif)); ?>'>
            </div>
          </div>
          <?php isian('Nomor KTP', 'id', 'maxlength="16" inputmode="numeric" data-hanya-angka autofocus'); ?>
          <?php isian('Nama', 'nama', 'maxlength="45" data-huruf-besar'); ?>
          <?php isian('Tempat Lahir', 'tempat_lahir', 'maxlength="14" data-huruf-besar'); ?>
          <?php isian('Tgl Lahir Sesuai KTP', 'tgl_lahir', '', true, 'date'); ?>
          <?php pilihan('Jenis Kelamin', 'jenis_kel', array('Perempuan' => 'Perempuan', 'Laki-laki' => 'Laki-laki')); ?>
          <?php isian('Alamat Sesuai KTP', 'alamat', 'maxlength="50" data-huruf-besar'); ?>
          <?php isian('RT/RW', 'rt'); ?>
          <?php isian('Kode Pos', 'kd_pos'); ?>
          <?php isian('Kelurahan', 'lurah', 'data-huruf-besar'); ?>
          <?php isian('Kecamatan', 'kec', 'data-huruf-besar'); ?>
          <?php isian('Kabupaten/Kota', 'kota', 'data-huruf-besar'); ?>
          <?php pilihan('Status Perkawinan', 'status_kawin', array('kawin' => 'KAWIN', 'belum_kawin' => 'BELUM KAWIN', 'janda' => 'JANDA')); ?>
          <?php isian('Pekerjaan Pasangan', 'pekerjaan', 'maxlength="75" data-huruf-besar'); ?>
          <?php pilihan('Agama', 'agama', array('ISLAM' => 'ISLAM', 'BUDHA' => 'BUDHA', 'KRISTEN' => 'KRISTEN')); ?>
          <?php isian('ID Expire', 'id_expire'); ?>
        </div>
        <div class="col-lg-6">
          <?php isian('Kode Kelompok', 'code_kel', 'maxlength="20" data-huruf-besar', true, 'text', 'fw-bold'); ?>
          <?php isian('Kode AO', 'code_ao', '', false, 'text', 'fw-bold'); ?>
          <?php pilihan('Kewarganegaraan', 'negara', array('ID' => 'INDONESIA')); ?>
          <?php pilihan('Pendidikan', 'pendidikan', array('' => 'Pilih Salah satu', 'SMA' => 'SMA', 'SMP' => 'SMP', 'SD' => 'SD', 'D1' => 'D1', 'D3' => 'D3', 'S1' => 'S1')); ?>
          <?php isian('Nama Pasangan/Penjamin', 'wali', 'data-huruf-besar'); ?>
          <?php isian('No Tlp', 'no_tlp'); ?>
          <?php isian('No Tlp Penjamin', 'tlp_penjamin'); ?>
          <?php isian('NPWP', 'npwp', 'maxlength="20"'); ?>
          <?php isian('Nama Ibu Kandung', 'ibu', 'data-huruf-besar'); ?>
          <?php isian('Tanggal Wakalah', 'tgl_skep', '', true, 'date'); ?>
          <?php isian('Tgl Akad Murabahah', 'tgl_akad', '', true, 'date'); ?>
          <?php pilihan('Jenis Produk Pembiayaan', 'deal_type', array('1' => 'Minggu', '2' => 'Hari', '3' => 'Bulan')); ?>
          <div class="row g-2 mb-1">
            <label for="usaha" class="col-sm-5 col-form-label col-form-label-sm">Bidang Usaha <span class="text-danger">*</span></label>
            <div class="col-sm-7 d-flex gap-1">
              <select name="usaha" id="usaha" class="form-select form-select-sm">
                <option value="">Pilih Salah satu</option>
                <?php foreach ($bidang_usaha as $b): ?>
                  <option value="<?php echo h($b[0]); ?>" <?php echo (string) nilai('usaha') === (string) $b[0] ? 'selected' : ''; ?>><?php echo h($b[1]); ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="nama_usaha" id="nama_usaha" class="form-control form-control-sm" placeholder="Nama usaha"
                     value="<?php echo h(nilai('nama_usaha')); ?>" aria-label="Nama usaha">
            </div>
          </div>
          <?php pilihan('Sumber Dana Pembiayaan', 'sumber', array('1' => 'NI', '2' => 'Pihak Ketiga', '3' => 'Bank Victoria', '4' => 'BSM', '5' => 'BAV', '6' => 'bjb', '7' => 'BRI', '8' => 'LPDB')); ?>
          <?php isian('Golongan Pemilik', 'gol_pe', 'maxlength="20"'); ?>
          <?php isian('Golongan Debitur', 'gol_deb'); ?>
          <?php isian('Keterkaitan', 'ket'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header judul-bagian d-flex justify-content-between align-items-center">
      <span>Dokumen Pembiayaan</span>
      <button type="button" class="btn btn-light btn-sm py-0" id="cari-dokumen">Cari Dokumen</button>
    </div>
    <div class="card-body py-2">
      <div class="row g-2 text-center">
        <?php foreach (array('ktp' => 'KTP', 'kk' => 'KK', 'penjamin' => 'Penjamin', 'usaha' => 'Usaha') as $jenis => $judul): ?>
          <div class="col-6 col-md-3">
            <div class="border rounded p-2 h-100">
              <div class="fw-semibold mb-1"><?php echo h($judul); ?></div>
              <div id="img<?php echo h($jenis); ?>" class="small text-muted">Isi Nomor KTP lalu klik Cari Dokumen</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header judul-bagian">Informasi Pengajuan Pembiayaan</div>
    <div class="card-body py-2">
      <?php foreach ($urutan as $i => $u): ?>
        <div class="row g-2 mb-1 align-items-center">
          <label for="barang_<?php echo $u; ?>" class="col-sm-2 col-form-label col-form-label-sm">Barang <?php echo $i + 1; ?></label>
          <div class="col-sm-4">
            <input type="text" name="barang_<?php echo $u; ?>" id="barang_<?php echo $u; ?>" class="form-control form-control-sm"
                   maxlength="<?php echo $i < 3 ? 20 : 40; ?>" data-huruf-besar value="<?php echo h(nilai('barang_' . $u)); ?>">
          </div>
          <label for="harga_<?php echo $u; ?>" class="col-sm-2 col-form-label col-form-label-sm">Harga</label>
          <div class="col-sm-4">
            <input type="text" name="harga_<?php echo $u; ?>" id="harga_<?php echo $u; ?>" class="form-control form-control-sm"
                   inputmode="numeric" data-hanya-angka value="<?php echo h(nilai('harga_' . $u) !== '' ? nilai('harga_' . $u) : '0'); ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header judul-bagian">Informasi Pembiayaan dan Simpanan Anggota</div>
    <div class="card-body py-2">
      <div class="row gx-4">
        <div class="col-lg-6">
          <?php isian('No Rekening', 'no_anggota', '', false); ?>
          <div class="row g-2 mb-1">
            <label for="num" class="col-sm-5 col-form-label col-form-label-sm">Pengajuan</label>
            <div class="col-sm-7">
              <input type="text" name="num" id="num" class="form-control form-control-sm" maxlength="40" inputmode="numeric"
                     data-hanya-angka value="<?php echo h(nilai('num')); ?>">
              <div class="form-text" id="format-num"></div>
            </div>
          </div>
          <?php pilihan('Tenor (Minggu)', 'tenor', array('25' => '25', '35' => '35', '50' => '50')); ?>
          <div class="row g-2 mb-1">
            <label for="pla" class="col-sm-5 col-form-label col-form-label-sm">Disetujui UM/GFH <span class="text-danger">*</span></label>
            <div class="col-sm-7">
              <input type="text" name="pla" id="pla" class="form-control form-control-sm fw-bold" maxlength="8" inputmode="numeric"
                     data-hanya-angka value="<?php echo h(nilai('pla')); ?>">
              <div class="form-text" id="format-pla"></div>
            </div>
          </div>
          <?php isian('Margin (%)', 'rate', 'readonly', false); ?>
        </div>
        <div class="col-lg-6">
          <?php isian('Simpanan Khusus 5%', 'simpanan', 'readonly', false); ?>
          <?php isian('Angsuran', 'angsuran', 'readonly', false); ?>
          <?php isian('Total Margin', 'tot_margin', 'readonly', false); ?>
          <?php isian('Harga Jual', 'harga_jual', 'readonly', false); ?>
          <?php isian('Setoran', 'setoran', 'readonly', false); ?>
        </div>
      </div>
    </div>
    <div class="card-footer text-end">
      <button type="submit" class="btn btn-ni btn-sm px-4" id="tombol-simpan">
        <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span> Simpan
      </button>
    </div>
  </div>
</form>
<?php
halaman_akhir(array('assets/app/entry_anggota.js'));
