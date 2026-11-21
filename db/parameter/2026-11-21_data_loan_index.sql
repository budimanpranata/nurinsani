-- PEKERJAAN_TERTUNDA §25 (2026-11-21, perintah user): index data_loan (unit, buss_date) untuk export Nominative
-- Pembiayaan EOM (report/nominatif_export.php) dan ringkasan EOM di report/Kr.php, yang mencari
--   WHERE data_loan.unit = ? AND data_loan.buss_date >= <awal bulan> AND data_loan.buss_date < <awal bulan berikut>
-- Tanpa index ini query membaca SELURUH data_loan (semua snapshot akhir bulan semua unit). Kode tetap berjalan tanpa
-- index; index hanya mempercepat. Tidak mengubah data.
--
-- Cara jalan (prod): di jam sepi, karena ALTER pada tabel besar butuh waktu dan ruang disk (InnoDB online DDL:
-- tabel tetap bisa dibaca/ditulis selama proses). Aman dijalankan ulang: bila index sudah ada, tidak melakukan apa-apa.
SET NAMES utf8mb4;

SET @ada := (SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'data_loan' AND index_name = 'unit_buss_date');
SET @sql := IF(@ada = 0,
               'ALTER TABLE data_loan ADD INDEX unit_buss_date (unit, buss_date), ALGORITHM=INPLACE, LOCK=NONE',
               'SELECT ''index unit_buss_date sudah ada'' AS info');
PREPARE s FROM @sql;
EXECUTE s;
DEALLOCATE PREPARE s;

-- Cek sesudahnya:
--   SHOW INDEX FROM data_loan WHERE Key_name = 'unit_buss_date';
--   EXPLAIN SELECT count(*) FROM data_loan WHERE unit = '001' AND buss_date >= '2026-10-01' AND buss_date < '2026-11-01';
--   (kolom key harus berisi unit_buss_date)
