-- Enkripsi NIK & alamat tahap 1 (PEKERJAAN_TERTUNDA.md bagian 87): deposan + baris anggota pasangan deposan.
-- Langkah 1 - skema. Kolom dilebarkan agar muat teks terenkripsi ('enc:v1:' + base64, NIK +-70, alamat 50 huruf +-110
-- karakter) dan ditambah kolom indeks buta NIK (HMAC-SHA256 hex 64 karakter) untuk cek dobel / cari NIK persis.
-- Sifat kolom lain (NULL, collation) tidak berubah. Aman dijalankan sebelum data dienkripsi: data lama tetap terbaca.
-- Jalankan di prod SESUDAH backup database. Urutan: file ini -> kode baru -> kunci prod -> skrip migrasi data.

-- Mode longgar seperti aplikasi (koneksi_db.php): anggota menyimpan tanggal lama tidak sah (mis. ID_EXPIRED
-- '1900-00-00') yang ditolak mode strict saat tabel disalin ulang oleh ALTER. Nilainya tidak diubah.
SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

ALTER TABLE deposan
  MODIFY ID_NUMBER varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  MODIFY KTP_SATU varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  MODIFY KTP_DUA varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  MODIFY HADDRESS1 varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  MODIFY HADDRESS2 varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  MODIFY HADDRESS3 varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  ADD COLUMN ID_NUMBER_IDX char(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL AFTER ID_NUMBER,
  ADD INDEX idx_deposan_id_number_idx (ID_NUMBER_IDX);

ALTER TABLE anggota
  MODIFY ID_NUMBER varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  MODIFY HADDRESS1 varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  ADD COLUMN ID_NUMBER_IDX char(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL AFTER ID_NUMBER,
  ADD INDEX idx_anggota_id_number_idx (ID_NUMBER_IDX);
