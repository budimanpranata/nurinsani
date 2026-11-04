<?php
/**
 * Jadwal bagi hasil & jatuh tempo deposito (perintah user 2026-11-04, PEKERJAAN_TERTUNDA.md bagian 86 item 3, bug I/J).
 *
 * Aturan: tanggal bayar bulan ke-k = tanggal penempatan D pada bulan ke-k sesudahnya; bila bulan itu lebih pendek
 * (mis. D=31 di April, D=29/30/31 di Februari) dipakai tanggal terakhir bulan itu. Jatuh tempo = tanggal bayar terakhir.
 *   15-01 -> 15-02, 15-03, ...        31-01 -> 28/29-02, 31-03, 30-04, ...        29-01-2026 -> 28-02, 29-03, ...
 * Pengganti cara lama (isi satu baris per hari lalu hapus dengan 42 aturan; jatuh tempo strtotime('+n month')) yang:
 *   - (I) membuang tanggal 29 di semua bulan selain Februari pada tahun bukan kabisat (jadwal 12 bulan hanya 1 baris)
 *   - (J) membuat jatuh tempo meluap ke bulan berikutnya untuk tanggal 29-31 (31-01 + 3 bulan = 01-05)
 * Untuk tanggal lain hasilnya sama dengan cara lama.
 */

/** Tanggal $tgl (Y-m-d) ditambah $n bulan, dipotong ke akhir bulan bila tanggalnya tidak ada di bulan tujuan. */
function tambah_bulan_akhir($tgl, $n)
{
    $t = DateTime::createFromFormat('!Y-m-d', substr((string) $tgl, 0, 10));
    if (!$t || $t->format('Y-m-d') !== substr((string) $tgl, 0, 10)) {
        // format lain yang dulu diterima strtotime() (mis. yyyy/mm/dd dari isian teks bebas)
        $detik = strtotime((string) $tgl);
        if ($detik === false) {
            return null;
        }
        $t = DateTime::createFromFormat('!Y-m-d', date('Y-m-d', $detik));
    }
    $bulan = (int) $t->format('n') - 1 + (int) $n;
    $tahun = (int) $t->format('Y') + intdiv($bulan, 12) - ($bulan % 12 < 0 ? 1 : 0);
    $bulan = (($bulan % 12) + 12) % 12 + 1;
    $hari = min((int) $t->format('j'), cal_hari_dalam_bulan($tahun, $bulan));
    return sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);
}

/** Jumlah hari dalam bulan (tanpa ekstensi calendar). */
function cal_hari_dalam_bulan($tahun, $bulan)
{
    return (int) date('t', mktime(0, 0, 0, $bulan, 1, $tahun));
}

/**
 * Jadwal bayar bagi hasil untuk penempatan $tgl selama $tenor bulan: array(ke => 'Y-m-d'), ke = 1..$tenor.
 * Jatuh tempo = elemen terakhir. Array kosong bila tanggal/tenor tidak sah.
 */
function jadwal_deposito($tgl, $tenor)
{
    $tenor = (int) $tenor;
    $jadwal = array();
    for ($k = 1; $k <= $tenor; $k++) {
        $t = tambah_bulan_akhir($tgl, $k);
        if ($t === null) {
            return array();
        }
        $jadwal[$k] = $t;
    }
    return $jadwal;
}
