<?php
/**
 * Password login user (tabel user, kolom pass). Dipakai log.php, ganti_password.php, admin/tambah_user.php,
 * admin/reset_password.php.
 *
 * Format simpan (2026-11-05, PEKERJAAN_TERTUNDA.md bagian 93): bcrypt dari hash md5 lama, password_hash(md5(isian)).
 * Isian dinormalkan sama seperti dulu (antiinjection log.php: htmlspecialchars, strip_tags, stripslashes, escape) lalu
 * md5, sehingga semua password lama tetap cocok tanpa reset. Hash md5 polos (32 heksa, data yang belum dikonversi
 * db/parameter/konversi_password.php) masih diterima supaya urutan deploy tidak membuat user gagal login.
 */
require_once __DIR__ . '/../koneksi_db.php';

/** Hash md5 isian password persis seperti log.php lama (antiinjection lalu md5). */
function password_md5_lama($isian)
{
    return md5(mysqli_real_escape_string(db_ni(), stripslashes(strip_tags(htmlspecialchars((string) $isian, ENT_QUOTES)))));
}

/** Nilai kolom user.pass dari hash md5 lama. */
function password_buat_dari_md5($md5)
{
    return password_hash(strtolower((string) $md5), PASSWORD_BCRYPT);
}

/** Nilai kolom user.pass dari isian password. */
function password_buat($isian)
{
    return password_buat_dari_md5(password_md5_lama($isian));
}

/** true bila hash md5 lama cocok dengan nilai kolom user.pass (bcrypt, atau md5 polos yang belum dikonversi). */
function password_cocok_md5($md5, $tersimpan)
{
    $md5 = strtolower((string) $md5);
    $tersimpan = (string) $tersimpan;
    if (strncmp($tersimpan, '$2y$', 4) === 0) {
        return password_verify($md5, $tersimpan);
    }
    return preg_match('/^[0-9a-f]{32}$/i', $tersimpan) === 1 && hash_equals(strtolower($tersimpan), $md5);
}

/** true bila isian password cocok dengan nilai kolom user.pass. */
function password_cocok($isian, $tersimpan)
{
    return password_cocok_md5(password_md5_lama($isian), $tersimpan);
}
