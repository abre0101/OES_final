<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name']) || !isset($_SESSION['ID'])){
    header("Location: ../index.php");
    exit();
}

$resultId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Connect to database
$con = require_once(__DIR__ . "/../Connections/OES.php"); $con;

// Try to get result from database first
if ($resultId > 0) {
    $sql = "SELECT result.*, exam_category.exam_name, course.course_name 
            FROM exam_results 
            LEFT JOIN exam_categories ON result.exam_id = exam_category.exam_id
            LEFT JOIN courses ON result.course_id = course.course_id
            WHERE result.result_id = ? AND result.student_id = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("is", $resultId, $_SESSION['ID']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && mysqli_num_rows($result) > 0) {
        $examResult = mysqli_fetch_assoc($result);
        $examName = $examResult['exam_name'] ?? 'Exam';
        $courseName = $examResult['course_name'] ?? 'Course';
        $obtainedMarks = $examResult['Result'];
        $maxMarks = $examResult['Total'] * 2;
        $correct = $examResult['Correct'];
        $wrong = $examResult['Wrong'];
        $total = $examResult['Total'];
    }
    $stmt->close();
}

// Fallback to session data if database query failed or no ID
if (!isset($examName) && isset($_SESSION['last_exam_result'])) {
    $sessionData = $_SESSION['last_exam_result'];
    $examName = $sessionData['exam_name'] ?? 'Exam';
    $courseName = $sessionData['course_name'] ?? 'Course';
    $correct = $sessionData['correct'];
    $wrong = $sessionData['wrong'];
    $total = $sessionData['total'];
    $obtainedMarks = $sessionData['score'];
    $maxMarks = $total * 2;
}

// If still no data, redirect to results page
if (!isset($examName)) {
    mysqli_close($con);
    header("Location: Result-modern.php");
    exit();
}

mysqli_close($con);
$currentDate = date('d M Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Result - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .result-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(26, 43, 74, 0.5) 0%, rgba(15, 26, 46, 0.6) 100%),
                url('../images/exam.webp') center/cover no-repeat fixed;
        }
        
        .result-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            max-width: 700px;
            width: 100%;
            overflow: hidden;
        }
        
        .result-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .result-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .result-header h1 {
            color: white;
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }
        
        .result-header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 1.1rem;
        }
        
        .result-body {
            padding: 3rem;
        }
        
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        
        .result-table tr {
            border-bottom: 1px solid var(--border-color);
        }
        
        .result-table td {
            padding: 1.25rem 1rem;
            font-size: 1.1rem;
        }
        
        .result-table td:first-child {
            font-weight: 600;
            color: var(--text-secondary);
            width: 40%;
        }
        
        .result-table td:last-child {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.25rem;
        }
        
        .score-highlight {
            background: linear-gradient(135deg, var(--success-color) 0%, #27ae60 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            text-align: center;
            margin: 2rem 0;
        }
        
        .score-highlight h2 {
            margin: 0 0 0.5rem 0;
            font-size: 3rem;
            color: white;
        }
        
        .score-highlight p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .result-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .stat-box {
            padding: 1.5rem;
            border-radius: var(--radius-md);
            text-align: center;
            border: 2px solid;
        }
        
        .stat-box.correct {
            background: rgba(40, 167, 69, 0.1);
            border-color: var(--success-color);
        }
        
        .stat-box.wrong {
            background: rgba(220, 53, 69, 0.1);
            border-color: var(--danger-color);
        }
        
        .stat-box.total {
            background: rgba(0, 123, 255, 0.1);
            border-color: var(--primary-color);
        }
        
        .stat-box h3 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            color: var(--primary-color);
        }
        
        .stat-box p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="result-card">
            <div class="result-header">
                <div class="result-icon">🎉</div>
                <h1>Exam Completed!</h1>
                <p>Your exam has been submitted successfully</p>
            </div>
            
            <div class="result-body">
                <h2 style="text-align: center; color: var(--primary-color); margin-bottom: 2rem;">Examination Result</h2>
                
                <table class="result-table">
                    <tr>
                        <td>Exam Name</td>
                        <td><?php echo htmlspecialchars($examName); ?></td>
                    </tr>
                    <tr>
                        <td>Course</td>
                        <td><?php echo htmlspecialchars($courseName); ?></td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td><?php echo $currentDate; ?></td>
                    </tr>
                    <tr>
                        <td>Student Name</td>
                        <td><?php echo htmlspecialchars($_SESSION['Name']); ?></td>
                    </tr>
                    <tr>
                        <td>Student ID</td>
                        <td><?php echo htmlspecialchars($_SESSION['ID']); ?></td>
                    </tr>
                </table>
                
                <div class="stats-grid">
                    <div class="stat-box correct">
                        <h3><?php echo $correct; ?></h3>
                        <p>Correct Answers</p>
                    </div>
                    <div class="stat-box wrong">
                        <h3><?php echo $wrong; ?></h3>
                        <p>Wrong Answers</p>
                    </div>
                    <div class="stat-box total">
                        <h3><?php echo $total; ?></h3>
                        <p>Total Questions</p>
                    </div>
                </div>
                
                <div class="score-highlight">
                    <h2><?php echo $obtainedMarks; ?> / <?php echo $maxMarks; ?></h2>
                    <p>Your Score</p>
                </div>
                
                <div class="result-actions">
                    <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
                    <a href="Result-modern.php" class="btn btn-success">📊 View All Results</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
