-- ============================================
-- SAMPLE DATA FOR OES SYSTEM
-- Debre Markos University Health Campus
-- ============================================

USE `oes_professional`;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. INSERT FACULTIES
-- ============================================
INSERT IGNORE INTO `faculties` (`faculty_code`, `faculty_name`, `description`, `is_active`) VALUES
('FHS', 'Faculty of Health Sciences', 'Health and Medical Sciences Programs', 1);

-- ============================================
-- 2. INSERT DEPARTMENTS
-- ============================================
INSERT IGNORE INTO `departments` (`faculty_id`, `department_code`, `department_name`, `description`, `is_active`) VALUES
(1, 'NURS', 'Nursing', 'Bachelor of Science in Nursing', 1),
(1, 'MIDW', 'Midwifery', 'Bachelor of Science in Midwifery', 1),
(1, 'PHO', 'Public Health Officer', 'Public Health Officer Program', 1),
(1, 'ANES', 'Anesthesia', 'Anesthesia Technology Program', 1),
(1, 'MLT', 'Medical Laboratory Technology', 'Medical Laboratory Science', 1);

-- ============================================
-- 3. INSERT COURSES
-- ============================================
-- Nursing Courses
INSERT IGNORE INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(1, 'NURS101', 'Fundamentals of Nursing', 4, 1, 'Introduction to basic nursing principles and practices', 1),
(1, 'NURS102', 'Anatomy and Physiology for Nurses', 5, 1, 'Study of human body structure and function', 1),
(1, 'NURS103', 'Medical-Surgical Nursing I', 4, 2, 'Care of adult patients with medical-surgical conditions', 1);

-- Midwifery Courses
INSERT IGNORE INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(2, 'MIDW101', 'Introduction to Midwifery', 3, 1, 'Fundamentals of midwifery practice', 1),
(2, 'MIDW102', 'Reproductive Health', 4, 1, 'Women\'s reproductive health and family planning', 1);

-- Public Health Courses
INSERT IGNORE INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(3, 'PHO101', 'Introduction to Public Health', 3, 1, 'Overview of public health principles', 1),
(3, 'PHO102', 'Epidemiology', 4, 1, 'Study of disease patterns and prevention', 1);

-- Anesthesia Courses
INSERT IGNORE INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(4, 'ANES101', 'Fundamentals of Anesthesia', 4, 1, 'Basic principles of anesthesia', 1),
(4, 'ANES102', 'Pharmacology for Anesthesia', 4, 1, 'Anesthetic drugs and their effects', 1);

-- Medical Laboratory Courses
INSERT IGNORE INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(5, 'MLT101', 'Clinical Chemistry', 4, 1, 'Chemical analysis of body fluids', 1),
(5, 'MLT102', 'Hematology', 4, 1, 'Study of blood and blood disorders', 1);

-- ============================================
-- 4. INSERT ADMINISTRATORS
-- ============================================
INSERT IGNORE INTO `administrators` (`username`, `password`, `full_name`, `email`, `phone`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@dmu.edu.et', '+251911000001', 1);
-- Password: password

-- ============================================
-- 5. INSERT DEPARTMENT HEADS
-- ============================================
INSERT IGNORE INTO `department_heads` (`head_code`, `username`, `password`, `full_name`, `email`, `phone`, `department_id`, `is_active`) VALUES
('DH001', 'solomon.k', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Solomon Kebede', 'solomon.k@dmu.edu.et', '+251911234580', 1, 1),
('DH002', 'rahel.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Rahel Tesfaye', 'rahel.t@dmu.edu.et', '+251911234581', 2, 1),
('DH003', 'yared.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Yared Mengistu', 'yared.m@dmu.edu.et', '+251911234582', 3, 1),
('DH004', 'helen.w', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Helen Worku', 'helen.w@dmu.edu.et', '+251911234583', 4, 1),
('DH005', 'daniel.a', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Daniel Alemu', 'daniel.a@dmu.edu.et', '+251911234584', 5, 1);
-- Password: password

-- ============================================
-- 6. INSERT INSTRUCTORS
-- ============================================
INSERT IGNORE INTO `instructors` (`instructor_code`, `username`, `password`, `full_name`, `email`, `phone`, `department_id`, `gender`, `is_active`) VALUES
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
INSERT IGNORE INTO `students` (`student_code`, `username`, `password`, `full_name`, `email`, `phone`, `gender`, `department_id`, `academic_year`, `semester`, `is_active`) VALUES
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
INSERT IGNORE INTO `instructor_courses` (`instructor_id`, `course_id`) VALUES
(1, 1), (1, 3), (2, 2),
(3, 4), (3, 5),
(4, 6), (4, 7),
(5, 8), (5, 9),
(6, 10), (6, 11);

-- ============================================
-- 9. INSERT STUDENT COURSES (ENROLLMENTS)
-- ============================================
INSERT IGNORE INTO `student_courses` (`student_id`, `course_id`, `status`) VALUES
(1, 1, 'enrolled'), (1, 2, 'enrolled'),
(2, 1, 'enrolled'), (2, 2, 'enrolled'),
(3, 4, 'enrolled'), (3, 5, 'enrolled'),
(4, 6, 'enrolled'), (4, 7, 'enrolled'),
(5, 8, 'enrolled'), (5, 9, 'enrolled'),
(6, 10, 'enrolled'), (6, 11, 'enrolled');

-- ============================================
-- 10. INSERT EXAM CATEGORIES
-- ============================================
INSERT IGNORE INTO `exam_categories` (`category_name`, `description`, `is_active`) VALUES
('Midterm', 'Mid-semester examination', 1),
('Final', 'End of semester examination', 1),
('Quiz', 'Short assessment', 1),
('Makeup', 'Makeup examination', 1);

-- ============================================
-- 11. INSERT GRADING CONFIG
-- ============================================
INSERT IGNORE INTO `grading_config` (`grade_letter`, `min_percentage`, `max_percentage`, `gpa_value`, `status_label`, `display_order`, `is_active`) VALUES
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
INSERT IGNORE INTO `question_topics` (`topic_name`, `description`) VALUES
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
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(1, 1, 'multiple_choice', 'What is the primary goal of nursing care?', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1),
(1, 1, 'multiple_choice', 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1),
(1, 9, 'multiple_choice', 'What is the correct order for hand hygiene?', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1),
-- True/False
(1, 9, 'true_false', 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1),
(1, 9, 'true_false', 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1),
(1, 1, 'true_false', 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1);

-- ANATOMY QUESTIONS (Course 2: Anatomy and Physiology)
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(2, 2, 'multiple_choice', 'Which organ is responsible for pumping blood throughout the body?', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1),
(2, 2, 'multiple_choice', 'How many chambers does the human heart have?', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1),
(2, 3, 'multiple_choice', 'What is the normal resting heart rate for adults?', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1),
-- True/False
(2, 2, 'true_false', 'The human body has 206 bones in the adult skeleton.', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1),
(2, 3, 'true_false', 'The liver is located in the left upper quadrant of the abdomen.', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1),
(2, 2, 'true_false', 'The skin is the largest organ in the human body.', 'True', 'False', NULL, NULL, 'True', 1, 'The skin is the largest organ, covering the entire body surface.', 1);

-- MIDWIFERY QUESTIONS (Course 4: Introduction to Midwifery)
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(4, 5, 'multiple_choice', 'What is the normal duration of pregnancy?', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3),
(4, 5, 'multiple_choice', 'Which trimester is considered the most critical for fetal development?', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3),
-- True/False
(4, 5, 'true_false', 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3),
(4, 5, 'true_false', 'Fetal movements should be felt by the mother starting from the first trimester.', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3),
(4, 5, 'true_false', 'Folic acid supplementation helps prevent neural tube defects in developing fetuses.', 'True', 'False', NULL, NULL, 'True', 1, 'Folic acid is essential for preventing neural tube defects and should be taken before and during pregnancy.', 3);

-- PUBLIC HEALTH QUESTIONS (Course 6: Introduction to Public Health)
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(6, 6, 'multiple_choice', 'What is the primary focus of public health?', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4),
(6, 6, 'multiple_choice', 'Which of the following is a communicable disease?', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4),
-- True/False
(6, 6, 'true_false', 'Vaccination is one of the most cost-effective public health interventions.', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4),
(6, 6, 'true_false', 'Antibiotics are effective against viral infections like the common cold.', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4),
(6, 6, 'true_false', 'Hand washing is one of the most effective ways to prevent disease transmission.', 'True', 'False', NULL, NULL, 'True', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4);

-- ANESTHESIA QUESTIONS (Course 8: Fundamentals of Anesthesia)
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(8, 8, 'multiple_choice', 'What is the primary purpose of anesthesia?', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5),
(8, 8, 'multiple_choice', 'Which type of anesthesia affects the entire body?', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5),
-- True/False
(8, 8, 'true_false', 'General anesthesia causes complete loss of consciousness.', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5),
(8, 8, 'true_false', 'Local anesthesia affects the entire body.', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5),
(8, 9, 'true_false', 'Oxygen saturation must be continuously monitored during anesthesia.', 'True', 'False', NULL, NULL, 'True', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5);

-- MEDICAL LABORATORY QUESTIONS (Course 10: Clinical Chemistry)
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Multiple Choice
(10, 7, 'multiple_choice', 'What is the normal pH range of human blood?', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6),
(10, 7, 'multiple_choice', 'Which blood type is considered the universal donor?', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6),
-- True/False
(10, 7, 'true_false', 'Blood type O negative is considered the universal donor.', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6),
(10, 7, 'true_false', 'Hemoglobin is found in white blood cells.', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6),
(10, 7, 'true_false', 'Red blood cells are responsible for transporting oxygen throughout the body.', 'True', 'False', NULL, NULL, 'True', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6);

-- ============================================
-- ADDITIONAL NURSING QUESTIONS (Course 1)
-- ============================================
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(1, 1, 'multiple_choice', 'What does the acronym ADPIE stand for in the nursing process?', 'Assess, Diagnose, Plan, Implement, Evaluate', 'Analyze, Develop, Perform, Inspect, Execute', 'Admit, Discharge, Prescribe, Inject, Examine', 'Advise, Direct, Prepare, Intervene, Exit', 'A', 1, 'ADPIE represents the five steps of the nursing process.', 1),
(1, 9, 'multiple_choice', 'How long should you scrub your hands during hand washing?', '5 seconds', '10 seconds', '20 seconds', '60 seconds', 'C', 1, 'Proper hand washing requires at least 20 seconds of scrubbing.', 1),
(1, 1, 'multiple_choice', 'Which vital sign is measured in beats per minute?', 'Temperature', 'Blood pressure', 'Pulse', 'Respiratory rate', 'C', 1, 'Pulse is measured in beats per minute (bpm).', 1),
(1, 9, 'multiple_choice', 'What is the proper angle for intramuscular injection?', '15 degrees', '45 degrees', '90 degrees', '180 degrees', 'C', 1, 'Intramuscular injections are given at a 90-degree angle.', 1),
(1, 1, 'multiple_choice', 'What is the normal body temperature in Celsius?', '35.5°C', '37°C', '38.5°C', '40°C', 'B', 1, 'Normal body temperature is approximately 37°C or 98.6°F.', 1),
(1, 9, 'true_false', 'Nurses should always identify patients using two identifiers before administering medication.', 'True', 'False', NULL, NULL, 'True', 1, 'Two patient identifiers (name and date of birth) are required for patient safety.', 1),
(1, 1, 'true_false', 'Documentation in nursing should be done at the end of the shift.', 'True', 'False', NULL, NULL, 'False', 1, 'Documentation should be done immediately after care is provided to ensure accuracy.', 1),
(1, 9, 'true_false', 'Standard precautions should be used with all patients regardless of diagnosis.', 'True', 'False', NULL, NULL, 'True', 1, 'Standard precautions are infection control practices used with all patients.', 1),
(1, 1, 'true_false', 'Nurses can delegate assessment tasks to unlicensed assistive personnel.', 'True', 'False', NULL, NULL, 'False', 1, 'Assessment is a nursing responsibility that cannot be delegated to unlicensed personnel.', 1),
(1, 9, 'true_false', 'Gloves should be changed between tasks on the same patient.', 'True', 'False', NULL, NULL, 'True', 1, 'Gloves should be changed to prevent cross-contamination between different body sites.', 1);

-- ============================================
-- ADDITIONAL ANATOMY QUESTIONS (Course 2)
-- ============================================
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(2, 2, 'multiple_choice', 'Which bone protects the brain?', 'Femur', 'Skull', 'Ribs', 'Pelvis', 'B', 1, 'The skull (cranium) protects the brain.', 1),
(2, 3, 'multiple_choice', 'What is the largest artery in the human body?', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 1, 'The aorta is the largest artery, carrying blood from the heart to the body.', 1),
(2, 2, 'multiple_choice', 'How many pairs of ribs does a human have?', '10', '12', '14', '16', 'B', 1, 'Humans have 12 pairs of ribs (24 ribs total).', 1),
(2, 3, 'multiple_choice', 'Which organ produces insulin?', 'Liver', 'Pancreas', 'Kidney', 'Spleen', 'B', 1, 'The pancreas produces insulin to regulate blood sugar.', 1),
(2, 2, 'multiple_choice', 'What is the longest bone in the human body?', 'Humerus', 'Tibia', 'Femur', 'Radius', 'C', 1, 'The femur (thigh bone) is the longest and strongest bone.', 1),
(2, 3, 'true_false', 'The lungs are located in the thoracic cavity.', 'True', 'False', NULL, NULL, 'True', 1, 'The lungs are housed in the thoracic (chest) cavity, protected by the rib cage.', 1),
(2, 2, 'true_false', 'The human spine has 33 vertebrae.', 'True', 'False', NULL, NULL, 'True', 1, 'The spine consists of 33 vertebrae: 7 cervical, 12 thoracic, 5 lumbar, 5 sacral (fused), and 4 coccygeal (fused).', 1),
(2, 3, 'true_false', 'The kidneys filter approximately 180 liters of blood per day.', 'True', 'False', NULL, NULL, 'True', 1, 'The kidneys filter about 180 liters of blood daily, producing 1-2 liters of urine.', 1),
(2, 2, 'true_false', 'Cartilage is a type of connective tissue.', 'True', 'False', NULL, NULL, 'True', 1, 'Cartilage is a flexible connective tissue found in joints, ears, nose, and other structures.', 1),
(2, 3, 'true_false', 'The stomach is located in the right upper quadrant of the abdomen.', 'True', 'False', NULL, NULL, 'False', 1, 'The stomach is primarily located in the left upper quadrant of the abdomen.', 1);

-- ============================================
-- ADDITIONAL MEDICAL-SURGICAL NURSING QUESTIONS (Course 3)
-- ============================================
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(3, 4, 'multiple_choice', 'What is the antidote for warfarin overdose?', 'Protamine sulfate', 'Vitamin K', 'Naloxone', 'Flumazenil', 'B', 1, 'Vitamin K is the antidote for warfarin (Coumadin) overdose.', 1),
(3, 9, 'multiple_choice', 'What is the priority nursing action for a patient with chest pain?', 'Document the pain', 'Administer oxygen', 'Call the family', 'Ambulate the patient', 'B', 1, 'Administering oxygen is priority to improve cardiac oxygenation.', 1),
(3, 4, 'multiple_choice', 'Which medication is used to treat hypertension?', 'Insulin', 'Lisinopril', 'Aspirin', 'Metformin', 'B', 1, 'Lisinopril is an ACE inhibitor used to treat high blood pressure.', 1),
(3, 9, 'multiple_choice', 'What is the normal range for adult blood pressure?', '90/60 to 120/80 mmHg', '130/90 to 150/100 mmHg', '160/100 to 180/110 mmHg', '200/120 to 220/130 mmHg', 'A', 1, 'Normal blood pressure is less than 120/80 mmHg.', 1),
(3, 4, 'multiple_choice', 'Which lab value indicates kidney function?', 'Hemoglobin', 'Creatinine', 'Glucose', 'Cholesterol', 'B', 1, 'Creatinine levels indicate how well the kidneys are filtering waste.', 1),
(3, 9, 'true_false', 'Patients with diabetes should skip meals if their blood sugar is high.', 'True', 'False', NULL, NULL, 'False', 1, 'Diabetic patients should maintain regular meal schedules and work with healthcare providers to adjust medications.', 1),
(3, 4, 'true_false', 'Aspirin is an anticoagulant medication.', 'True', 'False', NULL, NULL, 'True', 1, 'Aspirin inhibits platelet aggregation and acts as an anticoagulant.', 1),
(3, 9, 'true_false', 'A patient with a myocardial infarction should be kept on bed rest initially.', 'True', 'False', NULL, NULL, 'True', 1, 'Initial bed rest reduces cardiac workload and oxygen demand after a heart attack.', 1),
(3, 4, 'true_false', 'Antibiotics are effective against all types of infections.', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1),
(3, 9, 'true_false', 'Patients should be assessed for pain using a standardized pain scale.', 'True', 'False', NULL, NULL, 'True', 1, 'Standardized pain scales ensure consistent and accurate pain assessment.', 1);

-- ============================================
-- ADDITIONAL MIDWIFERY QUESTIONS (Course 4 & 5)
-- ============================================
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(4, 5, 'multiple_choice', 'What is the first stage of labor?', 'Delivery of the baby', 'Cervical dilation', 'Delivery of placenta', 'Recovery', 'B', 1, 'The first stage of labor involves cervical dilation from 0 to 10 cm.', 3),
(4, 5, 'multiple_choice', 'At what week is a fetus considered full-term?', '32 weeks', '35 weeks', '37 weeks', '42 weeks', 'C', 1, 'A pregnancy is considered full-term at 37 weeks.', 3),
(4, 5, 'multiple_choice', 'What is the normal fetal heart rate?', '60-80 bpm', '80-100 bpm', '110-160 bpm', '180-200 bpm', 'C', 1, 'Normal fetal heart rate is 110-160 beats per minute.', 3),
(4, 5, 'multiple_choice', 'Which hormone maintains pregnancy?', 'Estrogen', 'Progesterone', 'Testosterone', 'Insulin', 'B', 1, 'Progesterone is essential for maintaining pregnancy.', 3),
(4, 5, 'multiple_choice', 'What is the recommended weight gain during pregnancy for normal BMI?', '5-10 kg', '11-16 kg', '20-25 kg', '30-35 kg', 'B', 1, 'Recommended weight gain for normal BMI is 11-16 kg (25-35 lbs).', 3),
(5, 5, 'multiple_choice', 'What is postpartum hemorrhage defined as?', 'Blood loss >500 ml after vaginal delivery', 'Blood loss >100 ml after delivery', 'Blood loss >200 ml after delivery', 'Any bleeding after delivery', 'A', 1, 'Postpartum hemorrhage is blood loss exceeding 500 ml after vaginal delivery.', 3),
(4, 5, 'true_false', 'Prenatal care should begin in the second trimester.', 'True', 'False', NULL, NULL, 'False', 1, 'Prenatal care should ideally begin as soon as pregnancy is confirmed, in the first trimester.', 3),
(4, 5, 'true_false', 'Breastfeeding should be initiated within the first hour after birth.', 'True', 'False', NULL, NULL, 'True', 1, 'Early initiation of breastfeeding promotes bonding and provides important antibodies to the newborn.', 3),
(5, 5, 'true_false', 'The umbilical cord contains two arteries and one vein.', 'True', 'False', NULL, NULL, 'True', 1, 'The umbilical cord normally contains two arteries and one vein.', 3),
(4, 5, 'true_false', 'Morning sickness only occurs in the morning.', 'True', 'False', NULL, NULL, 'False', 1, 'Despite its name, morning sickness can occur at any time of day.', 3);

-- ============================================
-- ADDITIONAL PUBLIC HEALTH QUESTIONS (Course 6 & 7)
-- ============================================
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(6, 6, 'multiple_choice', 'What does WHO stand for?', 'World Health Office', 'World Health Organization', 'Worldwide Health Operations', 'World Hospital Organization', 'B', 1, 'WHO is the World Health Organization, a UN agency for public health.', 4),
(6, 6, 'multiple_choice', 'Which disease was eradicated globally through vaccination?', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 1, 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 4),
(6, 6, 'multiple_choice', 'What is the leading cause of death worldwide?', 'Cancer', 'Cardiovascular disease', 'Respiratory infections', 'Accidents', 'B', 1, 'Cardiovascular diseases are the leading cause of death globally.', 4),
(7, 6, 'multiple_choice', 'What is the basic reproduction number (R0) in epidemiology?', 'Number of deaths', 'Number of new infections from one case', 'Number of recovered patients', 'Number of vaccinated individuals', 'B', 1, 'R0 represents the average number of people infected by one contagious person.', 4),
(6, 6, 'multiple_choice', 'Which vitamin deficiency causes scurvy?', 'Vitamin A', 'Vitamin B12', 'Vitamin C', 'Vitamin D', 'C', 1, 'Scurvy is caused by severe vitamin C deficiency.', 4),
(7, 6, 'multiple_choice', 'What is herd immunity?', 'Immunity in animals', 'Individual immunity', 'Population-level immunity', 'Temporary immunity', 'C', 1, 'Herd immunity occurs when enough people are immune to prevent disease spread.', 4),
(6, 6, 'true_false', 'Malaria is transmitted by mosquitoes.', 'True', 'False', NULL, NULL, 'True', 1, 'Malaria is transmitted through the bite of infected Anopheles mosquitoes.', 4),
(7, 6, 'true_false', 'Incidence refers to existing cases of disease in a population.', 'True', 'False', NULL, NULL, 'False', 1, 'Incidence refers to new cases; prevalence refers to existing cases.', 4),
(6, 6, 'true_false', 'Clean water access is a social determinant of health.', 'True', 'False', NULL, NULL, 'True', 1, 'Access to clean water significantly impacts population health outcomes.', 4),
(7, 6, 'true_false', 'An epidemic affects multiple countries or continents.', 'True', 'False', NULL, NULL, 'False', 1, 'An epidemic is widespread in one region; a pandemic affects multiple countries or continents.', 4);

-- ============================================
-- ADDITIONAL ANESTHESIA QUESTIONS (Course 8 & 9)
-- ============================================
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(8, 8, 'multiple_choice', 'What is the ASA classification system used for?', 'Anesthesia dosing', 'Patient physical status', 'Surgery duration', 'Recovery time', 'B', 1, 'ASA classification assesses patient physical status before anesthesia.', 5),
(8, 8, 'multiple_choice', 'Which drug is commonly used for induction of general anesthesia?', 'Aspirin', 'Propofol', 'Insulin', 'Warfarin', 'B', 1, 'Propofol is a commonly used induction agent for general anesthesia.', 5),
(9, 4, 'multiple_choice', 'What is the antidote for opioid overdose?', 'Epinephrine', 'Naloxone', 'Atropine', 'Dopamine', 'B', 1, 'Naloxone (Narcan) reverses opioid overdose effects.', 5),
(8, 8, 'multiple_choice', 'What does MAC stand for in anesthesia?', 'Maximum Anesthesia Concentration', 'Monitored Anesthesia Care', 'Minimal Airway Control', 'Medical Anesthesia Certification', 'B', 1, 'MAC is Monitored Anesthesia Care, a type of sedation.', 5),
(9, 4, 'multiple_choice', 'Which gas is commonly used for general anesthesia?', 'Oxygen', 'Sevoflurane', 'Carbon dioxide', 'Nitrogen', 'B', 1, 'Sevoflurane is a volatile anesthetic gas used for general anesthesia.', 5),
(8, 8, 'true_false', 'Spinal anesthesia is a type of regional anesthesia.', 'True', 'False', NULL, NULL, 'True', 1, 'Spinal anesthesia blocks nerve transmission in a specific region of the body.', 5),
(9, 9, 'true_false', 'Patients should fast before general anesthesia to prevent aspiration.', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting reduces the risk of aspiration of stomach contents during anesthesia.', 5),
(8, 8, 'true_false', 'Epidural anesthesia is commonly used for cesarean sections.', 'True', 'False', NULL, NULL, 'True', 1, 'Epidural anesthesia provides effective pain relief for cesarean deliveries.', 5),
(9, 4, 'true_false', 'Atropine is used to increase heart rate.', 'True', 'False', NULL, NULL, 'True', 1, 'Atropine is an anticholinergic drug that increases heart rate.', 5),
(8, 9, 'true_false', 'Capnography measures carbon dioxide levels in exhaled breath.', 'True', 'False', NULL, NULL, 'True', 1, 'Capnography monitors CO2 levels and is essential during anesthesia.', 5);

-- ============================================
-- ADDITIONAL LABORATORY QUESTIONS (Course 10 & 11)
-- ============================================
INSERT IGNORE INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(10, 7, 'multiple_choice', 'What is the normal range for fasting blood glucose?', '50-70 mg/dL', '70-100 mg/dL', '120-150 mg/dL', '180-200 mg/dL', 'B', 1, 'Normal fasting blood glucose is 70-100 mg/dL.', 6),
(10, 7, 'multiple_choice', 'Which test measures kidney function?', 'Hemoglobin A1C', 'Creatinine', 'Lipid panel', 'Liver enzymes', 'B', 1, 'Serum creatinine is a key indicator of kidney function.', 6),
(11, 7, 'multiple_choice', 'What is anemia?', 'High white blood cell count', 'Low red blood cell count', 'High platelet count', 'Low glucose level', 'B', 1, 'Anemia is a condition with low red blood cells or hemoglobin.', 6),
(10, 7, 'multiple_choice', 'What does HbA1c measure?', 'Current blood sugar', 'Average blood sugar over 3 months', 'Kidney function', 'Liver function', 'B', 1, 'HbA1c reflects average blood glucose levels over 2-3 months.', 6),
(11, 7, 'multiple_choice', 'What is the normal white blood cell count?', '1,000-3,000 cells/μL', '4,000-11,000 cells/μL', '15,000-20,000 cells/μL', '25,000-30,000 cells/μL', 'B', 1, 'Normal WBC count is 4,000-11,000 cells per microliter.', 6),
(10, 7, 'true_false', 'Cholesterol levels should be checked while fasting.', 'True', 'False', NULL, NULL, 'True', 1, 'Fasting lipid panels provide more accurate cholesterol measurements.', 6),
(11, 7, 'true_false', 'Platelets are responsible for blood clotting.', 'True', 'False', NULL, NULL, 'True', 1, 'Platelets (thrombocytes) play a crucial role in blood clotting.', 6),
(10, 7, 'true_false', 'Urine should be tested within 2 hours of collection.', 'True', 'False', NULL, NULL, 'True', 1, 'Urine samples should be tested promptly to ensure accurate results.', 6),
(11, 7, 'true_false', 'A complete blood count (CBC) includes hemoglobin, WBC, and platelet counts.', 'True', 'False', NULL, NULL, 'True', 1, 'CBC is a comprehensive blood test that measures multiple blood components.', 6),
(10, 7, 'true_false', 'Elevated liver enzymes always indicate liver disease.', 'True', 'False', NULL, NULL, 'False', 1, 'Elevated liver enzymes can have various causes and require further investigation.', 6);

-- ============================================
-- 14. INSERT SAMPLE EXAMS
-- ============================================
-- Rule: Each course can have 1 Midterm + 1 Final + Multiple Quizzes/Makeups per semester

-- COURSE 1: Fundamentals of Nursing (Semester 1)
INSERT IGNORE INTO `exams` (`course_id`, `exam_category_id`, `exam_name`, `exam_date`, `start_time`, `end_time`, `duration_minutes`, `total_marks`, `pass_marks`, `instructions`, `is_active`, `approval_status`, `submitted_at`, `approved_by`, `approved_at`, `created_by`) VALUES
(1, 1, 'Fundamentals of Nursing - Midterm', '2026-03-15', '09:00:00', '10:30:00', 90, 20, 10, 'Read all questions carefully. Choose the best answer.', 1, 'approved', '2026-02-01 10:00:00', 1, '2026-02-02 14:00:00', 1),
(1, 2, 'Fundamentals of Nursing - Final Exam', '2026-05-20', '09:00:00', '11:00:00', 120, 30, 15, 'Comprehensive final exam covering all course material.', 1, 'approved', '2026-04-01 10:00:00', 1, '2026-04-02 14:00:00', 1),
(1, 3, 'Fundamentals of Nursing - Quiz 1', '2026-02-25', '10:00:00', '10:30:00', 30, 10, 5, 'Quick quiz on chapters 1-3.', 1, 'approved', '2026-02-10 09:00:00', 1, '2026-02-11 10:00:00', 1),
(1, 3, 'Fundamentals of Nursing - Quiz 2', '2026-04-10', '10:00:00', '10:30:00', 30, 10, 5, 'Quiz on chapters 4-6.', 1, 'approved', '2026-03-25 09:00:00', 1, '2026-03-26 10:00:00', 1),

-- COURSE 2: Anatomy and Physiology (Semester 1)
(2, 1, 'Anatomy and Physiology - Midterm', '2026-03-16', '09:00:00', '10:30:00', 90, 20, 10, 'Answer all questions. Use of notes is not permitted.', 1, 'approved', '2026-02-01 11:00:00', 1, '2026-02-02 15:00:00', 1),
(2, 2, 'Anatomy and Physiology - Final Exam', '2026-05-21', '09:00:00', '11:00:00', 120, 30, 15, 'Final examination. No materials allowed.', 1, 'approved', '2026-04-01 11:00:00', 1, '2026-04-02 15:00:00', 1),
(2, 3, 'Anatomy and Physiology - Quiz 1', '2026-02-28', '11:00:00', '11:30:00', 30, 10, 5, 'Quiz on skeletal system.', 1, 'approved', '2026-02-12 09:00:00', 1, '2026-02-13 10:00:00', 1),

-- COURSE 3: Medical-Surgical Nursing I (Semester 2) - Pending
(3, 1, 'Medical-Surgical Nursing I - Midterm', '2026-03-20', '09:00:00', '10:30:00', 90, 20, 10, 'Midterm examination covering medical-surgical nursing concepts.', 1, 'pending', '2026-02-08 14:00:00', NULL, NULL, 1),

-- COURSE 4: Introduction to Midwifery (Semester 1)
(4, 1, 'Introduction to Midwifery - Midterm', '2026-03-17', '10:00:00', '11:30:00', 90, 15, 8, 'Answer all questions to the best of your ability.', 1, 'approved', '2026-02-03 09:00:00', 2, '2026-02-04 10:00:00', 3),
(4, 2, 'Introduction to Midwifery - Final Exam', '2026-05-22', '10:00:00', '12:00:00', 120, 25, 13, 'Comprehensive final exam.', 1, 'approved', '2026-04-02 09:00:00', 2, '2026-04-03 10:00:00', 3),

-- COURSE 5: Reproductive Health (Semester 1) - Draft
(5, 1, 'Reproductive Health - Midterm', NULL, NULL, NULL, 90, 0, 0, 'Draft exam - questions being added.', 0, 'draft', NULL, NULL, NULL, 3),

-- COURSE 6: Introduction to Public Health (Semester 1)
(6, 1, 'Introduction to Public Health - Midterm', '2026-03-18', '14:00:00', '15:30:00', 90, 15, 8, 'Read instructions carefully before starting.', 1, 'approved', '2026-02-03 10:00:00', 3, '2026-02-04 11:00:00', 4),
(6, 2, 'Introduction to Public Health - Final Exam', '2026-05-23', '14:00:00', '16:00:00', 120, 25, 13, 'Final exam covering all public health topics.', 1, 'approved', '2026-04-03 10:00:00', 3, '2026-04-04 11:00:00', 4),
(6, 3, 'Introduction to Public Health - Quiz 1', '2026-03-01', '14:00:00', '14:30:00', 30, 10, 5, 'Quiz on epidemiology basics.', 1, 'approved', '2026-02-14 09:00:00', 3, '2026-02-15 10:00:00', 4),

-- COURSE 8: Fundamentals of Anesthesia (Semester 1)
(8, 1, 'Fundamentals of Anesthesia - Midterm', '2026-03-19', '11:00:00', '12:30:00', 90, 15, 8, 'Midterm exam on anesthesia fundamentals.', 1, 'approved', '2026-02-04 10:00:00', 4, '2026-02-05 11:00:00', 5),
(8, 3, 'Fundamentals of Anesthesia - Quiz 1', '2026-02-26', '11:00:00', '11:30:00', 30, 10, 5, 'Quiz covering basic anesthesia principles.', 1, 'approved', '2026-02-10 10:00:00', 4, '2026-02-11 11:00:00', 5),

-- COURSE 9: Pharmacology for Anesthesia (Semester 1) - Rejected
(9, 1, 'Pharmacology for Anesthesia - Midterm', NULL, NULL, NULL, 90, 15, 8, 'Midterm exam on anesthetic pharmacology.', 0, 'rejected', '2026-02-05 10:00:00', 4, '2026-02-06 09:00:00', 5),

-- COURSE 10: Clinical Chemistry (Semester 1) - Pending
(10, 1, 'Clinical Chemistry - Midterm', '2026-03-21', '13:00:00', '14:30:00', 90, 20, 10, 'Laboratory chemistry midterm exam.', 1, 'pending', '2026-02-08 15:00:00', NULL, NULL, 6),

-- COURSE 11: Hematology (Semester 1) - Draft
(11, 1, 'Hematology - Midterm', NULL, NULL, NULL, 90, 0, 0, 'Draft exam - under construction.', 0, 'draft', NULL, NULL, NULL, 6),

-- Makeup Exams (Can have multiple)
(1, 4, 'Fundamentals of Nursing - Makeup Exam', '2026-06-10', '09:00:00', '11:00:00', 120, 30, 15, 'Makeup exam for students who missed the final.', 1, 'approved', '2026-05-25 10:00:00', 1, '2026-05-26 14:00:00', 1),
(2, 4, 'Anatomy and Physiology - Makeup Exam', '2026-06-11', '09:00:00', '11:00:00', 120, 30, 15, 'Makeup exam for absent students.', 1, 'approved', '2026-05-25 11:00:00', 1, '2026-05-26 15:00:00', 1);

-- ============================================
-- 15. INSERT EXAM QUESTIONS (LINK EXAMS TO QUESTIONS)
-- ============================================
-- Note: Exam IDs are auto-incremented, so these mappings assume sequential insertion

-- Exam 1: Fundamentals of Nursing - Midterm (20 questions = 20 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(1, 1, 1), (1, 2, 2), (1, 3, 3), (1, 4, 4), (1, 5, 5), (1, 6, 6),
(1, 31, 7), (1, 32, 8), (1, 33, 9), (1, 34, 10), (1, 35, 11), (1, 36, 12),
(1, 37, 13), (1, 38, 14), (1, 39, 15), (1, 40, 16), (1, 41, 17), (1, 42, 18),
(1, 43, 19), (1, 44, 20);

-- Exam 2: Fundamentals of Nursing - Final Exam (30 questions = 30 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(2, 1, 1), (2, 2, 2), (2, 3, 3), (2, 4, 4), (2, 5, 5), (2, 6, 6),
(2, 31, 7), (2, 32, 8), (2, 33, 9), (2, 34, 10), (2, 35, 11), (2, 36, 12),
(2, 37, 13), (2, 38, 14), (2, 39, 15), (2, 40, 16), (2, 41, 17), (2, 42, 18),
(2, 43, 19), (2, 44, 20), (2, 1, 21), (2, 2, 22), (2, 3, 23), (2, 4, 24),
(2, 5, 25), (2, 6, 26), (2, 31, 27), (2, 32, 28), (2, 33, 29), (2, 34, 30);

-- Exam 3: Fundamentals of Nursing - Quiz 1 (10 questions = 10 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(3, 1, 1), (3, 2, 2), (3, 3, 3), (3, 4, 4), (3, 5, 5),
(3, 6, 6), (3, 31, 7), (3, 32, 8), (3, 33, 9), (3, 34, 10);

-- Exam 4: Fundamentals of Nursing - Quiz 2 (10 questions = 10 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(4, 35, 1), (4, 36, 2), (4, 37, 3), (4, 38, 4), (4, 39, 5),
(4, 40, 6), (4, 41, 7), (4, 42, 8), (4, 43, 9), (4, 44, 10);

-- Exam 5: Anatomy and Physiology - Midterm (20 questions = 20 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(5, 7, 1), (5, 8, 2), (5, 9, 3), (5, 10, 4), (5, 11, 5), (5, 12, 6),
(5, 45, 7), (5, 46, 8), (5, 47, 9), (5, 48, 10), (5, 49, 11), (5, 50, 12),
(5, 51, 13), (5, 52, 14), (5, 53, 15), (5, 54, 16), (5, 55, 17), (5, 56, 18),
(5, 57, 19), (5, 58, 20);

-- Exam 6: Anatomy and Physiology - Final Exam (30 questions = 30 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(6, 7, 1), (6, 8, 2), (6, 9, 3), (6, 10, 4), (6, 11, 5), (6, 12, 6),
(6, 45, 7), (6, 46, 8), (6, 47, 9), (6, 48, 10), (6, 49, 11), (6, 50, 12),
(6, 51, 13), (6, 52, 14), (6, 53, 15), (6, 54, 16), (6, 55, 17), (6, 56, 18),
(6, 57, 19), (6, 58, 20), (6, 7, 21), (6, 8, 22), (6, 9, 23), (6, 10, 24),
(6, 11, 25), (6, 12, 26), (6, 45, 27), (6, 46, 28), (6, 47, 29), (6, 48, 30);

-- Exam 7: Anatomy and Physiology - Quiz 1 (10 questions = 10 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(7, 7, 1), (7, 8, 2), (7, 9, 3), (7, 10, 4), (7, 11, 5),
(7, 12, 6), (7, 45, 7), (7, 46, 8), (7, 47, 9), (7, 48, 10);

-- Exam 8: Medical-Surgical Nursing I - Midterm (20 questions = 20 marks) - PENDING
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(8, 59, 1), (8, 60, 2), (8, 61, 3), (8, 62, 4), (8, 63, 5),
(8, 64, 6), (8, 65, 7), (8, 66, 8), (8, 67, 9), (8, 68, 10),
(8, 69, 11), (8, 70, 12), (8, 59, 13), (8, 60, 14), (8, 61, 15),
(8, 62, 16), (8, 63, 17), (8, 64, 18), (8, 65, 19), (8, 66, 20);

-- Exam 9: Introduction to Midwifery - Midterm (15 questions = 15 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(9, 13, 1), (9, 14, 2), (9, 15, 3), (9, 16, 4), (9, 17, 5),
(9, 71, 6), (9, 72, 7), (9, 73, 8), (9, 74, 9), (9, 75, 10),
(9, 76, 11), (9, 77, 12), (9, 78, 13), (9, 79, 14), (9, 80, 15);

-- Exam 10: Introduction to Midwifery - Final Exam (25 questions = 25 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(10, 13, 1), (10, 14, 2), (10, 15, 3), (10, 16, 4), (10, 17, 5),
(10, 71, 6), (10, 72, 7), (10, 73, 8), (10, 74, 9), (10, 75, 10),
(10, 76, 11), (10, 77, 12), (10, 78, 13), (10, 79, 14), (10, 80, 15),
(10, 13, 16), (10, 14, 17), (10, 15, 18), (10, 16, 19), (10, 17, 20),
(10, 71, 21), (10, 72, 22), (10, 73, 23), (10, 74, 24), (10, 75, 25);

-- Exam 11: Reproductive Health - Midterm (DRAFT - No questions yet)
-- No questions for draft exams

-- Exam 12: Introduction to Public Health - Midterm (15 questions = 15 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(12, 18, 1), (12, 19, 2), (12, 20, 3), (12, 21, 4), (12, 22, 5),
(12, 81, 6), (12, 82, 7), (12, 83, 8), (12, 84, 9), (12, 85, 10),
(12, 86, 11), (12, 87, 12), (12, 88, 13), (12, 89, 14), (12, 90, 15);

-- Exam 13: Introduction to Public Health - Final Exam (25 questions = 25 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(13, 18, 1), (13, 19, 2), (13, 20, 3), (13, 21, 4), (13, 22, 5),
(13, 81, 6), (13, 82, 7), (13, 83, 8), (13, 84, 9), (13, 85, 10),
(13, 86, 11), (13, 87, 12), (13, 88, 13), (13, 89, 14), (13, 90, 15),
(13, 18, 16), (13, 19, 17), (13, 20, 18), (13, 21, 19), (13, 22, 20),
(13, 81, 21), (13, 82, 22), (13, 83, 23), (13, 84, 24), (13, 85, 25);

-- Exam 14: Introduction to Public Health - Quiz 1 (10 questions = 10 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(14, 18, 1), (14, 19, 2), (14, 20, 3), (14, 21, 4), (14, 22, 5),
(14, 81, 6), (14, 82, 7), (14, 83, 8), (14, 84, 9), (14, 85, 10);

-- Exam 15: Fundamentals of Anesthesia - Midterm (15 questions = 15 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(15, 23, 1), (15, 24, 2), (15, 25, 3), (15, 26, 4), (15, 27, 5),
(15, 91, 6), (15, 92, 7), (15, 93, 8), (15, 94, 9), (15, 95, 10),
(15, 96, 11), (15, 97, 12), (15, 98, 13), (15, 99, 14), (15, 100, 15);

-- Exam 16: Fundamentals of Anesthesia - Quiz 1 (10 questions = 10 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(16, 23, 1), (16, 24, 2), (16, 25, 3), (16, 26, 4), (16, 27, 5),
(16, 91, 6), (16, 92, 7), (16, 93, 8), (16, 94, 9), (16, 95, 10);

-- Exam 17: Pharmacology for Anesthesia - Midterm (REJECTED - Has questions but needs revision)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(17, 91, 1), (17, 92, 2), (17, 93, 3), (17, 94, 4), (17, 95, 5),
(17, 96, 6), (17, 97, 7), (17, 98, 8), (17, 99, 9), (17, 100, 10),
(17, 91, 11), (17, 92, 12), (17, 93, 13), (17, 94, 14), (17, 95, 15);

-- Exam 18: Clinical Chemistry - Midterm (20 questions = 20 marks) - PENDING
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(18, 28, 1), (18, 29, 2), (18, 30, 3), (18, 101, 4), (18, 102, 5),
(18, 103, 6), (18, 104, 7), (18, 105, 8), (18, 106, 9), (18, 107, 10),
(18, 108, 11), (18, 109, 12), (18, 110, 13), (18, 28, 14), (18, 29, 15),
(18, 30, 16), (18, 101, 17), (18, 102, 18), (18, 103, 19), (18, 104, 20);

-- Exam 19: Hematology - Midterm (DRAFT - No questions yet)
-- No questions for draft exams

-- Exam 20: Fundamentals of Nursing - Makeup Exam (30 questions = 30 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(20, 1, 1), (20, 2, 2), (20, 3, 3), (20, 4, 4), (20, 5, 5), (20, 6, 6),
(20, 31, 7), (20, 32, 8), (20, 33, 9), (20, 34, 10), (20, 35, 11), (20, 36, 12),
(20, 37, 13), (20, 38, 14), (20, 39, 15), (20, 40, 16), (20, 41, 17), (20, 42, 18),
(20, 43, 19), (20, 44, 20), (20, 1, 21), (20, 2, 22), (20, 3, 23), (20, 4, 24),
(20, 5, 25), (20, 6, 26), (20, 31, 27), (20, 32, 28), (20, 33, 29), (20, 34, 30);

-- Exam 21: Anatomy and Physiology - Makeup Exam (30 questions = 30 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(21, 7, 1), (21, 8, 2), (21, 9, 3), (21, 10, 4), (21, 11, 5), (21, 12, 6),
(21, 45, 7), (21, 46, 8), (21, 47, 9), (21, 48, 10), (21, 49, 11), (21, 50, 12),
(21, 51, 13), (21, 52, 14), (21, 53, 15), (21, 54, 16), (21, 55, 17), (21, 56, 18),
(21, 57, 19), (21, 58, 20), (21, 7, 21), (21, 8, 22), (21, 9, 23), (21, 10, 24),
(21, 11, 25), (21, 12, 26), (21, 45, 27), (21, 46, 28), (21, 47, 29), (21, 48, 30);

-- Exam 9: Medical-Surgical Nursing Midterm - Pending (20 questions = 20 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(9, 59, 1), (9, 60, 2), (9, 61, 3), (9, 62, 4), (9, 63, 5),
(9, 64, 6), (9, 65, 7), (9, 66, 8), (9, 67, 9), (9, 68, 10),
(9, 69, 11), (9, 70, 12), (9, 59, 13), (9, 60, 14), (9, 61, 15),
(9, 62, 16), (9, 63, 17), (9, 64, 18), (9, 65, 19), (9, 66, 20);

-- Exam 10: Clinical Chemistry Midterm - Pending (20 questions = 20 marks)
INSERT IGNORE INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(10, 28, 1), (10, 29, 2), (10, 30, 3), (10, 101, 4), (10, 102, 5),
(10, 103, 6), (10, 104, 7), (10, 105, 8), (10, 106, 9), (10, 107, 10),
(10, 108, 11), (10, 109, 12), (10, 110, 13), (10, 28, 14), (10, 29, 15),
(10, 30, 16), (10, 101, 17), (10, 102, 18), (10, 103, 19), (10, 104, 20);

-- ============================================
-- DATA IMPORT COMPLETE
-- ============================================

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Sample data imported successfully!' AS status;

