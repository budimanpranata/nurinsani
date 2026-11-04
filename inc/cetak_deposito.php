<?php
/**
 * Daftar deposito siap cetak: bilyet (status_print 'OK', fund/view/view_cetak_bilyet.php) dan advis (status_print
 * 'ADVIS', fund/view/view_advise.php), juga untuk fund/view/tempo_export.php?laporan=bilyet|advis (filter sama).
 * Reprint (fund/view/view_reprint.php, fund/transaksi/input_reprint.php): daftar semua deposito dan catatan dep_reprint
 * (status 'BARU' -> 'DICETAK' saat cetak ulang dipakai, sekali pakai).
 * Membutuhkan koneksi_db.php (db_semua).
 */

/** Jenis cetak dep_reprint.jenis_cetak => nama. */
const REPRINT_JENIS = array('1' => 'Bilyet', '2' => 'Advis');

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

/** Semua deposito (menu Reprint), disaring CIF/nama bila filter cari terisi (versi lama hanya CIF). */
function reprint_daftar($filter, $kolom = "deposito.norek,CUST_SHORT_NAME,deposito.nominal,deposito.tenor,deposito.maturity_date,deposito.cif,deposito.aro,deposito.status_print")
{
    $cari = isset($filter['cari']) ? (string) $filter['cari'] : '';
    $sql = "SELECT $kolom FROM deposito join deposan on deposan.cif = deposito.cif";
    if ($cari !== '') {
        return db_semua("$sql where deposito.cif LIKE ? or CUST_SHORT_NAME LIKE ? order by deposito.norek", 'ss', '%' . $cari . '%', '%' . $cari . '%');
    }
    return db_semua("$sql order by deposito.norek");
}

/**
 * Pakai catatan reprint $id untuk norek dan jenis itu: status 'BARU' -> 'DICETAK' dalam satu UPDATE (dua klik/tab
 * bersamaan tidak bisa memakai catatan yang sama). true bila berhasil diambil.
 */
function reprint_pakai($id, $norek, $jenis)
{
    if (!preg_match('/^\d+$/', (string) $id)) {
        return false;
    }
    // statement sendiri: affected_rows koneksi (db_jalankan) bernilai -1 sesudah prepared statement
    $st = db_ni()->prepare("UPDATE dep_reprint SET status='DICETAK' WHERE id=? AND norek=? AND jenis_cetak=? AND status='BARU'");
    $id = (int) $id;
    return $st && $st->bind_param('iss', $id, $norek, $jenis) && $st->execute() && $st->affected_rows === 1;
}

/** Halaman pesan saat bilyet/advis tidak boleh dicetak (butuh inc/tampilan.php). */
function cetak_tolak($judul, $pesan, $kembali)
{
    halaman_awal($judul);
    echo '<div class="alert alert-warning">' . $pesan . '</div>';
    echo '<a href="' . h($kembali) . '" class="btn btn-sm btn-secondary">Kembali</a>';
    halaman_akhir();
    exit;
}
