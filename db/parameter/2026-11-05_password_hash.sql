-- Migrasi password login ke bcrypt (PEKERJAAN_TERTUNDA.md bagian 93), perintah user 2026-11-05.
-- Jalankan SEBELUM kode baru dipasang; aman untuk kode lama (hanya memperbesar kolom + tabel baru). Aman diulang.
-- Sesudah kode baru terpasang jalankan: php db/parameter/konversi_password.php (lihat keterangan di file itu).
-- Pesan "'utf8mb3' is deprecated" = peringatan biasa, bukan error.

-- 1. kolom pass cukup untuk hash bcrypt (60 karakter). last_update tidak berubah oleh ALTER.
ALTER TABLE user MODIFY pass VARCHAR(255) NOT NULL;

-- 2. cadangan hash md5 lama sebelum dikonversi (untuk kembali bila perlu: konversi_password.php --kembalikan)
CREATE TABLE IF NOT EXISTS user_pass_md5 (
  userid VARCHAR(9) NOT NULL,
  pass VARCHAR(40) NOT NULL,
  waktu DATETIME NOT NULL,
  PRIMARY KEY (userid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- 3. riwayat menu Admin > Parameter User > Reset Password (admin/reset_password.php)
CREATE TABLE IF NOT EXISTS riwayat_reset_password (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  waktu DATETIME NOT NULL,
  admin VARCHAR(15) NOT NULL,
  userid VARCHAR(9) NOT NULL,
  alasan VARCHAR(255) NOT NULL,
  ip VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY idx_riwayat_reset_password_userid (userid),
  KEY idx_riwayat_reset_password_waktu (waktu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
