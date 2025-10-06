-- Sample/demo data only (not real accounts). Every users.password value is a
-- bcrypt ($2y$10$) hash of the password demo1234. is_default_password is 0, so
-- sign-in must supply that password; a blank password is accepted only when the flag is 1.

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
(1, 'random.user@example.com', '$2y$10$2L2ilXJBlAJoZXO0NCOpuO1IFsuOu/7t0FYGO.xL1298JGwIXXF6K', 'RandomUser123', 0, '2025-09-23 23:43:43', '2025-10-06 15:28:02'),
(2, 'happy.star@example.com', '$2y$10$1JsfiCy9NKk3aq1Zoulp7ulGJ2lCs6CgNUsS5LDT6kbzdC7c9AlOW', 'HappyStar947', 0, '2025-09-24 00:39:27', '2025-10-06 15:28:02'),
(3, 'swift.eagle@example.com', '$2y$10$/u/3t11dAaMX8nh71FqCuORE7nhS1dlpuoTMdulDXXEuEIr/YViYS', 'SwiftEagle117', 0, '2025-09-24 00:44:11', '2025-10-06 15:28:02'),
(4, 'smart.wolf@example.com', '$2y$10$H29oA04vkSy69G7xqWUKGOayojqXnu6lac6PTJPzxUZ9IPl3SDRUu', 'SmartWolf804', 0, '2025-09-24 14:04:53', '2025-10-06 15:28:02'),
(5, 'quick.tiger@example.com', '$2y$10$tWZMdyxrAuchB9nPBO9fP.HEC2wyGYWsJdSpiLXP7FNX8luz6Mhvy', 'QuickTiger482', 0, '2025-10-01 10:54:41', '2025-10-06 15:28:02'),
(6, 'brave.eagle@example.com', '$2y$10$ODUOc6x2sqL0IuTP1c1ske.ruBkTweYj6DjflM.eJmJ/3U0ttBybS', 'BraveEagle539', 0, '2025-10-01 10:55:46', '2025-10-06 15:28:27'),
(7, 'brave.tiger@example.com', '$2y$10$rV2oG5d/t/UgKWLLOMLTXuFcvRHU51K.ua9t1EhHb/HlRFMK/XHkO', 'BraveTiger735', 0, '2025-10-03 01:01:42', '2025-10-06 15:28:02'),
(8, 'red.eagle@example.com', '$2y$10$32aLrm6w2Y0uR0sBxBOUWeNi0WoDUZq9.cp7iwo1xqcRW50LxE7qG', 'RedEagle528', 0, '2025-10-06 15:31:33', '2025-10-06 15:36:47'),
(9, 'red.eagle910@example.com', '$2y$10$F8vDSo26/SCRUjo5viPRgeik92qoH1mHyJm7nxiX/6sQ8ED5MuO.K', 'RedEagle910', 0, '2025-10-06 15:39:19', '2025-10-06 15:47:09');

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
