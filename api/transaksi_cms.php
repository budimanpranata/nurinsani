<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Penerima jurnal dari server CMS (POST ?function=insert_transaksi) ke tabel_transaksi_cms.
 * Diperbaiki 2026-11-21 (§103 P1-1): hanya insert_transaksi, prepared statement, hanya IP CMS. Lihat inc/terima_jurnal.php.
 */
require_once __DIR__ . '/../inc/terima_jurnal.php';
terima_jurnal('tabel_transaksi_cms', array('192.168.88.145'));
