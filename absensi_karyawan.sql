-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 11:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_karyawan`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id_absensi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `foto_keluar` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `lat_keluar` decimal(10,8) DEFAULT NULL,
  `lon_keluar` decimal(11,8) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id_absensi`, `id_user`, `nama`, `tanggal`, `jam_masuk`, `jam_keluar`, `foto`, `foto_keluar`, `latitude`, `longitude`, `lat_keluar`, `lon_keluar`, `keterangan`) VALUES
(7, 7, 'Alif Fullah', '2025-11-26', '08:00:00', '17:00:00', NULL, NULL, NULL, NULL, NULL, NULL, ''),
(8, 8, 'Raihan Rama Putra', '2025-11-26', '18:25:27', '18:25:44', 'checkin_8_1764156327.jpg', 'checkout_8_1764156344.jpg', -6.33111080, 106.63082940, -6.33110760, 106.63078110, ''),
(11, 7, 'Alif Fullah', '2025-11-25', '07:00:00', '17:00:00', NULL, NULL, NULL, NULL, NULL, NULL, ''),
(15, 8, 'Raihan Rama Putra', '2025-11-29', '02:41:25', '02:41:35', 'checkin_8_1764358885.jpg', 'checkout_8_1764358895.jpg', -6.33111870, 106.63080090, -6.33111870, 106.63080090, ''),
(16, 7, 'Alif Fullah', '2025-11-29', '15:21:45', '15:22:08', 'checkin_7_1764404505.jpg', 'checkout_7_1764404528.jpg', -6.33652840, 106.70504770, -6.33652840, 106.70504770, ''),
(23, 7, 'Alif Fullah', '2025-12-09', '15:15:18', '15:20:02', 'checkin_7_1765268118.jpg', 'checkout_7_1765268402.jpg', -6.33114340, 106.63083800, -6.33114870, 106.63084450, ''),
(24, 7, 'Alif Fullah', '2025-12-11', '11:49:04', '11:52:25', 'checkin_7_1765428544.jpg', 'checkout_7_1765428745.jpg', -6.33113730, 106.63084570, -6.33113730, 106.63084570, ''),
(25, 7, 'Alif Fullah', '2025-12-13', '09:43:42', '09:46:43', 'checkin_7_1765593822.jpg', 'checkout_7_1765594003.jpg', -6.20625920, 106.80729600, -6.20625920, 106.80729600, ''),
(26, 7, 'Alif Fullah', '2025-12-16', '09:21:22', '12:09:38', 'checkin_7_1765851682.jpg', 'checkout_7_1765861778.jpg', -6.33111690, 106.63083880, -6.42908160, 106.81384960, ''),
(27, 7, 'Alif Fullah', '2025-12-18', '11:20:02', '16:03:11', 'checkin_7_1766031602.jpg', 'checkout_7_1766048591.jpg', -6.33112250, 106.63083100, -6.33112270, 106.63084130, '');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id_karyawan` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_tlp` varchar(15) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `nip`, `id_user`, `nama_lengkap`, `jenis_kelamin`, `email`, `no_tlp`, `tgl_lahir`, `alamat`, `foto`, `created_at`, `updated_at`) VALUES
(3, 'NIP001', 7, 'Alif Fullah', 'Laki-laki', 'aliffullah1103@gmail.com', '085770483751', '1998-03-15', 'Jl. Padat Karya Kec. Cisauk Kel. Cisauk Kab. Tangerang', NULL, '2025-11-29 08:56:48', '2025-12-18 03:09:11'),
(4, 'NIP002', 8, 'Raihan Rama Putra', 'Laki-laki', 'rama.putra@company.com', '081298765432', '1997-07-22', 'Jl. Sudirman No. 45, Bandung', NULL, '2025-11-29 08:56:48', '2025-11-29 08:56:48'),
(5, 'NIP003', 9, 'Rini Fatmawati', 'Perempuan', 'rini.fatmawati@gmail.com', '081345678901', '1999-12-10', 'Jl. Asia Afrika No. 78, Yogyakarta', NULL, '2025-11-29 08:56:48', '2025-12-19 03:11:15');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','karyawan') NOT NULL DEFAULT 'karyawan',
  `foto_profil` varchar(255) NOT NULL,
  `tgl_register` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama`, `username`, `password`, `role`, `foto_profil`, `tgl_register`) VALUES
(1, '', 'admin', 'admin123', 'admin', '', '2025-11-26 06:50:43'),
(7, '', 'Alif', 'alif1212', 'karyawan', 'profil_7_1766048720.png', '2025-11-26 11:15:41'),
(8, '', 'Rama', 'rama123', 'karyawan', '', '2025-11-26 11:16:00'),
(9, '', 'Rini', 'rini123', 'karyawan', '', '2025-11-26 11:16:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `user_id` (`id_user`),
  ADD KEY `tanggal` (`tanggal`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_nip` (`nip`),
  ADD KEY `idx_id_user` (`id_user`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
