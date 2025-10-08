-- Add more sample tasks for dashboard with proper dates and user assignments

-- Get existing user IDs (2, 6, 7) and use them

-- Add some completed tasks with completed_at dates for the trend chart
INSERT INTO tasks (user_id, assigned_to, title, description, status, priority, due_date, completed_at, created_at, project) VALUES
-- Completed tasks for user 7 (Admin)
(7, 7, 'Setup authentication system', 'Implement JWT auth', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 24 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY), 'Backend'),
(7, 7, 'Create login page', 'Design and implement login', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 22 DAY), DATE_SUB(NOW(), INTERVAL 21 DAY), DATE_SUB(NOW(), INTERVAL 28 DAY), 'Frontend'),
(7, 2, 'Database optimization', 'Optimize slow queries', 'completed', 'critical', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 19 DAY), DATE_SUB(NOW(), INTERVAL 27 DAY), 'Backend'),
(7, 6, 'Write unit tests', 'Coverage for auth module', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 18 DAY), DATE_SUB(NOW(), INTERVAL 17 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY), 'Testing'),
(7, 7, 'Deploy staging server', 'Setup staging environment', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), 'DevOps'),
(7, 2, 'Fix security issues', 'Address security audit findings', 'completed', 'critical', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), 'Security'),
(7, 7, 'Create admin dashboard', 'Admin panel implementation', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 12 DAY), 'Frontend'),
(7, 6, 'API rate limiting', 'Implement rate limiting', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY), 'Backend'),
(7, 7, 'Update dependencies', 'Update all npm packages', 'completed', 'low', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY), 'Maintenance'),
(7, 2, 'Fix payment bug', 'Resolve checkout issues', 'completed', 'critical', DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 6 HOUR), DATE_SUB(NOW(), INTERVAL 5 DAY), 'Backend'),

-- Completed tasks for user 2 (Edward)
(2, 2, 'Design homepage', 'Create new homepage design', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(NOW(), INTERVAL 11 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY), 'Design'),
(2, 2, 'Implement search', 'Add search functionality', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY), 'Frontend'),
(2, 7, 'Code review', 'Review pull requests', 'completed', 'low', DATE_SUB(CURDATE(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 9 DAY), 'Review'),
(2, 2, 'Fix CSS issues', 'Responsive design fixes', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 6 DAY), 'Frontend'),

-- Completed tasks for user 6 (Ed)
(6, 6, 'Setup monitoring', 'Configure application monitoring', 'completed', 'high', DATE_SUB(CURDATE(), INTERVAL 16 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 22 DAY), 'DevOps'),
(6, 6, 'Create backup system', 'Automated backups', 'completed', 'critical', DATE_SUB(CURDATE(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY), DATE_SUB(NOW(), INTERVAL 13 DAY), 'DevOps'),
(6, 7, 'Performance tuning', 'Optimize application performance', 'completed', 'medium', DATE_SUB(CURDATE(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 11 DAY), 'Backend'),

-- In-progress tasks
(7, 7, 'Implement notifications', 'Real-time notification system', 'in_progress', 'high', DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL, DATE_SUB(NOW(), INTERVAL 4 DAY), 'Backend'),
(2, 2, 'Mobile responsive updates', 'Make all pages mobile-friendly', 'in_progress', 'high', DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, DATE_SUB(NOW(), INTERVAL 3 DAY), 'Frontend'),
(6, 6, 'Database migration', 'Migrate to new database schema', 'in_progress', 'critical', DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, DATE_SUB(NOW(), INTERVAL 2 DAY), 'Backend'),
(7, 2, 'User feedback system', 'Add feedback collection', 'in_progress', 'medium', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, DATE_SUB(NOW(), INTERVAL 1 DAY), 'Feature'),

-- Pending tasks
(7, 7, 'Multi-language support', 'Add i18n support', 'pending', 'medium', DATE_ADD(CURDATE(), INTERVAL 10 DAY), NULL, NOW(), 'Feature'),
(2, 2, 'Dark mode', 'Implement dark mode toggle', 'pending', 'low', DATE_ADD(CURDATE(), INTERVAL 15 DAY), NULL, NOW(), 'Frontend'),
(6, 6, 'Load testing', 'Performance load testing', 'pending', 'high', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, NOW(), 'Testing'),
(7, 2, 'Email templates', 'Design email templates', 'pending', 'medium', DATE_ADD(CURDATE(), INTERVAL 12 DAY), NULL, NOW(), 'Design'),
(2, 6, 'API versioning', 'Implement API v2', 'pending', 'high', DATE_ADD(CURDATE(), INTERVAL 8 DAY), NULL, NOW(), 'Backend'),
(6, 7, 'Security audit', 'Quarterly security review', 'pending', 'critical', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, NOW(), 'Security');

-- Create activities table if not exists and add sample activities
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

-- Insert sample activities
INSERT INTO activities (user_id, action, target_type, target_id, description, created_at) VALUES
(7, 'completed', 'task', 22, 'completed task "Deploy to production"', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(2, 'completed', 'task', 14, 'completed task "Fix CSS issues"', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 'completed', 'task', 17, 'completed task "Performance tuning"', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(7, 'created', 'task', 18, 'created new task "Implement notifications"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(2, 'started', 'task', 19, 'started working on "Mobile responsive updates"', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 'commented', 'task', 20, 'commented on task "Database migration"', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(7, 'updated', 'project', NULL, 'updated project settings', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'assigned', 'task', 25, 'assigned task to Ed Hon', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(6, 'joined', 'team', 1, 'joined the Development Team', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(7, 'created', 'task', 27, 'created new task "Security audit"', DATE_SUB(NOW(), INTERVAL 2 HOUR));