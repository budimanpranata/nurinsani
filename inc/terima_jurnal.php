<?php
/**
 * Penerima jurnal dari server lain (2026-11-21, perintah user, PEKERJAAN_TERTUNDA §103 P1-1), dipakai oleh:
 *   api/transaksi.php      -> tabel_transaksi_hris (server HRIS 192.168.88.140)
 *   api/transaksi_cms.php  -> tabel_transaksi_cms  (server CMS  192.168.88.145)
 * Baris tersebut disalin ke tabel_transaksi oleh cron api/omzet.php (23:40), tidak berubah.
 *
 * Perilaku dipertahankan seperti versi lama: URL ?function=insert_transaksi, field POST sama, nilai disimpan apa adanya
 * (field yang tidak dikirim = ''), jawaban JSON {"status":1,"message":"Insert Success"} / {"status":0,"message":"Insert Failed."}.
 * Yang diperbaiki:
 *   - dulu `$_GET['function']()` memanggil fungsi PHP APA PUN (mis. ?function=phpinfo); kini hanya insert_transaksi;
 *   - dulu field POST ditempel ke SQL (injeksi); kini prepared statement;
 *   - hanya IP server pengirim (+ localhost) yang diterima, di sini dan di api/.htaccess. File ini dikecualikan dari
 *     wajib login (inc/penjaga_sesi.php) karena server pengirim tidak punya sesi login.
 */

/** IP yang boleh mengirim, per file. */
function terima_jurnal_ip_boleh($ip, array $ip_pengirim)
{
    return in_array($ip, array_merge(array('127.0.0.1', '::1'), $ip_pengirim), true);
}

function terima_jurnal_jawab($status, $pesan, $kode_http = 200)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code($kode_http);
        header('Content-Type: application/json');
    }
    echo json_encode(array('status' => $status, 'message' => $pesan));
    exit;
}

/** $tabel: 'tabel_transaksi_hris' atau 'tabel_transaksi_cms'. */
function terima_jurnal($tabel, array $ip_pengirim)
{
    if (!in_array($tabel, array('tabel_transaksi_hris', 'tabel_transaksi_cms'), true)) {
        terima_jurnal_jawab(0, 'Insert Failed.', 500);
    }
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    if (!terima_jurnal_ip_boleh($ip, $ip_pengirim)) {
        terima_jurnal_jawab(0, 'Forbidden', 403);
    }
    $fungsi = isset($_GET['function']) ? (string) $_GET['function'] : '';
    if ($fungsi !== 'insert_transaksi') {
        // versi lama: nama fungsi lain dijalankan begitu saja (celah); kini ditolak
        terima_jurnal_jawab(0, 'Function tidak dikenal.', 400);
    }

    $kolom = array('unit', 'id_transaksi', 'kode_transaksi', 'kode_rekening', 'tanggal_transaksi', 'jenis_transaksi',
        'keterangan_transaksi', 'debet', 'kredit', 'tanggal_posting', 'keterangan_posting', 'id_admin');
    $nilai = array();
    foreach ($kolom as $k) {
        $v = isset($_POST[$k]) ? $_POST[$k] : '';
        $nilai[] = is_scalar($v) ? (string) $v : '';
    }
    $db = db_ni();
    $sql = "INSERT INTO `$tabel` SET " . implode(', ', array_map(function ($k) {
        return "$k = ?";
    }, $kolom));
    $ok = false;
    try {
        $st = $db->prepare($sql);
        $ok = $st && $st->bind_param(str_repeat('s', count($kolom)), ...$nilai) && $st->execute();
    } catch (Throwable $e) {
        $ok = false;
    }
    if ($ok) {
        terima_jurnal_jawab(1, 'Insert Success');
    }
    terima_jurnal_jawab(0, 'Insert Failed.');
}
