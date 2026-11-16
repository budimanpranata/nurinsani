-- PEKERJAAN_TERTUNDA §2b (2026-11-16, perintah user, opsi A).
-- Dulu realisasi_tag.php dan EOD (proses_eod_debet_lebaran.php, proses_eod_debet_rest.php,
-- proses_eod_mob_debet_tunggakan.php) menempelkan '-1' ke tanggal jadwal saat anggota menunggak. Hasil di MySQL:
--   - tanggal murni   '2026-11-23-1'          -> '2026-11-23 01:00:00' (jam 01:00)
--   - tanggal + jam   '2026-11-23 00:00:00-1' -> '0000-00-00 00:00:00' (TANGGAL HILANG) <- yang terjadi di
--     realisasi_tag.php & proses_eod_debet_lebaran.php karena nilainya dari DATE_ADD() kolom DATETIME
-- Kode sudah diperbaiki (tanpa '-1'). Skrip ini:
--   (1) menghitung baris terdampak, (2) membersihkan jam 01:00 -> 00:00 (tanggal tidak berubah).
-- Baris 0000-00-00 TIDAK bisa diperbaiki dengan skrip ini (tanggal aslinya hilang) -> lihat hasil langkah 1 dan
-- hubungi developer bila jumlahnya > 0.
-- Jalankan SETELAH kode baru terpasang. Aman dijalankan ulang. BACKUP dulu loan, pinjaman_detail, pinjaman_detail_jatpo.
SET NAMES utf8mb4;

-- 1) Hitung baris terdampak
SELECT 'loan.next_schedule jam 01:00' AS kolom, COUNT(*) AS jumlah FROM loan WHERE TIME(next_schedule) = '01:00:00'
UNION ALL
SELECT 'loan.next_schedule 0000-00-00 (os > 0)', COUNT(*) FROM loan WHERE CAST(next_schedule AS CHAR) LIKE '0000-00-00%' AND os > 0
UNION ALL
SELECT 'pinjaman_detail JATPO jam 01:00', COUNT(*) FROM pinjaman_detail WHERE TIME(tgl_jatuh_tempo) = '01:00:00'
UNION ALL
SELECT 'pinjaman_detail JATPO 0000-00-00', COUNT(*) FROM pinjaman_detail WHERE ket = 'JATPO' AND CAST(tgl_jatuh_tempo AS CHAR) LIKE '0000-00-00%'
UNION ALL
SELECT 'pinjaman_detail_jatpo jam 01:00', COUNT(*) FROM pinjaman_detail_jatpo WHERE TIME(tgl_jatuh_tempo) = '01:00:00'
UNION ALL
SELECT 'pinjaman_detail_jatpo 0000-00-00', COUNT(*) FROM pinjaman_detail_jatpo WHERE CAST(tgl_jatuh_tempo AS CHAR) LIKE '0000-00-00%';

-- 2) Bersihkan jam 01:00 (hanya baris berjam tepat 01:00:00)
UPDATE loan SET next_schedule = DATE(next_schedule) WHERE TIME(next_schedule) = '01:00:00';
UPDATE pinjaman_detail SET tgl_jatuh_tempo = DATE(tgl_jatuh_tempo) WHERE TIME(tgl_jatuh_tempo) = '01:00:00';
UPDATE pinjaman_detail_jatpo SET tgl_jatuh_tempo = DATE(tgl_jatuh_tempo) WHERE TIME(tgl_jatuh_tempo) = '01:00:00';
