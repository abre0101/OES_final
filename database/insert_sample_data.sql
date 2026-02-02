-- ============================================
-- SAMPLE DATA FOR HEALTH CAMPUS OES SYSTEM
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
(1, 'NURS103', 'Medical-Surgical Nursing I', 4, 2, 'Care of adult patients with medical-surgical conditions', 1),
(1, 'NURS201', 'Pediatric Nursing', 4, 3, 'Nursing care of children and adolescents', 1),
(1, 'NURS202', 'Maternal and Child Health Nursing', 4, 3, 'Care during pregnancy, childbirth, and postpartum', 1),
(1, 'NURS203', 'Mental Health Nursing', 3, 4, 'Psychiatric and mental health nursing care', 1),
(1, 'NURS301', 'Community Health Nursing', 4, 5, 'Public health and community-based nursing', 1),
(1, 'NURS302', 'Critical Care Nursing', 4, 5, 'Advanced care for critically ill patients', 1);

-- Midwifery Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(2, 'MIDW101', 'Introduction to Midwifery', 3, 1, 'Fundamentals of midwifery practice', 1),
(2, 'MIDW102', 'Reproductive Health', 4, 1, 'Women\'s reproductive health and family planning', 1),
(2, 'MIDW201', 'Antenatal Care', 4, 2, 'Prenatal care and monitoring', 1),
(2, 'MIDW202', 'Labor and Delivery', 5, 2, 'Management of labor and childbirth', 1),
(2, 'MIDW203', 'Postnatal Care', 3, 3, 'Postpartum care for mother and newborn', 1),
(2, 'MIDW301', 'High-Risk Pregnancy Management', 4, 4, 'Care for complicated pregnancies', 1);

-- Public Health Officer Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(3, 'PHO101', 'Introduction to Public Health', 3, 1, 'Overview of public health principles', 1),
(3, 'PHO102', 'Epidemiology', 4, 1, 'Study of disease patterns and prevention', 1),
(3, 'PHO201', 'Health Promotion and Disease Prevention', 4, 2, 'Strategies for health promotion', 1),
(3, 'PHO202', 'Environmental Health', 3, 2, 'Environmental factors affecting health', 1),
(3, 'PHO301', 'Health Policy and Management', 4, 3, 'Healthcare systems and policy', 1);

-- Anesthesia Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(4, 'ANES101', 'Fundamentals of Anesthesia', 4, 1, 'Basic principles of anesthesia', 1),
(4, 'ANES102', 'Pharmacology for Anesthesia', 4, 1, 'Anesthetic drugs and their effects', 1),
(4, 'ANES201', 'Clinical Anesthesia Practice', 5, 2, 'Practical anesthesia techniques', 1),
(4, 'ANES202', 'Pain Management', 3, 2, 'Acute and chronic pain management', 1),
(4, 'ANES301', 'Advanced Anesthesia Techniques', 4, 3, 'Specialized anesthesia procedures', 1);

-- Medical Laboratory Technology Courses
INSERT INTO `courses` (`department_id`, `course_code`, `course_name`, `credit_hours`, `semester`, `description`, `is_active`) VALUES
(5, 'MLT101', 'Clinical Chemistry', 4, 1, 'Chemical analysis of body fluids', 1),
(5, 'MLT102', 'Hematology', 4, 1, 'Study of blood and blood disorders', 1),
(5, 'MLT201', 'Medical Microbiology', 4, 2, 'Study of microorganisms and infections', 1),
(5, 'MLT202', 'Immunology and Serology', 3, 2, 'Immune system and diagnostic tests', 1),
(5, 'MLT301', 'Clinical Parasitology', 3, 3, 'Study of parasites and parasitic diseases', 1);

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
-- Nursing Students
INSERT INTO `students` (`student_code`, `username`, `password`, `full_name`, `email`, `phone`, `gender`, `department_id`, `academic_year`, `semester`, `is_active`) VALUES
('STU001', 'alem.h', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alem Hailu', 'alem.h@student.dmu.edu.et', '+251911111001', 'Male', 1, 'Year 1', 1, 1),
('STU002', 'bethel.k', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bethel Kebede', 'bethel.k@student.dmu.edu.et', '+251911111002', 'Female', 1, 'Year 1', 1, 1),
('STU003', 'chala.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Chala Mengistu', 'chala.m@student.dmu.edu.et', '+251911111003', 'Male', 1, 'Year 2', 3, 1),
('STU004', 'eden.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eden Tesfaye', 'eden.t@student.dmu.edu.et', '+251911111004', 'Female', 1, 'Year 2', 3, 1);

-- Midwifery Students
INSERT INTO `students` (`student_code`, `username`, `password`, `full_name`, `email`, `phone`, `gender`, `department_id`, `academic_year`, `semester`, `is_active`) VALUES
('STU005', 'frehiwot.a', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Frehiwot Alemu', 'frehiwot.a@student.dmu.edu.et', '+251911111005', 'Female', 2, 'Year 1', 1, 1),
('STU006', 'genet.w', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Genet Worku', 'genet.w@student.dmu.edu.et', '+251911111006', 'Female', 2, 'Year 1', 1, 1);

-- Public Health Students
INSERT INTO `students` (`student_code`, `username`, `password`, `full_name`, `email`, `phone`, `gender`, `department_id`, `academic_year`, `semester`, `is_active`) VALUES
('STU007', 'habtamu.g', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Habtamu Gebre', 'habtamu.g@student.dmu.edu.et', '+251911111007', 'Male', 3, 'Year 1', 1, 1),
('STU008', 'aster.b', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aster Bekele', 'aster.b@student.dmu.edu.et', '+251911111008', 'Female', 3, 'Year 1', 1, 1);

-- Anesthesia Students
INSERT INTO `students` (`student_code`, `username`, `password`, `full_name`, `email`, `phone`, `gender`, `department_id`, `academic_year`, `semester`, `is_active`) VALUES
('STU009', 'dawit.h', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dawit Haile', 'dawit.h@student.dmu.edu.et', '+251911111009', 'Male', 4, 'Year 1', 1, 1),
('STU010', 'eleni.m', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eleni Mulugeta', 'eleni.m@student.dmu.edu.et', '+251911111010', 'Female', 4, 'Year 1', 1, 1);

-- Medical Laboratory Students
INSERT INTO `students` (`student_code`, `username`, `password`, `full_name`, `email`, `phone`, `gender`, `department_id`, `academic_year`, `semester`, `is_active`) VALUES
('STU011', 'fikadu.t', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fikadu Tadesse', 'fikadu.t@student.dmu.edu.et', '+251911111011', 'Male', 5, 'Year 1', 1, 1),
('STU012', 'girmay.k', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Girmay Kebede', 'girmay.k@student.dmu.edu.et', '+251911111012', 'Male', 5, 'Year 1', 1, 1);
-- Password for all: password

-- ============================================
-- 8. INSERT INSTRUCTOR COURSES
-- ============================================
INSERT INTO `instructor_courses` (`instructor_id`, `course_id`) VALUES
(1, 1), (1, 3), (2, 2), (2, 4), (2, 5),
(3, 6), (3, 7), (3, 8), (3, 9),
(4, 10), (4, 11), (4, 12),
(5, 13), (5, 14), (5, 15),
(6, 16), (6, 17), (6, 18);

-- ============================================
-- 9. INSERT STUDENT COURSES (ENROLLMENTS)
-- ============================================
INSERT INTO `student_courses` (`student_id`, `course_id`, `status`) VALUES
(1, 1, 'enrolled'), (1, 2, 'enrolled'),
(2, 1, 'enrolled'), (2, 2, 'enrolled'),
(3, 3, 'enrolled'), (3, 4, 'enrolled'), (3, 5, 'enrolled'),
(4, 3, 'enrolled'), (4, 4, 'enrolled'), (4, 5, 'enrolled'),
(5, 6, 'enrolled'), (5, 7, 'enrolled'),
(6, 6, 'enrolled'), (6, 7, 'enrolled'),
(7, 10, 'enrolled'), (7, 11, 'enrolled'),
(8, 10, 'enrolled'), (8, 11, 'enrolled'),
(9, 13, 'enrolled'), (9, 14, 'enrolled'),
(10, 13, 'enrolled'), (10, 14, 'enrolled'),
(11, 16, 'enrolled'), (11, 17, 'enrolled'),
(12, 16, 'enrolled'), (12, 17, 'enrolled');

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
('Child Health', 'Pediatric care'),
('Public Health', 'Community health concepts'),
('Laboratory Techniques', 'Lab procedures and tests'),
('Anesthesia Basics', 'Anesthesia principles'),
('Patient Safety', 'Safety protocols and procedures');

-- ============================================
-- 13. INSERT SAMPLE QUESTIONS (30 questions)
-- ============================================
-- Nursing Questions (Course 1 & 2)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(1, 1, 'What is the primary goal of nursing care?', 'To cure all diseases', 'To promote health and prevent illness', 'To perform medical procedures', 'To manage hospital operations', 'B', 1, 'The primary goal of nursing is to promote health, prevent illness, and help patients cope with illness.', 1),
(1, 1, 'Which of the following is a basic human need according to Maslow\'s hierarchy?', 'Internet access', 'Physiological needs', 'Entertainment', 'Social media', 'B', 1, 'Maslow\'s hierarchy starts with physiological needs like food, water, and shelter.', 1),
(1, 10, 'What is the correct order for hand hygiene?', 'Dry, rinse, soap, wet', 'Wet, soap, rinse, dry', 'Soap, wet, dry, rinse', 'Rinse, dry, wet, soap', 'B', 1, 'Proper hand hygiene: wet hands, apply soap, scrub, rinse, and dry.', 1),
(1, 10, 'How long should you wash your hands with soap?', '5 seconds', '10 seconds', '20 seconds', '60 seconds', 'C', 1, 'Hands should be washed for at least 20 seconds to effectively remove germs.', 1),
(1, 1, 'What does the acronym ADPIE stand for in the nursing process?', 'Assess, Diagnose, Plan, Implement, Evaluate', 'Admit, Discharge, Plan, Intervene, Exit', 'Analyze, Decide, Perform, Inspect, End', 'Assess, Document, Prescribe, Inject, Examine', 'A', 1, 'ADPIE represents the five steps of the nursing process.', 1),
(2, 2, 'Which organ is responsible for pumping blood throughout the body?', 'Liver', 'Lungs', 'Heart', 'Kidneys', 'C', 1, 'The heart is the muscular organ that pumps blood through the circulatory system.', 1),
(2, 2, 'How many chambers does the human heart have?', 'Two', 'Three', 'Four', 'Five', 'C', 1, 'The heart has four chambers: two atria and two ventricles.', 1),
(2, 3, 'What is the normal resting heart rate for adults?', '40-50 bpm', '60-100 bpm', '120-140 bpm', '150-180 bpm', 'B', 1, 'Normal resting heart rate for adults is 60-100 beats per minute.', 1),
(2, 3, 'Which system is responsible for gas exchange in the body?', 'Digestive system', 'Respiratory system', 'Nervous system', 'Endocrine system', 'B', 1, 'The respiratory system facilitates oxygen intake and carbon dioxide removal.', 1),
(2, 2, 'What is the largest organ in the human body?', 'Liver', 'Brain', 'Skin', 'Heart', 'C', 1, 'The skin is the largest organ, covering the entire body surface.', 1);

-- Midwifery Questions (Course 6)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(6, 5, 'What is the normal duration of pregnancy?', '30 weeks', '40 weeks', '50 weeks', '60 weeks', 'B', 1, 'Normal pregnancy duration is approximately 40 weeks or 280 days from the last menstrual period.', 3),
(6, 5, 'Which trimester is considered the most critical for fetal development?', 'First trimester', 'Second trimester', 'Third trimester', 'All are equally critical', 'A', 1, 'The first trimester is crucial as major organs and structures develop during this period.', 3),
(6, 5, 'What is the recommended folic acid intake for pregnant women?', '100 mcg', '200 mcg', '400 mcg', '800 mcg', 'C', 1, 'Pregnant women should take 400-800 mcg of folic acid daily to prevent neural tube defects.', 3),
(6, 5, 'At what week can fetal heartbeat typically be detected?', '2-3 weeks', '6-7 weeks', '12-13 weeks', '20-21 weeks', 'B', 1, 'Fetal heartbeat can usually be detected by ultrasound around 6-7 weeks of gestation.', 3),
(6, 5, 'What is the normal fetal heart rate?', '60-80 bpm', '80-100 bpm', '110-160 bpm', '180-200 bpm', 'C', 1, 'Normal fetal heart rate ranges from 110 to 160 beats per minute.', 3);

-- Public Health Questions (Course 10)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(10, 7, 'What is the primary focus of public health?', 'Individual patient care', 'Population health', 'Hospital management', 'Pharmaceutical sales', 'B', 1, 'Public health focuses on protecting and improving the health of entire populations.', 4),
(10, 7, 'Which of the following is a communicable disease?', 'Diabetes', 'Tuberculosis', 'Hypertension', 'Cancer', 'B', 1, 'Tuberculosis is a communicable disease that spreads from person to person.', 4),
(10, 7, 'What does WHO stand for?', 'World Health Office', 'World Health Organization', 'Worldwide Health Operations', 'World Hospital Organization', 'B', 1, 'WHO is the World Health Organization, a specialized agency of the United Nations.', 4),
(10, 7, 'What is the most effective way to prevent disease transmission?', 'Taking antibiotics', 'Hand washing', 'Avoiding sunlight', 'Drinking coffee', 'B', 1, 'Hand washing is one of the most effective ways to prevent the spread of infections.', 4),
(10, 7, 'What is herd immunity?', 'Immunity in animals only', 'Protection of a population when most are immune', 'Individual immunity', 'Temporary immunity', 'B', 1, 'Herd immunity occurs when a large portion of a population becomes immune, protecting vulnerable individuals.', 4);

-- Anesthesia Questions (Course 13)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(13, 9, 'What is the primary purpose of anesthesia?', 'To cure diseases', 'To prevent pain during procedures', 'To increase blood pressure', 'To improve digestion', 'B', 1, 'Anesthesia is used to prevent pain and discomfort during medical procedures.', 5),
(13, 9, 'Which type of anesthesia affects the entire body?', 'Local anesthesia', 'Regional anesthesia', 'General anesthesia', 'Topical anesthesia', 'C', 1, 'General anesthesia affects the entire body and causes unconsciousness.', 5),
(13, 9, 'What vital sign must be continuously monitored during anesthesia?', 'Hair growth', 'Oxygen saturation', 'Appetite', 'Vision', 'B', 1, 'Oxygen saturation is critical to monitor to ensure adequate oxygenation during anesthesia.', 5),
(13, 4, 'Which drug is commonly used for induction of general anesthesia?', 'Aspirin', 'Propofol', 'Vitamin C', 'Insulin', 'B', 1, 'Propofol is a commonly used intravenous anesthetic agent for induction.', 5),
(13, 9, 'What is the recovery room called after anesthesia?', 'ICU', 'PACU', 'ER', 'OR', 'B', 1, 'PACU (Post-Anesthesia Care Unit) is where patients recover after anesthesia.', 5);

-- Medical Laboratory Questions (Course 16)
INSERT INTO `questions` (`course_id`, `topic_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
(16, 8, 'What is the normal pH range of human blood?', '6.35-6.45', '7.35-7.45', '8.35-8.45', '9.35-9.45', 'B', 1, 'Normal blood pH is slightly alkaline, ranging from 7.35 to 7.45.', 6),
(16, 8, 'Which blood type is considered the universal donor?', 'A', 'B', 'AB', 'O', 'D', 1, 'Type O negative blood is the universal donor as it can be given to any blood type.', 6),
(16, 8, 'What is the primary function of red blood cells?', 'Fight infection', 'Transport oxygen', 'Clot blood', 'Produce hormones', 'B', 1, 'Red blood cells contain hemoglobin which transports oxygen throughout the body.', 6),
(16, 8, 'What is the normal range for white blood cell count?', '1,000-3,000 cells/μL', '4,000-11,000 cells/μL', '20,000-30,000 cells/μL', '50,000-60,000 cells/μL', 'B', 1, 'Normal WBC count ranges from 4,000 to 11,000 cells per microliter.', 6),
(16, 8, 'Which test measures blood sugar levels?', 'CBC', 'Glucose test', 'Lipid panel', 'Liver function test', 'B', 1, 'Glucose test measures the amount of sugar (glucose) in the blood.', 6);

-- ============================================
-- 14. INSERT SAMPLE EXAMS
-- ============================================
INSERT INTO `exams` (`course_id`, `exam_category_id`, `exam_name`, `exam_date`, `start_time`, `end_time`, `duration_minutes`, `total_marks`, `pass_marks`, `instructions`, `is_active`, `approval_status`, `submitted_at`, `approved_by`, `approved_at`, `created_by`) VALUES
(1, 1, 'Fundamentals of Nursing - Midterm', '2026-03-15', '09:00:00', '10:30:00', 90, 100, 50, 'Read all questions carefully. Choose the best answer. No cheating allowed.', 1, 'approved', '2026-02-01 10:00:00', 1, '2026-02-02 14:00:00', 1),
(2, 1, 'Anatomy and Physiology - Midterm', '2026-03-16', '09:00:00', '10:30:00', 90, 100, 50, 'Answer all questions. Use of notes is not permitted.', 1, 'approved', '2026-02-01 11:00:00', 1, '2026-02-02 15:00:00', 1),
(6, 1, 'Introduction to Midwifery - Midterm', '2026-03-17', '14:00:00', '15:30:00', 90, 100, 50, 'Multiple choice questions. Select the most appropriate answer.', 1, 'approved', '2026-02-01 12:00:00', 2, '2026-02-02 16:00:00', 3),
(10, 1, 'Introduction to Public Health - Midterm', '2026-03-18', '09:00:00', '10:30:00', 90, 100, 50, 'Read instructions carefully before starting the exam.', 1, 'approved', '2026-02-01 13:00:00', 3, '2026-02-02 17:00:00', 4),
(13, 1, 'Fundamentals of Anesthesia - Midterm', '2026-03-19', '14:00:00', '15:30:00', 90, 100, 50, 'Answer all questions to the best of your ability.', 1, 'approved', '2026-02-01 14:00:00', 4, '2026-02-02 18:00:00', 5),
(16, 1, 'Clinical Chemistry - Midterm', '2026-03-20', '09:00:00', '10:30:00', 90, 100, 50, 'All questions carry equal marks. Good luck!', 1, 'approved', '2026-02-01 15:00:00', 5, '2026-02-02 19:00:00', 6);

-- ============================================
-- 15. INSERT EXAM QUESTIONS (LINK EXAMS TO QUESTIONS)
-- ============================================
-- Exam 1: Fundamentals of Nursing (Questions 1-5)
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(1, 1, 1), (1, 2, 2), (1, 3, 3), (1, 4, 4), (1, 5, 5);

-- Exam 2: Anatomy and Physiology (Questions 6-10)
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(2, 6, 1), (2, 7, 2), (2, 8, 3), (2, 9, 4), (2, 10, 5);

-- Exam 3: Introduction to Midwifery (Questions 11-15)
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(3, 11, 1), (3, 12, 2), (3, 13, 3), (3, 14, 4), (3, 15, 5);

-- Exam 4: Introduction to Public Health (Questions 16-20)
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(4, 16, 1), (4, 17, 2), (4, 18, 3), (4, 19, 4), (4, 20, 5);

-- Exam 5: Fundamentals of Anesthesia (Questions 21-25)
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(5, 21, 1), (5, 22, 2), (5, 23, 3), (5, 24, 4), (5, 25, 5);

-- Exam 6: Clinical Chemistry (Questions 26-30)
INSERT INTO `exam_questions` (`exam_id`, `question_id`, `question_order`) VALUES
(6, 26, 1), (6, 27, 2), (6, 28, 3), (6, 29, 4), (6, 30, 5);

-- ============================================
-- 16. INSERT SAMPLE EXAM RESULTS
-- ============================================
INSERT INTO `exam_results` (`student_id`, `exam_id`, `total_questions`, `correct_answers`, `wrong_answers`, `unanswered`, `total_points_earned`, `total_points_possible`, `percentage_score`, `letter_grade`, `gpa`, `pass_status`, `exam_started_at`, `exam_submitted_at`, `time_taken_minutes`) VALUES
(1, 1, 5, 4, 1, 0, 4.00, 5.00, 80.00, 'A-', 3.50, 'Pass', '2026-03-15 09:00:00', '2026-03-15 09:45:00', 45),
(2, 1, 5, 5, 0, 0, 5.00, 5.00, 100.00, 'A+', 4.00, 'Pass', '2026-03-15 09:00:00', '2026-03-15 09:50:00', 50);

-- ============================================
-- 17. INSERT SAMPLE STUDENT ANSWERS
-- ============================================
INSERT INTO `student_answers` (`result_id`, `question_id`, `selected_answer`, `is_correct`, `points_earned`, `answered_at`) VALUES
(1, 1, 'B', 1, 1.00, '2026-03-15 09:10:00'),
(1, 2, 'B', 1, 1.00, '2026-03-15 09:20:00'),
(1, 3, 'B', 1, 1.00, '2026-03-15 09:30:00'),
(1, 4, 'B', 0, 0.00, '2026-03-15 09:35:00'),
(1, 5, 'A', 1, 1.00, '2026-03-15 09:40:00'),
(2, 1, 'B', 1, 1.00, '2026-03-15 09:12:00'),
(2, 2, 'B', 1, 1.00, '2026-03-15 09:22:00'),
(2, 3, 'B', 1, 1.00, '2026-03-15 09:32:00'),
(2, 4, 'C', 1, 1.00, '2026-03-15 09:38:00'),
(2, 5, 'A', 1, 1.00, '2026-03-15 09:45:00');

-- ============================================
-- 18. INSERT SAMPLE TECHNICAL ISSUES
-- ============================================
INSERT INTO `technical_issues` (`student_id`, `exam_id`, `issue_description`, `status`, `reported_at`) VALUES
(1, 1, 'The exam page froze for 2 minutes during question 3. I had to refresh the browser.', 'resolved', '2026-03-15 09:28:00'),
(5, 3, 'I could not see the images in question 2. The image placeholder was showing but no actual image loaded.', 'pending', '2026-03-17 14:15:00'),
(7, 4, 'My internet connection dropped briefly and I was worried my answers were not saved.', 'resolved', '2026-03-18 09:45:00');

-- ============================================
-- 19. INSERT EXAM APPROVAL HISTORY
-- ============================================
INSERT INTO `exam_approval_history` (`exam_id`, `action`, `performed_by`, `performed_by_type`, `comments`, `previous_status`, `new_status`) VALUES
(1, 'submitted', 1, 'instructor', 'Submitting midterm exam for approval', 'draft', 'pending'),
(1, 'approved', 1, 'department_head', 'Exam approved. Questions are appropriate and well-structured.', 'pending', 'approved'),
(2, 'submitted', 1, 'instructor', 'Submitting anatomy midterm for approval', 'draft', 'pending'),
(2, 'approved', 1, 'department_head', 'Approved. Good coverage of topics.', 'pending', 'approved'),
(3, 'submitted', 3, 'instructor', 'Midwifery midterm ready for review', 'draft', 'pending'),
(3, 'approved', 2, 'department_head', 'Approved with no changes needed.', 'pending', 'approved');

-- ============================================
-- 20. INSERT SAMPLE PRACTICE QUESTIONS
-- ============================================
INSERT INTO `practice_questions` (`course_id`, `topic_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty_level`, `explanation`, `is_active`, `created_by`) VALUES
(1, 1, 'What is the first step in the nursing process?', 'Planning', 'Assessment', 'Implementation', 'Evaluation', 'B', 'Easy', 'Assessment is always the first step in the nursing process.', 1, 1),
(1, 10, 'Which PPE should be worn first?', 'Gloves', 'Mask', 'Gown', 'Face shield', 'C', 'Medium', 'The correct order is: gown, mask, goggles/face shield, then gloves.', 1, 1),
(2, 2, 'How many bones are in the adult human body?', '186', '206', '226', '246', 'B', 'Easy', 'The adult human skeleton has 206 bones.', 1, 1),
(6, 5, 'What is the medical term for morning sickness?', 'Hyperemesis', 'Nausea gravidarum', 'Emesis', 'Gastritis', 'B', 'Medium', 'Nausea gravidarum is the medical term for morning sickness during pregnancy.', 1, 3),
(10, 7, 'What is the leading cause of death worldwide?', 'Cancer', 'Cardiovascular disease', 'Respiratory disease', 'Accidents', 'B', 'Medium', 'Cardiovascular diseases are the leading cause of death globally.', 1, 4);

-- ============================================
-- END OF SAMPLE DATA
-- ============================================

SELECT 'Sample data inserted successfully!' as message;
