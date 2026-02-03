-- ============================================
-- ADD MEDICAL-SURGICAL NURSING I QUIZ
-- This creates a LIVE quiz for NURS103 that students can take now
-- ============================================

-- Insert the quiz exam (exam_category_id = 2 for Quiz)
-- Make it available NOW: starts 30 minutes ago, ends in 2 hours
INSERT INTO `exams` (`course_id`, `exam_category_id`, `exam_name`, `exam_date`, `start_time`, `end_time`, `duration_minutes`, `total_marks`, `pass_marks`, `instructions`, `is_active`, `approval_status`, `submitted_at`, `approved_by`, `approved_at`, `created_by`) VALUES
(3, 2, 'Medical-Surgical Nursing I - Quiz', CURDATE(), TIME(DATE_SUB(NOW(), INTERVAL 30 MINUTE)), TIME(DATE_ADD(NOW(), INTERVAL 2 HOUR)), 60, 20, 10, 'This is a quick quiz to test your understanding of medical-surgical nursing concepts. Answer all questions carefully.', 1, 'approved', NOW(), 1, NOW(), 2);

-- Get the exam_id that was just inserted
SET @exam_id = LAST_INSERT_ID();

-- Add some sample questions to the quiz (using existing questions from the question bank)
-- You can modify these to use different question IDs
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(@exam_id, 1, 1),
(@exam_id, 2, 2),
(@exam_id, 3, 3),
(@exam_id, 4, 4);

SELECT CONCAT('Quiz created successfully with exam_id: ', @exam_id) as message;
SELECT 'Students enrolled in NURS103 can now see and take this quiz!' as status;
