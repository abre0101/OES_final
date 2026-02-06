-- Migration: Add True/False Question Support
-- This migration adds support for True/False questions in addition to multiple choice

USE `oes_professional`;

-- Step 1: Check and add question_type column to questions table if it doesn't exist
-- (Skip if column already exists)

-- Step 2: Modify correct_answer to support True/False
ALTER TABLE `questions` 
MODIFY COLUMN `correct_answer` ENUM('A','B','C','D','True','False') NOT NULL;

-- Step 3: Make option_c and option_d nullable (not needed for True/False)
-- Already nullable in the schema

-- Step 4: Update option_a and option_b to allow True/False values
-- No change needed, they can store "True" and "False" text

-- Step 5: Add some sample True/False questions
INSERT INTO `questions` (`course_id`, `topic_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `point_value`, `explanation`, `created_by`) VALUES
-- Nursing True/False Questions
(1, 1, 'true_false', 'Hand hygiene is the single most important practice to prevent healthcare-associated infections.', 'True', 'False', NULL, NULL, 'True', 1, 'Hand hygiene is universally recognized as the most effective way to prevent the spread of infections in healthcare settings.', 1),
(1, 10, 'true_false', 'Sterile gloves must be worn when taking a patient\'s blood pressure.', 'True', 'False', NULL, NULL, 'False', 1, 'Taking blood pressure is a non-invasive procedure that requires clean technique, not sterile gloves.', 1),
(1, 1, 'true_false', 'The nursing process consists of five steps: Assessment, Diagnosis, Planning, Implementation, and Evaluation.', 'True', 'False', NULL, NULL, 'True', 1, 'These five steps (ADPIE) form the foundation of the nursing process.', 1),

-- Anatomy True/False Questions
(2, 2, 'true_false', 'The human body has 206 bones in the adult skeleton.', 'True', 'False', NULL, NULL, 'True', 1, 'An adult human skeleton typically contains 206 bones, though babies are born with about 270 bones that fuse as they grow.', 1),
(2, 3, 'true_false', 'The liver is located in the left upper quadrant of the abdomen.', 'True', 'False', NULL, NULL, 'False', 1, 'The liver is primarily located in the right upper quadrant of the abdomen, beneath the diaphragm.', 1),

-- Midwifery True/False Questions
(6, 5, 'true_false', 'A normal pregnancy lasts approximately 40 weeks from the first day of the last menstrual period.', 'True', 'False', NULL, NULL, 'True', 1, 'Pregnancy duration is calculated as 40 weeks or 280 days from the last menstrual period (LMP).', 3),
(6, 5, 'true_false', 'Fetal movements should be felt by the mother starting from the first trimester.', 'True', 'False', NULL, NULL, 'False', 1, 'Fetal movements (quickening) are typically felt between 16-25 weeks of pregnancy, which is in the second trimester.', 3),

-- Public Health True/False Questions
(10, 7, 'true_false', 'Vaccination is one of the most cost-effective public health interventions.', 'True', 'False', NULL, NULL, 'True', 1, 'Vaccines prevent millions of deaths annually and are considered one of the most successful and cost-effective public health measures.', 4),
(10, 7, 'true_false', 'Antibiotics are effective against viral infections like the common cold.', 'True', 'False', NULL, NULL, 'False', 1, 'Antibiotics only work against bacterial infections, not viral infections. Misuse of antibiotics contributes to antibiotic resistance.', 4),

-- Anesthesia True/False Questions
(13, 9, 'true_false', 'General anesthesia causes complete loss of consciousness.', 'True', 'False', NULL, NULL, 'True', 1, 'General anesthesia induces a reversible state of unconsciousness, allowing surgical procedures to be performed without pain or awareness.', 5),
(13, 9, 'true_false', 'Local anesthesia affects the entire body.', 'True', 'False', NULL, NULL, 'False', 1, 'Local anesthesia only numbs a specific area of the body where it is applied, without affecting consciousness.', 5),

-- Medical Laboratory True/False Questions
(16, 8, 'true_false', 'Blood type O negative is considered the universal donor.', 'True', 'False', NULL, NULL, 'True', 1, 'O negative blood can be given to patients of any blood type in emergency situations, making it the universal donor.', 6),
(16, 8, 'true_false', 'Hemoglobin is found in white blood cells.', 'True', 'False', NULL, NULL, 'False', 1, 'Hemoglobin is the oxygen-carrying protein found in red blood cells, not white blood cells.', 6);

-- Step 6: Update practice_questions table to support True/False as well
-- (question_type column already exists in practice_questions table)

-- Update correct_answer enum for practice_questions to support True/False
ALTER TABLE `practice_questions` 
MODIFY COLUMN `correct_answer` ENUM('A','B','C','D','True','False') NOT NULL;

-- Migration complete
SELECT 'True/False question support added successfully!' AS status;
