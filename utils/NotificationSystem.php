<?php
/**
 * Notification System
 * Handles all notification creation and management
 */
class NotificationSystem {
    private $con;
    
    public function __construct($connection) {
        $this->con = $connection;
        $this->ensureTableExists();
    }
    
    private function ensureTableExists() {
        // Create notifications table if it doesn't exist
        $this->con->query("CREATE TABLE IF NOT EXISTS notifications (
            notification_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_type ENUM('admin', 'instructor', 'student', 'committee') NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
            related_type ENUM('exam', 'result', 'announcement', 'approval') NULL,
            related_id INT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at DATETIME NULL,
            INDEX idx_user (user_id, user_type),
            INDEX idx_read (is_read),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB");
    }
    
    /**
     * Send notification to user
     */
    public function send($userId, $userType, $title, $message, $type = 'info', $relatedType = null, $relatedId = null) {
        $stmt = $this->con->prepare("INSERT INTO notifications 
            (user_id, user_type, title, message, type, related_type, related_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $userId, $userType, $title, $message, $type, $relatedType, $relatedId);
        return $stmt->execute();
    }
    
    /**
     * Notify exam committee about new exam submission
     */
    public function notifyCommitteeNewExam($scheduleId, $examName, $courseName, $instructorName) {
        // Get all active committee members
        $committee = $this->con->query("SELECT committee_id FROM exam_committee WHERE is_active = TRUE");
        
        $title = "New Exam Submitted for Approval";
        $message = "Instructor {$instructorName} has submitted exam '{$examName}' for course '{$courseName}' for your review.";
        
        while($member = $committee->fetch_assoc()) {
            $this->send($member['committee_id'], 'committee', $title, $message, 'info', 'approval', $scheduleId);
        }
    }
    
    /**
     * Notify instructor about exam approval
     */
    public function notifyInstructorApproval($instructorId, $scheduleId, $examName, $status, $comments = '') {
        $titles = [
            'approved' => "Exam Approved ✓",
            'revision' => "Exam Needs Revision",
            'rejected' => "Exam Rejected"
        ];
        
        $messages = [
            'approved' => "Your exam '{$examName}' has been approved and is now available to students.",
            'revision' => "Your exam '{$examName}' requires revision. Comments: {$comments}",
            'rejected' => "Your exam '{$examName}' has been rejected. Reason: {$comments}"
        ];
        
        $types = [
            'approved' => 'success',
            'revision' => 'warning',
            'rejected' => 'danger'
        ];
        
        $this->send($instructorId, 'instructor', $titles[$status], $messages[$status], $types[$status], 'approval', $scheduleId);
    }
    
    /**
     * Get unread notifications for user
     */
    public function getUnread($userId, $userType) {
        $stmt = $this->con->prepare("SELECT * FROM notifications 
            WHERE user_id = ? AND user_type = ? AND is_read = FALSE 
            ORDER BY created_at DESC");
        $stmt->bind_param("is", $userId, $userType);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount($userId, $userType) {
        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM notifications 
            WHERE user_id = ? AND user_type = ? AND is_read = FALSE");
        $stmt->bind_param("is", $userId, $userType);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        $stmt = $this->con->prepare("UPDATE notifications 
            SET is_read = TRUE, read_at = NOW() 
            WHERE notification_id = ?");
        $stmt->bind_param("i", $notificationId);
        return $stmt->execute();
    }
    
    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId, $userType) {
        $stmt = $this->con->prepare("UPDATE notifications 
            SET is_read = TRUE, read_at = NOW() 
            WHERE user_id = ? AND user_type = ? AND is_read = FALSE");
        $stmt->bind_param("is", $userId, $userType);
        return $stmt->execute();
    }
}
