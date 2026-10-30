<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Simpan / ubah / hapus / ambil data omzet (dipanggil view_omzet.php lewat AJAX, POST aksi=...).
 * Logika sama dengan versi lama, ditambah prepared statement, pengaman login, dan cek unit (lihat di bawah).
 * Edit juga menghitung ulang porsi nisbah anggota (nominal x nisbah) di pinjaman_detail_mus pada tanggal tersebut.
 */
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_login('json');

$aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';
$isian = function ($nama) {
    return isset($_POST[$nama]) ? (string) $_POST[$nama] : '';
};

// Perbaikan 2026-10-30 (perintah user): hanya omzet anggota unit user yang boleh ditambah/diubah/dihapus/dibaca.
// Unit = userid, sama dengan filter daftar omzet di ajax/ajax_omzet.php.
$unit = (string) $_SESSION['userid'];
$cif_unit = function ($cif) use ($unit) {
    return (bool) db_satu('SELECT CIF FROM anggota WHERE CIF = ? AND UNIT = ?', 'ss', (string) $cif, $unit);
};
$omzet_unit = function ($id) use ($unit) {
    return (bool) db_satu('SELECT omzet.id FROM omzet INNER JOIN anggota ON anggota.cif = omzet.cif WHERE omzet.id = ? AND anggota.UNIT = ?', 'ss', (string) $id, $unit);
};
if (($aksi == 'tambah' || $aksi == 'edit') && !$cif_unit($isian('cif'))) {
    echo 'CIF ' . $isian('cif') . ' tidak terdaftar sebagai anggota unit ' . $unit;
    exit;
}
if (($aksi == 'edit' || $aksi == 'hapus') && !$omzet_unit($isian('id'))) {
    echo 'Data omzet tidak ditemukan di unit ' . $unit;
    exit;
}
if ($aksi == 'get' && !$omzet_unit($isian('id'))) {
    echo json_encode(null);
    exit;
}

if ($aksi == 'tambah') {
    $tanggal = $isian('tanggal');
    $cif     = $isian('cif');
    $nominal = $isian('nominal');

    db_jalankan("INSERT INTO omzet (tanggal, cif, nominal) VALUES (?, ?, ?)", 'sss', $tanggal, $cif, $nominal);
    echo "ok";

} elseif ($aksi == 'edit') {
    $id      = $isian('id');
    $tanggal = $isian('tanggal');
    $cif     = $isian('cif');
    $nominal = $isian('nominal');

    db_jalankan("UPDATE omzet SET tanggal=?, cif=?, nominal=? WHERE id=?", 'ssss', $tanggal, $cif, $nominal, $id);
    $cek_nisbah = db_satu("SELECT nisbah from nisbah WHERE cif =?", 's', $cif);
    $nisbah = $cek_nisbah ? $cek_nisbah['nisbah'] : null;
    // semantik PHP 5: nisbah tidak ada -> 0
    $porsi = compat_num($nominal) * compat_num($nisbah);
    db_jalankan("UPDATE pinjaman_detail_mus SET nisbah_anggota=? WHERE cif=? and tgl_bayar =?", 'sss', (string) $porsi, $cif, $tanggal);

    echo "ok";

} elseif ($aksi == 'hapus') {
    db_jalankan("DELETE FROM omzet WHERE id=?", 's', $isian('id'));
    echo "ok";

} elseif ($aksi == 'get') {
    $data = db_satu("SELECT * FROM omzet WHERE id=?", 's', $isian('id'));
    if ($data) {
        $data['id'] = (string) $data['id'];
        $data['nominal'] = (string) $data['nominal'];
    }
    echo json_encode($data);
}
