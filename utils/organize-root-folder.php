<?php
// Script to organize OES root folder into proper subdirectories

echo "<!DOCTYPE html>
<html>
<head>
    <title>OES Root Folder Organization</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #1a2b4a; border-bottom: 3px solid #d4af37; padding-bottom: 10px; }
        h3 { color: #1a2b4a; margin-top: 30px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .summary { background: #e7f3ff; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #1a2b4a; }
        .btn { display: inline-block; padding: 12px 24px; background: #1a2b4a; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        ul { list-style: none; padding: 0; }
        li { padding: 5px 0; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h2>📁 OES Root Folder Organization</h2>";
echo "<p>Organizing files into proper subdirectories...</p>";

// Create subdirectories if they don't exist
$directories = [
    'docs' => 'Documentation files',
    'utils' => 'Utility and setup scripts',
    'auth' => 'Authentication and login files'
];

echo "<h3>📂 Creating Directories:</h3>";
echo "<ul>";

foreach ($directories as $dir => $description) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<li class='success'>✅ Created: $dir/ ($description)</li>";
        } else {
            echo "<li class='error'>❌ Failed to create: $dir/</li>";
        }
    } else {
        echo "<li class='info'>ℹ️ Already exists: $dir/</li>";
    }
}

echo "</ul>";

// Define file movements
$fileMovements = [
    // Documentation files -> docs/
    'BEFORE_AFTER.html' => 'docs/BEFORE_AFTER.html',
    'DEPLOYMENT_SUMMARY.md' => 'docs/DEPLOYMENT_SUMMARY.md',
    'IMPLEMENTATION_GUIDE.md' => 'docs/IMPLEMENTATION_GUIDE.md',
    'MODERNIZATION_README.md' => 'docs/MODERNIZATION_README.md',
    'QUICK_REFERENCE.md' => 'docs/QUICK_REFERENCE.md',
    'README_FIRST.html' => 'docs/README_FIRST.html',
    'START_HERE.html' => 'docs/START_HERE.html',
    'COMPREHENSIVE_CLEANUP_PLAN.md' => 'docs/COMPREHENSIVE_CLEANUP_PLAN.md',
    'ORGANIZATION_PLAN.md' => 'docs/ORGANIZATION_PLAN.md',
    'Student/CLEANUP_PLAN.md' => 'docs/STUDENT_CLEANUP_PLAN.md',
    
    // Utility scripts -> utils/
    'create-test-schedule.php' => 'utils/create-test-schedule.php',
    'update-schedule-table.php' => 'utils/update-schedule-table.php',
    'cleanup-student-folder.php' => 'utils/cleanup-student-folder.php',
    'cleanup-entire-project.php' => 'utils/cleanup-entire-project.php',
    'organize-root-folder.php' => 'utils/organize-root-folder.php',
    'quick-reset.php' => 'utils/quick-reset.php',
    'reset-database.php' => 'utils/reset-database.php',
    'reset-database.sql' => 'utils/reset-database.sql',
    
    // Auth files -> auth/
    'login.php' => 'auth/login.php',
    'Logout.php' => 'auth/Logout.php',
    'forgot-password.php' => 'auth/forgot-password.php',
    'forgot-password-process.php' => 'auth/forgot-password-process.php',
    'institute-login.php' => 'auth/institute-login.php',
    'institute-login-process.php' => 'auth/institute-login-process.php',
];

echo "<h3>📦 Moving Files:</h3>";
echo "<ul>";

$movedFiles = 0;
$failedFiles = 0;
$notFoundFiles = 0;

foreach ($fileMovements as $source => $destination) {
    if (file_exists($source)) {
        // Create destination directory if it doesn't exist
        $destDir = dirname($destination);
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }
        
        if (rename($source, $destination)) {
            echo "<li class='success'>✅ Moved: $source → $destination</li>";
            $movedFiles++;
        } else {
            echo "<li class='error'>❌ Failed to move: $source</li>";
            $failedFiles++;
        }
    } else {
        echo "<li class='info'>ℹ️ Not found: $source</li>";
        $notFoundFiles++;
    }
}

echo "</ul>";

echo "<div class='summary'>";
echo "<h3>📊 Organization Summary:</h3>";
echo "<p><strong>Files moved:</strong> $movedFiles</p>";
echo "<p><strong>Files failed:</strong> $failedFiles</p>";
echo "<p><strong>Files not found:</strong> $notFoundFiles</p>";
echo "<hr>";
echo "<h4>New Folder Structure:</h4>";
echo "<ul>";
echo "<li><strong>docs/</strong> - All documentation and guides</li>";
echo "<li><strong>utils/</strong> - Utility scripts and database tools</li>";
echo "<li><strong>auth/</strong> - Login and authentication files</li>";
echo "<li><strong>assets/</strong> - CSS, JS, and other assets (already exists)</li>";
echo "<li><strong>images/</strong> - Images and logos (already exists)</li>";
echo "<li><strong>Student/</strong> - Student portal files</li>";
echo "<li><strong>Admin/</strong> - Admin portal files</li>";
echo "<li><strong>Instructor/</strong> - Instructor portal files</li>";
echo "<li><strong>ExamCommittee/</strong> - Exam committee portal files</li>";
echo "</ul>";
echo "</div>";

if ($failedFiles == 0) {
    echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>✅ Organization completed successfully!</p>";
    echo "<p>Your root folder is now clean and organized.</p>";
} else {
    echo "<p style='color: orange; font-weight: bold; font-size: 1.2em;'>⚠️ Organization completed with some errors.</p>";
}

// Create index.html files in new directories to prevent directory listing
$indexContent = "<!DOCTYPE html><html><head><title>Access Denied</title></head><body><h1>403 - Access Denied</h1></body></html>";

foreach (['docs', 'utils', 'auth'] as $dir) {
    if (file_exists($dir) && !file_exists("$dir/index.html")) {
        file_put_contents("$dir/index.html", $indexContent);
    }
}

echo "<p class='info'>ℹ️ Added index.html files to prevent directory listing</p>";

echo "<hr>";
echo "<h3>📌 Important Notes:</h3>";
echo "<ul>";
echo "<li>✅ This script has moved itself to <strong>utils/organize-root-folder.php</strong></li>";
echo "<li>✅ All utility scripts are now in <strong>utils/</strong> folder</li>";
echo "<li>✅ All documentation is now in <strong>docs/</strong> folder</li>";
echo "<li>✅ All auth files are now in <strong>auth/</strong> folder</li>";
echo "<li>⚠️ Update any bookmarks or links to point to new locations</li>";
echo "</ul>";

echo "<h4>Quick Access Links:</h4>";
echo "<ul>";
echo "<li><a href='utils/create-test-schedule.php'>Create Test Schedule</a></li>";
echo "<li><a href='utils/update-schedule-table.php'>UPDATE exam_schedules Table</a></li>";
echo "<li><a href='docs/ORGANIZATION_PLAN.md'>View Organization Plan</a></li>";
echo "</ul>";

echo "<a href='index.php' class='btn'>Go to Home Page</a>";
echo "<a href='Student/index.php' class='btn' style='margin-left: 10px;'>Go to Student Dashboard</a>";

echo "</div></body></html>";
?>
