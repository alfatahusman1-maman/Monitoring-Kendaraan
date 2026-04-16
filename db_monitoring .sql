-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 24, 2025 at 01:09 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `bbm`
--

CREATE TABLE `bbm` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kendaraan` int NOT NULL,
  `tanggal` date NOT NULL,
  `liter` decimal(8,2) DEFAULT NULL,
  `status` enum('Pending','Disetujui','Ditolak','Selesai') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id` int NOT NULL,
  `no_polisi` varchar(20) NOT NULL,
  `merk` varchar(50) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `tahun` int DEFAULT NULL,
  `jenis` enum('Roda Dua','Roda Empat') DEFAULT NULL,
  `kondisi` enum('Baik','Kurang Baik') DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kendaraan`
--

INSERT INTO `kendaraan` (`id`, `no_polisi`, `merk`, `tipe`, `tahun`, `jenis`, `kondisi`, `status`) VALUES
(1, 'H 1234 AB', 'Toyota', 'Avanza', 2020, NULL, NULL, 'Aktif'),
(2, 'H 5678 CD', 'Honda', 'Civic', 2021, NULL, NULL, 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pegawai_detail`
--

CREATE TABLE `pegawai_detail` (
  `id` int NOT NULL,
  `id_pegawai` int NOT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `alamat` text,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `kendaraan` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servis`
--

CREATE TABLE `servis` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `id_kendaraan` int NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_servis` varchar(100) DEFAULT NULL,
  `biaya` decimal(12,2) DEFAULT NULL,
  `status` enum('Pending','Disetujui','Ditolak','Selesai') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `servis`
--

INSERT INTO `servis` (`id`, `id_user`, `id_kendaraan`, `tanggal`, `jenis_servis`, `biaya`, `status`, `created_at`) VALUES
(3, 8, 1, '2025-09-24', 'Ganti Oli', '50.00', 'Disetujui', '2025-09-24 12:54:54');

-- --------------------------------------------------------

--
-- Table structure for table `tanda_terima`
--

CREATE TABLE `tanda_terima` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `jenis` enum('Servis','BBM') NOT NULL,
  `id_transaksi` int NOT NULL,
  `tanggal_serah` date NOT NULL,
  `status` enum('Belum Diverifikasi','Terverifikasi') DEFAULT 'Belum Diverifikasi',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_text` varchar(255) DEFAULT NULL,
  `plain_password` varchar(255) DEFAULT NULL,
  `role` enum('Admin','User','Keuangan') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `password_text`, `plain_password`, `role`, `created_at`) VALUES
(1, 'Admin Utama', 'admin', '0192023a7bbd73250516f069df18b500', NULL, 'admin123', 'Admin', '2025-09-18 09:31:27'),
(3, 'Keuangan 1', 'keuangan', '87cbf810625de2ff054ac8b841e135df', NULL, 'keuangan123', 'Keuangan', '2025-09-18 09:31:27'),
(8, 'Sunardi', 'sunardi', '082e6707d966930dc12d384e56b4c58e', 'sunardi123', 'sunardi123', 'User', '2025-09-24 04:19:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bbm`
--
ALTER TABLE `bbm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kendaraan` (`id_kendaraan`);

--
-- Indexes for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pegawai_detail`
--
ALTER TABLE `pegawai_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pegawai` (`id_pegawai`);

--
-- Indexes for table `servis`
--
ALTER TABLE `servis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kendaraan` (`id_kendaraan`);

--
-- Indexes for table `tanda_terima`
--
ALTER TABLE `tanda_terima`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bbm`
--
ALTER TABLE `bbm`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kendaraan`
--
ALTER TABLE `kendaraan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pegawai_detail`
--
ALTER TABLE `pegawai_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servis`
--
ALTER TABLE `servis`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tanda_terima`
--
ALTER TABLE `tanda_terima`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bbm`
--
ALTER TABLE `bbm`
  ADD CONSTRAINT `bbm_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bbm_ibfk_2` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pegawai_detail`
--
ALTER TABLE `pegawai_detail`
  ADD CONSTRAINT `fk_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `servis`
--
ALTER TABLE `servis`
  ADD CONSTRAINT `servis_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `servis_ibfk_2` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tanda_terima`
--
ALTER TABLE `tanda_terima`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
