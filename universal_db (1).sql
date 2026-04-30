-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 05:54 AM
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
-- Database: `universal_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `credit_hours` int(11) DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `semester_no` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `program_id`, `name`, `code`, `credit_hours`, `created_at`, `semester_no`) VALUES
(1, 8, 'Business analytics', 'BS_01', 3, '2026-03-03 05:19:47', 1),
(6, 8, 'Businesss principles', 'BS_10', 3, '2026-03-03 05:43:37', 8),
(7, 8, 'Programming Fundamentals', 'CS-101', 4, '2026-03-08 09:19:23', 1),
(8, 8, 'Calculus & Analytical Geometry', 'MT-101', 3, '2026-03-08 09:19:23', 1),
(9, 8, 'Introduction to ICT', 'CS-100', 3, '2026-03-08 09:19:23', 1),
(10, 8, 'English Composition', 'ENG-101', 3, '2026-03-08 09:19:23', 1),
(32, 24, 'Programming Fundamentals', 'CS-102', 4, '2026-04-25 14:10:23', 1),
(33, 24, 'Digital Logic Design', 'CS-201', 4, '2026-04-25 14:10:23', 2),
(34, 24, 'Object Oriented Programming', 'CS-202', 4, '2026-04-25 14:10:23', 2),
(35, 24, 'Data Structures & Algorithms', 'CS-301', 4, '2026-04-25 14:10:23', 3),
(36, 24, 'Database Systems', 'CS-401', 4, '2026-04-25 14:10:23', 4),
(37, 19, 'Business English-I', 'ENG-111', 3, '2026-04-25 14:10:23', 1),
(38, 19, 'Computer Applications in Business', 'MIS-111', 3, '2026-04-25 14:10:23', 1),
(39, 19, 'Financial Accounting-I', 'ACC-111', 3, '2026-04-25 14:10:23', 2),
(40, 19, 'Business Economics', 'ECO-111', 3, '2026-04-25 14:10:23', 3),
(41, 19, 'Principles of Marketing', 'MKT-111', 3, '2026-04-25 14:10:23', 4),
(42, 19, 'Financial Management', 'FIN-111', 3, '2026-04-25 14:10:23', 5),
(43, 16, 'Introduction to Psychology', 'PSY-101', 3, '2026-04-25 14:10:23', 1),
(44, 16, 'History & Systems in Psychology', 'PSY-102', 3, '2026-04-25 14:10:23', 1),
(45, 16, 'Applied Psychology', 'PSY-201', 3, '2026-04-25 14:10:23', 2),
(46, 16, 'Introduction to Social Work', 'SW-201', 3, '2026-04-25 14:10:23', 2),
(47, 25, 'Information Technology Infrastructure', 'IT-101', 3, '2026-04-25 14:10:23', 1),
(48, 25, 'Discrete Structures', 'IT-102', 3, '2026-04-25 14:10:23', 1),
(49, 25, 'Web Systems & Technologies', 'IT-301', 4, '2026-04-25 14:10:23', 3),
(51, 19, 'Quantitative Reasoning-I', 'QR-101', 3, '2026-04-25 14:29:57', 1),
(52, 19, 'Islamic Studies', 'ISL-101', 2, '2026-04-25 14:29:57', 1),
(53, 19, 'Pakistan Studies', 'PS-101', 2, '2026-04-25 14:29:57', 1),
(54, 19, 'Human Psychology', 'PSY-111', 3, '2026-04-25 14:29:57', 1),
(55, 19, 'Business English-II', 'ENG-102', 3, '2026-04-25 14:29:57', 2),
(56, 19, 'Quantitative Reasoning-II', 'QR-102', 3, '2026-04-25 14:29:57', 2),
(57, 19, 'Business Philosophy', 'PHI-101', 3, '2026-04-25 14:29:57', 2),
(58, 19, 'General Science', 'GSC-101', 3, '2026-04-25 14:29:57', 2),
(59, 19, 'Principles of Management', 'MGT-111', 3, '2026-04-25 14:29:57', 2),
(60, 19, 'Business Communication', 'ENG-201', 3, '2026-04-25 14:29:57', 3),
(61, 19, 'Environmental Sciences', 'ESC-201', 3, '2026-04-25 14:29:57', 3),
(62, 19, 'Sociology', 'SOC-201', 3, '2026-04-25 14:29:57', 3),
(63, 19, 'Foreign Language', 'LAN-201', 3, '2026-04-25 14:29:57', 3),
(64, 19, 'Financial Accounting-II', 'ACC-211', 3, '2026-04-25 14:29:57', 3),
(65, 19, 'Human Resource Management', 'HRM-111', 3, '2026-04-25 14:29:57', 4),
(66, 19, 'Economic environment of Pakistan', 'ECO-211', 3, '2026-04-25 14:29:57', 4),
(67, 19, 'Corporate and Business Law', 'LAW-111', 3, '2026-04-25 14:29:57', 4),
(68, 19, 'Management Information System', 'MIS-211', 3, '2026-04-25 14:29:57', 4),
(69, 24, 'Calculus & Analytical Geometry', 'MATH-101', 3, '2026-04-25 14:29:57', 1),
(70, 24, 'English Composition & Comprehension', 'ENG-CS101', 3, '2026-04-25 14:29:57', 1),
(71, 24, 'Applied Physics', 'PHY-101', 3, '2026-04-25 14:29:57', 1),
(72, 24, 'Communication & Presentation Skills', 'ENG-CS102', 3, '2026-04-25 14:29:57', 2),
(73, 24, 'Probability & Statistics', 'STAT-101', 3, '2026-04-25 14:29:57', 2),
(74, 24, 'University Elective-I', 'UE-101', 3, '2026-04-25 14:29:57', 2),
(75, 24, 'Data Structures & Algorithms', 'CS-302', 4, '2026-04-25 14:29:57', 3),
(76, 24, 'Discrete Structures', 'CS-303', 3, '2026-04-25 14:29:57', 3),
(77, 24, 'Professional Practices', 'CS-304', 3, '2026-04-25 14:29:57', 3),
(78, 24, 'CS Supporting-I', 'CSS-301', 3, '2026-04-25 14:29:57', 3),
(79, 24, 'Theory of Automata', 'CS-402', 3, '2026-04-25 14:29:57', 4),
(80, 24, 'Database Systems', 'CS-403', 4, '2026-04-25 14:29:57', 4),
(81, 24, 'Linear Algebra', 'MATH-201', 3, '2026-04-25 14:29:57', 4),
(82, 24, 'University Elective-II', 'UE-201', 3, '2026-04-25 14:29:57', 4),
(83, 24, 'Compiler Construction', 'CS-501', 3, '2026-04-25 14:29:57', 5),
(84, 24, 'CS Supporting-II', 'CSS-501', 3, '2026-04-25 14:29:57', 5),
(85, 24, 'Operating Systems', 'CS-502', 4, '2026-04-25 14:29:57', 5),
(86, 24, 'Software Engineering', 'CS-503', 3, '2026-04-25 14:29:57', 5),
(87, 24, 'CS Supporting-III', 'CSS-502', 3, '2026-04-25 14:29:57', 5),
(88, 24, 'Artificial Intelligence', 'CS-601', 4, '2026-04-25 14:29:57', 6),
(89, 24, 'Computer Networks', 'CS-602', 4, '2026-04-25 14:29:57', 6),
(90, 24, 'CS Elective-I', 'CSE-601', 3, '2026-04-25 14:29:57', 6),
(91, 24, 'CS Elective-II', 'CSE-602', 3, '2026-04-25 14:29:57', 6),
(92, 24, 'Technical & Business Writing', 'ENG-201-CS', 3, '2026-04-25 14:29:57', 6);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `created_at`) VALUES
(1, 'department of business administration', '02', '2026-03-03 04:56:45'),
(2, 'Department of computer science', '05', '2026-03-03 04:57:17'),
(3, 'Department of Applied psychology', '01', '2026-03-03 04:58:00'),
(4, 'Department of law', '08', '2026-03-03 04:59:12'),
(5, 'Department of chemistry', '03', '2026-04-25 12:37:10'),
(6, 'department of Commerce', '04', '2026-04-25 12:38:05'),
(7, 'department of Economics', '06', '2026-04-25 12:39:13'),
(8, 'department of English', '07', '2026-04-25 12:39:42'),
(9, 'department of Mathematics', '09', '2026-04-25 12:40:36'),
(10, 'department of Physics', '10', '2026-04-25 12:40:50');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `fee_category` varchar(50) DEFAULT 'Regular',
  `admission_fee` decimal(10,2) DEFAULT 0.00,
  `base_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lab_charges` decimal(10,2) DEFAULT 0.00,
  `library_fee` decimal(10,2) DEFAULT 0.00,
  `hostel_fee` decimal(10,2) DEFAULT 0.00,
  `credit_hour_rate` decimal(10,2) DEFAULT 0.00,
  `late_fine_per_day` decimal(10,2) DEFAULT 0.00,
  `exam_fee` decimal(10,2) DEFAULT 0.00,
  `registration_fee` decimal(10,2) DEFAULT 0.00,
  `sports_fund` decimal(10,2) DEFAULT 0.00,
  `library_security` decimal(10,2) DEFAULT 0.00,
  `it_services` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`id`, `semester_id`, `fee_category`, `admission_fee`, `base_fee`, `lab_charges`, `library_fee`, `hostel_fee`, `credit_hour_rate`, `late_fine_per_day`, `exam_fee`, `registration_fee`, `sports_fund`, `library_security`, `it_services`) VALUES
(4, 5, 'Regular', 0.00, 5000.00, 500.00, 500.00, 1000.00, 3.00, 100.00, 500.00, 3000.00, 500.00, 1000.00, 1000.00),
(7, 9, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(8, 16, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(9, 31, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(10, 67, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 2600.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(11, 75, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 2600.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(12, 24, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(13, 28, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(14, 39, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(15, 43, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(16, 51, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(17, 59, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(18, 83, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(19, 91, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(20, 99, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(21, 107, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(22, 111, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(23, 119, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(24, 127, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(25, 10, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(26, 11, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(27, 12, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(28, 13, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(29, 14, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(30, 15, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(31, 17, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(32, 18, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(33, 19, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(34, 20, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(35, 21, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(36, 22, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(37, 23, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(38, 25, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(39, 26, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(40, 27, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(41, 29, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(42, 30, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(43, 32, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(44, 33, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(45, 34, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(46, 35, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(47, 36, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(48, 37, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(49, 38, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(50, 40, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(51, 41, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(52, 42, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(53, 44, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(54, 45, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(55, 46, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(56, 47, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(57, 48, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(58, 49, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(59, 50, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(60, 52, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(61, 53, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(62, 54, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(63, 55, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(64, 56, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(65, 57, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(66, 58, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(67, 60, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(68, 61, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(69, 62, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(70, 63, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(71, 64, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(72, 65, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(73, 66, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(74, 68, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(75, 69, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(76, 70, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(77, 71, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(78, 72, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(79, 73, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(80, 74, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(81, 76, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(82, 77, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(83, 78, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(84, 79, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(85, 80, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(86, 81, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(87, 82, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(88, 84, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(89, 85, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(90, 86, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(91, 87, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(92, 88, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(93, 89, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(94, 90, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(95, 92, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(96, 93, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(97, 94, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(98, 95, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(99, 96, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(100, 97, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(101, 98, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(102, 100, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(103, 101, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(104, 102, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(105, 103, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(106, 104, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(107, 105, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(108, 106, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(109, 108, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(110, 109, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(111, 110, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(112, 112, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(113, 113, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(114, 114, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(115, 115, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(116, 116, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(117, 117, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(118, 118, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(119, 120, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(120, 121, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(121, 122, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(122, 123, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(123, 124, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(124, 125, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(125, 126, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(126, 128, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(127, 129, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(128, 130, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(129, 131, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(130, 132, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(131, 133, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00),
(132, 134, 'Regular', 3450.00, 0.00, 0.00, 1000.00, 0.00, 1800.00, 100.00, 4500.00, 2500.00, 500.00, 0.00, 1500.00);

-- --------------------------------------------------------

--
-- Table structure for table `installments`
--

CREATE TABLE `installments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `installment_no` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `paid_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `installments`
--

INSERT INTO `installments` (`id`, `invoice_id`, `installment_no`, `amount`, `due_date`, `status`, `paid_date`) VALUES
(1, 4, 1, 3500.00, '2026-03-04', 'pending', NULL),
(2, 4, 2, 3500.00, '2026-04-03', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `invoice_type` enum('academic','hostel') DEFAULT 'academic',
  `total_base_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `payable_amount` decimal(10,2) NOT NULL,
  `balance_due` decimal(10,2) NOT NULL,
  `status` varchar(30) DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `user_id`, `semester_id`, `invoice_type`, `total_base_amount`, `discount_amount`, `fine_amount`, `payable_amount`, `balance_due`, `status`, `created_at`, `due_date`) VALUES
(4, 11, 5, 'academic', 12000.00, 5000.00, 0.00, 7000.00, 7000.00, 'unpaid', '2026-03-04 17:41:21', '2026-03-19'),
(5, 11, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-04 17:41:21', '2026-03-19'),
(6, 15, 5, 'academic', 37500.00, 0.00, 0.00, 37500.00, 37500.00, 'draft', '2026-03-08 09:19:23', '2026-03-23'),
(7, 16, 5, 'academic', 37500.00, 0.00, 0.00, 37500.00, 37500.00, 'draft', '2026-03-08 09:19:23', '2026-03-23'),
(8, 17, 5, 'academic', 37500.00, 0.00, 0.00, 37500.00, 37500.00, 'draft', '2026-03-08 09:19:23', '2026-03-23'),
(9, 18, 5, 'academic', 37500.00, 0.00, 0.00, 37500.00, 37500.00, 'draft', '2026-03-08 09:19:23', '2026-03-23'),
(10, 19, 5, 'academic', 37500.00, 0.00, 0.00, 37500.00, 37500.00, 'draft', '2026-03-08 09:19:23', '2026-03-23'),
(11, 20, 5, 'academic', 37500.00, 0.00, 0.00, 37500.00, 37500.00, 'draft', '2026-03-08 09:19:23', '2026-03-23'),
(12, 21, 5, 'academic', 37500.00, 0.00, 0.00, 37500.00, 37500.00, 'draft', '2026-03-08 09:19:23', '2026-03-23'),
(13, 22, 5, 'academic', 37500.00, 0.00, 0.00, 37500.00, 37500.00, 'draft', '2026-03-08 09:19:23', '2026-03-23'),
(16, 15, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-29 17:46:50', '2026-04-13'),
(17, 16, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-29 17:46:50', '2026-04-13'),
(18, 17, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-29 17:46:50', '2026-04-13'),
(19, 18, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-29 17:46:50', '2026-04-13'),
(20, 19, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-29 17:46:50', '2026-04-13'),
(21, 20, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-29 17:46:50', '2026-04-13'),
(22, 21, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-29 17:46:50', '2026-04-13'),
(23, 22, 5, 'hostel', 1000.00, 0.00, 0.00, 1000.00, 1000.00, 'unpaid', '2026-03-29 17:46:50', '2026-04-13'),
(26, 23, 5, 'academic', 12054.00, 10000.00, 0.00, 2054.00, 2054.00, 'unpaid', '2026-04-26 13:21:40', '2026-05-11'),
(27, 24, 5, 'academic', 12054.00, 0.00, 0.00, 12054.00, 12054.00, 'unpaid', '2026-04-26 13:21:40', '2026-05-11'),
(28, 108, 9, 'academic', 10000.00, 0.00, 0.00, 10000.00, 10000.00, 'unpaid', '2026-04-29 12:21:56', '2026-05-14');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('JazzCash','EasyPaisa','Card','Cash') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `paid_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `proof_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `department_id` int(11) DEFAULT NULL,
  `total_semesters` int(11) DEFAULT 8
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `name`, `code`, `created_at`, `department_id`, `total_semesters`) VALUES
(8, 'Bachelor in Business information system', 'BBIS', '2026-03-03 05:00:16', 1, 8),
(16, 'BS Applied Psychology', 'BSAP', '2026-04-25 12:45:06', 3, 8),
(17, 'MS in Clinical Psychology', 'MSCP', '2026-04-25 12:50:22', 3, 4),
(18, 'Advanced Diploma in Clinical Psychology', 'ACDP', '2026-04-25 12:56:25', 3, 4),
(19, 'Bachelor in Business Administration', 'BBA', '2026-04-25 13:01:25', 1, 8),
(20, 'Executive Master of Business Administration.', 'EMBA', '2026-04-25 13:02:36', 1, 4),
(21, 'Bachelor of Science in Chemistry', 'BS CHEMISTRY', '2026-04-25 13:07:11', 5, 8),
(22, 'Bachelor of Studies in commerce', 'BS COMMERCE', '2026-04-25 13:32:13', 6, 8),
(23, 'Bachelor of studies in accounting & finance', 'BSAF', '2026-04-25 13:34:08', 6, 8),
(24, 'bachelor in computer science', 'BSCS', '2026-04-25 13:34:44', 2, 8),
(25, 'Bachelor in information technology', 'BSIT', '2026-04-25 13:35:20', 2, 8),
(26, 'bachelor of studies in economics', 'BS ECONOMICS', '2026-04-25 13:36:20', 7, 8),
(27, 'bachelor of studies in enlish', 'BS ENGLISH', '2026-04-25 13:36:56', 8, 8),
(28, 'Bachelor of Laws', 'LLB', '2026-04-25 13:38:10', 4, 8),
(29, 'Master of Laws', 'LLM', '2026-04-25 13:38:36', 4, 4),
(30, 'bachelor of studies in mathematics', 'BS MATH', '2026-04-25 13:39:43', 9, 8),
(31, 'bachelor of studies in physics', 'BS PHYSICS', '2026-04-25 13:40:27', 10, 8),
(32, 'bachelor of studies in electronics', 'BS ELECTRONICS', '2026-04-25 13:40:57', 10, 8);

-- --------------------------------------------------------

--
-- Table structure for table `role_access`
--

CREATE TABLE `role_access` (
  `role_key` varchar(50) NOT NULL,
  `page_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_access`
--

INSERT INTO `role_access` (`role_key`, `page_id`) VALUES
('admin', 1),
('clerk', 1),
('clerk', 31),
('clerk', 32),
('clerk', 35),
('finance', 1),
('finance', 9),
('finance', 10),
('finance', 11),
('finance', 12),
('finance', 13),
('finance', 14),
('finance', 15),
('finance', 16),
('finance', 35),
('hod', 1),
('hod', 17),
('student', 1),
('student', 18),
('super_admin', 1),
('super_admin', 2),
('super_admin', 3),
('super_admin', 4),
('super_admin', 5),
('super_admin', 6),
('super_admin', 7),
('super_admin', 8),
('super_admin', 9),
('super_admin', 10),
('super_admin', 11),
('super_admin', 12),
('super_admin', 13),
('super_admin', 14),
('super_admin', 15),
('super_admin', 16),
('super_admin', 17),
('super_admin', 18),
('super_admin', 20),
('super_admin', 21),
('super_admin', 33),
('super_admin', 34),
('super_admin', 35),
('super_admin', 36);

-- --------------------------------------------------------

--
-- Table structure for table `scholarships`
--

CREATE TABLE `scholarships` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholarships`
--

INSERT INTO `scholarships` (`id`, `name`, `type`, `amount`, `created_at`) VALUES
(1, 'Verification Relief', 'fixed', 10000.00, '2026-03-01 07:20:58'),
(2, 'student special', 'fixed', 5000.00, '2026-03-01 07:31:16'),
(3, 'student special', 'fixed', 5000.00, '2026-03-01 07:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` int(11) NOT NULL,
  `status` enum('active','completed','upcoming') DEFAULT 'upcoming'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `program_id`, `name`, `number`, `status`) VALUES
(5, 8, 'semester', 8, 'upcoming'),
(9, 8, 'Semester 1', 1, 'upcoming'),
(10, 8, 'Semester 2', 2, 'upcoming'),
(11, 8, 'Semester 3', 3, 'upcoming'),
(12, 8, 'Semester 4', 4, 'upcoming'),
(13, 8, 'Semester 5', 5, 'upcoming'),
(14, 8, 'Semester 6', 6, 'upcoming'),
(15, 8, 'Semester 7', 7, 'upcoming'),
(16, 16, 'Semester 1', 1, 'upcoming'),
(17, 16, 'Semester 2', 2, 'upcoming'),
(18, 16, 'Semester 3', 3, 'upcoming'),
(19, 16, 'Semester 4', 4, 'upcoming'),
(20, 16, 'Semester 5', 5, 'upcoming'),
(21, 16, 'Semester 6', 6, 'upcoming'),
(22, 16, 'Semester 7', 7, 'upcoming'),
(23, 16, 'Semester 8', 8, 'upcoming'),
(24, 17, 'Semester 1', 1, 'upcoming'),
(25, 17, 'Semester 2', 2, 'upcoming'),
(26, 17, 'Semester 3', 3, 'upcoming'),
(27, 17, 'Semester 4', 4, 'upcoming'),
(28, 18, 'Semester 1', 1, 'upcoming'),
(29, 18, 'Semester 2', 2, 'upcoming'),
(30, 18, 'Semester 3', 3, 'upcoming'),
(31, 19, 'Semester 1', 1, 'upcoming'),
(32, 19, 'Semester 2', 2, 'upcoming'),
(33, 19, 'Semester 3', 3, 'upcoming'),
(34, 19, 'Semester 4', 4, 'upcoming'),
(35, 19, 'Semester 5', 5, 'upcoming'),
(36, 19, 'Semester 6', 6, 'upcoming'),
(37, 19, 'Semester 7', 7, 'upcoming'),
(38, 19, 'Semester 8', 8, 'upcoming'),
(39, 20, 'Semester 1', 1, 'upcoming'),
(40, 20, 'Semester 2', 2, 'upcoming'),
(41, 20, 'Semester 3', 3, 'upcoming'),
(42, 20, 'Semester 4', 4, 'upcoming'),
(43, 21, 'Semester 1', 1, 'upcoming'),
(44, 21, 'Semester 2', 2, 'upcoming'),
(45, 21, 'Semester 3', 3, 'upcoming'),
(46, 21, 'Semester 4', 4, 'upcoming'),
(47, 21, 'Semester 5', 5, 'upcoming'),
(48, 21, 'Semester 6', 6, 'upcoming'),
(49, 21, 'Semester 7', 7, 'upcoming'),
(50, 21, 'Semester 8', 8, 'upcoming'),
(51, 22, 'Semester 1', 1, 'upcoming'),
(52, 22, 'Semester 2', 2, 'upcoming'),
(53, 22, 'Semester 3', 3, 'upcoming'),
(54, 22, 'Semester 4', 4, 'upcoming'),
(55, 22, 'Semester 5', 5, 'upcoming'),
(56, 22, 'Semester 6', 6, 'upcoming'),
(57, 22, 'Semester 7', 7, 'upcoming'),
(58, 22, 'Semester 8', 8, 'upcoming'),
(59, 23, 'Semester 1', 1, 'upcoming'),
(60, 23, 'Semester 2', 2, 'upcoming'),
(61, 23, 'Semester 3', 3, 'upcoming'),
(62, 23, 'Semester 4', 4, 'upcoming'),
(63, 23, 'Semester 5', 5, 'upcoming'),
(64, 23, 'Semester 6', 6, 'upcoming'),
(65, 23, 'Semester 7', 7, 'upcoming'),
(66, 23, 'Semester 8', 8, 'upcoming'),
(67, 24, 'Semester 1', 1, 'upcoming'),
(68, 24, 'Semester 2', 2, 'upcoming'),
(69, 24, 'Semester 3', 3, 'upcoming'),
(70, 24, 'Semester 4', 4, 'upcoming'),
(71, 24, 'Semester 5', 5, 'upcoming'),
(72, 24, 'Semester 6', 6, 'upcoming'),
(73, 24, 'Semester 7', 7, 'upcoming'),
(74, 24, 'Semester 8', 8, 'upcoming'),
(75, 25, 'Semester 1', 1, 'upcoming'),
(76, 25, 'Semester 2', 2, 'upcoming'),
(77, 25, 'Semester 3', 3, 'upcoming'),
(78, 25, 'Semester 4', 4, 'upcoming'),
(79, 25, 'Semester 5', 5, 'upcoming'),
(80, 25, 'Semester 6', 6, 'upcoming'),
(81, 25, 'Semester 7', 7, 'upcoming'),
(82, 25, 'Semester 8', 8, 'upcoming'),
(83, 26, 'Semester 1', 1, 'upcoming'),
(84, 26, 'Semester 2', 2, 'upcoming'),
(85, 26, 'Semester 3', 3, 'upcoming'),
(86, 26, 'Semester 4', 4, 'upcoming'),
(87, 26, 'Semester 5', 5, 'upcoming'),
(88, 26, 'Semester 6', 6, 'upcoming'),
(89, 26, 'Semester 7', 7, 'upcoming'),
(90, 26, 'Semester 8', 8, 'upcoming'),
(91, 27, 'Semester 1', 1, 'upcoming'),
(92, 27, 'Semester 2', 2, 'upcoming'),
(93, 27, 'Semester 3', 3, 'upcoming'),
(94, 27, 'Semester 4', 4, 'upcoming'),
(95, 27, 'Semester 5', 5, 'upcoming'),
(96, 27, 'Semester 6', 6, 'upcoming'),
(97, 27, 'Semester 7', 7, 'upcoming'),
(98, 27, 'Semester 8', 8, 'upcoming'),
(99, 28, 'Semester 1', 1, 'upcoming'),
(100, 28, 'Semester 2', 2, 'upcoming'),
(101, 28, 'Semester 3', 3, 'upcoming'),
(102, 28, 'Semester 4', 4, 'upcoming'),
(103, 28, 'Semester 5', 5, 'upcoming'),
(104, 28, 'Semester 6', 6, 'upcoming'),
(105, 28, 'Semester 7', 7, 'upcoming'),
(106, 28, 'Semester 8', 8, 'upcoming'),
(107, 29, 'Semester 1', 1, 'upcoming'),
(108, 29, 'Semester 2', 2, 'upcoming'),
(109, 29, 'Semester 3', 3, 'upcoming'),
(110, 29, 'Semester 4', 4, 'upcoming'),
(111, 30, 'Semester 1', 1, 'upcoming'),
(112, 30, 'Semester 2', 2, 'upcoming'),
(113, 30, 'Semester 3', 3, 'upcoming'),
(114, 30, 'Semester 4', 4, 'upcoming'),
(115, 30, 'Semester 5', 5, 'upcoming'),
(116, 30, 'Semester 6', 6, 'upcoming'),
(117, 30, 'Semester 7', 7, 'upcoming'),
(118, 30, 'Semester 8', 8, 'upcoming'),
(119, 31, 'Semester 1', 1, 'upcoming'),
(120, 31, 'Semester 2', 2, 'upcoming'),
(121, 31, 'Semester 3', 3, 'upcoming'),
(122, 31, 'Semester 4', 4, 'upcoming'),
(123, 31, 'Semester 5', 5, 'upcoming'),
(124, 31, 'Semester 6', 6, 'upcoming'),
(125, 31, 'Semester 7', 7, 'upcoming'),
(126, 31, 'Semester 8', 8, 'upcoming'),
(127, 32, 'Semester 1', 1, 'upcoming'),
(128, 32, 'Semester 2', 2, 'upcoming'),
(129, 32, 'Semester 3', 3, 'upcoming'),
(130, 32, 'Semester 4', 4, 'upcoming'),
(131, 32, 'Semester 5', 5, 'upcoming'),
(132, 32, 'Semester 6', 6, 'upcoming'),
(133, 32, 'Semester 7', 7, 'upcoming'),
(134, 32, 'Semester 8', 8, 'upcoming');

-- --------------------------------------------------------

--
-- Table structure for table `student_requests`
--

CREATE TABLE `student_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `request_type` enum('installment','scholarship','date_extension','fine_waiver') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_requests`
--

INSERT INTO `student_requests` (`id`, `user_id`, `invoice_id`, `request_type`, `description`, `status`, `admin_remarks`, `created_at`) VALUES
(1, 11, 4, 'installment', 'Due to temporary financial difficulties, I am unable to pay the full fee at once. I kindly request approval for an installment plan so that I can manage my payments.', 'approved', 'The installment request has been approved due to valid financial reasons. Kindly follow the payment schedule and clear dues on time.', '2026-03-08 08:18:34');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('footer_text', '© 2026 Universal Systems. All rights reserved.'),
('system_logo', 'https://cdn-icons-png.flaticon.com/512/906/906343.png'),
('system_name', 'Universal ERP');

-- --------------------------------------------------------

--
-- Table structure for table `sys_pages`
--

CREATE TABLE `sys_pages` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `page_name` varchar(100) NOT NULL,
  `page_url` varchar(255) DEFAULT '#',
  `icon_class` varchar(50) DEFAULT 'bi bi-circle',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sys_pages`
--

INSERT INTO `sys_pages` (`id`, `parent_id`, `page_name`, `page_url`, `icon_class`, `sort_order`) VALUES
(1, 0, 'Dashboard', 'index.php', 'bi bi-speedometer2', 1),
(2, 0, 'System Management', '#', 'bi bi-gear-fill', 2),
(3, 2, 'Manage Users', 'dashboards/super_admin/manage_users.php', 'bi bi-people', 1),
(4, 2, 'Manage Roles', 'dashboards/super_admin/manage_roles.php', 'bi bi-shield-lock', 2),
(5, 2, 'Manage Pages', 'dashboards/super_admin/manage_pages.php', 'bi bi-file-earmark-text', 3),
(6, 0, 'Academic Setup', '#', 'bi bi-mortarboard-fill', 10),
(7, 6, 'Manage Degrees', 'dashboards/super_admin/manage_programs.php', 'bi bi-book', 1),
(8, 6, 'Manage Semesters', 'dashboards/super_admin/manage_semesters.php', 'bi bi-calendar-event', 2),
(9, 0, 'Finance Management', '#', 'bi bi-cash-stack', 20),
(10, 9, 'Fee Structure', 'dashboards/finance/manage_fees.php', 'bi bi-list-columns-reverse', 1),
(11, 9, 'Generate Invoices', 'dashboards/finance/generate_invoices.php', 'bi bi-receipt', 2),
(12, 9, 'Installment Plans', 'dashboards/finance/installments.php', 'bi bi-calendar-check', 3),
(13, 9, 'Scholarships', 'dashboards/finance/manage_scholarships.php', 'bi bi-trophy', 4),
(14, 9, 'Finance Reports', 'dashboards/finance/reports.php', 'bi bi-graph-up', 5),
(15, 9, 'Fee Notifications', 'dashboards/finance/notifications.php', 'bi bi-bell-fill', 6),
(16, 9, 'Fine Engine', 'dashboards/finance/fine_engine.php', 'bi bi-stopwatch', 7),
(17, 0, 'Department Stats', 'dashboards/hod/department_report.php', 'bi bi-pie-chart', 40),
(18, 0, 'My Finances', 'dashboards/student/my_fees.php', 'bi bi-wallet2', 30),
(19, 0, 'Academic Setup', '#', 'bi bi-mortarboard-fill', 10),
(20, 19, 'Manage Degrees', 'dashboards/super_admin/manage_programs.php', 'bi bi-book', 1),
(21, 19, 'Manage Semesters', 'dashboards/super_admin/manage_semesters.php', 'bi bi-calendar-event', 2),
(22, 0, 'Finance Management', '#', 'bi bi-cash-stack', 20),
(23, 22, 'Fee Structure', 'dashboards/finance/manage_fees.php', 'bi bi-list-columns-reverse', 1),
(24, 22, 'Generate Invoices', 'dashboards/finance/generate_invoices.php', 'bi bi-receipt', 2),
(25, 22, 'Installment Plans', 'dashboards/finance/installments.php', 'bi bi-calendar-check', 3),
(26, 22, 'Scholarships', 'dashboards/finance/manage_scholarships.php', 'bi bi-trophy', 4),
(27, 22, 'Finance Reports', 'dashboards/finance/reports.php', 'bi bi-graph-up', 5),
(28, 22, 'Fee Notifications', 'dashboards/finance/notifications.php', 'bi bi-bell-fill', 6),
(29, 22, 'Fine Engine', 'dashboards/finance/fine_engine.php', 'bi bi-stopwatch', 7),
(30, 0, 'Department Stats', 'dashboards/hod/department_report.php', 'bi bi-pie-chart', 40),
(31, 0, 'Voucher Dispatch', 'dashboards/clerk/vouchers.php', 'bi bi-send', 100),
(32, 0, 'Verify Payments', 'dashboards/clerk/verify_payments.php', 'bi bi-check-all', 101),
(33, 6, 'Manage Departments', 'dashboards/super_admin/manage_departments.php', 'bi bi-building', 0),
(34, 6, 'Manage Courses', 'dashboards/super_admin/manage_courses.php', 'bi bi-journal-text', 3),
(35, 9, 'Help Requests', 'dashboards/finance/manage_requests.php', 'bi bi-patch-question', 8),
(36, 2, 'Students Directory', 'dashboards/super_admin/manage_students.php', 'bi bi-people-fill', 3);

-- --------------------------------------------------------

--
-- Table structure for table `sys_roles`
--

CREATE TABLE `sys_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_key` varchar(50) NOT NULL,
  `is_system_role` tinyint(1) DEFAULT 0 COMMENT '1=Cannot Delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sys_roles`
--

INSERT INTO `sys_roles` (`id`, `role_name`, `role_key`, `is_system_role`) VALUES
(1, 'Super Admin', 'super_admin', 1),
(2, 'Administrator', 'admin', 0),
(3, 'Student', 'student', 0),
(4, 'Suspended', 'suspended', 1),
(7, 'Finance Officer', 'finance', 1),
(8, 'Head of Department', 'hod', 1),
(13, 'Clerk', 'clerk', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `identity_no` varchar(50) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `roll_no` varchar(50) DEFAULT NULL,
  `program_id` int(11) DEFAULT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `fee_category` varchar(50) DEFAULT 'Regular',
  `scholarship_percent` decimal(5,2) DEFAULT 0.00,
  `scholarship_fixed` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `identity_no`, `registration_no`, `is_active`, `roll_no`, `program_id`, `semester_id`, `fee_category`, `scholarship_percent`, `scholarship_fixed`) VALUES
(1, 'Root Admin', 'admin@sys.com', '$2y$10$pHc7JdbFkPYZF9wjPCAun.QRyOMilAixrJzSOCM7eQXkGpq/qqYTS', 'super_admin', '12345-1234567-1', 'ADM-001', 1, NULL, NULL, NULL, 'Regular', 0.00, 0.00),
(11, 'Ali Raza', 'ali@student.com', '$2y$10$eVYt..nTzsEzgRBKoZ8TNe8p6nBJKUrggiZaxjxw/jc52E4MPqUjG', 'student', NULL, 'CS-24-101', 1, 'CS-24-101', 1, 5, 'Regular', 0.00, 5000.00),
(12, 'Finance Officer', 'finance@sys.com', '$2y$10$hBkoptu9wyqPxhEUWEFMo.CEObYXiICUd282vxOvEqdCKHSfZ7YSq', 'finance', NULL, NULL, 1, NULL, NULL, NULL, 'Regular', 0.00, 0.00),
(13, 'HOD Admin', 'hod@sys.com', '$2y$10$hBkoptu9wyqPxhEUWEFMo.CEObYXiICUd282vxOvEqdCKHSfZ7YSq', 'hod', NULL, NULL, 1, NULL, NULL, NULL, 'Regular', 0.00, 0.00),
(14, 'Verification Clerk', 'clerk@sys.com', '$2y$10$hBkoptu9wyqPxhEUWEFMo.CEObYXiICUd282vxOvEqdCKHSfZ7YSq', 'clerk', NULL, NULL, 1, NULL, NULL, NULL, 'Regular', 0.00, 0.00),
(15, 'Sarah Wajis Khan', 'sarah@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '01', 8, 5, 'Regular', 0.00, 0.00),
(16, 'Uswa Khalid', 'uswa@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '13', 8, 5, 'Regular', 0.00, 0.00),
(17, 'Visha Ahmad', 'visha@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '21', 8, 5, 'Regular', 0.00, 0.00),
(18, 'Laiba', 'laiba@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '31', 8, 5, 'Regular', 0.00, 0.00),
(19, 'Kainat', 'kainat@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '33', 8, 5, 'Regular', 0.00, 0.00),
(20, 'Irsha', 'irsha@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '38', 8, 5, 'Regular', 0.00, 0.00),
(21, 'Mahnoor', 'mahnoor@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '44', 8, 5, 'Regular', 0.00, 0.00),
(22, 'Nabiha', 'nabiha@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '54', 8, 5, 'Regular', 0.00, 0.00),
(23, 'Zeenat', 'zeenat@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '57', 8, 5, 'Regular', 0.00, 10000.00),
(24, 'Sawiba', 'sawiba@student.com', '$2y$10$WkxgGMUm87MlonMdR1HsG.QgphTz4szovWBs/335uA9tQ1lgRBl2G', 'student', NULL, NULL, 1, '63', 8, 5, 'Regular', 0.00, 0.00),
(105, 'Test User', 'admin@universal.com', '$2y$10$VgUMgslsIw.qVAut81COW.O2v2t2vdbwMIgXgRkdGwvpAlctwGdte', 'admin', '11111-1111111-1', '1234567890', 1, NULL, NULL, NULL, 'Regular', 0.00, 0.00),
(106, 'Test Admin', 'testadmin@test.com', '$2y$10$7wnsBg6cLOqrcSvea5MeI.ftVW3EZc.pY8Paz6NgL4uTlCtl/2LsS', 'admin', '22222-2222222-2', 'Admin001', 1, NULL, NULL, NULL, 'Regular', 0.00, 0.00),
(108, 'Abu Talib', 'abutalib@gmail.com', '$2y$10$DenW0RPXCJZ3SO33IGZ65ecaho2tzJW5HkTXPVC8/fUSbY5uW9LMG', 'student', NULL, NULL, 1, NULL, 8, 9, 'Regular', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `user_scholarships`
--

CREATE TABLE `user_scholarships` (
  `user_id` int(11) NOT NULL,
  `scholarship_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_scholarships`
--

INSERT INTO `user_scholarships` (`user_id`, `scholarship_id`) VALUES
(11, 1),
(11, 2),
(23, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_sem_cat` (`semester_id`,`fee_category`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `installments`
--
ALTER TABLE `installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `role_access`
--
ALTER TABLE `role_access`
  ADD PRIMARY KEY (`role_key`,`page_id`);

--
-- Indexes for table `scholarships`
--
ALTER TABLE `scholarships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `student_requests`
--
ALTER TABLE `student_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `sys_pages`
--
ALTER TABLE `sys_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sys_roles`
--
ALTER TABLE `sys_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_key` (`role_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_email` (`email`),
  ADD UNIQUE KEY `idx_identity` (`identity_no`),
  ADD UNIQUE KEY `idx_reg_no` (`registration_no`),
  ADD UNIQUE KEY `roll_no` (`roll_no`),
  ADD KEY `role` (`role`);

--
-- Indexes for table `user_scholarships`
--
ALTER TABLE `user_scholarships`
  ADD PRIMARY KEY (`user_id`,`scholarship_id`),
  ADD KEY `scholarship_id` (`scholarship_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `installments`
--
ALTER TABLE `installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `scholarships`
--
ALTER TABLE `scholarships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `student_requests`
--
ALTER TABLE `student_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sys_pages`
--
ALTER TABLE `sys_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `sys_roles`
--
ALTER TABLE `sys_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_structures_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `installments`
--
ALTER TABLE `installments`
  ADD CONSTRAINT `installments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `semesters`
--
ALTER TABLE `semesters`
  ADD CONSTRAINT `semesters_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_requests`
--
ALTER TABLE `student_requests`
  ADD CONSTRAINT `student_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `student_requests_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`);

--
-- Constraints for table `user_scholarships`
--
ALTER TABLE `user_scholarships`
  ADD CONSTRAINT `user_scholarships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_scholarships_ibfk_2` FOREIGN KEY (`scholarship_id`) REFERENCES `scholarships` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
