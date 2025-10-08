<?php
/**
 * Tasks API Endpoint
 * Handles all AJAX requests for task CRUD operations
 */

session_start();
header('Content-Type: application/json');

// Include required files
require_once '../includes/auth.php';
require_once '../classes/Task.php';
require_once '../includes/activity_logger.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

$userId = $_SESSION['user_id'];
$teamId = $_SESSION['current_team_id'] ?? null;
$task = new Task();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Get tasks for DataTables
            if ($method !== 'GET' && $method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Add team filter if set
            $params = $method === 'POST' ? $_POST : $_GET;
            if ($teamId !== null) {
                $params['team_id'] = $teamId;
            }
            $result = $task->getDataTableTasks($userId, $params);
            echo json_encode($result);
            break;

        case 'get':
            // Get single task
            if ($method !== 'GET' && $method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $taskId = intval(($method === 'POST' ? $_POST['id'] : $_GET['id']) ?? 0);
            if (!$taskId) {
                throw new Exception('Invalid task ID');
            }

            $taskData = $task->getById($taskId, $userId, $teamId);
            if (!$taskData) {
                throw new Exception('Task not found');
            }

            echo json_encode([
                'success' => true,
                'data' => $taskData,
                'task' => $taskData
            ]);
            break;

        case 'create':
            // Create new task
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Validate required fields
            if (empty($_POST['title'])) {
                throw new Exception('Title is required');
            }

            $taskData = [
                'user_id' => $userId,
                'team_id' => $teamId,
                'assigned_to' => !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'status' => $_POST['status'] ?? 'pending',
                'priority' => $_POST['priority'] ?? 'medium',
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null
            ];

            $taskId = $task->create($taskData);
            if (!$taskId) {
                throw new Exception('Failed to create task');
            }

            // Log activity
            logTaskActivity($userId, 'created', [
                'id' => $taskId,
                'title' => $taskData['title']
            ]);

            // Send notification if task is assigned to someone
            if (!empty($taskData['assigned_to']) && $taskData['assigned_to'] != $userId) {
                require_once '../api/notifications.php';
                $userName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
                createNotification(
                    $taskData['assigned_to'],
                    'task_assigned',
                    'New Task Assigned',
                    $userName . ' has assigned you a task: ' . $taskData['title'],
                    '/tasks.php?task=' . $taskId,
                    $userId,
                    $teamId
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => ['id' => $taskId]
            ]);
            break;

        case 'update':
            // Update existing task
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $taskId = intval($_POST['id'] ?? 0);
            if (!$taskId) {
                throw new Exception('Invalid task ID');
            }

            // Validate required fields
            if (empty($_POST['title'])) {
                throw new Exception('Title is required');
            }

            $taskData = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description'] ?? ''),
                'assigned_to' => !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null,
                'status' => $_POST['status'] ?? 'pending',
                'priority' => $_POST['priority'] ?? 'medium',
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null
            ];

            // Get previous task details to check if assignment changed
            $previousTask = $task->getById($taskId, $userId, $teamId);

            $result = $task->update($taskId, $taskData, $userId);
            if (!$result) {
                throw new Exception('Failed to update task or task not found');
            }

            // Log activity
            logTaskActivity($userId, 'updated', [
                'id' => $taskId,
                'title' => $taskData['title']
            ]);

            // Log status change if it changed
            if ($previousTask['status'] != $taskData['status']) {
                if ($taskData['status'] === 'completed') {
                    logTaskActivity($userId, 'completed', [
                        'id' => $taskId,
                        'title' => $taskData['title']
                    ]);
                } else if ($taskData['status'] === 'in_progress' && $previousTask['status'] === 'pending') {
                    logTaskActivity($userId, 'started', [
                        'id' => $taskId,
                        'title' => $taskData['title']
                    ]);
                }
            }

            // Send notification if task assignment changed
            if (!empty($taskData['assigned_to']) && $taskData['assigned_to'] != $userId) {
                // Check if this is a new assignment or reassignment
                if ($previousTask['assigned_to'] != $taskData['assigned_to']) {
                    require_once '../api/notifications.php';
                    $userName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
                    createNotification(
                        $taskData['assigned_to'],
                        'task_assigned',
                        'Task Assigned to You',
                        $userName . ' has assigned you a task: ' . $taskData['title'],
                        '/tasks.php?task=' . $taskId,
                        $userId,
                        $teamId
                    );
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);
            break;

        case 'delete':
            // Delete task
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $taskId = intval($_POST['id'] ?? 0);
            if (!$taskId) {
                throw new Exception('Invalid task ID');
            }

            // Get task details before deleting
            $taskData = $task->getById($taskId, $userId, $teamId);

            $result = $task->delete($taskId, $userId);
            if (!$result) {
                throw new Exception('Failed to delete task or task not found');
            }

            // Log activity
            if ($taskData) {
                logTaskActivity($userId, 'deleted', [
                    'id' => $taskId,
                    'title' => $taskData['title']
                ]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);
            break;

        case 'toggle':
            // Toggle task completion status
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $taskId = intval($_POST['id'] ?? 0);
            if (!$taskId) {
                throw new Exception('Invalid task ID');
            }

            // Get current task data
            $previousTask = $task->getById($taskId, $userId, $teamId);

            $result = $task->toggleComplete($taskId, $userId);
            if (!$result) {
                throw new Exception('Failed to update task status');
            }

            // Get updated task data
            $taskData = $task->getById($taskId, $userId, $teamId);

            // Log activity based on status change
            if ($previousTask && $taskData) {
                if ($taskData['status'] === 'completed' && $previousTask['status'] !== 'completed') {
                    logTaskActivity($userId, 'completed', [
                        'id' => $taskId,
                        'title' => $taskData['title']
                    ]);
                } else if ($taskData['status'] !== 'completed' && $previousTask['status'] === 'completed') {
                    logTaskActivity($userId, 'updated', [
                        'id' => $taskId,
                        'title' => $taskData['title']
                    ]);
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Task status updated',
                'data' => [
                    'status' => $taskData['status']
                ]
            ]);
            break;

        case 'statistics':
            // Get user task statistics
            if ($method !== 'GET' && $method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Check if we should only get tasks for current user (not created by, but assigned to)
            $myTasksOnly = isset($_REQUEST['my_tasks_only']) && $_REQUEST['my_tasks_only'];
            $stats = $task->getUserStatistics($userId, $teamId, $myTasksOnly);

            echo json_encode([
                'success' => true,
                'data' => $stats,
                'statistics' => $stats
            ]);
            break;

        case 'team_members':
            // Get team members for assignment dropdown
            if ($method !== 'GET' && $method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            if ($teamId === null) {
                echo json_encode(['success' => true, 'data' => []]);
                break;
            }

            $members = $task->getTeamMembers($teamId);
            echo json_encode(['success' => true, 'data' => $members]);
            break;

        case 'update_status':
            // Update task status (for kanban board)
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $taskId = intval($_POST['id'] ?? 0);
            if (!$taskId) {
                throw new Exception('Invalid task ID');
            }

            $newStatus = $_POST['status'] ?? '';
            if (!in_array($newStatus, ['todo', 'in_progress', 'review', 'done', 'pending', 'completed'])) {
                throw new Exception('Invalid status');
            }

            // Map kanban statuses to database statuses
            $statusMap = [
                'todo' => 'pending',
                'in_progress' => 'in_progress',
                'review' => 'review',
                'done' => 'completed'
            ];

            $dbStatus = $statusMap[$newStatus] ?? $newStatus;

            $result = $task->update($taskId, ['status' => $dbStatus], $userId);
            if (!$result) {
                throw new Exception('Failed to update task status');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Task status updated successfully'
            ]);
            break;

        case 'kanban':
            // Get tasks for kanban board
            if ($method !== 'GET' && $method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $filters = [];
            if ($teamId !== null) {
                $filters['team_id'] = $teamId;
            }

            $tasks = $task->getUserTasks($userId, $filters);

            // Map database statuses to kanban columns
            $kanbanTasks = [];
            foreach ($tasks as $t) {
                $kanbanStatus = $t['status'];
                if ($t['status'] === 'pending') {
                    $kanbanStatus = 'todo';
                } elseif ($t['status'] === 'completed') {
                    $kanbanStatus = 'done';
                }

                $kanbanTasks[] = [
                    'id' => $t['id'],
                    'title' => $t['title'],
                    'description' => $t['description'],
                    'status' => $kanbanStatus,
                    'priority' => $t['priority'],
                    'due_date' => $t['due_date'],
                    'assigned_to' => $t['assigned_to'],
                    'assigned_name' => $t['assigned_first_name'] ?
                        $t['assigned_first_name'] . ' ' . $t['assigned_last_name'] : null,
                    'tags' => [] // Could be enhanced later
                ];
            }

            echo json_encode([
                'success' => true,
                'tasks' => $kanbanTasks
            ]);
            break;

        case 'completed_statistics':
            // Get completed task statistics
            if ($method !== 'GET' && $method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $stats = $task->getCompletedStatistics($userId, $teamId);

            echo json_encode([
                'success' => true,
                'statistics' => $stats
            ]);
            break;

        case 'clear_completed':
            // Clear all completed tasks
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $result = $task->clearCompleted($userId, $teamId);

            echo json_encode([
                'success' => true,
                'message' => 'All completed tasks have been cleared',
                'deleted_count' => $result
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}