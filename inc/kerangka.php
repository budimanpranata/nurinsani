<?php
/**
 * Kerangka utama aplikasi sesudah login (pengganti frameset header/tree/footer).
 *
 *   require_once __DIR__ . '/inc/kerangka.php';
 *   kerangka_aplikasi(array('keluar' => 'Logout.php'));
 *
 * Menu dibaca dari tree_items.js milik folder level (sama seperti tree.html lama).
 * Halaman menu dibuka di frame bernama "frameset", sama seperti target di tree_tpl.js,
 * sehingga semua halaman lama tetap berjalan tanpa perubahan.
 */
require_once __DIR__ . '/tampilan.php';
require_once dirname(__DIR__) . '/koneksi_db.php';

/**
 * $opsi:
 *   menu   : file data menu, relatif ke halaman (default 'tree_items.js')
 *   awal   : halaman pertama di area isi (default 'semua.html', halaman kosong seperti frameset lama)
 *   beranda: halaman Home untuk logo di bar atas (default 'welcome.php')
 *   keluar : URL logout (default 'Logout.php')
 *   judul  : judul tab browser
 */
function kerangka_aplikasi($opsi = array())
{
    $opsi = array_merge(array(
        'menu' => 'tree_items.js',
        'awal' => 'semua.html',
        'beranda' => 'welcome.php',
        'keluar' => 'Logout.php',
        'judul' => 'KSPPS Nur Insani',
    ), $opsi);

    $userid = isset($_SESSION['userid']) ? (string) $_SESSION['userid'] : '';
    $u = db_satu('SELECT nama FROM user WHERE userid = ?', 's', $userid);
    $nama = $u ? (string) $u['nama'] : '';
    $level = isset($_SESSION['level']) ? (string) $_SESSION['level'] : '';
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo h($opsi['judul']); ?></title>
<link rel="stylesheet" href="<?php echo h(url_app('assets/vendor/bootstrap-5.3.3/css/bootstrap.min.css')); ?>">
<link rel="stylesheet" href="<?php echo h(url_app('assets/app/app.css')); ?>">
<link rel="stylesheet" href="<?php echo h(url_app('assets/app/kerangka.css')); ?>">
</head>
<body class="kerangka">

<header class="kerangka-atas">
  <button class="btn btn-tombol-menu" type="button" id="tombol-menu" aria-label="Buka/tutup menu" aria-controls="menu-samping">
    <span class="ikon-burger"><span></span><span></span><span></span></span>
  </button>
  <a class="kerangka-merek" href="<?php echo h($opsi['beranda']); ?>" target="frameset" data-menu-beranda>
    <img src="<?php echo h(url_app('Image/ni.png')); ?>" alt="Nur Insani">
    <span class="d-none d-sm-inline">KSPPS Nur Insani</span>
  </a>
  <nav aria-label="breadcrumb" class="kerangka-jejak">
    <ol class="breadcrumb mb-0" id="jejak-menu"><li class="breadcrumb-item active">Home</li></ol>
  </nav>
  <div class="kerangka-pengguna">
    <span class="pengguna-avatar" aria-hidden="true"><?php echo h(strtoupper(substr($nama !== '' ? $nama : $userid, 0, 1))); ?></span>
    <span class="pengguna-nama d-none d-md-inline">
      <strong><?php echo h($nama !== '' ? $nama : $userid); ?></strong>
      <small><?php echo h($userid . ($level !== '' ? ' · ' . $level : '')); ?></small>
    </span>
    <a class="btn btn-keluar" href="<?php echo h($opsi['keluar']); ?>" target="_top">Keluar</a>
  </div>
</header>

<div class="kerangka-tengah">
  <aside class="menu-samping" id="menu-samping" aria-label="Menu utama">
    <div class="menu-cari">
      <input type="search" class="form-control form-control-sm" id="cari-menu" placeholder="Cari menu…" autocomplete="off">
    </div>
    <nav class="menu-pohon" id="menu-pohon"></nav>
  </aside>
  <div class="menu-tirai" id="menu-tirai" hidden></div>

  <main class="kerangka-isi">
    <iframe name="frameset" id="frameset" src="<?php echo h($opsi['awal']); ?>" title="Isi halaman"></iframe>
  </main>
</div>

<footer class="kerangka-bawah">Copyright Nur Insani &copy; 2016</footer>

<script src="<?php echo h($opsi['menu']); ?>"></script>
<script src="<?php echo h(url_app('assets/app/menu.js')); ?>"></script>
</body>
</html>
<?php
}
