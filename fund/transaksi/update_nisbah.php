<?php require_once __DIR__ . '/../../koneksi_db.php';
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
        echo "<script>alert('Data Berhasil di Update!'); window.location = '../view/master_trans.php'</script>";
    } else {
        echo "<script>alert('Data Gagal di Update!'); window.location = '../view/master_trans.php'</script>";
    }
}
