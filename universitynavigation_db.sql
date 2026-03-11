-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 11:25 PM
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
  `name_en` varchar(255) DEFAULT NULL,
  `name_th` varchar(255) DEFAULT NULL,
  `faculty_name` varchar(100) NOT NULL DEFAULT 'General',
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

INSERT INTO `buildings` (`id`, `name_en`, `name_th`, `faculty_name`, `department_id`, `latitude`, `longitude`, `description`, `image_url`, `created_at`) VALUES
(1, 'Computer Engineering', 'อาคารปฏิบัติการวิศวกรรมคอมพิวเตอร์', 'General', 1, 14.03589300, 100.72550600, NULL, 'uploads/buildings/1773267822_Screenshot 2026-03-09 155103.png', '2025-12-04 04:22:17'),
(2, 'department of electrical engineering', 'อาคารปฏิบัติการวิศวกรรมไฟฟ้า', 'General', 2, 14.03625200, 100.72529600, NULL, 'uploads/buildings/1773046170_ac4ac335-9e75-4be7-a91f-801498245e04.jpg', '2025-12-04 04:22:17'),
(6, 'Department of Textile Engineer', NULL, 'General', NULL, 14.03815400, 100.72642200, NULL, 'uploads/buildings/1773045661_7ff35824-0bbc-47a5-80b8-8eaece17f0a8.jpg', '2026-03-09 08:41:01'),
(7, 'OFFICE OF THE PRESIDENT', NULL, 'General', NULL, 14.03377700, 100.72911500, NULL, 'uploads/buildings/1773045723_b3ed6cb4-2609-45a8-922b-84f274a0f5dc.jpg', '2026-03-09 08:42:03'),
(8, 'I-work (Helpdesk)', NULL, 'General', NULL, 14.03497600, 100.72552100, NULL, 'uploads/buildings/1773045896_3914fe52-512a-44a2-bfed-3406dd657cc6.jpg', '2026-03-09 08:44:56'),
(9, 'Central Laboratory', NULL, 'General', NULL, 14.03862000, 100.72470800, NULL, 'uploads/buildings/1773045950_86a69c05-b259-4f6e-a68c-1f5de83457b9.jpg', '2026-03-09 08:45:50'),
(10, 'Department of Aerospace Engineering', NULL, 'General', NULL, 14.03923100, 100.72473200, NULL, 'uploads/buildings/1773046023_38c971e3-3cd5-49f9-9b0d-ba2831d07627.jpg', '2026-03-09 08:47:03'),
(11, 'Division of Student Development', NULL, 'General', NULL, 14.03637900, 100.72441300, NULL, 'uploads/buildings/1773046081_09f8ac83-bba6-4670-93a6-f5adf0bbc893.jpg', '2026-03-09 08:48:01'),
(12, 'Faculty of Engineering Building (Multipurpose)', NULL, 'General', NULL, 14.03631700, 100.72613800, NULL, 'uploads/buildings/1773046467_ea3e820b-ce3e-40c1-b31a-825319ec34d2.jpg', '2026-03-09 08:54:27'),
(13, 'Department of Electronics and Telecommunication Engineering', NULL, 'General', NULL, 14.03745400, 100.72612700, NULL, 'uploads/buildings/1773046581_154842bd-2d3e-4919-b5b9-6745d9b92888.jpg', '2026-03-09 08:56:21'),
(14, 'Drama & Music', NULL, 'General', NULL, 14.03955800, 100.73086000, NULL, 'uploads/buildings/1773047005_96432328-6473-41cf-8dcc-3e98f5595569.jpg', '2026-03-09 09:03:25'),
(17, 'Faculty of Architecture', NULL, 'General', NULL, 14.03846100, 100.73099000, NULL, 'uploads/buildings/1773047621_4f4da8c1-efd5-4606-b065-b01e302dea0a.jpg', '2026-03-09 09:13:41');

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
(1, 'Computer Engineering', '2025-12-04 04:22:17', 2),
(2, 'Electrical Engineering', '2025-12-04 04:22:17', 2),
(3, 'Civil Engineering', '2025-12-04 04:22:17', 2),
(4, 'Marketing', '2025-12-04 04:22:17', 3),
(5, 'Accounting', '2025-12-04 04:22:17', 3),
(6, 'Interior Design', '2025-12-04 04:22:17', 1);

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
(1, 'ส่วนกลาง (General)', '2026-03-09 09:55:54'),
(2, 'คณะวิศวกรรมศาสตร์ (Engineering)', '2026-03-09 09:55:54'),
(3, 'คณะบริหารธุรกิจ (Business)', '2026-03-09 09:55:54'),
(4, 'คณะวิทยาศาสตร์และเทคโนโลยี (Science)', '2026-03-09 09:55:54'),
(5, 'คณะศิลปกรรมศาสตร์ (Fine Arts)', '2026-03-09 09:55:54'),
(6, 'คณะเทคโนโลยีการเกษตร (Agriculture)', '2026-03-09 09:55:54'),
(7, 'คณะครุศาสตร์อุตสาหกรรม (Technical Education)', '2026-03-09 09:55:54');

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
(43, 4, 1, 'Room', '2026-03-06 07:25:28'),
(45, 3, 1, 'Building', '2026-03-09 09:12:37'),
(47, 4, 6, 'Room', '2026-03-11 22:15:10');

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
(7, 3, 1, 'Other', '???', 'uploads/reports/1772755513_34.jpg', 'resolved', '2026-03-06 00:05:13', '2026-03-08 19:03:06');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `building_id` int(11) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `name_th` varchar(255) DEFAULT NULL,
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

INSERT INTO `rooms` (`id`, `building_id`, `name_en`, `name_th`, `room_number`, `floor`, `usage_type`, `floor_layout_url`, `created_at`, `image_url`) VALUES
(1, 1, 'Computer Programming Lab', 'ห้องปฏิบัติการเขียนโปรแกรมคอมพิวเตอร์', '16103', 1, 'Lab', 'uploads/layouts/1772666602_layout_Untitled Diagram.drawio.png', '2025-12-04 04:22:17', 'uploads/images/1772666602_img_1f6aa070-5c8c-4f6d-a500-6927ef028bf7 (1).jpg'),
(2, 1, 'Lecture Room', 'ห้องบรรยาย', '16105', 1, 'Lab', NULL, '2025-12-04 04:22:17', NULL),
(4, 1, 'Instructor Room', 'ห้องพักอาจารย์', '16104', 1, 'Office', NULL, '2025-12-04 04:22:17', NULL),
(6, 2, 'High Voltage Lab', 'ห้องปฏิบัติการไฟฟ้าแรงสูง', '501', 1, 'Lab', NULL, '2025-12-04 04:22:17', NULL),
(7, 2, 'Circuit Theory Room', 'ห้องเรียนทฤษฎีวงจรไฟฟ้า', '504', 2, 'Classroom', NULL, '2025-12-04 04:22:17', NULL),
(10, 12, 'Co-Working Space 1', NULL, '1', 1, NULL, NULL, '2026-03-09 08:57:56', 'uploads/images/1773046676_1c6e1d6e-9c4c-4829-ac3f-b2423c9d7cab.jpg'),
(11, 12, 'Editorial Office of JERMUTT', 'สำนักงานวารสารวิศวกรรมศาสตร์ราชมงคลธัญบุรี', 'EN-01 105', 1, NULL, NULL, '2026-03-09 08:59:48', 'uploads/images/1773046788_6ece8c9f-5b53-4c7a-a4b0-55f2699e09f7.jpg'),
(12, 12, 'Student Union Office', 'สำนักงานสโมสรนักศึกษา', 'EN-01 104', 1, NULL, NULL, '2026-03-09 09:00:51', 'uploads/images/1773046851_7ff8ed69-5767-4443-8399-0f0f80637959.jpg'),
(13, 12, 'Co-Working Space 2', NULL, '2', 1, NULL, NULL, '2026-03-09 09:01:20', 'uploads/images/1773046880_145c4b56-f752-42da-900d-dcde70dfe5ae.jpg');

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
(1, '109395742820228100954', 'jiradech.ksr@gmail.com', 'Jiradech', '2025-12-17 04:58:41', 'https://lh3.googleusercontent.com/a/ACg8ocLwNhbaL9tTiOQ63Ky6WOVVB-xaFHZRbUhiHJLumywSz6zhdA=s96-c', 'admin', 'active'),
(3, '102637320266923646699', 'jiraki002nd@gmail.com', 'Jiraki []', '2026-01-07 15:15:12', '', 'student', 'active'),
(4, '100652209792208710272', 'jiraki001@gmail.com', 'jiraki [jiradech]', '2026-01-08 06:14:29', 'https://lh3.googleusercontent.com/a/ACg8ocKp089o4dZxZ3OIZVKNvpd7i6c_UunGZNUdD0rMp5qLdHYxfos', 'student', 'active'),
(6, '', 'fah.thongkham@gmail.com', 'วนัชพร ทองคำ', '2026-03-04 14:15:52', NULL, 'staff', 'active'),
(8, '117193423119640302623', 'ingingl484@gmail.com', 'Phopiang Punpook', '2026-03-06 09:07:55', 'https://lh3.googleusercontent.com/a/ACg8ocIRdLdkeBDsdXF84JQrM9r7mC6K2WHrJJpyrqHIgc6DaVTNCDJmIw', 'staff', 'active');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
