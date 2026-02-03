<?php
/**
 * Test page to verify session isolation is working
 * This page shows all active sessions for different user types
 */
require_once(__DIR__ . "/utils/session_manager.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Isolation Test</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .header p {
            margin: 0;
            color: #666;
        }
        
        .sessions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .session-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .session-card h3 {
            margin: 0 0 1rem 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .session-card.active {
            border: 3px solid #28a745;
        }
        
        .session-card.inactive {
            opacity: 0.6;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .session-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .session-info p {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            color: #555;
        }
        
        .session-info strong {
            color: #333;
        }
        
        .actions {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            margin: 0.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Session Isolation Test</h1>
            <p>This page shows all active sessions for different user types. Each user type has its own isolated session.</p>
        </div>
        
        <div class="sessions-grid">
            <?php
            $userTypes = [
                'Student' => ['icon' => '👨‍🎓', 'session' => SessionManager::SESSION_STUDENT],
                'Instructor' => ['icon' => '👨‍🏫', 'session' => SessionManager::SESSION_INSTRUCTOR],
                'Administrator' => ['icon' => '👨‍💼', 'session' => SessionManager::SESSION_ADMIN],
                'DepartmentHead' => ['icon' => '👔', 'session' => SessionManager::SESSION_DEPT_HEAD]
            ];
            
            foreach ($userTypes as $type => $info) {
                // Check if this user type has an active session
                $originalSessionName = session_name();
                session_write_close();
                session_name($info['session']);
                session_start();
                
                $isActive = isset($_SESSION['ID']) && isset($_SESSION['UserType']);
                $userName = $_SESSION['Name'] ?? 'Not logged in';
                $userId = $_SESSION['ID'] ?? 'N/A';
                $sessionId = session_id();
                
                session_write_close();
                session_name($originalSessionName);
                session_start();
                
                $cardClass = $isActive ? 'active' : 'inactive';
                $statusClass = $isActive ? 'status-active' : 'status-inactive';
                $statusText = $isActive ? 'Active' : 'Inactive';
                ?>
                
                <div class="session-card <?php echo $cardClass; ?>">
                    <h3>
                        <span><?php echo $info['icon']; ?></span>
                        <?php echo $type; ?>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                    </h3>
                    
                    <?php if ($isActive): ?>
                        <div class="session-info">
                            <p><strong>User:</strong> <?php echo htmlspecialchars($userName); ?></p>
                            <p><strong>ID:</strong> <?php echo htmlspecialchars($userId); ?></p>
                            <p><strong>Session ID:</strong> <?php echo substr($sessionId, 0, 16); ?>...</p>
                        </div>
                    <?php else: ?>
                        <p style="color: #999; margin-top: 1rem;">No active session</p>
                    <?php endif; ?>
                </div>
            <?php } ?>
        </div>
        
        <div class="actions">
            <h3 style="margin-top: 0;">Quick Actions</h3>
            <a href="auth/student-login.php" class="btn btn-primary">Student Login</a>
            <a href="auth/institute-login.php" class="btn btn-primary">Institute Login</a>
            <a href="javascript:location.reload()" class="btn btn-success">Refresh Status</a>
        </div>
    </div>
</body>
</html>
