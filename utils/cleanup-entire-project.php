<?php
// Comprehensive cleanup script for entire OES project

$filesToDelete = [
    // ROOT - Old non-modern versions
    'AboutUs.php',
    'Help.php',
    'index.php',
    'Shedule.php',
    
    // ROOT - Old CSS
    'style.css',
    'style1.css',
    
    // STUDENT - Old non-modern versions
    'Student/EditProfile.php',
    'Student/EditProfile1.php',
    'Student/index.php',
    'Student/Profile.php',
    'Student/Profile1.php',
    'Student/Result.php',
    'Student/StartExam.php',
    
    // STUDENT - Debug/test files
    'Student/check-schedule.php',
    'Student/debug-exams.php',
    'Student/Logout - Copy.php',
    
    // STUDENT - Old exam files
    'Student/Display.php',
    'Student/Exam.php',
    'Student/Question.php',
    'Student/TrueFalse.php',
    
    // STUDENT - Old CSS
    'Student/style.css',
    'Student/style1.css',
    'Student/style-new.css',
];

$foldersToDelete = [
    // ROOT - Old framework folders
    '_mmServerScripts',
    '_notes',
    'SpryAssets',
    'nbproject',
    'Connections',
    
    // STUDENT - Old folders
    'Student/SpryAssets',
    'Student/_notes',
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>OES Comprehensive Cleanup</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        h2 { color: #1a2b4a; border-bottom: 3px solid #d4af37; padding-bottom: 15px; margin-bottom: 30px; font-size: 2em; }
        h3 { color: #1a2b4a; margin-top: 30px; font-size: 1.5em; }
        .success { color: #28a745; padding: 5px 0; }
        .error { color: #dc3545; padding: 5px 0; }
        .warning { color: #ffc107; padding: 5px 0; }
        .summary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px; margin: 30px 0; }
        .summary h3 { color: white; border-bottom: 2px solid rgba(255,255,255,0.3); padding-bottom: 10px; }
        .btn { display: inline-block; padding: 15px 30px; background: #1a2b4a; color: white; text-decoration: none; border-radius: 8px; margin: 10px 10px 0 0; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #2c3e5a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        ul { list-style: none; padding: 0; }
        li { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        li:last-child { border-bottom: none; }
        .icon { font-size: 1.2em; margin-right: 10px; }
        .stat-box { display: inline-block; background: white; padding: 15px 25px; border-radius: 8px; margin: 10px 10px 0 0; }
        .stat-number { font-size: 2em; font-weight: bold; color: #1a2b4a; }
        .stat-label { font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h2>🧹 OES Comprehensive Cleanup</h2>";
echo "<p style='font-size: 1.1em; color: #666;'>Removing old, unused, and duplicate files from the entire project...</p>";

echo "<h3>📁 Deleting Old Files:</h3>";
echo "<ul>";

$deletedFiles = 0;
$failedFiles = 0;
$notFoundFiles = 0;

foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<li class='success'><span class='icon'>✅</span> Deleted: <strong>$file</strong></li>";
            $deletedFiles++;
        } else {
            echo "<li class='error'><span class='icon'>❌</span> Failed to delete: <strong>$file</strong></li>";
            $failedFiles++;
        }
    } else {
        echo "<li class='warning'><span class='icon'>⚠️</span> Not found: $file</li>";
        $notFoundFiles++;
    }
}

echo "</ul>";

echo "<h3>📂 Deleting Old Folders:</h3>";
echo "<ul>";

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return false;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}

$deletedFolders = 0;
$failedFolders = 0;
$notFoundFolders = 0;

foreach ($foldersToDelete as $folder) {
    if (file_exists($folder)) {
        if (deleteDirectory($folder)) {
            echo "<li class='success'><span class='icon'>✅</span> Deleted folder: <strong>$folder</strong></li>";
            $deletedFolders++;
        } else {
            echo "<li class='error'><span class='icon'>❌</span> Failed to delete folder: <strong>$folder</strong></li>";
            $failedFolders++;
        }
    } else {
        echo "<li class='warning'><span class='icon'>⚠️</span> Folder not found: $folder</li>";
        $notFoundFolders++;
    }
}

echo "</ul>";

echo "<div class='summary'>";
echo "<h3>📊 Cleanup Summary</h3>";
echo "<div style='margin-top: 20px;'>";
echo "<div class='stat-box'><div class='stat-number'>$deletedFiles</div><div class='stat-label'>Files Deleted</div></div>";
echo "<div class='stat-box'><div class='stat-number'>$deletedFolders</div><div class='stat-label'>Folders Deleted</div></div>";
echo "<div class='stat-box'><div class='stat-number'>$failedFiles</div><div class='stat-label'>Failed</div></div>";
echo "</div>";
echo "</div>";

if ($failedFiles == 0 && $failedFolders == 0) {
    echo "<div style='background: #d4edda; border: 2px solid #28a745; color: #155724; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>✅ Cleanup Completed Successfully!</h3>";
    echo "<p style='font-size: 1.1em; margin-bottom: 0;'>Your project is now clean and organized. All old files have been removed.</p>";
    echo "</div>";
    echo "<p style='font-size: 1.1em;'><strong>Next Step:</strong> Run the organization script to organize remaining files into folders.</p>";
    echo "<a href='organize-root-folder.php' class='btn' style='background: #28a745;'>▶️ Organize Files Now</a>";
} else {
    echo "<div style='background: #fff3cd; border: 2px solid #ffc107; color: #856404; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3 style='color: #856404; margin-top: 0;'>⚠️ Cleanup Completed with Errors</h3>";
    echo "<p style='margin-bottom: 0;'>Some files or folders could not be deleted. Check file permissions.</p>";
    echo "</div>";
}

echo "<a href='index.php' class='btn'>🏠 Go to Home</a>";
echo "<a href='Student/index.php' class='btn'>👨‍🎓 Student Dashboard</a>";
echo "<a href='Admin/index.php' class='btn'>👨‍💼 Admin Dashboard</a>";

echo "</div></body></html>";
?>
