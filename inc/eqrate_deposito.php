<?php
/**
 * Laporan Bagi Hasil dan Eq Rate deposito (menu fund > Report > View Baghas & Eq Rate): fund/view/eq_rate.php
 * (ringkasan per unit) dan fund/view/eqrate_export.php (detail .xlsx). Membutuhkan koneksi_db.php.
 * Satu baris per deposito yang punya jadwal bagi hasil (deposito_detail) jatuh tempo dalam periode; jadwal yang dipakai
 * = jadwal pertama deposito itu dalam periode (keputusan user 2026-11-04; versi lama group by norek memilih jadwal acak).
 * Hitungan = Realisasi Bagi Hasil (inc/bagi_hasil_deposito.php bhd_hitung),
 * jumlah hari dari hari_dep (perintah user 2026-11-04; versi lama ditulis mati 31 hari).
 */
require_once __DIR__ . '/bagi_hasil_deposito.php';

/** FROM + join jadwal pertama dalam periode [awal, akhir] (Y-m-d) per deposito; parameter: awal, akhir. */
function eqrate_dari()
{
    return "from deposito
        inner join (select id_deposito, min(id) as id_jadwal from deposito_detail
            where tgl_jatuh_tempo between ? and ? group by id_deposito) p on p.id_deposito = deposito.norek
        inner join deposito_detail on deposito_detail.id = p.id_jadwal
        left join deposan on deposan.cif = deposito.cif";
}

/** Baris detail periode, urut nama deposan lalu norek (versi lama: order by CUST_SHORT_NAME). */
function eqrate_daftar($awal, $akhir)
{
    return db_semua("select deposito.norek, deposito.cif, CUST_SHORT_NAME, deposito.bilyet, deposito.nominal, deposito.tenor,
            deposito.star_date, deposito.maturity_date, deposito.aro, deposito.rasio, deposito.eq_rate, deposito.cara_hitung,
            deposito_detail.ke, deposito_detail.tgl_jatuh_tempo,
            IF(IFNULL(deposito_detail.ket, '') = '', 'Belum', deposito_detail.ket) as status_jadwal
        " . eqrate_dari() . " order by CUST_SHORT_NAME, deposito.norek", 'ss', $awal, $akhir . ' 23:59:59');
}

/** Ringkasan per unit: noa (deposito) dan total nominal (tiap deposito sekali). */
function eqrate_ringkasan($awal, $akhir)
{
    return db_semua("select deposito.unit, count(*) as noa, sum(deposito.nominal) as nominal " . eqrate_dari()
        . " group by deposito.unit order by deposito.unit", 'ss', $awal, $akhir . ' 23:59:59');
}

/**
 * Hitungan satu baris: bagi hasil menurut nisbah (eq param_dep) dan menurut rate (eq_rate deposito), gross = /0,9,
 * PPh4 ayat 2 = gross - bersih, jumlah bayar = gross - PPh4 (rumus laporan lama), dibulatkan ke rupiah.
 * rasio_eks dari param_dep menurut rasio (kolom "Nisbah" bagian Rate di laporan lama).
 */
function eqrate_hitung($row)
{
    static $eks = array();
    $rasio = (string) $row['rasio'];
    if (!array_key_exists($rasio, $eks)) {
        $p = db_satu("select rasio_eks from param_dep where rasio_invest = ?", 's', $rasio);
        $eks[$rasio] = $p ? (string) $p['rasio_eks'] : '';
    }
    $h = bhd_hitung(array('rasio' => $row['rasio'], 'nomi' => $row['nominal'], 'eq_rate' => $row['eq_rate'], 'cara_hitung' => $row['cara_hitung']));
    $hasil = array('rasio_eks' => $eks[$rasio]);
    foreach (array('nisbah' => $h['bayar_nisbah'], 'rate' => $h['bayar_rate']) as $k => $bayar) {
        $gross = $bayar / 0.9;
        $pph4 = $gross - $bayar;
        $hasil[$k] = round($bayar);
        $hasil[$k . '_gross'] = round($gross);
        $hasil[$k . '_pph4'] = round($pph4);
        $hasil[$k . '_bayar'] = round($gross - $pph4);
    }
    return $hasil;
}
