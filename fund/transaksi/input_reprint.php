<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Reprint bilyet/advis satu deposito (?kd=norek, dari fund/view/view_reprint.php).
 *   - hanya deposito berstatus 'Print' (sudah pernah dicetak); OK/ADVIS dicetak lewat menu Cetak Bilyet/Cetak Advis
 *   - Simpan: jenis cetak (1 Bilyet / 2 Advis), nomor surat kehilangan (wajib), keterangan, dokumen surat kehilangan
 *     (wajib, JPG/PNG/PDF maks 2 MB, jenis dicek dari isi file) -> dep_reprint (status 'BARU'); file di load/reprint/
 *     bernama reprint_<id>_<YmdHis>.<jpg|png|pdf>, dicatat di tabel image (NOPEN = norek, name = 'REPRINT <id>')
 *   - Cetak: ../view/cetak_bilyet.php / cetak_advis.php ?kd=norek&reprint=<id> (PDF tab baru); catatan menjadi
 *     'DICETAK' (sekali pakai), status_print deposito tidak diubah. Riwayat reprint deposito ini tampil di bawah.
 * Dibangun ulang 2026-11-04 atas keputusan user: versi lama tidak pernah berfungsi (tanpa cek login, ?kd tidak dibaca,
 * nama isian yang dibaca tidak ada di form, $kpt/$id tidak terisi, kolom ID_NUMBER/no_ket tidak ada di dep_reprint,
 * tidak mencetak apa pun, upload lewat upload_img.php yang tidak ada, Kembali ke master_reprint.php yang tidak ada).
 */
require_once __DIR__ . '/../../inc/tampilan.php';
date_default_timezone_set('Asia/Jakarta');
session_start();
ob_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "admin") {
    header("location:../../index.html");
    exit;
}
include_once __DIR__ . '/../../function.php';
require_once __DIR__ . '/../../inc/cetak_deposito.php';

const REPRINT_MAKS_BYTE = 2097152;
$folder = __DIR__ . '/../../load/reprint/';

/** Jenis file dokumen dari isinya: 'jpg' | 'png' | 'pdf', atau null. */
function jenis_dokumen($tmp)
{
    $info = @getimagesize($tmp);
    if ($info && $info[2] === IMAGETYPE_JPEG) {
        return 'jpg';
    }
    if ($info && $info[2] === IMAGETYPE_PNG) {
        return 'png';
    }
    return (string) @file_get_contents($tmp, false, null, 0, 5) === '%PDF-' ? 'pdf' : null;
}

/** Simpan catatan reprint + dokumennya; hasil array(id, null) bila berhasil, atau array(null, pesan galat). */
function simpan_reprint($dep, $folder)
{
    $jenis = isset($_POST['jenis_cetak']) ? (string) $_POST['jenis_cetak'] : '';
    $nomor_surat = isset($_POST['nomor_surat']) ? trim((string) $_POST['nomor_surat']) : '';
    $keterangan = isset($_POST['keterangan']) ? trim((string) $_POST['keterangan']) : '';
    $file = isset($_FILES['dokumen']) ? $_FILES['dokumen'] : array('error' => UPLOAD_ERR_NO_FILE);
    if ((string) $dep['status_print'] !== 'Print') {
        return array(null, 'Reprint hanya untuk deposito yang sudah pernah dicetak (status Print). Status sekarang: ' . $dep['status_print'] . '.');
    }
    if (!isset(REPRINT_JENIS[$jenis])) {
        return array(null, 'Pilih jenis cetak.');
    }
    if ($nomor_surat === '' || mb_strlen($nomor_surat) > 75) {
        return array(null, 'Nomor surat kehilangan wajib diisi (maks 75 karakter).');
    }
    if (mb_strlen($keterangan) > 30) {
        return array(null, 'Keterangan maks 30 karakter.');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return array(null, 'Dokumen surat kehilangan wajib diunggah.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return array(null, 'Dokumen gagal diunggah.');
    }
    if ($file['size'] > REPRINT_MAKS_BYTE) {
        return array(null, 'Dokumen lebih dari 2 MB.');
    }
    $ext = jenis_dokumen($file['tmp_name']);
    if ($ext === null) {
        return array(null, 'Dokumen harus JPG, PNG atau PDF.');
    }
    if (!db_jalankan("INSERT INTO dep_reprint(norek,cif,status,keterangan,nomor_surat,jenis_cetak) VALUES(?,?,'BARU',?,?,?)",
        'sssss', $dep['norek'], $dep['cif'], $keterangan, $nomor_surat, $jenis)) {
        return array(null, 'Catatan reprint gagal disimpan: ' . db_error_terakhir());
    }
    $id = (string) db_ni()->insert_id;
    $nama = 'reprint_' . $id . '_' . date('YmdHis') . '.' . $ext;
    $galat = null;
    if (!move_uploaded_file($file['tmp_name'], $folder . $nama)) {
        $galat = 'Dokumen gagal disimpan di server.';
    } elseif (!db_jalankan("INSERT INTO image(name,NOPEN,image) VALUES(?,?,?)", 'sss', 'REPRINT ' . $id, $dep['norek'], $nama)) {
        $galat = 'Dokumen gagal dicatat: ' . db_error_terakhir();
        @unlink($folder . $nama);
    }
    if ($galat !== null) {
        // dep_reprint MyISAM (tanpa transaksi): catatan tanpa dokumen dibatalkan
        db_jalankan("DELETE FROM dep_reprint WHERE id=?", 'i', (int) $id);
        return array(null, $galat);
    }
    return array($id, null);
}

$kode = isset($_REQUEST['kd']) ? trim((string) $_REQUEST['kd']) : '';
$dep = $kode === '' ? null : db_satu("SELECT deposito.norek, deposito.cif, CUST_SHORT_NAME, deposito.nominal, deposito.tenor,
    deposito.maturity_date, deposito.aro, deposito.bilyet, deposito.status_print
    FROM deposito join deposan on deposan.cif = deposito.cif WHERE deposito.norek=?", 's', $kode);
$url_ini = 'input_reprint.php?kd=' . urlencode($kode);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    if (!$dep) {
        $_SESSION['pesan_reprint'] = array('danger', 'Deposito tidak ditemukan.', null);
    } else {
        list($id, $galat) = simpan_reprint($dep, $folder);
        $_SESSION['pesan_reprint'] = $galat === null
            ? array('success', 'Catatan reprint ' . REPRINT_JENIS[(string) $_POST['jenis_cetak']] . ' nomor ' . $id . ' tersimpan. Klik Cetak untuk mencetak ulang (sekali pakai).', $id)
            : array('danger', $galat, null);
    }
    header('Location: ' . $url_ini);
    exit;
}
$pesan = isset($_SESSION['pesan_reprint']) ? $_SESSION['pesan_reprint'] : null;
unset($_SESSION['pesan_reprint']);
$riwayat = $dep ? db_semua("SELECT dep_reprint.id, jenis_cetak, nomor_surat, keterangan, status, image.image AS dokumen
    FROM dep_reprint left join image on image.name = concat('REPRINT ', dep_reprint.id)
    WHERE norek=? ORDER BY dep_reprint.id DESC", 's', $kode) : array();

/** Tautan cetak ulang catatan $r (array id, jenis_cetak). */
function url_cetak_ulang($kode, $r)
{
    return '../view/' . ((string) $r['jenis_cetak'] === '2' ? 'cetak_advis.php' : 'cetak_bilyet.php')
        . '?kd=' . urlencode($kode) . '&reprint=' . urlencode((string) $r['id']);
}

halaman_awal('Reprint Bilyet dan Advis');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo $pesan[0]; ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<?php if (!$dep): ?>
<div class="alert alert-warning">Deposito <?php echo h($kode); ?> tidak ditemukan.</div>
<a href="../view/view_reprint.php" class="btn btn-sm btn-secondary">Kembali</a>
<?php else: ?>
<div class="card card-ni mw-100">
  <div class="card-header">Deposito <?php echo h($dep['norek'] . ' - ' . $dep['CUST_SHORT_NAME']); ?></div>
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">No Transaksi</dt><dd class="col-sm-9"><?php echo h($dep['norek']); ?></dd>
      <dt class="col-sm-3">CIF</dt><dd class="col-sm-9"><?php echo h($dep['cif']); ?></dd>
      <dt class="col-sm-3">Nama</dt><dd class="col-sm-9"><?php echo h($dep['CUST_SHORT_NAME']); ?></dd>
      <dt class="col-sm-3">Bilyet</dt><dd class="col-sm-9"><?php echo h($dep['bilyet']); ?></dd>
      <dt class="col-sm-3">Nominal</dt><dd class="col-sm-9"><?php echo number_format(compat_num($dep['nominal'])); ?></dd>
      <dt class="col-sm-3">Tenor</dt><dd class="col-sm-9"><?php echo h($dep['tenor']); ?></dd>
      <dt class="col-sm-3">Jatuh Tempo</dt><dd class="col-sm-9"><?php echo h(tgl_indo($dep['maturity_date'])); ?></dd>
      <dt class="col-sm-3">Status Aro</dt><dd class="col-sm-9"><?php echo h($dep['aro']); ?></dd>
      <dt class="col-sm-3">Status Cetak</dt><dd class="col-sm-9"><?php echo h($dep['status_print']); ?></dd>
    </dl>
  </div>
</div>

<?php if ((string) $dep['status_print'] !== 'Print'): ?>
<div class="alert alert-warning mt-3">Reprint hanya untuk deposito yang sudah pernah dicetak (status Print). Deposito ini berstatus <?php echo h($dep['status_print']); ?>: gunakan menu Cetak Bilyet / Cetak Advis.</div>
<?php else: ?>
<form method="post" action="<?php echo h($url_ini); ?>" enctype="multipart/form-data" class="card card-ni mw-100 mt-3">
  <div class="card-header">Input Data Reprint</div>
  <div class="card-body row g-3">
    <div class="col-sm-4">
      <label class="form-label" for="jenis_cetak">Jenis Cetak</label>
      <select name="jenis_cetak" id="jenis_cetak" class="form-select" required>
        <option value="">Pilih Jenis Reprint</option>
        <?php foreach (REPRINT_JENIS as $k => $v): ?>
        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-8">
      <label class="form-label" for="nomor_surat">Nomor Surat Kehilangan</label>
      <input type="text" name="nomor_surat" id="nomor_surat" maxlength="75" class="form-control" placeholder="Nomor Surat Kehilangan" required>
    </div>
    <div class="col-sm-4">
      <label class="form-label" for="keterangan">Keterangan</label>
      <input type="text" name="keterangan" id="keterangan" maxlength="30" class="form-control">
    </div>
    <div class="col-sm-8">
      <label class="form-label" for="dokumen">Upload Dokumen Surat Kehilangan (JPG/PNG/PDF, maks 2 MB)</label>
      <input type="file" name="dokumen" id="dokumen" class="form-control" accept="image/jpeg,image/png,application/pdf" required>
    </div>
    <div class="col-12 d-flex gap-2">
      <button type="submit" name="simpan" value="1" class="btn btn-ni" id="tombol_simpan">Simpan</button>
      <a href="../view/view_reprint.php" class="btn btn-secondary">Kembali</a>
    </div>
  </div>
</form>
<?php endif; ?>

<div class="card card-ni mw-100 mt-3">
  <div class="card-header">Riwayat Reprint</div>
  <div class="card-body">
    <table class="table table-sm table-bordered table-ni align-middle w-100 mb-0">
      <thead>
        <tr>
          <th>No</th>
          <th>Jenis</th>
          <th>Nomor Surat Kehilangan</th>
          <th>Keterangan</th>
          <th>Waktu Input</th>
          <th>Dokumen</th>
          <th>Status</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$riwayat): ?>
        <tr><td colspan="8" class="text-center text-muted">Belum ada reprint</td></tr>
        <?php endif; ?>
        <?php foreach ($riwayat as $r):
            $jenis = isset(REPRINT_JENIS[(string) $r['jenis_cetak']]) ? REPRINT_JENIS[(string) $r['jenis_cetak']] : (string) $r['jenis_cetak'];
            $waktu = preg_match('/_(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})\d{2}\.\w+$/', (string) $r['dokumen'], $w) ? "$w[3]-$w[2]-$w[1] $w[4]:$w[5]" : '-'; ?>
        <tr>
          <td><?php echo h($r['id']); ?></td>
          <td><?php echo h($jenis); ?></td>
          <td><?php echo h($r['nomor_surat']); ?></td>
          <td><?php echo h($r['keterangan']); ?></td>
          <td><?php echo h($waktu); ?></td>
          <td><?php if ((string) $r['dokumen'] !== ''): ?><a href="../view/reprint_dokumen.php?id=<?php echo h(urlencode((string) $r['id'])); ?>" target="_blank" rel="noopener">Lihat</a><?php else: ?>-<?php endif; ?></td>
          <td><?php echo h($r['status']); ?></td>
          <td class="text-center">
            <?php if ((string) $r['status'] === 'BARU' && (string) $dep['status_print'] === 'Print'): ?>
            <a href="<?php echo h(url_cetak_ulang($kode, $r)); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success" data-cetak>Cetak</a>
            <?php else: ?>-<?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php
halaman_akhir(array('assets/app/fund_reprint.js'));
