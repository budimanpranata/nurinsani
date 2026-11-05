<?php
/**
 * Bagi hasil deposito yang jatuh tempo (menu fund > Realisasi Bagi Hasil / Posting). Dipakai fund/view/view_jatuh.php
 * (daftar) dan fund/realisasi_dep.php (pembukuan), supaya yang ditampilkan sama dengan yang dibukukan.
 * Rumus sama dengan versi lama: eq dari param_dep menurut rasio, pajak 10% dari gross.
 * Jumlah hari dari tabel hari_dep (inc/hari_dep.php; perintah user 2026-11-04, dulu tetap 30) = hari yang dipakai
 * Posting Parameter menghitung eq; selama hari_dep kosong dipakai 30 (bhd_hari() memberi tahu halaman).
 * Membutuhkan koneksi_db.php (db_semua/db_satu).
 */
require_once __DIR__ . '/hari_dep.php';

/** array(jumlah hari, true bila dari hari_dep / false bila cadangan 30). */
function bhd_hari()
{
    static $hari = null;
    if ($hari === null) {
        $n = hari_dep_ambil();
        $hari = $n !== null ? array($n, true) : array(HARI_DEP_CADANGAN, false);
    }
    return $hari;
}

/**
 * Jadwal bagi hasil jatuh tempo $tanggal (Y-m-d) yang belum direalisasi: baris deposito_detail ket kosong dan norek
 * belum ada di dep_realisasi (menunggu Disburse di kp). Satu baris per norek; $norek membatasi ke satu deposito.
 */
function bhd_daftar($tanggal, $norek = null)
{
    $sql = "select CUST_SHORT_NAME,bilyet,deposito_detail.id_deposito,norek,ke,deposito.cif,tgl_jatuh_tempo,aro,tenor,
            deposito.nominal as nomi,rasio,norek_tujuan,eq_rate,cara_hitung
        from deposito
        inner join deposito_detail on deposito.norek=deposito_detail.id_deposito
        inner join deposan on deposito.cif=deposan.cif
        inner join param_dep on param_dep.code_bulan= deposito.code_bulan
        where deposito_detail.tgl_jatuh_tempo =? and deposito_detail.ket ='' and deposito_detail.id_deposito not in (select norek from dep_realisasi)";
    if ($norek === null) {
        return db_semua($sql . " group by norek order by norek", 's', $tanggal);
    }
    return db_semua($sql . " and deposito_detail.id_deposito=? group by norek order by norek", 'ss', $tanggal, $norek);
}

/**
 * Hitungan bagi hasil satu baris bhd_daftar(): eq, bagi hasil menurut nisbah (bayar_nisbah) dan menurut rate
 * (bayar_rate), serta angka jurnal: baghas (debet pendapatan), bonus (debet biaya), bayar (kredit ke deposan),
 * pajak (kredit PPh). cara_hitung 2 = nisbah ditambah bonus sampai rate; lainnya = rate.
 * Perbaikan 2026-11-04 (perintah user): cara_hitung 2 membulatkan baghas, pajak dan bayar ke rupiah lalu bonus = selisihnya,
 * sehingga jurnal seimbang (dulu kolom bigint membulatkan masing-masing angka dan jurnal bisa selisih 1 rupiah).
 */
function bhd_hitung($row)
{
    static $eq_rasio = array();
    list($hari) = bhd_hari();
    $rasio = (string) $row['rasio'];
    if (!array_key_exists($rasio, $eq_rasio)) {
        $p = db_satu("select eq from param_dep where rasio_invest = ?", 's', $rasio);
        $eq_rasio[$rasio] = compat_num($p ? $p['eq'] : null);
    }
    $eq = $eq_rasio[$rasio];
    $nomi = compat_num($row['nomi']);

    // Perhitungan Nisbah
    $bayar_nisbah = ($nomi * $eq) * $hari / 365;
    // perhitungan Rate
    $bayar_rate = ($nomi * (compat_num($row['eq_rate']) / 100)) * $hari / 365;
    $gross_rate = $bayar_rate / 0.9;
    $pph4_rate = $gross_rate - $bayar_rate;
    $jlbayar_rate = $gross_rate - $pph4_rate;

    if ((string) $row['cara_hitung'] === '2') {
        $baghas = round($bayar_nisbah);
        $pajak = round($pph4_rate);
        $bayar = round($jlbayar_rate);
        $bonus = ($bayar + $pajak) - $baghas;
    } else {
        $baghas = $bayar_rate;
        $bonus = $pph4_rate;
        $pajak = $pph4_rate;
        $bayar = $bayar_rate;
    }
    return array('hari' => $hari, 'eq' => $eq, 'bayar_nisbah' => $bayar_nisbah, 'bayar_rate' => $bayar_rate,
        'baghas' => $baghas, 'bonus' => $bonus, 'pajak' => $pajak, 'bayar' => $bayar);
}
