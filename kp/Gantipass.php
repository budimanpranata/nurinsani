<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu kp > Ganti Password: formulir ganti password user yang login; proses di changepass.php (ganti_password.php:
 * password lama benar, baru = konfirmasi, minimal 8 karakter, lalu login ulang).
 * Dimigrasi 2026-11-03: hanya tampilan (Bootstrap 5); wajib login (dulu tanpa cek; query user dan md5 password yang
 * tidak dipakai dibuang). Nama field dan tujuan form sama dengan versi lama.
 */
require_once __DIR__ . '/../inc/tampilan.php';
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
$userid = (string) $_SESSION['userid'];

halaman_awal('Ganti Password');
?>
<div class="card card-ni">
  <div class="card-header">Ganti Password</div>
  <div class="card-body">
    <form action="changepass.php" method="POST" name="Gantipass" target="_self" id="Gantipass" class="form-ni">
      <div class="row g-2 mb-2">
        <label for="userid" class="col-sm-4 col-form-label">User Name <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="userid" type="text" id="userid" class="form-control form-control-sm" value="<?php echo h($userid); ?>" disabled maxlength="50"></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="passlama" class="col-sm-4 col-form-label">Password <strong>Lama</strong> <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="passlama" type="password" id="passlama" class="form-control form-control-sm" maxlength="50" autocomplete="current-password" required></div>
      </div>
      <div class="row g-2 mb-2">
        <label for="passbaru" class="col-sm-4 col-form-label">Password <strong>Baru</strong> <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <input name="passbaru" type="password" id="passbaru" class="form-control form-control-sm" maxlength="50" minlength="8" autocomplete="new-password" required>
          <div class="form-text">minimal 8 karakter</div>
        </div>
      </div>
      <div class="row g-2 mb-3">
        <label for="passlagi" class="col-sm-4 col-form-label"><strong>Konfirmasi</strong> Password <span class="text-danger">*</span></label>
        <div class="col-sm-8"><input name="passlagi" type="password" id="passlagi" class="form-control form-control-sm" maxlength="50" autocomplete="new-password" required></div>
      </div>
      <div class="text-end">
        <button type="reset" class="btn btn-outline-secondary btn-sm">Batal</button>
        <button name="kirim" type="submit" id="kirim" value="change" class="btn btn-ni btn-sm px-4">Ganti</button>
      </div>
    </form>
  </div>
</div>
<?php
halaman_akhir();
