<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Menu Home (level kp): sambutan untuk user yang login.
 * Isi dan pemeriksaan login/level sama dengan versi lama; hanya tampilan dan query (prepared statement) yang berubah.
 */
require_once __DIR__ . '/../inc/tampilan.php';
session_start();
ob_start();

//cek apakah user sudah login
if (!isset($_SESSION['userid'])) {
    header("location:index.html");
    exit;
}
//cek level user
if ($_SESSION['level'] != "kp") {
    header("location:index.html");
    exit;
}
$userid = $_SESSION['userid'];

$users = db_semua("SELECT * FROM user WHERE userid=?", 's', (string) $userid);

halaman_awal('Home');
?>
<?php foreach ($users as $row): ?>
<div class="card card-ni mb-3">
  <div class="card-body">
    <h1 class="h5 mb-1">Selamat Datang <?php echo h($row['nama']); ?></h1>
    <div class="text-muted small">Di Aplikasi Nurinsani</div>
  </div>
</div>
<?php endforeach; ?>
<?php
halaman_akhir();
