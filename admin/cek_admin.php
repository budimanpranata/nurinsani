<?php
// Hanya user level admin yang boleh membuka halaman di folder admin.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['userid']) || !isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    // kembali ke halaman login aplikasi (folder di atas /admin/)
    $script = $_SERVER['SCRIPT_NAME'];
    header('location:' . substr($script, 0, strrpos($script, '/admin/')) . '/index.html');
    exit;
}
