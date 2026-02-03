<?php
require_once(__DIR__ . "/../utils/session_manager.php");
SessionManager::startSession('Student');

if(!isset($_SESSION['ID'])){
    header("Location:../student-login.php");
    exit();
}

if(!isset($_SESSION['UserType']) || $_SESSION['UserType'] !== 'Student'){
    SessionManager::destroySession();
    header("Location: ../auth/student-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php");
require_once('../utils/NotificationSystem.php');

$notificationSystem = new NotificationSystem($con);
$userId = $_SESSION['Id'];
$userType = 'student';

// Handle mark as read
if(isset($_GET['mark_read']) && isset($_GET['id'])) {
    $notificationSystem->markAsRead($_GET['id']);
    header("Location: Notifications.php");
    exit();
}

// Handle mark all as read
if(isset($_GET['mark_all_read'])) {
    $notificationSystem->markAllAsRead($userId, $userType);
    header("Location: Notifications.php");
    exit();
}

// Get all notifications
$allNotifications = $con->query("SELECT * FROM notifications WHERE user_id = '$userId' AND user_type = '$userType' ORDER BY created_at DESC LIMIT 50");
$unreadCount = $notificationSystem->getUnreadCount($userId, $userType);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Portal</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/student-modern.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .notification-item {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
            cursor: pointer;
        }
        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .notification-item.unread {
            background: rgba(0, 123, 255, 0.05);
            border-left-color: var(--primary-color);
            border-left-width: 6px;
        }
        .notification-item.read {
            opacity: 0.7;
            border-left-color: var(--border-color);
        }
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: var(--primary-color);
            color: white;
        }
        .notification-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-exam { background: rgba(0, 123, 255, 0.1); color: var(--primary-color); }
        .badge-result { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }
        .badge-alert { background: rgba(255, 193, 7, 0.1); color: var(--warning-color); }
        .badge-system { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="padding: 2rem 1rem; max-width: 1200px; margin: 0 auto;">
        <div class="page-header" style="margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--primary-color); margin-bottom: 0.5rem;">
                🔔 Notifications
            </h1>
            <p style="color: var(--text-secondary);">Stay updated with your exam schedule and results</p>
        </div>

        <!-- Notification Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; padding: 1.5rem; border-radius: var(--radius-lg); text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                    <?php echo $unreadCount; ?>
                </div>
                <div style="opacity: 0.9;">Unread Notifications</div>
            </div>
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--success-color); margin-bottom: 0.5rem;">
                    <?php echo $allNotifications ? $allNotifications->num_rows : 0; ?>
                </div>
                <div style="color: var(--text-secondary);">Total Notifications</div>
            </div>
        </div>

        <!-- Actions -->
        <?php if($unreadCount > 0): ?>
        <div style="margin-bottom: 2rem;">
            <a href="?mark_all_read" class="btn btn-primary" onclick="return confirm('Mark all notifications as read?')">
                ✓ Mark All as Read
            </a>
        </div>
        <?php endif; ?>

        <!-- Notifications List -->
        <div>
            <?php if($allNotifications && $allNotifications->num_rows > 0): ?>
            <?php while($notification = $allNotifications->fetch_assoc()): ?>
            <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" 
                 onclick="window.location='<?php echo $notification['link'] ? $notification['link'] : '?mark_read&id=' . $notification['notification_id']; ?>'">
                <div style="display: flex; gap: 1.5rem; align-items: start;">
                    <div class="notification-icon" style="background: <?php 
                        echo $notification['notification_type'] == 'exam_scheduled' ? 'var(--primary-color)' : 
                             ($notification['notification_type'] == 'results_ready' ? 'var(--success-color)' : 
                             ($notification['notification_type'] == 'system_alert' ? 'var(--warning-color)' : '#6c757d')); 
                    ?>;">
                        <?php 
                        echo $notification['notification_type'] == 'exam_scheduled' ? '📅' : 
                             ($notification['notification_type'] == 'results_ready' ? '📊' : 
                             ($notification['notification_type'] == 'system_alert' ? '⚠️' : '🔔')); 
                        ?>
                    </div>
                    
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">
                                <?php echo $notification['title']; ?>
                            </h3>
                            <span class="notification-badge badge-<?php 
                                echo $notification['notification_type'] == 'exam_scheduled' ? 'exam' : 
                                     ($notification['notification_type'] == 'results_ready' ? 'result' : 
                                     ($notification['notification_type'] == 'system_alert' ? 'alert' : 'system')); 
                            ?>">
                                <?php echo str_replace('_', ' ', $notification['notification_type']); ?>
                            </span>
                        </div>
                        
                        <p style="margin: 0 0 0.75rem 0; color: var(--text-secondary); line-height: 1.6;">
                            <?php echo $notification['message']; ?>
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?php 
                                $time = strtotime($notification['created_at']);
                                $diff = time() - $time;
                                if($diff < 60) echo 'Just now';
                                elseif($diff < 3600) echo floor($diff/60) . ' minutes ago';
                                elseif($diff < 86400) echo floor($diff/3600) . ' hours ago';
                                else echo date('M d, Y', $time);
                                ?>
                            </div>
                            
                            <?php if(!$notification['is_read']): ?>
                            <a href="?mark_read&id=<?php echo $notification['notification_id']; ?>" 
                               class="btn btn-sm btn-primary" 
                               onclick="event.stopPropagation();">
                                Mark as Read
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div style="text-align: center; padding: 4rem; background: white; border-radius: var(--radius-lg); box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🔔</div>
                <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">No Notifications Yet</h3>
                <p style="color: var(--text-secondary);">You'll see notifications here when exams are scheduled or results are ready.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Notification Settings -->
        <div style="margin-top: 3rem; background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1rem 0; color: var(--primary-color);">⚙️ Notification Preferences</h3>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                Manage how you receive notifications about exams and results.
            </p>
            
            <div style="display: grid; gap: 1rem;">
                <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); cursor: pointer;">
                    <input type="checkbox" checked style="width: 20px; height: 20px;">
                    <div>
                        <strong>Exam Scheduled</strong>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Get notified when new exams are scheduled</div>
                    </div>
                </label>
                
                <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); cursor: pointer;">
                    <input type="checkbox" checked style="width: 20px; height: 20px;">
                    <div>
                        <strong>Results Ready</strong>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Get notified when exam results are available</div>
                    </div>
                </label>
                
                <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--bg-light); border-radius: var(--radius-md); cursor: pointer;">
                    <input type="checkbox" checked style="width: 20px; height: 20px;">
                    <div>
                        <strong>System Alerts</strong>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Important system announcements and updates</div>
                    </div>
                </label>
            </div>
            
            <div style="margin-top: 1.5rem;">
                <button class="btn btn-primary">Save Preferences</button>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 60 seconds
        setTimeout(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
<?php $con->close(); ?>
