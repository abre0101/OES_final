<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location: ../index.php");
    exit();
}

// Get available courses with questions
require_once(__DIR__ . "/../Connections/OES.php");
$sql = "SELECT c.course_name, COUNT(q.question_id) as question_count 
        FROM courses c
        INNER JOIN questions q ON c.course_id = q.course_id
        WHERE q.approval_status = 'approved'
        GROUP BY c.course_id, c.course_name 
        HAVING question_count > 0
        ORDER BY c.course_name";
$result = mysqli_query($con,$sql);
$courses = [];
while($row = mysqli_fetch_array($result)) {
    $courses[] = $row;
}
mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Practice Course - Debre Markos University Health Campus</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .practice-selection-container {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(79, 70, 229, 0.1) 100%);
        }
        
        .selection-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }
        
        .selection-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            color: white;
            text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(0, 0, 0, 0.3);
            font-weight: 800;
        }
        
        .selection-header p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 1;
            color: white;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5), 0 0 15px rgba(0, 0, 0, 0.3);
            font-weight: 600;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 3rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .course-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            border: 2px solid transparent;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
            border-color: #6366f1;
        }
        
        .course-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }
        
        .course-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .course-info {
            display: flex;
            justify-content: space-around;
            padding-top: 1rem;
            border-top: 2px solid var(--border-color);
        }
        
        .course-stat {
            text-align: center;
        }
        
        .course-stat .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6366f1;
        }
        
        .course-stat .label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .back-button {
            position: fixed;
            top: 2rem;
            left: 2rem;
            z-index: 100;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .empty-state h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: var(--text-secondary);
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="practice-selection-container">
        <a href="index.php" class="back-button btn btn-secondary">
            ← Back to Dashboard
        </a>
        
        <div class="selection-header">
            <h1>📚 Select Practice Course</h1>
            <p>Choose a subject to start practicing</p>
        </div>
        
        <?php if (count($courses) > 0): ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <a href="practice-modern.php?course=<?php echo urlencode($course['course_name']); ?>" class="course-card">
                        <div class="course-icon">📖</div>
                        <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                        <div class="course-info">
                            <div class="course-stat">
                                <div class="value"><?php echo $course['question_count']; ?></div>
                                <div class="label">Questions</div>
                            </div>
                            <div class="course-stat">
                                <div class="value">∞</div>
                                <div class="label">No Time Limit</div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h2>No Practice Questions Available</h2>
                <p>There are currently no practice questions in the system. Please check back later or contact your instructor.</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 2rem;">
                    Back to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
