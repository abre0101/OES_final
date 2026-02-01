<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$instructor_id = $_SESSION['ID'];
$result_id = $_GET['result_id'] ?? 0;

// Get result details with verification that instructor teaches this course
$resultQuery = $con->prepare("SELECT 
    er.*,
    es.exam_name,
    c.course_name,
    c.course_code,
    ec.category_name,
    s.student_code,
    s.full_name as student_name,
    s.email as student_email
    FROM exam_results er
    INNER JOIN students s ON er.student_id = s.student_id
    INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
    INNER JOIN courses c ON es.course_id = c.course_id
    INNER JOIN exam_categories ec ON es.exam_category_id = ec.exam_category_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE er.result_id = ? AND ic.instructor_id = ? AND ic.is_active = TRUE");
$resultQuery->bind_param("ii", $result_id, $instructor_id);
$resultQuery->execute();
$result = $resultQuery->get_result()->fetch_assoc();
$resultQuery->close();

if(!$result) {
    header("Location: SeeResults.php");
    exit();
}

// Get all questions and student's answers
$questionsQuery = $con->prepare("SELECT 
    q.question_id,
    q.question_text,
    q.option_a,
    q.option_b,
    q.option_c,
    q.option_d,
    q.correct_answer,
    q.point_value,
    sa.selected_answer,
    sa.is_correct,
    sa.points_earned
    FROM exam_questions eq
    INNER JOIN questions q ON eq.question_id = q.question_id
    LEFT JOIN student_answers sa ON q.question_id = sa.question_id AND sa.result_id = ?
    WHERE eq.schedule_id = ?
    ORDER BY eq.question_order");
$questionsQuery->bind_param("ii", $result_id, $result['schedule_id']);
$questionsQuery->execute();
$questions = $questionsQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Result - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .result-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
        }
        
        .result-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .result-stat {
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: var(--radius-md);
            text-align: center;
        }
        
        .result-stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }
        
        .result-stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .question-review {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .question-review.correct {
            border-left: 5px solid var(--success-color);
        }
        
        .question-review.incorrect {
            border-left: 5px solid #dc3545;
        }
        
        .question-review.unanswered {
            border-left: 5px solid #ffc107;
        }
        
        .option-review {
            padding: 1rem;
            margin: 0.75rem 0;
            border-radius: var(--radius-md);
            border: 2px solid #e0e0e0;
            background: var(--bg-light);
        }
        
        .option-review.correct-answer {
            background: rgba(40, 167, 69, 0.1);
            border-color: var(--success-color);
        }
        
        .option-review.wrong-answer {
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>📊 Student Exam Result</h1>
                <p>Detailed view of student performance</p>
            </div>

            <div style="margin-bottom: 2rem;">
                <a href="SeeResults.php" class="btn btn-secondary">← Back to Results</a>
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
            </div>

            <!-- Result Header -->
            <div class="result-header">
                <h2 style="margin: 0 0 0.5rem 0; color: white;">
                    <?php echo htmlspecialchars($result['student_name']); ?>
                </h2>
                <p style="margin: 0; opacity: 0.9;">
                    <?php echo htmlspecialchars($result['student_code']); ?> | <?php echo htmlspecialchars($result['student_email']); ?>
                </p>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">
                    <?php echo htmlspecialchars($result['exam_name']); ?> - <?php echo htmlspecialchars($result['course_name']); ?>
                </p>
                
                <div class="result-stats">
                    <div class="result-stat">
                        <div class="result-stat-value"><?php echo round($result['percentage_score'], 1); ?>%</div>
                        <div class="result-stat-label">Score</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value"><?php echo $result['letter_grade']; ?></div>
                        <div class="result-stat-label">Grade</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value"><?php echo $result['correct_answers']; ?></div>
                        <div class="result-stat-label">Correct</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value"><?php echo $result['wrong_answers']; ?></div>
                        <div class="result-stat-label">Wrong</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value"><?php echo $result['unanswered']; ?></div>
                        <div class="result-stat-label">Unanswered</div>
                    </div>
                    <div class="result-stat">
                        <div class="result-stat-value" style="font-size: 1.5rem;">
                            <?php echo $result['pass_status'] == 'Pass' ? '✅' : '❌'; ?>
                        </div>
                        <div class="result-stat-label"><?php echo $result['pass_status']; ?></div>
                    </div>
                </div>
            </div>

            <!-- Questions Review -->
            <h3 style="margin-bottom: 1.5rem;">📝 Answer Review</h3>
            
            <?php 
            $qNum = 1;
            while($q = $questions->fetch_assoc()): 
                $studentAnswer = $q['selected_answer'];
                $correctAnswer = $q['correct_answer'];
                $isCorrect = $q['is_correct'];
                
                $questionClass = 'unanswered';
                if($studentAnswer) {
                    $questionClass = $isCorrect ? 'correct' : 'incorrect';
                }
            ?>
            <div class="question-review <?php echo $questionClass; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <h4 style="margin: 0; color: var(--primary-color);">Question <?php echo $qNum++; ?></h4>
                    <?php if(!$studentAnswer): ?>
                        <span style="background: #ffc107; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600;">
                            ⚠️ Not Answered
                        </span>
                    <?php elseif($isCorrect): ?>
                        <span style="background: var(--success-color); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600;">
                            ✓ Correct (+<?php echo $q['points_earned']; ?> pts)
                        </span>
                    <?php else: ?>
                        <span style="background: #dc3545; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600;">
                            ✗ Incorrect (0 pts)
                        </span>
                    <?php endif; ?>
                </div>
                
                <p style="font-size: 1.1rem; line-height: 1.6; margin: 1rem 0;">
                    <?php echo htmlspecialchars($q['question_text']); ?>
                </p>
                
                <div style="margin-top: 1.5rem;">
                    <?php
                    $options = [
                        'A' => $q['option_a'], 
                        'B' => $q['option_b'], 
                        'C' => $q['option_c'], 
                        'D' => $q['option_d']
                    ];
                    foreach($options as $letter => $text):
                        if(empty($text)) continue;
                        
                        $optionClass = '';
                        $indicator = '';
                        
                        if($letter == $correctAnswer) {
                            $optionClass = 'correct-answer';
                            $indicator = '<span style="color: var(--success-color); font-weight: 700; margin-left: 1rem;">✓ Correct Answer</span>';
                        }
                        
                        if($letter == $studentAnswer && $letter != $correctAnswer) {
                            $optionClass = 'wrong-answer';
                            $indicator = '<span style="color: #dc3545; font-weight: 700; margin-left: 1rem;">✗ Student Answer</span>';
                        } elseif($letter == $studentAnswer && $letter == $correctAnswer) {
                            $indicator = '<span style="color: var(--success-color); font-weight: 700; margin-left: 1rem;">✓ Student Answer (Correct)</span>';
                        }
                    ?>
                    <div class="option-review <?php echo $optionClass; ?>">
                        <strong><?php echo $letter; ?>.</strong> <?php echo htmlspecialchars($text); ?>
                        <?php echo $indicator; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
