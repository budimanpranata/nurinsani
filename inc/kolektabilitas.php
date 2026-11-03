<?php
/**
 * Hitung ulang kolektabilitas (loan.gol) per unit, rumus menu Report > PPAP unit (report/ppap.php, bagian 27).
 * Dipakai juga Hitung PPAP kp (kp/laporan/progres_ppap.php) agar rumusnya sama (perintah user 2026-11-03).
 * Membutuhkan koneksi_db.php (db_ni, db_jalankan, db_jumlah_gagal).
 */

/**
 * Hitung ulang loan.gol unit (urutan dan rumus sama dengan versi lama) dalam satu transaksi DB.
 * Hasil true bila keenam UPDATE berhasil (commit); false bila ada yang gagal (rollback, gol tidak berubah).
 */
function hitung_kolektabilitas($unit, $tgl)
{
    $db = db_ni();
    $gagal_awal = db_jumlah_gagal();
    $db->begin_transaction();
    try {
        hitung_kolektabilitas_isi($unit, $tgl);
    } catch (Throwable $e) {
        $GLOBALS['__db_jumlah_gagal'] = db_jumlah_gagal() + 1;
    }
    if (db_jumlah_gagal() > $gagal_awal) {
        $db->rollback();
        return false;
    }
    return $db->commit();
}

/** Enam UPDATE kolektabilitas (dipanggil di dalam transaksi). */
function hitung_kolektabilitas_isi($unit, $tgl)
{
    $unit = (string) $unit;
    // golongan dari jumlah angsuran tertunggak, per jenis produk (deal_produk)
    $per_produk = array(
        '2' => 'ROUND(sum(KREDIT-DEBIT)/(angsuran+2500))',
        '1' => 'sum(KREDIT-DEBIT)/(angsuran)',
        '3' => 'sum(KREDIT-DEBIT)/(pokok+ijaroh)',
        '4' => 'ROUND(sum(KREDIT-DEBIT)/(angsuran+2500))',
    );
    foreach ($per_produk as $deal => $ft) {
        db_jalankan("update loan
            set gol=(SELECT
            CASE
            WHEN $ft between 1 and 3 then '1'
            WHEN $ft between 4 and 6 then '2'
            WHEN $ft between 7 and 12 then '3'
            WHEN $ft > 12 then '4'
            else '1' end
            FROM
            tunggakan
            where loan.cif=tunggakan.CIF
            ) where loan.unit =? and deal_produk=? and os > 0", 'ss', $unit, (string) $deal);
    }
    // sudah lewat jatuh tempo: minimal golongan 2/3 menurut lama keterlambatan
    db_jalankan("update loan
        set gol =
        CASE
            WHEN datediff(?,maturity_date) between 1 and 30 then '2'
            WHEN datediff(?,maturity_date) between 31 and 60 then '3'
            WHEN datediff(?,maturity_date) > 60 then '4'
            else '2'
        END
            where
            unit =?
            and maturity_date <= ?
            and os > 0 AND gol in ('1','2')", 'sssss', $tgl, $tgl, $tgl, $unit, $tgl);
    db_jalankan("update loan
        set gol =
        CASE
            WHEN datediff(?,maturity_date) between 1 and 59 then '3'
            WHEN datediff(?,maturity_date) between 31 and 60 then '3'
            WHEN datediff(?,maturity_date) > 60 then '4'
            else '3'
        END
            where
            unit =?
            and maturity_date <= ?
            and os > 0 AND gol in ('3')", 'sssss', $tgl, $tgl, $tgl, $unit, $tgl);
}
