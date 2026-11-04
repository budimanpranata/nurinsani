-- Index tanggal untuk tabel laporan konsolidasi (PEKERJAAN_TERTUNDA.md bagian 88, 2026-11-04).
-- Tanpa index ini kp/laporan/neraca_konsol.php (294 query per laporan, disaring tanggal_awal + unit) membaca semua
-- periode: uji 24 periode / 1,3 juta baris = 153 detik, dengan index 4,6 detik (prod: Neraca Konsolidasi tidak pernah
-- terunduh). Hanya menambah index; kolom dan data tidak berubah.
-- Jalankan di luar jam operasional (membangun index tabel besar bisa beberapa menit; tabel_master_hari MyISAM terkunci).
-- Bila muncul "Duplicate key name 'idx_tanggal_unit'", index itu sudah ada (abaikan, lanjut ke perintah berikutnya).
ALTER TABLE tabel_master_konsol ADD INDEX idx_tanggal_unit (tanggal_awal, unit);
ALTER TABLE tabel_master_eoy ADD INDEX idx_tanggal_unit (tanggal_awal, unit);
ALTER TABLE tabel_master_hari ADD INDEX idx_tanggal_unit (tanggal_awal, unit);
