<?php
/**
 * Kerangka halaman bersama (Bootstrap 5 + assets/app).
 *
 *   require_once __DIR__ . '/inc/tampilan.php';
 *   halaman_awal('Pengajuan');
 *   ... isi halaman ...
 *   halaman_akhir();
 */

/** URL relatif terhadap root aplikasi, mis. url_app('assets/app/app.css'). */
function url_app($path = '')
{
    static $base = null;
    if ($base === null) {
        $root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
        $app = str_replace('\\', '/', dirname(__DIR__));
        $base = substr($app, strlen($root)) . '/';
    }
    return $base . ltrim($path, '/');
}

if (!function_exists('h')) {
    /** Escape teks untuk ditampilkan di HTML. */
    function h($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}

function halaman_awal($judul)
{
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo h($judul); ?></title>
<link rel="stylesheet" href="<?php echo h(url_app('assets/vendor/bootstrap-5.3.3/css/bootstrap.min.css')); ?>">
<link rel="stylesheet" href="<?php echo h(url_app('assets/app/app.css')); ?>">
</head>
<body>
<div class="judul-halaman"><?php echo h($judul); ?></div>
<main class="isi-halaman">
<?php
}

/** $script: JS tambahan khusus halaman, mis. array('assets/app/pengajuan.js'). */
function halaman_akhir($script = array())
{
    ?>
</main>
<script src="<?php echo h(url_app('assets/vendor/bootstrap-5.3.3/js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo h(url_app('assets/app/app.js')); ?>"></script>
<?php foreach ($script as $js): ?>
<script src="<?php echo h(url_app($js)); ?>"></script>
<?php endforeach; ?>
</body>
</html>
<?php
}
