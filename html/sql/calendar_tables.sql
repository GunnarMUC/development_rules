-- Calendar tables for events and event attendees
USE vibe_templates;

-- Events table for calendar appointments and meetings
CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id INT UNSIGNED NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    all_day BOOLEAN DEFAULT FALSE,
    color VARCHAR(7) DEFAULT '#3788d8',  -- Hex color for display
    type ENUM('event', 'meeting', 'appointment', 'reminder') DEFAULT 'event',
    status ENUM('scheduled', 'cancelled', 'completed') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_team_id (team_id),
    INDEX idx_created_by (created_by),
    INDEX idx_start_datetime (start_datetime),
    INDEX idx_end_datetime (end_datetime),
    INDEX idx_status (status),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event attendees table for managing participants
CREATE TABLE IF NOT EXISTS event_attendees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    response_status ENUM('pending', 'accepted', 'declined', 'tentative') DEFAULT 'pending',
    is_organizer BOOLEAN DEFAULT FALSE,
    notes TEXT,
    responded_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_event_user (event_id, user_id),
    INDEX idx_event_id (event_id),
    INDEX idx_user_id (user_id),
    INDEX idx_response_status (response_status),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;