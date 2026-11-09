<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit', 'json');
/**
 * Dahulu berisi query Monitoring Nisbah; kini meneruskan ke ajax/ajax_nisbah.php agar alamat lama tetap berfungsi.
 * Pengaman login ada di ajax/ajax_nisbah.php (wajib_login('json')).
 */
require __DIR__ . '/ajax/ajax_nisbah.php';
