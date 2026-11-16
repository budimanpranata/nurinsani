-- PEKERJAAN_TERTUNDA §1 (2026-11-16, perintah user): nisbah desimal terpotong jadi 0 di rekap_nisbah.
-- nisbah.nisbah bertipe DECIMAL(5,3) (contoh 0,375) tetapi rekap_nisbah.nisbah DECIMAL(10,0), sehingga 0,375
-- tersimpan 0. Diubah ke DECIMAL(10,3): 3 angka desimal seperti tabel nisbah, bagian bulat tidak menyempit
-- (data lama tetap muat). Aman dijalankan ulang.
SET NAMES utf8mb4;

ALTER TABLE `rekap_nisbah` MODIFY `nisbah` DECIMAL(10,3) NULL;

-- Cek sesudahnya (harus decimal(10,3)):
-- SHOW COLUMNS FROM rekap_nisbah LIKE 'nisbah';

-- CATATAN prod: EOD (proses_eod_realisasi_mus.php) dulu menulis ke tabel salah ketik `rekap_nsibah`.
-- Cek apakah tabel itu pernah dibuat di prod:
--   SHOW TABLES LIKE 'rekap_n%';
-- Bila `rekap_nsibah` ADA dan berisi data, salin ke rekap_nisbah (nilai desimal yang sudah terpotong di sana tidak
-- bisa dipulihkan), lalu tabel lama boleh dihapus setelah dicek:
--   INSERT INTO rekap_nisbah (cif, nisbah, omzet, tgl_realisasi)
--     SELECT cif, nisbah, omzet, tgl_realisasi FROM rekap_nsibah;
