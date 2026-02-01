<?php
session_start();
if(!isset($_SESSION['Name'])){
    header("Location:../auth/institute-login.php");
    exit();
}

$con = require_once(__DIR__ . "/../Connections/OES.php"); // Auto-fixed connection;
require_once('../utils/NotificationSystem.php');

$notificationSystem = new NotificationSystem($con);
$userId = $_SESSION['instructor_id'];
$userType = 'instructor';

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
    <title>Notifications - Instructor</title>
    <link href="../assets/css/modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-modern-v2.css" rel="stylesheet">
    <link href="../assets/css/admin-sidebar.css" rel="stylesheet">
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
            border-left-width: 6px;
        }
        .notification-item.read {
            opacity: 0.7;
            border-left-color: var(--border-color);
        }
    </style>
</head>
<body class="admin-layout">
    <?php include 'sidebar-component.php'; ?>

    <div class="admin-main-content">
        <?php 
        $pageTitle = 'Notifications';
        include 'header-component.php'; 
        ?>

        <div class="admin-content">
            <div class="page-header">
                <h1>🔔 Notifications</h1>
                <p>Stay updated with question approvals and system alerts</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-2" style="margin-bottom: 2rem;">
                <div style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; padding: 2rem; border-radius: var(--radius-lg); text-align: center;">
                    <div style="font-size: 3rem; font-weight: 800; margin-bottom: 0.5rem;">
                        <?php echo $unreadCount; ?>
                    </div>
                    <div style="opacity: 0.9; font-size: 1.1rem;">Unread Notifications</div>
                </div>
                <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <div style="font-size: 3rem; font-weight: 800; color: var(--success-color); margin-bottom: 0.5rem;">
                        <?php echo $allNotifications ? $allNotifications->num_rows : 0; ?>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 1.1rem;">Total Notifications</div>
                </div>
            </div>

            <!-- Actions -->
            <?php if($unreadCount > 0): ?>
            <div style="margin-bottom: 2rem;">
                <a href="?mark_all_read" class="btn btn-primary" onclick="return confirm('Mark all as read?')">
                    ✓ Mark All as Read
                </a>
            </div>
            <?php endif; ?>

            <!-- Notifications -->
            <div>
                <?php if($allNotifications && $allNotifications->num_rows > 0): ?>
                <?php while($notification = $allNotifications->fetch_assoc()): ?>
                <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>" 
                     onclick="window.location='<?php echo $notification['link'] ? $notification['link'] : '?mark_read&id=' . $notification['notification_id']; ?>'">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">
                                <?php echo $notification['title']; ?>
                            </h3>
                            <p style="margin: 0 0 0.75rem 0; color: var(--text-secondary);">
                                <?php echo $notification['message']; ?>
                            </p>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?php 
                                $time = strtotime($notification['created_at']);
                                $diff = time() - $time;
                                if($diff < 60) echo 'Just now';
                                elseif($diff < 3600) echo floor($diff/60) . ' minutes ago';
                                elseif($diff < 86400) echo floor($diff/3600) . ' hours ago';
                                else echo date('M d, Y H:i', $time);
                                ?>
                            </div>
                        </div>
                        <?php if(!$notification['is_read']): ?>
                        <span style="padding: 0.5rem 1rem; background: var(--primary-color); color: white; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                            NEW
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div style="text-align: center; padding: 4rem; background: white; border-radius: var(--radius-lg);">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🔔</div>
                    <h3>No Notifications</h3>
                    <p style="color: var(--text-secondary);">You'll see notifications here when questions are approved or need revision.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin-sidebar.js"></script>
</body>
</html>
<?php $con->close(); ?>
