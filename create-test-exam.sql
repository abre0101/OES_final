-- Create a health sciences exam that's available now

-- First, let's create an exam
INSERT INTO exams (course_id, exam_category_id, exam_name, exam_date, start_time, end_time, duration_minutes, total_marks, pass_marks, instructions, is_active, approval_status, created_by, created_at)
VALUES (
    1, -- course_id
    1, -- exam_category_id (assuming 1 exists)
    'Introduction to Health Sciences - Midterm Exam',
    CURDATE(), -- Today's date
    DATE_SUB(NOW(), INTERVAL 5 MINUTE), -- Started 5 minutes ago
    DATE_ADD(NOW(), INTERVAL 3 HOUR), -- Ends in 3 hours
    90, -- 90 minutes duration
    100, -- total marks
    50, -- passing marks
    'This exam covers fundamental concepts in health sciences. Answer all questions carefully. You have 90 minutes to complete. Good luck!',
    1, -- is_active
    'approved', -- approval_status
    1, -- created_by (assuming admin id 1)
    NOW()
);

-- Get the exam_id we just created
SET @exam_id = LAST_INSERT_ID();

-- Add health-related questions to the questions table first
INSERT INTO questions (course_id, question_text, option_a, option_b, option_c, option_d, correct_answer, point_value, created_by, created_at)
VALUES
(1, 'What is the normal human body temperature in Celsius?', '35°C', '36°C', '37°C', '38°C', 'C', 5, 1, NOW()),
(1, 'Which organ is responsible for pumping blood throughout the body?', 'Liver', 'Heart', 'Kidney', 'Lungs', 'B', 5, 1, NOW()),
(1, 'What is the largest organ in the human body?', 'Liver', 'Brain', 'Skin', 'Heart', 'C', 5, 1, NOW()),
(1, 'How many bones are in the adult human body?', '186', '206', '226', '246', 'B', 5, 1, NOW()),
(1, 'What is the medical term for high blood pressure?', 'Hypotension', 'Hypertension', 'Hyperglycemia', 'Hypoglycemia', 'B', 5, 1, NOW()),
(1, 'Which vitamin is produced when skin is exposed to sunlight?', 'Vitamin A', 'Vitamin C', 'Vitamin D', 'Vitamin E', 'C', 5, 1, NOW()),
(1, 'What is the main function of red blood cells?', 'Fight infection', 'Carry oxygen', 'Clot blood', 'Produce hormones', 'B', 5, 1, NOW()),
(1, 'Which organ filters waste from the blood?', 'Liver', 'Pancreas', 'Kidney', 'Spleen', 'C', 5, 1, NOW()),
(1, 'What is the normal resting heart rate for adults (beats per minute)?', '40-60', '60-100', '100-120', '120-140', 'B', 5, 1, NOW()),
(1, 'Which type of diabetes is characterized by insulin resistance?', 'Type 1', 'Type 2', 'Type 3', 'Gestational', 'B', 5, 1, NOW()),
(1, 'What is the medical term for a heart attack?', 'Stroke', 'Myocardial Infarction', 'Cardiac Arrest', 'Angina', 'B', 5, 1, NOW()),
(1, 'Which blood type is known as the universal donor?', 'A+', 'B+', 'AB+', 'O-', 'D', 5, 1, NOW()),
(1, 'What is the primary function of white blood cells?', 'Carry oxygen', 'Fight infection', 'Clot blood', 'Transport nutrients', 'B', 5, 1, NOW()),
(1, 'How many chambers does the human heart have?', '2', '3', '4', '5', 'C', 5, 1, NOW()),
(1, 'What is the medical term for difficulty breathing?', 'Dysphagia', 'Dyspnea', 'Dysuria', 'Dysplasia', 'B', 5, 1, NOW()),
(1, 'Which hormone regulates blood sugar levels?', 'Adrenaline', 'Insulin', 'Cortisol', 'Thyroxine', 'B', 5, 1, NOW()),
(1, 'What is the normal blood pressure reading (systolic/diastolic)?', '100/60 mmHg', '120/80 mmHg', '140/90 mmHg', '160/100 mmHg', 'B', 5, 1, NOW()),
(1, 'Which part of the brain controls balance and coordination?', 'Cerebrum', 'Cerebellum', 'Medulla', 'Hypothalamus', 'B', 5, 1, NOW()),
(1, 'What is the medical term for inflammation of the liver?', 'Nephritis', 'Hepatitis', 'Gastritis', 'Bronchitis', 'B', 5, 1, NOW()),
(1, 'How many liters of blood does an average adult have?', '3-4 liters', '5-6 liters', '7-8 liters', '9-10 liters', 'B', 5, 1, NOW());

-- Link the last 20 questions to the exam
INSERT INTO exam_questions (exam_id, question_id, question_order)
SELECT @exam_id, question_id, (@row_number:=@row_number + 1) AS question_order
FROM (SELECT @row_number:=0) AS init, 
     (SELECT question_id FROM questions ORDER BY question_id DESC LIMIT 20) AS q
ORDER BY question_id;

SELECT 'Health Sciences Exam created successfully!' as message;
SELECT @exam_id as exam_id, exam_name, exam_date, start_time, end_time FROM exams WHERE exam_id = @exam_id;
