<?php
/**
 * Daftar deposito yang menunggu proses Break (tabel dep_break, diisi dari Data Jatuh Tempo > Realisasi > Break
 * Bayarkan) untuk fund/view/view_break.php dan fund/view/tempo_export.php?laporan=break (filter sama).
 * Membutuhkan koneksi_db.php (db_semua).
 */

/**
 * Daftar dep_break menurut filter array(cari): cari terisi -> CIF atau nama deposan mengandung teks (versi lama hanya
 * CIF); kosong -> semua. dep_break diberi alias "deposito" supaya daftar kolom export sama dengan Data Jatuh Tempo.
 */
function break_daftar($filter, $kolom = "deposito.norek,CUST_SHORT_NAME,deposito.nominal,deposito.tenor,deposito.maturity_date,deposito.cif,deposito.aro")
{
    $cari = isset($filter['cari']) ? (string) $filter['cari'] : '';
    $sql = "SELECT $kolom FROM dep_break deposito join deposan on deposan.cif = deposito.cif";
    if ($cari !== '') {
        return db_semua("$sql where deposito.cif LIKE ? or CUST_SHORT_NAME LIKE ? order by deposito.maturity_date, deposito.norek", 'ss', '%' . $cari . '%', '%' . $cari . '%');
    }
    return db_semua("$sql order by deposito.maturity_date, deposito.norek");
}
