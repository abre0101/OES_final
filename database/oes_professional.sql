-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 08, 2026 at 06:46 PM
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
-- Database: `oes_professional`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`admin_id`, `username`, `password`, `full_name`, `email`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@dmu.edu.et', '+251911000001', 1, NULL, '2026-02-06 11:10:20', '2026-02-06 11:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('student','instructor','department_head','admin','unknown') DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `user_type`, `action`, `table_name`, `record_id`, `old_value`, `new_value`, `ip_address`, `user_agent`, `metadata`, `created_at`) VALUES
(1, 1, 'admin', 'Updated record in students - Fields: semester, academic_year', 'students', 1, 'semester: \'1\' → \'2\', academic_year: \'Year 1\' → \'1\'', '{\"department_id\":1,\"is_active\":1,\"semester\":2,\"academic_year\":\"1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"operation\":\"update\",\"record_id\":\"1\",\"changed_fields\":{\"semester\":{\"old\":1,\"new\":2},\"academic_year\":{\"old\":\"Year 1\",\"new\":\"1\"}}}', '2026-02-06 11:13:22'),
(2, 1, 'admin', 'Updated record in students - Fields: semester, academic_year', 'students', 1, 'semester: \'2\' → \'1\', academic_year: \'1\' → \'2\'', '{\"department_id\":1,\"is_active\":1,\"semester\":1,\"academic_year\":\"2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"operation\":\"update\",\"record_id\":\"1\",\"changed_fields\":{\"semester\":{\"old\":2,\"new\":1},\"academic_year\":{\"old\":\"1\",\"new\":\"2\"}}}', '2026-02-06 11:13:31'),
(3, 1, 'admin', 'Updated record in department_heads', 'department_heads', 5, NULL, 'Updated department head: Dr. Daniel Alemuu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"operation\":\"update\",\"record_id\":\"5\"}', '2026-02-06 11:14:04'),
(4, 1, 'admin', 'Updated record in department_heads', 'department_heads', 5, NULL, 'Updated department head: Dr. Daniel Alemu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"operation\":\"update\",\"record_id\":\"5\"}', '2026-02-06 11:14:14'),
(5, 0, 'admin', 'Logout', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"action_type\":\"logout\",\"timestamp\":\"2026-02-06 12:24:46\"}', '2026-02-06 11:24:46'),
(6, 1, 'student', 'Login successful - Username: alem.h', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"success\":true,\"username\":\"alem.h\",\"timestamp\":\"2026-02-06 16:05:49\"}', '2026-02-06 15:05:49'),
(7, 1, 'admin', 'Updated record in students - Fields: is_active, academic_year', 'students', 14, 'is_active: \'0\' → \'1\', academic_year: \'1\' → \'2\'', '{\"department_id\":1,\"is_active\":1,\"semester\":2,\"academic_year\":\"2\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"operation\":\"update\",\"record_id\":\"14\",\"changed_fields\":{\"is_active\":{\"old\":0,\"new\":1},\"academic_year\":{\"old\":\"1\",\"new\":\"2\"}}}', '2026-02-06 16:45:11'),
(8, 1, 'admin', 'Created new record in department_heads - Created department head: asdasd (DH006)', 'department_heads', 6, NULL, 'Created department head: asdasd (DH006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"operation\":\"create\",\"details\":\"Created department head: asdasd (DH006)\"}', '2026-02-06 16:50:04'),
(9, 1, 'admin', 'Deleted record from department_heads - Deleted department head: asdasd (DH006)', 'department_heads', 6, 'Deleted department head: asdasd (DH006)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"operation\":\"delete\",\"details\":\"Deleted department head: asdasd (DH006)\",\"record_id\":\"6\"}', '2026-02-06 16:50:24'),
(10, NULL, 'student', 'Login failed - Username: alem.h', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"success\":false,\"username\":\"alem.h\",\"timestamp\":\"2026-02-08 17:04:31\"}', '2026-02-08 16:04:31'),
(11, 1, 'student', 'Login successful - Username: alem.h', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"success\":true,\"username\":\"alem.h\",\"timestamp\":\"2026-02-08 17:04:55\"}', '2026-02-08 16:04:55'),
(12, NULL, 'student', 'Login failed - Username: alem.h', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"success\":false,\"username\":\"alem.h\",\"timestamp\":\"2026-02-08 17:05:24\"}', '2026-02-08 16:05:24'),
(13, 1, 'student', 'Login successful - Username: alem.h', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"success\":true,\"username\":\"alem.h\",\"timestamp\":\"2026-02-08 17:05:43\"}', '2026-02-08 16:05:43'),
(14, 1, 'student', 'Login successful - Username: alem.h', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"success\":true,\"username\":\"alem.h\",\"timestamp\":\"2026-02-08 17:05:57\"}', '2026-02-08 16:05:57'),
(15, 0, 'admin', 'Logout', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"action_type\":\"logout\",\"timestamp\":\"2026-02-08 17:07:46\"}', '2026-02-08 16:07:46'),
(16, 1, 'student', 'Login successful - Username: alem.h', 'authentication', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '{\"event_type\":\"authentication\",\"success\":true,\"username\":\"alem.h\",\"timestamp\":\"2026-02-08 17:12:46\"}', '2026-02-08 16:12:46');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `credit_hours` int(11) DEFAULT 3,
  `semester` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'NURS101', 'Fundamentals of Nursing', 4, 1, 'Introduction to basic nursing principles and practices', 1, '2026-02-06 11:10:19', '2026-02-06 16:17:09'),
(2, 1, 'NURS102', 'Anatomy and Physiology for Nurses', 5, 1, 'Study of human body structure and function', 1, '2026-02-06 11:10:19', '2026-02-06 11:13:02'),
(3, 1, 'NURS103', 'Medical-Surgical Nursing I', 4, 2, 'Care of adult patients with medical-surgical conditions', 1, '2026-02-06 11:10:19', '2026-02-06 11:10:19'),
(4, 2, 'MIDW101', 'Introduction to Midwifery', 3, 1, 'Fundamentals of midwifery practice', 1, '2026-02-06 11:10:20', '2026-02-06 11:10:20'),
(5, 2, 'MIDW102', 'Reproductive Health', 4, 1, 'Women\'s reproductive health and family planning', 1, '2026-02-06 11:10:20', '2026-02-06 11:10:20'),
(6, 3, 'PHO101', 'Introduction to Public Health', 3, 1, 'Overview of public health principles', 1, '2026-02-06 11:10:20', '2026-02-06 11:10:20'),
(7, 3, 'PHO102', 'Epidemiology', 4, 1, 'Study of disease patterns and prevention', 1, '2026-02-06 11:10:20', '2026-02-06 11:10:20'),
(8, 4, 'ANES101', 'Fundamentals of Anesthesia', 4, 1, 'Basic principles of anesthesia', 1, '2026-02-06 11:10:20', '2026-02-06 11:10:20'),
(9, 4, 'ANES102', 'Pharmacology for Anesthesia', 4, 1, 'Anesthetic drugs and their effects', 1, '2026-02-06 11:10:20', '2026-02-06 11:10:20'),
(10, 5, 'MLT101', 'Clinical Chemistry', 4, 1, 'Chemical analysis of body fluids', 1, '2026-02-06 11:10:20', '2026-02-06 11:10:20'),
(11, 5, 'MLT102', 'Hematology', 4, 1, 'Study of blood and blood disorders', 1, '2026-02-06 11:10:20', '2026-02-06 11:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `department_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `faculty_id`, `department_code`, `department_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'NURS', 'Nursing', 'Bachelor of Science in Nursing', 1, '2026-02-06 11:10:19', '2026-02-06 11:10:19'),
(2, 1, 'MIDW', 'Midwifery', 'Bachelor of Science in Midwifery', 1, '2026-02-06 11:10:19', '2026-02-06 11:10:19'),
(3, 1, 'PHO', 'Public Health Officer', 'Public Health Officer Program', 1, '2026-02-06 11:10:19', '2026-02-06 11:10:19'),
(4, 1, 'ANES', 'Anesthesia', 'Anesthesia Technology Program', 1, '2026-02-06 11:10:19', '2026-02-06 11:11:04'),
(5, 1, 'MLT', 'Medical Laboratory Technology', 'Medical Laboratory Science', 1, '2026-02-06 11:10:19', '2026-02-06 11:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `department_heads`
--

CREATE TABLE `department_heads` (
  `department_head_id` int(11) NOT NULL,
  `head_code` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `department_heads`
--

INSERT INTO `department_heads` (`department_head_id`, `head_code`, `username`, `password`, `full_name`, `email`, `phone`, `department_id`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'DH001', 'solomon.k', '$2y$10$ffJ6Dj2/B282k1zEGar9AOaiLbBQG3pjveqcA7vbFjw3Yik4ZOeWu', 'Dr. Solomon Kebede', 'solomon.k@dmu.edu.et', '+251911234589', 1, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 15:56:40'),
(2, 'DH002', 'rahel.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Rahel Tesfaye', 'rahel.t@dmu.edu.et', '+251911234581', 2, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(3, 'DH003', 'yared.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Yared Mengistu', 'yared.m@dmu.edu.et', '+251911234582', 3, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(4, 'DH004', 'helen.w', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Helen Worku', 'helen.w@dmu.edu.et', '+251911234583', 4, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(5, 'DH005', 'daniel.a', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Daniel Alemu', 'daniel.a@dmu.edu.et', '+251911234584', 5, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:14:14');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `exam_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `exam_category_id` int(11) NOT NULL,
  `exam_name` varchar(200) NOT NULL,
  `exam_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `duration_minutes` int(11) NOT NULL,
  `total_marks` int(11) DEFAULT 100,
  `pass_marks` int(11) DEFAULT 50,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `approval_status` enum('draft','pending','approved','rejected','revision') DEFAULT 'draft',
  `submitted_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approval_comments` text DEFAULT NULL,
  `revision_count` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`exam_id`, `course_id`, `exam_category_id`, `exam_name`, `exam_date`, `start_time`, `end_time`, `duration_minutes`, `total_marks`, `pass_marks`, `instructions`, `is_active`, `approval_status`, `submitted_at`, `approved_by`, `approved_at`, `approval_comments`, `revision_count`, `created_by`, `created_at`, `updated_at`) VALUES
(81, 1, 1, 'Fundamentals of Nursing - Midterm', '2026-03-15', '09:00:00', '10:30:00', 90, 20, 10, 'Read all questions carefully. Choose the best answer.', 1, 'approved', '2026-02-01 10:00:00', 1, '2026-02-02 14:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(82, 1, 2, 'Fundamentals of Nursing - Final Exam', '2026-05-20', '09:00:00', '11:00:00', 120, 30, 15, 'Comprehensive final exam covering all course material.', 1, 'approved', '2026-04-01 10:00:00', 1, '2026-04-02 14:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(83, 1, 3, 'Fundamentals of Nursing - Quiz 1', '2026-02-25', '10:00:00', '10:30:00', 30, 10, 5, 'Quick quiz on chapters 1-3.', 1, 'approved', '2026-02-10 09:00:00', 1, '2026-02-11 10:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(84, 1, 3, 'Fundamentals of Nursing - Quiz 2', '2026-04-10', '10:00:00', '10:30:00', 30, 10, 5, 'Quiz on chapters 4-6.', 1, 'approved', '2026-03-25 09:00:00', 1, '2026-03-26 10:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(85, 2, 1, 'Anatomy and Physiology - Midterm', '2026-03-16', '09:00:00', '10:30:00', 90, 20, 10, 'Answer all questions. Use of notes is not permitted.', 1, 'approved', '2026-02-01 11:00:00', 1, '2026-02-02 15:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(86, 2, 2, 'Anatomy and Physiology - Final Exam', '2026-05-21', '09:00:00', '11:00:00', 120, 30, 15, 'Final examination. No materials allowed.', 1, 'approved', '2026-04-01 11:00:00', 1, '2026-04-02 15:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(87, 2, 3, 'Anatomy and Physiology - Quiz 1', '2026-02-28', '11:00:00', '11:30:00', 30, 10, 5, 'Quiz on skeletal system.', 1, 'approved', '2026-02-12 09:00:00', 1, '2026-02-13 10:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(88, 3, 1, 'Medical-Surgical Nursing I - Midterm', '2026-03-20', '09:00:00', '10:30:00', 90, 20, 10, 'Midterm examination covering medical-surgical nursing concepts.', 1, 'pending', '2026-02-08 14:00:00', NULL, NULL, NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(89, 4, 1, 'Introduction to Midwifery - Midterm', '2026-03-17', '10:00:00', '11:30:00', 90, 15, 8, 'Answer all questions to the best of your ability.', 1, 'approved', '2026-02-03 09:00:00', 2, '2026-02-04 10:00:00', NULL, 0, 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(90, 4, 2, 'Introduction to Midwifery - Final Exam', '2026-05-22', '10:00:00', '12:00:00', 120, 25, 13, 'Comprehensive final exam.', 1, 'approved', '2026-04-02 09:00:00', 2, '2026-04-03 10:00:00', NULL, 0, 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(91, 5, 1, 'Reproductive Health - Midterm', NULL, NULL, NULL, 90, 0, 0, 'Draft exam - questions being added.', 0, 'draft', NULL, NULL, NULL, NULL, 0, 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(92, 6, 1, 'Introduction to Public Health - Midterm', '2026-03-18', '14:00:00', '15:30:00', 90, 15, 8, 'Read instructions carefully before starting.', 1, 'approved', '2026-02-03 10:00:00', 3, '2026-02-04 11:00:00', NULL, 0, 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(93, 6, 2, 'Introduction to Public Health - Final Exam', '2026-05-23', '14:00:00', '16:00:00', 120, 25, 13, 'Final exam covering all public health topics.', 1, 'approved', '2026-04-03 10:00:00', 3, '2026-04-04 11:00:00', NULL, 0, 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(94, 6, 3, 'Introduction to Public Health - Quiz 1', '2026-03-01', '14:00:00', '14:30:00', 30, 10, 5, 'Quiz on epidemiology basics.', 1, 'approved', '2026-02-14 09:00:00', 3, '2026-02-15 10:00:00', NULL, 0, 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(95, 8, 1, 'Fundamentals of Anesthesia - Midterm', '2026-03-19', '11:00:00', '12:30:00', 90, 15, 8, 'Midterm exam on anesthesia fundamentals.', 1, 'approved', '2026-02-04 10:00:00', 4, '2026-02-05 11:00:00', NULL, 0, 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(96, 8, 3, 'Fundamentals of Anesthesia - Quiz 1', '2026-02-26', '11:00:00', '11:30:00', 30, 10, 5, 'Quiz covering basic anesthesia principles.', 1, 'approved', '2026-02-10 10:00:00', 4, '2026-02-11 11:00:00', NULL, 0, 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(97, 9, 1, 'Pharmacology for Anesthesia - Midterm', NULL, NULL, NULL, 90, 15, 8, 'Midterm exam on anesthetic pharmacology.', 0, 'rejected', '2026-02-05 10:00:00', 4, '2026-02-06 09:00:00', NULL, 0, 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(98, 10, 1, 'Clinical Chemistry - Midterm', '2026-03-21', '13:00:00', '14:30:00', 90, 20, 10, 'Laboratory chemistry midterm exam.', 1, 'pending', '2026-02-08 15:00:00', NULL, NULL, NULL, 0, 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(99, 11, 1, 'Hematology - Midterm', NULL, NULL, NULL, 90, 0, 0, 'Draft exam - under construction.', 0, 'draft', NULL, NULL, NULL, NULL, 0, 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(100, 1, 4, 'Fundamentals of Nursing - Makeup Exam', '2026-06-10', '09:00:00', '11:00:00', 120, 30, 15, 'Makeup exam for students who missed the final.', 1, 'approved', '2026-05-25 10:00:00', 1, '2026-05-26 14:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(101, 2, 4, 'Anatomy and Physiology - Makeup Exam', '2026-06-11', '09:00:00', '11:00:00', 120, 30, 15, 'Makeup exam for absent students.', 1, 'approved', '2026-05-25 11:00:00', 1, '2026-05-26 15:00:00', NULL, 0, 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(102, 1, 1, 'Fundamentals of Nursing - Midterm', '2026-03-15', '09:00:00', '10:30:00', 90, 20, 10, 'Read all questions carefully. Choose the best answer.', 1, 'approved', '2026-02-01 10:00:00', 1, '2026-02-02 14:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(103, 1, 2, 'Fundamentals of Nursing - Final Exam', '2026-05-20', '09:00:00', '11:00:00', 120, 30, 15, 'Comprehensive final exam covering all course material.', 1, 'approved', '2026-04-01 10:00:00', 1, '2026-04-02 14:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(104, 1, 3, 'Fundamentals of Nursing - Quiz 1', '2026-02-25', '10:00:00', '10:30:00', 30, 10, 5, 'Quick quiz on chapters 1-3.', 1, 'approved', '2026-02-10 09:00:00', 1, '2026-02-11 10:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(105, 1, 3, 'Fundamentals of Nursing - Quiz 2', '2026-04-10', '10:00:00', '10:30:00', 30, 10, 5, 'Quiz on chapters 4-6.', 1, 'approved', '2026-03-25 09:00:00', 1, '2026-03-26 10:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(106, 2, 1, 'Anatomy and Physiology - Midterm', '2026-03-16', '09:00:00', '10:30:00', 90, 20, 10, 'Answer all questions. Use of notes is not permitted.', 1, 'approved', '2026-02-01 11:00:00', 1, '2026-02-02 15:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(107, 2, 2, 'Anatomy and Physiology - Final Exam', '2026-05-21', '09:00:00', '11:00:00', 120, 30, 15, 'Final examination. No materials allowed.', 1, 'approved', '2026-04-01 11:00:00', 1, '2026-04-02 15:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(108, 2, 3, 'Anatomy and Physiology - Quiz 1', '2026-02-28', '11:00:00', '11:30:00', 30, 10, 5, 'Quiz on skeletal system.', 1, 'approved', '2026-02-12 09:00:00', 1, '2026-02-13 10:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(109, 3, 1, 'Medical-Surgical Nursing I - Midterm', '2026-03-20', '09:00:00', '10:30:00', 90, 20, 10, 'Midterm examination covering medical-surgical nursing concepts.', 1, 'pending', '2026-02-08 14:00:00', NULL, NULL, NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(110, 4, 1, 'Introduction to Midwifery - Midterm', '2026-03-17', '10:00:00', '11:30:00', 90, 15, 8, 'Answer all questions to the best of your ability.', 1, 'approved', '2026-02-03 09:00:00', 2, '2026-02-04 10:00:00', NULL, 0, 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(111, 4, 2, 'Introduction to Midwifery - Final Exam', '2026-05-22', '10:00:00', '12:00:00', 120, 25, 13, 'Comprehensive final exam.', 1, 'approved', '2026-04-02 09:00:00', 2, '2026-04-03 10:00:00', NULL, 0, 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(112, 5, 1, 'Reproductive Health - Midterm', NULL, NULL, NULL, 90, 0, 0, 'Draft exam - questions being added.', 0, 'draft', NULL, NULL, NULL, NULL, 0, 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(113, 6, 1, 'Introduction to Public Health - Midterm', '2026-03-18', '14:00:00', '15:30:00', 90, 15, 8, 'Read instructions carefully before starting.', 1, 'approved', '2026-02-03 10:00:00', 3, '2026-02-04 11:00:00', NULL, 0, 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(114, 6, 2, 'Introduction to Public Health - Final Exam', '2026-05-23', '14:00:00', '16:00:00', 120, 25, 13, 'Final exam covering all public health topics.', 1, 'approved', '2026-04-03 10:00:00', 3, '2026-04-04 11:00:00', NULL, 0, 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(115, 6, 3, 'Introduction to Public Health - Quiz 1', '2026-03-01', '14:00:00', '14:30:00', 30, 10, 5, 'Quiz on epidemiology basics.', 1, 'approved', '2026-02-14 09:00:00', 3, '2026-02-15 10:00:00', NULL, 0, 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(116, 8, 1, 'Fundamentals of Anesthesia - Midterm', '2026-03-19', '11:00:00', '12:30:00', 90, 15, 8, 'Midterm exam on anesthesia fundamentals.', 1, 'approved', '2026-02-04 10:00:00', 4, '2026-02-05 11:00:00', NULL, 0, 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(117, 8, 3, 'Fundamentals of Anesthesia - Quiz 1', '2026-02-26', '11:00:00', '11:30:00', 30, 10, 5, 'Quiz covering basic anesthesia principles.', 1, 'approved', '2026-02-10 10:00:00', 4, '2026-02-11 11:00:00', NULL, 0, 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(118, 9, 1, 'Pharmacology for Anesthesia - Midterm', NULL, NULL, NULL, 90, 15, 8, 'Midterm exam on anesthetic pharmacology.', 0, 'rejected', '2026-02-05 10:00:00', 4, '2026-02-06 09:00:00', NULL, 0, 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(119, 10, 1, 'Clinical Chemistry - Midterm', '2026-03-21', '13:00:00', '14:30:00', 90, 20, 10, 'Laboratory chemistry midterm exam.', 1, 'pending', '2026-02-08 15:00:00', NULL, NULL, NULL, 0, 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(120, 11, 1, 'Hematology - Midterm', NULL, NULL, NULL, 90, 0, 0, 'Draft exam - under construction.', 0, 'draft', NULL, NULL, NULL, NULL, 0, 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(121, 1, 4, 'Fundamentals of Nursing - Makeup Exam', '2026-06-10', '09:00:00', '11:00:00', 120, 30, 15, 'Makeup exam for students who missed the final.', 1, 'approved', '2026-05-25 10:00:00', 1, '2026-05-26 14:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(122, 2, 4, 'Anatomy and Physiology - Makeup Exam', '2026-06-11', '09:00:00', '11:00:00', 120, 30, 15, 'Makeup exam for absent students.', 1, 'approved', '2026-05-25 11:00:00', 1, '2026-05-26 15:00:00', NULL, 0, 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40');

-- --------------------------------------------------------

--
-- Table structure for table `exam_approval_history`
--

CREATE TABLE `exam_approval_history` (
  `history_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `action` enum('submitted','approved','revision_requested','rejected','resubmitted') NOT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_by_type` enum('instructor','department_head') NOT NULL,
  `comments` text DEFAULT NULL,
  `previous_status` enum('draft','pending','approved','revision','rejected') DEFAULT NULL,
  `new_status` enum('draft','pending','approved','revision','rejected') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_categories`
--

CREATE TABLE `exam_categories` (
  `exam_category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_categories`
--

INSERT INTO `exam_categories` (`exam_category_id`, `category_name`, `description`, `is_active`, `created_at`) VALUES
(1, 'Midterm', 'Mid-semester examination', 1, '2026-02-06 11:10:22'),
(2, 'Final', 'End of semester examination', 1, '2026-02-06 11:10:22'),
(3, 'Quiz', 'Short assessment', 1, '2026-02-06 11:10:22'),
(4, 'Makeup', 'Makeup examination', 1, '2026-02-06 11:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `exam_questions`
--

CREATE TABLE `exam_questions` (
  `exam_question_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `question_order` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_questions`
--

INSERT INTO `exam_questions` (`exam_question_id`, `exam_id`, `question_id`, `question_order`, `created_at`) VALUES
(776, 1, 1, 1, '2026-02-08 17:42:16'),
(777, 1, 2, 2, '2026-02-08 17:42:16'),
(778, 1, 3, 3, '2026-02-08 17:42:16'),
(779, 1, 4, 4, '2026-02-08 17:42:16'),
(780, 1, 5, 5, '2026-02-08 17:42:16'),
(781, 1, 6, 6, '2026-02-08 17:42:16'),
(782, 1, 31, 7, '2026-02-08 17:42:16'),
(783, 1, 32, 8, '2026-02-08 17:42:16'),
(784, 1, 33, 9, '2026-02-08 17:42:16'),
(785, 1, 34, 10, '2026-02-08 17:42:16'),
(786, 1, 35, 11, '2026-02-08 17:42:16'),
(787, 1, 36, 12, '2026-02-08 17:42:16'),
(788, 1, 37, 13, '2026-02-08 17:42:16'),
(789, 1, 38, 14, '2026-02-08 17:42:16'),
(790, 1, 39, 15, '2026-02-08 17:42:16'),
(791, 1, 40, 16, '2026-02-08 17:42:16'),
(792, 1, 41, 17, '2026-02-08 17:42:16'),
(793, 1, 42, 18, '2026-02-08 17:42:16'),
(794, 1, 43, 19, '2026-02-08 17:42:16'),
(795, 1, 44, 20, '2026-02-08 17:42:16'),
(796, 2, 1, 1, '2026-02-08 17:42:17'),
(797, 2, 2, 2, '2026-02-08 17:42:17'),
(798, 2, 3, 3, '2026-02-08 17:42:17'),
(799, 2, 4, 4, '2026-02-08 17:42:17'),
(800, 2, 5, 5, '2026-02-08 17:42:17'),
(801, 2, 6, 6, '2026-02-08 17:42:17'),
(802, 2, 31, 7, '2026-02-08 17:42:17'),
(803, 2, 32, 8, '2026-02-08 17:42:17'),
(804, 2, 33, 9, '2026-02-08 17:42:17'),
(805, 2, 34, 10, '2026-02-08 17:42:17'),
(806, 2, 35, 11, '2026-02-08 17:42:17'),
(807, 2, 36, 12, '2026-02-08 17:42:17'),
(808, 2, 37, 13, '2026-02-08 17:42:17'),
(809, 2, 38, 14, '2026-02-08 17:42:17'),
(810, 2, 39, 15, '2026-02-08 17:42:17'),
(811, 2, 40, 16, '2026-02-08 17:42:17'),
(812, 2, 41, 17, '2026-02-08 17:42:17'),
(813, 2, 42, 18, '2026-02-08 17:42:17'),
(814, 2, 43, 19, '2026-02-08 17:42:17'),
(815, 2, 44, 20, '2026-02-08 17:42:17'),
(816, 2, 1, 21, '2026-02-08 17:42:17'),
(817, 2, 2, 22, '2026-02-08 17:42:17'),
(818, 2, 3, 23, '2026-02-08 17:42:17'),
(819, 2, 4, 24, '2026-02-08 17:42:17'),
(820, 2, 5, 25, '2026-02-08 17:42:17'),
(821, 2, 6, 26, '2026-02-08 17:42:17'),
(822, 2, 31, 27, '2026-02-08 17:42:17'),
(823, 2, 32, 28, '2026-02-08 17:42:17'),
(824, 2, 33, 29, '2026-02-08 17:42:17'),
(825, 2, 34, 30, '2026-02-08 17:42:17'),
(826, 3, 1, 1, '2026-02-08 17:42:17'),
(827, 3, 2, 2, '2026-02-08 17:42:17'),
(828, 3, 3, 3, '2026-02-08 17:42:17'),
(829, 3, 4, 4, '2026-02-08 17:42:17'),
(830, 3, 5, 5, '2026-02-08 17:42:17'),
(831, 3, 6, 6, '2026-02-08 17:42:17'),
(832, 3, 31, 7, '2026-02-08 17:42:17'),
(833, 3, 32, 8, '2026-02-08 17:42:17'),
(834, 3, 33, 9, '2026-02-08 17:42:17'),
(835, 3, 34, 10, '2026-02-08 17:42:17'),
(836, 4, 35, 1, '2026-02-08 17:42:17'),
(837, 4, 36, 2, '2026-02-08 17:42:17'),
(838, 4, 37, 3, '2026-02-08 17:42:17'),
(839, 4, 38, 4, '2026-02-08 17:42:17'),
(840, 4, 39, 5, '2026-02-08 17:42:17'),
(841, 4, 40, 6, '2026-02-08 17:42:17'),
(842, 4, 41, 7, '2026-02-08 17:42:17'),
(843, 4, 42, 8, '2026-02-08 17:42:17'),
(844, 4, 43, 9, '2026-02-08 17:42:17'),
(845, 4, 44, 10, '2026-02-08 17:42:17'),
(846, 5, 7, 1, '2026-02-08 17:42:17'),
(847, 5, 8, 2, '2026-02-08 17:42:17'),
(848, 5, 9, 3, '2026-02-08 17:42:17'),
(849, 5, 10, 4, '2026-02-08 17:42:17'),
(850, 5, 11, 5, '2026-02-08 17:42:17'),
(851, 5, 12, 6, '2026-02-08 17:42:17'),
(852, 5, 45, 7, '2026-02-08 17:42:17'),
(853, 5, 46, 8, '2026-02-08 17:42:17'),
(854, 5, 47, 9, '2026-02-08 17:42:17'),
(855, 5, 48, 10, '2026-02-08 17:42:17'),
(856, 5, 49, 11, '2026-02-08 17:42:17'),
(857, 5, 50, 12, '2026-02-08 17:42:17'),
(858, 5, 51, 13, '2026-02-08 17:42:17'),
(859, 5, 52, 14, '2026-02-08 17:42:17'),
(860, 5, 53, 15, '2026-02-08 17:42:17'),
(861, 5, 54, 16, '2026-02-08 17:42:17'),
(862, 5, 55, 17, '2026-02-08 17:42:17'),
(863, 5, 56, 18, '2026-02-08 17:42:17'),
(864, 5, 57, 19, '2026-02-08 17:42:17'),
(865, 5, 58, 20, '2026-02-08 17:42:17'),
(866, 6, 7, 1, '2026-02-08 17:42:17'),
(867, 6, 8, 2, '2026-02-08 17:42:17'),
(868, 6, 9, 3, '2026-02-08 17:42:17'),
(869, 6, 10, 4, '2026-02-08 17:42:17'),
(870, 6, 11, 5, '2026-02-08 17:42:17'),
(871, 6, 12, 6, '2026-02-08 17:42:17'),
(872, 6, 45, 7, '2026-02-08 17:42:17'),
(873, 6, 46, 8, '2026-02-08 17:42:17'),
(874, 6, 47, 9, '2026-02-08 17:42:17'),
(875, 6, 48, 10, '2026-02-08 17:42:17'),
(876, 6, 49, 11, '2026-02-08 17:42:17'),
(877, 6, 50, 12, '2026-02-08 17:42:17'),
(878, 6, 51, 13, '2026-02-08 17:42:17'),
(879, 6, 52, 14, '2026-02-08 17:42:17'),
(880, 6, 53, 15, '2026-02-08 17:42:17'),
(881, 6, 54, 16, '2026-02-08 17:42:17'),
(882, 6, 55, 17, '2026-02-08 17:42:17'),
(883, 6, 56, 18, '2026-02-08 17:42:17'),
(884, 6, 57, 19, '2026-02-08 17:42:17'),
(885, 6, 58, 20, '2026-02-08 17:42:17'),
(886, 6, 7, 21, '2026-02-08 17:42:17'),
(887, 6, 8, 22, '2026-02-08 17:42:17'),
(888, 6, 9, 23, '2026-02-08 17:42:17'),
(889, 6, 10, 24, '2026-02-08 17:42:17'),
(890, 6, 11, 25, '2026-02-08 17:42:17'),
(891, 6, 12, 26, '2026-02-08 17:42:17'),
(892, 6, 45, 27, '2026-02-08 17:42:17'),
(893, 6, 46, 28, '2026-02-08 17:42:17'),
(894, 6, 47, 29, '2026-02-08 17:42:17'),
(895, 6, 48, 30, '2026-02-08 17:42:17'),
(896, 7, 7, 1, '2026-02-08 17:42:17'),
(897, 7, 8, 2, '2026-02-08 17:42:17'),
(898, 7, 9, 3, '2026-02-08 17:42:17'),
(899, 7, 10, 4, '2026-02-08 17:42:17'),
(900, 7, 11, 5, '2026-02-08 17:42:17'),
(901, 7, 12, 6, '2026-02-08 17:42:17'),
(902, 7, 45, 7, '2026-02-08 17:42:17'),
(903, 7, 46, 8, '2026-02-08 17:42:17'),
(904, 7, 47, 9, '2026-02-08 17:42:17'),
(905, 7, 48, 10, '2026-02-08 17:42:17'),
(906, 8, 59, 1, '2026-02-08 17:42:17'),
(907, 8, 60, 2, '2026-02-08 17:42:17'),
(908, 8, 61, 3, '2026-02-08 17:42:17'),
(909, 8, 62, 4, '2026-02-08 17:42:17'),
(910, 8, 63, 5, '2026-02-08 17:42:17'),
(911, 8, 64, 6, '2026-02-08 17:42:17'),
(912, 8, 65, 7, '2026-02-08 17:42:17'),
(913, 8, 66, 8, '2026-02-08 17:42:17'),
(914, 8, 67, 9, '2026-02-08 17:42:17'),
(915, 8, 68, 10, '2026-02-08 17:42:17'),
(916, 8, 69, 11, '2026-02-08 17:42:17'),
(917, 8, 70, 12, '2026-02-08 17:42:17'),
(918, 8, 59, 13, '2026-02-08 17:42:17'),
(919, 8, 60, 14, '2026-02-08 17:42:17'),
(920, 8, 61, 15, '2026-02-08 17:42:17'),
(921, 8, 62, 16, '2026-02-08 17:42:17'),
(922, 8, 63, 17, '2026-02-08 17:42:17'),
(923, 8, 64, 18, '2026-02-08 17:42:17'),
(924, 8, 65, 19, '2026-02-08 17:42:17'),
(925, 8, 66, 20, '2026-02-08 17:42:17'),
(926, 9, 13, 1, '2026-02-08 17:42:17'),
(927, 9, 14, 2, '2026-02-08 17:42:17'),
(928, 9, 15, 3, '2026-02-08 17:42:17'),
(929, 9, 16, 4, '2026-02-08 17:42:17'),
(930, 9, 17, 5, '2026-02-08 17:42:17'),
(931, 9, 71, 6, '2026-02-08 17:42:17'),
(932, 9, 72, 7, '2026-02-08 17:42:17'),
(933, 9, 73, 8, '2026-02-08 17:42:17'),
(934, 9, 74, 9, '2026-02-08 17:42:17'),
(935, 9, 75, 10, '2026-02-08 17:42:17'),
(936, 9, 76, 11, '2026-02-08 17:42:17'),
(937, 9, 77, 12, '2026-02-08 17:42:17'),
(938, 9, 78, 13, '2026-02-08 17:42:17'),
(939, 9, 79, 14, '2026-02-08 17:42:17'),
(940, 9, 80, 15, '2026-02-08 17:42:17'),
(941, 10, 13, 1, '2026-02-08 17:42:17'),
(942, 10, 14, 2, '2026-02-08 17:42:17'),
(943, 10, 15, 3, '2026-02-08 17:42:17'),
(944, 10, 16, 4, '2026-02-08 17:42:17'),
(945, 10, 17, 5, '2026-02-08 17:42:17'),
(946, 10, 71, 6, '2026-02-08 17:42:17'),
(947, 10, 72, 7, '2026-02-08 17:42:17'),
(948, 10, 73, 8, '2026-02-08 17:42:17'),
(949, 10, 74, 9, '2026-02-08 17:42:17'),
(950, 10, 75, 10, '2026-02-08 17:42:17'),
(951, 10, 76, 11, '2026-02-08 17:42:17'),
(952, 10, 77, 12, '2026-02-08 17:42:17'),
(953, 10, 78, 13, '2026-02-08 17:42:17'),
(954, 10, 79, 14, '2026-02-08 17:42:17'),
(955, 10, 80, 15, '2026-02-08 17:42:17'),
(956, 10, 13, 16, '2026-02-08 17:42:17'),
(957, 10, 14, 17, '2026-02-08 17:42:17'),
(958, 10, 15, 18, '2026-02-08 17:42:17'),
(959, 10, 16, 19, '2026-02-08 17:42:17'),
(960, 10, 17, 20, '2026-02-08 17:42:17'),
(961, 10, 71, 21, '2026-02-08 17:42:17'),
(962, 10, 72, 22, '2026-02-08 17:42:17'),
(963, 10, 73, 23, '2026-02-08 17:42:17'),
(964, 10, 74, 24, '2026-02-08 17:42:17'),
(965, 10, 75, 25, '2026-02-08 17:42:17'),
(966, 12, 18, 1, '2026-02-08 17:42:17'),
(967, 12, 19, 2, '2026-02-08 17:42:17'),
(968, 12, 20, 3, '2026-02-08 17:42:17'),
(969, 12, 21, 4, '2026-02-08 17:42:17'),
(970, 12, 22, 5, '2026-02-08 17:42:17'),
(971, 12, 81, 6, '2026-02-08 17:42:17'),
(972, 12, 82, 7, '2026-02-08 17:42:17'),
(973, 12, 83, 8, '2026-02-08 17:42:17'),
(974, 12, 84, 9, '2026-02-08 17:42:17'),
(975, 12, 85, 10, '2026-02-08 17:42:17'),
(976, 12, 86, 11, '2026-02-08 17:42:17'),
(977, 12, 87, 12, '2026-02-08 17:42:17'),
(978, 12, 88, 13, '2026-02-08 17:42:17'),
(979, 12, 89, 14, '2026-02-08 17:42:17'),
(980, 12, 90, 15, '2026-02-08 17:42:17'),
(981, 13, 18, 1, '2026-02-08 17:42:18'),
(982, 13, 19, 2, '2026-02-08 17:42:18'),
(983, 13, 20, 3, '2026-02-08 17:42:18'),
(984, 13, 21, 4, '2026-02-08 17:42:18'),
(985, 13, 22, 5, '2026-02-08 17:42:18'),
(986, 13, 81, 6, '2026-02-08 17:42:18'),
(987, 13, 82, 7, '2026-02-08 17:42:18'),
(988, 13, 83, 8, '2026-02-08 17:42:18'),
(989, 13, 84, 9, '2026-02-08 17:42:18'),
(990, 13, 85, 10, '2026-02-08 17:42:18'),
(991, 13, 86, 11, '2026-02-08 17:42:18'),
(992, 13, 87, 12, '2026-02-08 17:42:18'),
(993, 13, 88, 13, '2026-02-08 17:42:18'),
(994, 13, 89, 14, '2026-02-08 17:42:18'),
(995, 13, 90, 15, '2026-02-08 17:42:18'),
(996, 13, 18, 16, '2026-02-08 17:42:18'),
(997, 13, 19, 17, '2026-02-08 17:42:18'),
(998, 13, 20, 18, '2026-02-08 17:42:18'),
(999, 13, 21, 19, '2026-02-08 17:42:18'),
(1000, 13, 22, 20, '2026-02-08 17:42:18'),
(1001, 13, 81, 21, '2026-02-08 17:42:18'),
(1002, 13, 82, 22, '2026-02-08 17:42:18'),
(1003, 13, 83, 23, '2026-02-08 17:42:18'),
(1004, 13, 84, 24, '2026-02-08 17:42:18'),
(1005, 13, 85, 25, '2026-02-08 17:42:18'),
(1006, 14, 18, 1, '2026-02-08 17:42:18'),
(1007, 14, 19, 2, '2026-02-08 17:42:18'),
(1008, 14, 20, 3, '2026-02-08 17:42:18'),
(1009, 14, 21, 4, '2026-02-08 17:42:18'),
(1010, 14, 22, 5, '2026-02-08 17:42:18'),
(1011, 14, 81, 6, '2026-02-08 17:42:18'),
(1012, 14, 82, 7, '2026-02-08 17:42:18'),
(1013, 14, 83, 8, '2026-02-08 17:42:18'),
(1014, 14, 84, 9, '2026-02-08 17:42:18'),
(1015, 14, 85, 10, '2026-02-08 17:42:18'),
(1016, 15, 23, 1, '2026-02-08 17:42:18'),
(1017, 15, 24, 2, '2026-02-08 17:42:18'),
(1018, 15, 25, 3, '2026-02-08 17:42:18'),
(1019, 15, 26, 4, '2026-02-08 17:42:18'),
(1020, 15, 27, 5, '2026-02-08 17:42:18'),
(1021, 15, 91, 6, '2026-02-08 17:42:18'),
(1022, 15, 92, 7, '2026-02-08 17:42:18'),
(1023, 15, 93, 8, '2026-02-08 17:42:18'),
(1024, 15, 94, 9, '2026-02-08 17:42:18'),
(1025, 15, 95, 10, '2026-02-08 17:42:18'),
(1026, 15, 96, 11, '2026-02-08 17:42:18'),
(1027, 15, 97, 12, '2026-02-08 17:42:18'),
(1028, 15, 98, 13, '2026-02-08 17:42:18'),
(1029, 15, 99, 14, '2026-02-08 17:42:18'),
(1030, 15, 100, 15, '2026-02-08 17:42:18'),
(1031, 16, 23, 1, '2026-02-08 17:42:18'),
(1032, 16, 24, 2, '2026-02-08 17:42:18'),
(1033, 16, 25, 3, '2026-02-08 17:42:18'),
(1034, 16, 26, 4, '2026-02-08 17:42:18'),
(1035, 16, 27, 5, '2026-02-08 17:42:18'),
(1036, 16, 91, 6, '2026-02-08 17:42:18'),
(1037, 16, 92, 7, '2026-02-08 17:42:18'),
(1038, 16, 93, 8, '2026-02-08 17:42:18'),
(1039, 16, 94, 9, '2026-02-08 17:42:18'),
(1040, 16, 95, 10, '2026-02-08 17:42:18'),
(1041, 17, 91, 1, '2026-02-08 17:42:18'),
(1042, 17, 92, 2, '2026-02-08 17:42:18'),
(1043, 17, 93, 3, '2026-02-08 17:42:18'),
(1044, 17, 94, 4, '2026-02-08 17:42:18'),
(1045, 17, 95, 5, '2026-02-08 17:42:18'),
(1046, 17, 96, 6, '2026-02-08 17:42:18'),
(1047, 17, 97, 7, '2026-02-08 17:42:18'),
(1048, 17, 98, 8, '2026-02-08 17:42:18'),
(1049, 17, 99, 9, '2026-02-08 17:42:18'),
(1050, 17, 100, 10, '2026-02-08 17:42:18'),
(1051, 17, 91, 11, '2026-02-08 17:42:18'),
(1052, 17, 92, 12, '2026-02-08 17:42:18'),
(1053, 17, 93, 13, '2026-02-08 17:42:18'),
(1054, 17, 94, 14, '2026-02-08 17:42:18'),
(1055, 17, 95, 15, '2026-02-08 17:42:18'),
(1056, 18, 28, 1, '2026-02-08 17:42:18'),
(1057, 18, 29, 2, '2026-02-08 17:42:18'),
(1058, 18, 30, 3, '2026-02-08 17:42:18'),
(1059, 18, 101, 4, '2026-02-08 17:42:18'),
(1060, 18, 102, 5, '2026-02-08 17:42:18'),
(1061, 18, 103, 6, '2026-02-08 17:42:18'),
(1062, 18, 104, 7, '2026-02-08 17:42:18'),
(1063, 18, 105, 8, '2026-02-08 17:42:18'),
(1064, 18, 106, 9, '2026-02-08 17:42:18'),
(1065, 18, 107, 10, '2026-02-08 17:42:18'),
(1066, 18, 108, 11, '2026-02-08 17:42:18'),
(1067, 18, 109, 12, '2026-02-08 17:42:18'),
(1068, 18, 110, 13, '2026-02-08 17:42:18'),
(1069, 18, 28, 14, '2026-02-08 17:42:18'),
(1070, 18, 29, 15, '2026-02-08 17:42:18'),
(1071, 18, 30, 16, '2026-02-08 17:42:18'),
(1072, 18, 101, 17, '2026-02-08 17:42:18'),
(1073, 18, 102, 18, '2026-02-08 17:42:18'),
(1074, 18, 103, 19, '2026-02-08 17:42:18'),
(1075, 18, 104, 20, '2026-02-08 17:42:18'),
(1076, 20, 1, 1, '2026-02-08 17:42:18'),
(1077, 20, 2, 2, '2026-02-08 17:42:18'),
(1078, 20, 3, 3, '2026-02-08 17:42:18'),
(1079, 20, 4, 4, '2026-02-08 17:42:18'),
(1080, 20, 5, 5, '2026-02-08 17:42:18'),
(1081, 20, 6, 6, '2026-02-08 17:42:18'),
(1082, 20, 31, 7, '2026-02-08 17:42:18'),
(1083, 20, 32, 8, '2026-02-08 17:42:18'),
(1084, 20, 33, 9, '2026-02-08 17:42:18'),
(1085, 20, 34, 10, '2026-02-08 17:42:18'),
(1086, 20, 35, 11, '2026-02-08 17:42:18'),
(1087, 20, 36, 12, '2026-02-08 17:42:18'),
(1088, 20, 37, 13, '2026-02-08 17:42:18'),
(1089, 20, 38, 14, '2026-02-08 17:42:18'),
(1090, 20, 39, 15, '2026-02-08 17:42:18'),
(1091, 20, 40, 16, '2026-02-08 17:42:18'),
(1092, 20, 41, 17, '2026-02-08 17:42:18'),
(1093, 20, 42, 18, '2026-02-08 17:42:18'),
(1094, 20, 43, 19, '2026-02-08 17:42:18'),
(1095, 20, 44, 20, '2026-02-08 17:42:18'),
(1096, 20, 1, 21, '2026-02-08 17:42:18'),
(1097, 20, 2, 22, '2026-02-08 17:42:18'),
(1098, 20, 3, 23, '2026-02-08 17:42:18'),
(1099, 20, 4, 24, '2026-02-08 17:42:18'),
(1100, 20, 5, 25, '2026-02-08 17:42:18'),
(1101, 20, 6, 26, '2026-02-08 17:42:18'),
(1102, 20, 31, 27, '2026-02-08 17:42:18'),
(1103, 20, 32, 28, '2026-02-08 17:42:18'),
(1104, 20, 33, 29, '2026-02-08 17:42:18'),
(1105, 20, 34, 30, '2026-02-08 17:42:18'),
(1106, 21, 7, 1, '2026-02-08 17:42:18'),
(1107, 21, 8, 2, '2026-02-08 17:42:18'),
(1108, 21, 9, 3, '2026-02-08 17:42:18'),
(1109, 21, 10, 4, '2026-02-08 17:42:18'),
(1110, 21, 11, 5, '2026-02-08 17:42:18'),
(1111, 21, 12, 6, '2026-02-08 17:42:18'),
(1112, 21, 45, 7, '2026-02-08 17:42:18'),
(1113, 21, 46, 8, '2026-02-08 17:42:18'),
(1114, 21, 47, 9, '2026-02-08 17:42:18'),
(1115, 21, 48, 10, '2026-02-08 17:42:18'),
(1116, 21, 49, 11, '2026-02-08 17:42:18'),
(1117, 21, 50, 12, '2026-02-08 17:42:18'),
(1118, 21, 51, 13, '2026-02-08 17:42:18'),
(1119, 21, 52, 14, '2026-02-08 17:42:18'),
(1120, 21, 53, 15, '2026-02-08 17:42:18'),
(1121, 21, 54, 16, '2026-02-08 17:42:18'),
(1122, 21, 55, 17, '2026-02-08 17:42:18'),
(1123, 21, 56, 18, '2026-02-08 17:42:18'),
(1124, 21, 57, 19, '2026-02-08 17:42:18'),
(1125, 21, 58, 20, '2026-02-08 17:42:18'),
(1126, 21, 7, 21, '2026-02-08 17:42:18'),
(1127, 21, 8, 22, '2026-02-08 17:42:18'),
(1128, 21, 9, 23, '2026-02-08 17:42:18'),
(1129, 21, 10, 24, '2026-02-08 17:42:18'),
(1130, 21, 11, 25, '2026-02-08 17:42:18'),
(1131, 21, 12, 26, '2026-02-08 17:42:18'),
(1132, 21, 45, 27, '2026-02-08 17:42:18'),
(1133, 21, 46, 28, '2026-02-08 17:42:18'),
(1134, 21, 47, 29, '2026-02-08 17:42:18'),
(1135, 21, 48, 30, '2026-02-08 17:42:18'),
(1136, 9, 59, 1, '2026-02-08 17:42:18'),
(1137, 9, 60, 2, '2026-02-08 17:42:18'),
(1138, 9, 61, 3, '2026-02-08 17:42:18'),
(1139, 9, 62, 4, '2026-02-08 17:42:18'),
(1140, 9, 63, 5, '2026-02-08 17:42:18'),
(1141, 9, 64, 6, '2026-02-08 17:42:18'),
(1142, 9, 65, 7, '2026-02-08 17:42:18'),
(1143, 9, 66, 8, '2026-02-08 17:42:18'),
(1144, 9, 67, 9, '2026-02-08 17:42:18'),
(1145, 9, 68, 10, '2026-02-08 17:42:18'),
(1146, 9, 69, 11, '2026-02-08 17:42:18'),
(1147, 9, 70, 12, '2026-02-08 17:42:18'),
(1148, 9, 59, 13, '2026-02-08 17:42:18'),
(1149, 9, 60, 14, '2026-02-08 17:42:18'),
(1150, 9, 61, 15, '2026-02-08 17:42:18'),
(1151, 9, 62, 16, '2026-02-08 17:42:18'),
(1152, 9, 63, 17, '2026-02-08 17:42:18'),
(1153, 9, 64, 18, '2026-02-08 17:42:18'),
(1154, 9, 65, 19, '2026-02-08 17:42:18'),
(1155, 9, 66, 20, '2026-02-08 17:42:18'),
(1156, 1, 1, 1, '2026-02-08 17:44:40'),
(1157, 1, 2, 2, '2026-02-08 17:44:40'),
(1158, 1, 3, 3, '2026-02-08 17:44:40'),
(1159, 1, 4, 4, '2026-02-08 17:44:40'),
(1160, 1, 5, 5, '2026-02-08 17:44:40'),
(1161, 1, 6, 6, '2026-02-08 17:44:40'),
(1162, 1, 31, 7, '2026-02-08 17:44:40'),
(1163, 1, 32, 8, '2026-02-08 17:44:40'),
(1164, 1, 33, 9, '2026-02-08 17:44:40'),
(1165, 1, 34, 10, '2026-02-08 17:44:40'),
(1166, 1, 35, 11, '2026-02-08 17:44:40'),
(1167, 1, 36, 12, '2026-02-08 17:44:40'),
(1168, 1, 37, 13, '2026-02-08 17:44:40'),
(1169, 1, 38, 14, '2026-02-08 17:44:40'),
(1170, 1, 39, 15, '2026-02-08 17:44:40'),
(1171, 1, 40, 16, '2026-02-08 17:44:40'),
(1172, 1, 41, 17, '2026-02-08 17:44:40'),
(1173, 1, 42, 18, '2026-02-08 17:44:40'),
(1174, 1, 43, 19, '2026-02-08 17:44:40'),
(1175, 1, 44, 20, '2026-02-08 17:44:40'),
(1176, 2, 1, 1, '2026-02-08 17:44:41'),
(1177, 2, 2, 2, '2026-02-08 17:44:41'),
(1178, 2, 3, 3, '2026-02-08 17:44:41'),
(1179, 2, 4, 4, '2026-02-08 17:44:41'),
(1180, 2, 5, 5, '2026-02-08 17:44:41'),
(1181, 2, 6, 6, '2026-02-08 17:44:41'),
(1182, 2, 31, 7, '2026-02-08 17:44:41'),
(1183, 2, 32, 8, '2026-02-08 17:44:41'),
(1184, 2, 33, 9, '2026-02-08 17:44:41'),
(1185, 2, 34, 10, '2026-02-08 17:44:41'),
(1186, 2, 35, 11, '2026-02-08 17:44:41'),
(1187, 2, 36, 12, '2026-02-08 17:44:41'),
(1188, 2, 37, 13, '2026-02-08 17:44:41'),
(1189, 2, 38, 14, '2026-02-08 17:44:41'),
(1190, 2, 39, 15, '2026-02-08 17:44:41'),
(1191, 2, 40, 16, '2026-02-08 17:44:41'),
(1192, 2, 41, 17, '2026-02-08 17:44:41'),
(1193, 2, 42, 18, '2026-02-08 17:44:41'),
(1194, 2, 43, 19, '2026-02-08 17:44:41'),
(1195, 2, 44, 20, '2026-02-08 17:44:41'),
(1196, 2, 1, 21, '2026-02-08 17:44:41'),
(1197, 2, 2, 22, '2026-02-08 17:44:41'),
(1198, 2, 3, 23, '2026-02-08 17:44:41'),
(1199, 2, 4, 24, '2026-02-08 17:44:41'),
(1200, 2, 5, 25, '2026-02-08 17:44:41'),
(1201, 2, 6, 26, '2026-02-08 17:44:41'),
(1202, 2, 31, 27, '2026-02-08 17:44:41'),
(1203, 2, 32, 28, '2026-02-08 17:44:41'),
(1204, 2, 33, 29, '2026-02-08 17:44:41'),
(1205, 2, 34, 30, '2026-02-08 17:44:41'),
(1206, 3, 1, 1, '2026-02-08 17:44:41'),
(1207, 3, 2, 2, '2026-02-08 17:44:41'),
(1208, 3, 3, 3, '2026-02-08 17:44:41'),
(1209, 3, 4, 4, '2026-02-08 17:44:41'),
(1210, 3, 5, 5, '2026-02-08 17:44:41'),
(1211, 3, 6, 6, '2026-02-08 17:44:41'),
(1212, 3, 31, 7, '2026-02-08 17:44:41'),
(1213, 3, 32, 8, '2026-02-08 17:44:41'),
(1214, 3, 33, 9, '2026-02-08 17:44:41'),
(1215, 3, 34, 10, '2026-02-08 17:44:41'),
(1216, 4, 35, 1, '2026-02-08 17:44:41'),
(1217, 4, 36, 2, '2026-02-08 17:44:41'),
(1218, 4, 37, 3, '2026-02-08 17:44:41'),
(1219, 4, 38, 4, '2026-02-08 17:44:41'),
(1220, 4, 39, 5, '2026-02-08 17:44:41'),
(1221, 4, 40, 6, '2026-02-08 17:44:41'),
(1222, 4, 41, 7, '2026-02-08 17:44:41'),
(1223, 4, 42, 8, '2026-02-08 17:44:41'),
(1224, 4, 43, 9, '2026-02-08 17:44:41'),
(1225, 4, 44, 10, '2026-02-08 17:44:41'),
(1226, 5, 7, 1, '2026-02-08 17:44:41'),
(1227, 5, 8, 2, '2026-02-08 17:44:41'),
(1228, 5, 9, 3, '2026-02-08 17:44:41'),
(1229, 5, 10, 4, '2026-02-08 17:44:41'),
(1230, 5, 11, 5, '2026-02-08 17:44:41'),
(1231, 5, 12, 6, '2026-02-08 17:44:41'),
(1232, 5, 45, 7, '2026-02-08 17:44:41'),
(1233, 5, 46, 8, '2026-02-08 17:44:41'),
(1234, 5, 47, 9, '2026-02-08 17:44:41'),
(1235, 5, 48, 10, '2026-02-08 17:44:41'),
(1236, 5, 49, 11, '2026-02-08 17:44:41'),
(1237, 5, 50, 12, '2026-02-08 17:44:41'),
(1238, 5, 51, 13, '2026-02-08 17:44:41'),
(1239, 5, 52, 14, '2026-02-08 17:44:41'),
(1240, 5, 53, 15, '2026-02-08 17:44:41'),
(1241, 5, 54, 16, '2026-02-08 17:44:41'),
(1242, 5, 55, 17, '2026-02-08 17:44:41'),
(1243, 5, 56, 18, '2026-02-08 17:44:41'),
(1244, 5, 57, 19, '2026-02-08 17:44:41'),
(1245, 5, 58, 20, '2026-02-08 17:44:41'),
(1246, 6, 7, 1, '2026-02-08 17:44:41'),
(1247, 6, 8, 2, '2026-02-08 17:44:41'),
(1248, 6, 9, 3, '2026-02-08 17:44:41'),
(1249, 6, 10, 4, '2026-02-08 17:44:41'),
(1250, 6, 11, 5, '2026-02-08 17:44:41'),
(1251, 6, 12, 6, '2026-02-08 17:44:41'),
(1252, 6, 45, 7, '2026-02-08 17:44:41'),
(1253, 6, 46, 8, '2026-02-08 17:44:41'),
(1254, 6, 47, 9, '2026-02-08 17:44:41'),
(1255, 6, 48, 10, '2026-02-08 17:44:41'),
(1256, 6, 49, 11, '2026-02-08 17:44:41'),
(1257, 6, 50, 12, '2026-02-08 17:44:41'),
(1258, 6, 51, 13, '2026-02-08 17:44:41'),
(1259, 6, 52, 14, '2026-02-08 17:44:41'),
(1260, 6, 53, 15, '2026-02-08 17:44:41'),
(1261, 6, 54, 16, '2026-02-08 17:44:41'),
(1262, 6, 55, 17, '2026-02-08 17:44:41'),
(1263, 6, 56, 18, '2026-02-08 17:44:41'),
(1264, 6, 57, 19, '2026-02-08 17:44:41'),
(1265, 6, 58, 20, '2026-02-08 17:44:41'),
(1266, 6, 7, 21, '2026-02-08 17:44:41'),
(1267, 6, 8, 22, '2026-02-08 17:44:41'),
(1268, 6, 9, 23, '2026-02-08 17:44:41'),
(1269, 6, 10, 24, '2026-02-08 17:44:41'),
(1270, 6, 11, 25, '2026-02-08 17:44:41'),
(1271, 6, 12, 26, '2026-02-08 17:44:41'),
(1272, 6, 45, 27, '2026-02-08 17:44:41'),
(1273, 6, 46, 28, '2026-02-08 17:44:41'),
(1274, 6, 47, 29, '2026-02-08 17:44:41'),
(1275, 6, 48, 30, '2026-02-08 17:44:41'),
(1276, 7, 7, 1, '2026-02-08 17:44:41'),
(1277, 7, 8, 2, '2026-02-08 17:44:41'),
(1278, 7, 9, 3, '2026-02-08 17:44:41'),
(1279, 7, 10, 4, '2026-02-08 17:44:41'),
(1280, 7, 11, 5, '2026-02-08 17:44:41'),
(1281, 7, 12, 6, '2026-02-08 17:44:41'),
(1282, 7, 45, 7, '2026-02-08 17:44:41'),
(1283, 7, 46, 8, '2026-02-08 17:44:41'),
(1284, 7, 47, 9, '2026-02-08 17:44:41'),
(1285, 7, 48, 10, '2026-02-08 17:44:41'),
(1286, 8, 59, 1, '2026-02-08 17:44:41'),
(1287, 8, 60, 2, '2026-02-08 17:44:41'),
(1288, 8, 61, 3, '2026-02-08 17:44:41'),
(1289, 8, 62, 4, '2026-02-08 17:44:41'),
(1290, 8, 63, 5, '2026-02-08 17:44:41'),
(1291, 8, 64, 6, '2026-02-08 17:44:41'),
(1292, 8, 65, 7, '2026-02-08 17:44:41'),
(1293, 8, 66, 8, '2026-02-08 17:44:41'),
(1294, 8, 67, 9, '2026-02-08 17:44:41'),
(1295, 8, 68, 10, '2026-02-08 17:44:41'),
(1296, 8, 69, 11, '2026-02-08 17:44:41'),
(1297, 8, 70, 12, '2026-02-08 17:44:41'),
(1298, 8, 59, 13, '2026-02-08 17:44:41'),
(1299, 8, 60, 14, '2026-02-08 17:44:41'),
(1300, 8, 61, 15, '2026-02-08 17:44:41'),
(1301, 8, 62, 16, '2026-02-08 17:44:41'),
(1302, 8, 63, 17, '2026-02-08 17:44:41'),
(1303, 8, 64, 18, '2026-02-08 17:44:41'),
(1304, 8, 65, 19, '2026-02-08 17:44:41'),
(1305, 8, 66, 20, '2026-02-08 17:44:41'),
(1306, 9, 13, 1, '2026-02-08 17:44:41'),
(1307, 9, 14, 2, '2026-02-08 17:44:41'),
(1308, 9, 15, 3, '2026-02-08 17:44:41'),
(1309, 9, 16, 4, '2026-02-08 17:44:41'),
(1310, 9, 17, 5, '2026-02-08 17:44:41'),
(1311, 9, 71, 6, '2026-02-08 17:44:41'),
(1312, 9, 72, 7, '2026-02-08 17:44:41'),
(1313, 9, 73, 8, '2026-02-08 17:44:41'),
(1314, 9, 74, 9, '2026-02-08 17:44:41'),
(1315, 9, 75, 10, '2026-02-08 17:44:41'),
(1316, 9, 76, 11, '2026-02-08 17:44:41'),
(1317, 9, 77, 12, '2026-02-08 17:44:41'),
(1318, 9, 78, 13, '2026-02-08 17:44:41'),
(1319, 9, 79, 14, '2026-02-08 17:44:41'),
(1320, 9, 80, 15, '2026-02-08 17:44:41'),
(1321, 10, 13, 1, '2026-02-08 17:44:41'),
(1322, 10, 14, 2, '2026-02-08 17:44:41'),
(1323, 10, 15, 3, '2026-02-08 17:44:41'),
(1324, 10, 16, 4, '2026-02-08 17:44:41'),
(1325, 10, 17, 5, '2026-02-08 17:44:41'),
(1326, 10, 71, 6, '2026-02-08 17:44:41'),
(1327, 10, 72, 7, '2026-02-08 17:44:41'),
(1328, 10, 73, 8, '2026-02-08 17:44:41'),
(1329, 10, 74, 9, '2026-02-08 17:44:41'),
(1330, 10, 75, 10, '2026-02-08 17:44:41'),
(1331, 10, 76, 11, '2026-02-08 17:44:41'),
(1332, 10, 77, 12, '2026-02-08 17:44:41'),
(1333, 10, 78, 13, '2026-02-08 17:44:41'),
(1334, 10, 79, 14, '2026-02-08 17:44:41'),
(1335, 10, 80, 15, '2026-02-08 17:44:41'),
(1336, 10, 13, 16, '2026-02-08 17:44:41'),
(1337, 10, 14, 17, '2026-02-08 17:44:41'),
(1338, 10, 15, 18, '2026-02-08 17:44:41'),
(1339, 10, 16, 19, '2026-02-08 17:44:41'),
(1340, 10, 17, 20, '2026-02-08 17:44:41'),
(1341, 10, 71, 21, '2026-02-08 17:44:41'),
(1342, 10, 72, 22, '2026-02-08 17:44:41'),
(1343, 10, 73, 23, '2026-02-08 17:44:41'),
(1344, 10, 74, 24, '2026-02-08 17:44:41'),
(1345, 10, 75, 25, '2026-02-08 17:44:41'),
(1346, 12, 18, 1, '2026-02-08 17:44:41'),
(1347, 12, 19, 2, '2026-02-08 17:44:41'),
(1348, 12, 20, 3, '2026-02-08 17:44:41'),
(1349, 12, 21, 4, '2026-02-08 17:44:41'),
(1350, 12, 22, 5, '2026-02-08 17:44:41'),
(1351, 12, 81, 6, '2026-02-08 17:44:41'),
(1352, 12, 82, 7, '2026-02-08 17:44:41'),
(1353, 12, 83, 8, '2026-02-08 17:44:41'),
(1354, 12, 84, 9, '2026-02-08 17:44:41'),
(1355, 12, 85, 10, '2026-02-08 17:44:41'),
(1356, 12, 86, 11, '2026-02-08 17:44:41'),
(1357, 12, 87, 12, '2026-02-08 17:44:41'),
(1358, 12, 88, 13, '2026-02-08 17:44:41'),
(1359, 12, 89, 14, '2026-02-08 17:44:41'),
(1360, 12, 90, 15, '2026-02-08 17:44:41'),
(1361, 13, 18, 1, '2026-02-08 17:44:41'),
(1362, 13, 19, 2, '2026-02-08 17:44:41'),
(1363, 13, 20, 3, '2026-02-08 17:44:41'),
(1364, 13, 21, 4, '2026-02-08 17:44:41'),
(1365, 13, 22, 5, '2026-02-08 17:44:41'),
(1366, 13, 81, 6, '2026-02-08 17:44:41'),
(1367, 13, 82, 7, '2026-02-08 17:44:41'),
(1368, 13, 83, 8, '2026-02-08 17:44:41'),
(1369, 13, 84, 9, '2026-02-08 17:44:41'),
(1370, 13, 85, 10, '2026-02-08 17:44:41'),
(1371, 13, 86, 11, '2026-02-08 17:44:41'),
(1372, 13, 87, 12, '2026-02-08 17:44:41'),
(1373, 13, 88, 13, '2026-02-08 17:44:41'),
(1374, 13, 89, 14, '2026-02-08 17:44:41'),
(1375, 13, 90, 15, '2026-02-08 17:44:41'),
(1376, 13, 18, 16, '2026-02-08 17:44:41'),
(1377, 13, 19, 17, '2026-02-08 17:44:41'),
(1378, 13, 20, 18, '2026-02-08 17:44:41'),
(1379, 13, 21, 19, '2026-02-08 17:44:41'),
(1380, 13, 22, 20, '2026-02-08 17:44:41'),
(1381, 13, 81, 21, '2026-02-08 17:44:41'),
(1382, 13, 82, 22, '2026-02-08 17:44:41'),
(1383, 13, 83, 23, '2026-02-08 17:44:41'),
(1384, 13, 84, 24, '2026-02-08 17:44:41'),
(1385, 13, 85, 25, '2026-02-08 17:44:41'),
(1386, 14, 18, 1, '2026-02-08 17:44:41'),
(1387, 14, 19, 2, '2026-02-08 17:44:41'),
(1388, 14, 20, 3, '2026-02-08 17:44:41'),
(1389, 14, 21, 4, '2026-02-08 17:44:41'),
(1390, 14, 22, 5, '2026-02-08 17:44:41'),
(1391, 14, 81, 6, '2026-02-08 17:44:41'),
(1392, 14, 82, 7, '2026-02-08 17:44:41'),
(1393, 14, 83, 8, '2026-02-08 17:44:41'),
(1394, 14, 84, 9, '2026-02-08 17:44:41'),
(1395, 14, 85, 10, '2026-02-08 17:44:41'),
(1396, 15, 23, 1, '2026-02-08 17:44:41'),
(1397, 15, 24, 2, '2026-02-08 17:44:41'),
(1398, 15, 25, 3, '2026-02-08 17:44:41'),
(1399, 15, 26, 4, '2026-02-08 17:44:41'),
(1400, 15, 27, 5, '2026-02-08 17:44:41'),
(1401, 15, 91, 6, '2026-02-08 17:44:41'),
(1402, 15, 92, 7, '2026-02-08 17:44:41'),
(1403, 15, 93, 8, '2026-02-08 17:44:41'),
(1404, 15, 94, 9, '2026-02-08 17:44:41'),
(1405, 15, 95, 10, '2026-02-08 17:44:41'),
(1406, 15, 96, 11, '2026-02-08 17:44:41'),
(1407, 15, 97, 12, '2026-02-08 17:44:41'),
(1408, 15, 98, 13, '2026-02-08 17:44:41'),
(1409, 15, 99, 14, '2026-02-08 17:44:41'),
(1410, 15, 100, 15, '2026-02-08 17:44:41'),
(1411, 16, 23, 1, '2026-02-08 17:44:41'),
(1412, 16, 24, 2, '2026-02-08 17:44:41'),
(1413, 16, 25, 3, '2026-02-08 17:44:41'),
(1414, 16, 26, 4, '2026-02-08 17:44:41'),
(1415, 16, 27, 5, '2026-02-08 17:44:41'),
(1416, 16, 91, 6, '2026-02-08 17:44:41'),
(1417, 16, 92, 7, '2026-02-08 17:44:41'),
(1418, 16, 93, 8, '2026-02-08 17:44:41'),
(1419, 16, 94, 9, '2026-02-08 17:44:41'),
(1420, 16, 95, 10, '2026-02-08 17:44:41'),
(1421, 17, 91, 1, '2026-02-08 17:44:41'),
(1422, 17, 92, 2, '2026-02-08 17:44:41'),
(1423, 17, 93, 3, '2026-02-08 17:44:41'),
(1424, 17, 94, 4, '2026-02-08 17:44:41'),
(1425, 17, 95, 5, '2026-02-08 17:44:41'),
(1426, 17, 96, 6, '2026-02-08 17:44:41'),
(1427, 17, 97, 7, '2026-02-08 17:44:41'),
(1428, 17, 98, 8, '2026-02-08 17:44:41'),
(1429, 17, 99, 9, '2026-02-08 17:44:41'),
(1430, 17, 100, 10, '2026-02-08 17:44:41'),
(1431, 17, 91, 11, '2026-02-08 17:44:41'),
(1432, 17, 92, 12, '2026-02-08 17:44:41'),
(1433, 17, 93, 13, '2026-02-08 17:44:41'),
(1434, 17, 94, 14, '2026-02-08 17:44:41'),
(1435, 17, 95, 15, '2026-02-08 17:44:41'),
(1436, 18, 28, 1, '2026-02-08 17:44:42'),
(1437, 18, 29, 2, '2026-02-08 17:44:42'),
(1438, 18, 30, 3, '2026-02-08 17:44:42'),
(1439, 18, 101, 4, '2026-02-08 17:44:42'),
(1440, 18, 102, 5, '2026-02-08 17:44:42'),
(1441, 18, 103, 6, '2026-02-08 17:44:42'),
(1442, 18, 104, 7, '2026-02-08 17:44:42'),
(1443, 18, 105, 8, '2026-02-08 17:44:42'),
(1444, 18, 106, 9, '2026-02-08 17:44:42'),
(1445, 18, 107, 10, '2026-02-08 17:44:42'),
(1446, 18, 108, 11, '2026-02-08 17:44:42'),
(1447, 18, 109, 12, '2026-02-08 17:44:42'),
(1448, 18, 110, 13, '2026-02-08 17:44:42'),
(1449, 18, 28, 14, '2026-02-08 17:44:42'),
(1450, 18, 29, 15, '2026-02-08 17:44:42'),
(1451, 18, 30, 16, '2026-02-08 17:44:42'),
(1452, 18, 101, 17, '2026-02-08 17:44:42'),
(1453, 18, 102, 18, '2026-02-08 17:44:42'),
(1454, 18, 103, 19, '2026-02-08 17:44:42'),
(1455, 18, 104, 20, '2026-02-08 17:44:42'),
(1456, 20, 1, 1, '2026-02-08 17:44:42'),
(1457, 20, 2, 2, '2026-02-08 17:44:42'),
(1458, 20, 3, 3, '2026-02-08 17:44:42'),
(1459, 20, 4, 4, '2026-02-08 17:44:42'),
(1460, 20, 5, 5, '2026-02-08 17:44:42'),
(1461, 20, 6, 6, '2026-02-08 17:44:42'),
(1462, 20, 31, 7, '2026-02-08 17:44:42'),
(1463, 20, 32, 8, '2026-02-08 17:44:42'),
(1464, 20, 33, 9, '2026-02-08 17:44:42'),
(1465, 20, 34, 10, '2026-02-08 17:44:42'),
(1466, 20, 35, 11, '2026-02-08 17:44:42'),
(1467, 20, 36, 12, '2026-02-08 17:44:42'),
(1468, 20, 37, 13, '2026-02-08 17:44:42'),
(1469, 20, 38, 14, '2026-02-08 17:44:42'),
(1470, 20, 39, 15, '2026-02-08 17:44:42'),
(1471, 20, 40, 16, '2026-02-08 17:44:42'),
(1472, 20, 41, 17, '2026-02-08 17:44:42'),
(1473, 20, 42, 18, '2026-02-08 17:44:42'),
(1474, 20, 43, 19, '2026-02-08 17:44:42'),
(1475, 20, 44, 20, '2026-02-08 17:44:42'),
(1476, 20, 1, 21, '2026-02-08 17:44:42'),
(1477, 20, 2, 22, '2026-02-08 17:44:42'),
(1478, 20, 3, 23, '2026-02-08 17:44:42'),
(1479, 20, 4, 24, '2026-02-08 17:44:42'),
(1480, 20, 5, 25, '2026-02-08 17:44:42'),
(1481, 20, 6, 26, '2026-02-08 17:44:42'),
(1482, 20, 31, 27, '2026-02-08 17:44:42'),
(1483, 20, 32, 28, '2026-02-08 17:44:42'),
(1484, 20, 33, 29, '2026-02-08 17:44:42'),
(1485, 20, 34, 30, '2026-02-08 17:44:42'),
(1486, 21, 7, 1, '2026-02-08 17:44:42'),
(1487, 21, 8, 2, '2026-02-08 17:44:42'),
(1488, 21, 9, 3, '2026-02-08 17:44:42'),
(1489, 21, 10, 4, '2026-02-08 17:44:42'),
(1490, 21, 11, 5, '2026-02-08 17:44:42'),
(1491, 21, 12, 6, '2026-02-08 17:44:42'),
(1492, 21, 45, 7, '2026-02-08 17:44:42'),
(1493, 21, 46, 8, '2026-02-08 17:44:42'),
(1494, 21, 47, 9, '2026-02-08 17:44:42'),
(1495, 21, 48, 10, '2026-02-08 17:44:42'),
(1496, 21, 49, 11, '2026-02-08 17:44:42'),
(1497, 21, 50, 12, '2026-02-08 17:44:42'),
(1498, 21, 51, 13, '2026-02-08 17:44:42'),
(1499, 21, 52, 14, '2026-02-08 17:44:42'),
(1500, 21, 53, 15, '2026-02-08 17:44:42'),
(1501, 21, 54, 16, '2026-02-08 17:44:42'),
(1502, 21, 55, 17, '2026-02-08 17:44:42'),
(1503, 21, 56, 18, '2026-02-08 17:44:42'),
(1504, 21, 57, 19, '2026-02-08 17:44:42'),
(1505, 21, 58, 20, '2026-02-08 17:44:42'),
(1506, 21, 7, 21, '2026-02-08 17:44:42'),
(1507, 21, 8, 22, '2026-02-08 17:44:42'),
(1508, 21, 9, 23, '2026-02-08 17:44:42'),
(1509, 21, 10, 24, '2026-02-08 17:44:42'),
(1510, 21, 11, 25, '2026-02-08 17:44:42'),
(1511, 21, 12, 26, '2026-02-08 17:44:42'),
(1512, 21, 45, 27, '2026-02-08 17:44:42'),
(1513, 21, 46, 28, '2026-02-08 17:44:42'),
(1514, 21, 47, 29, '2026-02-08 17:44:42'),
(1515, 21, 48, 30, '2026-02-08 17:44:42'),
(1516, 9, 59, 1, '2026-02-08 17:44:42'),
(1517, 9, 60, 2, '2026-02-08 17:44:42'),
(1518, 9, 61, 3, '2026-02-08 17:44:42'),
(1519, 9, 62, 4, '2026-02-08 17:44:42'),
(1520, 9, 63, 5, '2026-02-08 17:44:42'),
(1521, 9, 64, 6, '2026-02-08 17:44:42'),
(1522, 9, 65, 7, '2026-02-08 17:44:42'),
(1523, 9, 66, 8, '2026-02-08 17:44:42'),
(1524, 9, 67, 9, '2026-02-08 17:44:42'),
(1525, 9, 68, 10, '2026-02-08 17:44:42'),
(1526, 9, 69, 11, '2026-02-08 17:44:42'),
(1527, 9, 70, 12, '2026-02-08 17:44:42'),
(1528, 9, 59, 13, '2026-02-08 17:44:42'),
(1529, 9, 60, 14, '2026-02-08 17:44:42'),
(1530, 9, 61, 15, '2026-02-08 17:44:42'),
(1531, 9, 62, 16, '2026-02-08 17:44:42'),
(1532, 9, 63, 17, '2026-02-08 17:44:42'),
(1533, 9, 64, 18, '2026-02-08 17:44:42'),
(1534, 9, 65, 19, '2026-02-08 17:44:42'),
(1535, 9, 66, 20, '2026-02-08 17:44:42'),
(1536, 10, 28, 1, '2026-02-08 17:44:42'),
(1537, 10, 29, 2, '2026-02-08 17:44:42'),
(1538, 10, 30, 3, '2026-02-08 17:44:42'),
(1539, 10, 101, 4, '2026-02-08 17:44:42'),
(1540, 10, 102, 5, '2026-02-08 17:44:42'),
(1541, 10, 103, 6, '2026-02-08 17:44:42'),
(1542, 10, 104, 7, '2026-02-08 17:44:42'),
(1543, 10, 105, 8, '2026-02-08 17:44:42'),
(1544, 10, 106, 9, '2026-02-08 17:44:42'),
(1545, 10, 107, 10, '2026-02-08 17:44:42'),
(1546, 10, 108, 11, '2026-02-08 17:44:42'),
(1547, 10, 109, 12, '2026-02-08 17:44:42'),
(1548, 10, 110, 13, '2026-02-08 17:44:42'),
(1549, 10, 28, 14, '2026-02-08 17:44:42'),
(1550, 10, 29, 15, '2026-02-08 17:44:42'),
(1551, 10, 30, 16, '2026-02-08 17:44:42'),
(1552, 10, 101, 17, '2026-02-08 17:44:42'),
(1553, 10, 102, 18, '2026-02-08 17:44:42'),
(1554, 10, 103, 19, '2026-02-08 17:44:42'),
(1555, 10, 104, 20, '2026-02-08 17:44:42');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `result_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `correct_answers` int(11) DEFAULT 0,
  `wrong_answers` int(11) DEFAULT 0,
  `unanswered` int(11) DEFAULT 0,
  `total_points_earned` decimal(10,2) DEFAULT 0.00,
  `total_points_possible` decimal(10,2) NOT NULL,
  `percentage_score` decimal(5,2) DEFAULT 0.00,
  `letter_grade` varchar(5) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT NULL,
  `pass_status` enum('Pass','Fail') DEFAULT 'Fail',
  `exam_started_at` timestamp NULL DEFAULT NULL,
  `exam_submitted_at` timestamp NULL DEFAULT NULL,
  `time_taken_minutes` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `faculty_id` int(11) NOT NULL,
  `faculty_code` varchar(20) NOT NULL,
  `faculty_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faculties`
--

INSERT INTO `faculties` (`faculty_id`, `faculty_code`, `faculty_name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'FHS', 'Faculty of Health Sciences', 'Health and Medical Sciences Programs', 1, '2026-02-06 11:10:18', '2026-02-06 11:10:18');

-- --------------------------------------------------------

--
-- Table structure for table `grading_config`
--

CREATE TABLE `grading_config` (
  `config_id` int(11) NOT NULL,
  `grade_letter` varchar(5) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `gpa_value` decimal(3,2) NOT NULL,
  `status_label` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grading_config`
--

INSERT INTO `grading_config` (`config_id`, `grade_letter`, `min_percentage`, `max_percentage`, `gpa_value`, `status_label`, `display_order`, `is_active`, `updated_at`) VALUES
(1, 'A+', 90.00, 100.00, 4.00, 'Excellent', 1, 1, '2026-02-06 11:10:22'),
(2, 'A', 85.00, 89.99, 3.75, 'Excellent', 2, 1, '2026-02-06 11:10:22'),
(3, 'A-', 80.00, 84.99, 3.50, 'Excellent', 3, 1, '2026-02-06 11:10:22'),
(4, 'B+', 75.00, 79.99, 3.00, 'Good', 4, 1, '2026-02-06 11:10:22'),
(5, 'B', 70.00, 74.99, 2.75, 'Good', 5, 1, '2026-02-06 11:10:22'),
(6, 'B-', 65.00, 69.99, 2.50, 'Good', 6, 1, '2026-02-06 11:10:22'),
(7, 'C+', 60.00, 64.99, 2.00, 'Satisfactory', 7, 1, '2026-02-06 11:10:22'),
(8, 'C', 55.00, 59.99, 1.75, 'Satisfactory', 8, 1, '2026-02-06 11:10:22'),
(9, 'C-', 50.00, 54.99, 1.50, 'Satisfactory', 9, 1, '2026-02-06 11:10:22'),
(10, 'D', 45.00, 49.99, 1.00, 'Pass', 10, 1, '2026-02-06 11:10:22'),
(11, 'F', 0.00, 44.99, 0.00, 'Fail', 11, 1, '2026-02-06 11:10:22'),
(12, 'A+', 90.00, 100.00, 4.00, 'Excellent', 1, 1, '2026-02-08 16:53:47'),
(13, 'A', 85.00, 89.99, 3.75, 'Excellent', 2, 1, '2026-02-08 16:53:47'),
(14, 'A-', 80.00, 84.99, 3.50, 'Excellent', 3, 1, '2026-02-08 16:53:47'),
(15, 'B+', 75.00, 79.99, 3.00, 'Good', 4, 1, '2026-02-08 16:53:47'),
(16, 'B', 70.00, 74.99, 2.75, 'Good', 5, 1, '2026-02-08 16:53:47'),
(17, 'B-', 65.00, 69.99, 2.50, 'Good', 6, 1, '2026-02-08 16:53:47'),
(18, 'C+', 60.00, 64.99, 2.00, 'Satisfactory', 7, 1, '2026-02-08 16:53:47'),
(19, 'C', 55.00, 59.99, 1.75, 'Satisfactory', 8, 1, '2026-02-08 16:53:47'),
(20, 'C-', 50.00, 54.99, 1.50, 'Satisfactory', 9, 1, '2026-02-08 16:53:47'),
(21, 'D', 45.00, 49.99, 1.00, 'Pass', 10, 1, '2026-02-08 16:53:47'),
(22, 'F', 0.00, 44.99, 0.00, 'Fail', 11, 1, '2026-02-08 16:53:47'),
(23, 'A+', 90.00, 100.00, 4.00, 'Excellent', 1, 1, '2026-02-08 17:34:13'),
(24, 'A', 85.00, 89.99, 3.75, 'Excellent', 2, 1, '2026-02-08 17:34:13'),
(25, 'A-', 80.00, 84.99, 3.50, 'Excellent', 3, 1, '2026-02-08 17:34:13'),
(26, 'B+', 75.00, 79.99, 3.00, 'Good', 4, 1, '2026-02-08 17:34:13'),
(27, 'B', 70.00, 74.99, 2.75, 'Good', 5, 1, '2026-02-08 17:34:13'),
(28, 'B-', 65.00, 69.99, 2.50, 'Good', 6, 1, '2026-02-08 17:34:13'),
(29, 'C+', 60.00, 64.99, 2.00, 'Satisfactory', 7, 1, '2026-02-08 17:34:13'),
(30, 'C', 55.00, 59.99, 1.75, 'Satisfactory', 8, 1, '2026-02-08 17:34:13'),
(31, 'C-', 50.00, 54.99, 1.50, 'Satisfactory', 9, 1, '2026-02-08 17:34:13'),
(32, 'D', 45.00, 49.99, 1.00, 'Pass', 10, 1, '2026-02-08 17:34:13'),
(33, 'F', 0.00, 44.99, 0.00, 'Fail', 11, 1, '2026-02-08 17:34:13'),
(34, 'A+', 90.00, 100.00, 4.00, 'Excellent', 1, 1, '2026-02-08 17:34:17'),
(35, 'A', 85.00, 89.99, 3.75, 'Excellent', 2, 1, '2026-02-08 17:34:17'),
(36, 'A-', 80.00, 84.99, 3.50, 'Excellent', 3, 1, '2026-02-08 17:34:17'),
(37, 'B+', 75.00, 79.99, 3.00, 'Good', 4, 1, '2026-02-08 17:34:17'),
(38, 'B', 70.00, 74.99, 2.75, 'Good', 5, 1, '2026-02-08 17:34:17'),
(39, 'B-', 65.00, 69.99, 2.50, 'Good', 6, 1, '2026-02-08 17:34:17'),
(40, 'C+', 60.00, 64.99, 2.00, 'Satisfactory', 7, 1, '2026-02-08 17:34:17'),
(41, 'C', 55.00, 59.99, 1.75, 'Satisfactory', 8, 1, '2026-02-08 17:34:17'),
(42, 'C-', 50.00, 54.99, 1.50, 'Satisfactory', 9, 1, '2026-02-08 17:34:17'),
(43, 'D', 45.00, 49.99, 1.00, 'Pass', 10, 1, '2026-02-08 17:34:17'),
(44, 'F', 0.00, 44.99, 0.00, 'Fail', 11, 1, '2026-02-08 17:34:17'),
(45, 'A+', 90.00, 100.00, 4.00, 'Excellent', 1, 1, '2026-02-08 17:37:32'),
(46, 'A', 85.00, 89.99, 3.75, 'Excellent', 2, 1, '2026-02-08 17:37:32'),
(47, 'A-', 80.00, 84.99, 3.50, 'Excellent', 3, 1, '2026-02-08 17:37:32'),
(48, 'B+', 75.00, 79.99, 3.00, 'Good', 4, 1, '2026-02-08 17:37:32'),
(49, 'B', 70.00, 74.99, 2.75, 'Good', 5, 1, '2026-02-08 17:37:32'),
(50, 'B-', 65.00, 69.99, 2.50, 'Good', 6, 1, '2026-02-08 17:37:32'),
(51, 'C+', 60.00, 64.99, 2.00, 'Satisfactory', 7, 1, '2026-02-08 17:37:32'),
(52, 'C', 55.00, 59.99, 1.75, 'Satisfactory', 8, 1, '2026-02-08 17:37:32'),
(53, 'C-', 50.00, 54.99, 1.50, 'Satisfactory', 9, 1, '2026-02-08 17:37:32'),
(54, 'D', 45.00, 49.99, 1.00, 'Pass', 10, 1, '2026-02-08 17:37:32'),
(55, 'F', 0.00, 44.99, 0.00, 'Fail', 11, 1, '2026-02-08 17:37:32'),
(56, 'A+', 90.00, 100.00, 4.00, 'Excellent', 1, 1, '2026-02-08 17:42:14'),
(57, 'A', 85.00, 89.99, 3.75, 'Excellent', 2, 1, '2026-02-08 17:42:14'),
(58, 'A-', 80.00, 84.99, 3.50, 'Excellent', 3, 1, '2026-02-08 17:42:14'),
(59, 'B+', 75.00, 79.99, 3.00, 'Good', 4, 1, '2026-02-08 17:42:14'),
(60, 'B', 70.00, 74.99, 2.75, 'Good', 5, 1, '2026-02-08 17:42:14'),
(61, 'B-', 65.00, 69.99, 2.50, 'Good', 6, 1, '2026-02-08 17:42:14'),
(62, 'C+', 60.00, 64.99, 2.00, 'Satisfactory', 7, 1, '2026-02-08 17:42:14'),
(63, 'C', 55.00, 59.99, 1.75, 'Satisfactory', 8, 1, '2026-02-08 17:42:14'),
(64, 'C-', 50.00, 54.99, 1.50, 'Satisfactory', 9, 1, '2026-02-08 17:42:14'),
(65, 'D', 45.00, 49.99, 1.00, 'Pass', 10, 1, '2026-02-08 17:42:14'),
(66, 'F', 0.00, 44.99, 0.00, 'Fail', 11, 1, '2026-02-08 17:42:14'),
(67, 'A+', 90.00, 100.00, 4.00, 'Excellent', 1, 1, '2026-02-08 17:44:39'),
(68, 'A', 85.00, 89.99, 3.75, 'Excellent', 2, 1, '2026-02-08 17:44:39'),
(69, 'A-', 80.00, 84.99, 3.50, 'Excellent', 3, 1, '2026-02-08 17:44:39'),
(70, 'B+', 75.00, 79.99, 3.00, 'Good', 4, 1, '2026-02-08 17:44:39'),
(71, 'B', 70.00, 74.99, 2.75, 'Good', 5, 1, '2026-02-08 17:44:39'),
(72, 'B-', 65.00, 69.99, 2.50, 'Good', 6, 1, '2026-02-08 17:44:39'),
(73, 'C+', 60.00, 64.99, 2.00, 'Satisfactory', 7, 1, '2026-02-08 17:44:39'),
(74, 'C', 55.00, 59.99, 1.75, 'Satisfactory', 8, 1, '2026-02-08 17:44:39'),
(75, 'C-', 50.00, 54.99, 1.50, 'Satisfactory', 9, 1, '2026-02-08 17:44:39'),
(76, 'D', 45.00, 49.99, 1.00, 'Pass', 10, 1, '2026-02-08 17:44:39'),
(77, 'F', 0.00, 44.99, 0.00, 'Fail', 11, 1, '2026-02-08 17:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `instructor_id` int(11) NOT NULL,
  `instructor_code` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`instructor_id`, `instructor_code`, `username`, `password`, `full_name`, `email`, `phone`, `department_id`, `gender`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'INST001', 'abebe.t', '$2y$10$h3k9C8VLGVPaPtTevIkHjOMJODn./D8eZHm.na30GCk2jEfo954iC', 'Dr. Abebe Tadesse', 'abebe.t@dmu.edu.et', '+251911234567', 1, 'Male', 1, NULL, '2026-02-06 11:10:21', '2026-02-06 15:55:39'),
(2, 'INST002', 'marta.g', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sr. Marta Gebre', 'marta.g@dmu.edu.et', '+251911234568', 1, 'Female', 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(3, 'INST003', 'sara.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sara Mulugeta', 'sara.m@dmu.edu.et', '+251911234569', 2, 'Female', 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(4, 'INST004', 'daniel.h', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Daniel Hailu', 'daniel.h@dmu.edu.et', '+251911234570', 3, 'Male', 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(5, 'INST005', 'helen.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Helen Tesfaye', 'helen.t@dmu.edu.et', '+251911234571', 4, 'Female', 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(6, 'INST006', 'yohannes.b', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Yohannes Bekele', 'yohannes.b@dmu.edu.et', '+251911234572', 5, 'Male', 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_courses`
--

CREATE TABLE `instructor_courses` (
  `assignment_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instructor_courses`
--

INSERT INTO `instructor_courses` (`assignment_id`, `instructor_id`, `course_id`, `assigned_at`) VALUES
(1, 1, 1, '2026-02-06 11:10:21'),
(2, 1, 3, '2026-02-06 11:10:21'),
(4, 3, 4, '2026-02-06 11:10:21'),
(5, 3, 5, '2026-02-06 11:10:21'),
(6, 4, 6, '2026-02-06 11:10:21'),
(7, 4, 7, '2026-02-06 11:10:21'),
(8, 5, 8, '2026-02-06 11:10:21'),
(9, 5, 9, '2026-02-06 11:10:21'),
(10, 6, 10, '2026-02-06 11:10:21'),
(11, 6, 11, '2026-02-06 11:10:21'),
(15, 2, 2, '2026-02-06 11:13:02');

-- --------------------------------------------------------

--
-- Table structure for table `practice_questions`
--

CREATE TABLE `practice_questions` (
  `practice_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `question_type` enum('multiple_choice','true_false') DEFAULT 'multiple_choice',
  `question_text` text NOT NULL,
  `option_a` varchar(500) DEFAULT NULL,
  `option_b` varchar(500) DEFAULT NULL,
  `option_c` varchar(500) DEFAULT NULL,
  `option_d` varchar(500) DEFAULT NULL,
  `correct_answer` enum('A','B','C','D','True','False') NOT NULL,
  `difficulty_level` enum('Easy','Medium','Hard') DEFAULT 'Medium',
  `explanation` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `practice_questions`
--

INSERT INTO `practice_questions` (`practice_id`, `course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty_level`, `explanation`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 1, 1, 'multiple_choice', 'Which vital sign is measured in beats per minute?', 'Temperature', 'Blood Pressure', 'Pulse', 'Respiratory Rate', 'C', 'Easy', 'Pulse is measured in beats per minute (bpm).', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(4, 1, 9, 'multiple_choice', 'How long should hands be scrubbed during surgical hand washing?', '10 seconds', '30 seconds', '2-6 minutes', '10 minutes', 'C', 'Medium', 'Surgical hand washing requires 2-6 minutes of thorough scrubbing.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(5, 1, 9, 'true_false', 'Gloves can replace hand washing in patient care.', 'True', 'False', NULL, NULL, 'False', 'Hard', 'Gloves are an additional barrier but do not replace proper hand hygiene.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:36:50'),
(6, 1, 1, 'true_false', 'Nurses can diagnose medical conditions independently.', 'True', 'False', NULL, NULL, 'False', 'Medium', 'Nurses make nursing diagnoses, but medical diagnoses are made by physicians.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(7, 1, 9, 'true_false', 'Standard precautions should be used for all patients.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Standard precautions are infection control practices used for all patients.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(8, 2, 2, 'multiple_choice', 'Which bone protects the brain?', 'Femur', 'Skull', 'Ribs', 'Vertebrae', 'B', 'Easy', 'The skull (cranium) protects the brain from injury.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(9, 2, 3, 'multiple_choice', 'What is the largest artery in the human body?', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 'Medium', 'The aorta is the largest artery, carrying oxygenated blood from the heart.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(10, 2, 2, 'multiple_choice', 'How many pairs of ribs does a human have?', '10', '12', '14', '16', 'B', 'Medium', 'Humans have 12 pairs of ribs (24 ribs total).', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(11, 2, 2, 'true_false', 'The femur is the longest bone in the human body.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'The femur (thigh bone) is the longest and strongest bone in the body.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(12, 2, 3, 'true_false', 'The heart has three chambers.', 'True', 'False', NULL, NULL, 'False', 'Easy', 'The heart has four chambers: two atria and two ventricles.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(13, 2, 3, 'true_false', 'Arteries carry blood away from the heart.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Arteries carry oxygenated blood away from the heart to body tissues.', 1, 1, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(14, 4, 5, 'multiple_choice', 'What is the average length of a menstrual cycle?', '21 days', '28 days', '35 days', '40 days', 'B', 'Easy', 'The average menstrual cycle is 28 days, though 21-35 days is considered normal.', 1, 3, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(15, 4, 5, 'multiple_choice', 'At what week does the second trimester begin?', 'Week 10', 'Week 13', 'Week 16', 'Week 20', 'B', 'Medium', 'The second trimester begins at week 13 and ends at week 27.', 1, 3, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(16, 4, 5, 'multiple_choice', 'What is the normal fetal heart rate range?', '60-100 bpm', '110-160 bpm', '180-200 bpm', '200-220 bpm', 'B', 'Medium', 'Normal fetal heart rate is 110-160 beats per minute.', 1, 3, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(17, 4, 5, 'true_false', 'Morning sickness only occurs in the morning.', 'True', 'False', NULL, NULL, 'False', 'Easy', 'Despite its name, morning sickness can occur at any time of day.', 1, 3, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(18, 4, 5, 'true_false', 'Pregnant women should avoid all exercise.', 'True', 'False', NULL, NULL, 'False', 'Medium', 'Moderate exercise is beneficial during pregnancy unless contraindicated.', 1, 3, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(19, 4, 5, 'true_false', 'The placenta provides oxygen and nutrients to the fetus.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'The placenta transfers oxygen and nutrients from mother to fetus.', 1, 3, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(20, 6, 6, 'multiple_choice', 'What does CDC stand for?', 'Center for Disease Control', 'Centers for Disease Control and Prevention', 'Central Disease Center', 'Clinical Disease Control', 'B', 'Easy', 'CDC stands for Centers for Disease Control and Prevention.', 1, 4, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(21, 6, 6, 'multiple_choice', 'Which disease was eradicated globally through vaccination?', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 'Medium', 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 1, 4, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(22, 6, 6, 'multiple_choice', 'What is the primary mode of HIV transmission?', 'Mosquito bites', 'Sharing food', 'Blood and body fluids', 'Casual contact', 'C', 'Medium', 'HIV is transmitted through blood, sexual contact, and from mother to child.', 1, 4, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(23, 6, 6, 'true_false', 'Epidemiology is the study of disease patterns in populations.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Epidemiology studies the distribution and determinants of health conditions in populations.', 1, 4, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(24, 6, 6, 'true_false', 'Antibiotics are effective against all types of infections.', 'True', 'False', NULL, NULL, 'False', 'Medium', 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1, 4, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(25, 6, 6, 'true_false', 'Clean water is essential for preventing waterborne diseases.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Access to clean water prevents diseases like cholera, typhoid, and dysentery.', 1, 4, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(26, 8, 8, 'multiple_choice', 'What does ASA stand for in anesthesia?', 'American Society of Anesthesiologists', 'Anesthesia Safety Association', 'Advanced Surgical Anesthesia', 'Anesthetic Standard Assessment', 'A', 'Medium', 'ASA is the American Society of Anesthesiologists classification system.', 1, 5, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(27, 8, 8, 'multiple_choice', 'Which drug reverses opioid effects?', 'Atropine', 'Naloxone', 'Epinephrine', 'Dopamine', 'B', 'Hard', 'Naloxone (Narcan) is an opioid antagonist that reverses opioid effects.', 1, 5, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(28, 8, 9, 'multiple_choice', 'What is the normal oxygen saturation level?', '70-80%', '85-90%', '95-100%', '100-110%', 'C', 'Easy', 'Normal oxygen saturation (SpO2) is 95-100%.', 1, 5, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(29, 8, 8, 'true_false', 'Spinal anesthesia is a type of regional anesthesia.', 'True', 'False', NULL, NULL, 'True', 'Medium', 'Spinal anesthesia blocks sensation in a specific region of the body.', 1, 5, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(30, 8, 9, 'true_false', 'Patients should fast before general anesthesia.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Fasting reduces the risk of aspiration during anesthesia.', 1, 5, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(31, 8, 8, 'true_false', 'Local anesthesia causes loss of consciousness.', 'True', 'False', NULL, NULL, 'False', 'Easy', 'Local anesthesia only numbs a specific area without affecting consciousness.', 1, 5, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(32, 10, 7, 'multiple_choice', 'What is the normal range for blood glucose (fasting)?', '50-70 mg/dL', '70-100 mg/dL', '120-140 mg/dL', '150-180 mg/dL', 'B', 'Medium', 'Normal fasting blood glucose is 70-100 mg/dL.', 1, 6, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(33, 10, 7, 'multiple_choice', 'Which blood cell fights infection?', 'Red blood cells', 'White blood cells', 'Platelets', 'Plasma cells', 'B', 'Easy', 'White blood cells (leukocytes) are part of the immune system.', 1, 6, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(34, 10, 7, 'multiple_choice', 'What does CBC stand for?', 'Complete Blood Count', 'Central Blood Center', 'Clinical Blood Chemistry', 'Cellular Blood Composition', 'A', 'Easy', 'CBC is a Complete Blood Count test that measures blood components.', 1, 6, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(35, 10, 7, 'true_false', 'Hemoglobin carries oxygen in the blood.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Hemoglobin in red blood cells binds and transports oxygen.', 1, 6, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(36, 10, 7, 'true_false', 'Blood type AB is the universal recipient.', 'True', 'False', NULL, NULL, 'True', 'Medium', 'People with AB blood type can receive blood from any blood type.', 1, 6, '2026-02-06 11:33:44', '2026-02-06 11:33:44'),
(37, 10, 7, 'true_false', 'Platelets are responsible for blood clotting.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Platelets (thrombocytes) play a crucial role in blood clotting.', 1, 6, '2026-02-06 11:33:44', '2026-02-06 11:33:44');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('multiple_choice','true_false') NOT NULL DEFAULT 'multiple_choice',
  `option_a` varchar(500) NOT NULL,
  `option_b` varchar(500) NOT NULL,
  `option_c` varchar(500) DEFAULT NULL,
  `option_d` varchar(500) DEFAULT NULL,
  `correct_answer` enum('A','B','C','D','True','False') NOT NULL,
  `point_value` int(11) DEFAULT 1,
  `explanation` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `course_id`, `topic_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'What is the primary goal of nursing care?', 'multiple_choice', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1, '2026-02-06 11:10:22', '2026-02-06 11:10:22'),
(2, 1, 1, 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'multiple_choice', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1, '2026-02-06 11:10:22', '2026-02-06 11:10:22'),
(3, 1, 9, 'What is the correct order for hand hygiene?', 'multiple_choice', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1, '2026-02-06 11:10:22', '2026-02-06 11:10:22'),
(4, 1, 9, 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1, '2026-02-06 11:10:22', '2026-02-06 11:10:22'),
(5, 1, 9, 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1, '2026-02-06 11:10:22', '2026-02-06 11:10:22'),
(6, 1, 1, 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1, '2026-02-06 11:10:22', '2026-02-06 11:10:22'),
(8, 2, 2, 'How many chambers does the human heart have?', 'multiple_choice', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(9, 2, 3, 'What is the normal resting heart rate for adults?', 'multiple_choice', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(10, 2, 2, 'The human body has 206 bones in the adult skeleton.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(11, 2, 3, 'The liver is located in the left upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(12, 2, 2, 'The skin is the largest organ in the human body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(13, 4, 5, 'What is the normal duration of pregnancy?', 'multiple_choice', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(14, 4, 5, 'Which trimester is considered the most critical for fetal development?', 'multiple_choice', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(15, 4, 5, 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(16, 4, 5, 'Fetal movements should be felt by the mother starting from the first trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(17, 4, 5, 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(18, 6, 6, 'What is the primary focus of public health?', 'multiple_choice', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(19, 6, 6, 'Which of the following is a communicable disease?', 'multiple_choice', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(20, 6, 6, 'Vaccination is one of the most cost-effective public health interventions.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(21, 6, 6, 'Antibiotics are effective against viral infections like the common cold.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(22, 6, 6, 'Hand washing is one of the most effective ways to prevent disease transmission.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(23, 8, 8, 'What is the primary purpose of anesthesia?', 'multiple_choice', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(24, 8, 8, 'Which type of anesthesia affects the entire body?', 'multiple_choice', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(25, 8, 8, 'General anesthesia causes complete loss of consciousness.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(26, 8, 8, 'Local anesthesia affects the entire body.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(27, 8, 9, 'Oxygen saturation must be continuously monitored during anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(28, 10, 7, 'What is the normal pH range of human blood?', 'multiple_choice', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(29, 10, 7, 'Which blood type is considered the universal donor?', 'multiple_choice', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(30, 10, 7, 'Blood type O negative is considered the universal donor.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(31, 10, 7, 'Hemoglobin is found in white blood cells.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(32, 10, 7, 'Red blood cells are responsible for transporting oxygen throughout the body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6, '2026-02-06 11:10:23', '2026-02-06 11:10:23'),
(34, 1, 1, 'What is the primary goal of nursing care?', 'multiple_choice', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1, '2026-02-08 16:53:47', '2026-02-08 16:53:47'),
(35, 1, 1, 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'multiple_choice', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1, '2026-02-08 16:53:47', '2026-02-08 16:53:47'),
(36, 1, 9, 'What is the correct order for hand hygiene?', 'multiple_choice', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1, '2026-02-08 16:53:47', '2026-02-08 16:53:47'),
(37, 1, 9, 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1, '2026-02-08 16:53:47', '2026-02-08 16:53:47'),
(38, 1, 9, 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1, '2026-02-08 16:53:47', '2026-02-08 16:53:47'),
(39, 1, 1, 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1, '2026-02-08 16:53:47', '2026-02-08 16:53:47'),
(40, 2, 2, 'Which organ is responsible for pumping blood throughout the body?', 'multiple_choice', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(41, 2, 2, 'How many chambers does the human heart have?', 'multiple_choice', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(42, 2, 3, 'What is the normal resting heart rate for adults?', 'multiple_choice', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(43, 2, 2, 'The human body has 206 bones in the adult skeleton.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(44, 2, 3, 'The liver is located in the left upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(45, 2, 2, 'The skin is the largest organ in the human body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(46, 4, 5, 'What is the normal duration of pregnancy?', 'multiple_choice', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(47, 4, 5, 'Which trimester is considered the most critical for fetal development?', 'multiple_choice', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(48, 4, 5, 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(49, 4, 5, 'Fetal movements should be felt by the mother starting from the first trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(50, 4, 5, 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(51, 6, 6, 'What is the primary focus of public health?', 'multiple_choice', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(52, 6, 6, 'Which of the following is a communicable disease?', 'multiple_choice', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(53, 6, 6, 'Vaccination is one of the most cost-effective public health interventions.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(54, 6, 6, 'Antibiotics are effective against viral infections like the common cold.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(55, 6, 6, 'Hand washing is one of the most effective ways to prevent disease transmission.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(56, 8, 8, 'What is the primary purpose of anesthesia?', 'multiple_choice', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(57, 8, 8, 'Which type of anesthesia affects the entire body?', 'multiple_choice', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(58, 8, 8, 'General anesthesia causes complete loss of consciousness.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(59, 8, 8, 'Local anesthesia affects the entire body.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(60, 8, 9, 'Oxygen saturation must be continuously monitored during anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(61, 10, 7, 'What is the normal pH range of human blood?', 'multiple_choice', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(62, 10, 7, 'Which blood type is considered the universal donor?', 'multiple_choice', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(63, 10, 7, 'Blood type O negative is considered the universal donor.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(64, 10, 7, 'Hemoglobin is found in white blood cells.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(65, 10, 7, 'Red blood cells are responsible for transporting oxygen throughout the body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(66, 1, 1, 'What does the acronym ADPIE stand for in the nursing process?', 'multiple_choice', 'Assess, Diagnose, Plan, Implement, Evaluate', 'Analyze, Develop, Perform, Inspect, Execute', 'Admit, Discharge, Prescribe, Inject, Examine', 'Advise, Direct, Prepare, Intervene, Exit', 'A', 1, 'ADPIE represents the five steps of the nursing process.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(67, 1, 9, 'How long should you scrub your hands during hand washing?', 'multiple_choice', '5 seconds', '10 seconds', '20 seconds', '60 seconds', 'C', 1, 'Proper hand washing requires at least 20 seconds of scrubbing.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(68, 1, 1, 'Which vital sign is measured in beats per minute?', 'multiple_choice', 'Temperature', 'Blood pressure', 'Pulse', 'Respiratory rate', 'C', 1, 'Pulse is measured in beats per minute (bpm).', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(69, 1, 9, 'What is the proper angle for intramuscular injection?', 'multiple_choice', '15 degrees', '45 degrees', '90 degrees', '180 degrees', 'C', 1, 'Intramuscular injections are given at a 90-degree angle.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(70, 1, 1, 'What is the normal body temperature in Celsius?', 'multiple_choice', '35.5°C', '37°C', '38.5°C', '40°C', 'B', 1, 'Normal body temperature is approximately 37°C or 98.6°F.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(71, 1, 9, 'Nurses should always identify patients using two identifiers before administering medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Two patient identifiers (name and date of birth) are required for patient safety.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(72, 1, 1, 'Documentation in nursing should be done at the end of the shift.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Documentation should be done immediately after care is provided to ensure accuracy.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(73, 1, 9, 'Standard precautions should be used with all patients regardless of diagnosis.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standard precautions are infection control practices used with all patients.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(74, 1, 1, 'Nurses can delegate assessment tasks to unlicensed assistive personnel.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Assessment is a nursing responsibility that cannot be delegated to unlicensed personnel.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(75, 1, 9, 'Gloves should be changed between tasks on the same patient.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Gloves should be changed to prevent cross-contamination between different body sites.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(76, 2, 2, 'Which bone protects the brain?', 'multiple_choice', 'Femur', 'Skull', 'Ribs', 'Pelvis', 'B', 1, 'The skull (cranium) protects the brain.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(77, 2, 3, 'What is the largest artery in the human body?', 'multiple_choice', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 1, 'The aorta is the largest artery, carrying blood from the heart to the body.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(78, 2, 2, 'How many pairs of ribs does a human have?', 'multiple_choice', '10', '12', '14', '16', 'B', 1, 'Humans have 12 pairs of ribs (24 ribs total).', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(79, 2, 3, 'Which organ produces insulin?', 'multiple_choice', 'Liver', 'Pancreas', 'Kidney', 'Spleen', 'B', 1, 'The pancreas produces insulin to regulate blood sugar.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(80, 2, 2, 'What is the longest bone in the human body?', 'multiple_choice', 'Humerus', 'Tibia', 'Femur', 'Radius', 'C', 1, 'The femur (thigh bone) is the longest and strongest bone.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(81, 2, 3, 'The lungs are located in the thoracic cavity.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The lungs are housed in the thoracic (chest) cavity, protected by the rib cage.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(82, 2, 2, 'The human spine has 33 vertebrae.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The spine consists of 33 vertebrae: 7 cervical, 12 thoracic, 5 lumbar, 5 sacral (fused), and 4 coccygeal (fused).', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(83, 2, 3, 'The kidneys filter approximately 180 liters of blood per day.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The kidneys filter about 180 liters of blood daily, producing 1-2 liters of urine.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(84, 2, 2, 'Cartilage is a type of connective tissue.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Cartilage is a flexible connective tissue found in joints, ears, nose, and other structures.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(85, 2, 3, 'The stomach is located in the right upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The stomach is primarily located in the left upper quadrant of the abdomen.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(86, 3, 4, 'What is the antidote for warfarin overdose?', 'multiple_choice', 'Protamine sulfate', 'Vitamin K', 'Naloxone', 'Flumazenil', 'B', 1, 'Vitamin K is the antidote for warfarin (Coumadin) overdose.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(87, 3, 9, 'What is the priority nursing action for a patient with chest pain?', 'multiple_choice', 'Document the pain', 'Administer oxygen', 'Call the family', 'Ambulate the patient', 'B', 1, 'Administering oxygen is priority to improve cardiac oxygenation.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(88, 3, 4, 'Which medication is used to treat hypertension?', 'multiple_choice', 'Insulin', 'Lisinopril', 'Aspirin', 'Metformin', 'B', 1, 'Lisinopril is an ACE inhibitor used to treat high blood pressure.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(89, 3, 9, 'What is the normal range for adult blood pressure?', 'multiple_choice', '90/60 to 120/80 mmHg', '130/90 to 150/100 mmHg', '160/100 to 180/110 mmHg', '200/120 to 220/130 mmHg', 'A', 1, 'Normal blood pressure is less than 120/80 mmHg.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(90, 3, 4, 'Which lab value indicates kidney function?', 'multiple_choice', 'Hemoglobin', 'Creatinine', 'Glucose', 'Cholesterol', 'B', 1, 'Creatinine levels indicate how well the kidneys are filtering waste.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(91, 3, 9, 'Patients with diabetes should skip meals if their blood sugar is high.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Diabetic patients should maintain regular meal schedules and work with healthcare providers to adjust medications.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(92, 3, 4, 'Aspirin is an anticoagulant medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Aspirin inhibits platelet aggregation and acts as an anticoagulant.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(93, 3, 9, 'A patient with a myocardial infarction should be kept on bed rest initially.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Initial bed rest reduces cardiac workload and oxygen demand after a heart attack.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(94, 3, 4, 'Antibiotics are effective against all types of infections.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(95, 3, 9, 'Patients should be assessed for pain using a standardized pain scale.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standardized pain scales ensure consistent and accurate pain assessment.', 1, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(96, 4, 5, 'What is the first stage of labor?', 'multiple_choice', 'Delivery of the baby', 'Cervical dilation', 'Delivery of placenta', 'Recovery', 'B', 1, 'The first stage of labor involves cervical dilation from 0 to 10 cm.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(97, 4, 5, 'At what week is a fetus considered full-term?', 'multiple_choice', '32 weeks', '35 weeks', '37 weeks', '42 weeks', 'C', 1, 'A pregnancy is considered full-term at 37 weeks.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(98, 4, 5, 'What is the normal fetal heart rate?', 'multiple_choice', '60-80 bpm', '80-100 bpm', '110-160 bpm', '180-200 bpm', 'C', 1, 'Normal fetal heart rate is 110-160 beats per minute.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(99, 4, 5, 'Which hormone maintains pregnancy?', 'multiple_choice', 'Estrogen', 'Progesterone', 'Testosterone', 'Insulin', 'B', 1, 'Progesterone is essential for maintaining pregnancy.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(100, 4, 5, 'What is the recommended weight gain during pregnancy for normal BMI?', 'multiple_choice', '5-10 kg', '11-16 kg', '20-25 kg', '30-35 kg', 'B', 1, 'Recommended weight gain for normal BMI is 11-16 kg (25-35 lbs).', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(101, 5, 5, 'What is postpartum hemorrhage defined as?', 'multiple_choice', 'Blood loss >500 ml after vaginal delivery', 'Blood loss >100 ml after delivery', 'Blood loss >200 ml after delivery', 'Any bleeding after delivery', 'A', 1, 'Postpartum hemorrhage is blood loss exceeding 500 ml after vaginal delivery.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(102, 4, 5, 'Prenatal care should begin in the second trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Prenatal care should ideally begin as soon as pregnancy is confirmed, in the first trimester.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(103, 4, 5, 'Breastfeeding should be initiated within the first hour after birth.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Early initiation of breastfeeding promotes bonding and provides important antibodies to the newborn.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(104, 5, 5, 'The umbilical cord contains two arteries and one vein.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The umbilical cord normally contains two arteries and one vein.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(105, 4, 5, 'Morning sickness only occurs in the morning.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Despite its name, morning sickness can occur at any time of day.', 3, '2026-02-08 16:53:48', '2026-02-08 16:53:48'),
(106, 6, 6, 'What does WHO stand for?', 'multiple_choice', 'World Health Office', 'World Health Organization', 'Worldwide Health Operations', 'World Hospital Organization', 'B', 1, 'WHO is the World Health Organization, a UN agency for public health.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(107, 6, 6, 'Which disease was eradicated globally through vaccination?', 'multiple_choice', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 1, 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(108, 6, 6, 'What is the leading cause of death worldwide?', 'multiple_choice', 'Cancer', 'Cardiovascular disease', 'Respiratory infections', 'Accidents', 'B', 1, 'Cardiovascular diseases are the leading cause of death globally.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(109, 7, 6, 'What is the basic reproduction number (R0) in epidemiology?', 'multiple_choice', 'Number of deaths', 'Number of new infections from one case', 'Number of recovered patients', 'Number of vaccinated individuals', 'B', 1, 'R0 represents the average number of people infected by one contagious person.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(110, 6, 6, 'Which vitamin deficiency causes scurvy?', 'multiple_choice', 'Vitamin A', 'Vitamin B12', 'Vitamin C', 'Vitamin D', 'C', 1, 'Scurvy is caused by severe vitamin C deficiency.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(111, 7, 6, 'What is herd immunity?', 'multiple_choice', 'Immunity in animals', 'Individual immunity', 'Population-level immunity', 'Temporary immunity', 'C', 1, 'Herd immunity occurs when enough people are immune to prevent disease spread.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(112, 6, 6, 'Malaria is transmitted by mosquitoes.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Malaria is transmitted through the bite of infected Anopheles mosquitoes.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(113, 7, 6, 'Incidence refers to existing cases of disease in a population.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Incidence refers to new cases; prevalence refers to existing cases.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(114, 6, 6, 'Clean water access is a social determinant of health.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Access to clean water significantly impacts population health outcomes.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(115, 7, 6, 'An epidemic affects multiple countries or continents.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'An epidemic is widespread in one region; a pandemic affects multiple countries or continents.', 4, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(116, 8, 8, 'What is the ASA classification system used for?', 'multiple_choice', 'Anesthesia dosing', 'Patient physical status', 'Surgery duration', 'Recovery time', 'B', 1, 'ASA classification assesses patient physical status before anesthesia.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(117, 8, 8, 'Which drug is commonly used for induction of general anesthesia?', 'multiple_choice', 'Aspirin', 'Propofol', 'Insulin', 'Warfarin', 'B', 1, 'Propofol is a commonly used induction agent for general anesthesia.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(118, 9, 4, 'What is the antidote for opioid overdose?', 'multiple_choice', 'Epinephrine', 'Naloxone', 'Atropine', 'Dopamine', 'B', 1, 'Naloxone (Narcan) reverses opioid overdose effects.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(119, 8, 8, 'What does MAC stand for in anesthesia?', 'multiple_choice', 'Maximum Anesthesia Concentration', 'Monitored Anesthesia Care', 'Minimal Airway Control', 'Medical Anesthesia Certification', 'B', 1, 'MAC is Monitored Anesthesia Care, a type of sedation.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(120, 9, 4, 'Which gas is commonly used for general anesthesia?', 'multiple_choice', 'Oxygen', 'Sevoflurane', 'Carbon dioxide', 'Nitrogen', 'B', 1, 'Sevoflurane is a volatile anesthetic gas used for general anesthesia.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(121, 8, 8, 'Spinal anesthesia is a type of regional anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Spinal anesthesia blocks nerve transmission in a specific region of the body.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(122, 9, 9, 'Patients should fast before general anesthesia to prevent aspiration.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting reduces the risk of aspiration of stomach contents during anesthesia.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(123, 8, 8, 'Epidural anesthesia is commonly used for cesarean sections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Epidural anesthesia provides effective pain relief for cesarean deliveries.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(124, 9, 4, 'Atropine is used to increase heart rate.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Atropine is an anticholinergic drug that increases heart rate.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(125, 8, 9, 'Capnography measures carbon dioxide levels in exhaled breath.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Capnography monitors CO2 levels and is essential during anesthesia.', 5, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(126, 10, 7, 'What is the normal range for fasting blood glucose?', 'multiple_choice', '50-70 mg/dL', '70-100 mg/dL', '120-150 mg/dL', '180-200 mg/dL', 'B', 1, 'Normal fasting blood glucose is 70-100 mg/dL.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(127, 10, 7, 'Which test measures kidney function?', 'multiple_choice', 'Hemoglobin A1C', 'Creatinine', 'Lipid panel', 'Liver enzymes', 'B', 1, 'Serum creatinine is a key indicator of kidney function.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(128, 11, 7, 'What is anemia?', 'multiple_choice', 'High white blood cell count', 'Low red blood cell count', 'High platelet count', 'Low glucose level', 'B', 1, 'Anemia is a condition with low red blood cells or hemoglobin.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(129, 10, 7, 'What does HbA1c measure?', 'multiple_choice', 'Current blood sugar', 'Average blood sugar over 3 months', 'Kidney function', 'Liver function', 'B', 1, 'HbA1c reflects average blood glucose levels over 2-3 months.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(130, 11, 7, 'What is the normal white blood cell count?', 'multiple_choice', '1,000-3,000 cells/μL', '4,000-11,000 cells/μL', '15,000-20,000 cells/μL', '25,000-30,000 cells/μL', 'B', 1, 'Normal WBC count is 4,000-11,000 cells per microliter.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(131, 10, 7, 'Cholesterol levels should be checked while fasting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting lipid panels provide more accurate cholesterol measurements.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(132, 11, 7, 'Platelets are responsible for blood clotting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Platelets (thrombocytes) play a crucial role in blood clotting.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(133, 10, 7, 'Urine should be tested within 2 hours of collection.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Urine samples should be tested promptly to ensure accurate results.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(134, 11, 7, 'A complete blood count (CBC) includes hemoglobin, WBC, and platelet counts.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'CBC is a comprehensive blood test that measures multiple blood components.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(135, 10, 7, 'Elevated liver enzymes always indicate liver disease.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Elevated liver enzymes can have various causes and require further investigation.', 6, '2026-02-08 16:53:49', '2026-02-08 16:53:49'),
(136, 1, 1, 'What is the primary goal of nursing care?', 'multiple_choice', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(137, 1, 1, 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'multiple_choice', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(138, 1, 9, 'What is the correct order for hand hygiene?', 'multiple_choice', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(139, 1, 9, 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(140, 1, 9, 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(141, 1, 1, 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(142, 2, 2, 'Which organ is responsible for pumping blood throughout the body?', 'multiple_choice', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(143, 2, 2, 'How many chambers does the human heart have?', 'multiple_choice', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(144, 2, 3, 'What is the normal resting heart rate for adults?', 'multiple_choice', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(145, 2, 2, 'The human body has 206 bones in the adult skeleton.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(146, 2, 3, 'The liver is located in the left upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(147, 2, 2, 'The skin is the largest organ in the human body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1, '2026-02-08 17:34:15', '2026-02-08 17:34:15'),
(148, 4, 5, 'What is the normal duration of pregnancy?', 'multiple_choice', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(149, 4, 5, 'Which trimester is considered the most critical for fetal development?', 'multiple_choice', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(150, 4, 5, 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(151, 4, 5, 'Fetal movements should be felt by the mother starting from the first trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(152, 4, 5, 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(153, 6, 6, 'What is the primary focus of public health?', 'multiple_choice', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(154, 6, 6, 'Which of the following is a communicable disease?', 'multiple_choice', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(155, 6, 6, 'Vaccination is one of the most cost-effective public health interventions.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(156, 6, 6, 'Antibiotics are effective against viral infections like the common cold.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(157, 6, 6, 'Hand washing is one of the most effective ways to prevent disease transmission.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4, '2026-02-08 17:34:16', '2026-02-08 17:34:16'),
(158, 8, 8, 'What is the primary purpose of anesthesia?', 'multiple_choice', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(159, 8, 8, 'Which type of anesthesia affects the entire body?', 'multiple_choice', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(160, 8, 8, 'General anesthesia causes complete loss of consciousness.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(161, 8, 8, 'Local anesthesia affects the entire body.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(162, 8, 9, 'Oxygen saturation must be continuously monitored during anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(163, 10, 7, 'What is the normal pH range of human blood?', 'multiple_choice', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(164, 10, 7, 'Which blood type is considered the universal donor?', 'multiple_choice', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(165, 10, 7, 'Blood type O negative is considered the universal donor.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(166, 10, 7, 'Hemoglobin is found in white blood cells.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(167, 10, 7, 'Red blood cells are responsible for transporting oxygen throughout the body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(168, 1, 1, 'What is the primary goal of nursing care?', 'multiple_choice', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(169, 1, 1, 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'multiple_choice', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(170, 1, 9, 'What is the correct order for hand hygiene?', 'multiple_choice', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(171, 1, 9, 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(172, 1, 9, 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(173, 1, 1, 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1, '2026-02-08 17:34:17', '2026-02-08 17:34:17'),
(174, 1, 1, 'What does the acronym ADPIE stand for in the nursing process?', 'multiple_choice', 'Assess, Diagnose, Plan, Implement, Evaluate', 'Analyze, Develop, Perform, Inspect, Execute', 'Admit, Discharge, Prescribe, Inject, Examine', 'Advise, Direct, Prepare, Intervene, Exit', 'A', 1, 'ADPIE represents the five steps of the nursing process.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(175, 1, 9, 'How long should you scrub your hands during hand washing?', 'multiple_choice', '5 seconds', '10 seconds', '20 seconds', '60 seconds', 'C', 1, 'Proper hand washing requires at least 20 seconds of scrubbing.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(176, 1, 1, 'Which vital sign is measured in beats per minute?', 'multiple_choice', 'Temperature', 'Blood pressure', 'Pulse', 'Respiratory rate', 'C', 1, 'Pulse is measured in beats per minute (bpm).', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(177, 1, 9, 'What is the proper angle for intramuscular injection?', 'multiple_choice', '15 degrees', '45 degrees', '90 degrees', '180 degrees', 'C', 1, 'Intramuscular injections are given at a 90-degree angle.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18');
INSERT INTO `questions` (`question_id`, `course_id`, `topic_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`, `created_at`, `updated_at`) VALUES
(178, 1, 1, 'What is the normal body temperature in Celsius?', 'multiple_choice', '35.5°C', '37°C', '38.5°C', '40°C', 'B', 1, 'Normal body temperature is approximately 37°C or 98.6°F.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(179, 1, 9, 'Nurses should always identify patients using two identifiers before administering medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Two patient identifiers (name and date of birth) are required for patient safety.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(180, 1, 1, 'Documentation in nursing should be done at the end of the shift.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Documentation should be done immediately after care is provided to ensure accuracy.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(181, 1, 9, 'Standard precautions should be used with all patients regardless of diagnosis.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standard precautions are infection control practices used with all patients.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(182, 1, 1, 'Nurses can delegate assessment tasks to unlicensed assistive personnel.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Assessment is a nursing responsibility that cannot be delegated to unlicensed personnel.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(183, 1, 9, 'Gloves should be changed between tasks on the same patient.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Gloves should be changed to prevent cross-contamination between different body sites.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(184, 2, 2, 'Which organ is responsible for pumping blood throughout the body?', 'multiple_choice', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(185, 2, 2, 'How many chambers does the human heart have?', 'multiple_choice', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(186, 2, 3, 'What is the normal resting heart rate for adults?', 'multiple_choice', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(187, 2, 2, 'The human body has 206 bones in the adult skeleton.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(188, 2, 3, 'The liver is located in the left upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(189, 2, 2, 'The skin is the largest organ in the human body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(190, 2, 2, 'Which bone protects the brain?', 'multiple_choice', 'Femur', 'Skull', 'Ribs', 'Pelvis', 'B', 1, 'The skull (cranium) protects the brain.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(191, 2, 3, 'What is the largest artery in the human body?', 'multiple_choice', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 1, 'The aorta is the largest artery, carrying blood from the heart to the body.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(192, 2, 2, 'How many pairs of ribs does a human have?', 'multiple_choice', '10', '12', '14', '16', 'B', 1, 'Humans have 12 pairs of ribs (24 ribs total).', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(193, 2, 3, 'Which organ produces insulin?', 'multiple_choice', 'Liver', 'Pancreas', 'Kidney', 'Spleen', 'B', 1, 'The pancreas produces insulin to regulate blood sugar.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(194, 2, 2, 'What is the longest bone in the human body?', 'multiple_choice', 'Humerus', 'Tibia', 'Femur', 'Radius', 'C', 1, 'The femur (thigh bone) is the longest and strongest bone.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(195, 2, 3, 'The lungs are located in the thoracic cavity.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The lungs are housed in the thoracic (chest) cavity, protected by the rib cage.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(196, 2, 2, 'The human spine has 33 vertebrae.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The spine consists of 33 vertebrae: 7 cervical, 12 thoracic, 5 lumbar, 5 sacral (fused), and 4 coccygeal (fused).', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(197, 2, 3, 'The kidneys filter approximately 180 liters of blood per day.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The kidneys filter about 180 liters of blood daily, producing 1-2 liters of urine.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(198, 2, 2, 'Cartilage is a type of connective tissue.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Cartilage is a flexible connective tissue found in joints, ears, nose, and other structures.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(199, 2, 3, 'The stomach is located in the right upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The stomach is primarily located in the left upper quadrant of the abdomen.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(200, 4, 5, 'What is the normal duration of pregnancy?', 'multiple_choice', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(201, 4, 5, 'Which trimester is considered the most critical for fetal development?', 'multiple_choice', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(202, 4, 5, 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(203, 4, 5, 'Fetal movements should be felt by the mother starting from the first trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(204, 4, 5, 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(205, 6, 6, 'What is the primary focus of public health?', 'multiple_choice', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(206, 6, 6, 'Which of the following is a communicable disease?', 'multiple_choice', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(207, 6, 6, 'Vaccination is one of the most cost-effective public health interventions.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(208, 6, 6, 'Antibiotics are effective against viral infections like the common cold.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(209, 6, 6, 'Hand washing is one of the most effective ways to prevent disease transmission.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(210, 3, 4, 'What is the antidote for warfarin overdose?', 'multiple_choice', 'Protamine sulfate', 'Vitamin K', 'Naloxone', 'Flumazenil', 'B', 1, 'Vitamin K is the antidote for warfarin (Coumadin) overdose.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(211, 3, 9, 'What is the priority nursing action for a patient with chest pain?', 'multiple_choice', 'Document the pain', 'Administer oxygen', 'Call the family', 'Ambulate the patient', 'B', 1, 'Administering oxygen is priority to improve cardiac oxygenation.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(212, 3, 4, 'Which medication is used to treat hypertension?', 'multiple_choice', 'Insulin', 'Lisinopril', 'Aspirin', 'Metformin', 'B', 1, 'Lisinopril is an ACE inhibitor used to treat high blood pressure.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(213, 3, 9, 'What is the normal range for adult blood pressure?', 'multiple_choice', '90/60 to 120/80 mmHg', '130/90 to 150/100 mmHg', '160/100 to 180/110 mmHg', '200/120 to 220/130 mmHg', 'A', 1, 'Normal blood pressure is less than 120/80 mmHg.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(214, 3, 4, 'Which lab value indicates kidney function?', 'multiple_choice', 'Hemoglobin', 'Creatinine', 'Glucose', 'Cholesterol', 'B', 1, 'Creatinine levels indicate how well the kidneys are filtering waste.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(215, 3, 9, 'Patients with diabetes should skip meals if their blood sugar is high.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Diabetic patients should maintain regular meal schedules and work with healthcare providers to adjust medications.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(216, 3, 4, 'Aspirin is an anticoagulant medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Aspirin inhibits platelet aggregation and acts as an anticoagulant.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(217, 3, 9, 'A patient with a myocardial infarction should be kept on bed rest initially.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Initial bed rest reduces cardiac workload and oxygen demand after a heart attack.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(218, 3, 4, 'Antibiotics are effective against all types of infections.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(219, 3, 9, 'Patients should be assessed for pain using a standardized pain scale.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standardized pain scales ensure consistent and accurate pain assessment.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(220, 8, 8, 'What is the primary purpose of anesthesia?', 'multiple_choice', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(221, 8, 8, 'Which type of anesthesia affects the entire body?', 'multiple_choice', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(222, 8, 8, 'General anesthesia causes complete loss of consciousness.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(223, 8, 8, 'Local anesthesia affects the entire body.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(224, 8, 9, 'Oxygen saturation must be continuously monitored during anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(225, 4, 5, 'What is the first stage of labor?', 'multiple_choice', 'Delivery of the baby', 'Cervical dilation', 'Delivery of placenta', 'Recovery', 'B', 1, 'The first stage of labor involves cervical dilation from 0 to 10 cm.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(226, 4, 5, 'At what week is a fetus considered full-term?', 'multiple_choice', '32 weeks', '35 weeks', '37 weeks', '42 weeks', 'C', 1, 'A pregnancy is considered full-term at 37 weeks.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(227, 4, 5, 'What is the normal fetal heart rate?', 'multiple_choice', '60-80 bpm', '80-100 bpm', '110-160 bpm', '180-200 bpm', 'C', 1, 'Normal fetal heart rate is 110-160 beats per minute.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(228, 4, 5, 'Which hormone maintains pregnancy?', 'multiple_choice', 'Estrogen', 'Progesterone', 'Testosterone', 'Insulin', 'B', 1, 'Progesterone is essential for maintaining pregnancy.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(229, 4, 5, 'What is the recommended weight gain during pregnancy for normal BMI?', 'multiple_choice', '5-10 kg', '11-16 kg', '20-25 kg', '30-35 kg', 'B', 1, 'Recommended weight gain for normal BMI is 11-16 kg (25-35 lbs).', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(230, 5, 5, 'What is postpartum hemorrhage defined as?', 'multiple_choice', 'Blood loss >500 ml after vaginal delivery', 'Blood loss >100 ml after delivery', 'Blood loss >200 ml after delivery', 'Any bleeding after delivery', 'A', 1, 'Postpartum hemorrhage is blood loss exceeding 500 ml after vaginal delivery.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(231, 4, 5, 'Prenatal care should begin in the second trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Prenatal care should ideally begin as soon as pregnancy is confirmed, in the first trimester.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(232, 4, 5, 'Breastfeeding should be initiated within the first hour after birth.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Early initiation of breastfeeding promotes bonding and provides important antibodies to the newborn.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(233, 5, 5, 'The umbilical cord contains two arteries and one vein.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The umbilical cord normally contains two arteries and one vein.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(234, 4, 5, 'Morning sickness only occurs in the morning.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Despite its name, morning sickness can occur at any time of day.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(235, 10, 7, 'What is the normal pH range of human blood?', 'multiple_choice', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(236, 10, 7, 'Which blood type is considered the universal donor?', 'multiple_choice', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(237, 10, 7, 'Blood type O negative is considered the universal donor.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(238, 10, 7, 'Hemoglobin is found in white blood cells.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(239, 10, 7, 'Red blood cells are responsible for transporting oxygen throughout the body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(240, 6, 6, 'What does WHO stand for?', 'multiple_choice', 'World Health Office', 'World Health Organization', 'Worldwide Health Operations', 'World Hospital Organization', 'B', 1, 'WHO is the World Health Organization, a UN agency for public health.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(241, 6, 6, 'Which disease was eradicated globally through vaccination?', 'multiple_choice', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 1, 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(242, 6, 6, 'What is the leading cause of death worldwide?', 'multiple_choice', 'Cancer', 'Cardiovascular disease', 'Respiratory infections', 'Accidents', 'B', 1, 'Cardiovascular diseases are the leading cause of death globally.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(243, 7, 6, 'What is the basic reproduction number (R0) in epidemiology?', 'multiple_choice', 'Number of deaths', 'Number of new infections from one case', 'Number of recovered patients', 'Number of vaccinated individuals', 'B', 1, 'R0 represents the average number of people infected by one contagious person.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(244, 6, 6, 'Which vitamin deficiency causes scurvy?', 'multiple_choice', 'Vitamin A', 'Vitamin B12', 'Vitamin C', 'Vitamin D', 'C', 1, 'Scurvy is caused by severe vitamin C deficiency.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(245, 7, 6, 'What is herd immunity?', 'multiple_choice', 'Immunity in animals', 'Individual immunity', 'Population-level immunity', 'Temporary immunity', 'C', 1, 'Herd immunity occurs when enough people are immune to prevent disease spread.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(246, 6, 6, 'Malaria is transmitted by mosquitoes.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Malaria is transmitted through the bite of infected Anopheles mosquitoes.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(247, 7, 6, 'Incidence refers to existing cases of disease in a population.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Incidence refers to new cases; prevalence refers to existing cases.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(248, 6, 6, 'Clean water access is a social determinant of health.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Access to clean water significantly impacts population health outcomes.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(249, 7, 6, 'An epidemic affects multiple countries or continents.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'An epidemic is widespread in one region; a pandemic affects multiple countries or continents.', 4, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(250, 1, 1, 'What does the acronym ADPIE stand for in the nursing process?', 'multiple_choice', 'Assess, Diagnose, Plan, Implement, Evaluate', 'Analyze, Develop, Perform, Inspect, Execute', 'Admit, Discharge, Prescribe, Inject, Examine', 'Advise, Direct, Prepare, Intervene, Exit', 'A', 1, 'ADPIE represents the five steps of the nursing process.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(251, 1, 9, 'How long should you scrub your hands during hand washing?', 'multiple_choice', '5 seconds', '10 seconds', '20 seconds', '60 seconds', 'C', 1, 'Proper hand washing requires at least 20 seconds of scrubbing.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(252, 1, 1, 'Which vital sign is measured in beats per minute?', 'multiple_choice', 'Temperature', 'Blood pressure', 'Pulse', 'Respiratory rate', 'C', 1, 'Pulse is measured in beats per minute (bpm).', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(253, 1, 9, 'What is the proper angle for intramuscular injection?', 'multiple_choice', '15 degrees', '45 degrees', '90 degrees', '180 degrees', 'C', 1, 'Intramuscular injections are given at a 90-degree angle.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(254, 1, 1, 'What is the normal body temperature in Celsius?', 'multiple_choice', '35.5°C', '37°C', '38.5°C', '40°C', 'B', 1, 'Normal body temperature is approximately 37°C or 98.6°F.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(255, 1, 9, 'Nurses should always identify patients using two identifiers before administering medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Two patient identifiers (name and date of birth) are required for patient safety.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(256, 1, 1, 'Documentation in nursing should be done at the end of the shift.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Documentation should be done immediately after care is provided to ensure accuracy.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(257, 1, 9, 'Standard precautions should be used with all patients regardless of diagnosis.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standard precautions are infection control practices used with all patients.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(258, 1, 1, 'Nurses can delegate assessment tasks to unlicensed assistive personnel.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Assessment is a nursing responsibility that cannot be delegated to unlicensed personnel.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(259, 1, 9, 'Gloves should be changed between tasks on the same patient.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Gloves should be changed to prevent cross-contamination between different body sites.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(260, 8, 8, 'What is the ASA classification system used for?', 'multiple_choice', 'Anesthesia dosing', 'Patient physical status', 'Surgery duration', 'Recovery time', 'B', 1, 'ASA classification assesses patient physical status before anesthesia.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(261, 8, 8, 'Which drug is commonly used for induction of general anesthesia?', 'multiple_choice', 'Aspirin', 'Propofol', 'Insulin', 'Warfarin', 'B', 1, 'Propofol is a commonly used induction agent for general anesthesia.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(262, 9, 4, 'What is the antidote for opioid overdose?', 'multiple_choice', 'Epinephrine', 'Naloxone', 'Atropine', 'Dopamine', 'B', 1, 'Naloxone (Narcan) reverses opioid overdose effects.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(263, 8, 8, 'What does MAC stand for in anesthesia?', 'multiple_choice', 'Maximum Anesthesia Concentration', 'Monitored Anesthesia Care', 'Minimal Airway Control', 'Medical Anesthesia Certification', 'B', 1, 'MAC is Monitored Anesthesia Care, a type of sedation.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(264, 9, 4, 'Which gas is commonly used for general anesthesia?', 'multiple_choice', 'Oxygen', 'Sevoflurane', 'Carbon dioxide', 'Nitrogen', 'B', 1, 'Sevoflurane is a volatile anesthetic gas used for general anesthesia.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(265, 8, 8, 'Spinal anesthesia is a type of regional anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Spinal anesthesia blocks nerve transmission in a specific region of the body.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(266, 9, 9, 'Patients should fast before general anesthesia to prevent aspiration.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting reduces the risk of aspiration of stomach contents during anesthesia.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(267, 8, 8, 'Epidural anesthesia is commonly used for cesarean sections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Epidural anesthesia provides effective pain relief for cesarean deliveries.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(268, 9, 4, 'Atropine is used to increase heart rate.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Atropine is an anticholinergic drug that increases heart rate.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(269, 8, 9, 'Capnography measures carbon dioxide levels in exhaled breath.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Capnography monitors CO2 levels and is essential during anesthesia.', 5, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(270, 2, 2, 'Which bone protects the brain?', 'multiple_choice', 'Femur', 'Skull', 'Ribs', 'Pelvis', 'B', 1, 'The skull (cranium) protects the brain.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(271, 2, 3, 'What is the largest artery in the human body?', 'multiple_choice', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 1, 'The aorta is the largest artery, carrying blood from the heart to the body.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(272, 2, 2, 'How many pairs of ribs does a human have?', 'multiple_choice', '10', '12', '14', '16', 'B', 1, 'Humans have 12 pairs of ribs (24 ribs total).', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(273, 2, 3, 'Which organ produces insulin?', 'multiple_choice', 'Liver', 'Pancreas', 'Kidney', 'Spleen', 'B', 1, 'The pancreas produces insulin to regulate blood sugar.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(274, 2, 2, 'What is the longest bone in the human body?', 'multiple_choice', 'Humerus', 'Tibia', 'Femur', 'Radius', 'C', 1, 'The femur (thigh bone) is the longest and strongest bone.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(275, 2, 3, 'The lungs are located in the thoracic cavity.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The lungs are housed in the thoracic (chest) cavity, protected by the rib cage.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(276, 2, 2, 'The human spine has 33 vertebrae.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The spine consists of 33 vertebrae: 7 cervical, 12 thoracic, 5 lumbar, 5 sacral (fused), and 4 coccygeal (fused).', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(277, 2, 3, 'The kidneys filter approximately 180 liters of blood per day.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The kidneys filter about 180 liters of blood daily, producing 1-2 liters of urine.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(278, 2, 2, 'Cartilage is a type of connective tissue.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Cartilage is a flexible connective tissue found in joints, ears, nose, and other structures.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(279, 2, 3, 'The stomach is located in the right upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The stomach is primarily located in the left upper quadrant of the abdomen.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(280, 3, 4, 'What is the antidote for warfarin overdose?', 'multiple_choice', 'Protamine sulfate', 'Vitamin K', 'Naloxone', 'Flumazenil', 'B', 1, 'Vitamin K is the antidote for warfarin (Coumadin) overdose.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(281, 3, 9, 'What is the priority nursing action for a patient with chest pain?', 'multiple_choice', 'Document the pain', 'Administer oxygen', 'Call the family', 'Ambulate the patient', 'B', 1, 'Administering oxygen is priority to improve cardiac oxygenation.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(282, 3, 4, 'Which medication is used to treat hypertension?', 'multiple_choice', 'Insulin', 'Lisinopril', 'Aspirin', 'Metformin', 'B', 1, 'Lisinopril is an ACE inhibitor used to treat high blood pressure.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(283, 3, 9, 'What is the normal range for adult blood pressure?', 'multiple_choice', '90/60 to 120/80 mmHg', '130/90 to 150/100 mmHg', '160/100 to 180/110 mmHg', '200/120 to 220/130 mmHg', 'A', 1, 'Normal blood pressure is less than 120/80 mmHg.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(284, 3, 4, 'Which lab value indicates kidney function?', 'multiple_choice', 'Hemoglobin', 'Creatinine', 'Glucose', 'Cholesterol', 'B', 1, 'Creatinine levels indicate how well the kidneys are filtering waste.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(285, 3, 9, 'Patients with diabetes should skip meals if their blood sugar is high.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Diabetic patients should maintain regular meal schedules and work with healthcare providers to adjust medications.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(286, 3, 4, 'Aspirin is an anticoagulant medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Aspirin inhibits platelet aggregation and acts as an anticoagulant.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(287, 3, 9, 'A patient with a myocardial infarction should be kept on bed rest initially.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Initial bed rest reduces cardiac workload and oxygen demand after a heart attack.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(288, 3, 4, 'Antibiotics are effective against all types of infections.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(289, 3, 9, 'Patients should be assessed for pain using a standardized pain scale.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standardized pain scales ensure consistent and accurate pain assessment.', 1, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(290, 10, 7, 'What is the normal range for fasting blood glucose?', 'multiple_choice', '50-70 mg/dL', '70-100 mg/dL', '120-150 mg/dL', '180-200 mg/dL', 'B', 1, 'Normal fasting blood glucose is 70-100 mg/dL.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(291, 10, 7, 'Which test measures kidney function?', 'multiple_choice', 'Hemoglobin A1C', 'Creatinine', 'Lipid panel', 'Liver enzymes', 'B', 1, 'Serum creatinine is a key indicator of kidney function.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(292, 11, 7, 'What is anemia?', 'multiple_choice', 'High white blood cell count', 'Low red blood cell count', 'High platelet count', 'Low glucose level', 'B', 1, 'Anemia is a condition with low red blood cells or hemoglobin.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(293, 10, 7, 'What does HbA1c measure?', 'multiple_choice', 'Current blood sugar', 'Average blood sugar over 3 months', 'Kidney function', 'Liver function', 'B', 1, 'HbA1c reflects average blood glucose levels over 2-3 months.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(294, 11, 7, 'What is the normal white blood cell count?', 'multiple_choice', '1,000-3,000 cells/μL', '4,000-11,000 cells/μL', '15,000-20,000 cells/μL', '25,000-30,000 cells/μL', 'B', 1, 'Normal WBC count is 4,000-11,000 cells per microliter.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(295, 10, 7, 'Cholesterol levels should be checked while fasting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting lipid panels provide more accurate cholesterol measurements.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(296, 11, 7, 'Platelets are responsible for blood clotting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Platelets (thrombocytes) play a crucial role in blood clotting.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(297, 10, 7, 'Urine should be tested within 2 hours of collection.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Urine samples should be tested promptly to ensure accurate results.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(298, 11, 7, 'A complete blood count (CBC) includes hemoglobin, WBC, and platelet counts.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'CBC is a comprehensive blood test that measures multiple blood components.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(299, 10, 7, 'Elevated liver enzymes always indicate liver disease.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Elevated liver enzymes can have various causes and require further investigation.', 6, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(300, 4, 5, 'What is the first stage of labor?', 'multiple_choice', 'Delivery of the baby', 'Cervical dilation', 'Delivery of placenta', 'Recovery', 'B', 1, 'The first stage of labor involves cervical dilation from 0 to 10 cm.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(301, 4, 5, 'At what week is a fetus considered full-term?', 'multiple_choice', '32 weeks', '35 weeks', '37 weeks', '42 weeks', 'C', 1, 'A pregnancy is considered full-term at 37 weeks.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(302, 4, 5, 'What is the normal fetal heart rate?', 'multiple_choice', '60-80 bpm', '80-100 bpm', '110-160 bpm', '180-200 bpm', 'C', 1, 'Normal fetal heart rate is 110-160 beats per minute.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(303, 4, 5, 'Which hormone maintains pregnancy?', 'multiple_choice', 'Estrogen', 'Progesterone', 'Testosterone', 'Insulin', 'B', 1, 'Progesterone is essential for maintaining pregnancy.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(304, 4, 5, 'What is the recommended weight gain during pregnancy for normal BMI?', 'multiple_choice', '5-10 kg', '11-16 kg', '20-25 kg', '30-35 kg', 'B', 1, 'Recommended weight gain for normal BMI is 11-16 kg (25-35 lbs).', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(305, 5, 5, 'What is postpartum hemorrhage defined as?', 'multiple_choice', 'Blood loss >500 ml after vaginal delivery', 'Blood loss >100 ml after delivery', 'Blood loss >200 ml after delivery', 'Any bleeding after delivery', 'A', 1, 'Postpartum hemorrhage is blood loss exceeding 500 ml after vaginal delivery.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(306, 4, 5, 'Prenatal care should begin in the second trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Prenatal care should ideally begin as soon as pregnancy is confirmed, in the first trimester.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(307, 4, 5, 'Breastfeeding should be initiated within the first hour after birth.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Early initiation of breastfeeding promotes bonding and provides important antibodies to the newborn.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(308, 5, 5, 'The umbilical cord contains two arteries and one vein.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The umbilical cord normally contains two arteries and one vein.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(309, 4, 5, 'Morning sickness only occurs in the morning.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Despite its name, morning sickness can occur at any time of day.', 3, '2026-02-08 17:34:18', '2026-02-08 17:34:18'),
(310, 6, 6, 'What does WHO stand for?', 'multiple_choice', 'World Health Office', 'World Health Organization', 'Worldwide Health Operations', 'World Hospital Organization', 'B', 1, 'WHO is the World Health Organization, a UN agency for public health.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(311, 6, 6, 'Which disease was eradicated globally through vaccination?', 'multiple_choice', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 1, 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(312, 6, 6, 'What is the leading cause of death worldwide?', 'multiple_choice', 'Cancer', 'Cardiovascular disease', 'Respiratory infections', 'Accidents', 'B', 1, 'Cardiovascular diseases are the leading cause of death globally.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(313, 7, 6, 'What is the basic reproduction number (R0) in epidemiology?', 'multiple_choice', 'Number of deaths', 'Number of new infections from one case', 'Number of recovered patients', 'Number of vaccinated individuals', 'B', 1, 'R0 represents the average number of people infected by one contagious person.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(314, 6, 6, 'Which vitamin deficiency causes scurvy?', 'multiple_choice', 'Vitamin A', 'Vitamin B12', 'Vitamin C', 'Vitamin D', 'C', 1, 'Scurvy is caused by severe vitamin C deficiency.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(315, 7, 6, 'What is herd immunity?', 'multiple_choice', 'Immunity in animals', 'Individual immunity', 'Population-level immunity', 'Temporary immunity', 'C', 1, 'Herd immunity occurs when enough people are immune to prevent disease spread.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(316, 6, 6, 'Malaria is transmitted by mosquitoes.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Malaria is transmitted through the bite of infected Anopheles mosquitoes.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(317, 7, 6, 'Incidence refers to existing cases of disease in a population.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Incidence refers to new cases; prevalence refers to existing cases.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(318, 6, 6, 'Clean water access is a social determinant of health.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Access to clean water significantly impacts population health outcomes.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(319, 7, 6, 'An epidemic affects multiple countries or continents.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'An epidemic is widespread in one region; a pandemic affects multiple countries or continents.', 4, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(320, 8, 8, 'What is the ASA classification system used for?', 'multiple_choice', 'Anesthesia dosing', 'Patient physical status', 'Surgery duration', 'Recovery time', 'B', 1, 'ASA classification assesses patient physical status before anesthesia.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(321, 8, 8, 'Which drug is commonly used for induction of general anesthesia?', 'multiple_choice', 'Aspirin', 'Propofol', 'Insulin', 'Warfarin', 'B', 1, 'Propofol is a commonly used induction agent for general anesthesia.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(322, 9, 4, 'What is the antidote for opioid overdose?', 'multiple_choice', 'Epinephrine', 'Naloxone', 'Atropine', 'Dopamine', 'B', 1, 'Naloxone (Narcan) reverses opioid overdose effects.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(323, 8, 8, 'What does MAC stand for in anesthesia?', 'multiple_choice', 'Maximum Anesthesia Concentration', 'Monitored Anesthesia Care', 'Minimal Airway Control', 'Medical Anesthesia Certification', 'B', 1, 'MAC is Monitored Anesthesia Care, a type of sedation.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(324, 9, 4, 'Which gas is commonly used for general anesthesia?', 'multiple_choice', 'Oxygen', 'Sevoflurane', 'Carbon dioxide', 'Nitrogen', 'B', 1, 'Sevoflurane is a volatile anesthetic gas used for general anesthesia.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(325, 8, 8, 'Spinal anesthesia is a type of regional anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Spinal anesthesia blocks nerve transmission in a specific region of the body.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(326, 9, 9, 'Patients should fast before general anesthesia to prevent aspiration.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting reduces the risk of aspiration of stomach contents during anesthesia.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(327, 8, 8, 'Epidural anesthesia is commonly used for cesarean sections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Epidural anesthesia provides effective pain relief for cesarean deliveries.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(328, 9, 4, 'Atropine is used to increase heart rate.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Atropine is an anticholinergic drug that increases heart rate.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(329, 8, 9, 'Capnography measures carbon dioxide levels in exhaled breath.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Capnography monitors CO2 levels and is essential during anesthesia.', 5, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(330, 10, 7, 'What is the normal range for fasting blood glucose?', 'multiple_choice', '50-70 mg/dL', '70-100 mg/dL', '120-150 mg/dL', '180-200 mg/dL', 'B', 1, 'Normal fasting blood glucose is 70-100 mg/dL.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(331, 10, 7, 'Which test measures kidney function?', 'multiple_choice', 'Hemoglobin A1C', 'Creatinine', 'Lipid panel', 'Liver enzymes', 'B', 1, 'Serum creatinine is a key indicator of kidney function.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(332, 11, 7, 'What is anemia?', 'multiple_choice', 'High white blood cell count', 'Low red blood cell count', 'High platelet count', 'Low glucose level', 'B', 1, 'Anemia is a condition with low red blood cells or hemoglobin.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(333, 10, 7, 'What does HbA1c measure?', 'multiple_choice', 'Current blood sugar', 'Average blood sugar over 3 months', 'Kidney function', 'Liver function', 'B', 1, 'HbA1c reflects average blood glucose levels over 2-3 months.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(334, 11, 7, 'What is the normal white blood cell count?', 'multiple_choice', '1,000-3,000 cells/μL', '4,000-11,000 cells/μL', '15,000-20,000 cells/μL', '25,000-30,000 cells/μL', 'B', 1, 'Normal WBC count is 4,000-11,000 cells per microliter.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(335, 10, 7, 'Cholesterol levels should be checked while fasting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting lipid panels provide more accurate cholesterol measurements.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(336, 11, 7, 'Platelets are responsible for blood clotting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Platelets (thrombocytes) play a crucial role in blood clotting.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(337, 10, 7, 'Urine should be tested within 2 hours of collection.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Urine samples should be tested promptly to ensure accurate results.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(338, 11, 7, 'A complete blood count (CBC) includes hemoglobin, WBC, and platelet counts.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'CBC is a comprehensive blood test that measures multiple blood components.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(339, 10, 7, 'Elevated liver enzymes always indicate liver disease.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Elevated liver enzymes can have various causes and require further investigation.', 6, '2026-02-08 17:34:19', '2026-02-08 17:34:19'),
(340, 1, 1, 'What is the primary goal of nursing care?', 'multiple_choice', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(341, 1, 1, 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'multiple_choice', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(342, 1, 9, 'What is the correct order for hand hygiene?', 'multiple_choice', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(343, 1, 9, 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(344, 1, 9, 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(345, 1, 1, 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(346, 2, 2, 'Which organ is responsible for pumping blood throughout the body?', 'multiple_choice', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(347, 2, 2, 'How many chambers does the human heart have?', 'multiple_choice', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(348, 2, 3, 'What is the normal resting heart rate for adults?', 'multiple_choice', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(349, 2, 2, 'The human body has 206 bones in the adult skeleton.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(350, 2, 3, 'The liver is located in the left upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(351, 2, 2, 'The skin is the largest organ in the human body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(352, 4, 5, 'What is the normal duration of pregnancy?', 'multiple_choice', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(353, 4, 5, 'Which trimester is considered the most critical for fetal development?', 'multiple_choice', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(354, 4, 5, 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(355, 4, 5, 'Fetal movements should be felt by the mother starting from the first trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(356, 4, 5, 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(357, 6, 6, 'What is the primary focus of public health?', 'multiple_choice', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(358, 6, 6, 'Which of the following is a communicable disease?', 'multiple_choice', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(359, 6, 6, 'Vaccination is one of the most cost-effective public health interventions.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4, '2026-02-08 17:37:33', '2026-02-08 17:37:33');
INSERT INTO `questions` (`question_id`, `course_id`, `topic_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`, `created_at`, `updated_at`) VALUES
(360, 6, 6, 'Antibiotics are effective against viral infections like the common cold.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(361, 6, 6, 'Hand washing is one of the most effective ways to prevent disease transmission.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(362, 8, 8, 'What is the primary purpose of anesthesia?', 'multiple_choice', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(363, 8, 8, 'Which type of anesthesia affects the entire body?', 'multiple_choice', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(364, 8, 8, 'General anesthesia causes complete loss of consciousness.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(365, 8, 8, 'Local anesthesia affects the entire body.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(366, 8, 9, 'Oxygen saturation must be continuously monitored during anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(367, 10, 7, 'What is the normal pH range of human blood?', 'multiple_choice', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(368, 10, 7, 'Which blood type is considered the universal donor?', 'multiple_choice', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(369, 10, 7, 'Blood type O negative is considered the universal donor.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(370, 10, 7, 'Hemoglobin is found in white blood cells.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(371, 10, 7, 'Red blood cells are responsible for transporting oxygen throughout the body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(372, 1, 1, 'What does the acronym ADPIE stand for in the nursing process?', 'multiple_choice', 'Assess, Diagnose, Plan, Implement, Evaluate', 'Analyze, Develop, Perform, Inspect, Execute', 'Admit, Discharge, Prescribe, Inject, Examine', 'Advise, Direct, Prepare, Intervene, Exit', 'A', 1, 'ADPIE represents the five steps of the nursing process.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(373, 1, 9, 'How long should you scrub your hands during hand washing?', 'multiple_choice', '5 seconds', '10 seconds', '20 seconds', '60 seconds', 'C', 1, 'Proper hand washing requires at least 20 seconds of scrubbing.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(374, 1, 1, 'Which vital sign is measured in beats per minute?', 'multiple_choice', 'Temperature', 'Blood pressure', 'Pulse', 'Respiratory rate', 'C', 1, 'Pulse is measured in beats per minute (bpm).', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(375, 1, 9, 'What is the proper angle for intramuscular injection?', 'multiple_choice', '15 degrees', '45 degrees', '90 degrees', '180 degrees', 'C', 1, 'Intramuscular injections are given at a 90-degree angle.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(376, 1, 1, 'What is the normal body temperature in Celsius?', 'multiple_choice', '35.5°C', '37°C', '38.5°C', '40°C', 'B', 1, 'Normal body temperature is approximately 37°C or 98.6°F.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(377, 1, 9, 'Nurses should always identify patients using two identifiers before administering medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Two patient identifiers (name and date of birth) are required for patient safety.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(378, 1, 1, 'Documentation in nursing should be done at the end of the shift.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Documentation should be done immediately after care is provided to ensure accuracy.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(379, 1, 9, 'Standard precautions should be used with all patients regardless of diagnosis.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standard precautions are infection control practices used with all patients.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(380, 1, 1, 'Nurses can delegate assessment tasks to unlicensed assistive personnel.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Assessment is a nursing responsibility that cannot be delegated to unlicensed personnel.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(381, 1, 9, 'Gloves should be changed between tasks on the same patient.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Gloves should be changed to prevent cross-contamination between different body sites.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(382, 2, 2, 'Which bone protects the brain?', 'multiple_choice', 'Femur', 'Skull', 'Ribs', 'Pelvis', 'B', 1, 'The skull (cranium) protects the brain.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(383, 2, 3, 'What is the largest artery in the human body?', 'multiple_choice', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 1, 'The aorta is the largest artery, carrying blood from the heart to the body.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(384, 2, 2, 'How many pairs of ribs does a human have?', 'multiple_choice', '10', '12', '14', '16', 'B', 1, 'Humans have 12 pairs of ribs (24 ribs total).', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(385, 2, 3, 'Which organ produces insulin?', 'multiple_choice', 'Liver', 'Pancreas', 'Kidney', 'Spleen', 'B', 1, 'The pancreas produces insulin to regulate blood sugar.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(386, 2, 2, 'What is the longest bone in the human body?', 'multiple_choice', 'Humerus', 'Tibia', 'Femur', 'Radius', 'C', 1, 'The femur (thigh bone) is the longest and strongest bone.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(387, 2, 3, 'The lungs are located in the thoracic cavity.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The lungs are housed in the thoracic (chest) cavity, protected by the rib cage.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(388, 2, 2, 'The human spine has 33 vertebrae.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The spine consists of 33 vertebrae: 7 cervical, 12 thoracic, 5 lumbar, 5 sacral (fused), and 4 coccygeal (fused).', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(389, 2, 3, 'The kidneys filter approximately 180 liters of blood per day.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The kidneys filter about 180 liters of blood daily, producing 1-2 liters of urine.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(390, 2, 2, 'Cartilage is a type of connective tissue.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Cartilage is a flexible connective tissue found in joints, ears, nose, and other structures.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(391, 2, 3, 'The stomach is located in the right upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The stomach is primarily located in the left upper quadrant of the abdomen.', 1, '2026-02-08 17:37:33', '2026-02-08 17:37:33'),
(392, 3, 4, 'What is the antidote for warfarin overdose?', 'multiple_choice', 'Protamine sulfate', 'Vitamin K', 'Naloxone', 'Flumazenil', 'B', 1, 'Vitamin K is the antidote for warfarin (Coumadin) overdose.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(393, 3, 9, 'What is the priority nursing action for a patient with chest pain?', 'multiple_choice', 'Document the pain', 'Administer oxygen', 'Call the family', 'Ambulate the patient', 'B', 1, 'Administering oxygen is priority to improve cardiac oxygenation.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(394, 3, 4, 'Which medication is used to treat hypertension?', 'multiple_choice', 'Insulin', 'Lisinopril', 'Aspirin', 'Metformin', 'B', 1, 'Lisinopril is an ACE inhibitor used to treat high blood pressure.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(395, 3, 9, 'What is the normal range for adult blood pressure?', 'multiple_choice', '90/60 to 120/80 mmHg', '130/90 to 150/100 mmHg', '160/100 to 180/110 mmHg', '200/120 to 220/130 mmHg', 'A', 1, 'Normal blood pressure is less than 120/80 mmHg.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(396, 3, 4, 'Which lab value indicates kidney function?', 'multiple_choice', 'Hemoglobin', 'Creatinine', 'Glucose', 'Cholesterol', 'B', 1, 'Creatinine levels indicate how well the kidneys are filtering waste.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(397, 3, 9, 'Patients with diabetes should skip meals if their blood sugar is high.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Diabetic patients should maintain regular meal schedules and work with healthcare providers to adjust medications.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(398, 3, 4, 'Aspirin is an anticoagulant medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Aspirin inhibits platelet aggregation and acts as an anticoagulant.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(399, 3, 9, 'A patient with a myocardial infarction should be kept on bed rest initially.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Initial bed rest reduces cardiac workload and oxygen demand after a heart attack.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(400, 3, 4, 'Antibiotics are effective against all types of infections.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(401, 3, 9, 'Patients should be assessed for pain using a standardized pain scale.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standardized pain scales ensure consistent and accurate pain assessment.', 1, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(402, 4, 5, 'What is the first stage of labor?', 'multiple_choice', 'Delivery of the baby', 'Cervical dilation', 'Delivery of placenta', 'Recovery', 'B', 1, 'The first stage of labor involves cervical dilation from 0 to 10 cm.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(403, 4, 5, 'At what week is a fetus considered full-term?', 'multiple_choice', '32 weeks', '35 weeks', '37 weeks', '42 weeks', 'C', 1, 'A pregnancy is considered full-term at 37 weeks.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(404, 4, 5, 'What is the normal fetal heart rate?', 'multiple_choice', '60-80 bpm', '80-100 bpm', '110-160 bpm', '180-200 bpm', 'C', 1, 'Normal fetal heart rate is 110-160 beats per minute.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(405, 4, 5, 'Which hormone maintains pregnancy?', 'multiple_choice', 'Estrogen', 'Progesterone', 'Testosterone', 'Insulin', 'B', 1, 'Progesterone is essential for maintaining pregnancy.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(406, 4, 5, 'What is the recommended weight gain during pregnancy for normal BMI?', 'multiple_choice', '5-10 kg', '11-16 kg', '20-25 kg', '30-35 kg', 'B', 1, 'Recommended weight gain for normal BMI is 11-16 kg (25-35 lbs).', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(407, 5, 5, 'What is postpartum hemorrhage defined as?', 'multiple_choice', 'Blood loss >500 ml after vaginal delivery', 'Blood loss >100 ml after delivery', 'Blood loss >200 ml after delivery', 'Any bleeding after delivery', 'A', 1, 'Postpartum hemorrhage is blood loss exceeding 500 ml after vaginal delivery.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(408, 4, 5, 'Prenatal care should begin in the second trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Prenatal care should ideally begin as soon as pregnancy is confirmed, in the first trimester.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(409, 4, 5, 'Breastfeeding should be initiated within the first hour after birth.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Early initiation of breastfeeding promotes bonding and provides important antibodies to the newborn.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(410, 5, 5, 'The umbilical cord contains two arteries and one vein.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The umbilical cord normally contains two arteries and one vein.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(411, 4, 5, 'Morning sickness only occurs in the morning.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Despite its name, morning sickness can occur at any time of day.', 3, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(412, 6, 6, 'What does WHO stand for?', 'multiple_choice', 'World Health Office', 'World Health Organization', 'Worldwide Health Operations', 'World Hospital Organization', 'B', 1, 'WHO is the World Health Organization, a UN agency for public health.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(413, 6, 6, 'Which disease was eradicated globally through vaccination?', 'multiple_choice', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 1, 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(414, 6, 6, 'What is the leading cause of death worldwide?', 'multiple_choice', 'Cancer', 'Cardiovascular disease', 'Respiratory infections', 'Accidents', 'B', 1, 'Cardiovascular diseases are the leading cause of death globally.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(415, 7, 6, 'What is the basic reproduction number (R0) in epidemiology?', 'multiple_choice', 'Number of deaths', 'Number of new infections from one case', 'Number of recovered patients', 'Number of vaccinated individuals', 'B', 1, 'R0 represents the average number of people infected by one contagious person.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(416, 6, 6, 'Which vitamin deficiency causes scurvy?', 'multiple_choice', 'Vitamin A', 'Vitamin B12', 'Vitamin C', 'Vitamin D', 'C', 1, 'Scurvy is caused by severe vitamin C deficiency.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(417, 7, 6, 'What is herd immunity?', 'multiple_choice', 'Immunity in animals', 'Individual immunity', 'Population-level immunity', 'Temporary immunity', 'C', 1, 'Herd immunity occurs when enough people are immune to prevent disease spread.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(418, 6, 6, 'Malaria is transmitted by mosquitoes.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Malaria is transmitted through the bite of infected Anopheles mosquitoes.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(419, 7, 6, 'Incidence refers to existing cases of disease in a population.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Incidence refers to new cases; prevalence refers to existing cases.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(420, 6, 6, 'Clean water access is a social determinant of health.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Access to clean water significantly impacts population health outcomes.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(421, 7, 6, 'An epidemic affects multiple countries or continents.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'An epidemic is widespread in one region; a pandemic affects multiple countries or continents.', 4, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(422, 8, 8, 'What is the ASA classification system used for?', 'multiple_choice', 'Anesthesia dosing', 'Patient physical status', 'Surgery duration', 'Recovery time', 'B', 1, 'ASA classification assesses patient physical status before anesthesia.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(423, 8, 8, 'Which drug is commonly used for induction of general anesthesia?', 'multiple_choice', 'Aspirin', 'Propofol', 'Insulin', 'Warfarin', 'B', 1, 'Propofol is a commonly used induction agent for general anesthesia.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(424, 9, 4, 'What is the antidote for opioid overdose?', 'multiple_choice', 'Epinephrine', 'Naloxone', 'Atropine', 'Dopamine', 'B', 1, 'Naloxone (Narcan) reverses opioid overdose effects.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(425, 8, 8, 'What does MAC stand for in anesthesia?', 'multiple_choice', 'Maximum Anesthesia Concentration', 'Monitored Anesthesia Care', 'Minimal Airway Control', 'Medical Anesthesia Certification', 'B', 1, 'MAC is Monitored Anesthesia Care, a type of sedation.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(426, 9, 4, 'Which gas is commonly used for general anesthesia?', 'multiple_choice', 'Oxygen', 'Sevoflurane', 'Carbon dioxide', 'Nitrogen', 'B', 1, 'Sevoflurane is a volatile anesthetic gas used for general anesthesia.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(427, 8, 8, 'Spinal anesthesia is a type of regional anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Spinal anesthesia blocks nerve transmission in a specific region of the body.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(428, 9, 9, 'Patients should fast before general anesthesia to prevent aspiration.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting reduces the risk of aspiration of stomach contents during anesthesia.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(429, 8, 8, 'Epidural anesthesia is commonly used for cesarean sections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Epidural anesthesia provides effective pain relief for cesarean deliveries.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(430, 9, 4, 'Atropine is used to increase heart rate.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Atropine is an anticholinergic drug that increases heart rate.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(431, 8, 9, 'Capnography measures carbon dioxide levels in exhaled breath.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Capnography monitors CO2 levels and is essential during anesthesia.', 5, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(432, 10, 7, 'What is the normal range for fasting blood glucose?', 'multiple_choice', '50-70 mg/dL', '70-100 mg/dL', '120-150 mg/dL', '180-200 mg/dL', 'B', 1, 'Normal fasting blood glucose is 70-100 mg/dL.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(433, 10, 7, 'Which test measures kidney function?', 'multiple_choice', 'Hemoglobin A1C', 'Creatinine', 'Lipid panel', 'Liver enzymes', 'B', 1, 'Serum creatinine is a key indicator of kidney function.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(434, 11, 7, 'What is anemia?', 'multiple_choice', 'High white blood cell count', 'Low red blood cell count', 'High platelet count', 'Low glucose level', 'B', 1, 'Anemia is a condition with low red blood cells or hemoglobin.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(435, 10, 7, 'What does HbA1c measure?', 'multiple_choice', 'Current blood sugar', 'Average blood sugar over 3 months', 'Kidney function', 'Liver function', 'B', 1, 'HbA1c reflects average blood glucose levels over 2-3 months.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(436, 11, 7, 'What is the normal white blood cell count?', 'multiple_choice', '1,000-3,000 cells/μL', '4,000-11,000 cells/μL', '15,000-20,000 cells/μL', '25,000-30,000 cells/μL', 'B', 1, 'Normal WBC count is 4,000-11,000 cells per microliter.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(437, 10, 7, 'Cholesterol levels should be checked while fasting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting lipid panels provide more accurate cholesterol measurements.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(438, 11, 7, 'Platelets are responsible for blood clotting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Platelets (thrombocytes) play a crucial role in blood clotting.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(439, 10, 7, 'Urine should be tested within 2 hours of collection.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Urine samples should be tested promptly to ensure accurate results.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(440, 11, 7, 'A complete blood count (CBC) includes hemoglobin, WBC, and platelet counts.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'CBC is a comprehensive blood test that measures multiple blood components.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(441, 10, 7, 'Elevated liver enzymes always indicate liver disease.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Elevated liver enzymes can have various causes and require further investigation.', 6, '2026-02-08 17:37:34', '2026-02-08 17:37:34'),
(442, 1, 1, 'What is the primary goal of nursing care?', 'multiple_choice', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(443, 1, 1, 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'multiple_choice', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(444, 1, 9, 'What is the correct order for hand hygiene?', 'multiple_choice', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(445, 1, 9, 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(446, 1, 9, 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(447, 1, 1, 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(448, 2, 2, 'Which organ is responsible for pumping blood throughout the body?', 'multiple_choice', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(449, 2, 2, 'How many chambers does the human heart have?', 'multiple_choice', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(450, 2, 3, 'What is the normal resting heart rate for adults?', 'multiple_choice', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(451, 2, 2, 'The human body has 206 bones in the adult skeleton.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(452, 2, 3, 'The liver is located in the left upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(453, 2, 2, 'The skin is the largest organ in the human body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(454, 4, 5, 'What is the normal duration of pregnancy?', 'multiple_choice', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(455, 4, 5, 'Which trimester is considered the most critical for fetal development?', 'multiple_choice', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(456, 4, 5, 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(457, 4, 5, 'Fetal movements should be felt by the mother starting from the first trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(458, 4, 5, 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(459, 6, 6, 'What is the primary focus of public health?', 'multiple_choice', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(460, 6, 6, 'Which of the following is a communicable disease?', 'multiple_choice', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(461, 6, 6, 'Vaccination is one of the most cost-effective public health interventions.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(462, 6, 6, 'Antibiotics are effective against viral infections like the common cold.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(463, 6, 6, 'Hand washing is one of the most effective ways to prevent disease transmission.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(464, 8, 8, 'What is the primary purpose of anesthesia?', 'multiple_choice', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(465, 8, 8, 'Which type of anesthesia affects the entire body?', 'multiple_choice', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(466, 8, 8, 'General anesthesia causes complete loss of consciousness.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(467, 8, 8, 'Local anesthesia affects the entire body.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(468, 8, 9, 'Oxygen saturation must be continuously monitored during anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(469, 10, 7, 'What is the normal pH range of human blood?', 'multiple_choice', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(470, 10, 7, 'Which blood type is considered the universal donor?', 'multiple_choice', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(471, 10, 7, 'Blood type O negative is considered the universal donor.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(472, 10, 7, 'Hemoglobin is found in white blood cells.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(473, 10, 7, 'Red blood cells are responsible for transporting oxygen throughout the body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(474, 1, 1, 'What does the acronym ADPIE stand for in the nursing process?', 'multiple_choice', 'Assess, Diagnose, Plan, Implement, Evaluate', 'Analyze, Develop, Perform, Inspect, Execute', 'Admit, Discharge, Prescribe, Inject, Examine', 'Advise, Direct, Prepare, Intervene, Exit', 'A', 1, 'ADPIE represents the five steps of the nursing process.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(475, 1, 9, 'How long should you scrub your hands during hand washing?', 'multiple_choice', '5 seconds', '10 seconds', '20 seconds', '60 seconds', 'C', 1, 'Proper hand washing requires at least 20 seconds of scrubbing.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(476, 1, 1, 'Which vital sign is measured in beats per minute?', 'multiple_choice', 'Temperature', 'Blood pressure', 'Pulse', 'Respiratory rate', 'C', 1, 'Pulse is measured in beats per minute (bpm).', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(477, 1, 9, 'What is the proper angle for intramuscular injection?', 'multiple_choice', '15 degrees', '45 degrees', '90 degrees', '180 degrees', 'C', 1, 'Intramuscular injections are given at a 90-degree angle.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(478, 1, 1, 'What is the normal body temperature in Celsius?', 'multiple_choice', '35.5°C', '37°C', '38.5°C', '40°C', 'B', 1, 'Normal body temperature is approximately 37°C or 98.6°F.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(479, 1, 9, 'Nurses should always identify patients using two identifiers before administering medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Two patient identifiers (name and date of birth) are required for patient safety.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(480, 1, 1, 'Documentation in nursing should be done at the end of the shift.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Documentation should be done immediately after care is provided to ensure accuracy.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(481, 1, 9, 'Standard precautions should be used with all patients regardless of diagnosis.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standard precautions are infection control practices used with all patients.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(482, 1, 1, 'Nurses can delegate assessment tasks to unlicensed assistive personnel.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Assessment is a nursing responsibility that cannot be delegated to unlicensed personnel.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(483, 1, 9, 'Gloves should be changed between tasks on the same patient.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Gloves should be changed to prevent cross-contamination between different body sites.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(484, 2, 2, 'Which bone protects the brain?', 'multiple_choice', 'Femur', 'Skull', 'Ribs', 'Pelvis', 'B', 1, 'The skull (cranium) protects the brain.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(485, 2, 3, 'What is the largest artery in the human body?', 'multiple_choice', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 1, 'The aorta is the largest artery, carrying blood from the heart to the body.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(486, 2, 2, 'How many pairs of ribs does a human have?', 'multiple_choice', '10', '12', '14', '16', 'B', 1, 'Humans have 12 pairs of ribs (24 ribs total).', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(487, 2, 3, 'Which organ produces insulin?', 'multiple_choice', 'Liver', 'Pancreas', 'Kidney', 'Spleen', 'B', 1, 'The pancreas produces insulin to regulate blood sugar.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(488, 2, 2, 'What is the longest bone in the human body?', 'multiple_choice', 'Humerus', 'Tibia', 'Femur', 'Radius', 'C', 1, 'The femur (thigh bone) is the longest and strongest bone.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(489, 2, 3, 'The lungs are located in the thoracic cavity.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The lungs are housed in the thoracic (chest) cavity, protected by the rib cage.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(490, 2, 2, 'The human spine has 33 vertebrae.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The spine consists of 33 vertebrae: 7 cervical, 12 thoracic, 5 lumbar, 5 sacral (fused), and 4 coccygeal (fused).', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(491, 2, 3, 'The kidneys filter approximately 180 liters of blood per day.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The kidneys filter about 180 liters of blood daily, producing 1-2 liters of urine.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(492, 2, 2, 'Cartilage is a type of connective tissue.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Cartilage is a flexible connective tissue found in joints, ears, nose, and other structures.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(493, 2, 3, 'The stomach is located in the right upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The stomach is primarily located in the left upper quadrant of the abdomen.', 1, '2026-02-08 17:42:15', '2026-02-08 17:42:15'),
(494, 3, 4, 'What is the antidote for warfarin overdose?', 'multiple_choice', 'Protamine sulfate', 'Vitamin K', 'Naloxone', 'Flumazenil', 'B', 1, 'Vitamin K is the antidote for warfarin (Coumadin) overdose.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(495, 3, 9, 'What is the priority nursing action for a patient with chest pain?', 'multiple_choice', 'Document the pain', 'Administer oxygen', 'Call the family', 'Ambulate the patient', 'B', 1, 'Administering oxygen is priority to improve cardiac oxygenation.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(496, 3, 4, 'Which medication is used to treat hypertension?', 'multiple_choice', 'Insulin', 'Lisinopril', 'Aspirin', 'Metformin', 'B', 1, 'Lisinopril is an ACE inhibitor used to treat high blood pressure.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(497, 3, 9, 'What is the normal range for adult blood pressure?', 'multiple_choice', '90/60 to 120/80 mmHg', '130/90 to 150/100 mmHg', '160/100 to 180/110 mmHg', '200/120 to 220/130 mmHg', 'A', 1, 'Normal blood pressure is less than 120/80 mmHg.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(498, 3, 4, 'Which lab value indicates kidney function?', 'multiple_choice', 'Hemoglobin', 'Creatinine', 'Glucose', 'Cholesterol', 'B', 1, 'Creatinine levels indicate how well the kidneys are filtering waste.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(499, 3, 9, 'Patients with diabetes should skip meals if their blood sugar is high.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Diabetic patients should maintain regular meal schedules and work with healthcare providers to adjust medications.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(500, 3, 4, 'Aspirin is an anticoagulant medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Aspirin inhibits platelet aggregation and acts as an anticoagulant.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(501, 3, 9, 'A patient with a myocardial infarction should be kept on bed rest initially.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Initial bed rest reduces cardiac workload and oxygen demand after a heart attack.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(502, 3, 4, 'Antibiotics are effective against all types of infections.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(503, 3, 9, 'Patients should be assessed for pain using a standardized pain scale.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standardized pain scales ensure consistent and accurate pain assessment.', 1, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(504, 4, 5, 'What is the first stage of labor?', 'multiple_choice', 'Delivery of the baby', 'Cervical dilation', 'Delivery of placenta', 'Recovery', 'B', 1, 'The first stage of labor involves cervical dilation from 0 to 10 cm.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(505, 4, 5, 'At what week is a fetus considered full-term?', 'multiple_choice', '32 weeks', '35 weeks', '37 weeks', '42 weeks', 'C', 1, 'A pregnancy is considered full-term at 37 weeks.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(506, 4, 5, 'What is the normal fetal heart rate?', 'multiple_choice', '60-80 bpm', '80-100 bpm', '110-160 bpm', '180-200 bpm', 'C', 1, 'Normal fetal heart rate is 110-160 beats per minute.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(507, 4, 5, 'Which hormone maintains pregnancy?', 'multiple_choice', 'Estrogen', 'Progesterone', 'Testosterone', 'Insulin', 'B', 1, 'Progesterone is essential for maintaining pregnancy.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(508, 4, 5, 'What is the recommended weight gain during pregnancy for normal BMI?', 'multiple_choice', '5-10 kg', '11-16 kg', '20-25 kg', '30-35 kg', 'B', 1, 'Recommended weight gain for normal BMI is 11-16 kg (25-35 lbs).', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(509, 5, 5, 'What is postpartum hemorrhage defined as?', 'multiple_choice', 'Blood loss >500 ml after vaginal delivery', 'Blood loss >100 ml after delivery', 'Blood loss >200 ml after delivery', 'Any bleeding after delivery', 'A', 1, 'Postpartum hemorrhage is blood loss exceeding 500 ml after vaginal delivery.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(510, 4, 5, 'Prenatal care should begin in the second trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Prenatal care should ideally begin as soon as pregnancy is confirmed, in the first trimester.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(511, 4, 5, 'Breastfeeding should be initiated within the first hour after birth.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Early initiation of breastfeeding promotes bonding and provides important antibodies to the newborn.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(512, 5, 5, 'The umbilical cord contains two arteries and one vein.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The umbilical cord normally contains two arteries and one vein.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(513, 4, 5, 'Morning sickness only occurs in the morning.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Despite its name, morning sickness can occur at any time of day.', 3, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(514, 6, 6, 'What does WHO stand for?', 'multiple_choice', 'World Health Office', 'World Health Organization', 'Worldwide Health Operations', 'World Hospital Organization', 'B', 1, 'WHO is the World Health Organization, a UN agency for public health.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(515, 6, 6, 'Which disease was eradicated globally through vaccination?', 'multiple_choice', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 1, 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(516, 6, 6, 'What is the leading cause of death worldwide?', 'multiple_choice', 'Cancer', 'Cardiovascular disease', 'Respiratory infections', 'Accidents', 'B', 1, 'Cardiovascular diseases are the leading cause of death globally.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(517, 7, 6, 'What is the basic reproduction number (R0) in epidemiology?', 'multiple_choice', 'Number of deaths', 'Number of new infections from one case', 'Number of recovered patients', 'Number of vaccinated individuals', 'B', 1, 'R0 represents the average number of people infected by one contagious person.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(518, 6, 6, 'Which vitamin deficiency causes scurvy?', 'multiple_choice', 'Vitamin A', 'Vitamin B12', 'Vitamin C', 'Vitamin D', 'C', 1, 'Scurvy is caused by severe vitamin C deficiency.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(519, 7, 6, 'What is herd immunity?', 'multiple_choice', 'Immunity in animals', 'Individual immunity', 'Population-level immunity', 'Temporary immunity', 'C', 1, 'Herd immunity occurs when enough people are immune to prevent disease spread.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(520, 6, 6, 'Malaria is transmitted by mosquitoes.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Malaria is transmitted through the bite of infected Anopheles mosquitoes.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(521, 7, 6, 'Incidence refers to existing cases of disease in a population.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Incidence refers to new cases; prevalence refers to existing cases.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(522, 6, 6, 'Clean water access is a social determinant of health.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Access to clean water significantly impacts population health outcomes.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(523, 7, 6, 'An epidemic affects multiple countries or continents.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'An epidemic is widespread in one region; a pandemic affects multiple countries or continents.', 4, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(524, 8, 8, 'What is the ASA classification system used for?', 'multiple_choice', 'Anesthesia dosing', 'Patient physical status', 'Surgery duration', 'Recovery time', 'B', 1, 'ASA classification assesses patient physical status before anesthesia.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(525, 8, 8, 'Which drug is commonly used for induction of general anesthesia?', 'multiple_choice', 'Aspirin', 'Propofol', 'Insulin', 'Warfarin', 'B', 1, 'Propofol is a commonly used induction agent for general anesthesia.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(526, 9, 4, 'What is the antidote for opioid overdose?', 'multiple_choice', 'Epinephrine', 'Naloxone', 'Atropine', 'Dopamine', 'B', 1, 'Naloxone (Narcan) reverses opioid overdose effects.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(527, 8, 8, 'What does MAC stand for in anesthesia?', 'multiple_choice', 'Maximum Anesthesia Concentration', 'Monitored Anesthesia Care', 'Minimal Airway Control', 'Medical Anesthesia Certification', 'B', 1, 'MAC is Monitored Anesthesia Care, a type of sedation.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(528, 9, 4, 'Which gas is commonly used for general anesthesia?', 'multiple_choice', 'Oxygen', 'Sevoflurane', 'Carbon dioxide', 'Nitrogen', 'B', 1, 'Sevoflurane is a volatile anesthetic gas used for general anesthesia.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(529, 8, 8, 'Spinal anesthesia is a type of regional anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Spinal anesthesia blocks nerve transmission in a specific region of the body.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(530, 9, 9, 'Patients should fast before general anesthesia to prevent aspiration.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting reduces the risk of aspiration of stomach contents during anesthesia.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(531, 8, 8, 'Epidural anesthesia is commonly used for cesarean sections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Epidural anesthesia provides effective pain relief for cesarean deliveries.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(532, 9, 4, 'Atropine is used to increase heart rate.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Atropine is an anticholinergic drug that increases heart rate.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(533, 8, 9, 'Capnography measures carbon dioxide levels in exhaled breath.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Capnography monitors CO2 levels and is essential during anesthesia.', 5, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(534, 10, 7, 'What is the normal range for fasting blood glucose?', 'multiple_choice', '50-70 mg/dL', '70-100 mg/dL', '120-150 mg/dL', '180-200 mg/dL', 'B', 1, 'Normal fasting blood glucose is 70-100 mg/dL.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(535, 10, 7, 'Which test measures kidney function?', 'multiple_choice', 'Hemoglobin A1C', 'Creatinine', 'Lipid panel', 'Liver enzymes', 'B', 1, 'Serum creatinine is a key indicator of kidney function.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(536, 11, 7, 'What is anemia?', 'multiple_choice', 'High white blood cell count', 'Low red blood cell count', 'High platelet count', 'Low glucose level', 'B', 1, 'Anemia is a condition with low red blood cells or hemoglobin.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(537, 10, 7, 'What does HbA1c measure?', 'multiple_choice', 'Current blood sugar', 'Average blood sugar over 3 months', 'Kidney function', 'Liver function', 'B', 1, 'HbA1c reflects average blood glucose levels over 2-3 months.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(538, 11, 7, 'What is the normal white blood cell count?', 'multiple_choice', '1,000-3,000 cells/μL', '4,000-11,000 cells/μL', '15,000-20,000 cells/μL', '25,000-30,000 cells/μL', 'B', 1, 'Normal WBC count is 4,000-11,000 cells per microliter.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(539, 10, 7, 'Cholesterol levels should be checked while fasting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting lipid panels provide more accurate cholesterol measurements.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(540, 11, 7, 'Platelets are responsible for blood clotting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Platelets (thrombocytes) play a crucial role in blood clotting.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(541, 10, 7, 'Urine should be tested within 2 hours of collection.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Urine samples should be tested promptly to ensure accurate results.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16');
INSERT INTO `questions` (`question_id`, `course_id`, `topic_id`, `question_text`, `question_type`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`, `created_at`, `updated_at`) VALUES
(542, 11, 7, 'A complete blood count (CBC) includes hemoglobin, WBC, and platelet counts.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'CBC is a comprehensive blood test that measures multiple blood components.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(543, 10, 7, 'Elevated liver enzymes always indicate liver disease.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Elevated liver enzymes can have various causes and require further investigation.', 6, '2026-02-08 17:42:16', '2026-02-08 17:42:16'),
(544, 1, 1, 'What is the primary goal of nursing care?', 'multiple_choice', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1, '2026-02-08 17:44:39', '2026-02-08 17:44:39'),
(545, 1, 1, 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'multiple_choice', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1, '2026-02-08 17:44:39', '2026-02-08 17:44:39'),
(546, 1, 9, 'What is the correct order for hand hygiene?', 'multiple_choice', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1, '2026-02-08 17:44:39', '2026-02-08 17:44:39'),
(547, 1, 9, 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1, '2026-02-08 17:44:39', '2026-02-08 17:44:39'),
(548, 1, 9, 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1, '2026-02-08 17:44:39', '2026-02-08 17:44:39'),
(549, 1, 1, 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1, '2026-02-08 17:44:39', '2026-02-08 17:44:39'),
(550, 2, 2, 'Which organ is responsible for pumping blood throughout the body?', 'multiple_choice', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(551, 2, 2, 'How many chambers does the human heart have?', 'multiple_choice', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(552, 2, 3, 'What is the normal resting heart rate for adults?', 'multiple_choice', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(553, 2, 2, 'The human body has 206 bones in the adult skeleton.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(554, 2, 3, 'The liver is located in the left upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(555, 2, 2, 'The skin is the largest organ in the human body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(556, 4, 5, 'What is the normal duration of pregnancy?', 'multiple_choice', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(557, 4, 5, 'Which trimester is considered the most critical for fetal development?', 'multiple_choice', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(558, 4, 5, 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(559, 4, 5, 'Fetal movements should be felt by the mother starting from the first trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(560, 4, 5, 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(561, 6, 6, 'What is the primary focus of public health?', 'multiple_choice', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(562, 6, 6, 'Which of the following is a communicable disease?', 'multiple_choice', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(563, 6, 6, 'Vaccination is one of the most cost-effective public health interventions.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(564, 6, 6, 'Antibiotics are effective against viral infections like the common cold.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(565, 6, 6, 'Hand washing is one of the most effective ways to prevent disease transmission.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(566, 8, 8, 'What is the primary purpose of anesthesia?', 'multiple_choice', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(567, 8, 8, 'Which type of anesthesia affects the entire body?', 'multiple_choice', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(568, 8, 8, 'General anesthesia causes complete loss of consciousness.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(569, 8, 8, 'Local anesthesia affects the entire body.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(570, 8, 9, 'Oxygen saturation must be continuously monitored during anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(571, 10, 7, 'What is the normal pH range of human blood?', 'multiple_choice', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(572, 10, 7, 'Which blood type is considered the universal donor?', 'multiple_choice', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(573, 10, 7, 'Blood type O negative is considered the universal donor.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(574, 10, 7, 'Hemoglobin is found in white blood cells.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(575, 10, 7, 'Red blood cells are responsible for transporting oxygen throughout the body.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(576, 1, 1, 'What does the acronym ADPIE stand for in the nursing process?', 'multiple_choice', 'Assess, Diagnose, Plan, Implement, Evaluate', 'Analyze, Develop, Perform, Inspect, Execute', 'Admit, Discharge, Prescribe, Inject, Examine', 'Advise, Direct, Prepare, Intervene, Exit', 'A', 1, 'ADPIE represents the five steps of the nursing process.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(577, 1, 9, 'How long should you scrub your hands during hand washing?', 'multiple_choice', '5 seconds', '10 seconds', '20 seconds', '60 seconds', 'C', 1, 'Proper hand washing requires at least 20 seconds of scrubbing.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(578, 1, 1, 'Which vital sign is measured in beats per minute?', 'multiple_choice', 'Temperature', 'Blood pressure', 'Pulse', 'Respiratory rate', 'C', 1, 'Pulse is measured in beats per minute (bpm).', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(579, 1, 9, 'What is the proper angle for intramuscular injection?', 'multiple_choice', '15 degrees', '45 degrees', '90 degrees', '180 degrees', 'C', 1, 'Intramuscular injections are given at a 90-degree angle.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(580, 1, 1, 'What is the normal body temperature in Celsius?', 'multiple_choice', '35.5°C', '37°C', '38.5°C', '40°C', 'B', 1, 'Normal body temperature is approximately 37°C or 98.6°F.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(581, 1, 9, 'Nurses should always identify patients using two identifiers before administering medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Two patient identifiers (name and date of birth) are required for patient safety.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(582, 1, 1, 'Documentation in nursing should be done at the end of the shift.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Documentation should be done immediately after care is provided to ensure accuracy.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(583, 1, 9, 'Standard precautions should be used with all patients regardless of diagnosis.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standard precautions are infection control practices used with all patients.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(584, 1, 1, 'Nurses can delegate assessment tasks to unlicensed assistive personnel.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Assessment is a nursing responsibility that cannot be delegated to unlicensed personnel.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(585, 1, 9, 'Gloves should be changed between tasks on the same patient.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Gloves should be changed to prevent cross-contamination between different body sites.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(586, 2, 2, 'Which bone protects the brain?', 'multiple_choice', 'Femur', 'Skull', 'Ribs', 'Pelvis', 'B', 1, 'The skull (cranium) protects the brain.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(587, 2, 3, 'What is the largest artery in the human body?', 'multiple_choice', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 1, 'The aorta is the largest artery, carrying blood from the heart to the body.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(588, 2, 2, 'How many pairs of ribs does a human have?', 'multiple_choice', '10', '12', '14', '16', 'B', 1, 'Humans have 12 pairs of ribs (24 ribs total).', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(589, 2, 3, 'Which organ produces insulin?', 'multiple_choice', 'Liver', 'Pancreas', 'Kidney', 'Spleen', 'B', 1, 'The pancreas produces insulin to regulate blood sugar.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(590, 2, 2, 'What is the longest bone in the human body?', 'multiple_choice', 'Humerus', 'Tibia', 'Femur', 'Radius', 'C', 1, 'The femur (thigh bone) is the longest and strongest bone.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(591, 2, 3, 'The lungs are located in the thoracic cavity.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The lungs are housed in the thoracic (chest) cavity, protected by the rib cage.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(592, 2, 2, 'The human spine has 33 vertebrae.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The spine consists of 33 vertebrae: 7 cervical, 12 thoracic, 5 lumbar, 5 sacral (fused), and 4 coccygeal (fused).', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(593, 2, 3, 'The kidneys filter approximately 180 liters of blood per day.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The kidneys filter about 180 liters of blood daily, producing 1-2 liters of urine.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(594, 2, 2, 'Cartilage is a type of connective tissue.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Cartilage is a flexible connective tissue found in joints, ears, nose, and other structures.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(595, 2, 3, 'The stomach is located in the right upper quadrant of the abdomen.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'The stomach is primarily located in the left upper quadrant of the abdomen.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(596, 3, 4, 'What is the antidote for warfarin overdose?', 'multiple_choice', 'Protamine sulfate', 'Vitamin K', 'Naloxone', 'Flumazenil', 'B', 1, 'Vitamin K is the antidote for warfarin (Coumadin) overdose.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(597, 3, 9, 'What is the priority nursing action for a patient with chest pain?', 'multiple_choice', 'Document the pain', 'Administer oxygen', 'Call the family', 'Ambulate the patient', 'B', 1, 'Administering oxygen is priority to improve cardiac oxygenation.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(598, 3, 4, 'Which medication is used to treat hypertension?', 'multiple_choice', 'Insulin', 'Lisinopril', 'Aspirin', 'Metformin', 'B', 1, 'Lisinopril is an ACE inhibitor used to treat high blood pressure.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(599, 3, 9, 'What is the normal range for adult blood pressure?', 'multiple_choice', '90/60 to 120/80 mmHg', '130/90 to 150/100 mmHg', '160/100 to 180/110 mmHg', '200/120 to 220/130 mmHg', 'A', 1, 'Normal blood pressure is less than 120/80 mmHg.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(600, 3, 4, 'Which lab value indicates kidney function?', 'multiple_choice', 'Hemoglobin', 'Creatinine', 'Glucose', 'Cholesterol', 'B', 1, 'Creatinine levels indicate how well the kidneys are filtering waste.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(601, 3, 9, 'Patients with diabetes should skip meals if their blood sugar is high.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Diabetic patients should maintain regular meal schedules and work with healthcare providers to adjust medications.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(602, 3, 4, 'Aspirin is an anticoagulant medication.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Aspirin inhibits platelet aggregation and acts as an anticoagulant.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(603, 3, 9, 'A patient with a myocardial infarction should be kept on bed rest initially.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Initial bed rest reduces cardiac workload and oxygen demand after a heart attack.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(604, 3, 4, 'Antibiotics are effective against all types of infections.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(605, 3, 9, 'Patients should be assessed for pain using a standardized pain scale.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Standardized pain scales ensure consistent and accurate pain assessment.', 1, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(606, 4, 5, 'What is the first stage of labor?', 'multiple_choice', 'Delivery of the baby', 'Cervical dilation', 'Delivery of placenta', 'Recovery', 'B', 1, 'The first stage of labor involves cervical dilation from 0 to 10 cm.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(607, 4, 5, 'At what week is a fetus considered full-term?', 'multiple_choice', '32 weeks', '35 weeks', '37 weeks', '42 weeks', 'C', 1, 'A pregnancy is considered full-term at 37 weeks.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(608, 4, 5, 'What is the normal fetal heart rate?', 'multiple_choice', '60-80 bpm', '80-100 bpm', '110-160 bpm', '180-200 bpm', 'C', 1, 'Normal fetal heart rate is 110-160 beats per minute.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(609, 4, 5, 'Which hormone maintains pregnancy?', 'multiple_choice', 'Estrogen', 'Progesterone', 'Testosterone', 'Insulin', 'B', 1, 'Progesterone is essential for maintaining pregnancy.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(610, 4, 5, 'What is the recommended weight gain during pregnancy for normal BMI?', 'multiple_choice', '5-10 kg', '11-16 kg', '20-25 kg', '30-35 kg', 'B', 1, 'Recommended weight gain for normal BMI is 11-16 kg (25-35 lbs).', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(611, 5, 5, 'What is postpartum hemorrhage defined as?', 'multiple_choice', 'Blood loss >500 ml after vaginal delivery', 'Blood loss >100 ml after delivery', 'Blood loss >200 ml after delivery', 'Any bleeding after delivery', 'A', 1, 'Postpartum hemorrhage is blood loss exceeding 500 ml after vaginal delivery.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(612, 4, 5, 'Prenatal care should begin in the second trimester.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Prenatal care should ideally begin as soon as pregnancy is confirmed, in the first trimester.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(613, 4, 5, 'Breastfeeding should be initiated within the first hour after birth.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Early initiation of breastfeeding promotes bonding and provides important antibodies to the newborn.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(614, 5, 5, 'The umbilical cord contains two arteries and one vein.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'The umbilical cord normally contains two arteries and one vein.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(615, 4, 5, 'Morning sickness only occurs in the morning.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Despite its name, morning sickness can occur at any time of day.', 3, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(616, 6, 6, 'What does WHO stand for?', 'multiple_choice', 'World Health Office', 'World Health Organization', 'Worldwide Health Operations', 'World Hospital Organization', 'B', 1, 'WHO is the World Health Organization, a UN agency for public health.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(617, 6, 6, 'Which disease was eradicated globally through vaccination?', 'multiple_choice', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 1, 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(618, 6, 6, 'What is the leading cause of death worldwide?', 'multiple_choice', 'Cancer', 'Cardiovascular disease', 'Respiratory infections', 'Accidents', 'B', 1, 'Cardiovascular diseases are the leading cause of death globally.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(619, 7, 6, 'What is the basic reproduction number (R0) in epidemiology?', 'multiple_choice', 'Number of deaths', 'Number of new infections from one case', 'Number of recovered patients', 'Number of vaccinated individuals', 'B', 1, 'R0 represents the average number of people infected by one contagious person.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(620, 6, 6, 'Which vitamin deficiency causes scurvy?', 'multiple_choice', 'Vitamin A', 'Vitamin B12', 'Vitamin C', 'Vitamin D', 'C', 1, 'Scurvy is caused by severe vitamin C deficiency.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(621, 7, 6, 'What is herd immunity?', 'multiple_choice', 'Immunity in animals', 'Individual immunity', 'Population-level immunity', 'Temporary immunity', 'C', 1, 'Herd immunity occurs when enough people are immune to prevent disease spread.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(622, 6, 6, 'Malaria is transmitted by mosquitoes.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Malaria is transmitted through the bite of infected Anopheles mosquitoes.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(623, 7, 6, 'Incidence refers to existing cases of disease in a population.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Incidence refers to new cases; prevalence refers to existing cases.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(624, 6, 6, 'Clean water access is a social determinant of health.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Access to clean water significantly impacts population health outcomes.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(625, 7, 6, 'An epidemic affects multiple countries or continents.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'An epidemic is widespread in one region; a pandemic affects multiple countries or continents.', 4, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(626, 8, 8, 'What is the ASA classification system used for?', 'multiple_choice', 'Anesthesia dosing', 'Patient physical status', 'Surgery duration', 'Recovery time', 'B', 1, 'ASA classification assesses patient physical status before anesthesia.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(627, 8, 8, 'Which drug is commonly used for induction of general anesthesia?', 'multiple_choice', 'Aspirin', 'Propofol', 'Insulin', 'Warfarin', 'B', 1, 'Propofol is a commonly used induction agent for general anesthesia.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(628, 9, 4, 'What is the antidote for opioid overdose?', 'multiple_choice', 'Epinephrine', 'Naloxone', 'Atropine', 'Dopamine', 'B', 1, 'Naloxone (Narcan) reverses opioid overdose effects.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(629, 8, 8, 'What does MAC stand for in anesthesia?', 'multiple_choice', 'Maximum Anesthesia Concentration', 'Monitored Anesthesia Care', 'Minimal Airway Control', 'Medical Anesthesia Certification', 'B', 1, 'MAC is Monitored Anesthesia Care, a type of sedation.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(630, 9, 4, 'Which gas is commonly used for general anesthesia?', 'multiple_choice', 'Oxygen', 'Sevoflurane', 'Carbon dioxide', 'Nitrogen', 'B', 1, 'Sevoflurane is a volatile anesthetic gas used for general anesthesia.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(631, 8, 8, 'Spinal anesthesia is a type of regional anesthesia.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Spinal anesthesia blocks nerve transmission in a specific region of the body.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(632, 9, 9, 'Patients should fast before general anesthesia to prevent aspiration.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting reduces the risk of aspiration of stomach contents during anesthesia.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(633, 8, 8, 'Epidural anesthesia is commonly used for cesarean sections.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Epidural anesthesia provides effective pain relief for cesarean deliveries.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(634, 9, 4, 'Atropine is used to increase heart rate.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Atropine is an anticholinergic drug that increases heart rate.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(635, 8, 9, 'Capnography measures carbon dioxide levels in exhaled breath.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Capnography monitors CO2 levels and is essential during anesthesia.', 5, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(636, 10, 7, 'What is the normal range for fasting blood glucose?', 'multiple_choice', '50-70 mg/dL', '70-100 mg/dL', '120-150 mg/dL', '180-200 mg/dL', 'B', 1, 'Normal fasting blood glucose is 70-100 mg/dL.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(637, 10, 7, 'Which test measures kidney function?', 'multiple_choice', 'Hemoglobin A1C', 'Creatinine', 'Lipid panel', 'Liver enzymes', 'B', 1, 'Serum creatinine is a key indicator of kidney function.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(638, 11, 7, 'What is anemia?', 'multiple_choice', 'High white blood cell count', 'Low red blood cell count', 'High platelet count', 'Low glucose level', 'B', 1, 'Anemia is a condition with low red blood cells or hemoglobin.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(639, 10, 7, 'What does HbA1c measure?', 'multiple_choice', 'Current blood sugar', 'Average blood sugar over 3 months', 'Kidney function', 'Liver function', 'B', 1, 'HbA1c reflects average blood glucose levels over 2-3 months.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(640, 11, 7, 'What is the normal white blood cell count?', 'multiple_choice', '1,000-3,000 cells/μL', '4,000-11,000 cells/μL', '15,000-20,000 cells/μL', '25,000-30,000 cells/μL', 'B', 1, 'Normal WBC count is 4,000-11,000 cells per microliter.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(641, 10, 7, 'Cholesterol levels should be checked while fasting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting lipid panels provide more accurate cholesterol measurements.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(642, 11, 7, 'Platelets are responsible for blood clotting.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Platelets (thrombocytes) play a crucial role in blood clotting.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(643, 10, 7, 'Urine should be tested within 2 hours of collection.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'Urine samples should be tested promptly to ensure accurate results.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(644, 11, 7, 'A complete blood count (CBC) includes hemoglobin, WBC, and platelet counts.', 'true_false', 'True', 'False', NULL, NULL, 'True', 1, 'CBC is a comprehensive blood test that measures multiple blood components.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40'),
(645, 10, 7, 'Elevated liver enzymes always indicate liver disease.', 'true_false', 'True', 'False', NULL, NULL, 'False', 1, 'Elevated liver enzymes can have various causes and require further investigation.', 6, '2026-02-08 17:44:40', '2026-02-08 17:44:40');

-- --------------------------------------------------------

--
-- Table structure for table `question_topics`
--

CREATE TABLE `question_topics` (
  `topic_id` int(11) NOT NULL,
  `topic_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_topics`
--

INSERT INTO `question_topics` (`topic_id`, `topic_name`, `description`, `created_at`) VALUES
(1, 'Nursing Fundamentals', 'Basic nursing concepts and skills', '2026-02-06 11:10:22'),
(2, 'Anatomy', 'Human body structure', '2026-02-06 11:10:22'),
(3, 'Physiology', 'Body functions and systems', '2026-02-06 11:10:22'),
(4, 'Pharmacology', 'Drug therapy and medications', '2026-02-06 11:10:22'),
(5, 'Maternal Health', 'Pregnancy and childbirth', '2026-02-06 11:10:22'),
(6, 'Public Health', 'Community health concepts', '2026-02-06 11:10:22'),
(7, 'Laboratory Techniques', 'Lab procedures and tests', '2026-02-06 11:10:22'),
(8, 'Anesthesia Basics', 'Anesthesia principles', '2026-02-06 11:10:22'),
(9, 'Patient Safety', 'Safety protocols and procedures', '2026-02-06 11:10:22'),
(10, 'Nursing Fundamentals', 'Basic nursing concepts and skills', '2026-02-08 16:53:47'),
(11, 'Anatomy', 'Human body structure', '2026-02-08 16:53:47'),
(12, 'Physiology', 'Body functions and systems', '2026-02-08 16:53:47'),
(13, 'Pharmacology', 'Drug therapy and medications', '2026-02-08 16:53:47'),
(14, 'Maternal Health', 'Pregnancy and childbirth', '2026-02-08 16:53:47'),
(15, 'Public Health', 'Community health concepts', '2026-02-08 16:53:47'),
(16, 'Laboratory Techniques', 'Lab procedures and tests', '2026-02-08 16:53:47'),
(17, 'Anesthesia Basics', 'Anesthesia principles', '2026-02-08 16:53:47'),
(18, 'Patient Safety', 'Safety protocols and procedures', '2026-02-08 16:53:47'),
(19, 'Nursing Fundamentals', 'Basic nursing concepts and skills', '2026-02-08 17:34:14'),
(20, 'Anatomy', 'Human body structure', '2026-02-08 17:34:14'),
(21, 'Physiology', 'Body functions and systems', '2026-02-08 17:34:14'),
(22, 'Pharmacology', 'Drug therapy and medications', '2026-02-08 17:34:14'),
(23, 'Maternal Health', 'Pregnancy and childbirth', '2026-02-08 17:34:14'),
(24, 'Public Health', 'Community health concepts', '2026-02-08 17:34:14'),
(25, 'Laboratory Techniques', 'Lab procedures and tests', '2026-02-08 17:34:14'),
(26, 'Anesthesia Basics', 'Anesthesia principles', '2026-02-08 17:34:14'),
(27, 'Patient Safety', 'Safety protocols and procedures', '2026-02-08 17:34:14'),
(28, 'Nursing Fundamentals', 'Basic nursing concepts and skills', '2026-02-08 17:34:17'),
(29, 'Anatomy', 'Human body structure', '2026-02-08 17:34:17'),
(30, 'Physiology', 'Body functions and systems', '2026-02-08 17:34:17'),
(31, 'Pharmacology', 'Drug therapy and medications', '2026-02-08 17:34:17'),
(32, 'Maternal Health', 'Pregnancy and childbirth', '2026-02-08 17:34:17'),
(33, 'Public Health', 'Community health concepts', '2026-02-08 17:34:17'),
(34, 'Laboratory Techniques', 'Lab procedures and tests', '2026-02-08 17:34:17'),
(35, 'Anesthesia Basics', 'Anesthesia principles', '2026-02-08 17:34:17'),
(36, 'Patient Safety', 'Safety protocols and procedures', '2026-02-08 17:34:17'),
(37, 'Nursing Fundamentals', 'Basic nursing concepts and skills', '2026-02-08 17:37:33'),
(38, 'Anatomy', 'Human body structure', '2026-02-08 17:37:33'),
(39, 'Physiology', 'Body functions and systems', '2026-02-08 17:37:33'),
(40, 'Pharmacology', 'Drug therapy and medications', '2026-02-08 17:37:33'),
(41, 'Maternal Health', 'Pregnancy and childbirth', '2026-02-08 17:37:33'),
(42, 'Public Health', 'Community health concepts', '2026-02-08 17:37:33'),
(43, 'Laboratory Techniques', 'Lab procedures and tests', '2026-02-08 17:37:33'),
(44, 'Anesthesia Basics', 'Anesthesia principles', '2026-02-08 17:37:33'),
(45, 'Patient Safety', 'Safety protocols and procedures', '2026-02-08 17:37:33'),
(46, 'Nursing Fundamentals', 'Basic nursing concepts and skills', '2026-02-08 17:42:15'),
(47, 'Anatomy', 'Human body structure', '2026-02-08 17:42:15'),
(48, 'Physiology', 'Body functions and systems', '2026-02-08 17:42:15'),
(49, 'Pharmacology', 'Drug therapy and medications', '2026-02-08 17:42:15'),
(50, 'Maternal Health', 'Pregnancy and childbirth', '2026-02-08 17:42:15'),
(51, 'Public Health', 'Community health concepts', '2026-02-08 17:42:15'),
(52, 'Laboratory Techniques', 'Lab procedures and tests', '2026-02-08 17:42:15'),
(53, 'Anesthesia Basics', 'Anesthesia principles', '2026-02-08 17:42:15'),
(54, 'Patient Safety', 'Safety protocols and procedures', '2026-02-08 17:42:15'),
(55, 'Nursing Fundamentals', 'Basic nursing concepts and skills', '2026-02-08 17:44:39'),
(56, 'Anatomy', 'Human body structure', '2026-02-08 17:44:39'),
(57, 'Physiology', 'Body functions and systems', '2026-02-08 17:44:39'),
(58, 'Pharmacology', 'Drug therapy and medications', '2026-02-08 17:44:39'),
(59, 'Maternal Health', 'Pregnancy and childbirth', '2026-02-08 17:44:39'),
(60, 'Public Health', 'Community health concepts', '2026-02-08 17:44:39'),
(61, 'Laboratory Techniques', 'Lab procedures and tests', '2026-02-08 17:44:39'),
(62, 'Anesthesia Basics', 'Anesthesia principles', '2026-02-08 17:44:39'),
(63, 'Patient Safety', 'Safety protocols and procedures', '2026-02-08 17:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_code` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT 'Year 1',
  `semester` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_code`, `username`, `password`, `full_name`, `email`, `phone`, `gender`, `department_id`, `academic_year`, `semester`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'STU001', 'alem.h', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alem Hailu', 'alem.h@student.dmu.edu.et', '+251911111001', 'Male', 1, '2', 1, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:13:31'),
(2, 'STU002', 'bethel.k', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bethel Kebede', 'bethel.k@student.dmu.edu.et', '+251911111002', 'Female', 1, 'Year 1', 1, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(3, 'STU003', 'chala.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chala Mengistu', 'chala.m@student.dmu.edu.et', '+251911111003', 'Male', 2, 'Year 1', 1, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(4, 'STU004', 'eden.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eden Tesfaye', 'eden.t@student.dmu.edu.et', '+251911111004', 'Female', 3, 'Year 1', 1, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(5, 'STU005', 'frehiwot.a', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Frehiwot Alemu', 'frehiwot.a@student.dmu.edu.et', '+251911111005', 'Female', 4, 'Year 1', 1, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(6, 'STU006', 'genet.w', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Genet Worku', 'genet.w@student.dmu.edu.et', '+251911111006', 'Female', 5, 'Year 1', 1, 1, NULL, '2026-02-06 11:10:21', '2026-02-06 11:10:21'),
(7, 'STU007', 'Abraham', '$2y$10$jBfUNxq0s28fG/1Zauu8mOxIHUrgSEmnAEWiEzsmJXsBx0MzOW9.C', 'Abraham Worku', 'abraham@gmail.com', '+251900469816', NULL, 1, 'Year 1', 1, 1, NULL, '2026-02-06 16:20:57', '2026-02-06 16:20:57'),
(8, 'STU008', 'fitse', '$2y$10$VGv/n16kzdcIdkJD45YDN.3u.XDe5.Isq4uTMukPonIOLi67KZsGO', 'Fitsem Belay', 'fitse@gmail.com', '+251911223344', NULL, 1, 'Year 1', 1, 1, NULL, '2026-02-06 16:21:59', '2026-02-06 16:21:59'),
(9, 'STU009', 'dagi', '$2y$10$eB/dbnoebgrvnRxwDDOPpOTieUFnIutjgTpM1CvCdEx4cp/OAt8kq', 'dagim yehua', 'Dagim@gmail.com', '+251955667777', NULL, 1, 'Year 1', 2, 1, NULL, '2026-02-06 16:31:48', '2026-02-06 16:31:48'),
(10, 'STU010', 'BEREKET', '$2y$10$S8Saw5.rYhziFJ4EdP8lEONlcCn3LoFOArBeM2I1Ai8QKoCGvvrxi', 'Bereket Worku', 'bebi@gmail.com', '+251955667799', NULL, 1, 'Year 1', 2, 1, NULL, '2026-02-06 16:34:10', '2026-02-06 16:34:10'),
(14, 'STU011', 'daniel', '$2y$10$XfNpGN7YCkpHrJkb6a7gt.vYv6slzqB7cxmlOjI.RS30U8rCqpgK.', 'Daniel Abell', NULL, NULL, 'Male', 1, '2', 2, 1, NULL, '2026-02-06 16:44:24', '2026-02-06 16:45:10'),
(15, 'STU012', 'add', '$2y$10$7SVIeLsFPTXXH7NSZfjNeu/GGSaSNkE6WaraAh9YAXuqEge0kwh26', 'Daniel yoo', NULL, NULL, 'Male', 1, '1', 1, 0, NULL, '2026-02-06 16:45:45', '2026-02-06 16:45:45');

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE `student_answers` (
  `answer_id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_answer` enum('A','B','C','D') DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `points_earned` decimal(5,2) DEFAULT 0.00,
  `answered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_courses`
--

CREATE TABLE `student_courses` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('enrolled','completed','dropped','failed') DEFAULT 'enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_courses`
--

INSERT INTO `student_courses` (`enrollment_id`, `student_id`, `course_id`, `enrolled_at`, `status`) VALUES
(1, 1, 1, '2026-02-06 11:10:22', 'enrolled'),
(2, 1, 2, '2026-02-06 11:10:22', 'enrolled'),
(3, 2, 1, '2026-02-06 11:10:22', 'enrolled'),
(4, 2, 2, '2026-02-06 11:10:22', 'enrolled'),
(5, 3, 4, '2026-02-06 11:10:22', 'enrolled'),
(6, 3, 5, '2026-02-06 11:10:22', 'enrolled'),
(7, 4, 6, '2026-02-06 11:10:22', 'enrolled'),
(8, 4, 7, '2026-02-06 11:10:22', 'enrolled'),
(9, 5, 8, '2026-02-06 11:10:22', 'enrolled'),
(10, 5, 9, '2026-02-06 11:10:22', 'enrolled'),
(11, 6, 10, '2026-02-06 11:10:22', 'enrolled'),
(12, 6, 11, '2026-02-06 11:10:22', 'enrolled'),
(13, 7, 1, '2026-02-06 16:28:17', 'enrolled'),
(14, 7, 2, '2026-02-06 16:28:17', 'enrolled'),
(15, 7, 3, '2026-02-06 16:28:17', 'enrolled'),
(16, 8, 1, '2026-02-06 16:28:49', 'enrolled'),
(17, 8, 2, '2026-02-06 16:28:49', 'enrolled'),
(18, 8, 3, '2026-02-06 16:28:49', 'enrolled'),
(19, 9, 3, '2026-02-06 16:32:02', 'enrolled'),
(20, 10, 3, '2026-02-06 16:34:10', 'enrolled'),
(21, 14, 3, '2026-02-06 16:44:24', 'enrolled'),
(22, 15, 1, '2026-02-06 16:45:45', 'enrolled'),
(23, 15, 2, '2026-02-06 16:45:45', 'enrolled');

-- --------------------------------------------------------

--
-- Table structure for table `technical_issues`
--

CREATE TABLE `technical_issues` (
  `issue_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `issue_description` text NOT NULL,
  `status` enum('pending','resolved','closed') DEFAULT 'pending',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `table_name` (`table_name`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_code` (`department_code`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `department_heads`
--
ALTER TABLE `department_heads`
  ADD PRIMARY KEY (`department_head_id`),
  ADD UNIQUE KEY `head_code` (`head_code`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `exam_category_id` (`exam_category_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `exam_approval_history`
--
ALTER TABLE `exam_approval_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `exam_categories`
--
ALTER TABLE `exam_categories`
  ADD PRIMARY KEY (`exam_category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD PRIMARY KEY (`exam_question_id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `faculty_code` (`faculty_code`);

--
-- Indexes for table `grading_config`
--
ALTER TABLE `grading_config`
  ADD PRIMARY KEY (`config_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`instructor_id`),
  ADD UNIQUE KEY `instructor_code` (`instructor_code`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_instructor_course` (`instructor_id`,`course_id`),
  ADD KEY `instructor_id` (`instructor_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `practice_questions`
--
ALTER TABLE `practice_questions`
  ADD PRIMARY KEY (`practice_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indexes for table `question_topics`
--
ALTER TABLE `question_topics`
  ADD PRIMARY KEY (`topic_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_code` (`student_code`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `result_id` (`result_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_student_course` (`student_id`,`course_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `technical_issues`
--
ALTER TABLE `technical_issues`
  ADD PRIMARY KEY (`issue_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrators`
--
ALTER TABLE `administrators`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `department_heads`
--
ALTER TABLE `department_heads`
  MODIFY `department_head_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `exam_approval_history`
--
ALTER TABLE `exam_approval_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `exam_categories`
--
ALTER TABLE `exam_categories`
  MODIFY `exam_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `exam_questions`
--
ALTER TABLE `exam_questions`
  MODIFY `exam_question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1556;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `grading_config`
--
ALTER TABLE `grading_config`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `instructor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `practice_questions`
--
ALTER TABLE `practice_questions`
  MODIFY `practice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=646;

--
-- AUTO_INCREMENT for table `question_topics`
--
ALTER TABLE `question_topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `student_answers`
--
ALTER TABLE `student_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_courses`
--
ALTER TABLE `student_courses`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `technical_issues`
--
ALTER TABLE `technical_issues`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_departments_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`faculty_id`) ON DELETE CASCADE;

--
-- Constraints for table `department_heads`
--
ALTER TABLE `department_heads`
  ADD CONSTRAINT `fk_department_heads_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `fk_exams_approver` FOREIGN KEY (`approved_by`) REFERENCES `department_heads` (`department_head_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_exams_category` FOREIGN KEY (`exam_category_id`) REFERENCES `exam_categories` (`exam_category_id`),
  ADD CONSTRAINT `fk_exams_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exams_creator` FOREIGN KEY (`created_by`) REFERENCES `instructors` (`instructor_id`) ON DELETE SET NULL;

--
-- Constraints for table `exam_approval_history`
--
ALTER TABLE `exam_approval_history`
  ADD CONSTRAINT `fk_approval_history_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_questions`
--
ALTER TABLE `exam_questions`
  ADD CONSTRAINT `fk_exam_questions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_questions_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `fk_exam_results_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_results_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `fk_instructors_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;

--
-- Constraints for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD CONSTRAINT `fk_instructor_courses_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_instructor_courses_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE;

--
-- Constraints for table `practice_questions`
--
ALTER TABLE `practice_questions`
  ADD CONSTRAINT `fk_practice_questions_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_practice_questions_topic` FOREIGN KEY (`topic_id`) REFERENCES `question_topics` (`topic_id`) ON DELETE SET NULL;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_questions_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_questions_topic` FOREIGN KEY (`topic_id`) REFERENCES `question_topics` (`topic_id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `fk_student_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student_answers_result` FOREIGN KEY (`result_id`) REFERENCES `exam_results` (`result_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_courses`
--
ALTER TABLE `student_courses`
  ADD CONSTRAINT `fk_student_courses_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student_courses_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `technical_issues`
--
ALTER TABLE `technical_issues`
  ADD CONSTRAINT `fk_technical_issues_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_technical_issues_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
