-- Fix for create-task.php error: Missing columns in tasks table
-- This adds the category, tags, and created_by columns that are referenced in the PHP code

ALTER TABLE tasks
ADD COLUMN category VARCHAR(100) DEFAULT NULL COMMENT 'Task category',
ADD COLUMN tags TEXT DEFAULT NULL COMMENT 'Comma-separated tags',
ADD COLUMN created_by INT UNSIGNED DEFAULT NULL COMMENT 'User ID who created the task',
ADD INDEX idx_category (category),
ADD INDEX idx_created_by (created_by);

-- Add foreign key constraint for created_by
ALTER TABLE tasks
ADD CONSTRAINT fk_tasks_created_by
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;