<?php
// Konfigurasi & koneksi database ada di koneksi_db.php
require_once __DIR__ . '/koneksi_db.php';

if (!function_exists('open_connection2')) {
	function open_connection2()
	{
		return db_ni();
	}
}
?>
