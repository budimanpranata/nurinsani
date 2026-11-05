-- Tabel sementara Konsolidasi jenis "current" (temuan user 2026-11-05: tabel ini belum ada di prod, sehingga
-- Proses Konsolidasi Harian tidak mengisi apa pun dan Report Konsolidasi jenis current selalu "data tidak ditemukan").
-- Diisi menu kp > Proses Konsolidasi Harian (kp/laporan/proses_konsol_hari.php): TRUNCATE lalu salin tabel_master.
-- Struktur sama dengan tabel_master (kolom & urutan wajib sama karena INSERT ... SELECT *), tanpa PRIMARY KEY
-- seperti tabel asli di server lama. Aman dijalankan ulang (IF NOT EXISTS).
CREATE TABLE IF NOT EXISTS `tabel_master_hari` (
  `rekening_gl` varchar(20) NOT NULL,
  `kode_rekening` varchar(10) NOT NULL DEFAULT '',
  `nama_rekening` varchar(100) NOT NULL,
  `tanggal_awal` varchar(12) NOT NULL,
  `awal_debet` bigint NOT NULL,
  `awal_kredit` bigint NOT NULL,
  `mut_debet` bigint NOT NULL,
  `mut_kredit` bigint NOT NULL,
  `sisa_debet` bigint NOT NULL,
  `sisa_kredit` bigint NOT NULL,
  `rl_debet` bigint NOT NULL,
  `rl_kredit` bigint NOT NULL,
  `nrc_debet` bigint NOT NULL,
  `nrc_kredit` bigint NOT NULL,
  `posisi` varchar(15) NOT NULL,
  `normal` varchar(10) NOT NULL,
  `unit` varchar(4) NOT NULL,
  `gr_head` varchar(14) NOT NULL,
  `gr_sub` varchar(15) NOT NULL,
  `gr_neraca` varchar(15) NOT NULL,
  `saldo_awal` bigint NOT NULL,
  `saldo_akhir` bigint NOT NULL,
  `saldo_jalan` bigint NOT NULL,
  `LINE_BALANCE` varchar(20) NOT NULL,
  KEY `unit` (`unit`),
  KEY `gr_sub` (`gr_sub`),
  KEY `gr_head` (`gr_head`),
  KEY `kode_rekening` (`kode_rekening`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
