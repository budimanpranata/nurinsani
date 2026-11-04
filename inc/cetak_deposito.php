<?php
/**
 * Daftar deposito siap cetak: bilyet (status_print 'OK', fund/view/view_cetak_bilyet.php) dan advis (status_print
 * 'ADVIS', fund/view/view_advise.php), juga untuk fund/view/tempo_export.php?laporan=bilyet|advis (filter sama).
 * Membutuhkan koneksi_db.php (db_semua).
 */

/**
 * Daftar menurut filter array(cari): cari terisi -> CIF atau nama deposan mengandung teks (versi lama bermaksud sama
 * tetapi query-nya salah tanda kutip sehingga pencarian selalu gagal); kosong -> semua status OK.
 */
function bilyet_daftar($filter, $kolom = "deposito.norek,CUST_SHORT_NAME,deposito.nominal,deposito.tenor,deposito.maturity_date,deposito.cif,deposito.aro")
{
    return cetak_daftar('OK', $filter, $kolom);
}

/**
 * Daftar advis (status 'ADVIS') menurut filter array(cari): CIF atau nama deposan (versi lama hanya CIF).
 */
function advis_daftar($filter, $kolom = "deposito.norek,CUST_SHORT_NAME,deposito.nominal,deposito.tenor,deposito.maturity_date,deposito.cif,deposito.aro")
{
    return cetak_daftar('ADVIS', $filter, $kolom);
}

/** Deposito dengan status_print $status, disaring CIF/nama bila filter cari terisi. */
function cetak_daftar($status, $filter, $kolom)
{
    $cari = isset($filter['cari']) ? (string) $filter['cari'] : '';
    $sql = "SELECT $kolom FROM deposito join deposan on deposan.cif = deposito.cif where status_print =?";
    if ($cari !== '') {
        return db_semua("$sql and (deposito.cif LIKE ? or CUST_SHORT_NAME LIKE ?) order by deposito.norek", 'sss', $status, '%' . $cari . '%', '%' . $cari . '%');
    }
    return db_semua("$sql order by deposito.norek", 's', $status);
}
