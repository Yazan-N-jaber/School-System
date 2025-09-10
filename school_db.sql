-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2025 at 05:08 AM
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
-- Database: `school_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `class_fees`
--

CREATE TABLE `class_fees` (
  `id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `fee` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `class_fees`
--

INSERT INTO `class_fees` (`id`, `class_name`, `fee`) VALUES
(1, 'روضة أولى', 400),
(2, 'روضة ثانية', 450),
(3, 'صف اول', 600),
(4, 'صف ثاني', 700),
(5, 'صف ثالث', 800),
(6, 'صف رابع', 950),
(7, 'صف خامس', 1050),
(8, 'صف سادس', 1100),
(9, 'صف سابع', 1200),
(10, 'صف ثامن', 1300),
(11, 'صف تاسع', 1500),
(12, 'صف عاشر', 1700),
(13, 'اول ثانوي', 1950),
(14, 'ثاني ثانوي', 2100);

-- --------------------------------------------------------

--
-- Table structure for table `parents`
--

CREATE TABLE `parents` (
  `id` int(11) NOT NULL,
  `father_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `another_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_name` varchar(255) DEFAULT NULL,
  `max_students` int(11) DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `class_id`, `section_name`, `max_students`, `status`) VALUES
(3, 14, 'A', 20, 'open'),
(32, 3, 'غير مثبت صف اول', 100000, 'closed'),
(33, 4, 'غير مثبت صف ثاني', 100000, 'closed'),
(34, 5, 'غير مثبت صف ثالث', 100000, 'closed'),
(35, 6, 'غير مثبت صف رابع', 100000, 'closed'),
(36, 7, 'غير مثبت صف خامس', 100000, 'closed'),
(37, 8, 'غير مثبت صف سادس', 100000, 'closed'),
(38, 9, 'غير مثبت صف سابع', 100000, 'closed'),
(39, 10, 'غير مثبت صف ثامن', 100000, 'closed'),
(40, 11, 'غير مثبت صف تاسع', 100000, 'closed'),
(41, 12, 'غير مثبت صف عاشر', 100000, 'closed'),
(42, 13, 'غير مثبت اول ثانوي', 100000, 'closed'),
(43, 14, 'غير مثبت ثاني ثانوي', 100000, 'closed'),
(44, 1, 'غير مثبت روضة أولى', 100000, 'closed'),
(45, 2, 'غير مثبت روضة ثانية', 100000, 'closed'),
(46, 14, 'B', 22, 'open');

-- --------------------------------------------------------

--
-- Table structure for table `spending`
--

CREATE TABLE `spending` (
  `id` int(11) NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `spending_cause` varchar(255) DEFAULT NULL,
  `for_who` varchar(255) DEFAULT NULL,
  `spending_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `father_id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `address` varchar(255) NOT NULL,
  `class` varchar(50) NOT NULL,
  `fees` decimal(10,2) NOT NULL,
  `bus_service` tinyint(1) NOT NULL DEFAULT 0,
  `bus_fees` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `discount` int(11) NOT NULL,
  `bus_type` varchar(50) DEFAULT NULL,
  `previous_school` varchar(255) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `admission_date` date NOT NULL DEFAULT curdate(),
  `bus_number` varchar(20) DEFAULT '',
  `bus_route` varchar(50) DEFAULT '',
  `national_id` varchar(20) DEFAULT NULL,
  `religion` varchar(250) DEFAULT NULL,
  `exitway` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transferred_students`
--

CREATE TABLE `transferred_students` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `class_name` varchar(100) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `old_school` varchar(255) DEFAULT NULL,
  `total_fees` int(11) DEFAULT NULL,
  `father_id` int(11) DEFAULT NULL,
  `new_school` varchar(255) NOT NULL,
  `transfer_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `class_fees`
--
ALTER TABLE `class_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `spending`
--
ALTER TABLE `spending`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `father_id` (`father_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `transferred_students`
--
ALTER TABLE `transferred_students`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class_fees`
--
ALTER TABLE `class_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `parents`
--
ALTER TABLE `parents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `spending`
--
ALTER TABLE `spending`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `transferred_students`
--
ALTER TABLE `transferred_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class_fees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`father_id`) REFERENCES `parents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
