<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu admin > Parameter User > Tambah User: user name, password + konfirmasi, level, nama, kode unit -> tabel user.
 * Dimigrasi 2026-11-05 (perintah user): dulu form ini mengirim ke adduser.php (INSERT dengan teks SQL tempel, pesan
 * "User berhasil/gagal ditambahkan" di halaman kosong); kini proses di halaman ini (prepared statement), lalu kembali
 * ke formulir dengan pesan yang sama. Password tetap disimpan bcrypt(md5(isian)) (inc/password.php).
 * Disetujui user ("ok lanjut"):
 *   - konfirmasi password wajib sama dengan password (dulu tidak dicek)
 *   - user name yang sudah ada ditolak dengan pesan jelas (dulu hanya "gagal")
 *   - pilihan level = level yang dibuka log.php: admin, unit, lead, fund, kp, ho (dulu admin/user/lead/smo/spvs/kp;
 *     user/smo/spvs tidak bisa login)
 *   - user name maksimal 9 karakter sesuai kolom user.userid (dulu 50, terpotong diam-diam)
 * Hanya level admin (cek_admin.php).
 */
require_once __DIR__ . '/cek_admin.php';
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
require_once __DIR__ . '/../inc/tampilan.php';
require_once __DIR__ . '/../inc/password.php';

$daftar_level = array('admin', 'unit', 'lead', 'fund', 'kp', 'ho');

if (empty($_SESSION['token_tambah_user'])) {
    $_SESSION['token_tambah_user'] = bin2hex(random_bytes(16));
}

$isi = array('username' => '', 'level' => 'admin', 'nama' => '', 'branch' => '');
$pesan = isset($_SESSION['pesan_tambah_user']) ? $_SESSION['pesan_tambah_user'] : null;
unset($_SESSION['pesan_tambah_user']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($isi as $k => $v) {
        $isi[$k] = isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
    }
    $pass = isset($_POST['pass']) ? (string) $_POST['pass'] : '';
    $conf = isset($_POST['conf_pass']) ? (string) $_POST['conf_pass'] : '';
    $galat = '';
    if (!hash_equals($_SESSION['token_tambah_user'], isset($_POST['token']) ? (string) $_POST['token'] : '')) {
        $galat = 'Formulir kedaluwarsa, silakan ulangi.';
    } elseif ($isi['username'] === '' || $pass === '' || $isi['nama'] === '' || $isi['branch'] === '') {
        $galat = 'Semua isian bertanda * wajib diisi.';
    } elseif (strlen($isi['username']) > 9) {
        $galat = 'User name maksimal 9 karakter.';
    } elseif ($pass !== $conf) {
        $galat = 'Konfirmasi password tidak sama dengan password.';
    } elseif (!in_array($isi['level'], $daftar_level, true)) {
        $galat = 'Level tidak dikenal.';
    } elseif (db_satu("select userid from user where userid = ?", 's', $isi['username'])) {
        $galat = 'User name ' . $isi['username'] . ' sudah dipakai.';
    }
    if ($galat === '') {
        // bcrypt dari md5 isian (inc/password.php); md5 dari isian mentah seperti versi lama
        $password = password_buat_dari_md5(md5($pass));
        $ok = db_jalankan("insert into user set userid = ?, pass = ?, level = ?, nama = ?, kode_branch = ?", 'sssss',
            $isi['username'], $password, $isi['level'], $isi['nama'], $isi['branch']);
        if ($ok) {
            $_SESSION['pesan_tambah_user'] = array('success', 'User berhasil ditambahkan: ' . $isi['username'] . ' (' . $isi['level'] . ').');
            $_SESSION['token_tambah_user'] = bin2hex(random_bytes(16));
            header('Location: tambah_user.php');
            exit;
        }
        $galat = 'User gagal ditambahkan' . (db_error_terakhir() !== '' ? ': ' . db_error_terakhir() : '.');
    }
    $pesan = array('danger', $galat);
}

$unit = db_semua("select kode_branch, unit from branch order by kode_branch");

halaman_awal('Tambah User');
?>
<div class="card card-ni">
  <div class="card-header">Tambah User</div>
  <div class="card-body">
    <?php if ($pesan): ?>
      <div class="alert alert-<?php echo h($pesan[0]); ?> py-2"><?php echo h($pesan[1]); ?></div>
    <?php endif; ?>
    <form action="tambah_user.php" method="POST" name="Gantipass" id="Gantipass" class="form-ni" autocomplete="off">
      <input type="hidden" name="token" value="<?php echo h($_SESSION['token_tambah_user']); ?>">
      <div class="row g-2 mb-2">
        <label for="username" class="col-sm-4 col-form-label">User Name <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="username" type="text" id="username" class="form-control form-control-sm" maxlength="9" value="<?php echo h($isi['username']); ?>" required></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="pass" class="col-sm-4 col-form-label">Password <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="pass" type="password" id="pass" class="form-control form-control-sm" maxlength="50" autocomplete="new-password" required></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="conf_pass" class="col-sm-4 col-form-label"><strong>Konfirmasi</strong> Password <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="conf_pass" type="password" id="conf_pass" class="form-control form-control-sm" maxlength="50" autocomplete="new-password" required></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="level" class="col-sm-4 col-form-label">Level <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <select name="level" id="level" class="form-select form-select-sm">
            <?php foreach ($daftar_level as $lv): ?>
              <option value="<?php echo h($lv); ?>"<?php echo $isi['level'] === $lv ? ' selected' : ''; ?>><?php echo h($lv); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row g-2 mb-2">
        <label for="nama" class="col-sm-4 col-form-label">Nama <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="nama" type="text" id="nama" class="form-control form-control-sm" maxlength="50" value="<?php echo h($isi['nama']); ?>" required></div>
      </div>
      <div class="row g-2 mb-3">
        <label for="branch" class="col-sm-4 col-form-label">Kode Unit <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <input name="branch" type="text" id="branch" class="form-control form-control-sm" maxlength="4" list="daftar_unit" value="<?php echo h($isi['branch']); ?>" required>
          <datalist id="daftar_unit">
            <?php foreach ($unit as $u): ?><option value="<?php echo h($u['kode_branch']); ?>"><?php echo h($u['unit']); ?></option><?php endforeach; ?>
          </datalist>
          <div class="form-text">4 karakter, pilih dari daftar unit atau ketik (mis. 9999 untuk admin).</div>
        </div>
      </div>
      <div class="text-end">
        <button type="reset" class="btn btn-outline-secondary btn-sm">Batal</button>
        <button name="kirim" type="submit" id="kirim" value="Add User" class="btn btn-ni btn-sm px-4">Tambah User</button>
      </div>
    </form>
  </div>
</div>
<?php
halaman_akhir();
