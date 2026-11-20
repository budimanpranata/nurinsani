<?php
/**
 * Arsip deposito yang ditutup (2026-11-20, perintah user, PEKERJAAN_TERTUNDA §51).
 * Salin baris `deposito` ke `dep_audit` tepat SEBELUM `delete from deposito`, agar data master (bilyet, nominal, tenor,
 * rasio, ARO, tanggal buka, bank) tidak hilang. Tabel dibuat oleh db/parameter/2026-11-20_dep_audit.sql.
 *
 *   require_once __DIR__ . '/../../inc/arsip_deposito.php';
 *   if (!arsip_deposito($norek, 'BREAK', $tgl, $userid)) { ...hentikan, jangan hapus deposito... }
 *
 * Hasil true bila baris deposito tersalin, atau bila deposito memang sudah tidak ada (tidak ada yang perlu diarsip,
 * proses berjalan seperti dulu); false bila salinan gagal (mis. tabel dep_audit belum dibuat) -> pemanggil wajib
 * menghentikan proses dan TIDAK menghapus deposito.
 */
function arsip_deposito($norek, $jenis_tutup, $tgl_tutup, $userid)
{
    if (!db_satu("SELECT 1 AS ada FROM deposito WHERE norek = ?", 's', (string) $norek)) {
        return true;
    }
    $kolom = 'buss_date,norek,cif,bilyet,nominal,tenor,star_date,maturity_date,jenis_simpanan,aro,rasio,code_bulan,unit,'
        . 'suffix,contract_date,status_print,userid,jenis_bayar,eq_rate,nama_bank,norek_bank,cara_hitung,last_payment';
    $tgl = substr((string) $tgl_tutup, 0, 10);
    $tgl = $tgl === '' ? null : $tgl;
    $jenis_tutup = (string) $jenis_tutup;
    $userid = (string) $userid;
    $norek = (string) $norek;
    // statement sendiri (bukan db_jalankan) agar jumlah baris tersalin bisa dibaca dari statement-nya
    $st = db_ni()->prepare("INSERT INTO dep_audit (jenis_tutup,tgl_tutup,userid_tutup,$kolom)
        SELECT ?, ?, ?, $kolom FROM deposito WHERE norek = ?");
    if (!$st || !$st->bind_param('ssss', $jenis_tutup, $tgl, $userid, $norek) || !$st->execute()) {
        trigger_error('arsip_deposito(): ' . ($st ? $st->error : db_ni()->error), E_USER_WARNING);
        return false;
    }
    return $st->affected_rows >= 1;
}
