<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Manage Students";

// Get department ID from session
$deptId = $_SESSION['DeptId'] ?? null;

// Get all students in this department
$students_query = "SELECT s.*, d.department_name 
                   FROM students s 
                   LEFT JOIN departments d ON s.department_id = d.department_id 
                   WHERE s.department_id = ? 
                   ORDER BY s.academic_year ASC, s.student_id DESC";
$stmt = $con->prepare($students_query);
$stmt->bind_param("i", $deptId);
$stmt->execute();
$students = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Department Head</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>
    
    <div class="admin-main-content">
        <?php include 'header-component.php'; ?>
        
        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Manage Departmental Students</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    View and manage students in <?php echo $_SESSION['Dept']; ?> Department
                </p>
            </div>

            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Students List</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="BulkImportStudents.php" class="btn btn-info">
                            📤 Bulk Import
                        </a>
                        <a href="RegisterStudent.php" class="btn btn-primary">
                            ➕ Register New Student
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($students->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Academic Year</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($student = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_code']); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo $student['academic_year'] ? 'Year ' . htmlspecialchars($student['academic_year']) : 'N/A'; ?></td>
                                    <td>
                                        <?php if($student['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="ViewStudent.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-info">View</a>
                                        <a href="EditStudent.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <p>No students found in your department. <a href="RegisterStudent.php">Register a new student</a></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
