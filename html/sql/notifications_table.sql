-- Notifications table for the SaaS template
-- Stores user notifications for various events

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    team_id INT DEFAULT NULL,
    type ENUM('task_assigned', 'task_completed', 'task_updated', 'team_invite', 'mention', 'reminder', 'system') DEFAULT 'system',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(500) DEFAULT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,

    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    -- Indexes for performance
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_team (team_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;