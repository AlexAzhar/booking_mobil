-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2025 at 06:16 AM
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
-- Database: `booking_mobil`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `status` enum('in_progress','done') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vehicle_id` int(11) DEFAULT NULL,
  `vehicle_name_snapshot` varchar(255) DEFAULT NULL,
  `plate_number_snapshot` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `booking_id`, `driver_id`, `status`, `created_at`, `vehicle_id`, `vehicle_name_snapshot`, `plate_number_snapshot`) VALUES
(1, 1, 5, 'done', '2025-05-23 01:13:58', NULL, NULL, NULL),
(2, 2, 5, 'done', '2025-05-23 01:51:57', NULL, NULL, NULL),
(3, 3, 5, 'done', '2025-05-23 01:57:50', NULL, NULL, NULL),
(4, 4, 5, 'done', '2025-05-23 03:04:13', NULL, NULL, NULL),
(5, 5, 5, 'done', '2025-05-28 02:34:29', NULL, NULL, NULL),
(6, 6, 5, 'done', '2025-05-28 04:19:20', NULL, NULL, NULL),
(7, 7, 7, 'done', '2025-05-28 04:19:23', NULL, NULL, NULL),
(8, 8, 5, 'done', '2025-06-02 04:07:54', 7, NULL, NULL),
(9, 9, 7, 'done', '2025-06-02 04:25:54', 8, NULL, NULL),
(10, 10, 5, 'done', '2025-06-02 04:26:35', 8, NULL, NULL),
(11, 11, 7, 'done', '2025-06-02 04:30:05', 4, NULL, NULL),
(12, 12, 7, 'done', '2025-06-02 04:30:50', 7, NULL, NULL),
(13, 13, 5, 'done', '2025-06-02 04:32:48', 10, 'Kijang', 'C 1234 C'),
(14, 14, 5, 'done', '2025-06-02 04:35:43', 9, NULL, NULL),
(15, 15, 5, 'done', '2025-06-02 04:48:00', 11, 'Kijang', 'A 1234 A'),
(16, 16, 7, 'done', '2025-06-03 01:25:51', 14, NULL, NULL),
(17, 18, 5, 'done', '2025-06-03 01:29:23', 12, NULL, NULL),
(18, 19, 5, 'done', '2025-06-03 03:01:04', 13, NULL, NULL),
(19, 17, 7, 'done', '2025-06-03 03:06:01', 12, NULL, NULL),
(20, 20, 7, 'done', '2025-06-03 03:16:08', 12, NULL, NULL),
(21, 22, 5, 'done', '2025-06-03 03:16:53', 12, NULL, NULL),
(22, 23, 7, 'done', '2025-06-03 03:25:13', 12, NULL, NULL),
(23, 21, 5, 'in_progress', '2025-06-03 03:33:11', 12, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pickup_location` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time NOT NULL,
  `status` enum('pending','assigned','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `passenger_count` int(11) DEFAULT 1,
  `agenda` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `pickup_location`, `destination`, `date`, `time`, `status`, `created_at`, `passenger_count`, `agenda`) VALUES
(1, 4, 'kampus 1', 'rita super mall', '2025-05-23', '00:00:00', 'completed', '2025-05-23 01:09:38', 1, NULL),
(2, 4, 'kampus 3', 'kampus 1', '2025-05-23', '00:00:00', 'completed', '2025-05-23 01:51:41', 1, NULL),
(3, 4, 'pwt', 'rumah', '2025-05-23', '09:00:00', 'completed', '2025-05-23 01:57:35', 1, NULL),
(4, 4, 'rumah', 'pwt', '2025-05-23', '11:03:00', 'completed', '2025-05-23 03:03:47', 1, NULL),
(5, 4, 'pondok', 'jakarta', '2025-05-28', '00:00:00', 'completed', '2025-05-28 02:18:21', 1, NULL),
(6, 4, 'kampus 1', 'rita super mall', '2025-05-28', '01:03:00', 'completed', '2025-05-28 03:03:31', 1, NULL),
(7, 6, 'kampus 2', 'els', '2025-05-29', '15:00:00', 'completed', '2025-05-28 04:18:34', 1, NULL),
(8, 6, 'kampus 1', 'rita super mall', '2025-06-02', '01:00:00', 'completed', '2025-06-02 03:47:43', 1, NULL),
(9, 4, 'kampus 3', 'kampus 1', '2025-06-03', '01:00:00', 'completed', '2025-06-02 04:25:28', 1, NULL),
(10, 6, 'kampus 1', 'rita super mall', '2025-06-04', '02:00:00', 'completed', '2025-06-02 04:26:13', 1, NULL),
(11, 4, 'pwt', 'rumah', '2025-06-02', '02:00:00', 'completed', '2025-06-02 04:29:34', 1, NULL),
(12, 6, 'pondok', 'jakarta', '2025-06-03', '01:01:00', 'completed', '2025-06-02 04:30:35', 1, NULL),
(13, 4, 'kampus 1', 'rita super mall', '2025-06-02', '01:01:00', 'completed', '2025-06-02 04:32:36', 1, NULL),
(14, 4, 'kampus 1', 'rita super mall', '2025-06-03', '01:01:00', 'completed', '2025-06-02 04:35:32', 1, NULL),
(15, 4, 'kampus 1', 'rita super mall', '2025-06-02', '01:00:00', 'completed', '2025-06-02 04:47:49', 1, NULL),
(16, 8, 'kampus 1', 'els', '2025-06-03', '01:00:00', 'completed', '2025-06-03 01:24:55', 1, NULL),
(17, 4, 'kampus 3', 'kampus 1', '2025-06-03', '02:00:00', 'completed', '2025-06-03 01:26:20', 1, NULL),
(18, 8, 'kampus 1', 'purwokereto', '2025-06-03', '03:00:00', 'completed', '2025-06-03 01:28:50', 1, NULL),
(19, 8, 'kampus 1', 'rita super mall', '2025-06-04', '02:00:00', 'completed', '2025-06-03 02:29:39', 12, 'jalan jalan'),
(20, 8, 'pwt', 'els', '2025-06-03', '01:12:00', 'completed', '2025-06-03 03:06:34', 1, 'main'),
(21, 8, 'kampus 1', 'kampus 1', '2025-06-03', '01:02:00', 'assigned', '2025-06-03 03:15:18', 1, 'rahasia'),
(22, 8, 'pondok', 'kampus 1', '2025-06-03', '02:03:00', 'completed', '2025-06-03 03:15:30', 1, 'asd'),
(23, 8, 'kampus 1', 'rita super mall', '2025-06-03', '02:03:00', 'completed', '2025-06-03 03:16:34', 1, 'sewf');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','user','driver') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `username`, `password`, `role`) VALUES
(2, 'lexse', 'lexse', 'lexse', '$2y$10$Zjfm54xD.oDHahJz1WP/FeXjVU7XLLm2DFTLTIdSDZxLlnVAYFHfe', 'superadmin'),
(3, 'wartono', 'wartono@gmail.com', 'wartono', '$2y$10$FT9KRdsAkSdcNACxnTt6iep7qErJTwVbQrHG0W1oEaQ.9yA.EAKWS', 'admin'),
(4, 'agam', 'agam@gmail.com', 'agam', '$2y$10$uQLV5tUSjyV131HT5NsdXOyuuIbmlYhBU.JJ4o65yWBYTj6h7JYyC', 'user'),
(5, 'driver', 'driver@gmail.com', 'driver', '$2y$10$FyZ/f.eiW/Dtok8J3b4q5OA2o0e0RQX9q8sCWXWm7gORCk73lJ8XC', 'driver'),
(6, 'rahman', 'rahman@gmail.com', 'rahman', '$2y$10$IfQZrIGisC6BfwhogR.9Y.IDV4piXpr88yLbhFfYBq8b6v8G2hA2u', 'user'),
(7, 'driver2', 'driver2@gmail.com', 'driver2', '$2y$10$Dc.3uJwRDMh2hJIiy0A5CeX9T3mAHU26DWZiBWHXE9n1VlNFwGQzS', 'driver'),
(8, 'alex', 'alex@gmail.com', 'alex', '$2y$10$rjR1jzPVHvhHIp/j0PFhYuETusv0aWKYOWVPlqz9zIzKDrCs9kuee', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `plate_number` varchar(20) DEFAULT NULL,
  `vehicle_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `plate_number`, `vehicle_name`) VALUES
(12, 'A 1234 A', 'Avanza'),
(13, 'AA 1990 BB', 'Xenia'),
(14, 'C 1234 C', 'Kijang'),
(15, 'Z1234 Z', 'Pajero'),
(16, 'D 5637 CS', 'Luxio');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
