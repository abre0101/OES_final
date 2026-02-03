-- ============================================
-- UPDATE MEDICAL-SURGICAL NURSING I QUIZ TIME
-- Make it available NOW for students to take
-- ============================================

USE `oes_professional`;

-- Update the exam to be available NOW
-- Sets start time to 30 minutes ago and end time to 2 hours from now
UPDATE `exams` 
SET 
    `exam_date` = CURDATE(),
    `start_time` = TIME(DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
    `end_time` = TIME(DATE_ADD(NOW(), INTERVAL 2 HOUR)),
    `is_active` = 1,
    `approval_status` = 'approved'
WHERE 
    `course_id` = 3 
    AND `exam_name` = 'Medical-Surgical Nursing I - Quiz';

SELECT 'Quiz time updated successfully! Students can now take the exam.' as message;
