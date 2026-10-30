<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Pemeliharaan CIM: koreksi data anggota + pengajuan, disimpan oleh update_log.php.
 * Isian, nilai awal, urutan validasi, dan pesan alert sama dengan versi lama.
 * Kalkulator (2026-10-30, perintah user) mengikuti perhitungan server di update_log.php dari param_biaya.
 * Data anggota diisi otomatis lewat pencarian CIF/nama/KTP (ajax/cari_dim.php, pengganti autosuggesst_dim.php).
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}

$r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
$branch = $r ? $r['kode_branch'] : '';
$bidang_usaha = db_semua('select * from bidang_usaha');

// data kalkulator: perhitungan di form sama dengan update_log.php (param_biaya per tenor+plafond, jenis akad per unit)
$data_kalkulator = array('biaya' => array(), 'akad' => array());
foreach (db_semua('select jw, pla, margin, tab from param_biaya') as $pb) {
    $data_kalkulator['biaya'][$pb['jw'] . '|' . $pb['pla']] = array((float) $pb['margin'], (float) $pb['tab']);
}
foreach (db_semua('select unit, jenis_akad from jenis_akad') as $ja) {
    $data_kalkulator['akad'][(string) $ja['unit']] = (string) $ja['jenis_akad'];
}

$awal = array(
    'branch' => $branch,
    'code_kel' => isset($_SESSION['code_kel']) ? $_SESSION['code_kel'] : '',
    'code_ao' => isset($_SESSION['cao']) ? $_SESSION['cao'] : '',
    'no_tlp' => '0', 'tlp_penjamin' => '0', 'npwp' => '0', 'pekerjaan' => '0',
    'gol_pe' => '875', 'gol_deb' => '874', 'id_expire' => '1900:00:00', 'ket' => '2',
    'harga_satu' => '0', 'harga_dua' => '0', 'harga_tiga' => '0', 'harga_empat' => '0', 'harga_lima' => '0',
    'harga_enam' => '0', 'harga_tujuh' => '0', 'harga_delapan' => '0', 'harga_sembilan' => '0', 'harga_sepuluh' => '0',
    'simpanan' => '0', 'angsuran' => '0', 'tot_margin' => '0',
);

/** Satu baris label + input. */
function isian($label, $nama, $attr = '', $tipe = 'text', $wajib = true)
{
    global $awal;
    ?>
    <div class="row g-2 mb-1">
      <label for="<?php echo h($nama); ?>" class="col-sm-5 col-form-label col-form-label-sm"><?php echo h($label); ?><?php echo $wajib ? ' <span class="text-danger">*</span>' : ''; ?></label>
      <div class="col-sm-7">
        <input type="<?php echo h($tipe); ?>" name="<?php echo h($nama); ?>" id="<?php echo h($nama); ?>"
               class="form-control form-control-sm" value="<?php echo h(isset($awal[$nama]) ? $awal[$nama] : ''); ?>" <?php echo $attr; ?>>
      </div>
    </div>
    <?php
}

/** Satu baris label + select. $pilihan: daftar [value, teks]; value null = <option> tanpa value (teks jadi nilai). */
function pilihan($label, $nama, $pilihan)
{
    ?>
    <div class="row g-2 mb-1">
      <label for="<?php echo h($nama); ?>" class="col-sm-5 col-form-label col-form-label-sm"><?php echo h($label); ?> <span class="text-danger">*</span></label>
      <div class="col-sm-7">
        <select name="<?php echo h($nama); ?>" id="<?php echo h($nama); ?>" class="form-select form-select-sm">
          <?php foreach ($pilihan as $p): ?>
            <option<?php echo $p[0] === null ? '' : ' value="' . h($p[0]) . '"'; ?>><?php echo h($p[1]); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <?php
}

// isian yang diisi otomatis saat memilih anggota (id isian => kunci data di ajax/cari_dim.php)
$isi = array();
$urutan = array('satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh');
foreach (array('nama', 'no_anggota', 'branch', 'ibu', 'alamat', 'lurah', 'kec', 'kota', 'wali', 'no_tlp', 'tgl_lahir',
    'tempat_lahir', 'code_kel', 'pendidikan', 'npwp', 'kd_pos', 'status_kawin', 'id_expire', 'id', 'sumber', 'jenis_kel',
    'code_ao', 'tgl_skep', 'rt', 'pla', 'usaha', 'nama_usaha', 'tgl_akad', 'tenor', 'pekerjaan', 'suffix', 'tlp_penjamin') as $k) {
    $isi['#' . $k] = $k;
}
foreach ($urutan as $u) {
    $isi['#barang_' . $u] = 'barang_' . $u;
    $isi['#harga_' . $u] = 'harga_' . $u;
}
$nama_barang = array('satu' => 1, 'dua' => 2, 'tiga' => 3, 'empat' => 4, 'lima' => 5, 'enam' => 6, 'tujuh' => 7, 'delapan' => 8, 'sembilan' => 9, 'sepuluh' => 10);

halaman_awal('Pemeliharaan CIM');
?>
<form action="update_log.php" method="post" name="postform" id="form-cim" class="form-ni" novalidate autocomplete="off">

  <div class="card mb-3">
    <div class="card-header judul-bagian">Entry Calon Anggota</div>
    <div class="card-body py-2">
      <div class="row gx-4">
        <div class="col-lg-6">
          <?php isian('Kode Unit', 'branch', 'maxlength="6"'); ?>
          <div class="row g-2 mb-1">
            <label for="cif" class="col-sm-5 col-form-label col-form-label-sm">CIF <span class="text-danger">*</span></label>
            <div class="col-sm-7 autocomplete">
              <input type="text" name="cif" id="cif" class="form-control form-control-sm"
                     placeholder="ketik CIF / nama / nomor KTP"
                     data-autocomplete="ajax/cari_dim.php" data-isi-nilai="cif" data-isi-label="label"
                     data-isi-target='<?php echo h(json_encode($isi)); ?>'>
            </div>
          </div>
          <?php isian('Nomor KTP', 'id', 'maxlength="20" autofocus'); ?>
          <?php isian('Nama', 'nama', 'maxlength="60" data-huruf-besar'); ?>
          <?php isian('Tempat Lahir', 'tempat_lahir', 'data-huruf-besar'); ?>
          <?php isian('Tgl Lahir Sesuai KTP', 'tgl_lahir', '', 'date'); ?>
          <?php pilihan('Jenis Kelamin', 'jenis_kel', array(array('Perempuan', 'Perempuan'), array('Laki-laki', 'Laki-laki'))); ?>
          <?php isian('Alamat Sesuai KTP', 'alamat', 'maxlength="50" data-huruf-besar'); ?>
          <?php isian('RT/RW', 'rt'); ?>
          <?php isian('Kode Pos', 'kd_pos'); ?>
          <?php isian('Kelurahan', 'lurah', 'data-huruf-besar'); ?>
          <?php isian('Kecamatan', 'kec', 'data-huruf-besar'); ?>
          <?php isian('Kabupaten/Kota', 'kota', 'data-huruf-besar'); ?>
          <?php pilihan('Status Perkawinan', 'status_kawin', array(array('kawin', 'KAWIN'), array('belum_kawin', 'BELUM KAWIN'), array('janda', 'JANDA'))); ?>
          <?php isian('Pekerjaan Pasangan', 'pekerjaan', 'data-huruf-besar'); ?>
          <?php pilihan('Agama', 'agama', array(array('ISLAM', 'ISLAM'), array('BUDHA', 'BUDHA'), array('KRISTEN', 'KRISTEN'))); ?>
          <?php isian('ID Expire', 'id_expire'); ?>
        </div>
        <div class="col-lg-6">
          <?php isian('Kode Kelompok', 'code_kel', 'data-huruf-besar'); ?>
          <?php isian('Kode AO', 'code_ao'); ?>
          <?php pilihan('Kewarganegaraan', 'negara', array(array('ID', 'INDONESIA'))); ?>
          <?php pilihan('Pendidikan', 'pendidikan', array(array(null, 'Pilih Salah satu'), array('SMA', 'SMA'), array('SMP', 'SMP'), array('SD', 'SD'), array('D1', 'D1'), array('D3', 'D3'), array('S1', 'S1'))); ?>
          <?php isian('Nama Pasangan/Penjamin', 'wali', 'data-huruf-besar'); ?>
          <?php isian('No Tlp', 'no_tlp'); ?>
          <?php isian('No Tlp Penjamin', 'tlp_penjamin'); ?>
          <?php isian('NPWP', 'npwp', 'maxlength="20"'); ?>
          <?php isian('Nama Ibu Kandung', 'ibu', 'data-huruf-besar'); ?>
          <?php isian('Tanggal Wakalah', 'tgl_skep', '', 'date'); ?>
          <?php isian('Tgl Akad Murabahah', 'tgl_akad', '', 'date'); ?>
          <?php pilihan('Jenis Produk Pinjaman', 'deal_type', array(array('1', 'Minggu'), array('2', 'Hari'), array('3', 'Bulan'))); ?>
          <div class="row g-2 mb-1">
            <label for="usaha" class="col-sm-5 col-form-label col-form-label-sm">Bidang Usaha <span class="text-danger">*</span></label>
            <div class="col-sm-7">
              <select name="usaha" id="usaha" class="form-select form-select-sm mb-1">
                <option>Pilih Salah satu</option>
                <?php foreach ($bidang_usaha as $b): $b = array_values($b); ?>
                  <option value="<?php echo h($b[0]); ?>"><?php echo h($b[1]); ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="nama_usaha" id="nama_usaha" class="form-control form-control-sm" placeholder="nama usaha">
            </div>
          </div>
          <?php pilihan('Sumber Dana', 'sumber', array(array('2', 'Pihak Ketiga'), array('1', 'NI'), array('2', 'Pihak Ketiga'), array('3', 'Bank Victoria'), array('4', 'BSM'), array('5', 'BAV'), array('6', 'bjb'), array('7', 'BRI'))); ?>
          <?php isian('Golongan Pemilik', 'gol_pe'); ?>
          <?php isian('Golongan Debitur', 'gol_deb'); ?>
          <?php isian('Keterkaitan', 'ket'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header judul-bagian">Dokumen Pembiayaan</div>
    <div class="card-body py-2">
      <div class="row g-2 text-center small text-body-secondary">
        <div class="col-6 col-md-3"><div id="imgktp" class="border rounded p-2 h-100">ktp</div></div>
        <div class="col-6 col-md-3"><div id="imgkk" class="border rounded p-2 h-100">kk</div></div>
        <div class="col-6 col-md-3"><div id="imgpenjamin" class="border rounded p-2 h-100">Penjamin</div></div>
        <div class="col-6 col-md-3"><div id="imgusaha" class="border rounded p-2 h-100">usaha</div></div>
      </div>
      <div class="text-end mt-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="cari-dokumen">Cari Dokumen</button>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header judul-bagian">Informasi Pengajuan Pembiayaan</div>
    <div class="card-body py-2">
      <div class="row gx-4">
        <?php foreach ($nama_barang as $u => $no): ?>
          <div class="col-lg-6">
            <div class="row g-2 mb-1">
              <label for="barang_<?php echo $u; ?>" class="col-sm-2 col-form-label col-form-label-sm">Barang <?php echo $no; ?></label>
              <div class="col-sm-5"><input type="text" name="barang_<?php echo $u; ?>" id="barang_<?php echo $u; ?>" class="form-control form-control-sm" data-huruf-besar></div>
              <label for="harga_<?php echo $u; ?>" class="col-sm-1 col-form-label col-form-label-sm">Harga</label>
              <div class="col-sm-4"><input type="text" name="harga_<?php echo $u; ?>" id="harga_<?php echo $u; ?>" class="form-control form-control-sm" value="0"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header judul-bagian">Informasi Pembiayaan dan Simpanan Anggota</div>
    <div class="card-body py-2">
      <div class="row gx-4">
        <div class="col-lg-6">
          <div class="row g-2 mb-1">
            <label for="no_anggota" class="col-sm-5 col-form-label col-form-label-sm">No Rekening / Sufix</label>
            <div class="col-sm-5"><input type="text" name="no_anggota" id="no_anggota" class="form-control form-control-sm"></div>
            <div class="col-sm-2"><input type="text" name="suffix" id="suffix" class="form-control form-control-sm" maxlength="2" aria-label="Sufix"></div>
          </div>
          <div class="row g-2 mb-1">
            <label for="num" class="col-sm-5 col-form-label col-form-label-sm">Rekomendasi</label>
            <div class="col-sm-7"><input type="text" name="num" id="num" class="form-control form-control-sm" maxlength="40" data-format-rupiah></div>
          </div>
          <div class="row g-2 mb-1">
            <label for="tenor" class="col-sm-5 col-form-label col-form-label-sm">Tenor (minggu)</label>
            <div class="col-sm-7"><input type="text" name="tenor" id="tenor" class="form-control form-control-sm" maxlength="6" data-hitung></div>
          </div>
          <div class="row g-2 mb-1">
            <label for="pla" class="col-sm-5 col-form-label col-form-label-sm">Harga Beli <span class="text-danger">*</span></label>
            <div class="col-sm-7"><input type="text" name="pla" id="pla" class="form-control form-control-sm fw-bold" data-hitung data-format-rupiah></div>
          </div>
          <div class="small text-body-secondary text-end mb-1" id="format"></div>
          <div class="small text-end mb-2" id="info-parameter" aria-live="polite"></div>
        </div>
        <div class="col-lg-6">
          <?php isian('Simpanan Khusus 5%', 'simpanan', 'data-hitung', 'text', false); ?>
          <?php isian('Angsuran', 'angsuran', 'data-hitung', 'text', false); ?>
          <?php isian('Total Margin', 'tot_margin', 'data-hitung', 'text', false); ?>
          <?php isian('Margin', 'rate', 'data-hitung', 'text', false); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="text-end mb-3">
    <button type="submit" name="simpan" value="Simpan" class="btn btn-ni px-4">Simpan</button>
  </div>
</form>
<script type="application/json" id="data-kalkulator"><?php echo json_encode($data_kalkulator, JSON_HEX_TAG); ?></script>
<?php
halaman_akhir(array('assets/app/pemeliharaan_cim.js'));
