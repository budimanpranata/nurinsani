<?php require_once __DIR__ . '/../../koneksi_db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('fund', 'admin');
/**
 * Simpan Edit Data Master Deposan (dari edit.php): ubah nama, KTP, norek tujuan, bank, tanggal lahir, alamat, telepon
 * deposan WHERE no; lalu alert dan kembali ke daftar, sama dengan versi lama.
 * Dimigrasi 2026-11-04: wajib login (dulu tanpa cek sendiri), prepared statement. Perintah user 2026-11-04: pasangan
 * di tabel anggota (no sama, dibuat oleh + Data Perorangan) ikut diubah: nama, tanggal lahir, alamat, telepon, KTP
 * (deposan perusahaan tidak punya pasangan anggota, jadi tidak ada yang berubah di sana).
 * Enkripsi (PEKERJAAN_TERTUNDA.md bagian 87): NIK dan alamat disimpan terenkripsi + ID_NUMBER_IDX di deposan dan anggota.
 */
require_once __DIR__ . '/../../inc/cek_login.php';
require_once __DIR__ . '/../../inc/enkripsi.php';
wajib_login();

if (isset($_POST['update'])) {
    $pos = function ($k) {
        return isset($_POST[$k]) ? (string) $_POST[$k] : '';
    };
    $nim = $pos('nim');
    $ktp = trim($pos('ktp'));
    if (!nik_angka_sah($ktp)) {
        // perintah user 2026-11-04: Nomor KTP hanya angka, maksimal 16 digit; kembali ke form (isian tidak hilang)
        echo "<script>alert('Nomor KTP harus angka, maksimal 16 digit.'); history.back();</script>";
        exit;
    }
    $ktp_enc = enkripsi_data($ktp);
    $ktp_idx = indeks_nik($ktp);
    $alamat_enc = enkripsi_data(potong_data($pos('alamat'), 100));
    $update = db_jalankan("UPDATE deposan SET CUST_SHORT_NAME=?,ID_NUMBER=?,ID_NUMBER_IDX=?,norek_tujuan=?,nama_bank=?,BIRTH_DATE=?, HADDRESS1=?, MOBILE_PHONE=? WHERE no=?",
        'sssssssss', $pos('nama'), $ktp_enc, $ktp_idx, $pos('norek_tujuan'), $pos('bank'), $pos('tanggal_lahir'), $alamat_enc, $pos('notelp'), $nim);
    if ($update) {
        $update = db_jalankan("UPDATE anggota SET CUST_SHORT_NAME=?, BIRTH_DATE=?, HADDRESS1=?, MOBILE_PHONE=?, ID_NUMBER=?, ID_NUMBER_IDX=? WHERE no=?",
            'sssssss', $pos('nama'), $pos('tanggal_lahir'), $alamat_enc, $pos('notelp'), $ktp_enc, $ktp_idx, $nim);
    }
    if ($update) {
        echo "<script>alert('Data Berhasil di Update!'); window.location = '../view/master_fund.php'</script>";
    } else {
        echo "<script>alert('Data Gagal di Update!'); window.location = '../view/master_fund.php'</script>";
    }
}
