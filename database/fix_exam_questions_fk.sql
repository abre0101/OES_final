-- Fix exam_questions foreign key constraint issue
-- This script cleans up orphaned records and adds the constraints

USE `oes_professional`;

-- Step 1: Find and delete orphaned exam_questions (questions linked to non-existent exams)
DELETE FROM exam_questions 
WHERE exam_id NOT IN (SELECT exam_id FROM exams);

-- Step 2: Find and delete exam_questions with non-existent questions
DELETE FROM exam_questions 
WHERE question_id NOT IN (SELECT question_id FROM questions);

-- Step 3: Check if constraints already exist and drop them if they do
SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = 'oes_professional' 
    AND TABLE_NAME = 'exam_questions' 
    AND CONSTRAINT_NAME = 'fk_exam_questions_exam'
);

SET @sql = IF(@constraint_exists > 0, 
    'ALTER TABLE exam_questions DROP FOREIGN KEY fk_exam_questions_exam', 
    'SELECT "Constraint fk_exam_questions_exam does not exist"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @constraint_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_SCHEMA = 'oes_professional' 
    AND TABLE_NAME = 'exam_questions' 
    AND CONSTRAINT_NAME = 'fk_exam_questions_question'
);

SET @sql = IF(@constraint_exists > 0, 
    'ALTER TABLE exam_questions DROP FOREIGN KEY fk_exam_questions_question', 
    'SELECT "Constraint fk_exam_questions_question does not exist"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 4: Add the foreign key constraints
ALTER TABLE `exam_questions`
  ADD CONSTRAINT `fk_exam_questions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_questions_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

-- Step 5: Verify the fix
SELECT 'Foreign key constraints added successfully!' AS status;

-- Show current exam_questions count
SELECT COUNT(*) AS total_exam_questions FROM exam_questions;

-- Show exams with their question counts
SELECT 
    e.exam_id,
    e.exam_name,
    COUNT(eq.question_id) AS question_count
FROM exams e
LEFT JOIN exam_questions eq ON e.exam_id = eq.exam_id
GROUP BY e.exam_id, e.exam_name
ORDER BY e.exam_id;
