<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$pageTitle = "Analytics & Insights";

// Check if required tables exist
$questionsExists = false;
$studentAnswersExists = false;
$questionTopicsExists = false;
$examResultsExists = false;

$tableCheck = $con->query("SHOW TABLES LIKE 'questions'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $questionsExists = true;
}

$tableCheck = $con->query("SHOW TABLES LIKE 'student_answers'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $studentAnswersExists = true;
}

$tableCheck = $con->query("SHOW TABLES LIKE 'question_topics'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $questionTopicsExists = true;
}

$tableCheck = $con->query("SHOW TABLES LIKE 'exam_results'");
if($tableCheck && $tableCheck->num_rows > 0) {
    $examResultsExists = true;
}

// Get question difficulty analysis
$questionDifficulty = null;
if($questionsExists && $studentAnswersExists) {
    $questionDifficulty = $con->query("SELECT 
        q.question_id,
        q.question_text,
        c.course_name,
        COUNT(DISTINCT sa.answer_id) as attempt_count,
        SUM(CASE WHEN sa.is_correct = 1 THEN 1 ELSE 0 END) as correct_count,
        ROUND((SUM(CASE WHEN sa.is_correct = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(sa.answer_id), 0)), 2) as success_rate
        FROM questions q
        INNER JOIN courses c ON q.course_id = c.course_id
        LEFT JOIN student_answers sa ON q.question_id = sa.question_id
        GROUP BY q.question_id
        HAVING attempt_count > 0
        ORDER BY success_rate ASC
        LIMIT 20");
}

// Get performance trends over time
$performanceTrends = null;
if($examResultsExists) {
    $performanceTrends = $con->query("SELECT 
        DATE(er.exam_submitted_at) as exam_date,
        AVG(er.percentage_score) as avg_score,
        COUNT(*) as exam_count,
        SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END) as pass_count
        FROM exam_results er
        WHERE er.percentage_score > 0
        GROUP BY DATE(er.exam_submitted_at)
        ORDER BY exam_date DESC
        LIMIT 30");
}

// Get course performance comparison - using exam results
$coursePerformance = null;
if($examResultsExists) {
    $coursePerformance = $con->query("SELECT 
        c.course_name,
        c.course_code,
        COUNT(DISTINCT er.result_id) as exam_count,
        COUNT(DISTINCT er.student_id) as student_count,
        ROUND(AVG(er.percentage_score), 2) as avg_score,
        SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END) as pass_count,
        ROUND((SUM(CASE WHEN er.pass_status = 'Pass' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(er.result_id), 0)), 2) as pass_rate
        FROM courses c
        INNER JOIN exams es ON c.course_id = es.course_id
        INNER JOIN exam_results er ON es.exam_id = er.exam_id
        GROUP BY c.course_id
        HAVING exam_count > 0
        ORDER BY avg_score DESC");
}

// Get topic performance (if topics exist)
$topicPerformance = null;
if($questionTopicsExists && $questionsExists && $studentAnswersExists) {
    $topicPerformance = $con->query("SELECT 
        qt.topic_name,
        c.course_name,
        COUNT(DISTINCT q.question_id) as question_count,
        COUNT(DISTINCT sa.answer_id) as attempt_count,
        ROUND(AVG(CASE WHEN sa.is_correct = 1 THEN 100 ELSE 0 END), 2) as avg_accuracy
        FROM question_topics qt
        LEFT JOIN questions q ON qt.topic_id = q.topic_id
        LEFT JOIN courses c ON q.course_id = c.course_id
        LEFT JOIN student_answers sa ON q.question_id = sa.question_id
        GROUP BY qt.topic_id, qt.topic_name, c.course_name
        HAVING question_count > 0 AND attempt_count > 0
        ORDER BY avg_accuracy ASC
        LIMIT 10");
}

// Get overall statistics
$stats = [];
if($questionsExists) {
    $result = $con->query("SELECT COUNT(*) as count FROM questions");
    $stats['total_questions'] = $result ? $result->fetch_assoc()['count'] : 0;
} else {
    $stats['total_questions'] = 0;
}

if($studentAnswersExists) {
    $result = $con->query("SELECT COUNT(*) as count FROM student_answers");
    $stats['total_attempts'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    $result = $con->query("SELECT ROUND(AVG(CASE WHEN is_correct = 1 THEN 100 ELSE 0 END), 2) as avg FROM student_answers");
    $stats['avg_difficulty'] = $result ? ($result->fetch_assoc()['avg'] ?? 0) : 0;
} else {
    $stats['total_attempts'] = 0;
    $stats['avg_difficulty'] = 0;
}

if($questionsExists && $studentAnswersExists) {
    $result = $con->query("SELECT COUNT(*) as count FROM (
        SELECT q.question_id, 
        ROUND((SUM(CASE WHEN sa.is_correct = 1 THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(sa.answer_id), 0)), 2) as success_rate
        FROM questions q
        LEFT JOIN student_answers sa ON q.question_id = sa.question_id
        GROUP BY q.question_id
        HAVING success_rate < 50 AND COUNT(sa.answer_id) > 0
    ) as hard_questions");
    $stats['hardest_questions'] = $result ? $result->fetch_assoc()['count'] : 0;
} else {
    $stats['hardest_questions'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Instructor</title>
    <link href="../assets/css/modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body.admin-layout { background: #f5f7fa; font-family: 'Poppins', sans-serif; }
        .page-header-modern { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); color: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2); margin-bottom: 2rem; }
        .page-header-modern h1 { margin: 0 0 0.5rem 0; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; color: white; }
        .page-header-modern h1 span { color: white; }
        .page-header-modern p { margin: 0; opacity: 0.95; font-size: 1.05rem; color: white; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.75rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); border-left: 5px solid; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.primary { border-left-color: #007bff; }
        .stat-card.success { border-left-color: #28a745; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
        .stat-value { font-size: 2.5rem; font-weight: 900; color: #003366; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.95rem; color: #6c757d; font-weight: 500; }
        .report-section { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); margin-bottom: 2rem; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 3px solid #f0f0f0; }
        .section-title { font-size: 1.4rem; font-weight: 700; color: #003366; display: flex; align-items: center; gap: 0.75rem; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { background: linear-gradient(135deg, #003366 0%, #0055aa 100%); }
        .data-table th { padding: 1rem; text-align: left; color: white; font-weight: 600; font-size: 0.9rem; white-space: nowrap; }
        .data-table td { padding: 0.85rem 1rem; border-bottom: 1px solid #e8eef3; font-size: 0.9rem; }
        .data-table tbody tr:hover { background: #f8f9fa; }
        .score-badge { padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 600; font-size: 0.85rem; display: inline-block; }
        .badge-excellent { background: #d4edda; color: #155724; }
        .badge-good { background: #d1ecf1; color: #0c5460; }
        .badge-average { background: #fff3cd; color: #856404; }
        .badge-poor { background: #f8d7da; color: #721c24; }
        .chart-container { margin: 1.5rem 0; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; }
        .difficulty-badge { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .difficulty-easy { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .difficulty-medium { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .difficulty-hard { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php $pageTitle = 'Analytics & Insights'; include 'header-component.php'; ?>

        <div class="admin-content">
            <div class="page-header-modern">
                <h1><span>📊</span> Analytics & Insights</h1>
                <p>Question difficulty analysis and student performance trends</p>
            </div>

            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">❓</div>
                    <div class="stat-value"><?php echo number_format($stats['total_questions'] ?? 0); ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo number_format($stats['total_attempts'] ?? 0); ?></div>
                    <div class="stat-label">Total Attempts</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">📈</div>
                    <div class="stat-value"><?php echo number_format($stats['avg_difficulty'] ?? 0, 1); ?>%</div>
                    <div class="stat-label">Avg Success Rate</div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-value"><?php echo number_format($stats['hardest_questions'] ?? 0); ?></div>
                    </div>
                    <div style="font-size: 0.9rem; color: var(--text-secondary);">Hard Questions (<50%)</div>
                </div>
            </div>

            <div class="grid grid-2">
                <!-- Question Difficulty Analysis -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🎯 Most Difficult Questions</h3>
                    </div>
                    <div style="padding: 2rem; max-height: 600px; overflow-y: auto;">
                        <?php if($questionDifficulty && $questionDifficulty->num_rows > 0): ?>
                        <?php while($q = $questionDifficulty->fetch_assoc()): ?>
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1rem; border-left: 4px solid <?php 
                            echo $q['success_rate'] < 40 ? '#dc3545' : ($q['success_rate'] < 70 ? 'var(--warning-color)' : 'var(--success-color)'); 
                        ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                <div style="flex: 1;">
                                    <strong style="color: var(--primary-color);">Question #<?php echo $q['question_id']; ?></strong>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                        <?php echo $q['course_name']; ?>
                                    </div>
                                </div>
                                <span style="padding: 0.25rem 0.75rem; background: <?php 
                                    echo $q['success_rate'] < 40 ? '#dc3545' : ($q['success_rate'] < 70 ? 'var(--warning-color)' : 'var(--success-color)'); 
                                ?>; color: white; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo $q['success_rate']; ?>% Success
                                </span>
                            </div>
                            <p style="margin: 0 0 0.75rem 0; color: var(--text-secondary); font-size: 0.9rem;">
                                <?php echo substr($q['question_text'], 0, 150); ?><?php echo strlen($q['question_text']) > 150 ? '...' : ''; ?>
                            </p>
                            <div style="display: flex; gap: 2rem; font-size: 0.85rem; color: var(--text-secondary);">
                                <div>
                                    <strong><?php echo $q['attempt_count']; ?></strong> attempts
                                </div>
                                <div>
                                    <strong><?php echo $q['correct_count']; ?></strong> correct
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                            <p>No question attempt data available yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Performance Trends Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📈 Performance Trends</h3>
                    </div>
                    <div style="padding: 2rem;">
                        <canvas id="performanceChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Course Performance Comparison -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">📚 Course Performance Comparison</h3>
                </div>
                <div style="padding: 2rem;">
                    <?php if($coursePerformance && $coursePerformance->num_rows > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                        <?php while($course = $coursePerformance->fetch_assoc()): ?>
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md); text-align: center;">
                            <h4 style="margin: 0 0 1rem 0; color: var(--primary-color);">
                                <?php echo $course['course_name']; ?>
                            </h4>
                            <div style="font-size: 2.5rem; font-weight: 800; color: <?php 
                                echo $course['avg_score'] < 50 ? '#dc3545' : ($course['avg_score'] < 75 ? 'var(--warning-color)' : 'var(--success-color)'); 
                            ?>; margin-bottom: 0.5rem;">
                                <?php echo $course['avg_score']; ?>%
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
                                <?php echo $course['exam_count']; ?> exams • 
                                <?php echo $course['student_count']; ?> students
                            </div>
                            <div style="padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.85rem;">
                                <span style="color: var(--success-color); font-weight: 600;">
                                    <?php echo $course['pass_rate']; ?>%
                                </span>
                                <span style="color: var(--text-secondary);"> pass rate</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        No course performance data available
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Topic Performance (if available) -->
            <?php if($topicPerformance && $topicPerformance->num_rows > 0): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">📖 Weakest Topics (Need Attention)</h3>
                </div>
                <div style="padding: 2rem;">
                    <?php while($topic = $topicPerformance->fetch_assoc()): ?>
                    <div style="background: var(--bg-light); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: var(--primary-color);"><?php echo $topic['topic_name']; ?></strong>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?php echo $topic['course_name']; ?> • <?php echo $topic['question_count']; ?> questions
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: <?php 
                                echo $topic['avg_accuracy'] < 50 ? '#dc3545' : ($topic['avg_accuracy'] < 70 ? 'var(--warning-color)' : 'var(--success-color)'); 
                            ?>;">
                                <?php echo $topic['avg_accuracy']; ?>%
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary);">Success Rate</div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Insights & Recommendations -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">💡 Insights & Recommendations</h3>
                </div>
                <div style="padding: 2rem;">
                    <div class="grid grid-2">
                        <div style="background: rgba(0, 123, 255, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--primary-color);">
                            <h4 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">📊 Question Quality</h4>
                            <p style="margin: 0; color: var(--text-secondary);">
                                <?php 
                                if($stats['hardest_questions'] > 10) {
                                    echo "You have {$stats['hardest_questions']} questions with <50% success rate. Consider reviewing these for clarity or difficulty.";
                                } else {
                                    echo "Your questions have good difficulty balance. Keep monitoring student performance.";
                                }
                                ?>
                            </p>
                        </div>
                        <div style="background: rgba(40, 167, 69, 0.1); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--success-color);">
                            <h4 style="margin: 0 0 0.5rem 0; color: var(--success-color);">✅ Best Practices</h4>
                            <ul style="margin: 0.5rem 0 0 1.5rem; color: var(--text-secondary);">
                                <li>Review questions with <40% success rate</li>
                                <li>Balance easy, medium, and hard questions</li>
                                <li>Use topics to organize questions</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        // Performance Trends Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceData = {
            labels: [
                <?php 
                $trends = [];
                if($performanceTrends) {
                    $performanceTrends->data_seek(0);
                    while($trend = $performanceTrends->fetch_assoc()) {
                        $trends[] = $trend;
                        echo "'" . date('M d', strtotime($trend['exam_date'])) . "',";
                    }
                }
                ?>
            ],
            datasets: [{
                label: 'Average Score',
                data: [
                    <?php 
                    foreach($trends as $trend) {
                        echo $trend['avg_score'] . ",";
                    }
                    ?>
                ],
                borderColor: 'rgb(0, 123, 255)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: performanceData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php $con->close(); ?>
