-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2025 at 12:26 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `it_club`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Aditya Patil', 'adityaap471@gmail.com', 'dfsdf', '-', '2025-08-14 14:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_paid` tinyint(1) DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00,
  `payment_qr` varchar(255) DEFAULT NULL,
  `feedback_emails_sent` tinyint(1) DEFAULT 0,
  `feedback_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `event_date`, `location`, `description`, `image`, `created_at`, `is_paid`, `price`, `payment_qr`, `feedback_emails_sent`, `feedback_sent`) VALUES
(20, 'ABCD', '2025-08-10 17:13:00', 'Computer Lab 1', 'ABCD<M', NULL, '2025-08-11 07:40:45', 0, '0.00', '', 1, 0),
(21, 'sdad', '2025-08-13 23:03:00', 'vSADFSVXa', 'asdasdasd', NULL, '2025-08-14 14:33:49', 0, '0.00', '', 0, 1),
(22, 'dasdasdasd', '2025-08-27 12:04:00', 'DavzDSGX', 'asdasdasda', NULL, '2025-08-14 14:35:08', 1, '234.00', 'uploads/qr_8bdffaaca1587b59.jpg', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `event_feedback`
--

CREATE TABLE `event_feedback` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `participant_email` varchar(100) NOT NULL,
  `participant_name` varchar(100) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `suggestions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_queries`
--

CREATE TABLE `event_queries` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `query` text NOT NULL,
  `status` enum('pending','answered') DEFAULT 'pending',
  `response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_ratings`
--

CREATE TABLE `event_ratings` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `is_student` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_screenshot` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `name`, `email`, `phone`, `department`, `is_student`, `created_at`, `payment_screenshot`) VALUES
(4, 21, 'Aditya Patil', 'adityaap471@gmail.com', '9322662493', 'BCA', 1, '2025-08-15 14:20:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_proofs`
--

CREATE TABLE `payment_proofs` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `payment_screenshot` varchar(255) NOT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'contact_email', 'ap3441218@gmail.com', '2025-08-14 13:59:42'),
(2, 'contact_phone', '+91 9322662493', '2025-08-14 14:00:25'),
(3, 'contact_address', 'Sangmeshwar College, Solapur', '2025-08-14 14:00:10'),
(4, 'site_description', 'Your hub for tech innovation and events at out college!\r\n', '2025-08-14 14:12:49'),
(5, 'footer_text', '© 2025 IT Club, Computer Science Dept, New IT College. All rights reserved.', '2025-08-14 13:56:24'),
(6, 'instagram_url', 'https://www.instagram.com/sangameshwar_sanmcs_dept?igsh=MW55bWRvcXhxc3YxNw==', '2025-08-14 14:14:24'),
(46, 'site_title', 'Welcome to IT Club ', '2025-08-14 14:11:13'),
(48, 'college_name', 'SANGAMESHWAR COLLEGE, SOLAPUR AUTONUMOUS', '2025-08-14 14:11:13'),
(49, 'department_name', 'Computer Science Department', '2025-08-14 14:04:52'),
(51, 'facebook_url', '', '2025-08-14 14:04:52'),
(52, 'twitter_url', '', '2025-08-14 14:04:52'),
(53, 'linkedin_url', '', '2025-08-14 14:04:52'),
(55, 'about_text', 'Fostering tech innovation and collaboration among students.\r\n', '2025-08-14 14:14:24'),
(56, 'default_event_location', 'College Auditorium', '2025-08-14 14:04:52'),
(57, 'event_registration_email', 'ap3441218@gmail.com', '2025-08-14 14:04:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('faculty','member') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$ZKW25qSGZRi4uMd2/3oLpeRmalVO9DPD9GqtleHyY0tuao201NfW2', 'admin123@gmail.com', 'faculty', '2025-07-25 11:18:59'),
(2, 'President', '$2y$10$01LRgpCmsD93v34qVbzWNOMsdN4s3ZTf2j4KfiRHLYxy9y7CENAgC', 'adityaap471@gmail.com', 'member', '2025-07-25 12:29:24'),
(3, 'Vice_P', '$2y$10$p0R46AceclNH8JDbU6YIk.yB02Cvrtwx.uEIsKp4dxor77WlgUDEO', 'ap3441218@gmail.com', 'member', '2025-08-05 12:53:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_feedback`
--
ALTER TABLE `event_feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_feedback` (`event_id`,`participant_email`);

--
-- Indexes for table `event_queries`
--
ALTER TABLE `event_queries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_ratings`
--
ALTER TABLE `event_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_event` (`event_id`,`user_email`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

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
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `event_feedback`
--
ALTER TABLE `event_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_queries`
--
ALTER TABLE `event_queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `event_ratings`
--
ALTER TABLE `event_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_feedback`
--
ALTER TABLE `event_feedback`
  ADD CONSTRAINT `event_feedback_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_queries`
--
ALTER TABLE `event_queries`
  ADD CONSTRAINT `event_queries_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_ratings`
--
ALTER TABLE `event_ratings`
  ADD CONSTRAINT `event_ratings_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD CONSTRAINT `payment_proofs_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
