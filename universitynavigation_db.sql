-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2026 at 06:57 AM
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
  `department_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `responsible_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buildings`
--

INSERT INTO `buildings` (`id`, `name_en`, `name_th`, `department_id`, `faculty_id`, `latitude`, `longitude`, `description`, `image_url`, `responsible_email`, `created_at`) VALUES
(1, 'Computer Engineering', 'อาคารปฏิบัติการวิศวกรรมคอมพิวเตอร์', 1, 1, 14.03589300, 100.72550600, NULL, 'uploads/buildings/1773267822_Screenshot 2026-03-09 155103.png', NULL, '2025-12-04 04:22:17'),
(2, 'department of electrical engineering', 'อาคารปฏิบัติการวิศวกรรมไฟฟ้า', 2, 1, 14.03625200, 100.72529600, NULL, 'uploads/buildings/1773046170_ac4ac335-9e75-4be7-a91f-801498245e04.jpg', NULL, '2025-12-04 04:22:17'),
(6, 'Department of Textile Engineer', 'อาคารปฏิบัติการวิศวกรรมสิ่งทอ', 7, 1, 14.03815400, 100.72642200, NULL, 'uploads/buildings/1773045661_7ff35824-0bbc-47a5-80b8-8eaece17f0a8.jpg', NULL, '2026-03-09 08:41:01'),
(7, 'OFFICE OF THE PRESIDENT', 'ตึกอธิการบดี', 6, 1, 14.03377700, 100.72911500, NULL, 'uploads/buildings/1773045723_b3ed6cb4-2609-45a8-922b-84f274a0f5dc.jpg', NULL, '2026-03-09 08:42:03'),
(8, 'I-work (Helpdesk)', 'ศูนย์ช่วยเหลือด้านไอที (I-work)', NULL, NULL, 14.03497600, 100.72552100, NULL, 'uploads/buildings/1773045896_3914fe52-512a-44a2-bfed-3406dd657cc6.jpg', NULL, '2026-03-09 08:44:56'),
(9, 'Central Laboratory', 'ห้องปฏิบัติการกลาง', 2, 1, 14.03862000, 100.72470800, NULL, 'uploads/buildings/1773045950_86a69c05-b259-4f6e-a68c-1f5de83457b9.jpg', NULL, '2026-03-09 08:45:50'),
(10, 'Department of Avionic Engineering', 'อาคารปฏิบัติการวิศวกรรมการบินและอวกาศ', 3, 1, 14.03923100, 100.72473200, NULL, 'uploads/buildings/1773046023_38c971e3-3cd5-49f9-9b0d-ba2831d07627.jpg', NULL, '2026-03-09 08:47:03'),
(11, 'Division of Student Development', 'กองพัฒนานักศึกษา', NULL, NULL, 14.03637900, 100.72441300, NULL, 'uploads/buildings/1773046081_09f8ac83-bba6-4670-93a6-f5adf0bbc893.jpg', NULL, '2026-03-09 08:48:01'),
(12, 'Faculty of Engineering Building (Multipurpose)', 'อาคารเอนกประสงค์ คณะวิศวกรรมศาสตร์', 12, 1, 14.03631700, 100.72613800, NULL, 'uploads/buildings/1773046467_ea3e820b-ce3e-40c1-b31a-825319ec34d2.jpg', NULL, '2026-03-09 08:54:27'),
(13, 'Department of Electronics and Telecommunication Engineering', 'อาคารปฏิบัติการวิศวกรรมอิเล็กทรอนิกส์และโทรคมนาคม', 6, 1, 14.03745400, 100.72612700, NULL, 'uploads/buildings/1773046581_154842bd-2d3e-4919-b5b9-6745d9b92888.jpg', NULL, '2026-03-09 08:56:21'),
(14, 'Drama & Music', 'สาขาวิชาดนตรีและนาฏศิลป์', NULL, NULL, 14.03955800, 100.73086000, NULL, 'uploads/buildings/1773047005_96432328-6473-41cf-8dcc-3e98f5595569.jpg', NULL, '2026-03-09 09:03:25'),
(17, 'Faculty of Architecture', 'คณะสถาปัตยกรรมศาสตร์', 1, 1, 14.03846100, 100.73099000, NULL, 'uploads/buildings/1773047621_4f4da8c1-efd5-4606-b065-b01e302dea0a.jpg', NULL, '2026-03-09 09:13:41'),
(18, 'EV', 'ลานจอดรถ', 2, 1, 14.03588600, 100.72518300, NULL, 'uploads/buildings/1773311900_Screenshot 2026-03-09 155103.png', NULL, '2026-03-12 10:38:20'),
(19, 'EV', 'ลานจอดรถ', 6, 1, 14.03588600, 100.72518300, NULL, 'uploads/buildings/1773311915_Screenshot 2026-03-09 155103.png', NULL, '2026-03-12 10:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `faculty_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name_en`, `name_th`, `created_at`, `faculty_id`) VALUES
(1, 'Department of Civil Engineering', 'ภาควิชาวิศวกรรมโยธา', '2025-12-04 04:22:17', 1),
(2, 'Department of Mechanical Engineering', 'ภาควิชาวิศวกรรมเครื่องกล', '2025-12-04 04:22:17', 1),
(3, 'Department of Computer Engineering', 'ภาควิชาวิศวกรรมคอมพิวเตอร์', '2025-12-04 04:22:17', 1),
(4, 'Department of Industrial Engineering', 'ภาควิชาวิศวกรรมอุตสาหการ', '2025-12-04 04:22:17', 1),
(5, 'Department of Electrical Engineering', 'ภาควิชาวิศวกรรมไฟฟ้า', '2025-12-04 04:22:17', 1),
(6, 'Department of Electronics and Telecommunication Engineering', 'ภาควิชาวิศวกรรมอิเล็กทรอนิกส์และโทรคมนาคม', '2025-12-04 04:22:17', 1),
(7, 'Department of Textile Engineering', 'ภาควิชาวิศวกรรมสิ่งทอ', '2026-03-22 04:58:35', 1),
(8, 'Department of Materials and Metallurgical Engineering', 'ภาควิชาวิศวกรรมวัสดุและโลหการ', '2026-03-22 04:58:35', 1),
(9, 'Department of Agricultural Engineering', 'ภาควิชาวิศวกรรมเกษตร', '2026-03-22 04:58:35', 1),
(10, 'Department of Chemical Engineering', 'ภาควิชาวิศวกรรมเคมี', '2026-03-22 04:58:35', 1),
(12, 'general', 'ทั่วไป', '2026-03-22 05:43:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` int(11) NOT NULL,
  `name_en` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_th` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`id`, `name_en`, `name_th`, `created_at`) VALUES
(1, 'Faculty of Engineering', 'คณะวิศวกรรมศาสตร์ (วศ.)', '2026-03-09 09:55:54'),
(2, 'Faculty of Business Administration', 'คณะบริหารธุรกิจ (บธ.)', '2026-03-09 09:55:54'),
(3, 'Faculty of Home Economics Technology', 'คณะเทคโนโลยีคหกรรมศาสตร์ (ทค.)', '2026-03-09 09:55:54'),
(4, 'Faculty of Fine and Applied Arts', 'คณะศิลปกรรมศาสตร์ (ศก.)', '2026-03-09 09:55:54'),
(5, 'Faculty of Agricultural Technology', 'คณะเทคโนโลยีการเกษตร (ทก.)', '2026-03-09 09:55:54'),
(6, 'Faculty of Technical Education', 'คณะครุศาสตร์อุตสาหกรรม (คอ.)\r\n', '2026-03-09 09:55:54'),
(7, 'Faculty of Architecture', 'คณะสถาปัตยกรรมศาสตร์ (สถ.)\r\n', '2026-03-09 09:55:54'),
(8, 'Faculty of Science and Technology', 'คณะวิทยาศาสตร์และเทคโนโลยี (วท.)\r\n', '2026-03-22 04:37:01'),
(9, 'Faculty of Mass Communication Technology', 'คณะเทคโนโลยีสื่อสารมวลชน (ทสม.)\r\n', '2026-03-22 04:37:01'),
(10, 'Faculty of Liberal Arts', 'คณะศิลปศาสตร์ (ศศ.)\r\n', '2026-03-22 04:37:33'),
(11, 'Faculty of Integrative Medicine', 'คณะการแพทย์บูรณาการ (กพบ.)\r\n', '2026-03-22 04:37:33'),
(12, 'Faculty of Nursing', 'คณะพยาบาลศาสตร์\r\n', '2026-03-22 04:38:02'),
(14, 'Central Administration Division', 'กองกลางสำนักงานอธิการบดี', '2026-03-22 05:46:48');

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
(33, 15, 14, '2026-03-12 10:56:44'),
(34, 15, 1, '2026-03-12 10:57:20'),
(35, 1, 4, '2026-03-22 03:06:25');

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
(45, 3, 1, 'Building', '2026-03-09 09:12:37'),
(48, 9, 1, 'Room', '2026-03-11 23:10:06'),
(49, 9, 1, 'Building', '2026-03-11 23:10:11'),
(50, 9, 6, 'Room', '2026-03-11 23:13:38'),
(51, 9, 6, 'Room', '2026-03-11 23:23:04'),
(52, 9, 6, 'Room', '2026-03-12 00:50:30'),
(53, 9, 6, 'Building', '2026-03-12 07:06:42'),
(54, 9, 1, 'Room', '2026-03-12 07:07:36'),
(55, 9, 1, 'Room', '2026-03-12 07:08:06'),
(60, 15, 1, 'Room', '2026-03-12 09:24:34'),
(61, 9, 14, 'Room', '2026-03-12 10:54:58'),
(62, 9, 14, 'Room', '2026-03-12 10:55:37'),
(63, 9, 14, 'Room', '2026-03-12 10:56:13'),
(64, 15, 14, 'Room', '2026-03-12 10:56:43'),
(65, 15, 14, 'Room', '2026-03-12 10:57:03'),
(66, 15, 14, 'Room', '2026-03-12 10:57:08'),
(67, 15, 1, 'Room', '2026-03-12 10:57:19'),
(68, 15, 1, 'Room', '2026-03-12 10:57:26'),
(69, 15, 14, 'Room', '2026-03-12 11:00:46'),
(70, 9, 2, 'Room', '2026-03-14 07:44:01'),
(71, 9, 1, 'Room', '2026-03-14 07:56:01'),
(72, 9, 4, 'Room', '2026-03-14 07:57:40'),
(73, 9, 1, 'Room', '2026-03-14 07:57:46'),
(74, 1, 1, 'Building', '2026-03-22 03:04:55'),
(75, 1, 4, 'Room', '2026-03-22 03:05:08'),
(76, 1, 4, 'Room', '2026-03-22 03:06:24');

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
(7, 3, 1, 'Other', '???', 'uploads/reports/1772755513_34.jpg', 'resolved', '2026-03-06 00:05:13', '2026-03-08 19:03:06'),
(8, 15, 1, 'Other', 'image from class.\n', 'uploads/reports/1773307549_1000042534.jpg', 'resolved', '2026-03-12 09:25:49', '2026-03-12 17:46:20');

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
  `details` text DEFAULT NULL,
  `usage_type` enum('Classroom','Office','Lab','Meeting Room','Other') DEFAULT NULL,
  `floor_layout_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `building_id`, `name_en`, `name_th`, `room_number`, `floor`, `details`, `usage_type`, `floor_layout_url`, `created_at`, `image_url`) VALUES
(1, 1, 'Computer Programming Lab', 'ห้องปฏิบัติการเขียนโปรแกรมคอมพิวเตอร์', '16103', 1, NULL, 'Lab', 'uploads/layouts/1772666602_layout_Untitled Diagram.drawio.png', '2025-12-04 04:22:17', 'uploads/images/1772666602_img_1f6aa070-5c8c-4f6d-a500-6927ef028bf7 (1).jpg'),
(2, 1, 'Lecture Room', 'ห้องเรียน', '16105', 1, NULL, 'Lab', 'uploads/layouts/1773269458_Untitled Diagram.drawio.png', '2025-12-04 04:22:17', NULL),
(4, 1, 'Instructor Room', 'ห้องพักอาจารย์', '16104', 1, NULL, 'Office', 'uploads/layouts/1773269450_Untitled Diagram.drawio.png', '2025-12-04 04:22:17', NULL),
(6, 2, 'High Voltage Lab', 'ห้องปฏิบัติการไฟฟ้าแรงสูง', '501', 1, NULL, 'Lab', NULL, '2025-12-04 04:22:17', NULL),
(7, 2, 'Circuit Theory Room', 'ห้องเรียนทฤษฎีวงจรไฟฟ้า', '504', 2, NULL, 'Classroom', NULL, '2025-12-04 04:22:17', NULL),
(10, 12, 'Co-Working Space 1', 'โค-เวิร์คกิ้งสเปซ 1', '1', 1, NULL, NULL, NULL, '2026-03-09 08:57:56', 'uploads/images/1773046676_1c6e1d6e-9c4c-4829-ac3f-b2423c9d7cab.jpg'),
(11, 12, 'Editorial Office of JERMUTT', 'สำนักงานวารสารวิศวกรรมศาสตร์ราชมงคลธัญบุรี', 'EN-01 105', 1, NULL, NULL, NULL, '2026-03-09 08:59:48', 'uploads/images/1773046788_6ece8c9f-5b53-4c7a-a4b0-55f2699e09f7.jpg'),
(12, 12, 'Student Union Office', 'สำนักงานสโมสรนักศึกษา', 'EN-01 104', 1, NULL, NULL, NULL, '2026-03-09 09:00:51', 'uploads/images/1773046851_7ff8ed69-5767-4443-8399-0f0f80637959.jpg'),
(13, 12, 'Co-Working Space 2', 'โค-เวิร์คกิ้งสเปซ 2', '2', 1, NULL, NULL, NULL, '2026-03-09 09:01:20', 'uploads/images/1773046880_145c4b56-f752-42da-900d-dcde70dfe5ae.jpg'),
(14, 18, 'test', 'ทดสอบ', '1111', 1, NULL, NULL, 'uploads/layouts/1773312062_1f6aa070-5c8c-4f6d-a500-6927ef028bf7 (1).jpg', '2026-03-12 10:41:02', 'uploads/images/1773312062_Screenshot 2026-03-09 155103.png');

-- --------------------------------------------------------

--
-- Table structure for table `room_images`
--

CREATE TABLE `room_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(8, '117193423119640302623', 'ingingl484@gmail.com', 'Phopiang Punpook', '2026-03-06 09:07:55', 'https://lh3.googleusercontent.com/a/ACg8ocIRdLdkeBDsdXF84JQrM9r7mC6K2WHrJJpyrqHIgc6DaVTNCDJmIw=s96-c', 'staff', 'active'),
(9, 'guest_id', 'guest', 'Guest User', '2026-03-11 23:10:06', NULL, 'student', 'active'),
(14, 'pending_175e8b09198773db', 'fah.thongkham@gmail.com', 'วนัชพร ทองคำ', '2026-03-12 08:18:43', 'https://lh3.googleusercontent.com/a/ACg8ocILohdE1KVc8yvGUQ6WFqIItismgbw_xH0XR1AXG391sM3rRgW8cA=s96-c', 'staff', 'active'),
(15, '100652209792208710272', 'jiraki001@gmail.com', 'jiraki [jiradech]', '2026-03-12 08:29:08', 'https://lh3.googleusercontent.com/a/ACg8ocKp089o4dZxZ3OIZVKNvpd7i6c_UunGZNUdD0rMp5qLdHYxfos', 'student', 'active'),
(16, 'pending_83338224b02bfc5b', 'asd@gmail.com', 'asd', '2026-03-12 10:52:23', NULL, 'staff', 'active');

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
-- Indexes for table `room_images`
--
ALTER TABLE `room_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_id` (`room_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `room_images`
--
ALTER TABLE `room_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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

--
-- Constraints for table `room_images`
--
ALTER TABLE `room_images`
  ADD CONSTRAINT `room_images_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
