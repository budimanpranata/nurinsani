<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Home (level lead): sambutan untuk user yang login.
 * Isi sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 * Versi lama memeriksa login di akhir halaman (sesudah sambutan tampil); kini di awal.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login();
$userid = $_SESSION['userid'];

$users = db_semua("SELECT * FROM user WHERE userid=?", 's', (string) $userid);

halaman_awal('Home');
?>
<?php foreach ($users as $row): ?>
<div class="card card-ni mb-3">
  <div class="card-body">
    <h1 class="h5 mb-1">Selamat Datang <?php echo h($row['nama']); ?></h1>
    <div class="text-muted small">Di Aplikasi Sales Manajement Tools</div>
  </div>
</div>
<?php endforeach; ?>
<?php
halaman_akhir();
