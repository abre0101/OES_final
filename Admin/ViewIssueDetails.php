<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Administrator session
SessionManager::startSession('Administrator');

// Check if user is logged in
if(!isset($_SESSION['username'])){
    header("Location:../auth/staff-login.php");
    exit();
}

// Validate user role - only administrators can access this page
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Administrator'){
    SessionManager::destroySession();
    header("Location:../auth/staff-login.php");
    exit();
}

$con = require_once('../Connections/OES.php');

if (!isset($_GET['issue_id'])) {
    echo '<div class="alert alert-danger">No issue ID provided.</div>';
    exit();
}

$issue_id = mysqli_real_escape_string($con, $_GET['issue_id']);
$admin_id = $_SESSION['ID'];

$query = "SELECT ti.*, s.full_name as student_name, s.student_code, s.email as student_email,
          e.exam_name, e.exam_date, e.start_time, e.end_time,
          c.course_name, c.course_code,
          a.username as resolved_by_name
          FROM technical_issues ti
          INNER JOIN students s ON ti.student_id = s.student_id
          INNER JOIN exams e ON ti.exam_id = e.exam_id
          INNER JOIN courses c ON e.course_id = c.course_id
          LEFT JOIN administrators a ON ti.resolved_by = a.admin_id
          WHERE ti.issue_id = '$issue_id'";

$result = mysqli_query($con, $query);
$issue = mysqli_fetch_assoc($result);

if (!$issue) {
    echo '<div class="alert alert-danger">Issue not found.</div>';
    exit();
}
?>

<div class="issue-details">
    <!-- Student Information Card -->
    <div class="info-card">
        <div class="card-header">
            <h5><i class="fas fa-user-graduate"></i> Student Information</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['student_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Student Code:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['student_code']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['student_email']); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Exam Information Card -->
    <div class="info-card">
        <div class="card-header">
            <h5><i class="fas fa-file-alt"></i> Exam Information</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">Exam:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['exam_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Course:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['course_name']) . ' (' . htmlspecialchars($issue['course_code']) . ')'; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value"><?php echo date('M d, Y', strtotime($issue['exam_date'])); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Issue Details Card -->
    <div class="info-card">
        <div class="card-header">
            <h5><i class="fas fa-exclamation-circle"></i> Issue Details</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">Priority:</span>
                <span class="info-value">
                    <span class="badge badge-priority-<?php echo $issue['priority']; ?>"><?php echo strtoupper($issue['priority']); ?></span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="badge badge-status-<?php echo $issue['status']; ?>"><?php echo strtoupper($issue['status']); ?></span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Reported At:</span>
                <span class="info-value"><?php echo date('M d, Y H:i:s', strtotime($issue['reported_at'])); ?></span>
            </div>
            <?php if ($issue['resolved_at']): ?>
            <div class="info-row">
                <span class="info-label">Resolved At:</span>
                <span class="info-value"><?php echo date('M d, Y H:i:s', strtotime($issue['resolved_at'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Resolved By:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['resolved_by_name']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Issue Description Card -->
    <div class="info-card">
        <div class="card-header">
            <h5><i class="fas fa-comment-alt"></i> Issue Description</h5>
        </div>
        <div class="card-body">
            <div class="description-box">
                <?php echo nl2br(htmlspecialchars($issue['issue_description'])); ?>
            </div>
        </div>
    </div>
    
    <!-- System Information Card -->
    <div class="info-card">
        <div class="card-header">
            <h5><i class="fas fa-desktop"></i> System Information</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">Browser:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['browser_info'] ?: 'Not captured'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Operating System:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['os_info'] ?: 'Not captured'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Screen Resolution:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['screen_resolution'] ?: 'Not captured'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">IP Address:</span>
                <span class="info-value"><?php echo htmlspecialchars($issue['ip_address'] ?: 'Not captured'); ?></span>
            </div>
        </div>
    </div>
    
    <?php if ($issue['admin_notes']): ?>
    <!-- Admin Notes Card -->
    <div class="info-card">
        <div class="card-header">
            <h5><i class="fas fa-sticky-note"></i> Admin Notes</h5>
        </div>
        <div class="card-body">
            <div class="notes-box">
                <?php echo nl2br(htmlspecialchars($issue['admin_notes'])); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Update Form Card -->
    <div class="info-card">
        <div class="card-header">
            <h5><i class="fas fa-edit"></i> Update Status</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="TechnicalIssues.php">
                <input type="hidden" name="issue_id" value="<?php echo $issue['issue_id']; ?>">
                <input type="hidden" name="update_status" value="1">
                
                <div class="form-group">
                    <label><strong>Status</strong></label>
                    <select name="status" class="form-control" required>
                        <option value="pending" <?php echo $issue['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="resolved" <?php echo $issue['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $issue['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><strong>Admin Notes</strong></label>
                    <textarea name="admin_notes" class="form-control" rows="4" placeholder="Add notes about resolution or actions taken..."><?php echo htmlspecialchars($issue['admin_notes']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Update Issue
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .info-card {
        background: white;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .info-card .card-header {
        background: linear-gradient(135deg, #003366 0%, #004080 100%);
        color: #ffffff;
        padding: 1rem 1.5rem;
        border-bottom: 3px solid #d4af37;
    }
    
    .info-card .card-header h5 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #ffffff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    .info-card .card-header h5 i {
        color: #d4af37;
        font-size: 1.3rem;
    }
    
    .info-card .card-body {
        padding: 1.5rem;
    }
    
    .info-row {
        display: flex;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #003366;
        min-width: 180px;
        flex-shrink: 0;
    }
    
    .info-value {
        color: #333;
        flex: 1;
    }
    
    .description-box, .notes-box {
        background: #f8f9fa;
        padding: 1.25rem;
        border-radius: 8px;
        border-left: 4px solid #003366;
        line-height: 1.6;
        color: #333;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .notes-box {
        border-left-color: #17a2b8;
        background: #e7f5f8;
    }
    
    .badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .badge-priority-critical { 
        background: #dc3545; 
        color: white; 
    }
    
    .badge-priority-high { 
        background: #fd7e14; 
        color: white; 
    }
    
    .badge-priority-medium { 
        background: #ffc107; 
        color: #000; 
    }
    
    .badge-priority-low { 
        background: #28a745; 
        color: white; 
    }
    
    .badge-status-pending { 
        background: #6c757d; 
        color: white; 
    }
    
    .badge-status-resolved { 
        background: #28a745; 
        color: white; 
    }
    
    .badge-status-closed { 
        background: #17a2b8; 
        color: white; 
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #003366;
        font-weight: 600;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #003366;
    }
    
    .btn-block {
        width: 100%;
        padding: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #003366 0%, #004080 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
    }
</style>
