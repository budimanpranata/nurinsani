<?php require_once __DIR__ . '/../koneksi_db.php';
/**
 * Simpan/hapus untuk menu kp > Koreksi (view_koreksi.php). POST aksi:
 *   get   : satu baris tabel_transaksi (JSON) untuk form Edit
 *   edit  : ubah debet, kredit, kode_rekening, tanggal_transaksi satu baris tabel_transaksi
 *   hapus : hapus satu baris tabel_transaksi
 *   tambah: sisa versi lama (insert ke omzet), tidak dipanggil dari halaman
 * Jawaban "ok" sama dengan versi lama (juga bila query gagal, seperti versi lama).
 * Dimigrasi 2026-11-03: dulu tanpa cek login dan query disusun dari isian (rawan SQL injection); kini wajib login,
 * hanya level kp (perintah user 2026-11-03), dan prepared statement.
 */
session_start();
require_once __DIR__ . '/../inc/cek_login.php';
wajib_login('json');
// hanya level kp (perintah user 2026-11-03; dulu tanpa cek apa pun)
if ($_SESSION['level'] != 'kp') {
    http_response_code(403);
    echo "Anda bukan user";
    exit;
}

$aksi = isset($_POST['aksi']) ? (string) $_POST['aksi'] : '';
$isi = function ($kunci) {
    return isset($_POST[$kunci]) ? (string) $_POST[$kunci] : '';
};
if ($aksi == 'tambah') {
    db_jalankan("INSERT INTO omzet (tanggal, cif, nominal) VALUES (?, ?, ?)", 'sss', $isi('tanggal'), $isi('cif'), $isi('nominal'));
    echo "ok";
} elseif ($aksi == 'edit') {
    db_jalankan("UPDATE tabel_transaksi SET debet=?, kredit=?, kode_rekening=?, tanggal_transaksi=? WHERE id_transaksi=?", 'sssss',
        $isi('debet'), $isi('kredit'), $isi('kode_rekening'), $isi('tanggal'), $isi('id'));
    echo "ok";
} elseif ($aksi == 'hapus') {
    db_jalankan("DELETE FROM tabel_transaksi WHERE id_transaksi=?", 's', $isi('id'));
    echo "ok";
} elseif ($aksi == 'get') {
    $data = db_satu("SELECT id_transaksi as id, DATE(tanggal_transaksi) as tanggal,unit, kode_transaksi, kode_rekening, jenis_transaksi, keterangan_transaksi, debet,kredit
        from tabel_transaksi WHERE id_transaksi=?", 's', $isi('id'));
    // nilai sebagai teks, sama dengan versi lama (mysql_* mengembalikan string)
    echo json_encode($data ? array_map(function ($v) { return $v === null ? null : (string) $v; }, $data) : null);
}
