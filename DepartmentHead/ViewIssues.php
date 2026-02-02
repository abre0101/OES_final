<?php
session_start();
require_once('../Connections/config.php');

// Check if department head is logged in
if (!isset($_SESSION['department_head_id'])) {
    header("Location: ../institute-login.php");
    exit();
}

$department_head_id = $_SESSION['department_head_id'];
$department_id = $_SESSION['department_id'];

// Handle status update
if (isset($_POST['update_status'])) {
    $issue_id = mysqli_real_escape_string($conn, $_POST['issue_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_sql = "UPDATE technical_issues SET status = '$new_status' WHERE issue_id = '$issue_id'";
    mysqli_query($conn, $update_sql);
}

// Get all issues for department's exams
$issues_query = "SELECT ti.*, s.full_name as student_name, s.student_code, 
                 e.exam_name, c.course_name, c.course_code
                 FROM technical_issues ti
                 INNER JOIN students s ON ti.student_id = s.student_id
                 INNER JOIN exams e ON ti.exam_id = e.exam_id
                 INNER JOIN courses c ON e.course_id = c.course_id
                 WHERE c.department_id = '$department_id'
                 ORDER BY ti.reported_at DESC";
$issues_result = mysqli_query($conn, $issues_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technical Issues</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: #f4f7fa;
        }
        .main-container {
            padding: 20px;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .issue-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background: #ffc107;
            color: #000;
        }
        .status-resolved {
            background: #28a745;
            color: white;
        }
        .status-closed {
            background: #6c757d;
            color: white;
        }
        .issue-meta {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .issue-description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <?php include 'header-component.php'; ?>
    
    <div class="main-container">
        <div class="page-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Technical Issues</h2>
            <p>View and manage technical issues reported by students</p>
        </div>
        
        <?php if (mysqli_num_rows($issues_result) == 0): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No technical issues reported yet.
            </div>
        <?php else: ?>
            <?php while ($issue = mysqli_fetch_assoc($issues_result)): ?>
                <div class="issue-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5><?php echo htmlspecialchars($issue['course_code'] . ' - ' . $issue['exam_name']); ?></h5>
                            <div class="issue-meta">
                                <i class="fas fa-user"></i> <strong><?php echo htmlspecialchars($issue['student_name']); ?></strong> 
                                (<?php echo htmlspecialchars($issue['student_code']); ?>)
                                <br>
                                <i class="fas fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($issue['reported_at'])); ?>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $issue['status']; ?>">
                            <?php echo strtoupper($issue['status']); ?>
                        </span>
                    </div>
                    
                    <div class="issue-description">
                        <strong>Issue Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($issue['issue_description'])); ?>
                    </div>
                    
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>">
                        <div class="form-row align-items-center">
                            <div class="col-auto">
                                <label class="mr-2">Update Status:</label>
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="pending" <?php echo $issue['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="resolved" <?php echo $issue['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="closed" <?php echo $issue['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
        
        <a href="index.php" class="btn btn-secondary mt-3">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>
