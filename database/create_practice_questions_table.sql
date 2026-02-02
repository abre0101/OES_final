-- Create Practice Questions Table
-- This table stores questions specifically for student practice
-- Separate from exam questions to avoid overlap

CREATE TABLE IF NOT EXISTS `practice_questions` (
  `practice_question_id` INT(11) NOT NULL AUTO_INCREMENT,
  `course_id` INT(11) NOT NULL,
  `question_text` TEXT NOT NULL,
  `question_type` ENUM('multiple_choice', 'true_false', 'short_answer') NOT NULL DEFAULT 'multiple_choice',
  `option_a` VARCHAR(500) DEFAULT NULL,
  `option_b` VARCHAR(500) DEFAULT NULL,
  `option_c` VARCHAR(500) DEFAULT NULL,
  `option_d` VARCHAR(500) DEFAULT NULL,
  `correct_answer` VARCHAR(500) NOT NULL,
  `explanation` TEXT DEFAULT NULL,
  `difficulty_level` ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
  `topic` VARCHAR(200) DEFAULT NULL,
  `points` INT(11) DEFAULT 1,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`practice_question_id`),
  KEY `course_id` (`course_id`),
  KEY `created_by` (`created_by`),
  KEY `question_type` (`question_type`),
  KEY `difficulty_level` (`difficulty_level`),
  CONSTRAINT `fk_practice_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_practice_instructor` FOREIGN KEY (`created_by`) REFERENCES `instructors` (`instructor_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Practice Results Table
-- Track student practice attempts and scores
CREATE TABLE IF NOT EXISTS `practice_results` (
  `practice_result_id` INT(11) NOT NULL AUTO_INCREMENT,
  `student_id` INT(11) NOT NULL,
  `course_name` VARCHAR(200) NOT NULL,
  `total_questions` INT(11) NOT NULL,
  `correct_answers` INT(11) NOT NULL,
  `wrong_answers` INT(11) NOT NULL,
  `percentage_score` DECIMAL(5,2) NOT NULL,
  `time_taken_seconds` INT(11) DEFAULT NULL,
  `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`practice_result_id`),
  KEY `student_id` (`student_id`),
  KEY `course_name` (`course_name`),
  KEY `completed_at` (`completed_at`),
  CONSTRAINT `fk_practice_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Practice Answers Table
-- Store individual answers for review
CREATE TABLE IF NOT EXISTS `practice_answers` (
  `practice_answer_id` INT(11) NOT NULL AUTO_INCREMENT,
  `practice_result_id` INT(11) NOT NULL,
  `practice_question_id` INT(11) NOT NULL,
  `student_answer` VARCHAR(500) DEFAULT NULL,
  `is_correct` TINYINT(1) NOT NULL,
  `answered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`practice_answer_id`),
  KEY `practice_result_id` (`practice_result_id`),
  KEY `practice_question_id` (`practice_question_id`),
  CONSTRAINT `fk_practice_result` FOREIGN KEY (`practice_result_id`) REFERENCES `practice_results` (`practice_result_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_practice_question` FOREIGN KEY (`practice_question_id`) REFERENCES `practice_questions` (`practice_question_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_practice_active ON practice_questions(is_active);
CREATE INDEX idx_practice_course_active ON practice_questions(course_id, is_active);
CREATE INDEX idx_practice_student_course ON practice_results(student_id, course_name);
