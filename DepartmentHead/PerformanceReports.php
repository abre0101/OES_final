<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Performance Reports";
$deptId = $_SESSION['DeptId'] ?? null;

// Get top performing students
$top_students = "SELECT s.student_code, s.full_name, 
                 AVG(er.marks_obtained) as avg_marks,
                 COUNT(er.result_id) as exams_taken
                 FROM students s
                 INNER JOIN exam_results er ON s.student_id = er.student_id
                 INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
                 INNER JOIN courses c ON es.course_id = c.course_id
                 WHERE s.department_id = ? AND s.is_active = 1
                 GROUP BY s.student_id
                 HAVING exams_taken > 0
                 ORDER BY avg_marks DESC
                 LIMIT 10";
$stmt = $con->prepare($top_students);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$top_performers = $stmt->get_result();

// Get course performance
$course_performance = "SELECT c.course_code, c.course_name,
                       COUNT(DISTINCT er.result_id) as total_attempts,
                       AVG(er.marks_obtained) as avg_marks,
                       MAX(er.marks_obtained) as highest_marks,
                       MIN(er.marks_obtained) as lowest_marks
                       FROM courses c
                       LEFT JOIN exam_schedules es ON c.course_id = es.course_id
                       LEFT JOIN exam_results er ON es.schedule_id = er.schedule_id
                       WHERE c.department_id = ? AND c.is_active = 1
                       GROUP BY c.course_id
                       ORDER BY avg_marks DESC";
$stmt = $con->prepare($course_performance);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$courses = $stmt->get_result();

// Get pass/fail statistics
$pass_fail = "SELECT 
              COUNT(CASE WHEN er.marks_obtained >= es.passing_marks THEN 1 END) as passed,
              COUNT(CASE WHEN er.marks_obtained < es.passing_marks THEN 1 END) as failed,
              COUNT(er.result_id) as total
              FROM exam_results er
              INNER JOIN exam_schedules es ON er.schedule_id = es.schedule_id
              INNER JOIN courses c ON es.course_id = c.course_id
              WHERE c.department_id = ?";
$stmt = $con->prepare($pass_fail);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$pass_fail_data = $stmt->get_result()->fetch_assoc();
$pass_rate = $pass_fail_data['total'] > 0 ? 
    round(($pass_fail_data['passed'] / $pass_fail_data['total']) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Reports - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        <div class="admin-content">
            <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Performance Reports</h1>
                    <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                        Student and course performance analysis for <?php echo $_SESSION['Dept']; ?> Department
                    </p>
                </div>
                <a href="ExportToExcel.php?type=departmental" class="btn btn-success">📊 Export to Excel</a>
            </div>

            <!-- Pass/Fail Statistics -->
            <div class="row" style="margin-bottom: 2rem;">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>Overall Pass/Fail Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="stat-card">
                                        <h3><?php echo $pass_fail_data['total']; ?></h3>
                                        <p>Total Attempts</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card" style="background: #d4edda;">
                                        <h3><?php echo $pass_fail_data['passed']; ?></h3>
                                        <p>Passed</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card" style="background: #f8d7da;">
                                        <h3><?php echo $pass_fail_data['failed']; ?></h3>
                                        <p>Failed</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stat-card" style="background: #cce5ff;">
                                        <h3><?php echo $pass_rate; ?>%</h3>
                                        <p>Pass Rate</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Pass/Fail Distribution</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="passFailChart" style="max-height: 200px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performing Students -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>Top 10 Performing Students</h3>
                </div>
                <div class="card-body">
                    <?php if($top_performers->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Exams Taken</th>
                                <th>Average Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; while($student = $top_performers->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $rank++; ?></strong></td>
                                <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo $student['exams_taken']; ?></td>
                                <td><strong><?php echo round($student['avg_marks'], 2); ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">No performance data available yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Course Performance -->
            <div class="card">
                <div class="card-header">
                    <h3>Course-wise Performance</h3>
                </div>
                <div class="card-body">
                    <?php if($courses->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Total Attempts</th>
                                <th>Average Marks</th>
                                <th>Highest</th>
                                <th>Lowest</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($course = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['total_attempts'] ?? 0; ?></td>
                                <td><?php echo $course['avg_marks'] ? round($course['avg_marks'], 2) : 'N/A'; ?></td>
                                <td><?php echo $course['highest_marks'] ?? 'N/A'; ?></td>
                                <td><?php echo $course['lowest_marks'] ?? 'N/A'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">No course performance data available.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
                <a href="Reports.php" class="btn btn-secondary">📊 Back to Reports</a>
            </div>
        </div>
    </div>

    <script>
        // Pass/Fail Pie Chart
        const passFailCtx = document.getElementById('passFailChart').getContext('2d');
        new Chart(passFailCtx, {
            type: 'doughnut',
            data: {
                labels: ['Passed', 'Failed'],
                datasets: [{
                    data: [<?php echo $pass_fail_data['passed']; ?>, <?php echo $pass_fail_data['failed']; ?>],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
