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
