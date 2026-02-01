<?php
// Cleanup script for Student folder - removes old/unused files

$filesToDelete = [
    // Old non-modern versions
    'Student/EditProfile.php',
    'Student/EditProfile1.php',
    'Student/index.php',
    'Student/Profile.php',
    'Student/Profile1.php',
    'Student/Result.php',
    'Student/StartExam.php',
    
    // Debug/test files
    'Student/check-schedule.php',
    'Student/debug-exams.php',
    'Student/Logout - Copy.php',
    
    // Old exam files
    'Student/Display.php',
    'Student/Exam.php',
    'Student/Question.php',
    'Student/TrueFalse.php',
    
    // Old CSS files
    'Student/style.css',
    'Student/style1.css',
    'Student/style-new.css',
];

$foldersToDelete = [
    'Student/SpryAssets',
    'Student/_notes',
];

echo "<h2>🧹 Student Folder Cleanup</h2>";
echo "<h3>Deleting Old Files:</h3>";

$deletedFiles = 0;
$failedFiles = 0;

foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "✅ Deleted: $file<br>";
            $deletedFiles++;
        } else {
            echo "❌ Failed to delete: $file<br>";
            $failedFiles++;
        }
    } else {
        echo "⚠️ Not found: $file<br>";
    }
}

echo "<h3>Deleting Old Folders:</h3>";

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

foreach ($foldersToDelete as $folder) {
    if (file_exists($folder)) {
        if (deleteDirectory($folder)) {
            echo "✅ Deleted folder: $folder<br>";
            $deletedFolders++;
        } else {
            echo "❌ Failed to delete folder: $folder<br>";
            $failedFolders++;
        }
    } else {
        echo "⚠️ Folder not found: $folder<br>";
    }
}

echo "<hr>";
echo "<h3>📊 Summary:</h3>";
echo "<p><strong>Files deleted:</strong> $deletedFiles</p>";
echo "<p><strong>Files failed:</strong> $failedFiles</p>";
echo "<p><strong>Folders deleted:</strong> $deletedFolders</p>";
echo "<p><strong>Folders failed:</strong> $failedFolders</p>";

if ($failedFiles == 0 && $failedFolders == 0) {
    echo "<p style='color: green; font-weight: bold;'>✅ Cleanup completed successfully!</p>";
    echo "<p><a href='Student/index.php' style='padding: 10px 20px; background: #1a2b4a; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Student Dashboard</a></p>";
} else {
    echo "<p style='color: orange; font-weight: bold;'>⚠️ Cleanup completed with some errors.</p>";
}
?>
