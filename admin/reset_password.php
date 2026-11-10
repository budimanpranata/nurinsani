<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Admin > Parameter User > Reset Password (perintah user 2026-11-05): jalan terakhir bila user lupa password.
 *   - cari user (user name / nama), pilih, isi alasan
 *   - sistem membuat password sementara acak (ditampilkan SEKALI ke admin untuk diserahkan langsung ke user)
 *   - last_update diisi 2000-01-01 sehingga log.php menganggap password kedaluwarsa: saat login dengan password
 *     sementara user langsung diarahkan ke Ganti Password; status diisi '0' supaya Logout (UPDATE status='0') tidak
 *     mengubah last_update (kolom itu ON UPDATE CURRENT_TIMESTAMP)
 *   - sesi user yang sedang aktif diputus (penanda inc/penjaga_sesi.php ditimpa)
 *   - dicatat di riwayat_reset_password (db/parameter/2026-11-05_password_hash.sql)
 *   - akun sendiri tidak bisa di-reset di sini (pakai menu Ganti Password)
 * Reset MFA (2026-11-09, PEKERJAAN_TERTUNDA.md bagian 101): hapus pendaftaran authenticator user (HP hilang/ganti HP);
 * login berikutnya user memindai QR code baru. Dicatat di riwayat_reset_password dengan jenis 'MFA'.
 * MFA akun sendiri tidak bisa di-reset di sini; darurat: php db/parameter/reset_mfa.php <userid> di server.
 * Hanya level admin (cek_admin.php).
 */
require_once __DIR__ . '/cek_admin.php';
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
require_once __DIR__ . '/../inc/tampilan.php';
require_once __DIR__ . '/../inc/password.php';
require_once __DIR__ . '/../inc/mfa.php';

const PANJANG_PASSWORD_SEMENTARA = 10;

/** Password sementara acak; tanpa huruf/angka yang mirip (0/O, 1/l/I) supaya mudah didiktekan. */
function password_sementara()
{
    $huruf = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $hasil = '';
    for ($i = 0; $i < PANJANG_PASSWORD_SEMENTARA; $i++) {
        $hasil .= $huruf[random_int(0, strlen($huruf) - 1)];
    }
    return $hasil;
}

if (empty($_SESSION['token_reset_password'])) {
    $_SESSION['token_reset_password'] = bin2hex(random_bytes(16));
}
$admin = (string) $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $u = isset($_POST['u']) ? trim((string) $_POST['u']) : '';
    $alasan = isset($_POST['alasan']) ? trim((string) $_POST['alasan']) : '';
    $user = $u !== '' ? db_satu("select userid, nama from user where userid = ?", 's', $u) : null;
    if (!hash_equals($_SESSION['token_reset_password'], isset($_POST['token']) ? (string) $_POST['token'] : '')) {
        $_SESSION['pesan_reset_password'] = array('danger', 'Formulir kedaluwarsa, silakan ulangi.');
    } elseif (!$user) {
        $_SESSION['pesan_reset_password'] = array('danger', 'User tidak ditemukan.');
    } elseif (strcasecmp($user['userid'], $admin) === 0) {
        $_SESSION['pesan_reset_password'] = array('danger', 'Password akun sendiri diganti lewat menu Ganti Password.');
    } elseif ($alasan === '') {
        $_SESSION['pesan_reset_password'] = array('danger', 'Alasan reset wajib diisi.');
    } else {
        $sementara = password_sementara();
        $st = db_ni()->prepare("update user set pass = ?, status = '0', last_update = '2000-01-01 00:00:00' where userid = ?");
        $hash = password_buat($sementara);
        $st->bind_param('ss', $hash, $user['userid']);
        if ($st->execute() && $st->affected_rows === 1) {
            db_jalankan("insert into riwayat_reset_password (waktu, admin, userid, alasan, ip) values (now(), ?, ?, ?, ?)", 'ssss',
                $admin, $user['userid'], mb_substr($alasan, 0, 255), isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '');
            // sesi user yang sedang login (bila ada) tidak lagi dianggap sesi aktif -> keluar pada klik berikutnya
            @file_put_contents(penjaga_sesi_berkas($user['userid']), 'reset-' . time(), LOCK_EX);
            $_SESSION['hasil_reset_password'] = array('userid' => $user['userid'], 'nama' => $user['nama'], 'password' => $sementara);
            $_SESSION['token_reset_password'] = bin2hex(random_bytes(16));
        } else {
            $_SESSION['pesan_reset_password'] = array('danger', 'Reset password gagal: ' . ($st->error !== '' ? $st->error : 'data tidak berubah'));
        }
    }
    header('Location: reset_password.php?u=' . urlencode($u));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_mfa'])) {
    $u = isset($_POST['u']) ? trim((string) $_POST['u']) : '';
    $alasan = isset($_POST['alasan']) ? trim((string) $_POST['alasan']) : '';
    $user = $u !== '' ? db_satu("select userid, nama from user where userid = ?", 's', $u) : null;
    if (!hash_equals($_SESSION['token_reset_password'], isset($_POST['token']) ? (string) $_POST['token'] : '')) {
        $_SESSION['pesan_reset_password'] = array('danger', 'Formulir kedaluwarsa, silakan ulangi.');
    } elseif (!$user) {
        $_SESSION['pesan_reset_password'] = array('danger', 'User tidak ditemukan.');
    } elseif (strcasecmp($user['userid'], $admin) === 0) {
        $_SESSION['pesan_reset_password'] = array('danger', 'MFA akun sendiri tidak bisa di-reset di sini.');
    } elseif ($alasan === '') {
        $_SESSION['pesan_reset_password'] = array('danger', 'Alasan reset wajib diisi.');
    } elseif (mfa_hapus($user['userid']) !== 1) {
        $_SESSION['pesan_reset_password'] = array('warning', 'User ' . $user['userid'] . ' belum mendaftarkan MFA, tidak ada yang di-reset.');
    } else {
        db_jalankan("insert into riwayat_reset_password (waktu, admin, userid, jenis, alasan, ip) values (now(), ?, ?, 'MFA', ?, ?)", 'ssss',
            $admin, $user['userid'], mb_substr($alasan, 0, 255), isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '');
        // sesi user yang sedang login diputus: login berikutnya wajib memindai QR code baru
        @file_put_contents(penjaga_sesi_berkas($user['userid']), 'reset-mfa-' . time(), LOCK_EX);
        $_SESSION['pesan_reset_password'] = array('success', 'MFA ' . $user['userid'] . ' - ' . $user['nama']
            . ' sudah di-reset. Saat login berikutnya user memindai QR code baru dengan aplikasi authenticator di HP-nya.');
        $_SESSION['token_reset_password'] = bin2hex(random_bytes(16));
    }
    header('Location: reset_password.php?u=' . urlencode($u));
    exit;
}

$pesan =isset($_SESSION['pesan_reset_password']) ? $_SESSION['pesan_reset_password'] : null;
$hasil = isset($_SESSION['hasil_reset_password']) ? $_SESSION['hasil_reset_password'] : null;
unset($_SESSION['pesan_reset_password'], $_SESSION['hasil_reset_password']);

$cari = isset($_GET['cari']) ? trim((string) $_GET['cari']) : '';
$u = isset($_GET['u']) ? trim((string) $_GET['u']) : '';
// semua user tampil; isian cari menyaring user name / nama / level / unit
$kolom_daftar = "select user.userid, nama, level, kode_branch, user_mfa.didaftar as mfa_didaftar
    from user left join user_mfa on user_mfa.userid = user.userid";
$daftar = $cari !== '' ? db_semua($kolom_daftar . "
    where user.userid like ? or nama like ? or level like ? or kode_branch like ? order by user.userid", 'ssss',
    '%' . $cari . '%', '%' . $cari . '%', '%' . $cari . '%', '%' . $cari . '%')
    : db_semua($kolom_daftar . " order by user.userid");
$user = $u !== '' ? db_satu("select userid, nama, level, kode_branch, last_update from user where userid = ?", 's', $u) : null;
$mfa = $user ? mfa_data($user['userid']) : null;
$riwayat = db_semua("select waktu, admin, userid, jenis, alasan, ip from riwayat_reset_password"
    . ($user ? ' where userid = ?' : '') . " order by id desc limit 50", $user ? 's' : '', ...($user ? array($user['userid']) : array()));

halaman_awal('Reset Password User');
?>
<?php if ($pesan): ?>
<div class="alert alert-<?php echo h($pesan[0]); ?> alert-dismissible fade show" role="alert">
  <?php echo h($pesan[1]); ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<?php if ($hasil): ?>
<div class="alert alert-success" role="alert">
  Password <strong><?php echo h($hasil['userid'] . ' - ' . $hasil['nama']); ?></strong> sudah di-reset. Password sementara:
  <div class="fs-3 font-monospace my-2 user-select-all"><?php echo h($hasil['password']); ?></div>
  Serahkan langsung ke user yang bersangkutan (jangan lewat grup/chat umum). Password ini <strong>hanya ditampilkan
  sekali</strong>. Saat login dengan password sementara, user langsung diminta membuat password baru (minimal 8 karakter).
</div>
<?php endif; ?>

<div class="card card-ni mw-100">
  <div class="card-header">Daftar User</div>
  <div class="card-body">
    <form method="get" action="reset_password.php" class="row g-2 align-items-end">
      <div class="col-sm-6">
        <label class="form-label" for="cari">Cari User Name / Nama / Level / Unit</label>
        <input type="text" name="cari" id="cari" value="<?php echo h($cari); ?>" class="form-control">
      </div>
      <div class="col-sm-6 d-flex gap-2">
        <button type="submit" class="btn btn-ni">Cari</button>
        <?php if ($cari !== ''): ?><a href="reset_password.php" class="btn btn-outline-secondary">Tampilkan Semua</a><?php endif; ?>
      </div>
    </form>
    <div class="small text-muted mt-3"><?php echo count($daftar); ?> user<?php echo $cari !== '' ? ' cocok dengan "' . h($cari) . '"' : ''; ?></div>
    <div class="table-responsive mt-1" style="max-height: 28rem; overflow-y: auto;">
      <table class="table table-sm table-bordered table-ni align-middle">
        <thead class="sticky-top"><tr><th>User Name</th><th>Nama</th><th>Level</th><th>Unit</th><th>MFA</th><th></th></tr></thead>
        <tbody>
          <?php if (!$daftar): ?><tr><td colspan="6" class="text-center text-muted">Tidak ditemukan</td></tr><?php endif; ?>
          <?php foreach ($daftar as $d): ?>
          <tr>
            <td><?php echo h($d['userid']); ?></td><td><?php echo h($d['nama']); ?></td>
            <td><?php echo h($d['level']); ?></td><td><?php echo h($d['kode_branch']); ?></td>
            <td><?php echo $d['mfa_didaftar'] ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Belum daftar</span>'; ?></td>
            <td class="text-center"><a class="btn btn-sm btn-outline-primary" href="reset_password.php?u=<?php echo h(urlencode($d['userid'])); ?>">Pilih</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($u !== '' && !$user): ?>
<div class="alert alert-warning mt-3">User <?php echo h($u); ?> tidak ditemukan.</div>
<?php elseif ($user && !$hasil): ?>
<form method="post" action="reset_password.php" class="card card-ni mw-100 mt-3"
      onsubmit="if (!confirm('Reset password user <?php echo h($user['userid']); ?>? Password lama tidak berlaku lagi dan sesi user yang sedang login akan diputus.')) return false; this.querySelector('button[type=submit]').disabled = true;">
  <div class="card-header">Reset Password <?php echo h($user['userid'] . ' - ' . $user['nama']); ?></div>
  <div class="card-body">
    <p class="small mb-3">Level <strong><?php echo h($user['level']); ?></strong> · Unit <?php echo h($user['kode_branch']); ?></p>
    <?php if (strcasecmp($user['userid'], $admin) === 0): ?>
    <div class="alert alert-warning mb-0">Ini akun Anda sendiri. Ganti password lewat menu Ganti Password.</div>
    <?php else: ?>
    <input type="hidden" name="u" value="<?php echo h($user['userid']); ?>">
    <input type="hidden" name="token" value="<?php echo h($_SESSION['token_reset_password']); ?>">
    <?php /* reset=1 lewat hidden input: nilai tombol submit bisa hilang saat onsubmit menonaktifkan tombol (sebagian browser), akibatnya handler reset tidak jalan */ ?>
    <input type="hidden" name="reset" value="1">
    <label class="form-label" for="alasan">Alasan Reset <span class="text-danger">*</span></label>
    <textarea name="alasan" id="alasan" rows="2" maxlength="255" class="form-control" required placeholder="mis. user lupa password, konfirmasi langsung/telepon ke ..."></textarea>
    <div class="form-text">Pastikan permintaan benar dari user yang bersangkutan. Sistem membuat password sementara acak;
      user wajib menggantinya saat login berikutnya.</div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" name="reset" value="1" class="btn btn-danger">Reset Password</button>
      <a href="reset_password.php" class="btn btn-outline-secondary">Batal</a>
    </div>
    <?php endif; ?>
  </div>
</form>

<form method="post" action="reset_password.php" class="card card-ni mw-100 mt-3"
      onsubmit="if (!confirm('Reset MFA user <?php echo h($user['userid']); ?>? Authenticator lama tidak berlaku lagi dan sesi user yang sedang login akan diputus.')) return false; this.querySelector('button[type=submit]').disabled = true;">
  <div class="card-header">Reset MFA <?php echo h($user['userid'] . ' - ' . $user['nama']); ?></div>
  <div class="card-body">
    <p class="small mb-3">Status MFA:
      <?php if ($mfa): ?>
      <span class="badge text-bg-success">Aktif</span> didaftarkan <?php echo h($mfa['didaftar']); ?>,
      terakhir masuk <?php echo h($mfa['terakhir_masuk'] ?: '-'); ?><?php echo (int) $mfa['terkunci'] === 1 ? ', <strong class="text-danger">terkunci sampai ' . h($mfa['kunci_sampai']) . '</strong>' : ''; ?>
      <?php else: ?>
      <span class="badge text-bg-secondary">Belum daftar</span> (user memindai QR code saat login berikutnya)
      <?php endif; ?>
    </p>
    <?php if (strcasecmp($user['userid'], $admin) === 0): ?>
    <div class="alert alert-warning mb-0">Ini akun Anda sendiri. MFA akun sendiri tidak bisa di-reset di sini.</div>
    <?php elseif ($mfa): ?>
    <input type="hidden" name="u" value="<?php echo h($user['userid']); ?>">
    <input type="hidden" name="token" value="<?php echo h($_SESSION['token_reset_password']); ?>">
    <input type="hidden" name="reset_mfa" value="1">
    <label class="form-label" for="alasan_mfa">Alasan Reset MFA <span class="text-danger">*</span></label>
    <textarea name="alasan" id="alasan_mfa" rows="2" maxlength="255" class="form-control" required placeholder="mis. HP hilang / ganti HP, konfirmasi langsung/telepon ke ..."></textarea>
    <div class="form-text">Pastikan permintaan benar dari user yang bersangkutan. Sesudah di-reset, siapa pun yang tahu
      password user ini bisa mendaftarkan authenticator baru, jadi minta user segera login. Perangkat yang "diingat"
      user (login tanpa kode selama 30 hari) ikut tidak berlaku.</div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" name="reset_mfa" value="1" class="btn btn-danger">Reset MFA</button>
      <a href="reset_password.php" class="btn btn-outline-secondary">Batal</a>
    </div>
    <?php endif; ?>
  </div>
</form>
<?php endif; ?>

<div class="card card-ni mw-100 mt-3">
  <div class="card-header">Riwayat Reset Password <?php echo $user ? h($user['userid']) : '(50 terakhir)'; ?></div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm table-bordered table-ni align-middle mb-0">
        <thead><tr><th>Waktu</th><th>Admin</th><th>User</th><th>Jenis</th><th>Alasan</th><th>IP</th></tr></thead>
        <tbody>
          <?php if (!$riwayat): ?><tr><td colspan="6" class="text-center text-muted">Belum ada reset</td></tr><?php endif; ?>
          <?php foreach ($riwayat as $r): ?>
          <tr>
            <td class="text-nowrap"><?php echo h($r['waktu']); ?></td><td><?php echo h($r['admin']); ?></td>
            <td><a href="reset_password.php?u=<?php echo h(urlencode($r['userid'])); ?>"><?php echo h($r['userid']); ?></a></td>
            <td><?php echo h($r['jenis']); ?></td>
            <td><?php echo h($r['alasan']); ?></td><td><?php echo h($r['ip']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
halaman_akhir();
