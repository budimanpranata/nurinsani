<?php
/**
 * Nomor bukti jurnal berurutan per unit (2026-11-03, perintah user; PEKERJAAN_TERTUNDA.md bagian 65).
 * Format: <awalan>/-<unit>-<6 digit>, mis. BU/-001-000124 (Jurnal Umum), KK/-001-000007 (Kas Keluar),
 * KM/-1010-000031 (Kas Masuk). Nomor berikutnya = nomor terbesar kode berawalan sama di unit itu + 1 (bukan
 * nomor_jurnal yang auto increment global), dilewati selama kodenya sudah dipakai.
 * Jurnal SELESAI = kodenya ada di tabel jurnal (jurnal_umum/jurnal_keluar/jurnal_masuk) dengan tanggal_selesai terisi.
 * Nomor berjalan = kode format baru milik user (unit + id_admin + jenis_transaksi) yang belum selesai. Kode format lama
 * yang belum selesai TIDAK dilanjutkan (sama dengan versi lama yang selalu memberi nomor baru).
 * Pemakaian di halaman: tampilkan nb_tampil(); saat Simpan panggil nb_kunci(), ambil nb_tampil() lagi, tulis baris,
 * lalu nb_lepas() supaya dua user yang menyimpan bersamaan tidak mendapat nomor yang sama.
 * Membutuhkan koneksi_db.php (db_satu).
 */

/** Tabel penanda selesai untuk awalan BU / KK / KM. */
function nb_tabel_jurnal($awalan)
{
    $peta = array('BU' => 'jurnal_umum', 'KK' => 'jurnal_keluar', 'KM' => 'jurnal_masuk');
    if (!isset($peta[$awalan])) {
        throw new InvalidArgumentException('Awalan nomor bukti tidak dikenal: ' . $awalan);
    }
    return $peta[$awalan];
}

/** Kode bukti dari awalan, unit, dan nomor urut. */
function nb_format($awalan, $unit, $nomor)
{
    return $awalan . '/-' . $unit . '-' . str_pad((string) $nomor, 6, '0', STR_PAD_LEFT);
}

/** true bila kode sudah SELESAI (tercatat di tabel jurnal dengan tanggal_selesai terisi). */
function nb_selesai($awalan, $kode)
{
    $tabel = nb_tabel_jurnal($awalan);
    return (bool) db_satu("select 1 as ada from $tabel where kode_transaksi=? and tanggal_selesai is not null limit 1", 's', (string) $kode);
}

/**
 * Kode milik user yang belum selesai, atau null. Hanya kode format baru menu ini (<awalan>/-<unit>-...): kode lama
 * dan kode dari proses lain (realisasi, PB, dll. yang juga memakai BU/... tanpa catatan di tabel jurnal) tidak
 * dianggap jurnal berjalan (perbaikan 2026-11-03: di prod baris lama itu muncul terus di Jurnal Umum).
 */
function nb_berjalan($awalan, $unit, $userid, $jenis)
{
    $tabel = nb_tabel_jurnal($awalan);
    $r = db_satu("select t.kode_transaksi from tabel_transaksi t
        where t.unit=? and t.id_admin=? and t.jenis_transaksi=? and t.kode_transaksi like ?
          and not exists (select 1 from $tabel j where j.kode_transaksi=t.kode_transaksi and j.tanggal_selesai is not null)
        order by t.id_transaksi desc limit 1", 'ssss', (string) $unit, (string) $userid, (string) $jenis, $awalan . '/-' . $unit . '-%');
    return $r ? (string) $r['kode_transaksi'] : null;
}

/** Kode baru berikutnya untuk unit ini (nomor terbesar berawalan sama + 1, dilewati selama sudah dipakai). */
function nb_baru($awalan, $unit)
{
    $tabel = nb_tabel_jurnal($awalan);
    $depan = $awalan . '/-' . $unit . '-';
    $mulai = strlen($depan) + 1;
    $r = db_satu("select greatest(
            coalesce((select max(cast(substring(kode_transaksi, ?) as unsigned)) from tabel_transaksi where kode_transaksi like ?), 0),
            coalesce((select max(cast(substring(kode_transaksi, ?) as unsigned)) from $tabel where kode_transaksi like ?), 0)) as n",
        'isis', $mulai, $depan . '%', $mulai, $depan . '%');
    $nomor = ($r ? (int) $r['n'] : 0) + 1;
    while (db_satu("select 1 as ada from tabel_transaksi where kode_transaksi=? limit 1", 's', nb_format($awalan, $unit, $nomor))
        || db_satu("select 1 as ada from $tabel where kode_transaksi=? limit 1", 's', nb_format($awalan, $unit, $nomor))) {
        $nomor++;
    }
    return nb_format($awalan, $unit, $nomor);
}

/** Nomor bukti yang dipakai user saat ini: nomor berjalan, atau kode baru berikutnya. */
function nb_tampil($awalan, $unit, $userid, $jenis)
{
    $berjalan = nb_berjalan($awalan, $unit, $userid, $jenis);
    return $berjalan !== null ? $berjalan : nb_baru($awalan, $unit);
}

/** Kunci pembuatan nomor per awalan + unit (dipegang selama Simpan); true bila berhasil. */
function nb_kunci($awalan, $unit)
{
    $r = db_satu("SELECT GET_LOCK(?, 15) as ok", 's', 'nurinsani_nb_' . $awalan . '_' . $unit);
    return $r && (int) $r['ok'] === 1;
}

/** Lepas kunci nb_kunci(). */
function nb_lepas($awalan, $unit)
{
    db_satu("SELECT RELEASE_LOCK(?) as ok", 's', 'nurinsani_nb_' . $awalan . '_' . $unit);
}
