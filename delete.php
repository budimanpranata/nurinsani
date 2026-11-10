<?php require_once __DIR__ . '/koneksi_db.php';
require_once __DIR__ . '/inc/notif.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit');

// host/user/password mobcol hanya di koneksi_db.php (variabel lama yang tidak terpakai dibuang 2026-11-05)
$config_dua =  db_mobcol();
if(mysqli_connect_errno())
{
echo'Gagal :'.mysqli_connect_error();
}


$cif = isset($_GET['cif']) ? (string) $_GET['cif'] : '';
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';

// tabel tagihan per jenis; cif dikirim sebagai parameter (prepared statement)
$tabel_tagihan = array(
    'lima' => 'tagihan_lima_persen',
    'lebaran' => 'tagihan_lebaran',
    'pelunasan' => 'tagihan_pelunasan',
    'penarikan' => 'tagihan_penarikan',
    'pelunasan19' => 'tagihan_pelunasan',
    'pelunasanDO' => 'tagihan_pelunasan',
);
if (isset($tabel_tagihan[$jenis])) {
    $t = $tabel_tagihan[$jenis];
    @db_hasil_di($config_dua, "delete pull_data, $t from pull_data inner join $t on $t.cif = pull_data.cif  where pull_data.cif = ? ", 's', $cif);
}


notif_pergi("hapus pull data $cif sukses ", 'mob_pull_data.php', 'success');

?>
