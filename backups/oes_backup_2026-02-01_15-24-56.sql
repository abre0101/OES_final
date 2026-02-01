-- OES Database Backup
-- Generated: 2026-02-01 15:24:56
-- Database: oes

SET FOREIGN_KEY_CHECKS=0;

-- Table: academic_calendar
DROP TABLE IF EXISTS `academic_calendar`;
CREATE TABLE `academic_calendar` (
  `calendar_id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `semester_start_date` date NOT NULL,
  `semester_end_date` date NOT NULL,
  `registration_start_date` date DEFAULT NULL,
  `registration_end_date` date DEFAULT NULL,
  `exam_period_start_date` date DEFAULT NULL,
  `exam_period_end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`calendar_id`),
  UNIQUE KEY `unique_semester` (`academic_year`,`semester`),
  KEY `idx_active` (`is_active`),
  KEY `idx_dates` (`semester_start_date`,`semester_end_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `academic_calendar` VALUES
("1","2025/2026","Semester 1","2025-09-15","2026-01-30","2025-09-01","2025-09-14","2026-01-15","2026-01-30","0","2026-01-31 11:14:38","2026-02-01 16:32:54"),
("2","2025/2026","Semester 2","2026-02-10","2026-06-30","2026-02-01","2026-02-09","2026-06-15","2026-06-30","1","2026-01-31 11:14:38","2026-02-01 16:34:27");

-- Table: administrators
DROP TABLE IF EXISTS `administrators`;
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
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `administrators` VALUES
("1","admin","admin123","System Administrator","admin@dmu.edu","NULL","1","NULL","2026-01-31 11:14:30","2026-01-31 11:14:30");

-- Table: audit_logs
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_type` enum('student','instructor','exam_committee','admin') DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_user` (`user_id`,`user_type`),
  KEY `idx_action` (`action`),
  KEY `idx_table` (`table_name`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: choose
DROP TABLE IF EXISTS `choose`;
CREATE TABLE `choose` (
  `question_id` varchar(20) NOT NULL,
  `exam_id` varchar(20) NOT NULL,
  `course_id` varchar(20) NOT NULL,
  `semister` int(11) NOT NULL,
  `question` varchar(1000) NOT NULL,
  `Option1` varchar(100) NOT NULL,
  `Option2` varchar(100) NOT NULL,
  `Option3` varchar(100) NOT NULL,
  `Option4` varchar(100) NOT NULL,
  `Answer` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`question_id`),
  KEY `idx_exam` (`exam_id`),
  KEY `idx_course` (`course_id`),
  KEY `idx_semester` (`semister`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: courses
DROP TABLE IF EXISTS `courses`;
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
  KEY `idx_department` (`department_id`),
  KEY `idx_code` (`course_code`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `courses` VALUES
("1","1","NURS101","Fundamentals of Nursing","4","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("2","1","NURS102","Anatomy and Physiology for Nurses","5","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("3","1","NURS103","Medical-Surgical Nursing I","4","2","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("4","1","NURS201","Pediatric Nursing","4","3","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("5","1","NURS202","Maternal and Child Health Nursing","4","3","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("6","2","MIDW101","Introduction to Midwifery","3","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("7","2","MIDW102","Reproductive Health","4","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("8","2","MIDW201","Antenatal Care","4","2","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("9","2","MIDW202","Labor and Delivery","5","2","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("10","3","PHO101","Introduction to Public Health","3","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("11","3","PHO102","Epidemiology","4","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("12","3","PHO201","Health Promotion and Disease Prevention","4","2","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("13","4","ANES101","Fundamentals of Anesthesia","4","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("14","4","ANES102","Pharmacology for Anesthesia","4","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("15","4","ANES201","Clinical Anesthesia Practice","5","2","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("16","5","MLT101","Clinical Chemistry","4","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("17","5","MLT102","Hematology","4","1","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("18","5","MLT201","Medical Microbiology","4","2","NULL","1","2026-01-31 11:14:33","2026-01-31 11:14:33");

-- Table: departments
DROP TABLE IF EXISTS `departments`;
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
  KEY `idx_faculty` (`faculty_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_code` (`department_code`),
  CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`faculty_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `departments` VALUES
("1","1","NURS","Nursing","Bachelor of Science in Nursing","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("2","1","MIDW","Midwifery","Bachelor of Science in Midwifery","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("3","1","PHO","Public Health Officer","Public Health Officer Program","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("4","1","ANES","Anesthesia","Anesthesia Technology Program","1","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("5","1","MLT","Medical Laboratory Technology","Medical Laboratory Science","1","2026-01-31 11:14:33","2026-01-31 11:14:33");

-- Table: exam_approval_history
DROP TABLE IF EXISTS `exam_approval_history`;
CREATE TABLE `exam_approval_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `action` enum('submitted','approved','revision_requested','rejected','resubmitted') NOT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_by_type` enum('instructor','committee') NOT NULL,
  `comments` text DEFAULT NULL,
  `previous_status` enum('pending','approved','revision','rejected') DEFAULT NULL,
  `new_status` enum('pending','approved','revision','rejected') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `idx_schedule` (`schedule_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `exam_approval_history_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `exam_schedules` (`schedule_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `exam_approval_history` VALUES
("1","17","approved","1","committee","Excellent exam structure. Questions are well-balanced and cover all required topics.","pending","approved","2026-01-30 15:22:24"),
("2","18","approved","2","committee","Good coverage of SDLC and Agile methodologies. Approved.","pending","approved","2026-01-31 15:22:24"),
("3","19","revision_requested","3","committee","Please revise the following:\n1. Question 5 is ambiguous - clarify the expected output format\n2. Question 8 difficulty level is too high for midterm\n3. Add more questions on neural networks as per syllabus\n4. Total marks should be adjusted to match course credit hours","pending","revision","2026-01-31 15:22:25"),
("4","20","revision_requested","1","committee","The quiz needs the following improvements:\n1. Question 3 has incorrect answer options\n2. Add one more question on Activity lifecycle\n3. Instructions should specify if code snippets are allowed","pending","revision","2026-02-01 15:22:25"),
("5","21","rejected","2","committee","This exam is rejected for the following reasons:\n1. Questions do not align with the approved course syllabus\n2. Several questions are copied from online sources without modification\n3. Difficulty level is inconsistent - some questions too easy, others too difficult\n4. Missing questions on key topics: penetration testing and security policies\n5. Please create a new exam following the syllabus guidelines","pending","rejected","2026-01-29 15:22:25");

-- Table: exam_categories
DROP TABLE IF EXISTS `exam_categories`;
CREATE TABLE `exam_categories` (
  `exam_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`exam_category_id`),
  UNIQUE KEY `category_name` (`category_name`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `exam_categories` VALUES
("1","Midterm","Mid-semester examination","1","2026-01-31 11:14:30"),
("2","Final","End of semester examination","1","2026-01-31 11:14:30"),
("3","Quiz","Short assessment","1","2026-01-31 11:14:30"),
("4","Makeup","Makeup examination","1","2026-01-31 11:14:30");

-- Table: exam_committee_members
DROP TABLE IF EXISTS `exam_committee_members`;
CREATE TABLE `exam_committee_members` (
  `committee_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_code` varchar(20) NOT NULL,
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
  PRIMARY KEY (`committee_member_id`),
  UNIQUE KEY `member_code` (`member_code`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_code` (`member_code`),
  KEY `idx_username` (`username`),
  KEY `idx_department` (`department_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `exam_committee_members_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `exam_committee_members` VALUES
("1","EC001","solomon.k","pass123","Dr. Solomon Kebede","solomon.k@dmu.edu","+251911234580","1","1","NULL","2026-01-31 11:14:38","2026-01-31 11:14:38"),
("2","EC002","rahel.t","pass123","Dr. Rahel Tesfaye","rahel.t@dmu.edu","+251911234581","2","1","NULL","2026-01-31 11:14:38","2026-01-31 11:14:38"),
("3","EC003","yared.m","pass123","Dr. Yared Mengistu","yared.m@dmu.edu","+251911234582","3","1","NULL","2026-01-31 11:14:38","2026-01-31 11:14:38");

-- Table: exam_questions
DROP TABLE IF EXISTS `exam_questions`;
CREATE TABLE `exam_questions` (
  `exam_question_id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `question_order` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`exam_question_id`),
  UNIQUE KEY `unique_exam_question` (`schedule_id`,`question_id`),
  KEY `idx_schedule` (`schedule_id`),
  KEY `idx_question` (`question_id`),
  CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `exam_schedules` (`schedule_id`) ON DELETE CASCADE,
  CONSTRAINT `exam_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `exam_questions` VALUES
("1","1","1","1","2026-01-31 11:14:40"),
("2","1","2","2","2026-01-31 11:14:40"),
("3","1","3","3","2026-01-31 11:14:40"),
("4","1","4","4","2026-01-31 11:14:40"),
("5","1","5","5","2026-01-31 11:14:40"),
("6","1","6","6","2026-01-31 11:14:40"),
("7","1","7","7","2026-01-31 11:14:40"),
("8","1","8","8","2026-01-31 11:14:40"),
("9","2","9","1","2026-01-31 11:14:41"),
("10","2","10","2","2026-01-31 11:14:41"),
("11","2","11","3","2026-01-31 11:14:41"),
("12","2","12","4","2026-01-31 11:14:41"),
("13","3","13","1","2026-01-31 11:14:41"),
("14","3","14","2","2026-01-31 11:14:41"),
("15","3","15","3","2026-01-31 11:14:41"),
("16","5","16","1","2026-01-31 11:14:41"),
("17","5","17","2","2026-01-31 11:14:41"),
("18","5","18","3","2026-01-31 11:14:41"),
("19","7","19","1","2026-01-31 11:14:41"),
("20","7","20","2","2026-01-31 11:14:41"),
("21","7","21","3","2026-01-31 11:14:41"),
("22","9","22","1","2026-01-31 11:14:41"),
("23","9","23","2","2026-01-31 11:14:41"),
("24","9","24","3","2026-01-31 11:14:41"),
("25","11","26","1","2026-01-31 16:22:14");

-- Table: exam_results
DROP TABLE IF EXISTS `exam_results`;
CREATE TABLE `exam_results` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
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
  UNIQUE KEY `unique_student_exam` (`student_id`,`schedule_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_schedule` (`schedule_id`),
  KEY `idx_grade` (`letter_grade`),
  KEY `idx_pass_status` (`pass_status`),
  CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `exam_schedules` (`schedule_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `exam_results` VALUES
("1","1","1","8","7","1","0","87.50","100.00","87.50","A","3.75","Pass","2026-02-15 09:00:00","2026-02-15 10:45:00","105","2026-01-31 11:14:42"),
("2","2","1","8","6","2","0","75.00","100.00","75.00","B+","3.00","Pass","2026-02-15 09:00:00","2026-02-15 10:50:00","110","2026-01-31 11:14:42"),
("3","4","3","3","3","0","0","100.00","100.00","100.00","A+","4.00","Pass","2026-02-17 09:00:00","2026-02-17 10:15:00","75","2026-01-31 11:14:42"),
("4","7","5","3","2","1","0","66.67","100.00","66.67","B-","2.50","Pass","2026-02-19 09:00:00","2026-02-19 10:30:00","90","2026-01-31 11:14:42"),
("5","9","7","3","3","0","0","100.00","100.00","100.00","A+","4.00","Pass","2026-02-21 09:00:00","2026-02-21 10:20:00","80","2026-01-31 11:14:42"),
("6","11","9","3","2","0","1","66.67","100.00","66.67","B-","2.50","Pass","2026-02-23 09:00:00","2026-02-23 10:40:00","100","2026-01-31 11:14:43");

-- Table: exam_schedules
DROP TABLE IF EXISTS `exam_schedules`;
CREATE TABLE `exam_schedules` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `exam_category_id` int(11) NOT NULL,
  `exam_name` varchar(200) NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `total_marks` int(11) DEFAULT 100,
  `pass_marks` int(11) DEFAULT 50,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `approval_status` enum('draft','pending','approved','rejected','revision') DEFAULT 'draft',
  `submitted_for_approval` tinyint(1) DEFAULT 0,
  `submitted_at` datetime DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewer_comments` text DEFAULT NULL,
  `review_comments` text DEFAULT NULL,
  `revision_count` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  KEY `exam_category_id` (`exam_category_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_course` (`course_id`),
  KEY `idx_date` (`exam_date`),
  KEY `idx_active` (`is_active`),
  KEY `idx_approval_status` (`approval_status`),
  KEY `idx_submitted` (`submitted_for_approval`),
  KEY `idx_reviewed_by` (`reviewed_by`),
  CONSTRAINT `exam_schedules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `exam_schedules_ibfk_2` FOREIGN KEY (`exam_category_id`) REFERENCES `exam_categories` (`exam_category_id`),
  CONSTRAINT `exam_schedules_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `instructors` (`instructor_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `exam_schedules` VALUES
("1","1","1","Fundamentals of Nursing - Midterm","2026-02-15","09:00:00","11:00:00","120","100","50","Read all questions carefully. Choose the best answer. No calculators allowed.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","1","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("2","2","1","Anatomy and Physiology - Midterm","2026-02-16","09:00:00","11:00:00","120","100","50","Answer all questions. Diagrams may be required for some questions.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","1","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("3","6","1","Introduction to Midwifery - Midterm","2026-02-17","09:00:00","10:30:00","90","100","50","Multiple choice questions. Select the most appropriate answer.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","3","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("4","7","1","Reproductive Health - Midterm","2026-02-18","09:00:00","10:30:00","90","100","50","Answer all questions. Time management is important.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","3","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("5","10","1","Introduction to Public Health - Midterm","2026-02-19","09:00:00","11:00:00","120","100","50","Read carefully. Choose the best answer for each question.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","4","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("6","11","1","Epidemiology - Midterm","2026-02-20","09:00:00","11:00:00","120","100","50","Calculators allowed. Show your work for calculations.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","4","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("7","13","1","Fundamentals of Anesthesia - Midterm","2026-02-21","09:00:00","11:00:00","120","100","50","Answer all questions. Focus on safety principles.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","5","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("8","14","1","Pharmacology for Anesthesia - Midterm","2026-02-22","09:00:00","11:00:00","120","100","50","Drug calculations required. Bring calculator.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","5","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("9","16","1","Clinical Chemistry - Midterm","2026-02-23","09:00:00","11:00:00","120","100","50","Multiple choice and calculation questions. Calculator allowed.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","6","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("10","17","1","Hematology - Midterm","2026-02-24","09:00:00","11:00:00","120","100","50","Answer all questions. Review lab safety protocols.","1","pending","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","6","2026-01-31 11:14:40","2026-01-31 11:14:40"),
("11","3","1","Midterm Exam","2026-02-03","17:21:00","19:21:00","120","1","1","cszdx","1","approved","1","2026-01-31 16:22:25","Dr. Rahel Tesfaye","2026-02-01 12:39:07","NULL","NULL","","NULL","0","NULL","2026-01-31 16:22:14","2026-02-01 14:39:07"),
("12","1","1","Introduction to Programming - Midterm","2026-03-15","09:00:00","11:00:00","120","100","50","This is a midterm exam covering chapters 1-5. No calculators allowed. Bring your student ID.","1","draft","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","NULL","2026-02-01 15:22:24","2026-02-01 15:22:24"),
("13","2","2","Database Systems - Quiz 1","2026-03-20","14:00:00","15:00:00","60","50","25","Short quiz on SQL basics and normalization.","1","draft","0","NULL","NULL","NULL","NULL","NULL","NULL","NULL","0","NULL","2026-02-01 15:22:24","2026-02-01 15:22:24"),
("14","3","3","Data Structures - Final Exam","2026-04-10","10:00:00","13:00:00","180","150","75","Comprehensive final exam covering all topics. Closed book exam.","1","pending","1","2026-01-30 15:22:24","NULL","NULL","NULL","NULL","NULL","NULL","0","NULL","2026-01-29 15:22:24","2026-02-01 15:22:24"),
("15","4","1","Web Development - Midterm","2026-03-25","13:00:00","15:00:00","120","100","50","Covers HTML, CSS, JavaScript fundamentals. Practical coding questions included.","1","pending","1","2026-01-31 15:22:24","NULL","NULL","NULL","NULL","NULL","NULL","0","NULL","2026-01-30 15:22:24","2026-02-01 15:22:24"),
("16","5","2","Operating Systems - Quiz 2","2026-03-18","11:00:00","12:00:00","60","50","25","Quiz on process scheduling and memory management.","1","pending","1","2026-02-01 15:22:24","NULL","NULL","NULL","NULL","NULL","NULL","0","NULL","2026-01-31 15:22:24","2026-02-01 15:22:24"),
("17","6","3","Computer Networks - Final Exam","2026-04-15","09:00:00","12:00:00","180","150","75","Final exam covering OSI model, TCP/IP, routing protocols. Bring calculator.","1","approved","1","2026-01-27 15:22:24","Dr. Solomon Kebede","2026-01-30 15:22:24","NULL","NULL","NULL","NULL","0","NULL","2026-01-25 15:22:24","2026-02-01 15:22:24"),
("18","7","1","Software Engineering - Midterm","2026-03-28","14:00:00","16:00:00","120","100","50","Covers SDLC, Agile, UML diagrams. Open book exam.","1","approved","1","2026-01-28 15:22:24","Dr. Rahel Tesfaye","2026-01-31 15:22:24","NULL","NULL","NULL","NULL","0","NULL","2026-01-26 15:22:24","2026-02-01 15:22:24"),
("19","8","1","Artificial Intelligence - Midterm","2026-03-30","10:00:00","12:00:00","120","100","50","Covers search algorithms, knowledge representation, and machine learning basics.","1","revision","1","2026-01-29 15:22:24","NULL","NULL","0","2026-01-31 15:22:24","Please revise the following:\n1. Question 5 is ambiguous - clarify the expected output format\n2. Question 8 difficulty level is too high for midterm\n3. Add more questions on neural networks as per syllabus\n4. Total marks should be adjusted to match course credit hours","NULL","1","NULL","2026-01-27 15:22:24","2026-02-01 15:22:24"),
("20","9","2","Mobile App Development - Quiz 1","2026-03-22","15:00:00","16:00:00","60","50","25","Quiz on Android basics and UI components.","1","revision","1","2026-01-30 15:22:24","NULL","NULL","0","2026-02-01 15:22:24","The quiz needs the following improvements:\n1. Question 3 has incorrect answer options\n2. Add one more question on Activity lifecycle\n3. Instructions should specify if code snippets are allowed","NULL","1","NULL","2026-01-28 15:22:24","2026-02-01 15:22:24"),
("21","10","3","Cybersecurity - Final Exam","2026-04-20","09:00:00","12:00:00","180","150","75","Final exam on network security, cryptography, and ethical hacking.","1","rejected","1","2026-01-26 15:22:24","NULL","NULL","0","2026-01-29 15:22:24","This exam is rejected for the following reasons:\n1. Questions do not align with the approved course syllabus\n2. Several questions are copied from online sources without modification\n3. Difficulty level is inconsistent - some questions too easy, others too difficult\n4. Missing questions on key topics: penetration testing and security policies\n5. Please create a new exam following the syllabus guidelines","NULL","0","NULL","2026-01-24 15:22:24","2026-02-01 15:22:24");

-- Table: faculties
DROP TABLE IF EXISTS `faculties`;
CREATE TABLE `faculties` (
  `faculty_id` int(11) NOT NULL AUTO_INCREMENT,
  `faculty_code` varchar(20) NOT NULL,
  `faculty_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`faculty_id`),
  UNIQUE KEY `faculty_code` (`faculty_code`),
  KEY `idx_active` (`is_active`),
  KEY `idx_code` (`faculty_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `faculties` VALUES
("1","FHS","Faculty of Health Sciences","Health and Medical Sciences Programs","1","2026-01-31 11:14:32","2026-01-31 11:14:32");

-- Table: grading_config
DROP TABLE IF EXISTS `grading_config`;
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
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `grade_letter` (`grade_letter`),
  KEY `idx_percentage` (`min_percentage`,`max_percentage`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `grading_config` VALUES
("1","A+","90.00","100.00","4.00","Excellent","1","1","2026-01-31 11:14:30"),
("2","A","85.00","89.99","3.75","Excellent","2","1","2026-01-31 11:14:30"),
("3","A-","80.00","84.99","3.50","Excellent","3","1","2026-01-31 11:14:30"),
("4","B+","75.00","79.99","3.00","Good","4","1","2026-01-31 11:14:30"),
("5","B","70.00","74.99","2.75","Good","5","1","2026-01-31 11:14:30"),
("6","B-","65.00","69.99","2.50","Good","6","1","2026-01-31 11:14:30"),
("7","C+","60.00","64.99","2.00","Satisfactory","7","1","2026-01-31 11:14:30"),
("8","C","55.00","59.99","1.75","Satisfactory","8","1","2026-01-31 11:14:30"),
("9","C-","50.00","54.99","1.50","Satisfactory","9","1","2026-01-31 11:14:30"),
("10","D","45.00","49.99","1.00","Pass","10","1","2026-01-31 11:14:30"),
("11","F","0.00","44.99","0.00","Fail","11","1","2026-01-31 11:14:30");

-- Table: instructor_courses
DROP TABLE IF EXISTS `instructor_courses`;
CREATE TABLE `instructor_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_instructor_course` (`instructor_id`,`course_id`,`semester`),
  KEY `idx_instructor` (`instructor_id`),
  KEY `idx_course` (`course_id`),
  CONSTRAINT `instructor_courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE CASCADE,
  CONSTRAINT `instructor_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `instructor_courses` VALUES
("1","1","1","1","1","2026-01-31 11:14:36"),
("2","1","2","1","1","2026-01-31 11:14:36"),
("3","1","3","2","1","2026-01-31 11:14:36"),
("4","2","4","3","1","2026-01-31 11:14:36"),
("5","2","5","3","1","2026-01-31 11:14:36"),
("6","3","6","1","1","2026-01-31 11:14:36"),
("7","3","7","1","1","2026-01-31 11:14:36"),
("8","3","8","2","1","2026-01-31 11:14:36"),
("9","3","9","2","1","2026-01-31 11:14:36"),
("10","4","10","1","1","2026-01-31 11:14:36"),
("11","4","11","1","1","2026-01-31 11:14:36"),
("12","4","12","2","1","2026-01-31 11:14:36"),
("13","5","13","1","1","2026-01-31 11:14:36"),
("14","5","14","1","1","2026-01-31 11:14:36"),
("15","5","15","2","1","2026-01-31 11:14:36"),
("16","6","16","1","1","2026-01-31 11:14:36"),
("17","6","17","1","1","2026-01-31 11:14:36"),
("18","6","18","2","1","2026-01-31 11:14:36"),
("19","7","1","1","1","2026-01-31 11:14:36"),
("20","7","4","3","1","2026-01-31 11:14:36");

-- Table: instructors
DROP TABLE IF EXISTS `instructors`;
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
  UNIQUE KEY `email` (`email`),
  KEY `idx_code` (`instructor_code`),
  KEY `idx_username` (`username`),
  KEY `idx_department` (`department_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `instructors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `instructors` VALUES
("1","INST001","abebe.t","pass123","Dr. Abebe Tadesse","abebe.t@dmu.edu","+251911234567","1","Male","1","NULL","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("2","INST002","marta.g","pass123","Sr. Marta Gebre","marta.g@dmu.edu","+251911234568","1","Female","1","NULL","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("3","INST003","sara.m","pass123","Dr. Sara Mulugeta","sara.m@dmu.edu","+251911234569","2","Female","1","NULL","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("4","INST004","daniel.a","pass123","Dr. Daniel Alemu","daniel.a@dmu.edu","+251911234570","3","Male","1","NULL","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("5","INST005","helen.t","pass123","Dr. Helen Tesfaye","helen.t@dmu.edu","+251911234571","4","Female","1","NULL","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("6","INST006","yohannes.b","pass123","Dr. Yohannes Bekele","yohannes.b@dmu.edu","+251911234572","5","Male","1","NULL","2026-01-31 11:14:33","2026-01-31 11:14:33"),
("7","INST007","tigist.w","pass123","Sr. Tigist Worku","tigist.w@dmu.edu","+251911234573","1","Female","1","NULL","2026-01-31 11:14:33","2026-01-31 11:14:33");

-- Table: notifications
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','instructor','exam_committee','admin') NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_emailed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user` (`user_id`,`user_type`),
  KEY `idx_read` (`is_read`),
  KEY `idx_type` (`notification_type`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` VALUES
("1","1","student","exam_result","Exam Result Available","Your result for Fundamentals of Nursing - Midterm is now available.","/Student/results.php","0","0","2026-01-31 11:14:43","NULL"),
("2","2","student","exam_result","Exam Result Available","Your result for Fundamentals of Nursing - Midterm is now available.","/Student/results.php","0","0","2026-01-31 11:14:43","NULL"),
("3","4","student","exam_result","Exam Result Available","Your result for Introduction to Midwifery - Midterm is now available.","/Student/results.php","1","0","2026-01-31 11:14:43","NULL"),
("4","1","student","exam_schedule","Upcoming Exam","You have an exam scheduled for Anatomy and Physiology on Feb 16, 2026.","/Student/exams.php","0","0","2026-01-31 11:14:43","NULL"),
("5","1","instructor","question_approval","Questions Approved","8 questions have been approved for your course.","/Instructor/questions.php","0","0","2026-01-31 11:14:43","NULL");

-- Table: password_reset_requests
DROP TABLE IF EXISTS `password_reset_requests`;
CREATE TABLE `password_reset_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `user_type` enum('student','instructor','exam_committee') NOT NULL,
  `user_name` varchar(200) NOT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `processed_by` int(11) DEFAULT NULL,
  `processed_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`request_id`),
  KEY `processed_by` (`processed_by`),
  KEY `idx_status` (`status`),
  KEY `idx_user` (`user_id`,`user_type`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `password_reset_requests_ibfk_1` FOREIGN KEY (`processed_by`) REFERENCES `administrators` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: question_topics
DROP TABLE IF EXISTS `question_topics`;
CREATE TABLE `question_topics` (
  `topic_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `topic_name` varchar(200) NOT NULL,
  `topic_description` text DEFAULT NULL,
  `chapter_number` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`topic_id`),
  UNIQUE KEY `unique_topic` (`course_id`,`topic_name`),
  KEY `created_by` (`created_by`),
  KEY `idx_course` (`course_id`),
  KEY `idx_chapter` (`chapter_number`),
  CONSTRAINT `question_topics_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `question_topics_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `instructors` (`instructor_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `question_topics` VALUES
("1","1","Introduction to Nursing","Basic concepts and principles of nursing","1","1","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("2","1","Vital Signs Assessment","Measuring and interpreting vital signs","2","1","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("3","1","Patient Safety","Safety protocols and infection control","3","1","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("4","1","Basic Nursing Skills","Essential nursing procedures","4","1","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("5","2","Cardiovascular System","Heart and blood vessels anatomy","1","1","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("6","2","Respiratory System","Lungs and breathing mechanisms","2","1","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("7","2","Nervous System","Brain, spinal cord, and nerves","3","1","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("8","6","History of Midwifery","Evolution of midwifery practice","1","3","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("9","6","Professional Ethics","Ethical principles in midwifery","2","3","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("10","6","Scope of Practice","Midwifery roles and responsibilities","3","3","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("11","10","Public Health Concepts","Foundations of public health","1","4","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("12","10","Health Determinants","Social and environmental factors","2","4","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("13","10","Community Health","Population-based health approaches","3","4","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("14","13","Anesthesia Basics","Introduction to anesthesia practice","1","5","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("15","13","Patient Assessment","Pre-anesthetic evaluation","2","5","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("16","13","Anesthesia Equipment","Tools and machines used in anesthesia","3","5","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("17","16","Clinical Chemistry Principles","Basic chemistry in clinical lab","1","6","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("18","16","Laboratory Safety","Safety protocols in the lab","2","6","2026-01-31 11:14:39","2026-01-31 11:14:39"),
("19","16","Quality Control","Ensuring accurate test results","3","6","2026-01-31 11:14:39","2026-01-31 11:14:39");

-- Table: questions
DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `exam_category_id` int(11) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(500) NOT NULL,
  `option_b` varchar(500) NOT NULL,
  `option_c` varchar(500) DEFAULT NULL,
  `option_d` varchar(500) DEFAULT NULL,
  `correct_answer` enum('A','B','C','D') NOT NULL,
  `point_value` int(11) DEFAULT 1,
  `difficulty_level` enum('Easy','Medium','Hard') DEFAULT 'Medium',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_status` enum('pending','approved','revision','rejected') DEFAULT 'pending',
  PRIMARY KEY (`question_id`),
  KEY `approved_by` (`approved_by`),
  KEY `reviewed_by` (`reviewed_by`),
  KEY `idx_course` (`course_id`),
  KEY `idx_topic` (`topic_id`),
  KEY `idx_category` (`exam_category_id`),
  KEY `idx_instructor` (`instructor_id`),
  KEY `idx_difficulty` (`difficulty_level`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `question_topics` (`topic_id`) ON DELETE SET NULL,
  CONSTRAINT `questions_ibfk_3` FOREIGN KEY (`exam_category_id`) REFERENCES `exam_categories` (`exam_category_id`) ON DELETE SET NULL,
  CONSTRAINT `questions_ibfk_4` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`) ON DELETE SET NULL,
  CONSTRAINT `questions_ibfk_5` FOREIGN KEY (`approved_by`) REFERENCES `exam_committee_members` (`committee_member_id`) ON DELETE SET NULL,
  CONSTRAINT `questions_ibfk_6` FOREIGN KEY (`reviewed_by`) REFERENCES `exam_committee_members` (`committee_member_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `questions` VALUES
("1","1","1","1","1","What is the primary goal of nursing?","To cure diseases","To promote health and prevent illness","To prescribe medications","To perform surgeries","B","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("2","1","1","1","1","Which of the following is a core nursing value?","Profit maximization","Patient autonomy and dignity","Speed over quality","Cost reduction","B","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("3","1","2","1","1","Normal adult body temperature range is:","35.0-36.0°C","36.5-37.5°C","38.0-39.0°C","39.5-40.5°C","B","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("4","1","2","1","1","Normal adult resting heart rate is:","40-50 bpm","60-100 bpm","110-130 bpm","140-160 bpm","B","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("5","1","2","1","1","Normal adult blood pressure is approximately:","90/60 mmHg","120/80 mmHg","140/90 mmHg","160/100 mmHg","B","1","Medium","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("6","1","3","1","1","Hand hygiene should be performed:","Only before patient contact","Only after patient contact","Before and after patient contact","Once per shift","C","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("7","1","3","1","1","The most effective method to prevent infection transmission is:","Wearing gloves only","Hand washing","Using antibiotics","Isolation of all patients","B","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("8","1","4","1","1","When making a bed, the nurse should:","Work from far to near","Work from near to far","Start from the middle","Use any method","B","1","Medium","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("9","2","5","1","1","The heart has how many chambers?","Two","Three","Four","Five","C","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("10","2","5","1","1","Which chamber of the heart pumps blood to the body?","Right atrium","Right ventricle","Left atrium","Left ventricle","D","1","Medium","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("11","2","6","1","1","The primary function of the respiratory system is:","Digestion","Gas exchange","Blood production","Hormone secretion","B","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("12","2","6","1","1","The trachea divides into two:","Alveoli","Bronchi","Bronchioles","Capillaries","B","1","Medium","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("13","6","8","1","3","The primary role of a midwife is to:","Perform cesarean sections","Provide care during normal pregnancy and childbirth","Prescribe all medications","Manage surgical procedures","B","1","Easy","2","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("14","6","8","1","3","Midwifery practice is based on the principle that pregnancy is:","Always a medical emergency","A normal physiological process","A disease condition","A surgical event","B","1","Easy","2","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("15","6","9","1","3","A midwife must maintain patient confidentiality:","Only during working hours","Only for VIP patients","At all times","Only when convenient","C","1","Easy","2","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("16","10","11","1","4","Public health focuses on:","Individual patient care","Population health","Hospital management","Pharmaceutical sales","B","1","Easy","3","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("17","10","11","1","4","The three core functions of public health are:","Assessment, policy development, and assurance","Treatment, surgery, and medication","Diagnosis, prescription, and follow-up","Registration, billing, and discharge","A","1","Medium","3","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("18","10","12","1","4","Social determinants of health include:","Only genetic factors","Only medical care","Education, income, and environment","Only personal choices","C","1","Medium","3","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("19","13","14","1","5","General anesthesia causes:","Local numbness only","Loss of consciousness","Increased awareness","Muscle strengthening","B","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("20","13","14","1","5","Before anesthesia, patients should:","Eat a large meal","Fast for prescribed hours","Drink alcohol","Exercise vigorously","B","1","Easy","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("21","13","15","1","5","The anesthesia machine delivers:","Only oxygen","Anesthetic gases and oxygen","Only medications","Only air","B","1","Medium","1","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("22","16","17","1","6","Clinical chemistry analyzes:","Only blood cells","Chemical components in body fluids","Only bacteria","Only viruses","B","1","Easy","3","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("23","16","18","1","6","Personal protective equipment (PPE) in the lab includes:","Only gloves","Lab coat, gloves, and goggles","Only a mask","Regular clothes","B","1","Easy","3","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("24","16","19","1","6","Quality control in the laboratory ensures:","Fast results only","Accurate and reliable test results","Cheap testing","Minimal work","B","1","Medium","3","NULL","NULL","NULL","2026-01-31 11:14:39","2026-01-31 11:14:39","pending"),
("25","2","NULL","NULL","1","dfssdf","good","best","nice","all","A","1","Medium","NULL","NULL","NULL","NULL","2026-01-31 14:52:42","2026-01-31 14:52:42","pending"),
("26","3","NULL","NULL","1","dfhg df g","good","best","nice","all","A","1","Medium","NULL","NULL","NULL","NULL","2026-01-31 15:32:23","2026-01-31 15:32:23","pending");

-- Table: student_answers
DROP TABLE IF EXISTS `student_answers`;
CREATE TABLE `student_answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_answer` enum('A','B','C','D') DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `points_earned` decimal(10,2) DEFAULT 0.00,
  `answered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`answer_id`),
  UNIQUE KEY `unique_result_question` (`result_id`,`question_id`),
  KEY `idx_result` (`result_id`),
  KEY `idx_question` (`question_id`),
  KEY `idx_correct` (`is_correct`),
  CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `exam_results` (`result_id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `student_answers` VALUES
("1","1","1","B","1","12.50","2026-01-31 11:20:57"),
("2","1","2","B","1","12.50","2026-01-31 11:20:57"),
("3","1","3","B","1","12.50","2026-01-31 11:20:57"),
("4","1","4","A","0","0.00","2026-01-31 11:20:57"),
("5","1","5","B","1","12.50","2026-01-31 11:20:57"),
("6","1","6","C","1","12.50","2026-01-31 11:20:57"),
("7","1","7","B","1","12.50","2026-01-31 11:20:57"),
("8","1","8","B","1","12.50","2026-01-31 11:20:57"),
("9","2","1","B","1","12.50","2026-01-31 11:20:57"),
("10","2","2","B","1","12.50","2026-01-31 11:20:57"),
("11","2","3","A","0","0.00","2026-01-31 11:20:57"),
("12","2","4","B","1","12.50","2026-01-31 11:20:57"),
("13","2","5","A","0","0.00","2026-01-31 11:20:57"),
("14","2","6","C","1","12.50","2026-01-31 11:20:57"),
("15","2","7","B","1","12.50","2026-01-31 11:20:57"),
("16","2","8","B","1","12.50","2026-01-31 11:20:57"),
("17","3","13","B","1","33.33","2026-01-31 11:20:57"),
("18","3","14","B","1","33.33","2026-01-31 11:20:57"),
("19","3","15","C","1","33.34","2026-01-31 11:20:57"),
("20","4","16","B","1","33.33","2026-01-31 11:20:57"),
("21","4","17","A","1","33.33","2026-01-31 11:20:57"),
("22","4","18","A","0","0.00","2026-01-31 11:20:57"),
("23","5","19","B","1","33.33","2026-01-31 11:20:57"),
("24","5","20","B","1","33.33","2026-01-31 11:20:57"),
("25","5","21","B","1","33.34","2026-01-31 11:20:57"),
("26","6","22","B","1","33.33","2026-01-31 11:20:57"),
("27","6","23","NULL","0","0.00","2026-01-31 11:20:57"),
("28","6","24","B","1","33.33","2026-01-31 11:20:57");

-- Table: student_courses
DROP TABLE IF EXISTS `student_courses`;
CREATE TABLE `student_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_course` (`student_id`,`course_id`,`semester`),
  KEY `idx_student` (`student_id`),
  KEY `idx_course` (`course_id`),
  CONSTRAINT `student_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `student_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `student_courses` VALUES
("1","1","1","1","1","2026-01-31 11:14:36"),
("2","1","2","1","1","2026-01-31 11:14:36"),
("3","2","1","1","1","2026-01-31 11:14:36"),
("4","2","2","1","1","2026-01-31 11:14:36"),
("5","3","3","2","1","2026-01-31 11:14:36"),
("6","4","6","1","1","2026-01-31 11:14:36"),
("7","4","7","1","1","2026-01-31 11:14:36"),
("8","5","6","1","1","2026-01-31 11:14:36"),
("9","5","7","1","1","2026-01-31 11:14:36"),
("10","6","8","2","1","2026-01-31 11:14:36"),
("11","6","9","2","1","2026-01-31 11:14:36"),
("12","7","10","1","1","2026-01-31 11:14:36"),
("13","7","11","1","1","2026-01-31 11:14:36"),
("14","8","10","1","1","2026-01-31 11:14:36"),
("15","8","11","1","1","2026-01-31 11:14:36"),
("16","9","13","1","1","2026-01-31 11:14:36"),
("17","9","14","1","1","2026-01-31 11:14:36"),
("18","10","13","1","1","2026-01-31 11:14:36"),
("19","10","14","1","1","2026-01-31 11:14:36"),
("20","11","16","1","1","2026-01-31 11:14:36"),
("21","11","17","1","1","2026-01-31 11:14:36"),
("22","12","16","1","1","2026-01-31 11:14:36"),
("23","12","17","1","1","2026-01-31 11:14:36"),
("24","13","16","1","1","2026-01-31 11:14:36"),
("25","13","17","1","1","2026-01-31 11:14:36"),
("26","14","18","2","1","2026-01-31 11:14:36");

-- Table: students
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_code` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT 1,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `student_code` (`student_code`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_code` (`student_code`),
  KEY `idx_username` (`username`),
  KEY `idx_department` (`department_id`),
  KEY `idx_semester` (`semester`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `students` VALUES
("1","STU001","alem.h","pass12","Alem Hailu","alem.h@student.dmu.edu","+251922111001","1","1","Male","1","NULL","2026-01-31 11:14:35","2026-01-31 11:22:51"),
("2","STU002","bethel.k","pass123","Bethel Kebede","bethel.k@student.dmu.edu","+251922111002","1","1","Female","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("3","STU003","dawit.m","pass123","Dawit Mengistu","dawit.m@student.dmu.edu","+251922111003","1","2","Male","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("4","STU004","eden.t","pass123","Eden Tadesse","eden.t@student.dmu.edu","+251922111004","2","1","Female","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("5","STU005","frehiwot.a","pass123","Frehiwot Abebe","frehiwot.a@student.dmu.edu","+251922111005","2","1","Female","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("6","STU006","genet.w","pass123","Genet Worku","genet.w@student.dmu.edu","+251922111006","2","2","Female","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("7","STU007","habtamu.g","pass123","Habtamu Getachew","habtamu.g@student.dmu.edu","+251922111007","3","1","Male","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("8","STU008","hiwot.d","pass123","Hiwot Desta","hiwot.d@student.dmu.edu","+251922111008","3","1","Female","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("9","STU009","israel.b","pass123","Israel Bekele","israel.b@student.dmu.edu","+251922111009","4","1","Male","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("10","STU010","jerusalem.t","pass123","Jerusalem Tesfaye","jerusalem.t@student.dmu.edu","+251922111010","4","1","Female","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("11","STU011","kaleb.m","pass123","Kaleb Mekonnen","kaleb.m@student.dmu.edu","+251922111011","5","1","Male","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("12","STU012","lidya.a","pass123","Lidya Alemayehu","lidya.a@student.dmu.edu","+251922111012","5","1","Female","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("13","STU013","meron.h","pass123","Meron Haile","meron.h@student.dmu.edu","+251922111013","5","1","Female","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35"),
("14","STU014","nathan.g","pass123","Nathan Girma","nathan.g@student.dmu.edu","+251922111014","5","2","Male","1","NULL","2026-01-31 11:14:35","2026-01-31 11:14:35");

-- Table: system_settings
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_key` (`setting_key`),
  CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `administrators` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` VALUES
("1","system_name","Online Examination System","string","System name","NULL","2026-01-31 11:14:31"),
("2","institution_name","Debre Markos University","string","Institution name","NULL","2026-01-31 11:14:31"),
("3","pass_mark","50","number","Minimum passing percentage","NULL","2026-01-31 11:14:31"),
("4","grading_mode","standard","string","Grading mode: standard or custom","NULL","2026-01-31 11:14:31"),
("5","max_exam_attempts","1","number","Maximum exam attempts per student","NULL","2026-01-31 11:14:31"),
("6","auto_submit_enabled","true","boolean","Auto-submit exam when time expires","NULL","2026-01-31 11:14:31"),
("7","require_exam_approval","1","string","Require exam committee approval before exams are visible to students","NULL","2026-01-31 14:35:32"),
("8","auto_notify_committee","1","string","Automatically notify exam committee when exams are submitted","NULL","2026-01-31 14:35:32"),
("9","allow_instructor_resubmit","1","string","Allow instructors to resubmit rejected exams","NULL","2026-01-31 14:35:32");

-- Table: truefalse_question
DROP TABLE IF EXISTS `truefalse_question`;
CREATE TABLE `truefalse_question` (
  `question_id` int(10) NOT NULL AUTO_INCREMENT,
  `exam_id` int(20) NOT NULL,
  `semester` int(11) NOT NULL,
  `course_name` varchar(50) NOT NULL,
  `topic_id` int(11) DEFAULT NULL,
  `topic_name` varchar(100) DEFAULT NULL,
  `question` varchar(1000) NOT NULL,
  `Answer1` varchar(100) NOT NULL,
  `Answer2` varchar(100) NOT NULL,
  `Answer` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`question_id`),
  KEY `idx_exam` (`exam_id`),
  KEY `idx_course` (`course_name`),
  KEY `idx_semester` (`semester`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `truefalse_question` VALUES
("1","0","0","","NULL","NULL","","","","","2026-01-31 14:39:53"),
("2","0","0","","NULL","NULL","","","","","2026-01-31 14:40:41"),
("3","0","0","","NULL","NULL","","","","","2026-01-31 14:50:23");

SET FOREIGN_KEY_CHECKS=1;
