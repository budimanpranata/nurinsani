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

/**
 * true bila kode sudah SELESAI (tercatat di tabel jurnal dengan tanggal_selesai terisi). Hasil disimpan selama satu
 * request (lihat nb_lupa()), karena tanpa index jurnal_umum(kode_transaksi) setiap cek membaca seluruh tabel.
 */
function nb_selesai($awalan, $kode)
{
    global $NB_CACHE_SELESAI;
    $kunci = $awalan . '|' . $kode;
    if (!isset($NB_CACHE_SELESAI[$kunci])) {
        $tabel = nb_tabel_jurnal($awalan);
        $NB_CACHE_SELESAI[$kunci] = (bool) db_satu("select 1 as ada from $tabel where kode_transaksi=? and tanggal_selesai is not null limit 1", 's', (string) $kode);
    }
    return $NB_CACHE_SELESAI[$kunci];
}

/** Lupakan hasil nb_selesai() (dipanggil sesudah proses Selesai di halaman yang sama). */
function nb_lupa()
{
    $GLOBALS['NB_CACHE_SELESAI'] = array();
}

/**
 * Kode milik user yang belum selesai, atau null. Hanya kode format baru menu ini (<awalan>/-<unit>-...): kode lama
 * dan kode dari proses lain (realisasi, PB, dll. yang juga memakai BU/... tanpa catatan di tabel jurnal) tidak
 * dianggap jurnal berjalan (perbaikan 2026-11-03: di prod baris lama itu muncul terus di Jurnal Umum).
 * Cukup kode TERBARU milik user yang diperiksa: selama jurnal berjalan belum selesai, halaman selalu memakai kode itu,
 * jadi user tidak bisa memulai kode baru sebelum yang lama selesai.
 */
function nb_berjalan($awalan, $unit, $userid, $jenis)
{
    // index kode_transaksi (awalan kode) jauh lebih sempit daripada index unit
    $r = db_satu("select t.kode_transaksi from tabel_transaksi t force index (kode_transaksi)
        where t.kode_transaksi like ? and t.unit=? and t.id_admin=? and t.jenis_transaksi=?
        order by t.id_transaksi desc limit 1", 'ssss', $awalan . '/-' . $unit . '-%', (string) $unit, (string) $userid, (string) $jenis);
    if (!$r || nb_selesai($awalan, (string) $r['kode_transaksi'])) {
        return null;
    }
    return (string) $r['kode_transaksi'];
}

/**
 * Kode baru berikutnya untuk unit ini (nomor terbesar berawalan sama di tabel_transaksi + 1, dilewati selama sudah
 * dipakai). Tabel jurnal tidak perlu dibaca: kode baru tercatat di sana hanya sesudah barisnya ada di tabel_transaksi.
 */
function nb_baru($awalan, $unit)
{
    $depan = $awalan . '/-' . $unit . '-';
    $r = db_satu("select max(cast(substring(kode_transaksi, ?) as unsigned)) as n from tabel_transaksi force index (kode_transaksi)
        where kode_transaksi like ?", 'is', strlen($depan) + 1, $depan . '%');
    $nomor = ($r ? (int) $r['n'] : 0) + 1;
    while (db_satu("select 1 as ada from tabel_transaksi where kode_transaksi=? limit 1", 's', nb_format($awalan, $unit, $nomor))) {
        $nomor++;
    }
    // kode yang belum punya baris pasti belum selesai (tidak perlu membaca tabel jurnal)
    $GLOBALS['NB_CACHE_SELESAI'][$awalan . '|' . nb_format($awalan, $unit, $nomor)] = false;
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
