<?php require_once __DIR__ . '/../../koneksi_db.php';
/**
 * PDF Rugi Laba kp (tombol PDF di kp > Report > Rugi-Laba, kp/laporan/new_laba.php), dibuat 2026-11-21 atas perintah user
 * (PEKERJAAN_TERTUNDA §32 D1). Dulu tombol PDF membuka neraca_pdf.php yang tidak ada di kp/laporan (404).
 * Isi dan perhitungan sama dengan layar kp/laporan/new_laba.php (rekening ber-gr_neraca 'rugi-laba'), lewat
 * inc/rugi_laba_pdf.php. Hanya membaca.
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/../../inc/cek_login.php';
wajib_level('kp');
ob_start();
require_once __DIR__ . '/../../inc/rugi_laba_pdf.php';
$u = db_satu("select kode_branch from user where userid=?", 's', (string) $_SESSION['userid']);
rugi_laba_pdf($u ? $u['kode_branch'] : '', true);
