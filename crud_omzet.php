<?php require_once __DIR__ . '/koneksi_db.php';
/**
 * Simpan / ubah / hapus / ambil data omzet (dipanggil view_omzet.php lewat AJAX, POST aksi=...).
 * Logika sama dengan versi lama, ditambah prepared statement, pengaman login, dan cek unit (lihat di bawah).
 * 2026-11-21 (perintah user, §11): Tambah / Edit / Hapus menghitung ulang porsi nisbah anggota di pinjaman_detail_mus
 * pada tanggal omzet dengan rumus EOD (omzet x ijaroh / omzet awal), dalam satu transaksi; jawaban "ok" hanya bila
 * berhasil. Dulu hanya Edit, dengan omzet x nisbah.nisbah, dan jawaban selalu "ok".
 */
session_start();
require_once __DIR__ . '/inc/cek_login.php';
wajib_level('unit', 'json');
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

/**
 * 2026-11-21 (perintah user, PEKERJAAN_TERTUNDA §11): porsi nisbah anggota satu CIF pada satu tanggal dihitung ulang
 * dengan RUMUS EOD (proses_eod_mob.php kasus MUSYAROKAH): porsi = omzet tanggal itu x (loan.ijaroh / nisbah.omzet).
 * Hanya baris jadwal pinjaman_detail_mus CIF itu dengan tgl_bayar = tanggal omzet (minggu yang sudah ditagih EOD; yang
 * belum ditagih dihitung EOD malam seperti biasa). Omzet tidak ada (dihapus) -> porsi 0, sama dengan EOD tanpa omzet.
 * Dulu: hanya Edit yang menghitung, dengan omzet x nisbah.nisbah (decimal 5,3; di data 0.000 -> porsi selalu 0);
 * Tambah sesudah EOD dan Hapus tidak memperbarui porsi.
 */
function omzet_hitung_porsi($cif, $tanggal)
{
    $tgl = substr((string) $tanggal, 0, 10);
    // tanpa baris jadwal pada tanggal itu (belum ditagih) UPDATE di bawah tidak mengubah apa pun: dihitung EOD malam
    $o = db_satu("SELECT nominal FROM omzet WHERE tanggal=? AND cif=? ORDER BY id LIMIT 1", 'ss', $tgl, (string) $cif);
    $n = db_satu("SELECT omzet FROM nisbah WHERE cif=?", 's', (string) $cif);
    $l = db_satu("SELECT ijaroh FROM loan WHERE cif=? LIMIT 1", 's', (string) $cif);
    // PHP 5 (sama dengan EOD): pembagi 0/kosong -> false -> 0
    $porsi = compat_num($o ? $o['nominal'] : null) * compat_num(compat_bagi($l ? $l['ijaroh'] : null, $n ? $n['omzet'] : null));
    return db_jalankan("UPDATE pinjaman_detail_mus SET nisbah_anggota=? WHERE cif=? AND tgl_bayar=?", 'sss', (string) $porsi, (string) $cif, $tgl);
}
/** Selesaikan transaksi: "ok" hanya bila semua query berhasil (dulu selalu "ok"). */
function omzet_selesai($db, $gagal_awal)
{
    if (db_jumlah_gagal() > $gagal_awal) {
        mysqli_rollback($db);
        echo 'Data omzet gagal disimpan, tidak ada yang berubah. Silakan ulangi.';
    } else {
        mysqli_commit($db);
        echo "ok";
    }
}

if ($aksi == 'tambah' || $aksi == 'edit' || $aksi == 'hapus') {
    $db = db_ni();
    mysqli_begin_transaction($db);
    $gagal_awal = db_jumlah_gagal();
}

if ($aksi == 'tambah') {
    $tanggal = $isian('tanggal');
    $cif     = $isian('cif');
    $nominal = $isian('nominal');

    db_jalankan("INSERT INTO omzet (tanggal, cif, nominal) VALUES (?, ?, ?)", 'sss', $tanggal, $cif, $nominal);
    omzet_hitung_porsi($cif, $tanggal);
    omzet_selesai($db, $gagal_awal);

} elseif ($aksi == 'edit') {
    $id      = $isian('id');
    $tanggal = $isian('tanggal');
    $cif     = $isian('cif');
    $nominal = $isian('nominal');

    $lama = db_satu("SELECT tanggal, cif FROM omzet WHERE id=?", 's', $id);
    db_jalankan("UPDATE omzet SET tanggal=?, cif=?, nominal=? WHERE id=?", 'ssss', $tanggal, $cif, $nominal, $id);
    omzet_hitung_porsi($cif, $tanggal);
    // CIF / tanggal diganti: porsi pada CIF / tanggal lama ikut dihitung ulang (dulu tertinggal)
    if ($lama && ((string) $lama['cif'] !== $cif || substr((string) $lama['tanggal'], 0, 10) !== substr($tanggal, 0, 10))) {
        omzet_hitung_porsi($lama['cif'], $lama['tanggal']);
    }
    omzet_selesai($db, $gagal_awal);

} elseif ($aksi == 'hapus') {
    $lama = db_satu("SELECT tanggal, cif FROM omzet WHERE id=?", 's', $isian('id'));
    db_jalankan("DELETE FROM omzet WHERE id=?", 's', $isian('id'));
    if ($lama) {
        omzet_hitung_porsi($lama['cif'], $lama['tanggal']);
    }
    omzet_selesai($db, $gagal_awal);

} elseif ($aksi == 'get') {
    $data = db_satu("SELECT * FROM omzet WHERE id=?", 's', $isian('id'));
    if ($data) {
        $data['id'] = (string) $data['id'];
        $data['nominal'] = (string) $data['nominal'];
    }
    echo json_encode($data);
}
