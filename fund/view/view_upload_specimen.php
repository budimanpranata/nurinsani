<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menu fund > Input Data Master > Upload Specimen: specimen (contoh tanda tangan) 1 dan 2 per deposan.
 *   - pilih deposan (saran dari ../ajax_cari_deposan.php), tampil specimen yang sudah ada, unggah/ganti
 *   - hanya JPG/PNG maks 2 MB (jenis dicek dari isi file); disimpan di load/specimen/ (ditolak untuk akses web langsung)
 *     dengan nama specimen_<CIF>_<slot>_<waktu>.<jpg|png>; dicatat di tabel image (NOPEN = CIF, name = 'SPECIMEN 1|2',
 *     image = nama file); gambar ditampilkan lewat specimen_gambar.php (wajib login)
 * Dibangun ulang 2026-11-04 atas keputusan user: versi lama tidak pernah berfungsi (form bertumpuk tanpa
 * multipart sehingga file tidak terkirim, jQuery tidak dimuat, tanpa pilihan deposan, upload_img.php mencatat NOPEN
 * tetap '1212121', ekstensi file diambil 1 huruf, tanpa pengecekan jenis/ukuran file).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
ob_start();

const SPECIMEN_MAKS_BYTE = 2097152;
$folder = __DIR__ . '/../../load/specimen/';

/** Simpan satu file unggahan sebagai specimen slot $slot milik $cif; hasil null bila berhasil, atau pesan galat. */
function simpan_specimen($cif, $slot, $file, $folder)
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return "Specimen $slot gagal diunggah.";
    }
    if ($file['size'] > SPECIMEN_MAKS_BYTE) {
        return "Specimen $slot lebih dari 2 MB.";
    }
    $info = @getimagesize($file['tmp_name']);
    $jenis = $info ? $info[2] : null;
    if ($jenis !== IMAGETYPE_JPEG && $jenis !== IMAGETYPE_PNG) {
        return "Specimen $slot harus gambar JPG atau PNG.";
    }
    $nama = 'specimen_' . $cif . '_' . $slot . '_' . date('YmdHis') . ($jenis === IMAGETYPE_PNG ? '.png' : '.jpg');
    if (!move_uploaded_file($file['tmp_name'], $folder . $nama)) {
        return "Specimen $slot gagal disimpan di server.";
    }
    $lama = db_satu("SELECT image FROM image WHERE NOPEN=? AND name=?", 'ss', $cif, "SPECIMEN $slot");
    db_jalankan("DELETE FROM image WHERE NOPEN=? AND name=?", 'ss', $cif, "SPECIMEN $slot");
    if (!db_jalankan("INSERT INTO image(name,NOPEN,image) VALUES(?,?,?)", 'sss', "SPECIMEN $slot", $cif, $nama)) {
        @unlink($folder . $nama);
        return "Specimen $slot gagal dicatat: " . db_error_terakhir();
    }
    // file lama slot ini dihapus setelah yang baru tercatat
    if ($lama && basename((string) $lama['image']) !== $nama && preg_match('/^specimen_[A-Za-z0-9]+_[12]_\d{14}\.(jpg|png)$/', basename((string) $lama['image']))) {
        @unlink($folder . basename((string) $lama['image']));
    }
    return null;
}

$cif = isset($_REQUEST['cif']) ? trim((string) $_REQUEST['cif']) : '';
$deposan = $cif === '' ? null : db_satu("SELECT CIF, CUST_SHORT_NAME, JENIS FROM deposan WHERE CIF=?", 's', $cif);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unggah'])) {
    $pesan = array();
    if (!$deposan || !preg_match('/^[A-Za-z0-9]+$/', $cif)) {
        $pesan[] = array('danger', 'Deposan tidak ditemukan.');
    } else {
        $ada = false;
        foreach (array('1', '2') as $slot) {
            $f = isset($_FILES['specimen' . $slot]) ? $_FILES['specimen' . $slot] : array('error' => UPLOAD_ERR_NO_FILE);
            if ($f['error'] !== UPLOAD_ERR_NO_FILE) {
                $ada = true;
            }
            $g = simpan_specimen($cif, $slot, $f, $folder);
            $pesan[] = $g === null ? null : array('danger', $g);
        }
        $pesan = array_values(array_filter($pesan));
        if (!$ada) {
            $pesan[] = array('warning', 'Pilih file specimen terlebih dahulu.');
        } elseif (!$pesan) {
            $pesan[] = array('success', 'Specimen berhasil disimpan.');
        }
    }
    $_SESSION['pesan_specimen'] = $pesan;
    header('Location: view_upload_specimen.php' . ($cif !== '' ? '?cif=' . urlencode($cif) : ''));
    exit;
}
$pesan = isset($_SESSION['pesan_specimen']) ? $_SESSION['pesan_specimen'] : array();
unset($_SESSION['pesan_specimen']);
$ada_gambar = array();
if ($deposan) {
    foreach (db_semua("SELECT name, image FROM image WHERE NOPEN=?", 's', $cif) as $r) {
        $ada_gambar[(string) $r['name']] = is_file($folder . basename((string) $r['image']));
    }
}

halaman_awal('Upload Specimen');
?>
<?php foreach ($pesan as $p): ?>
<div class="alert alert-<?php echo $p[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($p[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endforeach; ?>
<div class="card card-ni">
  <div class="card-header">Pilih Deposan</div>
  <div class="card-body">
    <form method="get" action="view_upload_specimen.php" class="row g-2 align-items-end" autocomplete="off">
      <div class="col-sm-5 autocomplete">
        <label class="form-label" for="cif">CIF / Nama Deposan</label>
        <input type="text" name="cif" id="cif" value="<?php echo h($cif); ?>" class="form-control" placeholder="ketik CIF atau nama" required
               data-autocomplete="../ajax_cari_deposan.php" data-isi-nilai="cif" data-isi-label="label" data-isi-target='{"#nama_deposan":"nama"}'>
      </div>
      <div class="col-sm-5">
        <label class="form-label" for="nama_deposan">Nama</label>
        <input type="text" id="nama_deposan" value="<?php echo h($deposan ? $deposan['CUST_SHORT_NAME'] : ''); ?>" class="form-control" disabled>
      </div>
      <div class="col-sm-2 text-end">
        <button type="submit" class="btn btn-ni w-100">Tampilkan</button>
      </div>
    </form>
  </div>
</div>

<?php if ($cif !== '' && !$deposan): ?>
<div class="alert alert-warning mt-3">Deposan dengan CIF <?php echo h($cif); ?> tidak ditemukan.</div>
<?php elseif ($deposan): ?>
<form method="post" action="view_upload_specimen.php" enctype="multipart/form-data" class="mt-3">
  <input type="hidden" name="cif" value="<?php echo h($cif); ?>">
  <div class="card card-ni">
    <div class="card-header">Specimen <?php echo h($deposan['CIF'] . ' - ' . $deposan['CUST_SHORT_NAME']); ?></div>
    <div class="card-body row g-3">
      <?php foreach (array('1', '2') as $slot): ?>
      <div class="col-md-6">
        <div class="border rounded p-2 text-center mb-2" style="min-height: 200px;">
          <?php if (!empty($ada_gambar['SPECIMEN ' . $slot])): ?>
          <img src="specimen_gambar.php?cif=<?php echo h(urlencode($cif)); ?>&amp;slot=<?php echo $slot; ?>&amp;v=<?php echo time(); ?>"
               alt="Specimen <?php echo $slot; ?>" class="img-fluid" style="max-height: 260px;">
          <?php else: ?>
          <div class="text-muted pt-5">Belum ada Specimen <?php echo $slot; ?></div>
          <?php endif; ?>
        </div>
        <label class="form-label" for="specimen<?php echo $slot; ?>">Specimen <?php echo $slot; ?> (JPG/PNG, maks 2 MB)</label>
        <input type="file" name="specimen<?php echo $slot; ?>" id="specimen<?php echo $slot; ?>" class="form-control" accept="image/jpeg,image/png">
      </div>
      <?php endforeach; ?>
      <div class="col-12 text-end">
        <button type="submit" name="unggah" value="1" class="btn btn-ni px-4">Simpan Specimen</button>
      </div>
    </div>
  </div>
</form>
<?php endif; ?>
<?php
halaman_akhir();
