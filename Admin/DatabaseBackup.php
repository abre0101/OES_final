<?php
session_start();
if(!isset($_SESSION['username'])){
    header("Location:../index.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
$message = '';
$messageType = '';

// Handle backup request
if(isset($_POST['backup'])) {
    $backupDir = '../backups/';
    
    // Create backups directory if it doesn't exist
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0777, true);
    }
    
    $filename = 'oes_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backupDir . $filename;
    
    // Get all tables
    $tables = array();
    $result = $con->query("SHOW TABLES");
    while($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    $sqlScript = "-- OES Database Backup\n";
    $sqlScript .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $sqlScript .= "-- Database: oes\n\n";
    $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    // Loop through tables
    foreach($tables as $table) {
        $sqlScript .= "-- Table: $table\n";
        $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Get table structure
        $result = $con->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_row();
        $sqlScript .= $row[1] . ";\n\n";
        
        // Get table data
        $result = $con->query("SELECT * FROM `$table`");
        $columnCount = $result->field_count;
        
        if($result->num_rows > 0) {
            $sqlScript .= "INSERT INTO `$table` VALUES\n";
            $rowCount = 0;
            while($row = $result->fetch_row()) {
                $sqlScript .= "(";
                for($j=0; $j<$columnCount; $j++) {
                    $row[$j] = $row[$j] ?? 'NULL';
                    if(isset($row[$j])) {
                        $row[$j] = $con->real_escape_string($row[$j]);
                        $row[$j] = '"' . $row[$j] . '"';
                    }
                    if($j < ($columnCount-1)) {
                        $sqlScript .= $row[$j] . ',';
                    } else {
                        $sqlScript .= $row[$j];
                    }
                }
                $rowCount++;
                if($rowCount < $result->num_rows) {
                    $sqlScript .= "),\n";
                } else {
                    $sqlScript .= ");\n\n";
                }
            }
        }
    }
    
    $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Save to file
    if(file_put_contents($filepath, $sqlScript)) {
        $message = "Database backup created successfully: $filename";
        $messageType = 'success';
    } else {
        $message = "Failed to create backup file";
        $messageType = 'error';
    }
}

// Handle restore request
if(isset($_POST['restore']) && isset($_FILES['backup_file'])) {
    $file = $_FILES['backup_file'];
    
    if($file['error'] == 0) {
        $sqlScript = file_get_contents($file['tmp_name']);
        
        // Execute SQL script
        $con->multi_query($sqlScript);
        
        // Wait for all queries to complete
        do {
            if ($result = $con->store_result()) {
                $result->free();
            }
        } while ($con->more_results() && $con->next_result());
        
        $message = "Database restored successfully from: " . $file['name'];
        $messageType = 'success';
    } else {
        $message = "Error uploading file";
        $messageType = 'error';
    }
}

// Handle delete backup
if(isset($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $filepath = '../backups/' . $filename;
    
    if(file_exists($filepath) && unlink($filepath)) {
        $message = "Backup deleted successfully";
        $messageType = 'success';
    } else {
        $message = "Failed to delete backup";
        $messageType = 'error';
    }
}

// Get list of existing backups
$backups = array();
$backupDir = '../backups/';
if(file_exists($backupDir)) {
    $files = scandir($backupDir);
    foreach($files as $file) {
        if($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $backups[] = array(
                'name' => $file,
                'size' => filesize($backupDir . $file),
                'date' => filemtime($backupDir . $file)
            );
        }
    }
    // Sort by date, newest first
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup & Restore - Admin</title>
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
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Database Backup';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <!-- Page Header -->
            <div class="page-header-actions">
                <div class="page-title-section">
                    <h1><span>💾</span> Database Backup & Restore</h1>
                    <p>Manage database backups and restore points</p>
                </div>
            </div>

            <?php if($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem; padding: 1.25rem; border-radius: var(--radius-lg); background: <?php echo $messageType == 'success' ? 'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)'; ?>; border-left: 4px solid <?php echo $messageType == 'success' ? 'var(--success-color)' : '#dc3545'; ?>;">
                <strong><?php echo $messageType == 'success' ? '✓' : '✗'; ?></strong> <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-2">
                <!-- Create Backup -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📦 Create New Backup</h3>
                    </div>
                    <div style="padding: 2rem;">
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                            Create a complete backup of the entire database. This includes all tables, data, and structure.
                        </p>
                        
                        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                            <h4 style="margin: 0 0 1rem 0; color: var(--primary-color);">Backup Information</h4>
                            <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-secondary);">
                                <li>Includes all database tables</li>
                                <li>Preserves data and structure</li>
                                <li>Saved in SQL format</li>
                                <li>Can be restored anytime</li>
                            </ul>
                        </div>
                        
                        <form method="POST" onsubmit="return confirm('Create database backup now?');">
                            <button type="submit" name="backup" class="btn btn-primary btn-block">
                                💾 Create Backup Now
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Restore Backup -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">♻️ Restore from Backup</h3>
                    </div>
                    <div style="padding: 2rem;">
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                            Restore database from a backup file. This will replace all current data.
                        </p>
                        
                        <div style="background: rgba(220, 53, 69, 0.1); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border-left: 4px solid #dc3545;">
                            <h4 style="margin: 0 0 1rem 0; color: #dc3545;">⚠️ Warning</h4>
                            <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-secondary);">
                                <li>This will replace ALL current data</li>
                                <li>Create a backup before restoring</li>
                                <li>Cannot be undone</li>
                                <li>System will be unavailable during restore</li>
                            </ul>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('⚠️ WARNING: This will replace all current data. Are you sure?');">
                            <div class="form-group">
                                <label>Select Backup File (.sql)</label>
                                <input type="file" name="backup_file" accept=".sql" class="form-control" required>
                            </div>
                            <button type="submit" name="restore" class="btn btn-danger btn-block">
                                ♻️ Restore Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Existing Backups -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">📂 Existing Backups</h3>
                </div>
                <div class="table-container">
                    <?php if(count($backups) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Backup File</th>
                                <th>Date Created</th>
                                <th>File Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($backups as $backup): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($backup['name']); ?></strong>
                                </td>
                                <td><?php echo date('F j, Y - g:i A', $backup['date']); ?></td>
                                <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                                <td>
                                    <a href="../backups/<?php echo $backup['name']; ?>" download class="btn btn-primary btn-sm">
                                        ⬇️ Download
                                    </a>
                                    <a href="?delete=<?php echo $backup['name']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this backup?');">
                                        🗑️ Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div style="text-align: center; padding: 4rem 2rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📂</div>
                        <h3 style="color: var(--text-secondary);">No Backups Found</h3>
                        <p>Create your first backup to get started</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Best Practices -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">💡 Backup Best Practices</h3>
                </div>
                <div style="padding: 2rem;">
                    <div class="grid grid-2">
                        <div>
                            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">✅ Do's</h4>
                            <ul style="color: var(--text-secondary); line-height: 1.8;">
                                <li>Create backups regularly (daily/weekly)</li>
                                <li>Test restore process periodically</li>
                                <li>Store backups in multiple locations</li>
                                <li>Keep at least 3 recent backups</li>
                                <li>Document backup procedures</li>
                            </ul>
                        </div>
                        <div>
                            <h4 style="color: #dc3545; margin-bottom: 1rem;">❌ Don'ts</h4>
                            <ul style="color: var(--text-secondary); line-height: 1.8;">
                                <li>Don't rely on a single backup</li>
                                <li>Don't restore without testing first</li>
                                <li>Don't delete old backups immediately</li>
                                <li>Don't skip backup verification</li>
                                <li>Don't forget to backup before updates</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
