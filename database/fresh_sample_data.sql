-- ============================================
-- FRESH SAMPLE DATA FOR OES SYSTEM
-- Debre Markos University Health Campus
-- This script DELETES existing sample data and inserts fresh data
-- ============================================

USE `oes_professional`;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- CLEAR EXISTING SAMPLE DATA
-- ============================================
-- Delete in reverse order of dependencies

-- Clear audit logs
DELETE FROM audit_logs;

-- Clear exam results and answers
DELETE FROM exam_results;
DELETE FROM student_answers;

-- Clear exam questions and exams
DELETE FROM exam_questions;
DELETE FROM exams;

-- Clear questions and practice questions
DELETE FROM questions;
DELETE FROM practice_questions;

-- Clear enrollments
DELETE FROM student_courses;
DELETE FROM instructor_courses;

-- Clear users (keep only if you want to preserve manually created users)
-- Comment out these lines if you want to keep existing users
DELETE FROM students WHERE student_code LIKE 'STU%';
DELETE FROM instructors WHERE instructor_code LIKE 'INST%';
DELETE FROM department_heads WHERE head_code LIKE 'DH%';
DELETE FROM administrators WHERE username = 'admin';

-- Clear courses
DELETE FROM courses;

-- Clear departments and faculties
DELETE FROM departments;
DELETE FROM faculties;

-- Clear topics, categories, and grading config
DELETE FROM question_topics;
DELETE FROM exam_categories;
DELETE FROM grading_config;

-- Reset auto-increment counters
ALTER TABLE faculties AUTO_INCREMENT = 1;
ALTER TABLE departments AUTO_INCREMENT = 1;
ALTER TABLE courses AUTO_INCREMENT = 1;
ALTER TABLE administrators AUTO_INCREMENT = 1;
ALTER TABLE department_heads AUTO_INCREMENT = 1;
ALTER TABLE instructors AUTO_INCREMENT = 1;
ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE exam_categories AUTO_INCREMENT = 1;
ALTER TABLE question_topics AUTO_INCREMENT = 1;
ALTER TABLE questions AUTO_INCREMENT = 1;
ALTER TABLE practice_questions AUTO_INCREMENT = 1;
ALTER TABLE exams AUTO_INCREMENT = 1;
ALTER TABLE exam_questions AUTO_INCREMENT = 1;
ALTER TABLE grading_config AUTO_INCREMENT = 1;
ALTER TABLE audit_logs AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Existing data cleared. Ready for fresh import!' AS status;
SELECT 'Now run the insert_sample_data.sql file to import fresh data.' AS next_step;
