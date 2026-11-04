<?php
/**
 * Daftar deposito jatuh tempo untuk fund/view/view_tempo.php dan fund/view/tempo_export.php (filter sama).
 * Membutuhkan koneksi_db.php (db_semua).
 */

/**
 * Daftar jatuh tempo menurut filter array(tanggal 'Y-m-d' atau '', nama, bulan 'Y-m'): tanggal dan/atau nama terisi ->
 * pencarian semua bulan (tanggal jatuh tempo persis, nama deposan mengandung teks; keduanya terisi = keduanya harus
 * cocok); keduanya kosong -> jatuh tempo pada bulan dan tahun terpilih.
 * Perubahan 2026-11-04 (permintaan user): cari tanggal kini kalender terpisah dari nama (dulu satu kotak teks:
 * maturity_date = teks ATAU nama mengandung teks).
 */
function tempo_daftar($filter, $kolom = "norek,CUST_SHORT_NAME,bilyet,nominal,tenor,maturity_date,deposito.cif,aro")
{
    $tanggal = isset($filter['tanggal']) ? (string) $filter['tanggal'] : '';
    $nama = isset($filter['nama']) ? (string) $filter['nama'] : '';
    if ($tanggal !== '' || $nama !== '') {
        $syarat = array();
        $tipe = '';
        $param = array();
        if ($tanggal !== '') {
            $syarat[] = 'deposito.maturity_date=?';
            $tipe .= 's';
            $param[] = $tanggal;
        }
        if ($nama !== '') {
            $syarat[] = 'CUST_SHORT_NAME like ?';
            $tipe .= 's';
            $param[] = '%' . $nama . '%';
        }
        return db_semua("SELECT $kolom FROM deposito inner join deposan on deposan.cif = deposito.cif
            where " . implode(' and ', $syarat) . " order by maturity_date, norek", $tipe, ...$param);
    }
    return db_semua("SELECT $kolom FROM deposito inner join deposan on deposan.CIF = deposito.cif
        where MONTH(maturity_date)=? and YEAR(maturity_date)=? order by maturity_date, norek", 'ss',
        (string) (int) substr($filter['bulan'], 5, 2), substr($filter['bulan'], 0, 4));
}

/** Teks pencarian untuk judul/ringkasan, '' bila tidak mencari (tampilan per bulan). */
function tempo_keterangan_cari($filter)
{
    $bagian = array();
    if (!empty($filter['tanggal'])) {
        $bagian[] = 'tanggal ' . $filter['tanggal'];
    }
    if (isset($filter['nama']) && $filter['nama'] !== '') {
        $bagian[] = 'nama "' . $filter['nama'] . '"';
    }
    return implode(' dan ', $bagian);
}
