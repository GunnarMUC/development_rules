-- Create settings table for global configuration
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` ENUM('text', 'number', 'boolean', 'json', 'encrypted') DEFAULT 'text',
    `description` VARCHAR(500),
    `category` VARCHAR(50),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_setting_key` (`setting_key`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `category`) VALUES
('site_name', 'SaaS Template', 'text', 'The name of your site', 'general'),
('site_description', 'A modern SaaS application template', 'text', 'Brief description of your site', 'general'),
('max_file_size', '10485760', 'number', 'Maximum file upload size in bytes', 'limits'),
('max_team_members', '50', 'number', 'Maximum members allowed per team', 'limits'),
('max_teams_per_user', '10', 'number', 'Maximum teams a user can join', 'limits'),
('allow_registration', '1', 'boolean', 'Allow new users to register', 'security'),
('require_email_verification', '0', 'boolean', 'Require email verification for new users', 'security'),
('session_timeout', '1800', 'number', 'Session timeout in seconds', 'security'),
('password_min_length', '8', 'number', 'Minimum password length', 'security'),
('enable_notifications', '1', 'boolean', 'Enable system notifications', 'features'),
('enable_api', '1', 'boolean', 'Enable API access', 'features'),
('api_rate_limit', '100', 'number', 'API rate limit per hour', 'security'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode', 'system'),
('maintenance_message', 'We are currently performing maintenance. Please check back later.', 'text', 'Message shown during maintenance', 'system'),
('smtp_host', '', 'text', 'SMTP server hostname', 'email'),
('smtp_port', '587', 'number', 'SMTP server port', 'email'),
('smtp_username', '', 'text', 'SMTP username', 'email'),
('smtp_password', '', 'encrypted', 'SMTP password', 'email'),
('smtp_from_email', '', 'text', 'From email address', 'email'),
('smtp_from_name', '', 'text', 'From name for emails', 'email'),
('date_format', 'Y-m-d', 'text', 'Date format for display', 'localization'),
('time_format', 'H:i:s', 'text', 'Time format for display', 'localization'),
('timezone', 'America/New_York', 'text', 'Default timezone', 'localization')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);