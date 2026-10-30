<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Menu Pemeliharaan Data > Kelompok: ubah data kelompok (kode, AO, nama, alamat, hari minggon), disimpan di update_kel.php.
 * Saran kelompok memakai ajax/cari_kelompok.php (pengganti autosuggest.php).
 * Perbaikan atas perintah user (2026-10-30): hanya level unit, AO wajib, kelompok unit lain ditolak,
 * kode baru dikonfirmasi lebih dulu (ajax/cek_kelompok.php). Pemeriksaan yang sama diulang di update_kel.php.
 */
require_once __DIR__ . '/inc/tampilan.php';
session_start();
//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header('location:index.html'); //jika belum login jangan lanjut..
    exit;
}
//cek level user (perbaikan 2026-10-30: menu ini hanya untuk unit)
if ($_SESSION['level'] != 'unit') {
    header('location:index.html');
    exit;
}
$userid = $_SESSION['userid'];
$r = db_satu('SELECT kode_branch FROM user WHERE userid = ?', 's', (string) $userid);
$branch = $r ? $r['kode_branch'] : null;
$daftar_ao = db_semua('SELECT * FROM ao WHERE kode_unit = ?', 's', (string) $branch);

$isi = array('#nama' => 'nama_kel', '#cao' => 'cao', '#alamat' => 'alamat', '#hari' => 'hari');

halaman_awal('Update Kelompok');
?>
<div class="card card-ni">
  <div class="card-header">Update Kelompok</div>
  <div class="card-body">
    <form action="update_kel.php" method="post" name="postform" id="form-kelompok" class="form-ni" autocomplete="off">
      <div class="row g-2 mb-2">
        <label for="code_kel" class="col-sm-4 col-form-label">Kode Kelompok <span class="text-danger">*</span></label>
        <div class="col-sm-8 autocomplete">
          <input type="text" name="code_kel" id="code_kel" class="form-control form-control-sm" maxlength="10"
                 placeholder="ketik kode atau nama kelompok"
                 data-autocomplete="ajax/cari_kelompok.php" data-isi-nilai="code_kel" data-isi-label="code_kel,nama_kel"
                 data-isi-target='<?php echo h(json_encode($isi)); ?>'>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="cao" class="col-sm-4 col-form-label">Kode AO <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <select name="cao" id="cao" class="form-select form-select-sm">
            <option value="">- Select Kode AO -</option>
            <?php foreach ($daftar_ao as $ao): ?>
            <option value="<?php echo h($ao['cao']); ?>"><?php echo h($ao['nama_ao']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="nama" class="col-sm-4 col-form-label">Nama Kelompok <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <input type="text" name="nama" id="nama" class="form-control form-control-sm" maxlength="30">
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="alamat" class="col-sm-4 col-form-label">Alamat Kelompok <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <textarea name="alamat" id="alamat" class="form-control form-control-sm" rows="2"></textarea>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="hari" class="col-sm-4 col-form-label">Hari Minggon <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <input type="text" name="hari" id="hari" class="form-control form-control-sm" maxlength="30">
        </div>
      </div>
      <input type="hidden" name="kelompok_baru" id="kelompok_baru" value="">
      <div class="text-end">
        <button type="submit" name="cari" value="Simpan" class="btn btn-ni btn-sm px-4">Simpan</button>
      </div>
    </form>
  </div>
</div>
<script>
document.getElementById('form-kelompok').addEventListener('submit', function (e) {
    var f = this;
    if (f.dataset.sudahDicek === '1') {
        return; // lolos pemeriksaan di bawah, kirim
    }
    e.preventDefault();
    var kode = f.code_kel.value.trim();
    if (kode === '') {
        alert('Masukkan KODE KELOMPOK');
        f.code_kel.focus();
        return;
    }
    if (f.nama.value === '') {
        alert('Masukkan NAMA KELOMPOK');
        f.nama.focus();
        return;
    }
    if (f.cao.value === '') {
        alert('Pilih KODE AO');
        f.cao.focus();
        return;
    }
    fetch('ajax/cek_kelompok.php?code=' + encodeURIComponent(kode), { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (hasil) {
            if (hasil.ada && !hasil.unit_sendiri) {
                alert('Kelompok ' + kode + ' milik unit ' + hasil.unit + ', tidak bisa diubah dari unit ini');
                return;
            }
            if (!hasil.ada) {
                if (!confirm('Kode kelompok ' + kode + ' belum terdaftar.\nBuat kelompok baru?')) return;
                f.kelompok_baru.value = '1';
            } else {
                f.kelompok_baru.value = '';
            }
            f.dataset.sudahDicek = '1';
            f.requestSubmit ? f.requestSubmit() : f.submit();
        })
        .catch(function () { alert('Gagal memeriksa kode kelompok, coba lagi.'); });
});
// kembali dari update_kel.php (history.back): periksa ulang pada simpan berikutnya
window.addEventListener('pageshow', function () {
    delete document.getElementById('form-kelompok').dataset.sudahDicek;
});
</script>
<?php
halaman_akhir();
