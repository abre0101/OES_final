-- ============================================
-- PRACTICE QUESTIONS DATA
-- Sample practice questions for students
-- ============================================

USE `oes_professional`;

-- ============================================
-- INSERT PRACTICE QUESTIONS
-- Mix of Multiple Choice and True/False
-- ============================================

-- NURSING PRACTICE QUESTIONS (Course 1: Fundamentals of Nursing)
INSERT INTO `practice_questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty_level`, `explanation`, `is_active`, `created_by`) VALUES
-- Multiple Choice
(1, 1, 'multiple_choice', 'What is the first step in the nursing process?', 'Planning', 'Assessment', 'Implementation', 'Evaluation', 'B', 'Easy', 'Assessment is always the first step where nurses gather patient information.', 1, 1),
(1, 1, 'multiple_choice', 'Which vital sign is measured in beats per minute?', 'Temperature', 'Blood Pressure', 'Pulse', 'Respiratory Rate', 'C', 'Easy', 'Pulse is measured in beats per minute (bpm).', 1, 1),
(1, 9, 'multiple_choice', 'How long should hands be scrubbed during surgical hand washing?', '10 seconds', '30 seconds', '2-6 minutes', '10 minutes', 'C', 'Medium', 'Surgical hand washing requires 2-6 minutes of thorough scrubbing.', 1, 1),
-- True/False
(1, 9, 'true_false', 'Gloves can replace hand washing in patient care.', 'True', 'False', NULL, NULL, 'False', 'Easy', 'Gloves are an additional barrier but do not replace proper hand hygiene.', 1, 1),
(1, 1, 'true_false', 'Nurses can diagnose medical conditions independently.', 'True', 'False', NULL, NULL, 'False', 'Medium', 'Nurses make nursing diagnoses, but medical diagnoses are made by physicians.', 1, 1),
(1, 9, 'true_false', 'Standard precautions should be used for all patients.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Standard precautions are infection control practices used for all patients.', 1, 1);

-- ANATOMY PRACTICE QUESTIONS (Course 2: Anatomy and Physiology)
INSERT INTO `practice_questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty_level`, `explanation`, `is_active`, `created_by`) VALUES
-- Multiple Choice
(2, 2, 'multiple_choice', 'Which bone protects the brain?', 'Femur', 'Skull', 'Ribs', 'Vertebrae', 'B', 'Easy', 'The skull (cranium) protects the brain from injury.', 1, 1),
(2, 3, 'multiple_choice', 'What is the largest artery in the human body?', 'Pulmonary artery', 'Carotid artery', 'Aorta', 'Femoral artery', 'C', 'Medium', 'The aorta is the largest artery, carrying oxygenated blood from the heart.', 1, 1),
(2, 2, 'multiple_choice', 'How many pairs of ribs does a human have?', '10', '12', '14', '16', 'B', 'Medium', 'Humans have 12 pairs of ribs (24 ribs total).', 1, 1),
-- True/False
(2, 2, 'true_false', 'The femur is the longest bone in the human body.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'The femur (thigh bone) is the longest and strongest bone in the body.', 1, 1),
(2, 3, 'true_false', 'The heart has three chambers.', 'True', 'False', NULL, NULL, 'False', 'Easy', 'The heart has four chambers: two atria and two ventricles.', 1, 1),
(2, 3, 'true_false', 'Arteries carry blood away from the heart.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Arteries carry oxygenated blood away from the heart to body tissues.', 1, 1);

-- MIDWIFERY PRACTICE QUESTIONS (Course 4: Introduction to Midwifery)
INSERT INTO `practice_questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty_level`, `explanation`, `is_active`, `created_by`) VALUES
-- Multiple Choice
(4, 5, 'multiple_choice', 'What is the average length of a menstrual cycle?', '21 days', '28 days', '35 days', '40 days', 'B', 'Easy', 'The average menstrual cycle is 28 days, though 21-35 days is considered normal.', 1, 3),
(4, 5, 'multiple_choice', 'At what week does the second trimester begin?', 'Week 10', 'Week 13', 'Week 16', 'Week 20', 'B', 'Medium', 'The second trimester begins at week 13 and ends at week 27.', 1, 3),
(4, 5, 'multiple_choice', 'What is the normal fetal heart rate range?', '60-100 bpm', '110-160 bpm', '180-200 bpm', '200-220 bpm', 'B', 'Medium', 'Normal fetal heart rate is 110-160 beats per minute.', 1, 3),
-- True/False
(4, 5, 'true_false', 'Morning sickness only occurs in the morning.', 'True', 'False', NULL, NULL, 'False', 'Easy', 'Despite its name, morning sickness can occur at any time of day.', 1, 3),
(4, 5, 'true_false', 'Pregnant women should avoid all exercise.', 'True', 'False', NULL, NULL, 'False', 'Medium', 'Moderate exercise is beneficial during pregnancy unless contraindicated.', 1, 3),
(4, 5, 'true_false', 'The placenta provides oxygen and nutrients to the fetus.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'The placenta transfers oxygen and nutrients from mother to fetus.', 1, 3);

-- PUBLIC HEALTH PRACTICE QUESTIONS (Course 6: Introduction to Public Health)
INSERT INTO `practice_questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty_level`, `explanation`, `is_active`, `created_by`) VALUES
-- Multiple Choice
(6, 6, 'multiple_choice', 'What does CDC stand for?', 'Center for Disease Control', 'Centers for Disease Control and Prevention', 'Central Disease Center', 'Clinical Disease Control', 'B', 'Easy', 'CDC stands for Centers for Disease Control and Prevention.', 1, 4),
(6, 6, 'multiple_choice', 'Which disease was eradicated globally through vaccination?', 'Polio', 'Smallpox', 'Measles', 'Tuberculosis', 'B', 'Medium', 'Smallpox was declared eradicated in 1980 through global vaccination efforts.', 1, 4),
(6, 6, 'multiple_choice', 'What is the primary mode of HIV transmission?', 'Mosquito bites', 'Sharing food', 'Blood and body fluids', 'Casual contact', 'C', 'Medium', 'HIV is transmitted through blood, sexual contact, and from mother to child.', 1, 4),
-- True/False
(6, 6, 'true_false', 'Epidemiology is the study of disease patterns in populations.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Epidemiology studies the distribution and determinants of health conditions in populations.', 1, 4),
(6, 6, 'true_false', 'Antibiotics are effective against all types of infections.', 'True', 'False', NULL, NULL, 'False', 'Medium', 'Antibiotics only work against bacterial infections, not viral or fungal infections.', 1, 4),
(6, 6, 'true_false', 'Clean water is essential for preventing waterborne diseases.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Access to clean water prevents diseases like cholera, typhoid, and dysentery.', 1, 4);

-- ANESTHESIA PRACTICE QUESTIONS (Course 8: Fundamentals of Anesthesia)
INSERT INTO `practice_questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty_level`, `explanation`, `is_active`, `created_by`) VALUES
-- Multiple Choice
(8, 8, 'multiple_choice', 'What does ASA stand for in anesthesia?', 'American Society of Anesthesiologists', 'Anesthesia Safety Association', 'Advanced Surgical Anesthesia', 'Anesthetic Standard Assessment', 'A', 'Medium', 'ASA is the American Society of Anesthesiologists classification system.', 1, 5),
(8, 8, 'multiple_choice', 'Which drug reverses opioid effects?', 'Atropine', 'Naloxone', 'Epinephrine', 'Dopamine', 'B', 'Hard', 'Naloxone (Narcan) is an opioid antagonist that reverses opioid effects.', 1, 5),
(8, 9, 'multiple_choice', 'What is the normal oxygen saturation level?', '70-80%', '85-90%', '95-100%', '100-110%', 'C', 'Easy', 'Normal oxygen saturation (SpO2) is 95-100%.', 1, 5),
-- True/False
(8, 8, 'true_false', 'Spinal anesthesia is a type of regional anesthesia.', 'True', 'False', NULL, NULL, 'True', 'Medium', 'Spinal anesthesia blocks sensation in a specific region of the body.', 1, 5),
(8, 9, 'true_false', 'Patients should fast before general anesthesia.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Fasting reduces the risk of aspiration during anesthesia.', 1, 5),
(8, 8, 'true_false', 'Local anesthesia causes loss of consciousness.', 'True', 'False', NULL, NULL, 'False', 'Easy', 'Local anesthesia only numbs a specific area without affecting consciousness.', 1, 5);

-- MEDICAL LABORATORY PRACTICE QUESTIONS (Course 10: Clinical Chemistry)
INSERT INTO `practice_questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `difficulty_level`, `explanation`, `is_active`, `created_by`) VALUES
-- Multiple Choice
(10, 7, 'multiple_choice', 'What is the normal range for blood glucose (fasting)?', '50-70 mg/dL', '70-100 mg/dL', '120-140 mg/dL', '150-180 mg/dL', 'B', 'Medium', 'Normal fasting blood glucose is 70-100 mg/dL.', 1, 6),
(10, 7, 'multiple_choice', 'Which blood cell fights infection?', 'Red blood cells', 'White blood cells', 'Platelets', 'Plasma cells', 'B', 'Easy', 'White blood cells (leukocytes) are part of the immune system.', 1, 6),
(10, 7, 'multiple_choice', 'What does CBC stand for?', 'Complete Blood Count', 'Central Blood Center', 'Clinical Blood Chemistry', 'Cellular Blood Composition', 'A', 'Easy', 'CBC is a Complete Blood Count test that measures blood components.', 1, 6),
-- True/False
(10, 7, 'true_false', 'Hemoglobin carries oxygen in the blood.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Hemoglobin in red blood cells binds and transports oxygen.', 1, 6),
(10, 7, 'true_false', 'Blood type AB is the universal recipient.', 'True', 'False', NULL, NULL, 'True', 'Medium', 'People with AB blood type can receive blood from any blood type.', 1, 6),
(10, 7, 'true_false', 'Platelets are responsible for blood clotting.', 'True', 'False', NULL, NULL, 'True', 'Easy', 'Platelets (thrombocytes) play a crucial role in blood clotting.', 1, 6);

-- ============================================
-- PRACTICE QUESTIONS IMPORT COMPLETE
-- Total: 48 practice questions (24 MC + 24 T/F)
-- ============================================
SELECT 'Practice questions imported successfully!' AS status;
SELECT COUNT(*) as total_practice_questions FROM practice_questions;
