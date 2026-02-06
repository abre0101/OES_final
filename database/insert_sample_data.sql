-- ============================================
-- SAMPLE DATA FOR OES SYSTEM
-- Debre Markos University Health Campus
-- ============================================

USE `oes_professional`;

-- ============================================
-- 1. INSERT FACULTIES
-- ============================================
INSERT INTO `faculties` (`faculty_code`, `faculty_name`, `description`, `is_active`) VALUES
('FHS', 'Faculty of Health Sciences', 'Health and Medical Sciences Programs', 1);

-- ============================================
-- 2. INSERT DEPARTMENTS
-- ============================================
INSERT INTO `departments` (`faculty_id`, `department_code`, `department_name`, `description`, `is_active`) VALUES
(1, 'NURS', 'Nursing', 'Bachelor of Science in Nursing', 1),
(1, 'MIDW', 'Midwifery', 'Bachelor of Science in Midwifery', 1),
(1, 'PHO', 'Public Health Officer', 'Public Health Officer Program', 1),
(1, 'ANES', 'Anesthesia', 'Anesthesia Technology Program', 1),
(1, 'MLT', 'Medical Laboratory Technology', 'Medical Laboratory Science', 1);

-- ============================================
-- 3. INSERT COURSES
-- ============================================
-- Nursing Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(1, 'NURS101', 'Fundamentals of Nursing', 4, 1, 'Introduction to basic nursing principles and practices', 1),
(1, 'NURS102', 'Anatomy and Physiology for Nurses', 5, 1, 'Study of human body structure and function', 1),
(1, 'NURS103', 'Medical-Surgical Nursing I', 4, 2, 'Care of adult patients with medical-surgical conditions', 1);

-- Midwifery Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(2, 'MIDW101', 'Introduction to Midwifery', 3, 1, 'Fundamentals of midwifery practice', 1),
(2, 'MIDW102', 'Reproductive Health', 4, 1, 'Women\'s reproductive health and family planning', 1);

-- Public Health Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(3, 'PHO101', 'Introduction to Public Health', 3, 1, 'Overview of public health principles', 1),
(3, 'PHO102', 'Epidemiology', 4, 1, 'Study of disease patterns and prevention', 1);

-- Anesthesia Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(4, 'ANES101', 'Fundamentals of Anesthesia', 4, 1, 'Basic principles of anesthesia', 1),
(4, 'ANES102', 'Pharmacology for Anesthesia', 4, 1, 'Anesthetic drugs and their effects', 1);

-- Medical Laboratory Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(5, 'MLT101', 'Clinical Chemistry', 4, 1, 'Chemical analysis of body fluids', 1),
(5, 'MLT102', 'Hematology', 4, 1, 'Study of blood and blood disorders', 1);

-- ============================================
-- 4. INSERT ADMINISTRATORS
-- ============================================
INSERT INTO `administrators` (`username`, `password`, `full_name`, `email`, `phone`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@dmu.edu.et', '+251911000001', 1);
-- Password: password

-- ============================================
-- 5. INSERT DEPARTMENT HEADS
-- ============================================
INSERT INTO `department_heads` (`head_code`, `username`, `password`, `full_name`, `email`, `phone`, `department_id`, `is_active`) VALUES
('DH001', 'solomon.k', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Solomon Kebede', 'solomon.k@dmu.edu.et', '+251911234580', 1, 1),
('DH002', 'rahel.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Rahel Tesfaye', 'rahel.t@dmu.edu.et', '+251911234581', 2, 1),
('DH003', 'yared.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Yared Mengistu', 'yared.m@dmu.edu.et', '+251911234582', 3, 1),
('DH004', 'helen.w', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Helen Worku', 'helen.w@dmu.edu.et', '+251911234583', 4, 1),
('DH005', 'daniel.a', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Daniel Alemu', 'daniel.a@dmu.edu.et', '+251911234584', 5, 1);
-- Password: password

-- ============================================
-- 6. INSERT INSTRUCTORS
-- ============================================
INSERT INTO `instructors` (`instructor_code`, `username`, `password`, `full_name`, `email`, `phone`, `department_id`, `gender`, `is_active`) VALUES
('INST001', 'abebe.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Abebe Tadesse', 'abebe.t@dmu.edu.et', '+251911234567', 1, 'Male', 1),
('INST002', 'marta.g', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sr. Marta Gebre', 'marta.g@dmu.edu.et', '+251911234568', 1, 'Female', 1),
('INST003', 'sara.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sara Mulugeta', 'sara.m@dmu.edu.et', '+251911234569', 2, 'Female', 1),
('INST004', 'daniel.h', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Daniel Hailu', 'daniel.h@dmu.edu.et', '+251911234570', 3, 'Male', 1),
('INST005', 'helen.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Helen Tesfaye', 'helen.t@dmu.edu.et', '+251911234571', 4, 'Female', 1),
('INST006', 'yohannes.b', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Yohannes Bekele', 'yohannes.b@dmu.edu.et', '+251911234572', 5, 'Male', 1);
-- Password: password

-- ============================================
-- 7. INSERT STUDENTS
-- ============================================
INSERT INTO `students` (`student_code`, `username`, `password`, `full_name`, `email`, `phone`, `gender`, `department_id`, `academic_year`, `semester`, `is_active`) VALUES
('STU001', 'alem.h', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alem Hailu', 'alem.h@student.dmu.edu.et', '+251911111001', 'Male', 1, 'Year 1', 1, 1),
('STU002', 'bethel.k', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bethel Kebede', 'bethel.k@student.dmu.edu.et', '+251911111002', 'Female', 1, 'Year 1', 1, 1),
('STU003', 'chala.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chala Mengistu', 'chala.m@student.dmu.edu.et', '+251911111003', 'Male', 2, 'Year 1', 1, 1),
('STU004', 'eden.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eden Tesfaye', 'eden.t@student.dmu.edu.et', '+251911111004', 'Female', 3, 'Year 1', 1, 1),
('STU005', 'frehiwot.a', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Frehiwot Alemu', 'frehiwot.a@student.dmu.edu.et', '+251911111005', 'Female', 4, 'Year 1', 1, 1),
('STU006', 'genet.w', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Genet Worku', 'genet.w@student.dmu.edu.et', '+251911111006', 'Female', 5, 'Year 1', 1, 1);
-- Password: password

-- ============================================
-- 8. INSERT INSTRUCTOR COURSES
-- ============================================
INSERT INTO `instructor_courses` (`instructor_id`, `course_id`) VALUES
(1, 1), (1, 3), (2, 2),
(3, 4), (3, 5),
(4, 6), (4, 7),
(5, 8), (5, 9),
(6, 10), (6, 11);

-- ============================================
-- 9. INSERT STUDENT COURSES (ENROLLMENTS)
-- ============================================
INSERT INTO `student_courses` (`student_id`, `course_id`, `status`) VALUES
(1, 1, 'enrolled'), (1, 2, 'enrolled'),
(2, 1, 'enrolled'), (2, 2, 'enrolled'),
(3, 4, 'enrolled'), (3, 5, 'enrolled'),
(4, 6, 'enrolled'), (4, 7, 'enrolled'),
(5, 8, 'enrolled'), (5, 9, 'enrolled'),
(6, 10, 'enrolled'), (6, 11, 'enrolled');

-- ============================================
-- 10. INSERT EXAM CATEGORIES
-- ============================================
INSERT INTO `exam_categories` (`category_name`, `description`, `is_active`) VALUES
('Midterm', 'Mid-semester examination', 1),
('Final', 'End of semester examination', 1),
('Quiz', 'Short assessment', 1),
('Makeup', 'Makeup examination', 1);

-- ============================================
-- 11. INSERT GRADING CONFIG
-- ============================================
INSERT INTO `grading_config` (`grade_letter`, `min_percentage`, `max_percentage`, `gpa_value`, `status_label`, `display_order`, `is_active`) VALUES
('A+', 90.00, 100.00, 4.00, 'Excellent', 1, 1),
('A', 85.00, 89.99, 3.75, 'Excellent', 2, 1),
('A-', 80.00, 84.99, 3.50, 'Excellent', 3, 1),
('B+', 75.00, 79.99, 3.00, 'Good', 4, 1),
('B', 70.00, 74.99, 2.75, 'Good', 5, 1),
('B-', 65.00, 69.99, 2.50, 'Good', 6, 1),
('C+', 60.00, 64.99, 2.00, 'Satisfactory', 7, 1),
('C', 55.00, 59.99, 1.75, 'Satisfactory', 8, 1),
('C-', 50.00, 54.99, 1.50, 'Satisfactory', 9, 1),
('D', 45.00, 49.99, 1.00, 'Pass', 10, 1),
('F', 0.00, 44.99, 0.00, 'Fail', 11, 1);

-- ============================================
-- 12. INSERT QUESTION TOPICS
-- ============================================
INSERT INTO `question_topics` (`topic_name`, `description`) VALUES
('Nursing Fundamentals', 'Basic nursing concepts and skills'),
('Anatomy', 'Human body structure'),
('Physiology', 'Body functions and systems'),
('Pharmacology', 'Drug therapy and medications'),
('Maternal Health', 'Pregnancy and childbirth'),
('Public Health', 'Community health concepts'),
('Laboratory Techniques', 'Lab procedures and tests'),
('Anesthesia Basics', 'Anesthesia principles'),
('Patient Safety', 'Safety protocols and procedures');

-- ============================================
-- 13. INSERT QUESTIONS (MULTIPLE CHOICE & TRUE/FALSE)
-- ============================================

-- NURSING QUESTIONS (Course 1: Fundamentals of Nursing)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(1, 1, 'multiple_choice', 'What is the primary goal of nursing care?', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1),
(1, 1, 'multiple_choice', 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1),
(1, 9, 'multiple_choice', 'What is the correct order for hand hygiene?', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1),
-- True/False
(1, 9, 'true_false', 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1),
(1, 9, 'true_false', 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1),
(1, 1, 'true_false', 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1);

-- ANATOMY QUESTIONS (Course 2: Anatomy and Physiology)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(2, 2, 'multiple_choice', 'Which organ is responsible for pumping blood throughout the body?', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1),
(2, 2, 'multiple_choice', 'How many chambers does the human heart have?', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1),
(2, 3, 'multiple_choice', 'What is the normal resting heart rate for adults?', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1),
-- True/False
(2, 2, 'true_false', 'The human body has 206 bones in the adult skeleton.', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1),
(2, 3, 'true_false', 'The liver is located in the left upper quadrant of the abdomen.', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1),
(2, 2, 'true_false', 'The skin is the largest organ in the human body.', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1);

-- MIDWIFERY QUESTIONS (Course 4: Introduction to Midwifery)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(4, 5, 'multiple_choice', 'What is the normal duration of pregnancy?', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3),
(4, 5, 'multiple_choice', 'Which trimester is considered the most critical for fetal development?', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3),
-- True/False
(4, 5, 'true_false', 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3),
(4, 5, 'true_false', 'Fetal movements should be felt by the mother starting from the first trimester.', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3),
(4, 5, 'true_false', 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3);

-- PUBLIC HEALTH QUESTIONS (Course 6: Introduction to Public Health)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(6, 6, 'multiple_choice', 'What is the primary focus of public health?', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4),
(6, 6, 'multiple_choice', 'Which of the following is a communicable disease?', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4),
-- True/False
(6, 6, 'true_false', 'Vaccination is one of the most cost-effective public health interventions.', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4),
(6, 6, 'true_false', 'Antibiotics are effective against viral infections like the common cold.', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4),
(6, 6, 'true_false', 'Hand washing is one of the most effective ways to prevent disease transmission.', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4);

-- ANESTHESIA QUESTIONS (Course 8: Fundamentals of Anesthesia)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(8, 8, 'multiple_choice', 'What is the primary purpose of anesthesia?', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5),
(8, 8, 'multiple_choice', 'Which type of anesthesia affects the entire body?', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5),
-- True/False
(8, 8, 'true_false', 'General anesthesia causes complete loss of consciousness.', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5),
(8, 8, 'true_false', 'Local anesthesia affects the entire body.', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5),
(8, 9, 'true_false', 'Oxygen saturation must be continuously monitored during anesthesia.', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5);

-- MEDICAL LABORATORY QUESTIONS (Course 10: Clinical Chemistry)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(10, 7, 'multiple_choice', 'What is the normal pH range of human blood?', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6),
(10, 7, 'multiple_choice', 'Which blood type is considered the universal donor?', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6),
-- True/False
(10, 7, 'true_false', 'Blood type O negative is considered the universal donor.', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6),
(10, 7, 'true_false', 'Hemoglobin is found in white blood cells.', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6),
(10, 7, 'true_false', 'Red blood cells are responsible for transporting oxygen throughout the body.', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6);

-- ============================================
-- 14. INSERT SAMPLE EXAMS
-- ============================================
INSERT INTO `exams` (`course_id`, `exam_category_id`, `exam_name`, `exam_date`, `start_time`, `end_time`, `duration_minutes`, `total_marks`, `pass_marks`, `instructions`, `is_active`, `approval_status`, `submitted_at`, `approved_by`, `approved_at`, `created_by`) VALUES
(1, 1, 'Fundamentals of Nursing - Midterm', '2026-03-15', '09:00:00', '10:30:00', 90, 100, 50, 'Read all questions carefully. Choose the best answer. No cheating allowed.', 1, 'approved', '2026-02-01 10:00:00', 1, '2026-02-02 14:00:00', 1),
(2, 1, 'Anatomy and Physiology - Midterm', '2026-03-16', '09:00:00', '10:30:00', 90, 100, 50, 'Answer all questions. Use of notes is not permitted.', 1, 'approved', '2026-02-01 11:00:00', 1, '2026-02-02 15:00:00', 1);

-- ============================================
-- 15. INSERT EXAM QUESTIONS (LINK EXAMS TO QUESTIONS)
-- ============================================
-- Exam 1: Fundamentals of Nursing (Questions 1-6: Mix of MC and T/F)
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(1, 1, 1), (1, 2, 2), (1, 3, 3), (1, 4, 4), (1, 5, 5), (1, 6, 6);

-- Exam 2: Anatomy and Physiology (Questions 7-12: Mix of MC and T/F)
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(2, 7, 1), (2, 8, 2), (2, 9, 3), (2, 10, 4), (2, 11, 5), (2, 12, 6);

-- ============================================
-- DATA IMPORT COMPLETE
-- ============================================
SELECT 'Sample data imported successfully!' AS status;
