USE vibe_templates;

-- Create teams table
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create team_members table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('member', 'admin') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_team_member (team_id, user_id),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Check if team_id column exists before adding
SELECT COUNT(*) INTO @col_exists
FROM information_schema.columns
WHERE table_schema = 'vibe_templates'
AND table_name = 'tasks'
AND column_name = 'team_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE tasks ADD COLUMN team_id INT DEFAULT NULL',
    'SELECT "Column team_id already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for team_id if not exists
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'vibe_templates'
    AND TABLE_NAME = 'tasks'
    AND CONSTRAINT_NAME = 'tasks_ibfk_team');

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE tasks ADD CONSTRAINT tasks_ibfk_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE',
    'SELECT "Foreign key for team_id already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if assigned_to column exists before adding
SELECT COUNT(*) INTO @col_exists2
FROM information_schema.columns
WHERE table_schema = 'vibe_templates'
AND table_name = 'tasks'
AND column_name = 'assigned_to';

SET @sql = IF(@col_exists2 = 0,
    'ALTER TABLE tasks ADD COLUMN assigned_to INT DEFAULT NULL',
    'SELECT "Column assigned_to already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key for assigned_to if not exists
SET @fk_exists2 = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'vibe_templates'
    AND TABLE_NAME = 'tasks'
    AND CONSTRAINT_NAME = 'tasks_ibfk_assigned');

SET @sql = IF(@fk_exists2 = 0,
    'ALTER TABLE tasks ADD CONSTRAINT tasks_ibfk_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT "Foreign key for assigned_to already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;