-- Add 'review' status to tasks table for kanban board support
-- This allows tasks to have a review stage before completion

ALTER TABLE tasks
MODIFY COLUMN status ENUM('pending', 'in_progress', 'review', 'completed', 'cancelled') DEFAULT 'pending';

-- Update any existing tasks that might have invalid status
UPDATE tasks SET status = 'pending' WHERE status NOT IN ('pending', 'in_progress', 'review', 'completed', 'cancelled');