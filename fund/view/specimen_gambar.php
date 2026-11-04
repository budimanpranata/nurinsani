<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menyajikan gambar specimen deposan (?cif=&slot=1|2) dari load/specimen/ untuk user yang login. Folder itu ditolak
 * untuk akses web langsung (load/specimen/.htaccess), karena specimen tanda tangan adalah data sensitif.
 */
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();

$cif = isset($_GET['cif']) ? (string) $_GET['cif'] : '';
$slot = isset($_GET['slot']) && in_array($_GET['slot'], array('1', '2'), true) ? $_GET['slot'] : '';
$row = $slot === '' ? null : db_satu("SELECT image FROM image WHERE NOPEN=? AND name=?", 'ss', $cif, 'SPECIMEN ' . $slot);
$nama = $row ? basename((string) $row['image']) : '';
$berkas = __DIR__ . '/../../load/specimen/' . $nama;
if ($nama === '' || !preg_match('/^specimen_[A-Za-z0-9]+_[12]_\d{14}\.(jpg|png)$/', $nama) || !is_file($berkas)) {
    http_response_code(404);
    exit;
}
header('Content-Type: ' . (substr($nama, -4) === '.png' ? 'image/png' : 'image/jpeg'));
header('Content-Length: ' . filesize($berkas));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
readfile($berkas);
