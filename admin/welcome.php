<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Home (level admin): sambutan untuk user yang login.
 * Dimigrasi 2026-11-05 (perintah user), pola kp/welcome.php: isi sama dengan versi lama; hanya tampilan (Bootstrap 5)
 * dan query (prepared statement) yang berubah. Hanya level admin (cek_admin.php).
 */
require_once __DIR__ . '/cek_admin.php';
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
require_once __DIR__ . '/../inc/tampilan.php';

$users = db_semua("SELECT * FROM user WHERE userid=?", 's', (string) $_SESSION['userid']);

halaman_awal('Home');
?>
<?php foreach ($users as $row): ?>
<div class="card card-ni mb-3">
  <div class="card-body">
    <h1 class="h5 mb-1">Selamat Datang <?php echo h($row['nama']); ?></h1>
    <div class="text-muted small">Aplikasi Nurinsani</div>
  </div>
</div>
<?php endforeach; ?>
<?php
halaman_akhir();
