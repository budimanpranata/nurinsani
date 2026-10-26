<?php
// Konfigurasi & koneksi database ada di koneksi_db.php
require_once __DIR__ . '/koneksi_db.php';

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$conn = db_ni();
