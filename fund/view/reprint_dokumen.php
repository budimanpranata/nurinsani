<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * Menyajikan dokumen surat kehilangan reprint (?id=<id dep_reprint>) dari load/reprint/ untuk user level fund yang
 * login. Folder itu ditolak untuk akses web langsung (load/reprint/.htaccess).
 */
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();
if ((isset($_SESSION['level']) ? $_SESSION['level'] : '') != "admin") {
    http_response_code(403);
    exit;
}

$id = isset($_GET['id']) && preg_match('/^\d+$/', (string) $_GET['id']) ? (string) $_GET['id'] : '';
$row = $id === '' ? null : db_satu("SELECT image FROM image WHERE name=?", 's', 'REPRINT ' . $id);
$nama = $row ? basename((string) $row['image']) : '';
$berkas = __DIR__ . '/../../load/reprint/' . $nama;
if ($nama === '' || !preg_match('/^reprint_' . $id . '_\d{14}\.(jpg|png|pdf)$/', $nama, $m) || !is_file($berkas)) {
    http_response_code(404);
    exit;
}
$jenis = array('jpg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf');
header('Content-Type: ' . $jenis[$m[1]]);
header('Content-Length: ' . filesize($berkas));
header('Content-Disposition: inline; filename="' . $nama . '"');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
readfile($berkas);
