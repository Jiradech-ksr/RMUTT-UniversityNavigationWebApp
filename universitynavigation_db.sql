-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 06, 2026 at 09:20 AM
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
-- Database: `universitynavigation_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `buildings`
--

CREATE TABLE `buildings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buildings`
--

INSERT INTO `buildings` (`id`, `name`, `department_id`, `latitude`, `longitude`, `description`, `image_url`, `created_at`) VALUES
(1, 'CPE Building', 1, 14.03589500, 100.72550500, NULL, 'https://example.com/cpe_building.jpg', '2025-12-04 04:22:17'),
(2, 'Electrical', 2, 14.03625200, 100.72529600, NULL, 'https://example.com/ee_building.jpg', '2025-12-04 04:22:17'),
(3, 'Business Center Tower', 4, 14.03500000, 100.72500000, NULL, 'https://example.com/business.jpg', '2025-12-04 04:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `faculty_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_at`, `faculty_id`) VALUES
(1, 'Computer Engineering', '2025-12-04 04:22:17', 1),
(2, 'Electrical Engineering', '2025-12-04 04:22:17', 1),
(3, 'Civil Engineering', '2025-12-04 04:22:17', 1),
(4, 'Marketing', '2025-12-04 04:22:17', 2),
(5, 'Accounting', '2025-12-04 04:22:17', 2),
(6, 'Interior Design', '2025-12-04 04:22:17', 3);

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `name`, `created_at`) VALUES
(1, 'Faculty of Engineering', '2025-12-04 04:22:17'),
(2, 'Faculty of Business Administration', '2025-12-04 04:22:17'),
(3, 'Faculty of Architecture', '2025-12-04 04:22:17');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `room_id`, `created_at`) VALUES
(26, 3, 7, '2026-01-07 15:15:45'),
(31, 3, 1, '2026-03-05 14:21:12'),
(32, 4, 1, '2026-03-06 07:25:28');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `location_type` varchar(50) NOT NULL DEFAULT 'Room',
  `visited_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `user_id`, `location_id`, `location_type`, `visited_at`) VALUES
(33, 4, 2, 'Building', '2026-03-06 06:41:01'),
(34, 4, 1, 'Room', '2026-03-06 06:48:02'),
(35, 4, 6, 'Room', '2026-03-06 06:46:59'),
(36, 4, 1, 'Building', '2026-03-06 06:47:06'),
(37, 4, 4, 'Room', '2026-03-06 06:47:36'),
(38, 4, 2, 'Building', '2026-03-06 06:51:38'),
(39, 4, 1, 'Room', '2026-03-06 06:51:48'),
(40, 4, 1, 'Room', '2026-03-06 06:51:49'),
(41, 4, 1, 'Room', '2026-03-06 06:52:01'),
(42, 4, 1, 'Room', '2026-03-06 06:52:02'),
(43, 4, 1, 'Room', '2026-03-06 07:25:28');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `issue_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `room_id`, `issue_type`, `description`, `image_url`, `status`, `created_at`, `resolved_at`) VALUES
(6, 3, 4, 'Fix Equipment', 'asd', 'uploads/reports/1772755461_34.jpg', 'resolved', '2026-03-06 00:04:21', '2026-03-06 07:04:39'),
(7, 3, 1, 'Other', '???', 'uploads/reports/1772755513_34.jpg', 'pending', '2026-03-06 00:05:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `building_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `room_number` varchar(50) DEFAULT NULL,
  `floor` int(11) DEFAULT NULL,
  `usage_type` enum('Classroom','Office','Lab','Meeting Room','Other') DEFAULT NULL,
  `floor_layout_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `building_id`, `name`, `room_number`, `floor`, `usage_type`, `floor_layout_url`, `created_at`, `image_url`) VALUES
(1, 1, 'Computer Programming Lab', '16103', 1, 'Lab', 'uploads/layouts/1772666602_layout_Untitled Diagram.drawio.png', '2025-12-04 04:22:17', 'uploads/images/1772666602_img_1f6aa070-5c8c-4f6d-a500-6927ef028bf7 (1).jpg'),
(2, 1, 'Lecture Room', '16105', 1, 'Lab', NULL, '2025-12-04 04:22:17', NULL),
(4, 1, 'Instuctor Room', '16104', 1, 'Office', NULL, '2025-12-04 04:22:17', NULL),
(6, 2, 'High Voltage Lab', '501', 1, 'Lab', NULL, '2025-12-04 04:22:17', NULL),
(7, 2, 'Circuit Theory Room', '504', 2, 'Classroom', NULL, '2025-12-04 04:22:17', NULL),
(8, 3, 'Grand Seminar Hall', '101', 1, 'Meeting Room', NULL, '2025-12-04 04:22:17', NULL),
(9, 3, 'Marketing Class A', '202', 2, 'Classroom', NULL, '2025-12-04 04:22:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo_url` text DEFAULT NULL,
  `role` enum('student','staff','technician','admin') DEFAULT 'student',
  `status` enum('active','banned') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `google_id`, `email`, `display_name`, `created_at`, `photo_url`, `role`, `status`) VALUES
(1, '109395742820228100954', 'jiradech.ksr@gmail.com', 'Jiradech', '2025-12-17 04:58:41', '', 'admin', 'active'),
(3, '102637320266923646699', 'jiraki002nd@gmail.com', 'Jiraki []', '2026-01-07 15:15:12', '', 'student', 'active'),
(4, '100652209792208710272', 'jiraki001@gmail.com', 'jiraki [jiradech]', '2026-01-08 06:14:29', 'https://lh3.googleusercontent.com/a/ACg8ocKp089o4dZxZ3OIZVKNvpd7i6c_UunGZNUdD0rMp5qLdHYxfos', 'student', 'active'),
(6, '', 'fah.thongkham@gmail.com', 'วนัชพร ทองคำ', '2026-03-04 14:15:52', NULL, 'staff', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buildings`
--
ALTER TABLE `buildings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_faculty` (`faculty_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`room_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`location_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `building_id` (`building_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buildings`
--
ALTER TABLE `buildings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buildings`
--
ALTER TABLE `buildings`
  ADD CONSTRAINT `buildings_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `history_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
