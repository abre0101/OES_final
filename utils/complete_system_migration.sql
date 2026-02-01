-- Complete System Migration SQL
-- Run this to ensure all features work properly
-- Date: January 30, 2026

-- ============================================
-- 1. APPROVAL SYSTEM COLUMNS
-- ============================================

-- Add approval columns to question_page table
ALTER TABLE question_page 
ADD COLUMN IF NOT EXISTS approval_status ENUM('pending', 'approved', 'revision', 'rejected') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS approved_by VARCHAR(100),
ADD COLUMN IF NOT EXISTS approval_date DATETIME,
ADD COLUMN IF NOT EXISTS revision_comments TEXT,
ADD COLUMN IF NOT EXISTS reviewed_by VARCHAR(100),
ADD COLUMN IF NOT EXISTS review_date DATETIME,
ADD COLUMN IF NOT EXISTS instructor_id VARCHAR(100);

-- ============================================
-- 2. POINT VALUES SYSTEM
-- ============================================

-- Add point_value column to question tables
ALTER TABLE question_page 
ADD COLUMN IF NOT EXISTS point_value INT DEFAULT 1 AFTER Answer;

ALTER TABLE truefalse_question 
ADD COLUMN IF NOT EXISTS point_value INT DEFAULT 1 AFTER Answer;

-- Update existing questions to have default point value
UPDATE question_page SET point_value = 1 WHERE point_value IS NULL OR point_value = 0;
UPDATE truefalse_question SET point_value = 1 WHERE point_value IS NULL OR point_value = 0;

-- ============================================
-- 3. QUESTION TOPICS/CATEGORIES
-- ============================================

-- Create question_topics table
CREATE TABLE IF NOT EXISTS `question_topics` (
    `topic_id` INT AUTO_INCREMENT PRIMARY KEY,
    `topic_name` VARCHAR(100) NOT NULL,
    `topic_description` TEXT,
    `course_name` VARCHAR(100),
    `chapter_number` INT,
    `created_by` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_topic` (`topic_name`, `course_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add topic columns to question tables
ALTER TABLE question_page 
ADD COLUMN IF NOT EXISTS topic_id INT AFTER course_name,
ADD COLUMN IF NOT EXISTS topic_name VARCHAR(100) AFTER topic_id;

ALTER TABLE truefalse_question 
ADD COLUMN IF NOT EXISTS topic_id INT AFTER course_name,
ADD COLUMN IF NOT EXISTS topic_name VARCHAR(100) AFTER topic_id;

-- ============================================
-- 4. NOTIFICATIONS SYSTEM
-- ============================================

-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
    `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(100) NOT NULL,
    `user_type` ENUM('student', 'instructor', 'exam_committee', 'admin') NOT NULL,
    `notification_type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255),
    `is_read` TINYINT(1) DEFAULT 0,
    `is_emailed` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`, `user_type`),
    INDEX `idx_read` (`is_read`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create email_settings table
CREATE TABLE IF NOT EXISTS `email_settings` (
    `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
    `smtp_host` VARCHAR(100),
    `smtp_port` INT DEFAULT 587,
    `smtp_username` VARCHAR(100),
    `smtp_password` VARCHAR(255),
    `from_email` VARCHAR(100),
    `from_name` VARCHAR(100),
    `email_enabled` TINYINT(1) DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default email settings
INSERT IGNORE INTO email_settings (setting_id, from_email, from_name, email_enabled) 
VALUES (1, 'noreply@dmu.edu', 'DMU Online Examination System', 0);

-- Create notification_preferences table
CREATE TABLE IF NOT EXISTS `notification_preferences` (
    `pref_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(100) NOT NULL,
    `user_type` ENUM('student', 'instructor', 'exam_committee', 'admin') NOT NULL,
    `email_notifications` TINYINT(1) DEFAULT 1,
    `exam_scheduled` TINYINT(1) DEFAULT 1,
    `results_ready` TINYINT(1) DEFAULT 1,
    `revision_requested` TINYINT(1) DEFAULT 1,
    `question_approved` TINYINT(1) DEFAULT 1,
    `system_alerts` TINYINT(1) DEFAULT 1,
    UNIQUE KEY `unique_user` (`user_id`, `user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. ACADEMIC CALENDAR
-- ============================================

-- Create academic_calendar table
CREATE TABLE IF NOT EXISTS `academic_calendar` (
    `calendar_id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(20) NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `semester_start` DATE NOT NULL,
    `semester_end` DATE NOT NULL,
    `registration_start` DATE,
    `registration_end` DATE,
    `exam_period_start` DATE,
    `exam_period_end` DATE,
    `holiday_name` VARCHAR(100),
    `holiday_date` DATE,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_semester` (`academic_year`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. GRADING CONFIGURATION
-- ============================================

-- Create grading_config table
CREATE TABLE IF NOT EXISTS `grading_config` (
    `config_id` INT AUTO_INCREMENT PRIMARY KEY,
    `config_key` VARCHAR(50) UNIQUE,
    `config_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default GPA 4.0 grading system
INSERT IGNORE INTO grading_config (config_key, config_value) VALUES 
('pass_mark', '50'),
('grading_mode', 'standard'),
('grade_boundaries', '{"A+":{"min":90,"max":100,"gpa":4,"status":"Excellent"},"A":{"min":85,"max":89.99,"gpa":3.75,"status":"Excellent"},"A-":{"min":80,"max":84.99,"gpa":3.5,"status":"Excellent"},"B+":{"min":75,"max":79.99,"gpa":3,"status":"Good"},"B":{"min":70,"max":74.99,"gpa":2.75,"status":"Good"},"B-":{"min":65,"max":69.99,"gpa":2.5,"status":"Good"},"C+":{"min":60,"max":64.99,"gpa":2,"status":"Satisfactory"},"C":{"min":55,"max":59.99,"gpa":1.75,"status":"Satisfactory"},"C-":{"min":50,"max":54.99,"gpa":1.5,"status":"Satisfactory"},"D":{"min":45,"max":49.99,"gpa":1,"status":"Pass"},"F":{"min":0,"max":44.99,"gpa":0,"status":"Fail"}}');

-- ============================================
-- 7. STUDENT ANSWERS TABLE (for analytics)
-- ============================================

-- Create student_answers table if not exists
CREATE TABLE IF NOT EXISTS `student_answers` (
    `answer_id` INT AUTO_INCREMENT PRIMARY KEY,
    `result_id` INT NOT NULL,
    `student_id` VARCHAR(50) NOT NULL,
    `question_id` INT NOT NULL,
    `selected_answer` CHAR(1),
    `is_correct` TINYINT(1) DEFAULT 0,
    `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_student` (`student_id`),
    INDEX `idx_question` (`question_id`),
    INDEX `idx_result` (`result_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 8. INDEXES FOR PERFORMANCE
-- ============================================

-- Add indexes for better query performance
ALTER TABLE question_page ADD INDEX IF NOT EXISTS `idx_approval_status` (`approval_status`);
ALTER TABLE question_page ADD INDEX IF NOT EXISTS `idx_course` (`course_name`);
ALTER TABLE question_page ADD INDEX IF NOT EXISTS `idx_topic` (`topic_id`);

ALTER TABLE result ADD INDEX IF NOT EXISTS `idx_student` (`Stud_ID`);
ALTER TABLE result ADD INDEX IF NOT EXISTS `idx_exam` (`exam_id`);

ALTER TABLE student ADD INDEX IF NOT EXISTS `idx_status` (`Status`);
ALTER TABLE instructor ADD INDEX IF NOT EXISTS `idx_status` (`Status`);
ALTER TABLE exam_committee ADD INDEX IF NOT EXISTS `idx_status` (`Status`);

-- ============================================
-- MIGRATION COMPLETE
-- ============================================

-- Verify tables exist
SELECT 'Migration Complete!' as Status,
       (SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = 'oes' AND table_name = 'question_topics') as question_topics_exists,
       (SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = 'oes' AND table_name = 'notifications') as notifications_exists,
       (SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = 'oes' AND table_name = 'academic_calendar') as academic_calendar_exists,
       (SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = 'oes' AND table_name = 'grading_config') as grading_config_exists;
