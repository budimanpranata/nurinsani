<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * PDF Rugi Laba unit (tombol PDF di New Report > Rugi Laba, laporan/new_laba.php), dibuat 2026-11-21 atas perintah user
 * (PEKERJAAN_TERTUNDA §32 A2). Dulu tombol PDF membuka neraca_pdf.php (PDF Neraca).
 * Isi dan perhitungan sama dengan layar new_laba.php (inc/rugi_laba_pdf.php). Hanya membaca.
 */
date_default_timezone_set('Asia/Jakarta');
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_level('unit');
ob_start();
require_once __DIR__ . '/../inc/rugi_laba_pdf.php';
$u = db_satu("select kode_branch from user where userid=?", 's', (string) $_SESSION['userid']);
rugi_laba_pdf($u ? $u['kode_branch'] : '', false);
