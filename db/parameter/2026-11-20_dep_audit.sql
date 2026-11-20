-- PEKERJAAN_TERTUNDA §51 (2026-11-20, perintah user): arsip deposito yang ditutup karena BREAK.
-- Dulu baris `deposito` dihapus tanpa salinan (bilyet, nominal pokok, tenor, rasio, ARO, tanggal buka, bank hilang
-- permanen; audit_dep_realisasi hanya berisi catatan pembayaran). Kini tepat sebelum `delete from deposito`
-- barisnya disalin ke dep_audit oleh inc/arsip_deposito.php, dipanggil dari:
--   - kp/transaksi/engine_update_dep.php  (KP > Monitoring > Daily Deposito, Disburse status BREAK)
--   - fund/view/r_dep.php                 (Fund > Realisasi Deposito, BREAK "titip")
-- Kolom deposito disalin apa adanya (semua boleh NULL agar data lama selalu muat); id auto increment sehingga norek
-- yang sama boleh tercatat lebih dari sekali. WAJIB dijalankan di prod SEBELUM kode baru dipasang: bila tabel belum
-- ada, BREAK dihentikan dengan pesan dan tidak ada data yang berubah. Aman dijalankan ulang.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `dep_audit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jenis_tutup` varchar(20) NOT NULL COMMENT 'BREAK (Disburse kp) / BREAK_TITIP (Fund)',
  `tgl_tutup` date DEFAULT NULL COMMENT 'tanggal jurnal break',
  `userid_tutup` varchar(20) DEFAULT NULL,
  `waktu_arsip` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `buss_date` timestamp NULL DEFAULT NULL,
  `norek` varchar(25) DEFAULT NULL,
  `cif` varchar(20) DEFAULT NULL,
  `bilyet` varchar(6) DEFAULT NULL,
  `nominal` bigint DEFAULT NULL,
  `tenor` varchar(2) DEFAULT NULL,
  `star_date` datetime DEFAULT NULL,
  `maturity_date` datetime DEFAULT NULL,
  `jenis_simpanan` varchar(10) DEFAULT NULL,
  `aro` varchar(25) DEFAULT NULL,
  `rasio` float DEFAULT NULL,
  `code_bulan` varchar(10) DEFAULT NULL,
  `unit` varchar(4) DEFAULT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `contract_date` datetime DEFAULT NULL,
  `status_print` varchar(10) DEFAULT NULL,
  `userid` varchar(8) DEFAULT NULL,
  `jenis_bayar` varchar(10) DEFAULT NULL,
  `eq_rate` int DEFAULT NULL,
  `nama_bank` varchar(50) DEFAULT NULL,
  `norek_bank` varchar(20) DEFAULT NULL,
  `cara_hitung` varchar(2) DEFAULT NULL,
  `last_payment` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `norek` (`norek`),
  KEY `cif` (`cif`),
  KEY `unit` (`unit`),
  KEY `tgl_tutup` (`tgl_tutup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Cek sesudahnya:
--   SHOW CREATE TABLE dep_audit;
-- Riwayat deposito yang sudah break:
--   SELECT jenis_tutup, tgl_tutup, norek, cif, bilyet, nominal, tenor, rasio, aro FROM dep_audit ORDER BY id DESC;
