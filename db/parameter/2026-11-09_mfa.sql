-- MFA (kode 6 digit Google Authenticator / TOTP RFC 6238) untuk SEMUA user, perintah user 2026-11-09.
-- PEKERJAAN_TERTUNDA.md bagian 101. Jalankan SEKALI di lokal dan di prod SEBELUM kode baru dipasang
-- (kode baru membaca tabel user_mfa saat login; tanpa tabel ini login gagal).
-- Aman diulang (IF NOT EXISTS / cek kolom).
-- Baris sql_mode: Navicat menjalankan query profil (information_schema.PROFILING ... GROUP BY) sesudah script,
-- yang ditolak only_full_group_by ("[Err] 1055") walaupun script ini sendiri berhasil. Sama dengan koneksi aplikasi.
SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

CREATE TABLE IF NOT EXISTS `user_mfa` (
  `userid` varchar(9) NOT NULL,
  `rahasia` varchar(255) NOT NULL COMMENT 'kunci TOTP base32, terenkripsi inc/enkripsi.php (enc:v1:...)',
  `langkah_terakhir` bigint NOT NULL DEFAULT 0 COMMENT 'langkah waktu (30 detik) kode terakhir yang diterima; kode yang sama tidak bisa dipakai ulang',
  `gagal` int NOT NULL DEFAULT 0 COMMENT 'jumlah kode salah berturut-turut',
  `kunci_sampai` datetime DEFAULT NULL COMMENT 'terkunci sampai waktu ini setelah terlalu banyak kode salah',
  `didaftar` datetime NOT NULL,
  `terakhir_masuk` datetime DEFAULT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- riwayat reset di menu Admin > Reset Password kini juga mencatat reset MFA
SET @ada := (SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = 'riwayat_reset_password' AND column_name = 'jenis');
SET @sql := IF(@ada = 0,
  "ALTER TABLE riwayat_reset_password ADD COLUMN `jenis` varchar(10) NOT NULL DEFAULT 'PASSWORD' AFTER `userid`",
  'SELECT ''kolom jenis sudah ada'' AS info');
PREPARE s FROM @sql;
EXECUTE s;
DEALLOCATE PREPARE s;
