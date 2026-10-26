-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 16 Sep 2016 pada 23.36
-- Versi Server: 5.6.21
-- PHP Version: 5.5.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `smk`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilai`
--

CREATE TABLE IF NOT EXISTS `nilai` (
`id` int(5) NOT NULL,
  `semester` int(2) NOT NULL,
  `kode_pelajaran` char(4) NOT NULL,
  `kode_guru` char(5) NOT NULL,
  `kode_kelas` char(4) NOT NULL,
  `kode_siswa` char(5) NOT NULL,
  `nilai_tugas1` int(4) NOT NULL,
  `nilai_tugas2` int(4) NOT NULL,
  `nilai_tugas3` int(4) NOT NULL,
  `nilai_tugas4` int(4) NOT NULL,
  `nilai_tugas5` int(4) NOT NULL,
  `nilai_tugas6` int(4) NOT NULL,
  `nilai_tugas7` int(4) NOT NULL,
  `nilai_tugas8` int(4) NOT NULL,
  `nilai_tugas9` int(4) NOT NULL,
  `nilai_tugas10` int(4) NOT NULL,
  `nilai_tugas11` int(4) NOT NULL,
  `nilai_tugas12` int(4) NOT NULL,
  `nilai_tugas13` int(4) NOT NULL,
  `nilai_uts` int(4) NOT NULL,
  `nilai_uas` int(4) NOT NULL,
  `keterangan` varchar(100) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `nilai`
--

INSERT INTO `nilai` (`id`, `semester`, `kode_pelajaran`, `kode_guru`, `kode_kelas`, `kode_siswa`, `nilai_tugas1`, `nilai_tugas2`, `nilai_tugas3`, `nilai_tugas4`, `nilai_tugas5`, `nilai_tugas6`, `nilai_tugas7`, `nilai_tugas8`, `nilai_tugas9`, `nilai_tugas10`, `nilai_tugas11`, `nilai_tugas12`, `nilai_tugas13`, `nilai_uts`, `nilai_uas`, `keterangan`) VALUES
(1, 1, 'P001', 'G0002', 'K001', 'S0002', 75, 60, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 85, 80, 80, 'Delapan Puluh'),
(2, 1, 'P001', 'G0003', 'K001', 'S0003', 70, 60, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 75, 80, 'Delapan Puluh'),
(3, 1, 'P001', 'G0004', 'K001', 'S0004', 75, 80, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 75, 80, 'Delapan Puluh'),
(4, 1, 'P001', 'G0005', 'K001', 'S0006', 68, 70, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 85, 80, 'Delapan Puluh'),
(5, 1, 'P001', 'G0001', 'K002', 'S0007', 70, 70, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 75, 79, 'Tujuh Puluh Sembilan'),
(6, 1, 'P001', 'G0002', 'K002', 'S0010', 78, 80, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 75, 85, 'Delapan Puluh Lima'),
(7, 1, 'P001', 'G0002', 'K002', 'S0009', 78, 80, 75, 60, 75, 80, 75, 60, 75, 60, 60, 60, 60, 75, 80, 'Delapan Puluh'),
(8, 1, 'P001', 'G0002', 'K002', 'S0008', 85, 80, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 85, 90, 'Sembilan Puluh'),
(9, 1, 'P001', 'G0002', 'K002', 'S0005', 75, 70, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 75, 80, 'Delapan Puluh'),
(10, 1, 'P001', 'G0001', 'K002', 'S0007', 80, 100, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 100, 90, 'Sembilan Puluh'),
(19, 2, 'P001', 'G0006', 'K004', 'S0016', 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 'ok'),
(12, 2, 'P008', 'G0006', 'K002', 'S0009', 80, 70, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 90, 80, 'Delapan Puluh'),
(13, 1, 'P003', 'G0005', 'K003', 'S0002', 80, 80, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 75, 80, 'Delapan Puluh'),
(14, 1, 'P003', 'G0002', 'K003', 'S0002', 80, 80, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 80, 80, 'Selapan Puluh'),
(15, 1, 'P004', 'G0006', 'K001', 'S0005', 50, 0, 75, 60, 75, 60, 75, 60, 75, 60, 60, 60, 60, 80, 80, 'Delapan Puluh'),
(18, 1, 'P010', 'G0006', 'K001', 'S0006', 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 80, 'LULUS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `nilai`
--
ALTER TABLE `nilai`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `nilai`
--
ALTER TABLE `nilai`
MODIFY `id` int(5) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=20;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
