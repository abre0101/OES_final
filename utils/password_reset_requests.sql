-- Create password reset requests table
CREATE TABLE IF NOT EXISTS `password_reset_requests` (
    `request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(100) NOT NULL,
    `user_type` ENUM('student', 'instructor', 'exam_committee') NOT NULL,
    `user_name` VARCHAR(200) NOT NULL,
    `user_email` VARCHAR(100),
    `reason` TEXT,
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `processed_by` VARCHAR(100),
    `processed_date` DATETIME,
    `notes` TEXT,
    INDEX `idx_status` (`status`),
    INDEX `idx_user` (`user_id`, `user_type`),
    INDEX `idx_date` (`request_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
