-- ============================================
-- UPDATE EXAM APPROVAL HISTORY DATA
-- This script clears and repopulates the exam_approval_history table
-- ============================================

-- Clear existing approval history
TRUNCATE TABLE `exam_approval_history`;

-- Insert new realistic approval history data
INSERT INTO `exam_approval_history` (`exam_id`, `action`, `performed_by`, `performed_by_type`, `comments`, `previous_status`, `new_status`, `created_at`) VALUES
-- Exam 1: Fundamentals of Nursing - Midterm (Approved by Nursing Dept Head)
(1, 'submitted', 1, 'instructor', 'Submitting Fundamentals of Nursing midterm exam for department approval. All questions have been reviewed and aligned with course objectives.', 'draft', 'pending', '2026-02-01 10:30:00'),
(1, 'approved', 1, 'department_head', 'Excellent exam structure. Questions demonstrate good understanding of fundamental nursing concepts. Approved for administration.', 'pending', 'approved', '2026-02-01 14:45:00'),

-- Exam 2: Anatomy and Physiology - Midterm (Approved by Nursing Dept Head)
(2, 'submitted', 1, 'instructor', 'Anatomy and Physiology midterm ready for review. Covers all major body systems as per curriculum.', 'draft', 'pending', '2026-02-02 09:15:00'),
(2, 'approved', 1, 'department_head', 'Well-balanced exam covering essential anatomy topics. Question difficulty is appropriate for second-year students. Approved.', 'pending', 'approved', '2026-02-02 16:20:00'),

-- Exam 3: Introduction to Midwifery - Midterm (Approved by Midwifery Dept Head with revision)
(3, 'submitted', 3, 'instructor', 'Midwifery midterm exam submission. Focuses on prenatal care and maternal health assessment.', 'draft', 'pending', '2026-02-03 08:00:00'),
(3, 'revision_requested', 2, 'department_head', 'Please add 2 more questions on postpartum care to ensure comprehensive coverage. Otherwise excellent work.', 'pending', 'revision', '2026-02-03 11:30:00'),
(3, 'submitted', 3, 'instructor', 'Revised exam with additional postpartum care questions as requested.', 'revision', 'pending', '2026-02-03 15:00:00'),
(3, 'approved', 2, 'department_head', 'Perfect! The additional questions provide better balance. Approved for use.', 'pending', 'approved', '2026-02-03 16:45:00'),

-- Exam 4: Introduction to Public Health - Midterm (Approved by Midwifery Dept Head)
(4, 'submitted', 4, 'instructor', 'Public Health midterm covering epidemiology, health promotion, and disease prevention.', 'draft', 'pending', '2026-02-04 10:00:00'),
(4, 'approved', 2, 'department_head', 'Comprehensive coverage of public health fundamentals. Questions are clear and well-structured. Approved.', 'pending', 'approved', '2026-02-04 13:30:00'),

-- Exam 5: Fundamentals of Anesthesia - Midterm (Approved by Midwifery Dept Head)
(5, 'submitted', 5, 'instructor', 'Anesthesia midterm focusing on pharmacology and patient monitoring techniques.', 'draft', 'pending', '2026-02-05 09:30:00'),
(5, 'approved', 2, 'department_head', 'Excellent technical content. Safety protocols are well emphasized. Approved without changes.', 'pending', 'approved', '2026-02-05 14:00:00'),

-- Exam 6: Clinical Chemistry - Midterm (Rejected then approved by Public Health Officer Dept Head)
(6, 'submitted', 6, 'instructor', 'Clinical Chemistry midterm covering laboratory techniques and diagnostic procedures.', 'draft', 'pending', '2026-02-06 08:45:00'),
(6, 'rejected', 3, 'department_head', 'Several questions contain outdated reference ranges. Please update to current clinical standards before resubmission.', 'pending', 'rejected', '2026-02-06 12:00:00'),
(6, 'submitted', 6, 'instructor', 'Resubmitting with updated reference ranges per latest clinical guidelines.', 'rejected', 'pending', '2026-02-07 10:00:00'),
(6, 'approved', 3, 'department_head', 'All reference ranges now current. Quality exam that tests essential laboratory knowledge. Approved.', 'pending', 'approved', '2026-02-07 15:30:00');

SELECT 'Exam approval history updated successfully!' as message;
SELECT COUNT(*) as total_records FROM exam_approval_history;
