<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Redirect Page</title>
<META HTTP-EQUIV=Refresh CONTENT='2; URL=homeuse.php'>
<link rel="stylesheet" href="assets/vendor/bootstrap-5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/app/app.css">
<link rel="stylesheet" href="assets/app/login.css">
<style>
    .muat-kartu { text-align: center; }
    .muat-batang { height: .375rem; margin: 1.5rem 0 1rem; border-radius: 1rem; background: var(--ni-hijau-muda); overflow: hidden; }
    .muat-batang span { display: block; width: 40%; height: 100%; border-radius: 1rem; background: var(--ni-hijau); animation: muat 1.1s ease-in-out infinite; }
    @keyframes muat { 0% { transform: translateX(-100%); } 100% { transform: translateX(250%); } }
    @media (prefers-reduced-motion: reduce) { .muat-batang span { animation: none; width: 100%; } }
</style>
</head>
<body class="halaman-login">
<main class="login-panel" style="min-height:100vh">
  <div class="login-kartu muat-kartu">
    <img class="login-logo mx-auto" src="Image/ni.png" alt="KSPPS Nur Insani">
    <div class="muat-batang" role="progressbar" aria-label="Memuat"><span></span></div>
    <p class="mb-1 fw-semibold" style="color:#1f4a37">Loading...</p>
    <p class="small text-body-secondary mb-0"><a href="homeuse.php">Lanjutkan jika halaman tidak berpindah</a></p>
  </div>
</main>
</body>
</html>
