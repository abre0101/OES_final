<?php
/**
 * Migration Script: Create Practice Questions System
 * 
 * This script:
 * 1. Creates the practice_questions, practice_results, and practice_answers tables
 * 2. Optionally copies existing questions to practice_questions table
 * 3. Updates the system to use separate tables for practice and exams
 */

require_once(__DIR__ . '/../Connections/OES.php');

echo "=== Practice Questions System Migration ===\n\n";

// Read and execute the SQL file
$sqlFile = __DIR__ . '/create_practice_questions_table.sql';
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

// Split by semicolon and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "Creating tables...\n";
foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    if ($con->query($statement)) {
        // Extract table name from CREATE TABLE statement
        if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches)) {
            echo "✓ Created/verified table: {$matches[1]}\n";
        }
    } else {
        echo "✗ Error: " . $con->error . "\n";
    }
}

echo "\n=== Migration Options ===\n";
echo "Do you want to copy existing questions to practice_questions? (yes/no): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));

if (strtolower($response) === 'yes') {
    echo "\nCopying questions to practice_questions table...\n";
    
    // Copy all questions to practice_questions (they're all multiple choice based on the structure)
    $copySql = "INSERT INTO practice_questions 
                (course_id, question_text, question_type, option_a, option_b, option_c, option_d, 
                 correct_answer, difficulty_level, topic, points, created_by, created_at)
                SELECT 
                    course_id, 
                    question_text,
                    'multiple_choice' as question_type,
                    option_a, 
                    option_b, 
                    option_c, 
                    option_d,
                    CASE correct_answer
                        WHEN 'A' THEN option_a
                        WHEN 'B' THEN option_b
                        WHEN 'C' THEN option_c
                        WHEN 'D' THEN option_d
                        ELSE option_a
                    END as correct_answer,
                    CASE difficulty_level
                        WHEN 'Easy' THEN 'easy'
                        WHEN 'Medium' THEN 'medium'
                        WHEN 'Hard' THEN 'hard'
                        ELSE 'medium'
                    END as difficulty_level,
                    COALESCE((SELECT topic_name FROM question_topics WHERE topic_id = questions.topic_id), 'General') as topic,
                    COALESCE(point_value, 1) as points,
                    instructor_id as created_by,
                    created_at
                FROM questions
                WHERE course_id IS NOT NULL";
    
    if ($con->query($copySql)) {
        $copiedCount = $con->affected_rows;
        echo "✓ Copied $copiedCount questions to practice_questions table\n";
    } else {
        echo "✗ Error copying questions: " . $con->error . "\n";
    }
}

// Show summary
echo "\n=== Migration Summary ===\n";

$tables = ['practice_questions', 'practice_results', 'practice_answers'];
foreach ($tables as $table) {
    $result = $con->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "✓ $table: $count records\n";
    }
}

// Show courses with practice questions
echo "\n=== Courses with Practice Questions ===\n";
$result = $con->query("SELECT c.course_name, COUNT(pq.practice_question_id) as question_count 
                       FROM courses c
                       LEFT JOIN practice_questions pq ON c.course_id = pq.course_id AND pq.is_active = 1
                       GROUP BY c.course_id, c.course_name
                       HAVING question_count > 0
                       ORDER BY c.course_name");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  • {$row['course_name']}: {$row['question_count']} questions\n";
    }
} else {
    echo "  No practice questions found yet.\n";
}

$con->close();

echo "\n=== Migration Complete! ===\n";
echo "\nNext Steps:\n";
echo "1. Update Student/practice-selection.php to use practice_questions table\n";
echo "2. Update Student/practice.php to use practice_questions table\n";
echo "3. Create instructor interface to add practice questions\n";
echo "4. Test the practice system\n";
?>
