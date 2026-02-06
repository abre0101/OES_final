-- ============================================
-- FRESH INSTALLATION SCRIPT
-- This will DROP the existing database and create everything fresh
-- WARNING: This will DELETE ALL existing data!
-- ============================================

-- Drop the database if it exists
DROP DATABASE IF EXISTS `oes_professional`;

-- Create the database
CREATE DATABASE `oes_professional` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE `oes_professional`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- CREATE ALL TABLES
-- ============================================

-- 1. FACULTIES
CREATE TABLE `faculties` (
  `faculty_id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_code` varchar(20) NOT NULL,
  `faculty_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`faculty_id`),
  UNIQUE KEY `faculty_code` (`faculty_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. DEPARTMENTS
CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_id` int(11) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `department_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `department_code` (`department_code`),
  KEY `faculty_id` (`faculty_id`),
  CONSTRAINT `fk_departments_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`faculty_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. COURSES
CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `credit_hours` int(11) DEFAULT 3,
  `semester` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `course_code` (`course_code`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_courses_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. STUDENTS
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `student_code` (`student_code`),
  UNIQUE KEY `username` (`username`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_students_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. INSTRUCTORS
CREATE TABLE `instructors` (
  `instructor_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`instructor_id`),
  UNIQUE KEY `instructor_code` (`instructor_code`),
  UNIQUE KEY `username` (`username`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_instructors_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. DEPARTMENT HEADS
CREATE TABLE `department_heads` (
  `department_head_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`department_head_id`),
  UNIQUE KEY `head_code` (`head_code`),
  UNIQUE KEY `username` (`username`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_department_heads_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. ADMINISTRATORS
CREATE TABLE `administrators` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. INSTRUCTOR COURSES
CREATE TABLE `instructor_courses` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `unique_instructor_course` (`instructor_id`, `course_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `fk_instructor_courses_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_instructor_courses_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. STUDENT COURSES
CREATE TABLE `student_courses` (
  `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('enrolled','completed','dropped','failed') DEFAULT 'enrolled',
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `unique_student_course` (`student_id`, `course_id`),
  KEY `student_id` (`student_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `fk_student_courses_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_student_courses_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. EXAM CATEGORIES
CREATE TABLE `exam_categories` (
  `exam_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`exam_category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. EXAMS (NOT exams!)
CREATE TABLE `exams` (
  `exam_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`exam_id`),
  KEY `course_id` (`course_id`),
  KEY `exam_category_id` (`exam_category_id`),
  KEY `created_by` (`created_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `fk_exams_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_exams_category` FOREIGN KEY (`exam_category_id`) REFERENCES `exam_categories` (`exam_category_id`),
  CONSTRAINT `fk_exams_creator` FOREIGN KEY (`created_by`) REFERENCES `instructors` (`instructor_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_exams_approver` FOREIGN KEY (`approved_by`) REFERENCES `department_heads` (`department_head_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. QUESTION TOPICS
CREATE TABLE `question_topics` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. QUESTIONS
CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`question_id`),
  KEY `course_id` (`course_id`),
  KEY `topic_id` (`topic_id`),
  CONSTRAINT `fk_questions_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_questions_topic` FOREIGN KEY (`topic_id`) REFERENCES `question_topics` (`topic_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. EXAM QUESTIONS
CREATE TABLE `exam_questions` (
  `exam_question_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `question_order` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`exam_question_id`),
  KEY `exam_id` (`exam_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `fk_exam_questions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_exam_questions_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. EXAM RESULTS
CREATE TABLE `exam_results` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`result_id`),
  KEY `student_id` (`student_id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `fk_exam_results_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_exam_results_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. STUDENT ANSWERS
CREATE TABLE `student_answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_answer` enum('A','B','C','D') DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `points_earned` decimal(5,2) DEFAULT 0.00,
  `answered_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `result_id` (`result_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `fk_student_answers_result` FOREIGN KEY (`result_id`) REFERENCES `exam_results` (`result_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_student_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. EXAM APPROVAL HISTORY
CREATE TABLE `exam_approval_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `action` enum('submitted','approved','revision_requested','rejected','resubmitted') NOT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_by_type` enum('instructor','department_head') NOT NULL,
  `comments` text DEFAULT NULL,
  `previous_status` enum('draft','pending','approved','revision','rejected') DEFAULT NULL,
  `new_status` enum('draft','pending','approved','revision','rejected') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `fk_approval_history_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. GRADING CONFIG
CREATE TABLE `grading_config` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `grade_letter` varchar(5) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL,
  `gpa_value` decimal(3,2) NOT NULL,
  `status_label` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. TECHNICAL ISSUES
CREATE TABLE `technical_issues` (
  `issue_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `issue_description` text NOT NULL,
  `status` enum('pending','resolved','closed') DEFAULT 'pending',
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`issue_id`),
  KEY `student_id` (`student_id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `fk_technical_issues_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_technical_issues_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. AUDIT LOGS
CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('student','instructor','department_head','admin','unknown') DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  KEY `table_name` (`table_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. PRACTICE QUESTIONS
CREATE TABLE `practice_questions` (
  `practice_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`practice_id`),
  KEY `course_id` (`course_id`),
  KEY `topic_id` (`topic_id`),
  CONSTRAINT `fk_practice_questions_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_practice_questions_topic` FOREIGN KEY (`topic_id`) REFERENCES `question_topics` (`topic_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLES CREATED SUCCESSFULLY
-- Now run insert_sample_data.sql to populate with data
-- ============================================

SELECT 'Database created successfully! Now run insert_sample_data.sql' as message;
