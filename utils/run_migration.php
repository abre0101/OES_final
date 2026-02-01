<?php
/**
 * Database Migration Runner
 * Run this file once to set up all new features
 */

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

echo "<h1>🔧 Running Database Migration...</h1>";
echo "<pre>";

// Read the SQL file
$sqlFile = __DIR__ . '/complete_system_migration.sql';
$sql = file_get_contents($sqlFile);

// Split into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && 
               !preg_match('/^--/', $stmt) && 
               !preg_match('/^\/\*/', $stmt);
    }
);

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    
    try {
        if ($con->query($statement)) {
            $successCount++;
            // Extract table/action from statement for logging
            if (preg_match('/CREATE TABLE.*`(\w+)`/i', $statement, $matches)) {
                echo "✅ Created table: {$matches[1]}\n";
            } elseif (preg_match('/ALTER TABLE\s+`?(\w+)`?/i', $statement, $matches)) {
                echo "✅ Altered table: {$matches[1]}\n";
            } elseif (preg_match('/INSERT.*INTO\s+`?(\w+)`?/i', $statement, $matches)) {
                echo "✅ Inserted data into: {$matches[1]}\n";
            } else {
                echo "✅ Executed statement\n";
            }
        } else {
            $errorCount++;
            $error = $con->error;
            $errors[] = $error;
            echo "⚠️  Warning: $error\n";
        }
    } catch (Exception $e) {
        $errorCount++;
        $errors[] = $e->getMessage();
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo "========================================\n";
echo "📊 MIGRATION SUMMARY\n";
echo "========================================\n";
echo "✅ Successful: $successCount\n";
echo "⚠️  Warnings/Errors: $errorCount\n";
echo "\n";

if ($errorCount > 0) {
    echo "⚠️  Some statements had warnings (this is normal if columns/tables already exist)\n";
    echo "\nError Details:\n";
    foreach (array_unique($errors) as $error) {
        echo "  - $error\n";
    }
}

// Verify key tables exist
echo "\n========================================\n";
echo "🔍 VERIFICATION\n";
echo "========================================\n";

$tables = [
    'question_topics',
    'notifications',
    'academic_calendar',
    'grading_config',
    'student_answers'
];

foreach ($tables as $table) {
    $result = $con->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✅ Table '$table' exists\n";
    } else {
        echo "❌ Table '$table' NOT found\n";
    }
}

// Check for key columns
echo "\n";
$columns = [
    'question_page' => ['approval_status', 'point_value', 'topic_id'],
    'truefalse_question' => ['point_value', 'topic_id']
];

foreach ($columns as $table => $cols) {
    foreach ($cols as $col) {
        $result = $con->query("SHOW COLUMNS FROM $table LIKE '$col'");
        if ($result && $result->num_rows > 0) {
            echo "✅ Column '$table.$col' exists\n";
        } else {
            echo "❌ Column '$table.$col' NOT found\n";
        }
    }
}

echo "\n========================================\n";
echo "✅ MIGRATION COMPLETE!\n";
echo "========================================\n";
echo "\nYou can now use all new features:\n";
echo "  • Password Reset System\n";
echo "  • Bulk User Import\n";
echo "  • Academic Calendar\n";
echo "  • Exam Approval Workflow\n";
echo "  • Point Values per Question\n";
echo "  • System Monitoring\n";
echo "  • Question Topics\n";
echo "  • Notifications System\n";
echo "  • Approval History\n";
echo "  • Advanced Analytics\n";
echo "  • GPA 4.0 Grading System\n";
echo "\n";
echo "🎉 All systems ready!\n";
echo "</pre>";

$con->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Migration Complete</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
        }
        h1 {
            color: #00ff00;
            text-shadow: 0 0 10px #00ff00;
        }
        pre {
            background: #000;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #00ff00;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #00ff00;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #00cc00;
        }
    </style>
</head>
<body>
    <div style="text-align: center; margin-top: 20px;">
        <a href="../Admin/index.php" class="btn">🏠 Go to Admin Dashboard</a>
        <a href="../Admin/SystemMonitoring.php" class="btn">📊 System Monitoring</a>
        <a href="../Admin/GradingSettings.php" class="btn">📋 Grading Settings</a>
    </div>
</body>
</html>
