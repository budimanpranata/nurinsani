<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Langkah kedua login (MFA), wajib untuk SEMUA user (perintah user 2026-11-09, PEKERJAAN_TERTUNDA.md bagian 101).
 *   log.php?op=in  password benar -> $_SESSION['mfa_tunggu'] = userid + waktu (BELUM login: belum ada $_SESSION['userid'])
 *   mfa.php        user belum terdaftar: tampil QR code + kunci, user memindai lalu memasukkan kode pertama (pendaftaran)
 *                  sudah terdaftar: masukkan kode 6 digit dari aplikasi authenticator
 *   kode benar  -> $_SESSION['mfa_lolos'] lalu log.php?op=lanjut (pemeriksaan & pengalihan per level seperti dulu)
 * Halaman ini terdaftar di penjaga_sesi_publik() (dibuka sebelum ada sesi login); pemeriksaannya sendiri di bawah.
 * Batas: MFA_BATAS_TUNGGU detik setelah password benar; MFA_MAKS_GAGAL kode salah -> dikunci MFA_LAMA_KUNCI detik.
 */
require_once __DIR__ . '/inc/mfa.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Cache-Control: no-store');

function mfa_ke_login($pesan)
{
    unset($_SESSION['mfa_tunggu'], $_SESSION['mfa_token']);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Login</title></head><body><script>';
    if ($pesan !== '') {
        echo 'alert(' . json_encode($pesan) . ');';
    }
    echo "top.location.href='index.html';</script></body></html>";
    exit;
}

if (isset($_SESSION['userid']) && $_SESSION['userid'] !== '') {
    mfa_ke_login('Maaf User Sedang Login ');
}
if (isset($_GET['batal'])) {
    mfa_ke_login('');
}
$tunggu = isset($_SESSION['mfa_tunggu']) && is_array($_SESSION['mfa_tunggu']) ? $_SESSION['mfa_tunggu'] : null;
if (!$tunggu) {
    mfa_ke_login('');
}
if (time() - (int) $tunggu['waktu'] > MFA_BATAS_TUNGGU) {
    mfa_ke_login('Waktu memasukkan kode habis, silakan login ulang.');
}
$userid = (string) $tunggu['userid'];
$user = db_satu("select userid, nama from user where userid = ?", 's', $userid);
if (!$user) {
    mfa_ke_login('Maaf User name atau password salah');
}
if (empty($_SESSION['mfa_token'])) {
    $_SESSION['mfa_token'] = bin2hex(random_bytes(16));
}

$data = mfa_data($userid);
$daftar = $data === null;
if ($daftar && empty($tunggu['rahasia_baru'])) {
    // kunci baru hanya di sesi sampai kode pertama terbukti cocok (belum disimpan ke database)
    $tunggu['rahasia_baru'] = mfa_rahasia_baru();
    $tunggu['gagal_daftar'] = 0;
    $_SESSION['mfa_tunggu'] = $tunggu;
}

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = isset($_POST['kode']) ? (string) $_POST['kode'] : '';
    if (!hash_equals($_SESSION['mfa_token'], isset($_POST['token']) ? (string) $_POST['token'] : '')) {
        $pesan = 'Formulir kedaluwarsa, silakan masukkan kode lagi.';
    } elseif ($daftar) {
        $langkah = mfa_cocok($tunggu['rahasia_baru'], $kode, 0);
        if ($langkah !== false) {
            mfa_daftar($userid, $tunggu['rahasia_baru'], $langkah);
            $lolos = true;
        } else {
            $tunggu['gagal_daftar'] = (int) $tunggu['gagal_daftar'] + 1;
            $_SESSION['mfa_tunggu'] = $tunggu;
            if ($tunggu['gagal_daftar'] >= MFA_MAKS_GAGAL) {
                mfa_ke_login('Kode salah ' . MFA_MAKS_GAGAL . ' kali. Silakan login ulang dan pindai QR code yang baru.');
            }
            $pesan = 'Kode salah. Pastikan jam HP otomatis (sesuai jaringan) dan masukkan kode yang sedang tampil.';
        }
    } elseif ((int) $data['terkunci'] === 1) {
        mfa_ke_login('Terlalu banyak kode salah. Coba lagi sesudah ' . substr((string) $data['kunci_sampai'], 11, 5) . '.');
    } else {
        $langkah = mfa_cocok($data['rahasia'], $kode, $data['langkah_terakhir']);
        if ($langkah !== false) {
            mfa_berhasil($userid, $langkah);
            $lolos = true;
        } elseif (mfa_gagal($userid)) {
            mfa_ke_login('Terlalu banyak kode salah. Akun dikunci ' . (MFA_LAMA_KUNCI / 60) . ' menit.');
        } else {
            $pesan = 'Kode salah atau sudah dipakai. Tunggu kode berikutnya lalu coba lagi.';
        }
    }
    if (!empty($lolos)) {
        session_regenerate_id(true);
        unset($_SESSION['mfa_tunggu'], $_SESSION['mfa_token']);
        $_SESSION['mfa_lolos'] = array('userid' => $user['userid'], 'waktu' => time());
        header('Location: log.php?op=lanjut');
        exit;
    }
}
$h = function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verifikasi Login · KSPPS Nur Insani</title>
<link rel="stylesheet" href="assets/vendor/bootstrap-5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/app/app.css">
<link rel="stylesheet" href="assets/app/login.css">
<script>if (top != self) { top.location = self.location; }</script>
</head>
<body class="halaman-login">
<main class="login-bungkus">
  <section class="login-panel w-100">
    <div class="login-kartu">
      <img class="login-logo" src="Image/ni.png" alt="KSPPS Nur Insani">
      <?php if ($daftar): ?>
      <h2>Aktifkan verifikasi 2 langkah</h2>
      <p class="text-body-secondary mb-3">User <strong><?php echo $h($user['userid'] . ' - ' . $user['nama']); ?></strong>
        wajib memakai kode dari aplikasi authenticator setiap login.</p>
      <ol class="small ps-3 mb-3">
        <li>Pasang <strong>Google Authenticator</strong> (atau Microsoft Authenticator) di HP Anda.</li>
        <li>Buka aplikasinya, pilih <strong>+</strong> lalu <strong>Pindai kode QR</strong>, arahkan ke gambar di bawah.</li>
        <li>Ketik 6 digit kode yang muncul di aplikasi.</li>
      </ol>
      <div class="text-center mb-2">
        <div id="mfa-qr" class="d-inline-block p-2 bg-white border rounded" data-uri="<?php echo $h(mfa_uri($user['userid'], $tunggu['rahasia_baru'])); ?>"></div>
      </div>
      <p class="small text-center mb-3">Tidak bisa memindai? Pilih <em>Masukkan kunci penyiapan</em> dan ketik kunci ini
        (jenis: <em>Berbasis waktu</em>):<br>
        <span class="font-monospace fw-semibold user-select-all"><?php echo $h(mfa_rahasia_tampil($tunggu['rahasia_baru'])); ?></span></p>
      <?php else: ?>
      <h2>Verifikasi 2 langkah</h2>
      <p class="text-body-secondary mb-3">Buka aplikasi authenticator di HP Anda dan masukkan 6 digit kode untuk
        <strong><?php echo $h(MFA_PENERBIT . ' (' . $user['userid'] . ')'); ?></strong>.</p>
      <?php endif; ?>

      <?php if ($pesan !== ''): ?>
      <div class="alert alert-danger py-2 small" role="alert"><?php echo $h($pesan); ?></div>
      <?php endif; ?>

      <form action="mfa.php" method="post" id="form-mfa" autocomplete="off">
        <input type="hidden" name="token" value="<?php echo $h($_SESSION['mfa_token']); ?>">
        <div class="form-floating mb-2">
          <input class="form-control text-center fs-4 font-monospace" name="kode" id="kode" type="text"
                 inputmode="numeric" pattern="[0-9 ]{6,7}" maxlength="7" autocomplete="one-time-code"
                 placeholder="Kode 6 digit" required autofocus>
          <label for="kode">Kode 6 digit</label>
        </div>
        <button id="kirim-mfa" class="btn btn-ni btn-lg w-100 mt-2" type="submit">
          <span class="teks-tombol"><?php echo $daftar ? 'Aktifkan & Masuk' : 'Verifikasi'; ?></span>
          <span class="spinner-border spinner-border-sm ms-2" aria-hidden="true" hidden></span>
        </button>
      </form>
      <p class="small text-center mt-3 mb-0"><a href="mfa.php?batal=1">Batal, kembali ke login</a></p>
      <p class="small text-body-secondary text-center mt-2 mb-0">HP hilang / ganti HP? Hubungi admin untuk Reset MFA.</p>
    </div>
  </section>
</main>
<?php if ($daftar): ?>
<script src="assets/vendor/qrcode-generator-1.4.4/qrcode.min.js"></script>
<?php endif; ?>
<script src="assets/app/mfa.js?v=<?php echo (int) @filemtime(__DIR__ . '/assets/app/mfa.js'); ?>"></script>
</body>
</html>
