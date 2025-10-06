-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 05:56 PM
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
-- Database: `chatroom_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `room_id` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `username`, `message`, `timestamp`, `room_id`) VALUES
(1, 1, 'RandomUser 123', 'Hello, this is a test message!', '2025-09-23 23:43:43', 1),
(2, 2, 'HappyStar947', 'hi chat', '2025-09-24 00:39:43', 1),
(3, 3, 'SwiftEagle117', 'heyyy', '2025-09-24 00:44:46', 1),
(4, 9, 'RedEagle910', 'RRRRRRRRrrrrr!', '2025-10-06 15:39:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `is_default_password` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_active` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `username`, `is_default_password`, `created_at`, `last_active`) VALUES
(1, 'test@gmail.com', 'example12345', 'RandomUser123', 1, '2025-09-23 23:43:43', '2025-10-06 15:28:02'),
(2, 'dennis.kabaso03@gmail.com', '$2y$10$Ie9VoPVqqwHmCXkKGSfiRe5NlXqShy650NA.GN1ucas8VOtnDRHkm', 'HappyStar947', 1, '2025-09-24 00:39:27', '2025-10-06 15:28:02'),
(3, 'phiriedith1010@gmail.com', '1234567', 'SwiftEagle117', 1, '2025-09-24 00:44:11', '2025-10-06 15:28:02'),
(4, 'appleeater1@gmail.com', '$2y$10$7SySgjE4DXMSuv67bocJAeKa3R3goUke4lA2GIrdufmG72uxuv2pm', 'SmartWolf804', 1, '2025-09-24 14:04:53', '2025-10-06 15:28:02'),
(5, 'pluto123@gmail.com', '$2y$10$6Aoz8XUJoxJ5j6w.0f0bFewSNU2c8iswSUgdCB4LmWehAPZA4jAXi', 'QuickTiger482', 1, '2025-10-01 10:54:41', '2025-10-06 15:28:02'),
(6, 'chimchim@gmail.com', '', 'BraveEagle539', 1, '2025-10-01 10:55:46', '2025-10-06 15:28:27'),
(7, 'sabrinazcat123@gmail.com', '$2y$10$C8j7lT1ujRdaYcnfGmnhceACL1Kuuw.bi38qnAZS53tgVrm1D4yZu', 'BraveTiger735', 1, '2025-10-03 01:01:42', '2025-10-06 15:28:02'),
(8, 'happybird.123@gmail.com', '$2y$10$BvjdyHEzFUVeF7Cm0uq0l.sLQAcbPys/UbaCRoBp.EDr/BwISyhke', 'RedEagle528', 1, '2025-10-06 15:31:33', '2025-10-06 15:36:47'),
(9, 'chomilover@gmail.com', '$2y$10$ZaMEHFi.pfKXt.dQAxg8FOsPMpwKGzLKXVXl5YDctk.cwfLaM0mRS', 'RedEagle910', 1, '2025-10-06 15:39:19', '2025-10-06 15:47:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_room_id` (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
