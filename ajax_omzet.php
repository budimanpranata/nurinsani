<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit', 'json');
/**
 * Dahulu salinan identik ajax/ajax_omzet.php; kini meneruskan ke sana agar alamat lama tetap berfungsi.
 * Pengaman login ada di ajax/ajax_omzet.php (wajib_login('json')).
 */
require __DIR__ . '/ajax/ajax_omzet.php';
