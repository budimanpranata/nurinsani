<?php
/**
 * Proses batch (EOD) hanya boleh dijalankan dari cron / command line (php CLI), tidak lewat web.
 * Dipasang di baris pertama setiap proses_eod*.php dan file api/ yang dijalankan cron, SEBELUM koneksi_db.php,
 * supaya tidak ada query yang sempat berjalan bila file dibuka lewat browser (menjalankan ulang EOD di hari yang
 * sama mencatat potongan/jurnal ganda). Lihat PEKERJAAN_TERTUNDA.md bagian 42.
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied: CLI execution only.');
}
// php.ini CLI bisa tanpa date.timezone (bawaan UTC): cron antara 00.00-07.00 WIB akan memakai tanggal kemarin di date().
// Disamakan ke WIB untuk semua proses cron (2026-11-05, perintah user); file yang sudah menyetel sendiri tetap sama.
date_default_timezone_set('Asia/Jakarta');
