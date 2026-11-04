-- Riwayat menu Admin > Koreksi Pembiayaan (admin/koreksi_loan.php), perintah user 2026-11-04.
-- Satu baris per kolom tabel loan yang dikoreksi: siapa, kapan, CIF, kolom, nilai lama, nilai baru, alasan.
-- Tabel baru (tidak mengubah tabel lain). Aman dijalankan ulang (IF NOT EXISTS).
-- Pesan "'utf8mb3' is deprecated" = peringatan biasa, bukan error.
CREATE TABLE IF NOT EXISTS koreksi_loan (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  waktu DATETIME NOT NULL,
  userid VARCHAR(15) NOT NULL,
  cif VARCHAR(10) NOT NULL,
  no_anggota VARCHAR(16) NOT NULL DEFAULT '',
  kolom VARCHAR(30) NOT NULL,
  nilai_lama VARCHAR(50) NULL,
  nilai_baru VARCHAR(50) NULL,
  alasan VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_koreksi_loan_cif (cif),
  KEY idx_koreksi_loan_waktu (waktu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
