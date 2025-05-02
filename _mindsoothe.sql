-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 03:33 AM
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
-- Database: `_mindsoothe`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$SgxXW/7k6Bhw6kz.Om164OFV8.laQjgUc.SKGiiSJ.f6QaTTtlN3y', '2025-01-19 08:16:54');

-- --------------------------------------------------------

--
-- Table structure for table `call_slips`
--

CREATE TABLE `call_slips` (
  `id` int(11) NOT NULL,
  `user_id` int(7) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `unit` varchar(50) NOT NULL,
  `allow_student` tinyint(1) NOT NULL DEFAULT 0,
  `reschedule_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `call_slip_reasons`
--

CREATE TABLE `call_slip_reasons` (
  `id` int(11) NOT NULL,
  `call_slip_id` int(11) NOT NULL,
  `reason` varchar(100) NOT NULL,
  `others_specify` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gracefulthread`
--

CREATE TABLE `gracefulthread` (
  `id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `content` text NOT NULL,
  `likes` int(10) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `mhp_id` int(11) DEFAULT NULL,
  `sender_type` enum('student','MHP') NOT NULL,
  `receiver_type` enum('student','MHP') NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','read') DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `student_id`, `mhp_id`, `sender_type`, `receiver_type`, `message`, `timestamp`, `status`) VALUES
(292, 42, 51, 'student', 'MHP', 'test bon bading', '2025-04-29 01:30:52', 'sent'),
(293, 42, 51, 'MHP', 'student', 'reply guidance chat', '2025-04-29 01:31:33', 'sent');

-- --------------------------------------------------------

--
-- Table structure for table `mhp`
--

CREATE TABLE `mhp` (
  `id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `department` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'images/blueuser.svg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mhp`
--

INSERT INTO `mhp` (`id`, `fname`, `lname`, `email`, `department`, `password`, `profile_image`, `created_at`) VALUES
(51, 'Bonifer', 'Decena', '2101301@usl.edu.ph', 'SACE', '25d55ad283aa400af464c76d713c07ad', 'images/blueuser.svg', '2025-04-28 12:20:31');

-- --------------------------------------------------------

--
-- Table structure for table `phq9_responses`
--

CREATE TABLE `phq9_responses` (
  `id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `question_1` varchar(50) NOT NULL,
  `question_2` varchar(50) NOT NULL,
  `question_3` varchar(50) NOT NULL,
  `question_4` varchar(50) NOT NULL,
  `question_5` varchar(50) NOT NULL,
  `question_6` varchar(50) NOT NULL,
  `question_7` varchar(50) NOT NULL,
  `question_8` varchar(50) NOT NULL,
  `question_9` varchar(50) NOT NULL,
  `response_score` int(10) NOT NULL,
  `severity` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `response_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `phq9_responses`
--

INSERT INTO `phq9_responses` (`id`, `user_id`, `question_1`, `question_2`, `question_3`, `question_4`, `question_5`, `question_6`, `question_7`, `question_8`, `question_9`, `response_score`, `severity`, `created_at`, `response_date`) VALUES
(0, 4, 'Several days', 'More than half the days', 'Nearly every day', 'More than half the days', 'Nearly every day', 'More than half the days', 'Nearly every day', 'Nearly every day', 'More than half the days', 21, 'Severe', '2025-01-22 14:13:25', '2025-01-22 22:13:25'),
(0, 16, 'More than half the days', 'More than half the days', 'Nearly every day', 'More than half the days', 'Several days', 'More than half the days', 'Several days', 'Nearly every day', 'More than half the days', 18, 'Moderately severe', '2025-01-23 03:55:14', '2025-01-23 11:55:14'),
(0, 17, 'Nearly every day', 'More than half the days', 'Nearly every day', 'Several days', 'Not at all', 'More than half the days', 'Nearly every day', 'Several days', 'Not at all', 15, 'Moderately severe', '2025-01-23 12:10:13', '2025-01-23 20:10:13'),
(0, 19, 'Nearly every day', 'Nearly every day', 'More than half the days', 'Several days', 'More than half the days', 'Nearly every day', 'More than half the days', 'Several days', 'More than half the days', 19, 'Moderately severe', '2025-01-27 12:50:21', '2025-01-27 20:50:21'),
(0, 18, 'Not at all', 'Several days', 'More than half the days', 'Nearly every day', 'More than half the days', 'Several days', 'Not at all', 'More than half the days', 'Nearly every day', 14, 'Moderate', '2025-01-28 13:11:32', '2025-01-28 21:11:32'),
(0, 22, 'Nearly every day', 'Several days', 'More than half the days', 'More than half the days', 'Several days', 'Several days', 'Nearly every day', 'More than half the days', 'More than half the days', 17, 'Moderately severe', '2025-02-19 08:57:35', '2025-02-19 16:57:35'),
(0, 26, 'More than half the days', 'Several days', 'Not at all', 'Nearly every day', 'More than half the days', 'Several days', 'Not at all', 'Nearly every day', 'More than half the days', 14, 'Moderate', '2025-02-20 13:54:54', '2025-02-20 21:54:54'),
(0, 23, 'More than half the days', 'Several days', 'More than half the days', 'Several days', 'More than half the days', 'Several days', 'Not at all', 'Nearly every day', 'More than half the days', 14, 'Moderate', '2025-02-21 02:26:37', '2025-02-21 10:26:37'),
(0, 28, 'More than half the days', 'Nearly every day', 'More than half the days', 'Nearly every day', 'More than half the days', 'More than half the days', 'More than half the days', 'More than half the days', 'Not at all', 18, 'Moderately severe', '2025-02-21 05:13:40', '2025-02-21 13:13:40'),
(0, 32, 'Not at all', 'More than half the days', 'Nearly every day', 'More than half the days', 'Several days', 'Nearly every day', 'More than half the days', 'Several days', 'Nearly every day', 17, 'Moderately severe', '2025-02-21 17:58:57', '2025-02-22 01:58:57'),
(0, 31, 'More than half the days', 'Nearly every day', 'More than half the days', 'Several days', 'Nearly every day', 'More than half the days', 'Nearly every day', 'More than half the days', 'Nearly every day', 21, 'Severe', '2025-02-22 03:34:16', '2025-02-22 11:34:16'),
(0, 40, 'Several days', 'Several days', 'More than half the days', 'Nearly every day', 'Nearly every day', 'Nearly every day', 'Nearly every day', 'Nearly every day', 'Nearly every day', 22, 'Severe', '2025-04-11 01:05:42', '2025-04-11 09:05:42'),
(0, 41, 'Nearly every day', 'Not at all', 'Several days', 'More than half the days', 'More than half the days', 'Not at all', 'Several days', 'Nearly every day', 'Several days', 13, 'Moderate', '2025-04-28 12:23:03', '2025-04-28 20:23:03'),
(0, 42, 'Several days', 'More than half the days', 'Not at all', 'More than half the days', 'Nearly every day', 'Several days', 'More than half the days', 'Not at all', 'More than half the days', 13, 'Moderate', '2025-04-29 01:30:44', '2025-04-29 09:30:44');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `post_id` int(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`id`, `user_id`, `post_id`, `created_at`) VALUES
(1, 9, 1, '2025-01-19 02:09:47'),
(3, 1, 1, '2025-01-19 02:10:25'),
(4, 8, 1, '2025-01-20 03:12:12'),
(5, 17, 13, '2025-02-21 02:24:47'),
(6, 17, 12, '2025-02-21 02:24:49'),
(7, 28, 15, '2025-02-21 05:12:13'),
(8, 31, 16, '2025-02-22 00:34:50'),
(9, 31, 19, '2025-02-22 00:35:30'),
(10, 31, 24, '2025-02-22 03:32:49'),
(11, 31, 21, '2025-02-22 03:32:57');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) NOT NULL,
  `Student_id` int(7) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `profile_image` varchar(255) DEFAULT 'images/blueuser.svg',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `otp` varchar(6) DEFAULT NULL,
  `Course` varchar(250) NOT NULL,
  `Year` int(10) NOT NULL,
  `Department` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `Student_id`, `firstName`, `lastName`, `email`, `password`, `profile_image`, `status`, `otp`, `Course`, `Year`, `Department`) VALUES
(42, 2102446, 'JANINE KARLA', 'PABLO', '2102446@usl.edu.ph', '5e8667a439c68f5145dd2fcbecf02209', 'user_images/2102446.jpg', 0, '639709', 'BSACT', 4, 'SABH');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `call_slips`
--
ALTER TABLE `call_slips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `call_slip_reasons`
--
ALTER TABLE `call_slip_reasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `call_slip_id` (`call_slip_id`);

--
-- Indexes for table `gracefulthread`
--
ALTER TABLE `gracefulthread`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `mhp_id` (`mhp_id`);

--
-- Indexes for table `mhp`
--
ALTER TABLE `mhp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `call_slips`
--
ALTER TABLE `call_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `call_slip_reasons`
--
ALTER TABLE `call_slip_reasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gracefulthread`
--
ALTER TABLE `gracefulthread`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=294;

--
-- AUTO_INCREMENT for table `mhp`
--
ALTER TABLE `mhp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `call_slips`
--
ALTER TABLE `call_slips`
  ADD CONSTRAINT `call_slips_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `call_slip_reasons`
--
ALTER TABLE `call_slip_reasons`
  ADD CONSTRAINT `call_slip_reasons_ibfk_1` FOREIGN KEY (`call_slip_id`) REFERENCES `call_slips` (`id`);

--
-- Constraints for table `gracefulthread`
--
ALTER TABLE `gracefulthread`
  ADD CONSTRAINT `gracefulthread_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`mhp_id`) REFERENCES `mhp` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD CONSTRAINT `time_slots_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
