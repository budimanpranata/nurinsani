<?php require_once __DIR__ . '/../../koneksi_db.php';
require_once __DIR__ . '/../../inc/notif.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('fund', 'admin');
/**
 * Simpan Edit nisbah deposito (dari edit_nisbah.php): eq_rate, rasio, norek_bank, nama_bank, cara_hitung, dan
 * status_print 'ADVIS' WHERE norek; lalu alert dan kembali ke daftar, sama dengan versi lama.
 * Dimigrasi 2026-11-04: wajib login, prepared statement (dulu or die(mysqli_error()) = Fatal error PHP 8 bila gagal).
 * (H) gagal kini kembali ke ../view/master_trans.php (dulu ke '../view/master_fund.trans', salah ketik).
 */
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_login();

if (isset($_POST['update'])) {
    $pos = function ($k) {
        return isset($_POST[$k]) ? trim((string) $_POST[$k]) : '';
    };
    $update = db_jalankan("UPDATE deposito SET eq_rate=?,rasio=?,norek_bank=?,nama_bank=?,cara_hitung=?, status_print ='ADVIS' WHERE norek =?",
        'ssssss', $pos('eq_rate'), $pos('rasio'), $pos('norek_bank'), $pos('nama_bank'), $pos('cara_hitung'), $pos('norek'));
    if ($update) {
        notif_pergi('Data Berhasil di Update!', '../view/master_trans.php');
    } else {
        notif_pergi('Data Gagal di Update!', '../view/master_trans.php');
    }
}
