<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Unduh foto bukti setor bank Mobile Collection (tombol Download di mob_setoran_bank.php).
 *
 * 2026-11-19 (perintah user), dulu: tanpa cek login, nama file dari URL ditempel langsung ke alamat server mobcol
 * (bisa ../ keluar dari folder uploads), IP server tertulis di file ini, server gagal -> file kosong tanpa pesan.
 * Sekarang: wajib login level unit; file hanya boleh bila tercatat di bukti_setor milik unit user (sama dengan isi grid,
 * kode_unit = userid); alamat folder dari $DB_CONFIG['mobcol']['upload_url'] (koneksi_db.php); gagal -> notif.
 */
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit');
require_once __DIR__ . '/inc/notif.php';

if (isset($_REQUEST['file'])) {
    $file = (string) $_REQUEST['file'];
    $userid = (string) $_SESSION['userid'];

    $milik = false;
    if ($file !== '') {
        $hasil = db_hasil_di(db_mobcol(), "SELECT 1 AS ada FROM bukti_setor WHERE image = ? AND kode_unit = ? LIMIT 1", 'ss', $file, $userid);
        $milik = $hasil && db_fetch_array($hasil);
    }
    if (!$milik) {
        notif_pergi('Maaf, file bukti setor tidak ditemukan di unit Anda.', 'mob_setoran_bank.php', 'error');
        exit;
    }

    $url = isset($GLOBALS['DB_CONFIG']['mobcol']['upload_url']) ? (string) $GLOBALS['DB_CONFIG']['mobcol']['upload_url'] : '';
    $isi = $url !== '' ? @file_get_contents($url . $file) : false;
    if ($isi === false) {
        notif_pergi('Maaf, file tidak bisa diambil dari server Mobile Collection. Coba lagi nanti.', 'mob_setoran_bank.php', 'error');
        exit;
    }

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=" . basename($file));
    header("Content-Type: application/octet-stream;");
    echo $isi;
}
?>
