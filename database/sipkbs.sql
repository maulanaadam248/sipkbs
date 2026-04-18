-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 03:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sipkbs`
--

-- --------------------------------------------------------

--
-- Table structure for table `balai`
--

CREATE TABLE `balai` (
  `id_balai` int(11) NOT NULL,
  `nama_balai` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `balai`
--

INSERT INTO `balai` (`id_balai`, `nama_balai`) VALUES
(1, 'PALMA'),
(2, 'TRI'),
(3, 'TAS'),
(4, 'TROA');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `balai_id` int(11) NOT NULL,
  `komoditas` varchar(100) NOT NULL,
  `kelompok_komoditas` varchar(100) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `harga_satuan` int(11) DEFAULT NULL,
  `varietas` varchar(100) DEFAULT NULL,
  `kelas_benih` varchar(50) DEFAULT NULL,
  `status_ketersediaan` varchar(50) DEFAULT NULL,
  `jumlah_benih` varchar(50) DEFAULT NULL,
  `bulan` varchar(20) DEFAULT NULL,  
  `tahun` varchar(10) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `balai_id`, `komoditas`, `kelompok_komoditas`, `satuan`, `harga_satuan`, `varietas`, `kelas_benih`, `status_ketersediaan`, `jumlah_benih`, `bulan`, `tahun`, `deskripsi`, `foto`, `created_at`) VALUES
(1, 1, 'Kelapa', 'Dalam', 'Benih', 5000, 'Mapanget', 'Benih Pokok', 'Tersedia', '6000', 'Agustus', '2025', 'Benih kelapa kualitas unggul dengan produksi tinggi', NULL, '2026-03-11 02:01:19'),
(2, 2, 'Nilam', '-', 'Bibit', 3000, 'Sidikalang', 'Benih Sebar', 'Tersedia', '2500', 'Agustus', '2025', 'Benih nilam varietas unggul untuk dataran rendah', NULL, '2026-03-11 02:01:19'),
(3, 3, 'Lada', '-', 'Bibit', 4000, 'Petaling', 'Benih Sebar', 'Tidak Tersedia', '0', 'Agustus', '2025', 'Stok habis untuk sementara', NULL, '2026-03-11 02:01:19'),
(4, 4, 'Vanili', '-', 'Polybag', 10000, 'Vania 1', 'Benih Pokok', 'Tersedia', '3460', 'Agustus', '2025', 'Vanili bourbon dengan aroma khas', 'vanili_benih.jpg', '2026-03-11 02:01:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','operator') NOT NULL,
  `balai_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`, `balai_id`, `created_at`) VALUES
(1, 'Administrator', 'admin', 'admin123', 'admin', NULL, '2026-03-11 02:01:19'),
(2, 'Operator Palma', 'palma', '123456', 'operator', 1, '2026-03-11 02:01:19'),
(3, 'Operator TRI', 'tri', '123456', 'operator', 2, '2026-03-11 02:01:19'),
(4, 'Operator TAS', 'tas', '123456', 'operator', 3, '2026-03-11 02:01:19'),
(5, 'Operator TROA', 'troa', '123456', 'operator', 4, '2026-03-11 02:01:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `balai`
--
ALTER TABLE `balai`
  ADD PRIMARY KEY (`id_balai`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `balai_id` (`balai_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `balai_id` (`balai_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `balai`
--
ALTER TABLE `balai`
  MODIFY `id_balai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`balai_id`) REFERENCES `balai` (`id_balai`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`balai_id`) REFERENCES `balai` (`id_balai`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
