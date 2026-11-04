<?php
/**
 * Jumlah hari bagi hasil deposito, tabel hari_dep satu baris (id = 1; skema db/parameter/2026-11-04_hari_dep.sql).
 * Diisi otomatis oleh Posting Parameter (fund/transaksi/posting.php) dari DATEDIFF(sd, dari) periode Param Financial
 * terbaru, dan bisa diubah di Seting Parameter > Jumlah Hari Bagi Hasil (fund/view/view_hari_dep.php).
 * Dipakai Posting Parameter (eq) dan Realisasi Bagi Hasil (inc/bagi_hasil_deposito.php). Perintah user 2026-11-04.
 * Membutuhkan koneksi_db.php (db_satu/db_jalankan).
 */

const HARI_DEP_CADANGAN = 30;

/** Baris hari_dep (jumlah_hari, sumber, tgl_update, userid) atau null bila belum diisi / tabel belum ada. */
function hari_dep_baris()
{
    $r = db_satu("select jumlah_hari, sumber, tgl_update, userid from hari_dep where id=1");
    return $r && (int) $r['jumlah_hari'] > 0 ? $r : null;
}

/** Jumlah hari yang berlaku, atau null bila belum diisi. */
function hari_dep_ambil()
{
    $r = hari_dep_baris();
    return $r ? (int) $r['jumlah_hari'] : null;
}

/** Simpan jumlah hari (1-366); $sumber 'posting' atau 'manual'. Hasil true bila tersimpan. */
function hari_dep_simpan($jumlah, $sumber, $userid)
{
    $jumlah = (int) $jumlah;
    if ($jumlah < 1 || $jumlah > 366) {
        return false;
    }
    return (bool) db_jalankan("insert into hari_dep (id, jumlah_hari, sumber, tgl_update, userid) values (1, ?, ?, now(), ?)
        on duplicate key update jumlah_hari=values(jumlah_hari), sumber=values(sumber), tgl_update=values(tgl_update), userid=values(userid)",
        'iss', $jumlah, (string) $sumber, (string) $userid);
}

/** Periode Param Financial terbaru (code_param terbesar) beserta hari = DATEDIFF(sd, dari), atau null. */
function hari_dep_periode_terbaru()
{
    return db_satu("select code_param, nomi_murab, marigin_murab, dari, sd, DATEDIFF(sd,dari) as hari from param_finan order by code_param desc limit 1");
}
