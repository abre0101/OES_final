<?php
if (!isset($_SESSION)) {
    session_start();
}

if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
$pageTitle = "Bulk Import Students";
$deptId = $_SESSION['DeptId'] ?? null;

$message = '';
$messageType = '';
$imported_count = 0;
$error_count = 0;
$errors = [];

// Function to generate next student code
function generateNextStudentCode($con) {
    $query = "SELECT student_code FROM students WHERE student_code LIKE 'STU%' ORDER BY student_code DESC LIMIT 1";
    $result = $con->query($query);
    
    if($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastCode = $row['student_code'];
        // Extract number from STU004 format
        $number = intval(substr($lastCode, 3));
        $nextNumber = $number + 1;
        return 'STU' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    } else {
        // First student
        return 'STU001';
    }
}

// Handle CSV upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if($file['error'] == 0 && $file['type'] == 'text/csv') {
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // Skip header row
        
        while(($data = fgetcsv($handle)) !== FALSE) {
            // Skip empty rows
            if(empty(array_filter($data))) continue;
            
            if(count($data) >= 5) {
                // Auto-generate student code
                $student_code = generateNextStudentCode($con);
                
                // CSV columns: Full Name, Email, Phone, Username, Password, Academic Year (optional)
                $full_name = mysqli_real_escape_string($con, trim($data[0]));
                $email = mysqli_real_escape_string($con, trim($data[1]));
                $phone = mysqli_real_escape_string($con, trim($data[2]));
                $username = mysqli_real_escape_string($con, trim($data[3]));
                $password = password_hash(trim($data[4]), PASSWORD_DEFAULT);
                $academic_year = mysqli_real_escape_string($con, trim($data[5] ?? 'Year 1'));
                
                // Validate required fields
                if(empty($full_name) || empty($username)) {
                    $error_count++;
                    $errors[] = "Row skipped: Missing required fields (Full Name or Username)";
                    continue;
                }
                
                // Check if username already exists
                $check = $con->prepare("SELECT student_id FROM students WHERE username = ?");
                $check->bind_param("s", $username);
                $check->execute();
                
                if($check->get_result()->num_rows == 0) {
                    $insert = $con->prepare("INSERT INTO students (student_code, username, password, full_name, email, phone, department_id, academic_year, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
                    $insert->bind_param("ssssssss", $student_code, $username, $password, $full_name, $email, $phone, $deptId, $academic_year);
                    
                    if($insert->execute()) {
                        $imported_count++;
                    } else {
                        $error_count++;
                        $errors[] = "Error importing $full_name: " . $con->error;
                    }
                } else {
                    $error_count++;
                    $errors[] = "Username '$username' already exists";
                }
            } else {
                $error_count++;
                $errors[] = "Row skipped: Insufficient columns (minimum 5 required)";
            }
        }
        fclose($handle);
        
        $message = "Import completed! $imported_count students imported, $error_count errors.";
        $messageType = $error_count > 0 ? 'warning' : 'success';
    } else {
        $message = "Please upload a valid CSV file.";
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Import Students - Department Head</title>
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
                <h1 style="font-size: 2rem; margin: 0 0 0.35rem 0; color: var(--primary-color); font-weight: 700;">Bulk Import Students</h1>
                <p style="margin: 0; color: var(--text-secondary); font-size: 1.05rem;">
                    Import multiple students at once using CSV file
                </p>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : ($messageType == 'warning' ? 'warning' : 'danger'); ?>">
                <?php echo $message; ?>
                <?php if(!empty($errors)): ?>
                <details style="margin-top: 1rem;">
                    <summary style="cursor: pointer; font-weight: 600;">View Errors (<?php echo count($errors); ?>)</summary>
                    <ul style="margin-top: 0.5rem;">
                        <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>Upload CSV File</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Select CSV File *</label>
                                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                                    <small style="color: #6c757d;">Maximum file size: 5MB</small>
                                </div>
                                <button type="submit" class="btn btn-primary">📤 Upload and Import</button>
                                <a href="Students.php" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3>CSV Format Instructions</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Required Columns (in order):</strong></p>
                            <ol>
                                <li><strong>Full Name</strong> (required)</li>
                                <li><strong>Email</strong></li>
                                <li><strong>Phone</strong></li>
                                <li><strong>Username</strong> (required)</li>
                                <li><strong>Password</strong> (required)</li>
                                <li><strong>Academic Year</strong> (optional, defaults to Year 1)</li>
                            </ol>
                            
                            <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #ffc107;">
                                <strong>📌 Note:</strong> Student codes will be <strong>auto-generated</strong> (STU001, STU002, etc.). Do not include student codes in your CSV file.
                            </div>
                            
                            <p style="margin-top: 1rem;"><strong>Example CSV:</strong></p>
                            <pre style="background: #f8f9fa; padding: 1rem; border-radius: 8px; font-size: 0.85rem; overflow-x: auto;">full_name,email,phone,username,password,academic_year
Abebe Kebede,abebe@example.com,+251911234567,abebe.kebede,pass123,Year 1
Tigist Alemu,tigist@example.com,+251911234568,tigist.alemu,pass123,Year 2
Dawit Haile,dawit@example.com,+251911234569,dawit.haile,pass123,Year 1</pre>

                            <a href="data:text/csv;charset=utf-8,full_name,email,phone,username,password,academic_year%0AAbebe Kebede,abebe@example.com,+251911234567,abebe.kebede,pass123,Year 1%0ATigist Alemu,tigist@example.com,+251911234568,tigist.alemu,pass123,Year 2" 
                               download="student_import_template.csv" 
                               class="btn btn-sm btn-info">
                                📥 Download Template
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
