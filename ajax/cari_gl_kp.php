<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Saran rekening GL kantor pusat (tabel_master unit '1010') untuk halaman level kp. Query sama dengan versi lama:
 *   tanpa kolom / kolom=kode_rekening : awalan kode_rekening (pengganti kp/autosuggest_kp_kp.php, transaksi simpanan)
 *   kolom=rekening_gl                 : awalan rekening_gl   (pengganti kp/autosuggesst.php, jurnal kas kp)
 *   kolom=bank                        : awalan kode_rekening, hanya gr_sub 1310000/1330000
 *                                       (pengganti kp/autosuggest_kp.php, Daily Deposito kp)
 * GET q = awalan. Hasil: JSON [{rekening_gl, nama_rekening, label}]
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login('json');
header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? (string) $_GET['q'] : '';
if (strlen($q) == 0) {
    echo '[]';
    exit;
}
$kolom = isset($_GET['kolom']) && in_array($_GET['kolom'], array('rekening_gl', 'bank'), true) ? $_GET['kolom'] : 'kode_rekening';
if ($kolom === 'rekening_gl') {
    $sql = "SELECT nama_rekening,rekening_gl FROM tabel_master WHERE rekening_gl LIKE ? and unit ='1010'";
} elseif ($kolom === 'bank') {
    $sql = "SELECT nama_rekening, rekening_gl,kode_rekening FROM tabel_master WHERE kode_rekening LIKE ? and unit ='1010' and gr_sub in ('1310000','1330000')";
} else {
    $sql = "SELECT nama_rekening, rekening_gl,kode_rekening FROM tabel_master WHERE kode_rekening LIKE ? and unit ='1010'";
}
$hasil = array();
foreach (db_semua($sql, 's', $q . '%') as $b) {
    $hasil[] = array(
        'rekening_gl' => (string) $b['rekening_gl'],
        'nama_rekening' => (string) $b['nama_rekening'],
        'label' => $b['rekening_gl'] . '  ' . $b['nama_rekening'],
    );
}
echo json_encode($hasil);
