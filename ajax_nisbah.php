<?php
/**
 * Dahulu berisi query Monitoring Nisbah; kini meneruskan ke ajax/ajax_nisbah.php agar alamat lama tetap berfungsi.
 * Pengaman login ada di ajax/ajax_nisbah.php (wajib_login('json')).
 */
require __DIR__ . '/ajax/ajax_nisbah.php';
