<?php
/**
 * Daftar Data Nominatif deposito (fund/view/view_nomi.php, juga fund/view/tempo_export.php?laporan=nomi; filter sama).
 * Membutuhkan koneksi_db.php (db_semua).
 * Versi lama memakai join escrow_deposito per CIF + group by norek: saldo escrow deposan dengan beberapa deposito
 * terhitung gabungan di tiap depositonya. Kini tanpa group by; saldo escrow dihitung per norek oleh pemanggil yang
 * membutuhkannya (export).
 */

/**
 * Daftar menurut filter array(cari): cari terisi -> norek sama persis (pencarian versi lama), atau CIF / nama deposan
 * mengandung teks (perintah user 2026-11-04); kosong -> semua deposito.
 */
function nomi_daftar($filter, $kolom = "deposito.norek,deposito.bilyet,CUST_SHORT_NAME,deposito.cif,deposito.nominal,deposito.tenor,deposito.maturity_date")
{
    $cari = isset($filter['cari']) ? (string) $filter['cari'] : '';
    $sql = "SELECT $kolom FROM deposito join deposan on deposan.CIF = deposito.cif";
    if ($cari !== '') {
        return db_semua("$sql where deposito.norek = ? or deposito.cif LIKE ? or CUST_SHORT_NAME LIKE ? order by deposito.norek",
            'sss', $cari, '%' . $cari . '%', '%' . $cari . '%');
    }
    return db_semua("$sql order by deposito.norek");
}
