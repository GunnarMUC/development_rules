-- Use the database
USE vibe_templates;

-- Create activities table if not exists
CREATE TABLE IF NOT EXISTS activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id INT UNSIGNED,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add assigned_to column to tasks table if not exists
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS assigned_to INT UNSIGNED DEFAULT NULL;
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS team_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE tasks ADD COLUMN IF NOT EXISTS project VARCHAR(100) DEFAULT NULL;

-- Clear existing sample data (optional - comment out if you want to keep existing data)
-- DELETE FROM activities;
-- DELETE FROM tasks WHERE user_id IN (1, 2, 3, 4, 5);

-- Insert sample users if they don't exist
INSERT IGNORE INTO users (id, email, password, first_name, last_name, username, role, status) VALUES
(1, 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'johndoe', 'admin', 'active'),
(2, 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'janesmith', 'user', 'active'),
(3, 'bob.wilson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob', 'Wilson', 'bobwilson', 'user', 'active'),
(4, 'alice.brown@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Brown', 'alicebrown', 'user', 'active'),
(5, 'charlie.davis@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Charlie', 'Davis', 'charliedavis', 'user', 'active');

-- Insert sample teams if teams table exists
INSERT IGNORE INTO teams (id, name, description, created_by)
SELECT 1, 'Development Team', 'Main development team', 1
WHERE EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'teams')
UNION ALL
SELECT 2, 'Marketing Team', 'Marketing and sales team', 2
WHERE EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'teams');

-- Insert team members if team_members table exists
INSERT IGNORE INTO team_members (team_id, user_id, role)
SELECT * FROM (
    SELECT 1, 1, 'leader' UNION ALL
    SELECT 1, 2, 'member' UNION ALL
    SELECT 1, 3, 'member' UNION ALL
    SELECT 2, 2, 'leader' UNION ALL
    SELECT 2, 4, 'member' UNION ALL
    SELECT 2, 5, 'member'
) AS tm
WHERE EXISTS (SELECT * FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'team_members');

-- Insert sample tasks with various dates for trend analysis
-- Tasks from 30 days ago to today
INSERT INTO tasks (user_id, assigned_to, title, description, status, priority, due_date, completed_at, created_at, project) VALUES
-- Completed tasks (historical data for trends)
(1, 1, 'Setup database schema', 'Create initial database schema for the project', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 24 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY), 'Backend'),
(1, 2, 'Design login page', 'Create responsive login page design', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 22 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY), DATE_SUB(NOW(), INTERVAL 28 DAY), 'Frontend'),
(2, 3, 'Implement authentication', 'Add JWT authentication system', 'completed', 'critical', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 19 DAY), DATE_SUB(NOW(), INTERVAL 27 DAY), 'Backend'),
(2, 2, 'Create dashboard layout', 'Design main dashboard components', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 18 DAY), DATE_SUB(NOW(), INTERVAL 17 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY), 'Frontend'),
(3, 4, 'Write API documentation', 'Document all API endpoints', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 23 DAY), 'Documentation'),
(1, 1, 'Setup CI/CD pipeline', 'Configure automated deployment', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 11 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), 'DevOps'),
(3, 5, 'Create user management', 'CRUD operations for users', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY), 'Backend'),
(4, 3, 'Design landing page', 'Create marketing landing page', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), 'Marketing'),
(5, 4, 'Implement search feature', 'Add search functionality', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY), 'Frontend'),
(2, 2, 'Fix login bug', 'Resolve session timeout issue', 'completed', 'critical', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY), 'Backend'),

-- In-progress tasks
(1, 1, 'Complete authentication system', 'Implement full authentication flow with 2FA', 'in_progress', 'high', DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, DATE_SUB(NOW(), INTERVAL 5 DAY), 'Backend'),
(2, 3, 'Optimize database queries', 'Improve query performance', 'in_progress', 'high', DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL, DATE_SUB(NOW(), INTERVAL 4 DAY), 'Backend'),
(3, 2, 'Create notification system', 'Real-time notifications', 'in_progress', 'medium', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), 'Frontend'),
(4, 5, 'Mobile responsive design', 'Make all pages mobile-friendly', 'in_progress', 'high', DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, DATE_SUB(NOW(), INTERVAL 6 DAY), 'Frontend'),
(5, 4, 'Write test cases', 'Unit tests for core modules', 'in_progress', 'medium', DATE_ADD(CURDATE(), INTERVAL 4 DAY), NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), 'Testing'),

-- Pending tasks
(1, 2, 'Design dashboard layout', 'Create modern dashboard UI', 'pending', 'medium', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), 'Frontend'),
(2, 1, 'Implement payment gateway', 'Integrate Stripe payments', 'pending', 'critical', DATE_ADD(CURDATE(), INTERVAL 10 DAY), NULL, NOW(), 'Backend'),
(3, 3, 'Create reporting module', 'Generate PDF reports', 'pending', 'low', DATE_ADD(CURDATE(), INTERVAL 14 DAY), NULL, NOW(), 'Backend'),
(4, 4, 'SEO optimization', 'Improve search rankings', 'pending', 'low', DATE_ADD(CURDATE(), INTERVAL 20 DAY), NULL, NOW(), 'Marketing'),
(5, 5, 'Setup monitoring', 'Add application monitoring', 'pending', 'medium', DATE_ADD(CURDATE(), INTERVAL 12 DAY), NULL, NOW(), 'DevOps'),
(1, 3, 'Create admin panel', 'Admin dashboard for management', 'pending', 'high', DATE_ADD(CURDATE(), INTERVAL 8 DAY), NULL, DATE_SUB(NOW(), INTERVAL 1 HOUR), 'Backend'),
(2, 2, 'Add file upload', 'Support for file attachments', 'pending', 'medium', DATE_ADD(CURDATE(), INTERVAL 9 DAY), NULL, DATE_SUB(NOW(), INTERVAL 2 HOUR), 'Frontend'),
(3, 1, 'Security audit', 'Perform security assessment', 'pending', 'critical', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, DATE_SUB(NOW(), INTERVAL 3 HOUR), 'Security');

-- Insert sample activities
INSERT INTO activities (user_id, action, target_type, target_id, description, created_at) VALUES
(2, 'completed', 'task', 10, 'Jane Smith completed task "Fix login bug"', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 'completed', 'task', 9, 'Charlie Davis completed task "Implement search feature"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 'created', 'task', 11, 'John Doe created a new task "Complete authentication system"', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 'joined', 'team', 1, 'Bob Wilson joined the Development Team', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'updated', 'project', NULL, 'Admin updated project settings', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 'completed', 'task', 8, 'Alice Brown completed task "Design landing page"', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 'started', 'task', 13, 'Jane Smith started working on "Create notification system"', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 'commented', 'task', 15, 'Charlie Davis commented on task "Write test cases"', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'assigned', 'task', 21, 'John Doe assigned task to Bob Wilson', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 'updated', 'task', 12, 'Bob Wilson updated task priority', DATE_SUB(NOW(), INTERVAL 4 HOUR));

-- Update task counts for more recent data (last 7 days)
INSERT INTO tasks (user_id, assigned_to, title, description, status, priority, due_date, completed_at, created_at, project) VALUES
-- Tasks completed in last 7 days for trend chart
(1, 2, 'Fix header styling', 'Adjust header CSS', 'completed', 'low', CURDATE(), DATE_SUB(NOW(), INTERVAL 6 HOUR), DATE_SUB(NOW(), INTERVAL 7 DAY), 'Frontend'),
(2, 3, 'Update dependencies', 'Update npm packages', 'completed', 'medium', CURDATE(), DATE_SUB(NOW(), INTERVAL 12 HOUR), DATE_SUB(NOW(), INTERVAL 6 DAY), 'DevOps'),
(3, 1, 'Add validation', 'Form validation rules', 'completed', 'medium', DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 18 HOUR), DATE_SUB(NOW(), INTERVAL 5 DAY), 'Frontend'),
(4, 4, 'Cache optimization', 'Implement Redis cache', 'completed', 'high', DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 24 HOUR), DATE_SUB(NOW(), INTERVAL 4 DAY), 'Backend');