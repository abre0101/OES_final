<?php
require_once(__DIR__ . "/../utils/session_manager.php");

// Start Administrator session
SessionManager::startSession('Administrator');

// Check if user is logged in
if(!isset($_SESSION['username'])){
    header("Location: ../auth/staff-login.php");
    exit();
}

// Validate user role
if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Administrator'){
    SessionManager::destroySession();
    header("Location: ../auth/staff-login.php");
    exit();
}

require_once(__DIR__ . "/../utils/password_helper.php");

$con = require_once(__DIR__ . "/../Connections/OES.php");
$message = '';
$messageType = '';
$importResults = [];

// Handle CSV upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $userType = $_POST['import_type'];
    $file = $_FILES['csv_file'];
    
    if($file['error'] == 0 && $file['size'] <= 5242880) { // 5MB limit
        $filename = $file['tmp_name'];
        $handle = fopen($filename, 'r');
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $rowNumber = 1;
        
        while(($data = fgetcsv($handle)) !== FALSE) {
            $rowNumber++;
            try {
                switch($userType) {
                    case 'student':
                        // Expected: student_code, username, password, full_name, email, phone, gender, department_id, academic_year, semester
                        if(count($data) >= 10) {
                            // Hash the password before storing
                            $hashedPassword = hashPassword($data[2]);
                            $stmt = $con->prepare("INSERT INTO students (student_code, username, password, full_name, email, phone, gender, department_id, academic_year, semester, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                            $stmt->bind_param("sssssssssi", $data[0], $data[1], $hashedPassword, $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9]);
                            if($stmt->execute()) {
                                $successCount++;
                            } else {
                                $errorCount++;
                                $errors[] = "Row {$rowNumber}: {$data[0]} - " . $stmt->error;
                            }
                        } else {
                            $errorCount++;
                            $errors[] = "Row {$rowNumber}: Insufficient columns (expected 10, got " . count($data) . ")";
                        }
                        break;
                        
                    case 'instructor':
                        // Expected: instructor_code, username, password, full_name, email, phone, gender, department_id
                        if(count($data) >= 8) {
                            // Hash the password before storing
                            $hashedPassword = hashPassword($data[2]);
                            $stmt = $con->prepare("INSERT INTO instructors (instructor_code, username, password, full_name, email, phone, gender, department_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
                            $stmt->bind_param("sssssssi", $data[0], $data[1], $hashedPassword, $data[3], $data[4], $data[5], $data[6], $data[7]);
                            if($stmt->execute()) {
                                $successCount++;
                            } else {
                                $errorCount++;
                                $errors[] = "Row {$rowNumber}: {$data[0]} - " . $stmt->error;
                            }
                        } else {
                            $errorCount++;
                            $errors[] = "Row {$rowNumber}: Insufficient columns (expected 8, got " . count($data) . ")";
                        }
                        break;
                        
                    case 'department_head':
                        // Expected: head_code, username, password, full_name, email, phone, department_id
                        if(count($data) >= 7) {
                            // Hash the password before storing
                            $hashedPassword = hashPassword($data[2]);
                            $stmt = $con->prepare("INSERT INTO department_heads (head_code, username, password, full_name, email, phone, department_id, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                            $stmt->bind_param("ssssssi", $data[0], $data[1], $hashedPassword, $data[3], $data[4], $data[5], $data[6]);
                            if($stmt->execute()) {
                                $successCount++;
                            } else {
                                $errorCount++;
                                $errors[] = "Row {$rowNumber}: {$data[0]} - " . $stmt->error;
                            }
                        } else {
                            $errorCount++;
                            $errors[] = "Row {$rowNumber}: Insufficient columns (expected 7, got " . count($data) . ")";
                        }
                        break;
                }
            } catch(Exception $e) {
                $errorCount++;
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        $importResults = [
            'success' => $successCount,
            'errors' => $errorCount,
            'error_details' => $errors
        ];
        
        if($successCount > 0) {
            $message = "✅ Successfully imported {$successCount} user(s)!";
            $messageType = 'success';
        }
        if($errorCount > 0) {
            $message .= " ⚠️ {$errorCount} error(s) occurred.";
            $messageType = $successCount > 0 ? 'warning' : 'danger';
        }
    } else {
        $message = '❌ Error: File too large or upload failed. Maximum size is 5MB.';
        $messageType = 'danger';
    }
}

// Get departments for reference
$departments = $con->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
$deptList = [];
while($dept = $departments->fetch_assoc()) {
    $deptList[] = $dept;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Import - Admin Dashboard</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .page-header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 2rem;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.05) 100%);
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 2px solid rgba(59, 130, 246, 0.1);
        }
        
        .page-title-section h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .page-title-section h1 span {
            -webkit-text-fill-color: initial;
            background: none;
        }
        
        .page-title-section p {
            margin: 0;
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 500;
        }
        
        .upload-zone {
            border: 3px dashed var(--border-color);
            border-radius: var(--radius-lg);
            padding: 3rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: var(--bg-light);
        }
        .upload-zone:hover {
            border-color: var(--primary-color);
            background: rgba(59, 130, 246, 0.05);
            transform: translateY(-2px);
        }
        .upload-zone.dragover {
            border-color: var(--success-color);
            background: rgba(40, 167, 69, 0.1);
            border-style: solid;
        }
        .upload-zone.has-file {
            border-color: var(--success-color);
            background: rgba(40, 167, 69, 0.05);
        }
        .template-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .template-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .template-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .stats-card {
            text-align: center;
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 2px solid;
        }
        .stats-card.success {
            background: rgba(40, 167, 69, 0.1);
            border-color: var(--success-color);
        }
        .stats-card.error {
            background: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
        }
        .dept-reference {
            max-height: 300px;
            overflow-y: auto;
            background: var(--bg-light);
            padding: 1rem;
            border-radius: var(--radius-md);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Bulk Import';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>📥</span> Bulk User Import</h1>
                    <p>Import multiple users at once using CSV files</p>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1.25rem; border-radius: var(--radius-lg); border-left: 4px solid;">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <!-- Import Results -->
            <?php if(!empty($importResults)): ?>
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">📊 Import Results</h3>
                </div>
                <div style="padding: 2rem;">
                    <div class="grid grid-2" style="gap: 2rem; margin-bottom: 2rem;">
                        <div class="stats-card success">
                            <div style="font-size: 3.5rem; font-weight: 900; color: var(--success-color); margin-bottom: 0.5rem;">
                                <?php echo $importResults['success']; ?>
                            </div>
                            <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-secondary);">
                                ✓ Successfully Imported
                            </div>
                        </div>
                        <div class="stats-card error">
                            <div style="font-size: 3.5rem; font-weight: 900; color: #dc3545; margin-bottom: 0.5rem;">
                                <?php echo $importResults['errors']; ?>
                            </div>
                            <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-secondary);">
                                ✗ Errors Encountered
                            </div>
                        </div>
                    </div>
                    
                    <?php if(!empty($importResults['error_details'])): ?>
                    <details style="margin-top: 1.5rem;">
                        <summary style="cursor: pointer; font-weight: 700; color: var(--primary-color); padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md);">
                            🔍 View Error Details (<?php echo count($importResults['error_details']); ?> errors)
                        </summary>
                        <div style="margin-top: 1rem; padding: 1.5rem; background: #fff3cd; border-radius: var(--radius-md); border-left: 4px solid #ffc107; max-height: 400px; overflow-y: auto;">
                            <?php foreach($importResults['error_details'] as $error): ?>
                            <div style="padding: 0.75rem; margin-bottom: 0.5rem; background: white; border-radius: 6px; border-left: 3px solid #dc3545;">
                                <strong style="color: #dc3545;">⚠</strong> <?php echo htmlspecialchars($error); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </details>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-2" style="gap: 2rem;">
                <!-- Upload Form -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📤 Upload CSV File</h3>
                    </div>
                    <div style="padding: 2rem;">
                        <form method="POST" enctype="multipart/form-data" id="uploadForm">
                            <div class="form-group">
                                <label style="font-weight: 700; margin-bottom: 0.5rem; display: block;">Select Import Type *</label>
                                <select name="import_type" id="importType" class="form-control" required onchange="updateTemplate()">
                                    <option value="">-- Select User Type --</option>
                                    <option value="student">👨‍🎓 Students</option>
                                    <option value="instructor">👨‍🏫 Instructors</option>
                                    <option value="department_head">👔 Department Heads</option>
                                </select>
                                <small style="color: var(--text-secondary); margin-top: 0.5rem; display: block;">Choose the type of users you want to import</small>
                            </div>

                            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('csvFile').click()">
                                <div style="font-size: 4rem; margin-bottom: 1rem;">📁</div>
                                <h3 style="margin-bottom: 0.5rem; font-weight: 700;">Drop CSV file here or click to browse</h3>
                                <p style="color: var(--text-secondary); margin-bottom: 0.5rem;">Supported format: .csv</p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem;">Maximum file size: 5MB</p>
                                <input type="file" name="csv_file" id="csvFile" accept=".csv" required style="display: none;" onchange="handleFileSelect(event)">
                                <div id="fileName" style="font-weight: 700; color: var(--success-color); margin-top: 1rem; font-size: 1.1rem;"></div>
                            </div>

                            <div style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.1) 100%); padding: 1.5rem; border-radius: var(--radius-md); margin: 1.5rem 0; border-left: 4px solid var(--primary-color);">
                                <strong style="color: var(--primary-color); display: block; margin-bottom: 0.75rem;">📋 CSV Format Requirements:</strong>
                                <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-secondary); line-height: 1.8;">
                                    <li>First row must contain column headers</li>
                                    <li>Use comma (,) as field delimiter</li>
                                    <li>Ensure all required fields are present</li>
                                    <li>Download template for correct format</li>
                                    <li>Check department IDs in reference table</li>
                                </ul>
                            </div>

                            <div class="form-actions" style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary" id="submitBtn" disabled style="flex: 1;">
                                    📥 Import Users
                                </button>
                                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Templates & Reference -->
                <div>
                    <!-- CSV Templates -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3 class="card-title">📄 Download CSV Templates</h3>
                        </div>
                        <div style="padding: 2rem;">
                            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                                Download the appropriate template, fill in your data, and upload it above.
                            </p>

                            <div id="studentTemplate" class="template-card" style="display: none; margin-bottom: 1rem;" onclick="downloadTemplate('student')">
                                <div class="template-icon">👨‍🎓</div>
                                <h4 style="margin: 0 0 0.5rem 0; font-weight: 700;">Student Template</h4>
                                <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.9rem;">
                                    student_code, username, password, full_name, email, phone, gender, department_id, academic_year, semester
                                </p>
                                <button class="btn btn-primary btn-sm">⬇️ Download Template</button>
                            </div>

                            <div id="instructorTemplate" class="template-card" style="display: none; margin-bottom: 1rem;" onclick="downloadTemplate('instructor')">
                                <div class="template-icon">👨‍🏫</div>
                                <h4 style="margin: 0 0 0.5rem 0; font-weight: 700;">Instructor Template</h4>
                                <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.9rem;">
                                    instructor_code, username, password, full_name, email, phone, gender, department_id
                                </p>
                                <button class="btn btn-primary btn-sm">⬇️ Download Template</button>
                            </div>

                            <div id="departmentHeadTemplate" class="template-card" style="display: none; margin-bottom: 1rem;" onclick="downloadTemplate('department_head')">
                                <div class="template-icon">👔</div>
                                <h4 style="margin: 0 0 0.5rem 0; font-weight: 700;">Department Head Template</h4>
                                <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.9rem;">
                                    head_code, username, password, full_name, email, phone, department_id
                                </p>
                                <button class="btn btn-primary btn-sm">⬇️ Download Template</button>
                            </div>

                            <div id="noTemplate" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                                <p style="margin: 0; font-weight: 600;">Select an import type to view templates</p>
                            </div>
                        </div>
                    </div>

                    <!-- Department Reference -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">🏢 Department Reference</h3>
                        </div>
                        <div style="padding: 2rem;">
                            <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                                Use these department IDs in your CSV file:
                            </p>
                            <div class="dept-reference">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: var(--primary-color); color: white;">
                                            <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid var(--border-color);">ID</th>
                                            <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid var(--border-color);">Department Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($deptList as $dept): ?>
                                        <tr style="border-bottom: 1px solid var(--border-color);">
                                            <td style="padding: 0.75rem; font-weight: 700; color: var(--primary-color);"><?php echo $dept['department_id']; ?></td>
                                            <td style="padding: 0.75rem;"><?php echo $dept['department_name']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">📚 Import Instructions</h3>
                </div>
                <div style="padding: 2rem;">
                    <div class="grid grid-3" style="gap: 2rem;">
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="background: var(--primary-color); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">1</span>
                                Prepare Your Data
                            </h4>
                            <ul style="color: var(--text-secondary); line-height: 1.8; padding-left: 1.5rem;">
                                <li>Download the appropriate CSV template</li>
                                <li>Fill in all required fields</li>
                                <li>Use correct department IDs from reference table</li>
                                <li>Ensure data format matches template</li>
                            </ul>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="background: var(--primary-color); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">2</span>
                                Upload & Import
                            </h4>
                            <ul style="color: var(--text-secondary); line-height: 1.8; padding-left: 1.5rem;">
                                <li>Select the user type to import</li>
                                <li>Upload your prepared CSV file</li>
                                <li>Review file details before submitting</li>
                                <li>Click "Import Users" to process</li>
                            </ul>
                        </div>
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span style="background: var(--primary-color); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">3</span>
                                Review Results
                            </h4>
                            <ul style="color: var(--text-secondary); line-height: 1.8; padding-left: 1.5rem;">
                                <li>Check import success count</li>
                                <li>Review any error messages</li>
                                <li>Fix errors and re-import if needed</li>
                                <li>Verify imported users in respective sections</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
    <script>
        const uploadZone = document.getElementById('uploadZone');
        const csvFile = document.getElementById('csvFile');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');
        const importType = document.getElementById('importType');

        // Drag and drop handlers
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if(files.length > 0 && files[0].name.endsWith('.csv')) {
                csvFile.files = files;
                handleFileSelect({ target: { files: files } });
            } else {
                alert('⚠️ Please drop a valid CSV file');
            }
        });

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if(file) {
                if(file.size > 5242880) {
                    alert('❌ File too large! Maximum size is 5MB');
                    csvFile.value = '';
                    return;
                }
                
                fileName.textContent = `✓ Selected: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
                uploadZone.classList.add('has-file');
                
                if(importType.value) {
                    submitBtn.disabled = false;
                }
            }
        }

        function updateTemplate() {
            const type = importType.value;
            
            // Hide all templates
            document.getElementById('studentTemplate').style.display = 'none';
            document.getElementById('instructorTemplate').style.display = 'none';
            document.getElementById('departmentHeadTemplate').style.display = 'none';
            document.getElementById('noTemplate').style.display = 'none';
            
            // Show selected template
            if(type === 'student') {
                document.getElementById('studentTemplate').style.display = 'block';
            } else if(type === 'instructor') {
                document.getElementById('instructorTemplate').style.display = 'block';
            } else if(type === 'department_head') {
                document.getElementById('departmentHeadTemplate').style.display = 'block';
            } else {
                document.getElementById('noTemplate').style.display = 'block';
            }
            
            // Enable submit if file is selected
            if(csvFile.files.length > 0) {
                submitBtn.disabled = false;
            }
        }

        function downloadTemplate(type) {
            let csv = '';
            let filename = '';
            
            switch(type) {
                case 'student':
                    csv = 'student_code,username,password,full_name,email,phone,gender,department_id,academic_year,semester\n';
                    csv += 'STU001,abebe.kebede,pass123,Abebe Kebede,abebe.k@student.dmu.edu.et,+251911234567,Male,1,Year 1,1\n';
                    csv += 'STU002,tigist.hailu,pass123,Tigist Hailu,tigist.h@student.dmu.edu.et,+251911234568,Female,1,Year 1,1';
                    filename = 'student_import_template.csv';
                    break;
                case 'instructor':
                    csv = 'instructor_code,username,password,full_name,email,phone,gender,department_id\n';
                    csv += 'INS001,dr.solomon,pass123,Dr. Solomon Tesfaye,solomon.t@dmu.edu.et,+251911234567,Male,1\n';
                    csv += 'INS002,dr.marta,pass123,Dr. Marta Gebre,marta.g@dmu.edu.et,+251911234568,Female,2';
                    filename = 'instructor_import_template.csv';
                    break;
                case 'department_head':
                    csv = 'head_code,username,password,full_name,email,phone,department_id\n';
                    csv += 'DH001,yohannes.m,pass123,Dr. Yohannes Mengistu,yohannes.m@dmu.edu.et,+251911234567,1\n';
                    csv += 'DH002,rahel.w,pass123,Dr. Rahel Worku,rahel.w@dmu.edu.et,+251911234568,2';
                    filename = 'department_head_import_template.csv';
                    break;
            }
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function resetForm() {
            fileName.textContent = '';
            uploadZone.classList.remove('has-file');
            submitBtn.disabled = true;
            updateTemplate();
        }

        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            if(!importType.value) {
                e.preventDefault();
                alert('⚠️ Please select an import type');
                return false;
            }
            
            if(!csvFile.files.length) {
                e.preventDefault();
                alert('⚠️ Please select a CSV file');
                return false;
            }
            
            return confirm('📥 Are you sure you want to import these users?\n\nThis action will add new users to the system.');
        });
    </script>
</body>
</html>
